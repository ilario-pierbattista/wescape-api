var rest = require("restler");
var url = require('url');
var env = require('../env.js');
var mutex = require('semaphore')(1);
var callSem = require('semaphore')(5);  // Massimo 5 in contemporanea
var strformat = require('strformat');
var Progress = require('progress');
require("../array-extension");

function DataLoader() {
    var $this = this;
    this.data = {};
    this.dbmirror = {};

    Object.keys(env.parsed_data_paths).map(function (label) {
        $this.data[label] = require(env.parsed_data_paths[label]);
    });

    this.endpoints = {
        "get_node": this.build_url("api/v1/nodes/{id}.json"),
        "get_nodes": this.build_url("api/v1/nodes.json"),
        "post_node": this.build_url("api/v1/nodes.json"),
        "delete_node": this.build_url("api/v1/nodes/{id}.json"),
        "get_edge": this.build_url("api/v1/edges/{id}.json"),
        "get_edges": this.build_url("api/v1/edges.json"),
        "post_edge": this.build_url("api/v1/edges.json"),
        "delete_edge": this.build_url("api/v1/edges/{id}.json")
    }
}

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
 * Rimozione dei nodi presenti
 * @returns {DataLoader}
 */
DataLoader.prototype.clearData = function () {
    var $this = this;
    var savedNodes = null;
    var savedEdges = null;

    // Lettura degli archi presenti nel database
    mutex.take(function () {
        rest.get($this.endpoints.get_edges)
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
        rest.get($this.endpoints.get_nodes)
            .on("complete", function (data) {
                $this.handleServerResult(data, {
                    "success": function (data) {
                        savedNodes = data;
                    },
                    "complete": function () {
                        mutex.leave();
                    }
                });
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
 * Metodo per la definizione della funzione di creazione del nodo
 * @param node
 * @returns {Function}
 */
DataLoader.prototype.postNodeFunction = function (node) {
    var $this = this;
    return function () {
        var jsonData = $this.transform_node(node);
        rest.postJson($this.endpoints.post_node, jsonData)
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
        rest.postJson($this.endpoints.post_edge, jsonData)
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
    }
};

DataLoader.prototype.postStairsFunction = function (stair) {
    var $this = this;
    return function () {
        var jsonData = $this.transform_stairs(stair);
        rest.postJson($this.endpoints.post_edge, jsonData)
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
        rest.del(strformat($this.endpoints.delete_node, {id: node.id}))
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
        rest.del(strformat($this.endpoints.delete_edge, {id: edge.id}))
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
        width: node.larghezza
    };
};

/**
 * Trasforma un oggetto Edge locale in un oggetto per l'inserimento nel db
 * @param edge
 * @returns {{begin: (*|String|string), end: (*|String|string), width: *, length: *, stairs: boolean, v: number, i: number, los: number, c: number}}
 */
DataLoader.prototype.transform_edge = function (edge) {
    var beginNode = this.search_node(this.dbmirror.nodes, {
        "name": edge.node1.codice,
        "meter_x": edge.node1.coordinates.meters.x,
        "meter_y": edge.node1.coordinates.meters.y
    });
    var endNode = this.search_node(this.dbmirror.nodes, {
        "name": edge.node2.codice,
        "meter_x": edge.node2.coordinates.meters.x,
        "meter_y": edge.node2.coordinates.meters.y
    });

    return {
        "begin": beginNode.id,
        "end": endNode.id,
        "width": edge.larghezza,
        "length": edge.lunghezza,
        "stairs": false,
        "v": 0.,
        "i": 0.,
        "los": 0.,
        "c": 0.
    }
};

/**
 * Trasforma un oggetto strais locale in uno pronto per il database
 * @param stairs
 * @returns {{begin: (*|String|string), end: (*|String|string), width: *, length: *, stairs: boolean, v: number, i: number, los: number, c: number}}
 */
DataLoader.prototype.transform_stairs = function (stairs) {
    var beginNode = this.search_node(this.dbmirror.nodes, {
        "name": stairs.node1
    });
    var endNode = this.search_node(this.dbmirror.nodes, {
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
DataLoader.prototype.search_node = function (nodes, params) {
    var founds = nodes.searchObject(params);
    if (founds.length > 0) {
        return founds[0];
    } else {
        return null;
    }
};

/**
 * Costruisce l'url dell'endpoint specificando la path
 * @param path
 * @returns {*}
 */
DataLoader.prototype.build_url = function (path) {
    return url.format({
        protocol: "http",
        hostname: env.host_name,
        pathname: path
    });
};

module.exports = new DataLoader();