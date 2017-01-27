
var request = require('request');

var Crawl = function (options) {

    var output = {};
    if (isset(options['cookies'])) {
        var jar = request.jar();
        var coookies = options['cookies']['file'].split(';');
        for (i = 0;i < coookies.length;i++) {
            jar.setCookie(coookies[i], options['url']);
        }
        options['jar'] = jar;
    }
    var connect = request(options, function(error, response, body) {
        if (error) {
            output['error'] = error;
        } else {
            // output = response.toJSON();
            output['statusCode'] = response.statusCode;
            output['body'] = body;
            if (options['dumpHeaders'] == true) {
                output['headers'] = response.headers;
            }
            output['cookies'] = response.headers['set-cookie'];
        }
        console.log(JSON.stringify(output));
    });
};

var prepare = function () {
    var $_ = JSON.parse(process.argv[2]);
    var options = {};
    var headers = {};
    options['url'] = $_['url'];
    options['dumpHeaders'] = $_['dumpHeaders'];
    options['followRedirect'] = false;

    options['method'] = $_['method'];
    if ($_['method'] == 'post') {
        options['form'] = $_['data'];
    }

    if (isset($_['json'])) {
        headers['content-type'] = 'application/json';
        headers['content-length'] = $_['data'].length;
    }

    if (isset($_['xml'])) {
        headers['content-type'] = 'text/xml';
        headers['content-length'] = $_['data'].length;
    }

    if (isset($_['referer'])) {
        headers['referer'] = $_['referer'];
    }

    if (isset($_['proxy'])) {
        options['proxy'] = $_['proxy']['type'] + '://' + $_['proxy']['ip'];
    }

    if (isset($_['headers'])) {
        headers = $_['headers'];
    }
    if (isset($_['cookies'])) {
        options['cookies'] = $_['cookies'];
    }

    options['headers'] = headers;


    return options;
};

var isset = function (parameter) {
    return typeof parameter !== 'undefined' ? parameter : false;
}

Crawl(prepare());
