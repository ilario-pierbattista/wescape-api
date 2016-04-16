var path = require("path");

module.exports = {
    parsed_data_paths: {
        "nodes": path.normalize(__dirname + "/maps/json/nodes.json"),
        "edges": path.normalize(__dirname + "/maps/json/edges.json"),
        "stairs": path.normalize(__dirname + "/maps/json/stairs.json")
    },
    host_name: "wescape.dev"
};