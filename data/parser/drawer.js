var fs = require('fs');
var gm = require('gm').subClass({imageMagick: true});
var parser = require('./parser.js');
var path = require('path');

const NODES_COLOR = "#E53A40";
const EDGES_COLOR = "#30A9DE";
const TEXT_COLOR = "#000000";

/**
 * Costruttore
 * @param {object} dataSrc Paths dei file con i dati
 * @param {string} imgSrc  Cartella delle immagini delle mappe
 * @param {string} imgDest Cartella di destinazione delle immagini
 */
function Drawer(dataSrc, imgSrc, imgDest) {
    this.dataSrc = dataSrc;
    this.imgSrc = imgSrc;
    this.imgDest = imgDest;
    this.openedImages = {};
    var $this = this;

    $this.data = {};
    Object.keys(this.dataSrc).map(function(label) {
        $this.data[label] = $this._readJson($this.dataSrc[label]);
    });
}

/**
 * Disegna i nodi sulla mappa
 * @return {object} Istanza corrente di Drawer
 */
Drawer.prototype.drawNodes = function() {
    var $this = this;

    this.data.nodes.map(function(node) {
        $this._draw_node(node);
    });

    return this;
};

/**
 * Disegna gli archi sulla mappa
 * @return {object} Istanza corrente di Drawer
 */
Drawer.prototype.drawEdges = function() {
    var $this = this;

    this.data.edges.map(function(edge) {
        $this._draw_edge(edge);
    });

    return this;
};

/**
 * Legge un oggetto serializzato in json da un file
 * @param  {string} path Percorso del file
 * @return {undefined}
 */
Drawer.prototype._readJson = function(path) {
    try {
        var content = fs.readFileSync(path);
        return JSON.parse(content);
    } catch (e) {
        console.log(e);
    }
};

/**
 * Disegna sull'immagine il pallino con il codice del nodo
 * @param  {object} node Oggetto nodo
 * @return {object}      Istanza corrente di Drawer
 */
Drawer.prototype._draw_node = function(node) {
    var imageGm = this._open_image(node.quota + ".jpg");
    var x = node.coordinates.pixel.x;
    var y = node.coordinates.pixel.y;

    imageGm.stroke(NODES_COLOR).fill(NODES_COLOR)
    .drawCircle(x, y, x+3, y)
    .stroke(TEXT_COLOR).fill(TEXT_COLOR)
    .drawText(x-10, y-6, node.codice);
    return this;
};

/**
 * Disegna un arco
 * @param  {object} edge Oggetto arco
 * @return {object}      Istanza corrente di Drawer
 */
Drawer.prototype._draw_edge = function(edge) {
    var node1 = parser._find_node(this.data.nodes, edge.node1);
    var node2 = parser._find_node(this.data.nodes, edge.node2);
    var imageGm = this._open_image(node1.quota + ".jpg");
    var x1 = node1.coordinates.pixel.x;
    var y1 = node1.coordinates.pixel.y;
    var x2 = node2.coordinates.pixel.x;
    var y2 = node2.coordinates.pixel.y;

    imageGm.stroke(EDGES_COLOR).fill(EDGES_COLOR)
    .drawLine(x1, y1, x2, y2);
    return this;
};

/**
 * Apre un'immagine
 * @param  {string} filename Nome del file immagine
 * @return {object}          Istanza di gm relativa all'immagine
 */
Drawer.prototype._open_image = function(filename) {
    if(! this.openedImages.hasOwnProperty(filename)) {
        this.openedImages[filename] = gm(this.imgSrc + filename);
    }
    return this.openedImages[filename];
};

/**
 * Salva tutte le immagini aperte
 * @return {Object} Istanza di Drawer
 */
Drawer.prototype.write = function() {
    var $this = this;

    try {
        fs.accessSync(this.imgDest, fs.F_OK);
    } catch(e) {
        fs.mkdirSync(this.imgDest);
    }

    Object.keys(this.openedImages).map(function(filename) {
        $this.openedImages[filename].write($this.imgDest + filename, function(err) {
            if(err) {
                console.log(err);
            }
        });
        delete $this.openedImages[filename];
    });
    return this;
};

// Esposizione del modulo
module.exports = Drawer;
