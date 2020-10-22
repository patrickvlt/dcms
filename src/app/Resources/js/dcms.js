/**
 *
 *  Import plugins which are not defined in the current project
 *
 */

var dcmsConfig;
try {
    dcmsConfig = require('../../../dcms.json');
} catch (error){
    dcmsConfig = {};
}

if (typeof process.env.MIX_DCMS_ENV == 'undefined'){
    console.log('No environment defined for DCMS. Define this in your .env as MIX_DCMS_ENV.')
}

window.LoadJS = function(pluginFunction, plugin, pluginPath='cdn') {
    var scriptSources = (process.env.MIX_DCMS_ENV == 'local' || pluginPath !== 'cdn') ? plugin['local']['js'] : plugin[pluginPath]['js'];
    function LoadJSSource(source){
        var script = document.createElement('script');
        script.type = 'text/javascript';
        script.src = source;
        var s = document.getElementsByTagName('script')[0];
        s.parentNode.insertBefore(script, s);
    }
    if (typeof scriptSources == 'string'){
        LoadJSSource(scriptSources);
    } else {
        Array.from(scriptSources).forEach(function(source){
            LoadJSSource(source);
        });
    }
}

let cssLink, cssTag;
window.LoadCSS = function(plugin,pluginPath='cdn') {
    var cssSources = (process.env.MIX_DCMS_ENV == 'local' || pluginPath !== 'cdn') ? plugin['local']['css'] : plugin[pluginPath]['css'];
    function LoadCSSSource(source){
        cssLink = document.createElement('link');
        cssLink.rel = 'stylesheet';
        cssLink.type = 'text/css';
        cssLink.href = source;
        cssTag = document.getElementsByTagName('head')[0];
        cssTag.append(cssLink);
    }
    if (typeof cssSources == 'string'){
        LoadCSSSource(cssSources);
    } else {
        Array.from(cssSources).forEach(function(source){
            LoadCSSSource(source);
        });
    }
}

if (dcmsConfig.plugins.axios && dcmsConfig.plugins.axios.enable !== false){
    LoadJS('axios',dcmsConfig.plugins.axios);
}

if (dcmsConfig.plugins.vue && dcmsConfig.plugins.vue.enable !== false){
    LoadJS('Vue',dcmsConfig.plugins.vue);
}

if (dcmsConfig.plugins.sweetalert2 && dcmsConfig.plugins.sweetalert2.enable !== false){
    LoadJS('Swal',dcmsConfig.plugins.sweetalert2);
}

if (dcmsConfig.plugins.toastr && dcmsConfig.plugins.toastr.enable !== false){
    LoadCSS(dcmsConfig.plugins.toastr);
    LoadJS('toastr',dcmsConfig.plugins.toastr);
}

if (document.querySelectorAll('[data-type=slimselect]').length > 0 && (dcmsConfig.plugins.slimselect && dcmsConfig.plugins.slimselect.enable !== false)){
    LoadCSS(dcmsConfig.plugins.slimselect);
    LoadJS('SlimSelect',dcmsConfig.plugins.slimselect);
}

if (document.querySelectorAll('[data-type=datepicker]').length > 0 && (dcmsConfig.plugins.datepicker && dcmsConfig.plugins.datepicker.enable !== false)){
    LoadCSS(dcmsConfig.plugins.datepicker);
    LoadJS('datepicker',dcmsConfig.plugins.datepicker);
}

if (document.querySelectorAll('[data-type=clockpicker]').length > 0 && (dcmsConfig.plugins.clockpicker && dcmsConfig.plugins.clockpicker !== false)){
    LoadCSS(dcmsConfig.plugins.clockpicker);
    LoadJS('clockpicker',dcmsConfig.plugins.clockpicker);
}

if (document.querySelectorAll('[data-type=jexcel]').length > 0 && (dcmsConfig.plugins.jexcel && dcmsConfig.plugins.jexcel !== false)){
    LoadCSS(dcmsConfig.plugins.jexcel);
    LoadJS('jexcel',dcmsConfig.plugins.jexcel);
}
if (document.querySelectorAll('[data-type=jexcel]').length > 0 && (dcmsConfig.plugins.jsuites && dcmsConfig.plugins.jsuites !== false)){
    LoadCSS(dcmsConfig.plugins.jsuites);
    LoadJS('jsuites',dcmsConfig.plugins.jsuites);
}

if (dcmsConfig.plugins.papa && dcmsConfig.plugins.papa !== false){
    LoadJS('Papa',dcmsConfig.plugins.papa,'local');
}

if (document.querySelectorAll('[data-type=filepond]').length > 0 && (dcmsConfig.plugins.filepond && dcmsConfig.plugins.filepond !== false)){
    LoadCSS(dcmsConfig.plugins.filepond);
    LoadJS('FilePond',dcmsConfig.plugins.filepond);
    LoadCSS(dcmsConfig.plugins.filepondImagePreview);
    LoadJS('FilePondPluginImagePreview',dcmsConfig.plugins.filepondImagePreview);
    LoadJS('FilePondPluginFileValidateSize',dcmsConfig.plugins.filepondValidateSize);
}

if (document.querySelectorAll('[data-type=tinymce]').length > 0 && (dcmsConfig.plugins.tinymce && dcmsConfig.plugins.tinymce !== false)){
    LoadJS('tinymce',dcmsConfig.plugins.tinymce,'local');
}

if (document.querySelectorAll('.datatable').length > 0 && (dcmsConfig.plugins.KTDatatable && dcmsConfig.plugins.KTDatatable !== false)){
    LoadJS('KTDatatable',dcmsConfig.plugins.KTDatatable,'local');
    LoadCSS(dcmsConfig.plugins.KTDatatable,'local');
}

window.onReady = function(yourMethod) {
    var readyStateCheckInterval = setInterval(function() {
        if (document && document.readyState === 'complete') {
            clearInterval(readyStateCheckInterval);
            yourMethod();
        }
    }, 100);
}

window.hasLoaded = function(plugins,yourMethod) {
    plugins = (typeof plugins == 'string') ? [plugins] : plugins;
    var success = 0;
    var readyStateCheckInterval = setInterval(function() {
        plugins.forEach(function(plugin,x){
            if (typeof window[plugin] !== 'undefined' || typeof $.fn[plugin] !== 'undefined') {
                clearInterval(readyStateCheckInterval);
                success++;
                if (success == plugins.length){
                    yourMethod();
                }
            }
        })
    }, 100);
}

/**
 *
 *  Plugin Settings
 *
 */

// Axios Laravel
window.axiosCfg = {
    headers: {
        'X-CSRF-TOKEN': document.querySelectorAll('meta[name=csrf-token]')[0].content,
        "Content-type": "application/x-www-form-urlencoded"
    }
}

// Date Format
window.AppDateFormat = "dd-mm-yyyy";

// Datatable Settings
window.AllowNewTab = false;

// Locale
window.language = 'en';

// Either use locale provided by server, or manually specified language
window.language = (typeof locale !== 'undefined') ? locale : window.language;

// TinyMCE
window.langFiles = '/js/dcms/tinymce_lang/'+window.language+'.js';
window.tinyMCEplugins = 'print preview fullpage searchreplace autolink directionality visualblocks visualchars fullscreen image link media template codesample table charmap hr pagebreak nonbreaking anchor toc insertdatetime advlist lists textcolor wordcount imagetools  contextmenu colorpicker textpattern help';
window.tinyMCEtoolbar = 'formatselect | bold italic strikethrough forecolor backcolor | link | alignleft aligncenter alignright alignjustify  | numlist bullist outdent indent  | removeformat';

// Filepond
window.FilePondMaxFileSize = 1; //in MB
window.FilePondAllowRevert = true;
window.FilePondInstantUpload = true;
try {
    window.FilePondMaxFileSize = maxSizeServer;
} catch (error) {
    window.FilePondMaxFileSize = window.FilePondMaxFileSize;
}

/**
 *
 *  Translations
 *
 */

try {
    var lang = require('../../../resources/lang/' + window.language + '.json');
} catch (error) {
    console.log('No translation yet for: ' + window.language);
}

window.Lang = function (string) {
    try {
        var langVal;
        langVal = (typeof lang[string] !== 'undefined') ? lang[string] : string;
        return langVal;
    } catch (error) {
        return string;
    }
}

/**
 *
 *  AJAX Headers
 *
 */

const { extendWith, isArray } = require('lodash');

try {
    window.csrf = document.querySelectorAll('meta[name=csrf-token]')[0].content;
} catch (error) {
    console.log('Put a meta tag with name=csrf-token and the CSRF token in <head></head>')
}

/**
 *
 *  Spinner on submit/file upload
 *
 */

var spinner = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';

window.HaltSubmit = function () {
    document.querySelectorAll('button[type=submit]').forEach(element => element.disabled = true);
}
window.DisableSubmit = function () {
    document.querySelectorAll('button[type=submit]').forEach(function (element) {
        element.innerHTML = spinner + " " + element.innerHTML;
        element.disabled = true;
    });
}
window.EnableSubmit = function () {
    document.querySelectorAll('button[type=submit]').forEach(function (element) {
        element.innerHTML = element.innerHTML.replace(spinner, '');
        element.disabled = false;
    });
}

require('./plugins/carousel.js');
require('./plugins/slimselect.js');
require('./plugins/tinymce.js');
require('./plugins/dateclockpicker.js');
require('./plugins/jexcel.js');
require('./plugins/filepond.js');
require('./metronic/dcmsdatatable.js');
require('../../../public/js/dcms/assets/spotlight.js');

/**
 *
 *  jQuery Datatables
 *
 */

//Reload Datatable if one present
function ReloadDT() {
    try {
        table.ajax.reload(null, false);
    } catch (error) {
        // do nothing
    }
    try {
        $.each($('.datatable'), function (indexInArray, table) {
            $(table).KTDatatable('reload');
        });
    } catch (error) {
        // do nothing
    }
}

/**
 *
 *  AJAX Submits
 *
 */

window.HttpReq = function (formMethod, formAction, formData) {
    DisableSubmit();
    $.ajax({
        type: formMethod,
        url: formAction,
        processData: false,
        contentType: false,
        data: formData,
        headers: {
            'X-CSRF-TOKEN': window.csrf
        },
        success: function (response) {
            Swal.fire({
                title: Lang((response['title']) ? response['title'] : 'Succesfully created'),
                text: Lang((response['message']) ? response['message'] : 'Your request was successful.'),
                icon: "success"
            }).then(function(result){
                if (result.value){
                    if (response.url) {
                        window.location.href = response.url;
                    }
                }
            });
        },
        error: function (response) {
            var reply = response.responseJSON;
            if (reply['errors']) {
                let errorString = '';
                $.each(reply['errors'], function (key, error) {
                    errorString = errorString + error[0].replace(':', '.') + "<br>";
                });
                Swal.fire({
                    title: Lang(reply['message']),
                    html: errorString,
                    icon: "error"
                });
            } else {
                Swal.fire({
                    title: Lang('Unknown error'),
                    html: Lang('An unknown error has occurred.') + "<br>" + Lang('Contact support if this problem persists.'),
                    icon: "error"
                })
            }
        },
        complete: function () {
            EnableSubmit();
        }
    });
}

let ajaxForms = document.querySelectorAll('[data-dcms-action=ajax]')
function SubmitAjax(e) {
    try {
        tinyMCE.triggerSave();
    } catch (error) {
        
    }
    let formAction = e.target.action;
    let formMethod = e.target.method;
    let formData = new FormData(e.target);
    let formRequest = HttpReq(formMethod, formAction, formData);
}
ajaxForms.forEach(element =>
    element.addEventListener('submit', function (e) {
        e.preventDefault();
        SubmitAjax(e);
    }));


/**
 *
 *  Delete from a table or form
 *
 */

window.DeleteModel = function (args) {
    var id = (args['id']) ? args['id'] : null;
    var route = (args['route']) ? args['route'] : null;
    var confirmTitle = (Lang(args['confirmTitle'])) ? Lang(args['confirmTitle']) : '';
    var confirmMsg = (Lang(args['confirmMsg'])) ? Lang(args['confirmMsg']) : '';
    var completeTitle = (Lang(args['completeTitle'])) ? Lang(args['completeTitle']) : '';
    var completeMsg = (Lang(args['completeMsg'])) ? Lang(args['completeMsg']) : '';
    var failedTitle = (Lang(args['failedTitle'])) ? Lang(args['failedTitle']) : '';
    var failedMsg = (Lang(args['failedMsg'])) ? Lang(args['failedMsg']) : '';
    var redirect = (args['redirect']) ? args['redirect'] : '';

    Swal.fire({
        showCancelButton: true,
        title: confirmTitle,
        html: confirmMsg,
        icon: "warning"
    }).then(function(result){
        if (result.value){
            if (id != null) {
                if (isArray(id)) {
                    let success = true;
                    let deleteRows = $.each(id, function (key, x) {
                        jQuery.ajax({
                            type: "POST",
                            async: false,
                            headers: {
                                'X-CSRF-TOKEN': window.csrf
                            },
                            url: route.replace('__id__', x),
                            data: {
                                _method: "DELETE"
                            },
                            error: function () {
                                success = false;
                            }
                        });
                    });
                    if (success == true) {
                        ReloadDT();
                        Swal.fire(completeTitle, completeMsg, 'success');
                    } else {
                        Swal.fire(failedTitle, failedMsg, 'error');
                    }
                } else {
                    jQuery.ajax({
                        type: "POST",
                        headers: {
                            'X-CSRF-TOKEN': window.csrf
                        },
                        url: route,
                        data: {
                            _method: "DELETE",
                        },
                        success: function (response) {
                            ReloadDT();
                            Swal.fire({
                                title: completeTitle,
                                text: completeMsg,
                                icon: "success"
                            }).then(function(result){
                                if (result.value){
                                    if (redirect !== '') {
                                        if (window.AllowNewTab == false) {
                                            window.location.href = redirect;
                                        } else {
                                            window.open(redirect, '_blank');
                                        }
                                    }
                                }
                            });
                        },
                        error: function () {
                            Swal.fire(failedTitle, failedMsg, 'error');
                        }
                    });
                }
            }
        }
    });
};

/**
 *
 *  Dynamic deleting
 *
 */

$(document).on('click', 'form [data-dcms-action=destroy]', function (e) {
    e.preventDefault();
    let element = e.currentTarget;
    let id = element.dataset.dcmsId;
    let route = element.dataset.dcmsDestroyRoute.replace('__id__', id);
    let redirect = (element.dataset.dcmsDestroyRedirect) ? element.dataset.dcmsDestroyRedirect : false;
    DeleteModel({
        id: id,
        route: route,
        confirmTitle: (element.dataset.dcmsDeleteConfirmTitle) ? Lang(element.dataset.dcmsDeleteConfirmTitle) : Lang('Delete object'),
        confirmMsg: (element.dataset.dcmsDeleteConfirmMessage) ? Lang(element.dataset.dcmsDeleteConfirmMessage) : Lang('Are you sure you want to delete this object?'),
        completeTitle: (element.dataset.dcmsDeleteCompleteTitle) ? Lang(element.dataset.dcmsDeleteCompleteTitle) : Lang('Deleted object'),
        completeMsg: (element.dataset.dcmsDeleteCompleteMessage) ? Lang(element.dataset.dcmsDeleteCompleteMessage) : Lang('The object has been succesfully deleted.'),
        failedTitle: (element.dataset.dcmsDeleteFailedTitle) ? Lang(element.dataset.dcmsDeleteFailedTitle) : Lang('Deleting failed'),
        failedMsg: (element.dataset.dcmsDeleteFailedMessage) ? Lang(element.dataset.dcmsDeleteFailedMessage) : Lang('This object can\'t be deleted. It might still be required somewhere.'),
        redirect: redirect
    });
});

/**
 *
 *  Merge table columns
 *
 */


window.MergeColumns = function (row, column) {
    var value = '';
    if (column.split(',').length > 1) {
        Array.from(column.split(',')).forEach(function (element) {
            value = value + row[element] + ' ';
        });
    } else {
        value = row[column];
    }
    return value;
}

/**
 *
 *  Load links in modal
 *
 */

window.LoadInModal = function (url, modal){
    $.get(url, function (data) {
        el = $('#global_modal');
        el.find('.modal-content').html(data);
        el.modal('show');
        if (el.find('[data-modal-init').length == 1) {
            let callback = el.find('[data-modal-init]').data('modal-init');
            var fn = window[callback];
            if (typeof fn === 'function') {
                fn(el);
            }
        }
    });
}

/**
 *
 *  Copy text to clipboard
 */

window.textToClipBoard = function (text) {
    var dummy = document.createElement("textarea");
    document.body.appendChild(dummy);
    dummy.value = text;
    dummy.select();
    document.execCommand("copy");
    document.body.removeChild(dummy);
}
