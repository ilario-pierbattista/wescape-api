var path = require("path");
var url = require("url");

module.exports = {
    data_src: path.normalize(__dirname + "/maps/data.xlsx"),
    data_dst: path.normalize(__dirname + "/maps/json/"),
    parsed_data_paths: {
        "nodes": path.normalize(__dirname + "/maps/json/nodes.json"),
        "edges": path.normalize(__dirname + "/maps/json/edges.json"),
        "stairs": path.normalize(__dirname + "/maps/json/stairs.json")
    },
    images_src_path: path.normalize(__dirname + "/../maps/"),
    images_dst_path: path.normalize(__dirname + "/../maps/graph/"),
    host_name: "wescape.dev",
    dev_path_prefix: "app_dev.php/",
    environment: "dev",
    client_id: "1_d9d9322ad1a46e889e8102aa9072ea2fc87b525652a114b335d21542cc528bee",
    client_secret: "7e1be901e9439a0176072e9277dbf04dd606b31054226eccbce1b9f611a81fcba",

    build_url: function (path) {
        if(module.exports.environment = "dev") {
            path = module.exports.dev_path_prefix + path;
        }
        return url.format({
            protocol: "http",
            hostname: module.exports.host_name,
            pathname: path
        })
    }
};