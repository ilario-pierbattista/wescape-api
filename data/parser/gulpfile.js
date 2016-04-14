var gulp = require("gulp");
var plugins = require("gulp-load-plugins");
var parser = require("./parser.js");
var drawer = require("./drawer.js");

gulp.task('parse', function() {
    parser.parse();
});

gulp.task('clear', function() {
    parser.clear();
});

gulp.task('draw', ['parse'], function() {
    drawer.drawEdges()
    .drawNodes()
    .write();
});
