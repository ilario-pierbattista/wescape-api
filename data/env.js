var path = require("path");
var url = require("url");

module.exports = {
    parsed_data_paths: {
        "nodes": path.normalize(__dirname + "/maps/json/nodes.json"),
        "edges": path.normalize(__dirname + "/maps/json/edges.json"),
        "stairs": path.normalize(__dirname + "/maps/json/stairs.json")
    },
    host_name: "wescape.dev",
    build_url: function (path) {
        return url.format({
            protocol: "http",
            hostname: module.exports.host_name,
            pathname: path
        })
    }
};