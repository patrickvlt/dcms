window.DCMS = {};
window.DCMS.config = require('../../../dcms.json');
window.DCMS.csrf = (document.querySelector('meta[name=csrf-token]')) ? document.querySelector('meta[name=csrf-token]').content : null;
window.DCMS.allowNewTab = false;
window.DCMS.language = (typeof dcmsLanguage !== 'undefined') ? dcmsLanguage : 'en';

if (typeof process.env.MIX_DCMS_ENV == 'undefined') {
    console.log('No environment defined for DCMS. Define this in your .env as MIX_DCMS_ENV.');
}

/**
 *
 *  Append CSS or JS files to the browser, from a CDN or locally
 *
 */

window.DCMS.loadJSFile = function (source, type = null) {
    var script = document.createElement('script');
    script.type = (type) ? type : 'text/javascript';
    script.src = source;
    var s = document.getElementsByTagName('script')[0];
    s.parentNode.append(script, s);
};

window.DCMS.loadJS = function (plugin, pluginPath = 'cdn') {
    var scriptSources = (process.env.MIX_DCMS_ENV == 'local' || pluginPath !== 'cdn') ? plugin['local']['js'] : plugin[pluginPath]['js'];
    function loadJSSource(source) {
        var script = document.createElement('script');
        script.type = 'text/javascript';
        script.src = source;
        var s = document.getElementsByTagName('script')[0];
        s.parentNode.append(script, s);
    }
    if (typeof scriptSources == 'string') {
        loadJSSource(scriptSources);
    } else {
        Array.from(scriptSources).forEach(function (source) {
            loadJSSource(source);
        });
    }
};

let cssLink, cssTag;
window.DCMS.loadCSS = function (plugin, pluginPath = 'cdn') {
    var cssSources = (process.env.MIX_DCMS_ENV == 'local' || pluginPath !== 'cdn') ? plugin['local']['css'] : plugin[pluginPath]['css'];
    function loadCSSSource(source) {
        cssLink = document.createElement('link');
        cssLink.rel = 'stylesheet';
        cssLink.type = 'text/css';
        cssLink.href = source;
        cssTag = document.getElementsByTagName('head')[0];
        cssTag.prepend(cssLink);
    }
    if (typeof cssSources == 'string') {
        loadCSSSource(cssSources);
    } else {
        Array.from(cssSources).forEach(function (source) {
            loadCSSSource(source);
        });
    }
};

if (typeof axios == 'undefined') {
    window.DCMS.loadJS(window.DCMS.config.plugins.axios);
}

if (typeof toastr == 'undefined' && (window.DCMS.config.plugins.toastr && window.DCMS.config.plugins.toastr.enable !== false)) {
    window.DCMS.loadCSS(window.DCMS.config.plugins.toastr);
    window.DCMS.loadJS(window.DCMS.config.plugins.toastr);
}

if (typeof Swal == 'undefined' && (window.DCMS.config.plugins.sweetalert2 && window.DCMS.config.plugins.sweetalert2.enable !== false)) {
    window.DCMS.loadCSS(window.DCMS.config.plugins.sweetalert2);
    window.DCMS.loadJS(window.DCMS.config.plugins.sweetalert2);
}

if (typeof Papa == 'undefined' && (window.DCMS.config.plugins.papa && window.DCMS.config.plugins.papa.enable !== false)) {
    window.DCMS.loadJS(window.DCMS.config.plugins.papa, 'local');
}

/**
*
*  Require custom settings such as language, form alerts, window variables
*
*/

require('./_settings.js');

/**
 *
 *  Translations method
 *
 */

try {
    var lang = require('../../../resources/lang/' + window.DCMS.language + '.json');
} catch (error) {
    //
}

window.Lang = function (string) {
    try {
        var langVal;
        langVal = (typeof lang[string] !== 'undefined') ? lang[string] : string;
        return langVal;
    } catch (error) {
        return string;
    }
};

/**
*
*  Custom messages
*
*/

require('./_messages.js');

/**
*
*  Helpers
*
*/

require('./helpers.js');

/**
*
*  Plugins
*
*/

require('./plugins/init.js');
