var rest = require("restler");
var url = require('url');
var env = require('../env.js');
var mutex = require('semaphore')(1);
var callSem = require('semaphore')(5);  // Massimo 5 in contemporanea
var strformat = require('strformat');

function DataLoader() {
    var $this = this;
    this.data = {};

    Object.keys(env.parsed_data_paths).map(function (label) {
        $this.data[label] = require(env.parsed_data_paths[label]);
    });

    this.endpoints = {
        "get_node": this.build_url("api/v1/nodes/{id}.json"),
        "get_nodes": this.build_url("api/v1/nodes.json"),
        "post_node": this.build_url("api/v1/nodes.json"),
        "delete_node": this.build_url("api/v1/nodes/{id}.json")
    }
}

DataLoader.prototype.loadData = function () {
    var $this = this;
    // Caricamento dei nodi
    mutex.take(function () {
        $this.data.nodes.map(function (node) {
            callSem.take($this.postNodeFunction(node));
        });
    });

    // Caricamento dei lati @TODO implementare
    mutex.take(function () {
        console.log("Done");
        mutex.leave();
    })
};

DataLoader.prototype.clearData = function () {
    var $this = this;
    var savedNodes = null;

    // Lettura dei nodi presenti nel database
    mutex.take(function () {
        rest.get($this.endpoints.get_nodes)
            .on("complete", function (data) {
                if (data instanceof Error) {
                    console.log("Error");
                }
                savedNodes = data;
                mutex.leave();
            });
    });

    // Rimozione dei nodi presenti nel database
    mutex.take(function () {
        if (!(typeof savedNodes == "string" && !savedNodes.trim())) {
            savedNodes.map(function (node) {
                callSem.take($this.deleteNodeFunction(node));
            });
        } else {
            mutex.leave();
        }
    })
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
                if (result instanceof Error) {
                    console.log(result);
                } else {
                    if(result.hasOwnProperty('error')) {
                        console.log(strformat("Errore {code}", {code: result.error.code}));
                        console.log(result.error.exception);
                    }
                }

                callSem.leave();
                if (callSem.current == 0) {
                    mutex.leave();
                }
            });
    };
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
                if (result instanceof Error) {
                    console.log(result);
                } else {
                    // console.log(strformat("Deleted node with id {id}", {id: node.id}));
                }
                callSem.leave();

                if (callSem.current == 0) {
                    mutex.leave();
                }
            });
    };
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
        floor: node.quota,
        width: node.larghezza
    };
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