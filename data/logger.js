var path = require("path");
var fs = require("fs");

const LOG_PATH = path.normalize(__dirname + "/../logs/gulp/");

module.exports = {
    getFilePath: function(filename) {
        return path.normalize(LOG_PATH + filename)
    },
    saveLog: function (logName, content) {
        try {
            fs.accessSync(LOG_PATH, fs.F_OK);
        } catch (err) {
            fs.mkdirSync(LOG_PATH);
        }

        var fd = fs.openSync(module.exports.getFilePath(logName), 'w');
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
    },
    readLog: function (logName) {
        try {
            fs.accessSync(LOG_PATH, fs.F_OK);
        } catch (err) {
            return null;
        }

        try {
            fs.accessSync(module.exports.getFilePath(logName), fs.F_OK);
            return require(module.exports.getFilePath(logName));
        } catch (err) {
            return null;
        }
    }
};