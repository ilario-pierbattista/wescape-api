var gulp = require("gulp");
var plugins = require("gulp-load-plugins");
var parser = require("./parser.js");

gulp.task('parse', function() {
    parser.parse();
});
