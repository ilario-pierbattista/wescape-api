var xlsx = require('node-xlsx');
var fs = require('fs');

const DATAPATH = __dirname + "/../maps/data.xlsx";
const DESTPATH = __dirname + "/../maps/json/"
const NODES_SHEET = "elenco nodi";
const EDGES_SHEET = "vie di piano";
const STAIRS_SHEET = "scale";

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
Parser.prototype.get_sheet_data = function(sheet_name) {
    var obj = this.parseData.filter(function(value) {
        return value.name == sheet_name;
    });
    return obj[0].data;
}

/**
* Estrazione della lista di nodi dal foglio excel
* @return {Array} Array di nodi
*/
Parser.prototype.get_nodes = function() {
    var $this = this;
    var data_nodes = this.get_sheet_data(NODES_SHEET);
    // Formattazione dei nodi @TODO parsare anche gli altri parametri
    var nodes = data_nodes.map(function(row) {
        if(typeof row[1] == "number" && typeof row[2] == "number") {
            var node = {
                "coordinates": {
                    "meters": {
                        "x": row[1],
                        "y": row[2]
                    }
                },
                "quota": row[3],
                "larghezza": row[4],
                "codice": row[5],
                "desc": row[6],
            };
            node["type"] = $this._get_node_type_description(node);
            return node;
        } else {
            return null;
        }
    });
    // Eliminazione delle voci nulle
    nodes = nodes.filter(function(value) {
        return value != null;
    })
    return nodes;
}

/**
* Estrazione della lista degli archi dal foglio excel
* @param  {Array} nodes Lista dei nodi che devono essere collegati
* @return {Array}       Lista degli archi estratti
*/
Parser.prototype.get_edges = function(nodes) {
    var data_edges = this.get_sheet_data(EDGES_SHEET);
    var $this = this;
    var edges = data_edges.map(function(row) {
        if(typeof row[0] == "number" && typeof row[1] == "number") {
            var edge = {
                "node1": row[4],
                "node2": row[9]
            };
            var node1 = $this._find_node(nodes, edge.node1);
            var node2 = $this._find_node(nodes, edge.node2);
            edge["lunghezza"] = $this._distance(node1, node2);
            edge["larghezza"] = $this._trunk_width(node1, node2);
            return edge;
        } else {
            return null;
        }
    });
    return edges.filter(function(value) {
        return value != null;
    })
}

/**
 * Descrive i nodi
 * @param  {object} node Oggetto nodo da descrivere
 * @return {object}      Oggetto descrizione del nodo
 */
Parser.prototype._get_node_type_description = function(node) {
    return {
        aula: /.*R.*/.test(node.codice),
        uscita: /.*U.*/.test(node.codice),
        uscita_emergenza: /.*EM.*/.test(node.codice)
    };
}

/**
* Calcolo della distanza euclidea
* @param  {object} begin Nodo d'inizio
* @param  {object} end   Nodo di fine
* @return {number}       Distanza euclidea tra i due nodi in metri
*/
Parser.prototype._distance = function(begin, end) {
    var x1 = begin.coordinates.meters.x;
    var x2 = end.coordinates.meters.x;
    var y1 = begin.coordinates.meters.y;
    var y2 = end.coordinates.meters.y;
    var distance = Math.sqrt( Math.pow((x1 - x2), 2) + Math.pow((y1 - y2), 2) );
    return Math.round(distance * 100) / 100;
}

/**
 * Calcolo della larghezza del tronco
 * @param  {object} begin Nodo d'inizio
 * @param  {object} end   Nodo di fine
 * @return {number}       Larghezza media del tronco
 */
Parser.prototype._trunk_width = function(begin, end) {
    var width = (begin.larghezza + end.larghezza) / 2;
    return Math.round(width * 100) / 100;
}

/**
* Ricerca di un nodo in base al codice dalla lista dei nodi
* @param  {Array} nodes  Array di nodi
* @param  {string} codice Codice di ricerca
* @return {object}        Nodo trovato
*/
Parser.prototype._find_node = function(nodes, codice) {
    var founds = nodes.filter(function(v) {
        return v.codice == codice;
    });
    if (founds.length > 0) {
        return founds[0];
    } else {
        return null;
    }
}

/**
* Estrazione della lista delle scale
* @return {Array} Lista delle scale estratte
*/
Parser.prototype.get_stairs = function() {
    var data_stairs = this.get_sheet_data(STAIRS_SHEET);
    var stairs = data_stairs.map(function(row) {
        if(typeof row[1] == "number" && typeof row[2] == "number") {
            return {
                "node1": row[4],
                "node2": row[8],
                "codice": row[9],
                "lunghezza": row[10],
                "larghezza": row[11]
            }
        } else {
            return null;
        }
    });
    return stairs.filter(function(v) {
        return v != null;
    })
}

/**
* Salvataggio di un oggetto serializzandolo in un file
* @param  {obejct} obj      Oggetto da salvare
* @param  {String} filename Path del file di destinazione
* @return {undefined}
*/
Parser.prototype.save = function(obj, filename) {
    try {
        fs.accessSync(this.dest, fs.F_OK);
    } catch(e) {
        fs.mkdirSync(this.dest);
    }
    // Pretty print del json
    var content = JSON.stringify(obj, null, '\t');
    fs.writeFileSync(filename, content);
}

/**
* Ciclo principale di parsing
* @return {undefined}
*/
Parser.prototype.parse = function() {
    var nodes = this.get_nodes();
    this.save(nodes, this.dest + "nodes.json");
    var edges = this.get_edges(nodes);
    this.save(edges, this.dest + "edges.json");
    var stairs = this.get_stairs();
    this.save(stairs, this.dest + "stairs.json");
}

/**
 * Pulisce la cartella con i risultati del parsing
 * @return {undefined}
 */
Parser.prototype.clear = function() {
    var files = fs.readdirSync(this.dest);
    var $this = this;
    files.map(function(f) {
        var path = $this.dest + f;
        fs.unlink(path);
    });
}

// Esposizione del modulo
module.exports = new Parser(DATAPATH, DESTPATH);
