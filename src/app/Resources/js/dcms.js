/**
*
*  Plugin Settings
*
*/

// Alerts - pick one
window.JQAlerts = true;
window.SweetAlert = false;

// Date Format
window.AppDateFormat = "dd-mm-yyyy";

// Datatable Settings
window.AllowNewTab = false;

// Locale
window.language = 'en';

// Either use locale provided by server, or manually specified language
window.language = (locale) ? locale : window.language;

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

// There are no translations
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
 *  Plugins
 *
 */

try {
    require('./plugins/toastr.js');
    if (window.SweetAlert == true || window.JQAlerts == true) {
        require('./plugins/alerts.js');
    }
    require('./plugins/carousel.js');
    require('./plugins/slimselect.js');
    require('./plugins/tinymce.js');
    require('./plugins/datepicker.js');
    require('./plugins/jexcel.js');
    require('./plugins/spotlight.js');
    require('./plugins/filepond.js');
    // require('./plugins/jspdftable.js');
    require('./metronic/dcmsdatatable.js');
} catch (error) {
    console.log(error);
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
            Alert('success', Lang((response['title']) ? response['title'] : 'Succesfully created'), Lang((response['message']) ? response['message'] : 'Your request was successful.'), {
                confirm: {
                    text: Lang('Ok'),
                    btnClass: 'btn-success',
                    action: function () {
                        if (response.url) {
                            window.location.href = response.url;
                        }
                    }
                },
            });
        },
        error: function (response) {
            reply = response.responseJSON;
            if (reply['errors']) {
                let errorString = '';
                $.each(reply['errors'], function (key, error) {
                    errorString = errorString + error[0].replace(':', '.') + "<br>";
                });
                Alert('error', Lang(reply['message']), errorString, {
                    confirm: {
                        text: Lang('Ok'),
                        btnClass: 'btn-danger',
                    }
                });
            }
        },
        complete: function () {
            EnableSubmit();
        }
    });
}

document.addEventListener("DOMContentLoaded", function(){
    let ajaxForms = document.querySelectorAll('[data-dcms-action=ajax]')
    function SubmitAjax(e) {
        tinyMCE.triggerSave();
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
});


/**
 *
 *  Delete from a table or formTitle
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

    Alert('warning', confirmTitle, confirmMsg, {
        confirm: {
            text: Lang('OK'),
            btnClass: 'btn-warning',
            action: function () {
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
                            Alert('success', completeTitle, completeMsg, {
                                confirm: {
                                    text: Lang('Ok'),
                                    btnClass: 'btn-success',
                                }
                            });
                        } else {
                            Alert('error', failedTitle, failedMsg, {
                                confirm: {
                                    text: Lang('Ok'),
                                    btnClass: 'btn-danger',
                                }
                            });
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
                                Alert('success', completeTitle, completeMsg, {
                                    confirm: {
                                        text: Lang('Ok'),
                                        btnClass: 'btn-success',
                                        action: function() {
                                            if (redirect !== '') {
                                                if (window.AllowNewTab == false) {
                                                    window.location.href = redirect;
                                                } else {
                                                    window.open(redirect, '_blank');
                                                }
                                            }
                                        }
                                    }
                                });
                            },
                            error: function () {
                                Alert('error', failedTitle, failedMsg, {
                                    confirm: {
                                        text: Lang('Ok'),
                                        btnClass: 'btn-danger',
                                    }
                                });
                            }
                        });
                    }
                }
            }
        },
        cancel: {
            text: Lang('Cancel'),
            btnClass: 'btn-dark',
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