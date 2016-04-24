var gulp = require("gulp");
var plugins = require("gulp-load-plugins");
var shell = require("gulp-shell");
var gulpsync = require("gulp-sync")(gulp);
var parser = require("./data/parser/parser.js");
var drawer = require("./data/parser/drawer.js");
var authorization_provider = require("./data/loader/authorization-provider");
var DataLoader = require("./data/loader/data-loader");

gulp.task('parse', function () {
    parser.parse();
});

gulp.task('clear', function () {
    parser.clear();
});

gulp.task('draw', ['parse'], function () {
    drawer.drawEdges()
        .drawNodes()
        .write();
});

gulp.task('load', function () {
    authorization_provider.getBearer(function ($this) {
        var loader = new DataLoader($this.accessToken);
        $this.mutex.leave();
        loader.loadData();
    })
});

gulp.task('setup-db', shell.task([
    './bin/console doctrine:schema:drop --dump-sql',
    './bin/console doctrine:schema:drop --force',
    './bin/console doctrine:schema:create',
    './bin/console doctrine:fixtures:load --fixtures=src/Wescape/CoreBundle/DataFixtures/ORM/SetupCredentials.php'
], {
    "cwd": __dirname + "/wescape"
}));

gulp.task('setup', gulpsync.sync([['parse', 'setup-db'], 'load']));

