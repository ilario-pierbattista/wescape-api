'use strict';

/**
 * Ricerca di oggetti tramite parametri
 * 
 * @param filterParams
 * @returns {Array.<T>}
 */
Array.prototype.searchObject = function (filterParams) {
    return this.filter(function (v) {
        return recursiveMatchFunc(v, filterParams);
    });
};

/**
 * Ritorna un elemento casuale dell'array
 * @returns {*}
 */
Array.prototype.randomSelect = function () {
    return this[Math.floor(Math.random() * this.length)];
};

/**
 * Match ricorsivo
 * @param data
 * @param params
 * @returns {boolean}
 */
function recursiveMatchFunc(data, params) {
    var match = true;
    if (typeof params == "object" && typeof data == "object") {
        Object.keys(params).map(function (label) {
            if (data.hasOwnProperty(label)) {
                match = match && recursiveMatchFunc(data[label], params[label]);
            } else {
                match = false;
            }
        });
    } else {
        return data == params;
    }
    return match;
}