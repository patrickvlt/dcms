/**
 *
 *  Import plugins which are not defined in the current project
 *
 */

var dcmsConfig, formElementSelectors, table;
try {
    dcmsConfig = require('../../../dcms.json');
    window.dcmsConfig = dcmsConfig;
} catch (error) {
    dcmsConfig = {};
}

if (typeof process.env.MIX_DCMS_ENV == 'undefined') {
    console.log('No environment defined for DCMS. Define this in your .env as MIX_DCMS_ENV.');
}

window.LoadJS = function (plugin, pluginPath = 'cdn') {
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
};

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
};

/**
 *
 *  Load assets from CDN or locally, configure this in dcms.json
 *
 */

if (typeof axios == 'undefined' && (dcmsConfig.plugins.axios && dcmsConfig.plugins.axios.enable !== false)) {
    window.LoadJS(dcmsConfig.plugins.axios);
}

if (typeof Vue == 'undefined' && (dcmsConfig.plugins.vue && dcmsConfig.plugins.vue.enable !== false)) {
    window.LoadJS(dcmsConfig.plugins.vue);
}

if (typeof Swal == 'undefined' && (dcmsConfig.plugins.sweetalert2 && dcmsConfig.plugins.sweetalert2.enable !== false)) {
    window.LoadJS(dcmsConfig.plugins.sweetalert2);
}

if (typeof toastr == 'undefined' && (dcmsConfig.plugins.toastr && dcmsConfig.plugins.toastr.enable !== false)) {
    window.LoadCSS(dcmsConfig.plugins.toastr);
    window.LoadJS(dcmsConfig.plugins.toastr);
}

if (typeof Papa == 'undefined' && (dcmsConfig.plugins.papa && dcmsConfig.plugins.papa.enable !== false)) {
    window.LoadJS(dcmsConfig.plugins.papa, 'local');
}

if (typeof SlimSelect == 'undefined' && document.querySelectorAll('[data-type=slimselect]').length > 0 && (dcmsConfig.plugins.slimselect && dcmsConfig.plugins.slimselect.enable !== false)) {
    window.LoadCSS(dcmsConfig.plugins.slimselect);
    window.LoadJS(dcmsConfig.plugins.slimselect);
}

if (typeof datepicker == 'undefined' && document.querySelectorAll('[data-type=datepicker]').length > 0 && (dcmsConfig.plugins.datepicker && dcmsConfig.plugins.datepicker.enable !== false)) {
    window.LoadCSS(dcmsConfig.plugins.datepicker);
    window.LoadJS(dcmsConfig.plugins.datepicker);
}

if (typeof clockpicker == 'undefined' && document.querySelectorAll('[data-type=clockpicker]').length > 0 && (dcmsConfig.plugins.clockpicker && dcmsConfig.plugins.clockpicker.enable !== false)) {
    window.LoadCSS(dcmsConfig.plugins.clockpicker);
    window.LoadJS(dcmsConfig.plugins.clockpicker);
}

if (typeof jexcel == 'undefined' && document.querySelectorAll('[data-type=jexcel]').length > 0 && (dcmsConfig.plugins.jexcel && dcmsConfig.plugins.jexcel.enable !== false)) {
    window.LoadCSS(dcmsConfig.plugins.jexcel);
    window.LoadJS(dcmsConfig.plugins.jexcel);
}
if (typeof jsuites == 'undefined' && document.querySelectorAll('[data-type=jexcel]').length > 0 && (dcmsConfig.plugins.jsuites && dcmsConfig.plugins.jsuites.enable !== false)) {
    window.LoadCSS(dcmsConfig.plugins.jsuites);
    window.LoadJS(dcmsConfig.plugins.jsuites);
}


if (typeof FilePond == 'undefined' && document.querySelectorAll('[data-type=filepond]').length > 0 && (dcmsConfig.plugins.filepond && dcmsConfig.plugins.filepond.enable !== false)) {
    window.LoadCSS(dcmsConfig.plugins.filepond);
    window.LoadJS(dcmsConfig.plugins.filepond);
    window.LoadCSS(dcmsConfig.plugins.filepondImagePreview);
    window.LoadJS(dcmsConfig.plugins.filepondImagePreview);
    window.LoadJS(dcmsConfig.plugins.filepondValidateSize);
}

window.enableICheck = false;
if (document.querySelectorAll('[data-type=iCheck]').length > 0 && (dcmsConfig.plugins.iCheck && dcmsConfig.plugins.iCheck.enable !== false)) {
    window.enableICheck = true;
    window.LoadJS(dcmsConfig.plugins.iCheck);
    window.LoadCSS(dcmsConfig.plugins.iCheck);
}

if (typeof tinymce == 'undefined' && (document.querySelectorAll('[data-type=tinymce]').length > 0 || window.enableEditors == true) && (dcmsConfig.plugins.tinymce && dcmsConfig.plugins.tinymce.enable !== false)) {
    window.LoadJS(dcmsConfig.plugins.tinymce, 'local');
}

if (document.querySelectorAll('.datatable').length > 0 && (dcmsConfig.plugins.KTDatatable && dcmsConfig.plugins.KTDatatable.enable !== false)) {
    window.LoadJS(dcmsConfig.plugins.KTDatatable, 'local');
    window.LoadCSS(dcmsConfig.plugins.KTDatatable, 'local');
}

window.onReady = function (yourMethod) {
    var readyStateCheckInterval = setInterval(function () {
        if (document && (document.readyState == 'interactive' || document.readyState == 'complete')) {
            clearInterval(readyStateCheckInterval);
            yourMethod();
        }
    }, 200);
};

window.hasLoaded = function (plugins, yourMethod) {
    plugins = (typeof plugins == 'string') ? [plugins] : plugins;
    var success = 0;
    var readyStateCheckInterval = setInterval(function () {
        plugins.forEach(function (plugin) {
            if (typeof window[plugin] !== 'undefined' || typeof $.fn[plugin] !== 'undefined') {
                clearInterval(readyStateCheckInterval);
                success++;
                if (success == plugins.length) {
                    yourMethod();
                }
            }
        });
    }, 200);
};

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
};

// Date Format
window.AppDateFormat = "dd-mm-yyyy";

// Datatable Settings
window.AllowNewTab = false;

// Locale
window.language = 'en';

// Either use locale provided by server, or manually specified language
window.language = (typeof window.locale !== 'undefined') ? window.locale : window.language;

// TinyMCE
window.langFiles = '/js/dcms/tinymce_lang/' + window.language + '.js';
window.tinyMCEplugins = 'print preview fullpage searchreplace autolink directionality visualblocks visualchars fullscreen image link media template codesample table charmap hr pagebreak nonbreaking anchor toc insertdatetime advlist lists textcolor wordcount imagetools  contextmenu colorpicker textpattern help';
window.tinyMCEtoolbar = 'formatselect | bold italic strikethrough forecolor backcolor | link | alignleft aligncenter alignright alignjustify  | numlist bullist outdent indent  | removeformat';

// Filepond
window.FilePondMaxFileSize = "2000KB"; //in KB
window.FilePondAllowRevert = true;
window.FilePondInstantUpload = true;
try {
    window.FilePondMaxFileSize = window.maxSizeServer+"KB";
} catch (error) {
    window.FilePondMaxFileSize = window.FilePondMaxFileSize;
}

// Form validation
formElementSelectors = [
    'name', 'data-id'
];
window.DCMSFormAlerts = true;
window.DCMSFormErrorBag = false;

/**
 *
 *  SweetAlert Colors
 *
 */


window.SwalConfirmButtonColor = "var(--primary)";
window.SwalCancelButtonColor = "black";

/**
*
*  Require custom settings such as language, form alerts, window variables
*/

require('./_settings.js');

/**
 *
 *  Translations
 *
 */

try {
    var lang = require('../../../resources/lang/' + window.language + '.json');
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
 *  SweetAlert Messages
 *
 */


window.SwalConfirmButtonText = Lang("OK");
window.SwalCancelButtonText = Lang("Cancel");

/**
*
*  Require custom translations/messages
*/

require('./_messages.js');

/**
 *
 *  AJAX Headers
 *
 */

try {
    window.csrf = document.querySelectorAll('meta[name=csrf-token]')[0].content;
} catch (error) {
    console.log('Put a meta tag with name=csrf-token and the CSRF token in <head></head>');
}

/**
 *
 *  Spinner on submit/file upload
 *
 */

var spinner = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';

window.HaltSubmit = function (selector = null) {
    if (!selector) {
        selector = 'button[type=submit]';
    }
    document.querySelectorAll(selector).forEach(element => element.disabled = true);
};
window.DisableSubmit = function (selector = null) {
    if (!selector) {
        selector = 'button[type=submit]';
    }
    document.querySelectorAll(selector).forEach(function (element) {
        element.innerHTML = spinner + " " + element.innerHTML;
        element.disabled = true;
    });
};
window.EnableSubmit = function (selector = null) {
    if (!selector) {
        selector = 'button[type=submit]';
    }
    document.querySelectorAll(selector).forEach(function (element) {
        element.innerHTML = element.innerHTML.replace(spinner, '');
        element.disabled = false;
    });
};

/**
 *
 *  Setup global DCMS object
 *
 */

 window.DCMS = {};

require('./plugins/carousel.js');
require('./plugins/slimselect.js');
require('./plugins/tinymce.js');
require('./plugins/editor.js');
require('./plugins/dateclockpicker.js');
require('./plugins/jexcel.js');
require('./plugins/filepond.js');
require('./plugins/icheck.js');
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

// If the document has a simple div with .dcms-error-parent class, then the errors from the request
// will be appended to this div, provided window.DCMSFormErrorBag is set to true above
if (document.querySelector('.dcms-error-parent')){
    var errorBagParent, errorBagTitle, errorBag;
    var errorElement = document.createElement('div');
    errorElement.innerHTML = `<div class="alert alert-danger fade show dcms-error-bag" role="alert">
        <strong class='dcms-error-title'></strong></p>
        <p class='dcms-errors'></p>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>`;
    errorBagParent = document.querySelector('.dcms-error-parent');
    window.FillErrorBag = function (args) {
        if (window.DCMSFormErrorBag == true) {
            errorBagParent.appendChild(errorElement);
        }
        errorBagParent = document.querySelector('.dcms-error-parent');
        errorBagParent.style.display = 'block';
        errorBagTitle = errorBagParent.querySelector('.dcms-error-title');
        errorBagTitle.innerHTML = (typeof args.title !== 'undefined') ? args.title : Lang("An error has occurred");
        errorBag = errorBagParent.querySelector('.dcms-errors');
        errorBag.innerHTML = (typeof args.message !== 'undefined') ? args.message : Lang('An unknown error has occurred.') + "<br>" + Lang('Contact support if this problem persists.');
    };
    window.ScrollToBag = function () {
        $([document.documentElement, document.body]).animate({
            scrollTop: $(`.dcms-error-parent`).offset().top - 300
        }, 1500);
    };
}

window.HttpReq = function (formMethod, formAction, formData, customSettings = null) {
    // Grab custom functions if these have been defined
    var customBefore = (customSettings) ? customSettings.customBefore : null;
    var customBeforeSuccess = (customSettings) ? customSettings.customBeforeSuccess : null;
    var customBeforeError = (customSettings) ? customSettings.customBeforeError : null;
    var customSuccess = (customSettings) ? customSettings.customSuccess : null;
    var customSuccessMessage = (customSettings) ? customSettings.customSuccessMessage : null;
    var customSuccessRedirect = (customSettings) ? customSettings.customSuccessRedirect : null;
    var customError = (customSettings) ? customSettings.customError : null;
    var customErrorTitle = (customSettings) ? customSettings.customErrorTitle : null;
    var customErrorMessage = (customSettings) ? customSettings.customErrorMessage : null;
    var customComplete = (customSettings) ? customSettings.customComplete : null;
    var customBeforeComplete = (customSettings) ? customSettings.customBeforeComplete : null;
    var dontDisableSubmit = (customSettings) ? customSettings.dontDisableSubmit : null;

    if (customBefore) {
        if (typeof customBefore == 'string') {
            window[customBefore]();
        } else {
            customBefore();
        }
    }
    // Clear invalid classes
    document.querySelectorAll(".is-invalid").forEach(function (element) {
        element.classList.remove('is-invalid');
    });
    // Start request
    if (errorBagParent) {
        errorBagParent.innerHTML = "";
    }
    if (dontDisableSubmit == false || dontDisableSubmit == null){
        window.DisableSubmit();
    }
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
            if (customBeforeSuccess) {
                if (typeof customBeforeSuccess == 'string') {
                    window[customBeforeSuccess](response);
                } else {
                    customBeforeSuccess(response);
                }
            }
            if (customSuccess) {
                if (typeof customSuccess == 'string') {
                    window[customSuccess](response);
                } else {
                    customSuccess(response);
                }
            } else {
                var swalMessage, swalRedirect;
                    // Make message
                    if (response['message']){
                        swalMessage = Lang(response['message']);
                    } else if (customSuccessMessage){
                        swalMessage = Lang(customSuccessMessage);
                    } else {
                        swalMessage = Lang('Press OK to return to the overview.');
                    }
                    // Make redirect
                    if (response['url']){
                        swalRedirect = response['url'];
                    } else if (customSuccessRedirect){
                        swalRedirect = customSuccessRedirect;
                    }
                if (window.DCMSFormAlerts == true || window.DCMSFormErrorBag == false) {
                    toastr.success(swalMessage);
                    if (swalRedirect) {
                        setTimeout(function(){
                            window.location.href = swalRedirect;
                        },2500);
                    }
                }
            }
        },
        error: function (response) {
            if (customBeforeError) {
                if (typeof customBeforeError == 'string') {
                    window[customBeforeError](response);
                } else {
                    customBeforeError(response);
                }
            }
            if (customError) {
                if (typeof customError == 'string') {
                    window[customError](response);
                } else {
                    customError(response);
                }
            } else {
                var reply = response.responseJSON;
                if (reply['errors']) {
                    let errorString = '';
                    $.each(reply['errors'], function (name, error) {
                        errorString = errorString + error[0].replace(':', '.') + "<br>";
                        formElementSelectors.forEach(function (selector) {
                            let formElement = (document.querySelector(`[` + selector + `^="` + name + `"`)) ? document.querySelector(`[` + selector + `^="` + name + `"`) : null;
                            if (formElement) {
                                formElement.classList.add('is-invalid');
                            }
                        });
                    });
                    if (window.DCMSFormAlerts == true || window.DCMSFormErrorBag == false) {
                        Swal.fire({
                            title: Lang(reply['message']),
                            html: errorString,
                            confirmButtonColor: (typeof window.SwalConfirmButtonColor !== 'undefined') ? window.SwalConfirmButtonColor : "var(--primary)",
                            confirmButtonText: (typeof window.SwalConfirmButtonText !== 'undefined') ? window.SwalConfirmButtonText : Lang("OK"),
                            cancelButtonColor: (typeof window.SwalCancelButtonColor !== 'undefined') ? window.SwalCancelButtonColor : "var(--dark)",
                            cancelButtonText: (typeof window.SwalCancelButtonText !== 'undefined') ? window.SwalCancelButtonText : Lang("Cancel"),
                            icon: "error"
                        });
                    }
                    if (window.DCMSFormAlerts == false || window.DCMSFormErrorBag == true) {
                        window.FillErrorBag({
                            title: Lang(reply['message']),
                            message: errorString
                        });
                    }
                } else {
                    var swalTitle, swalMessage;
                    // Make title
                    if (customErrorTitle){
                        swalTitle = Lang(customErrorTitle);
                    } else {
                        swalTitle = Lang('Unknown error');
                    }
                    // Make message
                    if (customErrorMessage){
                        swalMessage = Lang(customErrorMessage);
                    } else {
                        swalMessage = Lang('An unknown error has occurred.') + "<br>" + Lang('Contact support if this problem persists.');
                    }
                    if (window.DCMSFormAlerts == true || window.DCMSFormErrorBag == false) {
                        Swal.fire({
                            title: swalTitle,
                            html: swalMessage,
                            icon: "error",
                            confirmButtonColor: (typeof window.SwalConfirmButtonColor !== 'undefined') ? window.SwalConfirmButtonColor : "var(--primary)",
                            confirmButtonText: (typeof window.SwalConfirmButtonText !== 'undefined') ? window.SwalConfirmButtonText : Lang("OK"),
                            cancelButtonColor: (typeof window.SwalCancelButtonColor !== 'undefined') ? window.SwalCancelButtonColor : "var(--dark)",
                            cancelButtonText: (typeof window.SwalCancelButtonText !== 'undefined') ? window.SwalCancelButtonText : Lang("Cancel"),
                        });
                    } else if (window.DCMSFormAlerts == false || window.DCMSFormErrorBag == true) {
                        window.FillErrorBag({
                            title: swalTitle,
                            message: swalMessage
                        });
                    }

                }
            }
            if ($('.dcms-error-parent') && window.DCMSFormErrorBag == true){
                window.ScrollToBag();
            }
        },
        complete: function () {
            if (customBeforeComplete) {
                if (typeof customBeforeComplete == 'string') {
                    window[customBeforeComplete](response);
                } else {
                    customBeforeComplete(response);
                }
            }
            if (customComplete) {
                if (typeof customComplete == 'string') {
                    window[customComplete](response);
                } else {
                    customComplete(response);
                }
            } else {
                window.EnableSubmit();
            }
        }
    });
};

let ajaxForms = document.querySelectorAll('[data-dcms-action=ajax]');
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
            formData.delete(name);
            curInputs.forEach(function (input) {
                formData.append(name, input.value);
            });
        });
    }
    window.HttpReq(formMethod, formAction, formData);
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
    var id = (typeof args['id'] !== 'undefined') ? args['id'] : null;
    var route = (typeof args['route'] !== 'undefined') ? args['route'] : null;
    var confirmTitle = (typeof args['confirmTitle'] !== 'undefined') ? Lang(args['confirmTitle']) : '';
    var confirmMsg = (typeof args['confirmMsg'] !== 'undefined') ? Lang(args['confirmMsg']) : '';
    var completeMsg = (typeof args['completeMsg'] !== 'undefined') ? Lang(args['completeMsg']) : '';
    var failedTitle = (typeof args['failedTitle'] !== 'undefined') ? Lang(args['failedTitle']) : '';
    var failedMsg = (typeof args['failedMsg'] !== 'undefined') ? Lang(args['failedMsg']) : '';
    var redirect = (typeof args['redirect'] !== 'undefined') ? args['redirect'] : '';

    Swal.fire({
        showCancelButton: true,
        title: confirmTitle,
        html: confirmMsg,
        icon: "warning",
        confirmButtonColor: (typeof window.SwalConfirmButtonColor !== 'undefined') ? window.SwalConfirmButtonColor : "var(--primary)",
        confirmButtonText: (typeof window.SwalConfirmButtonText !== 'undefined') ? window.SwalConfirmButtonText : Lang("OK"),
        cancelButtonColor: (typeof window.SwalCancelButtonColor !== 'undefined') ? window.SwalCancelButtonColor : "var(--dark)",
        cancelButtonText: (typeof window.SwalCancelButtonText !== 'undefined') ? window.SwalCancelButtonText : Lang("Cancel"),
    }).then(function (result) {
        if (result.value) {
            if (id != null) {
                if (isArray(id)) {
                    jQuery.ajax({
                        type: "DELETE",
                        headers: {
                            'X-CSRF-TOKEN': window.csrf
                        },
                        url: route,
                        data: {
                            deleteIDs: id
                        },
                        success: function () {
                            ReloadDT();
                            toastr.success(completeMsg);
                            if (redirect) {
                                setTimeout(function(){
                                    window.location.href = redirect;
                                },2500);
                            }
                        },
                        error: function () {
                            Swal.fire({
                                title: failedTitle,
                                text: failedMsg,
                                icon: "error",
                                confirmButtonColor: (typeof window.SwalConfirmButtonColor !== 'undefined') ? window.SwalConfirmButtonColor : "var(--primary)",
                                confirmButtonText: (typeof window.SwalConfirmButtonText !== 'undefined') ? window.SwalConfirmButtonText : Lang("OK"),
                                cancelButtonColor: (typeof window.SwalCancelButtonColor !== 'undefined') ? window.SwalCancelButtonColor : "var(--dark)",
                                cancelButtonText: (typeof window.SwalCancelButtonText !== 'undefined') ? window.SwalCancelButtonText : Lang("Cancel"),
                            });
                        }
                    });
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
                        success: function () {
                            ReloadDT();
                            toastr.success(completeMsg);
                            if (redirect) {
                                setTimeout(function(){
                                    window.location.href = redirect;
                                },2500);
                            }
                        },
                        error: function () {
                            Swal.fire({
                                title: failedTitle,
                                text: failedMsg,
                                icon: "error",
                                confirmButtonColor: (typeof window.SwalConfirmButtonColor !== 'undefined') ? window.SwalConfirmButtonColor : "var(--primary)",
                                confirmButtonText: (typeof window.SwalConfirmButtonText !== 'undefined') ? window.SwalConfirmButtonText : Lang("OK"),
                                cancelButtonColor: (typeof window.SwalCancelButtonColor !== 'undefined') ? window.SwalCancelButtonColor : "var(--dark)",
                                cancelButtonText: (typeof window.SwalCancelButtonText !== 'undefined') ? window.SwalCancelButtonText : Lang("Cancel"),
                            });
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
    window.DeleteModel({
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
};

/**
 *
 *  Load links in modal
 *
 */

window.LoadInModal = function (url) {
    var modalEl;
    $.get(url, function (data) {
        modalEl = $('#global_modal');
        modalEl.find('.modal-content').html(data);
        modalEl.modal('show');
        if (modalEl.find('[data-modal-init').length == 1) {
            let callback = modalEl.find('[data-modal-init]').data('modal-init');
            var fn = window[callback];
            if (typeof fn === 'function') {
                fn(modalEl);
            }
        }
    });
};

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
};