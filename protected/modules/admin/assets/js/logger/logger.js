(function() {
    'use strict';

    // fix IE
    var noop = function(){};
    window.console = window.console || {log: noop, warn: noop, error: noop, info: noop};

    window.onerror = function(message, source, lineno, colno, error) {
        if (window.debug) {
            return false;
        }

        error = error || {stack: ''};

        var Request = window.XMLHttpRequest;
        if (typeof Request == 'undefined') {
            Request = function(){
                return new window.ActiveXObject(
                    navigator.userAgent.indexOf('MSIE 5') >= 0 ?
                        'Microsoft.XMLHTTP' : 'Msxml2.XMLHTTP'
                );
            };
        }

        var request = new Request();
        request.open('POST', '/site/jsError', true);
        request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        request.send('error='+message+'&source='+source+'&line='+lineno+'&col='+colno+'&stack='+encodeURIComponent(error.stack));
    };
}());
