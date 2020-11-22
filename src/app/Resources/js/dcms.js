/**
 *
 *  Import plugins which are not defined in the current project
 *
 */

var dcmsConfig;
try {
    dcmsConfig = require('../../../dcms.json');
    window.dcmsConfig = dcmsConfig;
} catch (error) {
    dcmsConfig = {};
}

if (typeof process.env.MIX_DCMS_ENV == 'undefined') {
    console.log('No environment defined for DCMS. Define this in your .env as MIX_DCMS_ENV.')
}

window.LoadJS = function (pluginFunction, plugin, pluginPath = 'cdn') {
    var scriptSources = (process.env.MIX_DCMS_ENV == 'local' || pluginPath !== 'cdn') ? plugin['local']['js'] : plugin[pluginPath]['js'];
    function LoadJSSource(source) {
        var script = document.createElement('script');
        script.type = 'text/javascript';
        script.src = source;
        var s = document.getElementsByTagName('script')[0];
        s.parentNode.insertBefore(script, s);
    }
    if (typeof scriptSources == 'string') {
        LoadJSSource(scriptSources);
    } else {
        Array.from(scriptSources).forEach(function (source) {
            LoadJSSource(source);
        });
    }
}

let cssLink, cssTag;
window.LoadCSS = function (plugin, pluginPath = 'cdn') {
    var cssSources = (process.env.MIX_DCMS_ENV == 'local' || pluginPath !== 'cdn') ? plugin['local']['css'] : plugin[pluginPath]['css'];
    function LoadCSSSource(source) {
        cssLink = document.createElement('link');
        cssLink.rel = 'stylesheet';
        cssLink.type = 'text/css';
        cssLink.href = source;
        cssTag = document.getElementsByTagName('head')[0];
        cssTag.append(cssLink);
    }
    if (typeof cssSources == 'string') {
        LoadCSSSource(cssSources);
    } else {
        Array.from(cssSources).forEach(function (source) {
            LoadCSSSource(source);
        });
    }
}

if (typeof axios == 'undefined' && (dcmsConfig.plugins.axios && dcmsConfig.plugins.axios.enable !== false)) {
    LoadJS('axios', dcmsConfig.plugins.axios);
}

if (typeof Vue == 'undefined' && (dcmsConfig.plugins.vue && dcmsConfig.plugins.vue.enable !== false)) {
    LoadJS('Vue', dcmsConfig.plugins.vue);
}

if (typeof Swal == 'undefined' && (dcmsConfig.plugins.sweetalert2 && dcmsConfig.plugins.sweetalert2.enable !== false)) {
    LoadJS('Swal', dcmsConfig.plugins.sweetalert2);
}

if (typeof toastr == 'undefined' && (dcmsConfig.plugins.toastr && dcmsConfig.plugins.toastr.enable !== false)) {
    LoadCSS(dcmsConfig.plugins.toastr);
    LoadJS('toastr', dcmsConfig.plugins.toastr);
}

if (typeof SlimSelect == 'undefined' && document.querySelectorAll('[data-type=slimselect]').length > 0 && (dcmsConfig.plugins.slimselect && dcmsConfig.plugins.slimselect.enable !== false)) {
    LoadCSS(dcmsConfig.plugins.slimselect);
    LoadJS('SlimSelect', dcmsConfig.plugins.slimselect);
}

if (typeof datepicker == 'undefined' && document.querySelectorAll('[data-type=datepicker]').length > 0 && (dcmsConfig.plugins.datepicker && dcmsConfig.plugins.datepicker.enable !== false)) {
    LoadCSS(dcmsConfig.plugins.datepicker);
    LoadJS('datepicker', dcmsConfig.plugins.datepicker);
}

if (typeof clockpicker == 'undefined' && document.querySelectorAll('[data-type=clockpicker]').length > 0 && (dcmsConfig.plugins.clockpicker && dcmsConfig.plugins.clockpicker !== false)) {
    LoadCSS(dcmsConfig.plugins.clockpicker);
    LoadJS('clockpicker', dcmsConfig.plugins.clockpicker);
}

if (typeof jexcel == 'undefined' && document.querySelectorAll('[data-type=jexcel]').length > 0 && (dcmsConfig.plugins.jexcel && dcmsConfig.plugins.jexcel !== false)) {
    LoadCSS(dcmsConfig.plugins.jexcel);
    LoadJS('jexcel', dcmsConfig.plugins.jexcel);
}
if (typeof jsuites == 'undefined' && document.querySelectorAll('[data-type=jexcel]').length > 0 && (dcmsConfig.plugins.jsuites && dcmsConfig.plugins.jsuites !== false)) {
    LoadCSS(dcmsConfig.plugins.jsuites);
    LoadJS('jsuites', dcmsConfig.plugins.jsuites);
}

if (typeof Papa == 'undefined' && (dcmsConfig.plugins.papa && dcmsConfig.plugins.papa !== false)) {
    LoadJS('Papa', dcmsConfig.plugins.papa, 'local');
}

if (typeof FilePond == 'undefined' && document.querySelectorAll('[data-type=filepond]').length > 0 && (dcmsConfig.plugins.filepond && dcmsConfig.plugins.filepond !== false)) {
    LoadCSS(dcmsConfig.plugins.filepond);
    LoadJS('FilePond', dcmsConfig.plugins.filepond);
    LoadCSS(dcmsConfig.plugins.filepondImagePreview);
    LoadJS('FilePondPluginImagePreview', dcmsConfig.plugins.filepondImagePreview);
    LoadJS('FilePondPluginFileValidateSize', dcmsConfig.plugins.filepondValidateSize);
}

if (typeof tinymce == 'undefined' && document.querySelectorAll('[data-type=tinymce]').length > 0 && (dcmsConfig.plugins.tinymce && dcmsConfig.plugins.tinymce !== false)) {
    LoadJS('tinymce', dcmsConfig.plugins.tinymce, 'local');
}

if (document.querySelectorAll('.datatable').length > 0 && (dcmsConfig.plugins.KTDatatable && dcmsConfig.plugins.KTDatatable !== false)) {
    LoadJS('KTDatatable', dcmsConfig.plugins.KTDatatable, 'local');
    LoadCSS(dcmsConfig.plugins.KTDatatable, 'local');
}

window.onReady = function (yourMethod) {
    var readyStateCheckInterval = setInterval(function () {
        if (document && document.readyState === 'complete') {
            clearInterval(readyStateCheckInterval);
            yourMethod();
        }
    }, 100);
}

window.hasLoaded = function (plugins, yourMethod) {
    plugins = (typeof plugins == 'string') ? [plugins] : plugins;
    var success = 0;
    var readyStateCheckInterval = setInterval(function () {
        plugins.forEach(function (plugin, x) {
            if (typeof window[plugin] !== 'undefined' || typeof $.fn[plugin] !== 'undefined') {
                clearInterval(readyStateCheckInterval);
                success++;
                if (success == plugins.length) {
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
window.langFiles = '/js/dcms/tinymce_lang/' + window.language + '.js';
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

// Form validation
formElementSelectors = [
    'name', 'data-id'
]
window.DCMSFormAlerts = true;
window.DCMSFormErrorBag = false;

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
 *  SweetAlert variables
 *
 */


window.SwalConfirmButtonColor = "var(--primary)";
window.SwalConfirmButtonText = Lang("OK");
window.SwalCancelButtonColor = "black";
window.SwalCancelButtonText = Lang("Cancel");

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
    // If the document has a simple div with .dcms-error-parent class, then the errors from the request
    // will be appended to this div, provided window.DCMSFormErrorBag is set to true above
    var errorBag, errorBagTitle, errorBagParent, errorElement;
    errorElement = document.createElement('div');
    errorElement.innerHTML = `<div class="alert alert-danger fade show dcms-error-bag" role="alert">
        <strong class='dcms-error-title'></strong></p>
        <p class='dcms-errors'></p>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>`;
    errorBagParent = document.querySelector('.dcms-error-parent');
    function FillErrorBag(args) {
        if (errorBagParent){
            errorBagParent.style.display = 'block';
            errorBagTitle = errorBagParent.querySelector('.dcms-error-title');
            errorBagTitle.innerHTML = args.title ?? Lang("An error has occurred");
            errorBag = errorBagParent.querySelector('.dcms-errors');
            errorBag.innerHTML = args.message ?? Lang('An unknown error has occurred.') + "<br>" + Lang('Contact support if this problem persists.');
        }
    }
    // Clear invalid classes
    document.querySelectorAll(".is-invalid").forEach(function (element) {
        element.classList.remove('is-invalid')
    })
    // Start request
    if (errorBagParent){
        errorBagParent.innerHTML = "";
    }
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
                icon: "success",
                confirmButtonColor: window.SwalConfirmButtonColor ?? "var(--primary)",
                confirmButtonText: window.SwalConfirmButtonText ?? Lang("OK"),
                cancelButtonColor: window.SwalCancelButtonColor ?? "var(--dark)",
                cancelButtonText: window.SwalCancelButtonText ?? Lang("Cancel"),
            }).then(function (result) {
                if (result.value) {
                    if (response.url) {
                        window.location.href = response.url;
                    }
                }
            });
        },
        error: function (response) {
            var reply = response.responseJSON;
            if (window.DCMSFormErrorBag == true && errorBagParent !== null) {
                errorBagParent.appendChild(errorElement);
            }
            if (reply['errors']) {
                let errorString = '';
                $.each(reply['errors'], function (name, error) {
                    errorString = errorString + error[0].replace(':', '.') + "<br>";
                    formElementSelectors.forEach(function (selector) {
                        let formElement = document.querySelector(`[` + selector + `^="` + name + `"`) ?? null;
                        if (formElement) {
                            formElement.classList.add('is-invalid')
                        }
                    })
                });
                if (window.DCMSFormAlerts == true || window.DCMSFormErrorBag == false) {
                    Swal.fire({
                        title: Lang(reply['message']),
                        html: errorString,
                        confirmButtonColor: window.SwalConfirmButtonColor ?? "var(--primary)",
                        confirmButtonText: window.SwalConfirmButtonText ?? Lang("OK"),
                        cancelButtonColor: window.SwalCancelButtonColor ?? "var(--dark)",
                        cancelButtonText: window.SwalCancelButtonText ?? Lang("Cancel"),
                        icon: "error"
                    });
                }
                if (window.DCMSFormAlerts == false || window.DCMSFormErrorBag == true && errorBagParent !== null) {
                    FillErrorBag({
                        title: Lang(reply['message']),
                        message: errorString
                    });
                }
            } else {
                if (window.DCMSFormAlerts == true || window.DCMSFormErrorBag == false) {
                    Swal.fire({
                        title: Lang('Unknown error'),
                        html: Lang('An unknown error has occurred.') + "<br>" + Lang('Contact support if this problem persists.'),
                        icon: "error",
                        confirmButtonColor: window.SwalConfirmButtonColor ?? "var(--primary)",
                        confirmButtonText: window.SwalConfirmButtonText ?? Lang("OK"),
                        cancelButtonColor: window.SwalCancelButtonColor ?? "var(--dark)",
                        cancelButtonText: window.SwalCancelButtonText ?? Lang("Cancel"),
                    })
                } else if (window.DCMSFormAlerts == false || window.DCMSFormErrorBag == true && errorBagParent !== null) {
                    FillErrorBag({
                        title: Lang("Unknown error"),
                        message: Lang('An unknown error has occurred.') + "<br>" + Lang('Contact support if this problem persists.')
                    });
                }

            }
        },
        complete: function () {
            EnableSubmit();
        }
    });
}

let ajaxForms = document.querySelectorAll('[data-dcms-action=ajax]')
function SubmitAjax(e) {
    if (typeof tinymce !== 'undefined') {
        tinyMCE.triggerSave();
    }
    let formAction = e.target.action;
    let formMethod = e.target.method;
    let formData = new FormData(e.target);
    if (document.querySelectorAll('.filepond--file').length > 0) {
        let loopedNames = [];
        let namesToLoop = [];
        Array.from(window.fileArray).forEach(function (fileWindow) {
            namesToLoop.push(fileWindow.input);
        });
        namesToLoop.forEach(function (name) {
            if (!loopedNames.includes(name)) {
                loopedNames.push(name);
            }
        });
        loopedNames.forEach(function (name) {
            let curInputs = document.getElementsByName(name);
            let curFiles = [];
            formData.delete(name);
            curInputs.forEach(function (input) {
                formData.append(name, input.value);
            })
        });
    }
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
        icon: "warning",
        confirmButtonColor: window.SwalConfirmButtonColor ?? "var(--primary)",
        confirmButtonText: window.SwalConfirmButtonText ?? Lang("OK"),
        cancelButtonColor: window.SwalCancelButtonColor ?? "var(--dark)",
        cancelButtonText: window.SwalCancelButtonText ?? Lang("Cancel"),
    }).then(function (result) {
        if (result.value) {
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
                        Swal.fire({
                            title: completeTitle,
                            text: completeMsg,
                            icon: "success",
                            confirmButtonColor: window.SwalConfirmButtonColor ?? "var(--primary)",
                            confirmButtonText: window.SwalConfirmButtonText ?? Lang("OK"),
                            cancelButtonColor: window.SwalCancelButtonColor ?? "var(--dark)",
                            cancelButtonText: window.SwalCancelButtonText ?? Lang("Cancel"),
                        })

                    } else {
                        Swal.fire({
                            title: failedTitle,
                            text: failedMsg,
                            icon: "error",
                            confirmButtonColor: window.SwalConfirmButtonColor ?? "var(--primary)",
                            confirmButtonText: window.SwalConfirmButtonText ?? Lang("OK"),
                            cancelButtonColor: window.SwalCancelButtonColor ?? "var(--dark)",
                            cancelButtonText: window.SwalCancelButtonText ?? Lang("Cancel"),
                        })
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
                                icon: "success",
                                confirmButtonColor: window.SwalConfirmButtonColor ?? "var(--primary)",
                                confirmButtonText: window.SwalConfirmButtonText ?? Lang("OK"),
                                cancelButtonColor: window.SwalCancelButtonColor ?? "var(--dark)",
                                cancelButtonText: window.SwalCancelButtonText ?? Lang("Cancel"),
                            }).then(function (result) {
                                if (result.value) {
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
                            Swal.fire({
                                title: failedTitle,
                                text: failedMsg,
                                icon: "error",
                                confirmButtonColor: window.SwalConfirmButtonColor ?? "var(--primary)",
                                confirmButtonText: window.SwalConfirmButtonText ?? Lang("OK"),
                                cancelButtonColor: window.SwalCancelButtonColor ?? "var(--dark)",
                                cancelButtonText: window.SwalCancelButtonText ?? Lang("Cancel"),
                            })
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

$(document).on('click', '[data-dcms-action=destroy]', function (e) {
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

window.LoadInModal = function (url, modal) {
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
