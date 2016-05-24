'use strict';

var xlsx = require('node-xlsx');
var fs = require('fs');
var path = require('path');
require("../array-extension");

var config = {
    nodesSheet: 'elenco nodi',
    edgesSheet: 'vie di piano',
    stairsSheet: 'scale',
    notUsedFlag: 'non usato'
};

// Costanti per la traduzione delle coordinate
var coordinates = {
    centerMeterX: 95,
    centerMeterY: 487,
    centerPixelX: 326,
    centerPixelY: 180,
    pixelPerMeterX: 142 / 18,
    pixelPerMeterY: 223 / 29
};

/**
 * Parser
 * @param src
 * @param dest
 * @constructor
 */
function Parser(src, dest) {
    this.src = src;
    this.dest = dest;
    this.parseData = xlsx.parse(src);
}

/**
 * Estrazione dei dati da uno specifico foglio di calcolo
 * @param  {string} sheet_name Nome del foglio di calcolo
 * @return {object}            Dati estratti dal foglio
 */
Parser.prototype.getXLSSheetData = function (sheet_name) {
    var obj = this.parseData.filter(function (value) {
        return value.name === sheet_name;
    });
    return obj[0].data;
};

/**
 * Estrazione della lista di nodi dal foglio excel
 * @return {Array} Array di nodi
 */
Parser.prototype.getNodes = function () {
    var $this = this;
    var data_nodes = this.getXLSSheetData(config.nodesSheet);
    var nodes = data_nodes.map(function (row) {
        if (typeof row[1] === "number" && typeof row[2] === "number") {
            var node = {
                "coordinates": {
                    "meters": {
                        "x": row[1],
                        "y": row[2]
                    }
                },
                "quota": parseInt(row[3]),
                "larghezza": row[4],
                "codice": row[5],
                "desc": row[6]
            };
            if (node.desc === config.notUsedFlag) {
                return null;
            }
            var description = $this.getNodeType(node);
            node.description = description.desc;
            node.type = description.type;
            node.coordinates.pixel = $this.convertNodeCoordinatesToPixels(node);
            return node;
        } else {
            return null;
        }
    });
    // Eliminazione delle voci nulle
    nodes = nodes.filter(function (value) {
        return value !== null;
    });
    return nodes;
};

/**
 * Estrazione della lista degli archi dal foglio excel
 * @param  {Array} nodes Lista dei nodi che devono essere collegati
 * @return {Array}       Lista degli archi estratti
 */
Parser.prototype.getEdges = function (nodes) {
    var data_edges = this.getXLSSheetData(config.edgesSheet);
    var $this = this;
    var edges = data_edges.map(function (row) {
        if (typeof row[0] === "number" && typeof row[1] === "number") {
            var edge = {
                "node1": {
                    "codice": row[4],
                    "coordinates": {
                        "meters": {
                            "x": row[0],
                            "y": row[1]
                        }
                    }
                },
                "node2": {
                    "codice": row[9],
                    "coordinates": {
                        "meters": {
                            "x": row[5],
                            "y": row[6]
                        }
                    }
                }
            };
            var node1 = $this.searchNode(nodes, edge.node1, true);
            var node2 = $this.searchNode(nodes, edge.node2, true);
            edge.lunghezza = $this.distance(node1, node2);
            edge.larghezza = $this.averageTrunkWidth(node1, node2);
            return edge;
        } else {
            return null;
        }
    });
    return edges.filter(function (value) {
        return value !== null;
    });
};

/**
 * Descrive i nodi
 * @param  {object} node Oggetto nodo da descrivere
 * @return {object}      Oggetto descrizione del nodo
 */
Parser.prototype.getNodeType = function (node) {
    // Descrizione della tipologia di uscita
    var desc = {
        aula: /.*R.*/.test(node.codice),
        uscita: /.*U.*/.test(node.codice),
        uscita_emergenza: /.*EM.*/.test(node.codice)
    };
    if (desc.uscita_emergenza) {
        desc.uscita = true;
    }

    // Codice più sintentico descrittivo della tipologia di nodo
    var type = "G";
    if (desc.aula) {
        type = "R";
    } else if (desc.uscita && !desc.uscita_emergenza) {
        type = "U";
    } else if (desc.uscita_emergenza) {
        type = "E";
    }

    return {
        "desc": desc,
        "type": type
    };
};

/**
 * Rimuove i nodi che non sono connessi tra di loro con degli archi
 * @param nodes Nodi
 * @param edges Archi
 * @returns {Array} Nodi connessi
 */
Parser.prototype.removeUnlinkedNodes = function (nodes, edges) {
    var $this = this;
    var linkedNodes = [];
    var end = null, begin = null, edge = null;

    for(var i = 0; i < edges.length; i++) {
        edge = edges[i];
        begin = $this.searchNode(nodes, edge.node1, false);
        end = $this.searchNode(nodes, edge.node2, false);

        if($this.searchNode(linkedNodes, begin, false) === null) {
            linkedNodes.push(begin);
        }
        if($this.searchNode(linkedNodes, end, false) === null) {
            linkedNodes.push(end);
        }
    }

    return linkedNodes;
};

/**
 * Converte le coordinate da pixel a metri
 * @param  {object} node Oggetto nodo
 * @return {object}      Nuove coordinate in pixel
 */
Parser.prototype.convertNodeCoordinatesToPixels = function (node) {
    var xm = node.coordinates.meters.x;
    var ym = node.coordinates.meters.y;
    var delta_xm = xm - coordinates.centerMeterX;
    var delta_ym = -(ym - coordinates.centerMeterY);
    var delta_xp = delta_xm * coordinates.pixelPerMeterX;
    var delta_yp = delta_ym * coordinates.pixelPerMeterY;
    var xp = coordinates.centerPixelX + delta_xp;
    var yp = coordinates.centerMeterY + delta_yp;

    return {
        x: Math.round(xp),
        y: Math.round(yp)
    };
};

/**
 * Calcolo della distanza euclidea
 * @param  {object} begin Nodo d'inizio
 * @param  {object} end   Nodo di fine
 * @return {number}       Distanza euclidea tra i due nodi in metri
 */
Parser.prototype.distance = function (begin, end) {
    var x1 = begin.coordinates.meters.x;
    var x2 = end.coordinates.meters.x;
    var y1 = begin.coordinates.meters.y;
    var y2 = end.coordinates.meters.y;
    var distance = Math.sqrt(Math.pow((x1 - x2), 2) + Math.pow((y1 - y2), 2));
    return Math.round(distance * 100) / 100;
};

/**
 * Calcolo della larghezza del tronco
 * @param  {object} begin Nodo d'inizio
 * @param  {object} end   Nodo di fine
 * @return {number}       Larghezza media del tronco
 */
Parser.prototype.averageTrunkWidth = function (begin, end) {
    var width = (begin.larghezza + end.larghezza) / 2;
    return Math.round(width * 100) / 100;
};

/**
 * Ricerca di un nodo in base al codice dalla lista dei nodi
 * @param  {Array} nodes  Array di nodi
 * @param  {object} filterParams Codice di ricerca
 * @param  {boolean} debug Attivazione del debug
 * @return {object}        Nodo trovato
 */
Parser.prototype.searchNode = function (nodes, filterParams, debug) {
    debug = typeof debug !== "undefined" ? debug : false;

    var searchParams = {
        "codice": filterParams.codice
    };

    var nodesFound = nodes.searchObject(searchParams);
    var found = nodesFound.length > 0;
    var unique = nodesFound.length === 1;

    if (found && !unique) {
        searchParams.coordinates = filterParams.coordinates;
        nodesFound = nodes.searchObject(searchParams);

        found = nodesFound.length > 0;
        unique = nodesFound.length === 1;
    }

    if (found && unique) {
        return nodesFound[0];
    } else {
        if(debug) {
            console.log("Node not found or not unique");
            console.log(filterParams);
        }
        return null;
    }
};

/**
 * Estrazione della lista delle scale
 * @return {Array} Lista delle scale estratte
 */
Parser.prototype.getStairs = function () {
    var data_stairs = this.getXLSSheetData(config.stairsSheet);
    var stairs = data_stairs.map(function (row) {
        if (typeof row[1] === "number" && typeof row[2] === "number") {
            return {
                "node1": row[4],
                "node2": row[8],
                "codice": row[9],
                "lunghezza": row[10],
                "larghezza": row[11]
            };
        } else {
            return null;
        }
    });
    return stairs.filter(function (v) {
        return v !== null;
    });
};

/**
 * Salvataggio di un oggetto serializzandolo in un file
 * @param  {object} obj      Oggetto da salvare
 * @param  {String} filename Path del file di destinazione
 * @return {undefined}
 */
Parser.prototype.save = function (obj, filename) {
    try {
        fs.accessSync(this.dest, fs.F_OK);
    } catch (e) {
        fs.mkdirSync(this.dest);
    }
    // Pretty print del json
    var content = JSON.stringify(obj, null, '  ');
    fs.writeFileSync(filename, content);
};

/**
 * Ciclo principale di parsing
 * @return {undefined}
 */
Parser.prototype.parse = function () {
    var nodes = this.getNodes();
    // this.save(nodes, this.dest + "nodes.json");
    var edges = this.getEdges(nodes);
    this.save(edges, this.dest + "edges.json");
    nodes = this.removeUnlinkedNodes(nodes, edges);
    this.save(nodes, this.dest + "nodes.json");
    var stairs = this.getStairs();
    this.save(stairs, this.dest + "stairs.json");
    // @TODO Rimuovere qui i nodi non collegati
};

/**
 * Pulisce la cartella con i risultati del parsing
 * @return {undefined}
 */
Parser.prototype.clear = function () {
    var $this = this;
    var files = fs.readdirSync(this.dest);
    files.map(function (f) {
        var path = path.normalize($this.dest + f);
        fs.unlink(path);
    });
};

// Esposizione del modulo
module.exports = Parser;
