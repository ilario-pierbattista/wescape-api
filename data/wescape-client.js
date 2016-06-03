'use strict';

var env = require('./env');
var restler = require('restler');

module.exports = {
    
    constructor: restler.service(function (accessToken) {
        this.defaults.headers = {
            "Content-Type": 'application/json',
            "Authorization": 'Bearer ' + accessToken
        };
    }),

    endpoints: {
        "get_node": env.build_url("api/v1/nodes/{id}.json"),
        "getNodes": env.build_url("api/v1/nodes.json"),
        "post_node": env.build_url("api/v1/nodes.json"),
        "delete_node": env.build_url("api/v1/nodes/{id}.json"),
        "get_edge": env.build_url("api/v1/edges/{id}.json"),
        "getEdges": env.build_url("api/v1/edges.json"),
        "post_edge": env.build_url("api/v1/edges.json"),
        "put_edge": env.build_url("api/v1/edges/{id}.json"),
        "delete_edge": env.build_url("api/v1/edges/{id}.json"),
        "trigger_emergency": env.build_url("api/v1/emergency.json")
    }
};