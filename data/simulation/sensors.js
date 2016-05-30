'use strict';

var wescapeClient = require('./../wescape-client');
var semaphore = require('semaphore');
var Progress = require('progress');
var strformat = require('strformat');
var sleep = require('sleep');
require('../array-extension');

// Il workflow dovrebbe essere quello di listare tutti i nodi, dare valori casuali ai parametri
// I, V e C dei lati

/**
 * 
 * @param accessToken
 * @constructor
 */
function SensorsSimulator(accessToken) {
    var $this = this;

    $this.client = new wescapeClient.constructor(accessToken);
    $this.endpoints = wescapeClient.endpoints;
    $this.mutex = semaphore(1);
    $this.synchronousSemaphore = semaphore(5);
}

/**
 * Aggiorna tutti gli archi
 */
SensorsSimulator.prototype.updateAllEdges = function () {
    var $this = this;
    var edges = [];

    $this.mutex.take(function () {
        $this.client.get($this.endpoints.getEdges)
            .on('complete', function (data) {
                edges = data;
                $this.loadingProgress = new Progress("loading sensors data :bar :current/:total",
                    {total: edges.length});
                $this.mutex.leave();
            });
    });

    $this.mutex.take(function () {
        edges.map(function (edge) {
            $this.synchronousSemaphore.take($this.updateEdgeFunc($this, edge));
        });
    });
};

/**
 * 
 * @param $this
 * @param edge
 * @returns {Function}
 */
SensorsSimulator.prototype.updateEdgeFunc = function ($this, edge) {
    return function () {
        edge = $this.injectRandomParams(edge);

        $this.client.put(strformat($this.endpoints.put_edge, {id: edge.id}), {
            data: edge
        }).on('complete', function (result) {
            $this.loadingProgress.tick();
            $this.synchronousSemaphore.leave();
            if ($this.loadingProgress.complete) {
                $this.mutex.leave();
            }
        });
    };
};

/**
 * 
 * @param edge
 * @returns {*}
 */
SensorsSimulator.prototype.injectRandomParams = function (edge) {
    var vField = [0.0, 0.33, 0.67, 1];
    var iField = [0, 1];

    edge.v = vField.randomSelect();
    edge.i = iField.randomSelect();
    edge.c = Math.random();

    edge.begin = edge.begin.id;
    edge.end = edge.end.id;

    return edge;
};

/**
 *
 * @param seconds
 */
SensorsSimulator.prototype.continuousSimulation = function (seconds) {
    var $this = this;
    while(1) {
        console.log("Loading sensor data on " + (new Date()));
        $this.updateAllEdges();
        sleep.sleep(seconds);
    }
};

module.exports = SensorsSimulator;