var gulp = require("gulp");
var plugins = require("gulp-load-plugins");
var shell = require("gulp-shell");
var gulpsync = require("gulp-sync")(gulp);

var Parser = require("./data/parser/parser.js");
var Drawer = require("./data/parser/drawer.js");
var DataLoader = require("./data/loader/data-loader");
var env = require("./data/env");
var authorization_provider = require("./data/loader/authorization-provider");

/**
 * Parsing dei dati
 */
gulp.task('parse', function () {
    var parser = new Parser(env.data_src, env.data_dst);
    parser.parse();
});

/**
 * Pulizia dei dati parsati
 */
gulp.task('clear', function () {
    var parser = new Parser(env.data_src, env.data_dst);
    parser.clear();
});

/**
 * Disegno delle mappe con i punti parsati (debugging)
 */
gulp.task('draw', ['parse'], function () {
    var drawer = new Drawer(env.parsed_data_paths,
        env.images_src_path,
        env.images_dst_path);
    drawer.drawEdges()
        .drawNodes()
        .write();
});

/**
 * Caricamento sul database dei dati parsati
 */
gulp.task('load', function () {
    authorization_provider.getBearer(function ($this) {
        var loader = new DataLoader($this.accessToken);
        $this.mutex.leave();
        loader.clearData();
        loader.loadData();
    });
});

/**
 * Setup iniziale del database
 */
gulp.task('setup-db', shell.task([
    './bin/console doctrine:schema:drop --dump-sql',
    './bin/console doctrine:schema:drop --force',
    './bin/console doctrine:schema:create',
    './bin/console doctrine:fixtures:load --fixtures=src/Wescape/CoreBundle/DataFixtures/ORM/SetupCredentials.php'
], {
    "cwd": __dirname + "/wescape"
}));

/**
 * Pulizia dei token salvati
 */
gulp.task('clear-oauth', function () {
    authorization_provider.clearTokens();
});

/**
 * Setup complessivo del database
 */
gulp.task('default', gulpsync.sync([
    ['parse', 'setup-db', 'clear-oauth'],
    'load'
]));

