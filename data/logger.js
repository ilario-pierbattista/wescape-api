var path = require("path");
var fs = require("fs");

const LOG_PATH = path.normalize(__dirname + "/../logs/gulp/");

module.exports = {
    saveLog: function (logName, content) {
        var filePath = path.normalize(LOG_PATH + logName);

        try {
            fs.accessSync(LOG_PATH, fs.F_OK);
        } catch (err) {
            fs.mkdirSync(LOG_PATH);
        }

        var fd = fs.openSync(filePath, 'w');
        var cache = [];
        fs.writeSync(fd, JSON.stringify(content, function (key, value) {
            if (typeof value === 'object' && value !== null) {
                if (cache.indexOf(value) !== -1) {
                    // Circular reference found, discard key
                    return;
                }
                // Store value in our collection
                cache.push(value);
            }
            return value;
        }, "\t"));
    }
};