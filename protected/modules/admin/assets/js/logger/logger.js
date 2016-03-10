(function() {
    'use strict';

    // fix IE
    var noop = function(){};
    window.console = window.console || {log: noop, warn: noop, error: noop, info: noop};

    window.onerror = function(message, source, lineno, colno, error) {
        if (typeof message !== 'string') {
            var messageString = '';
            for (var x in message) {
                if (messageString) messageString += ', ';
                messageString += x + ': ' + message[x];
            }
            message = 'Unexpected error message type: {' + messageString + '}';

            source = source || '';
            lineno = lineno || 0;
        }

        if (window.debug) {
            return false;
        }

        colno = colno || 0;
        error = error || {stack: ''};

        var request = new XMLHttpRequest();
        request.open('POST', '/site/jsError', true);
        request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        request.send([
            'error='+encodeURIComponent(message),
            'source='+encodeURIComponent(source),
            'line='+encodeURIComponent(lineno),
            'col='+encodeURIComponent(colno),
            'stack='+encodeURIComponent(error.stack),
            'location='+encodeURIComponent(window.location.href)
        ].join('&'));
    };
}());
