var restler = require("restler");
var prompt = require("prompt");
var exit = require("exit");
var env = require("../env");
var logger = require("../logger");

const CLIENT_ID = "1_d9d9322ad1a46e889e8102aa9072ea2fc87b525652a114b335d21542cc528bee";
const CLIENT_SECRET = "7e1be901e9439a0176072e9277dbf04dd606b31054226eccbce1b9f611a81fcb";
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
    this.tokenEndpoint = env.build_url("/oauth/v2/token");
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
            $this.requestTokens(result.username, result.password, callback);
        })
    }, 500);
};

/**
 * Richiede i token di accesso
 * @param username
 * @param password
 * @param callback
 */
AuthorizationProvider.prototype.requestTokens = function (username, password, callback) {
    var $this = this;
    restler.post(this.tokenEndpoint, {
            data: {
                grant_type: "password",
                client_id: $this.client_id,
                client_secret: $this.client_secret,
                username: username,
                password: password
            }
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
 * Estrae l'access token
 * @param callback
 */
AuthorizationProvider.prototype.getBearer = function (callback) {
    var $this = this;

    $this.mutex.take(function () {
        $this.authenticate(function () {
            $this.mutex.leave();
        });
    });

    $this.mutex.take(function () {
        callback($this)
    })
};

module.exports = new AuthorizationProvider(CLIENT_ID, CLIENT_SECRET);