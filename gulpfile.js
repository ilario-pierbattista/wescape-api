var gulp = require("gulp");
var plugins = require("gulp-load-plugins");
var parser = require("./data/parser/parser.js");
var drawer = require("./data/parser/drawer.js");
var loader = require("./data/loader/data-loader");

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

gulp.task('clear-db', function () {
    loader.clearData();
});

gulp.task('load', ['parse', 'clear-db'], function() {
    loader.loadData();
});
