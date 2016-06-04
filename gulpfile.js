'use strict';

var gulp = require("gulp");
var plugins = require("gulp-load-plugins")({lazy: false});
var shell = require("gulp-shell");
var gulpsync = require("gulp-sync")(gulp);

var Parser = require("./data/parser/parser.js");
var Drawer = require("./data/parser/drawer.js");
var DataLoader = require("./data/loader/data-loader");
var env = require("./data/env");
var authorization_provider = require("./data/loader/authorization-provider");
var SensorsSimulator = require('./data/simulation/sensors');

var config = {
    path: {
        public: "wescape/web/assets",
        frontendAssets: "wescape/app/Resources/assets",
        bower: "wescape/vendor/bower_components"
    }
};

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
 * Aggiornamento con valori casuali di tutti i nodi
 */
gulp.task('sensors-simulate', function () {
    authorization_provider.getBearer(function ($this) {
        var simulator = new SensorsSimulator($this.accessToken);
        $this.mutex.leave();
        simulator.updateAllEdges();
    });
});

/**
 * Simulazione continua ogni 60 secondi
 * @TODO Aggiungere altri eventi per la simulazione, come l'attivazione dell'emergenza
 */
gulp.task('simulation', function () {
    authorization_provider.getBearer(function ($this) {
        var simulator = new SensorsSimulator($this.accessToken);
        $this.mutex.leave();
        simulator.continuousSimulation(60);
    });
});

/**
 * Attiva la condizione di emergenza
 */
gulp.task('emergency', function () {
    authorization_provider.getBearer(function (authorizer) {
        var trigger = new SensorsSimulator(authorizer.accessToken);
        authorizer.mutex.leave();
        trigger.triggerEmergency();
    });
});

/**
 * Setup complessivo del database
 */
gulp.task('default', gulpsync.sync([
    ['parse', 'setup-db', 'clear-oauth'],
    'load'
]));

gulp.task('styles', function () {
    gulp.src([
            config.path.bower + "/bootstrap/dist/css/bootstrap.min.css",
            config.path.frontendAssets + "/scss/**/*.scss"
        ])
        .pipe(plugins.debug())
        .pipe(plugins.sass())
        .pipe(plugins.concat("index.css"))
        .pipe(gulp.dest(config.path.public + "/css"))
});

gulp.task('images', function () {
    gulp.src(config.path.frontendAssets + "/images/**")
        .pipe(gulp.dest(config.path.public + "/images"))
});

gulp.task('watch', function () {
    gulp.watch(config.path.frontendAssets + "/scss/**/*.scss", ['styles']);
});


