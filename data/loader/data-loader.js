var restler = require("restler");
var url = require('url');
var env = require('../env.js');
var mutex = require('semaphore')(1);
var callSem = require('semaphore')(5);  // Massimo 5 in contemporanea
var strformat = require('strformat');
var Progress = require('progress');
var exit = require('exit');
var logger = require("../logger");
require("../array-extension");

const LOG_FILE = "data-loader.log.json";

/**
 * Prototipo di client restful configurato per autenticarsi con il server
 */
OAuthClient = restler.service(function (accessToken) {
    this.defaults.headers = {
        "Content-Type": 'application/json',
        "Authorization": 'Bearer ' + accessToken
    }
});

/**
 * Classe per il caricamento dei dati
 * @constructor
 */
function DataLoader(accessToken) {
    var $this = this;
    this.data = {};
    this.dbmirror = {};
    this.client = new OAuthClient(accessToken);

    Object.keys(env.parsed_data_paths).map(function (label) {
        $this.data[label] = require(env.parsed_data_paths[label]);
    });

    this.endpoints = {
        "get_node": env.build_url("api/v1/nodes/{id}.json"),
        "getNodes": env.build_url("api/v1/nodes.json"),
        "post_node": env.build_url("api/v1/nodes.json"),
        "delete_node": env.build_url("api/v1/nodes/{id}.json"),
        "get_edge": env.build_url("api/v1/edges/{id}.json"),
        "getEdges": env.build_url("api/v1/edges.json"),
        "post_edge": env.build_url("api/v1/edges.json"),
        "delete_edge": env.build_url("api/v1/edges/{id}.json")
    }
}

/**
 * Rimozione dei nodi presenti
 * @returns {DataLoader}
 */
DataLoader.prototype.clearData = function () {
    var $this = this;
    var savedNodes = null;
    var savedEdges = null;

    // Lettura degli archi presenti nel database
    mutex.take(function () {
        $this.client.get($this.endpoints.getEdges)
            .on("complete", function (data) {
                $this.handleServerResult(data, {
                    "success": function (data) {
                        savedEdges = data;
                    },
                    "complete": function () {
                        mutex.leave();
                    }
                })
            })
            .on("fail", function (data, response) {
                logger.saveLog(LOG_FILE, {
                    "response": response,
                    "data": data
                });
                console.error("Error cleaning edges");
                exit(1);
            })
    });

    // Rimozione degli archi presenti nel database
    mutex.take(function () {
        if (!(typeof savedEdges == "string" && !savedEdges.trim())) {
            $this.clearProgress = new Progress("clearing edges :bar :current/:total",
                {total: savedEdges.length});
            savedEdges.map(function (edge) {
                callSem.take($this.deleteEdgeFunction(edge));
            });
        } else {
            mutex.leave();
        }
    });

    // Lettura dei nodi presenti nel database
    mutex.take(function () {
        $this.client.get($this.endpoints.getNodes)
            .on("complete", function (data) {
                $this.handleServerResult(data, {
                    "success": function (data) {
                        savedNodes = data;
                    },
                    "complete": function () {
                        mutex.leave();
                    }
                });
            })
            .on("fail", function (data, response) {
                logger.saveLog(LOG_FILE, {
                    "response": response,
                    "data": data
                });
                console.error("Error cleaning nodes");
                exit(1);
            });
    });

    // Rimozione dei nodi presenti nel database
    mutex.take(function () {
        if (!(typeof savedNodes == "string" && !savedNodes.trim())) {
            $this.clearProgress = new Progress("clearing nodes :bar :current/:total",
                {total: savedNodes.length});
            savedNodes.map(function (node) {
                callSem.take($this.deleteNodeFunction(node));
            });
        } else {
            mutex.leave();
        }
    });

    return this;
};

/**
 * Upload
 * @returns {DataLoader}
 */
DataLoader.prototype.loadData = function () {
    var $this = this;
    // Caricamento dei nodi
    mutex.take(function () {
        $this.loadProgress = new Progress("uploading nodes :bar :current/:total",
            {total: $this.data.nodes.length});
        $this.data.nodes.map(function (node) {
            callSem.take($this.postNodeFunction(node));
        });
    });

    // Caricamento dei lati
    mutex.take(function () {
        $this.loadProgress = new Progress("uploading edges :bar :current/:total",
            {total: $this.data.edges.length});
        $this.data.edges.map(function (edge) {
            callSem.take($this.postEdgeFunction(edge));
        });
    });

    // Caricamento delle scale
    mutex.take(function () {
        $this.loadProgress = new Progress("uploading stairs :bar :current/:total",
            {total: $this.data.stairs.length});
        $this.data.stairs.map(function (stair) {
            callSem.take($this.postStairsFunction(stair));
        })
    });

    return this;
};

/**
 * Metodo per la definizione della funzione di creazione del nodo
 * @param node
 * @returns {Function}
 */
DataLoader.prototype.postNodeFunction = function (node) {
    var $this = this;
    return function () {
        var jsonData = $this.transform_node(node);
        $this.client.post($this.endpoints.post_node, {data: jsonData})
            .on("complete", function (result) {
                $this.handleServerResult(result, {
                    "success": function (data) {
                        if (!$this.dbmirror.hasOwnProperty("nodes")) {
                            $this.dbmirror.nodes = [];
                        }
                        $this.dbmirror.nodes.push(data);
                    },
                    "complete": function () {
                        callSem.leave();
                        $this.loadProgress.tick();
                        if ($this.loadProgress.complete) {
                            mutex.leave();
                        }
                    }
                });
            })
            .on("fail", function (data, response) {
                logger.saveLog(LOG_FILE, {
                    "response": response,
                    "data": data
                });
                console.error("Error posting Nodes");
                exit(1);
            });
    };
};

/**
 * Inserisce i lati
 * @param edge
 * @returns {Function}
 */
DataLoader.prototype.postEdgeFunction = function (edge) {
    var $this = this;
    return function () {
        var jsonData = $this.transform_edge(edge);
        $this.client.post($this.endpoints.post_edge, {data: jsonData})
            .on("complete", function (result) {
                $this.handleServerResult(result, {
                    success: function (data) {
                        if (!$this.dbmirror.hasOwnProperty("edges")) {
                            $this.dbmirror.edges = [];
                        }
                        $this.dbmirror.edges.push(data);
                    },
                    complete: function () {
                        callSem.leave();
                        $this.loadProgress.tick();
                        if ($this.loadProgress.complete) {
                            mutex.leave();
                        }
                    }
                })
            })
            .on("fail", function (data, response) {
                logger.saveLog(LOG_FILE, {
                    "response": response,
                    "data": data
                });
                console.error("Error posting Edges");
                exit(1);
            });
    }
};

DataLoader.prototype.postStairsFunction = function (stair) {
    var $this = this;
    return function () {
        var jsonData = $this.transform_stairs(stair);
        $this.client.post($this.endpoints.post_edge, {data: jsonData})
            .on("complete", function (result) {
                $this.handleServerResult(result, {
                    success: function (data) {
                        if (!$this.dbmirror.hasOwnProperty("stairs")) {
                            $this.dbmirror.stairs = [];
                        }
                        $this.dbmirror.stairs.push(data);
                    },
                    complete: function () {
                        callSem.leave();
                        $this.loadProgress.tick();
                        if ($this.loadProgress.complete) {
                            mutex.leave();
                        }
                    }
                })
            })
            .on("fail", function (data, response) {
                logger.saveLog(LOG_FILE, {
                    "response": response,
                    "data": data
                });
                console.error("Error posting Stairs");
                exit(1);
            });
    }
};

/**
 * Metodo per la creazione della funzione di eliminazione del nodo
 *
 * @param node Nodo da eliminare
 * @returns {Function}
 */
DataLoader.prototype.deleteNodeFunction = function (node) {
    var $this = this;
    return function () {
        $this.client.del(strformat($this.endpoints.delete_node, {id: node.id}))
            .on("complete", function (result) {
                $this.handleServerResult(result, {
                    "complete": function () {
                        $this.clearProgress.tick();
                        callSem.leave();
                        if ($this.clearProgress.complete) {
                            mutex.leave();
                        }
                    }
                })
            });
    };
};

/**
 * Metodo per la creazione della funzione di elminazione degli archi
 * @param edge
 * @returns {Function}
 */
DataLoader.prototype.deleteEdgeFunction = function (edge) {
    var $this = this;
    return function () {
        $this.client.del(strformat($this.endpoints.delete_edge, {id: edge.id}))
            .on("complete", function (result) {
                $this.handleServerResult(result, {
                    "complete": function () {
                        $this.clearProgress.tick();
                        callSem.leave();
                        if ($this.clearProgress.complete) {
                            mutex.leave();
                        }
                    }
                })
            });
    }
};

/**
 * Handler dei risultati del server
 * @param result Risposta del server
 * @param callbacks Callbacks da eseguire
 */
DataLoader.prototype.handleServerResult = function (result, callbacks) {
    // Registrazione delle callback standard
    var handlers = {
        "connection_error": function (error) {
            console.log(error);
        },
        "success": function (data) {
        },
        "error": function (error) {
            console.log(strformat("Errore {code}", {code: error.error.code}));
            console.log(error.error["exception"]);
        },
        "complete": function (result) {
        }
    };

    // Registrazione delle callback custom
    Object.keys(handlers).map(function (label) {
        if (callbacks.hasOwnProperty(label) && typeof callbacks[label] == "function") {
            handlers[label] = callbacks[label];
        }
    });

    // Esecuzione delle callback
    if (result instanceof Error) {
        handlers.connection_error(result);
    } else {
        if (result.hasOwnProperty('error')) {
            handlers.error(result);
        } else {
            handlers.success(result);
        }
    }
    handlers.complete(result);
};

/**
 * Trasforma un oggetto nodo parsato in un oggetto pronto ad essere inviato al servizio
 * @param node Oggetto nodo serializzato nel file json
 * @returns {{name: *, x: (*|Number), y: (*|Number), floor: *, width: *}}
 */
DataLoader.prototype.transform_node = function (node) {
    return {
        name: node.codice,
        x: node.coordinates.pixel.x,
        y: node.coordinates.pixel.y,
        meter_x: node.coordinates.meters.x,
        meter_y: node.coordinates.meters.y,
        floor: node.quota,
        width: node.larghezza,
        type: node.type
    };
};

/**
 * Trasforma un oggetto Edge locale in un oggetto per l'inserimento nel db
 * @param edge
 * @returns {{begin: (*|String|string), end: (*|String|string), width: *, length: *, v: number, i: number, los: number, c: number}}
 */
DataLoader.prototype.transform_edge = function (edge) {
    var beginNode = this.searchNode(this.dbmirror.nodes, {
        "name": edge.node1.codice,
        "meter_x": edge.node1.coordinates.meters.x,
        "meter_y": edge.node1.coordinates.meters.y
    });
    var endNode = this.searchNode(this.dbmirror.nodes, {
        "name": edge.node2.codice,
        "meter_x": edge.node2.coordinates.meters.x,
        "meter_y": edge.node2.coordinates.meters.y
    });

    return {
        "begin": beginNode.id,
        "end": endNode.id,
        "width": edge.larghezza,
        "length": edge.lunghezza,
        // Lo standard de facto dei form codificati in json prevede che
        // i campi impostati a false siano assenti
        // "stairs": false,
        "v": 0.,
        "i": 0.,
        "los": 0.,
        "c": 0.
    };
};

/**
 * Trasforma un oggetto strais locale in uno pronto per il database
 * @param stairs
 * @returns {{begin: (*|String|string), end: (*|String|string), width: *, length: *, stairs: boolean, v: number, i: number, los: number, c: number}}
 */
DataLoader.prototype.transform_stairs = function (stairs) {
    var beginNode = this.searchNode(this.dbmirror.nodes, {
        "name": stairs.node1
    });
    var endNode = this.searchNode(this.dbmirror.nodes, {
        "name": stairs.node2
    });

    return {
        "begin": beginNode.id,
        "end": endNode.id,
        "width": stairs.larghezza,
        "length": stairs.lunghezza,
        "stairs": true,
        "v": 0.,
        "i": 0.,
        "los": 0.,
        "c": 0.
    }
};

/**
 * Ricerca un nodo
 * @param nodes
 * @param params
 * @returns {*}
 */
DataLoader.prototype.searchNode = function (nodes, params) {
    var searchParams = {
        "name": params.name
    };

    var nodesFound = nodes.searchObject(searchParams);
    var found = nodesFound.length > 0;
    var unique = nodesFound.length == 1;

    if(found && !unique) {
        searchParams.meter_x = params.meter_x;
        searchParams.meter_y = params.meter_y;
        nodesFound = nodes.searchObject(searchParams);

        found = nodesFound.length > 0;
        unique = nodesFound.length == 1;
    }

    if(found && unique) {
        return nodesFound[0];
    } else {
        // @TODO debugging
        console.log("Node not found or not unique");
        console.log(params);
        return null;
    }
};

module.exports = DataLoader;