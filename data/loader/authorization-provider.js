var restler = require("restler");
var prompt = require("prompt");
var exit = require("exit");
var env = require("../env");
var logger = require("../logger");

const LOG_FILE = "authentication-provider.log";
const TOKEN_FILE = "authentication_tokens.json";

WescapeTokenRequester = restler.service(function () {
    this.defaults.headers = {'Content-Type': 'application/json'}
});

/**
 * Autenticatore
 * @param clientId
 * @param clientSecret
 * @constructor
 */
function AuthorizationProvider(clientId, clientSecret) {
    this.client_id = clientId;
    this.client_secret = clientSecret;
    this.username = null;
    this.password = null;
    this.tokenEndpoint = env.build_url("oauth/v2/token");
    this.accessToken = null;
    this.refreshToken = null;
    this.tokenExpiration = null;
    this.mutex = require("semaphore")(1);
}

/**
 * Richiede le credenziali all'utente
 * @param callback
 */
AuthorizationProvider.prototype.authenticate = function (callback) {
    var $this = this;

    setTimeout(function () {
        prompt.start();
        prompt.get(['username', {
            name: 'password',
            hidden: true
        }], function (err, result) {
            if (err) {
                console.log(err);
                exit(1);
            }
            $this.requestTokens({
                grant_type: "password",
                username: result.username,
                password: result.password
            }, callback);
        })
    }, 500);
};

/**
 * Richiede i token di accesso
 * @param credentials
 * @param callback
 */
AuthorizationProvider.prototype.requestTokens = function (credentials, callback) {
    var $this = this;
    credentials['client_id'] = $this.client_id;
    credentials['client_secret'] = $this.client_secret;
    restler.post(this.tokenEndpoint, {
            data: credentials
        })
        .on("complete", function (result) {
            $this.accessToken = result['access_token'];
            $this.refreshToken = result['refresh_token'];
            $this.tokenExpiration = Date.now() + result['expires_in'];
            $this.saveTokens();
            callback();
        })
        .on("fail", function (data, response) {
            logger.saveLog(LOG_FILE, {
                "data": data,
                "response": response
            })
        });
};

/**
 * Salva i token in un file
 */
AuthorizationProvider.prototype.saveTokens = function () {
    var $this = this;
    logger.saveLog(TOKEN_FILE, {
        "accessToken": $this.accessToken,
        "refreshToken": $this.refreshToken,
        "tokenExpiration": $this.tokenExpiration
    });
};

/**
 * Pulisce i token salvati
 */
AuthorizationProvider.prototype.clearTokens = function () {
    logger.deleteLog(TOKEN_FILE);
};

/**
 * Estrae l'access token
 * @param callback
 */
AuthorizationProvider.prototype.getBearer = function (callback) {
    var $this = this;

    $this.mutex.take(function () {
        var savedTokens = logger.readLog(TOKEN_FILE);

        if(savedTokens == null || Object.keys(savedTokens).length == 0) {
            // Token non presente
            $this.authenticate(function () {
                $this.mutex.leave();
            });
        } else if (Date.now() >= savedTokens['tokenExpiration']) {
            // Token expired
            $this.requestTokens({
                grant_type: "refresh_token",
                refresh_token: savedTokens['refreshToken']
            }, function () {
                $this.mutex.leave();
            })
        } else {
            // Token valido
            $this.accessToken = savedTokens['accessToken'];
            $this.refreshToken = savedTokens['refreshToken'];
            $this.tokenExpiration = savedTokens['tokenExpiration'];
            $this.mutex.leave();
        }
    });

    $this.mutex.take(function () {
        callback($this)
    })
};

module.exports = new AuthorizationProvider(env.client_id, env.client_secret);