/**
 *
 *  Import plugins
 *
 */

window.DCMS = {};
window.DCMS.config = require('../../../dcms.json');
window.DCMS.AllowNewTab = false;
window.DCMS.language = (typeof dcmsLanguage !== 'undefined') ? dcmsLanguage : 'en';

if (typeof process.env.MIX_DCMS_ENV == 'undefined') {
    console.log('No environment defined for DCMS. Define this in your .env as MIX_DCMS_ENV.');
}

/**
 *
 *  Append CSS or JS files to the browser, from a CDN or locally
 *
 */

window.DCMS.loadJS = function (plugin, pluginPath = 'cdn') {
    var scriptSources = (process.env.MIX_DCMS_ENV == 'local' || pluginPath !== 'cdn') ? plugin['local']['js'] : plugin[pluginPath]['js'];
    function loadJSSource(source) {
        var script = document.createElement('script');
        script.type = 'text/javascript';
        script.src = source;
        var s = document.getElementsByTagName('script')[0];
        s.parentNode.insertBefore(script, s);
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
        cssTag.append(cssLink);
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

if (typeof Vue == 'undefined' && (window.DCMS.config.plugins.vue && window.DCMS.config.plugins.vue.enable !== false)) {
    window.DCMS.loadJS(window.DCMS.config.plugins.vue);
}

if (typeof toastr == 'undefined' && (window.DCMS.config.plugins.toastr && window.DCMS.config.plugins.toastr.enable !== false)) {
    window.DCMS.loadCSS(window.DCMS.config.plugins.toastr);
    window.DCMS.loadJS(window.DCMS.config.plugins.toastr);
}

if (typeof Papa == 'undefined' && (window.DCMS.config.plugins.papa && window.DCMS.config.plugins.papa.enable !== false)) {
    window.DCMS.loadJS(window.DCMS.config.plugins.papa, 'local');
}

if (document.querySelectorAll('.datatable').length > 0 && (window.DCMS.config.plugins.KTDatatable && window.DCMS.config.plugins.KTDatatable.enable !== false)) {
    window.DCMS.loadJS(window.DCMS.config.plugins.KTDatatable, 'local');
    window.DCMS.loadCSS(window.DCMS.config.plugins.KTDatatable, 'local');
}

/**
*
*  Require custom settings such as language, form alerts, window variables
*
*/

require('./_settings.js');

/**
 *
 *  Translations
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

require('./plugins/sweetalert2.js');
require('./plugins/requests.js');
require('./plugins/carousel.js');
require('./plugins/slimselect.js');
require('./plugins/tinymce.js');
require('./plugins/editor.js');
require('./plugins/datetimepicker.js');
require('./plugins/jexcel.js');
require('./plugins/filepond.js');
require('./metronic/dcmsdatatable.js');
require('../../../public/js/dcms/assets/spotlight.js');