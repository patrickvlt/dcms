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
window.language = 'nl';

// Either use locale provided by server, or manually specified language
window.language = (locale) ? locale : window.language;

// TinyMCE
window.langFiles = '/js/dcms/tinymce_lang/'+window.language+'.js';
window.tinyMCEplugins = 'link';
window.tinyMCEtoolbar = 'insert';

// Filepond
window.FilePondMaxFileSize = 1; //in MB
window.FilePondMaxFileSize = (maxSizeServer) ? maxSizeServer : window.MaxFileSize;
window.FilePondAllowRevert = true;
window.FilePondInstantUpload = true;
// window.FilePondProcessRoute = ""
// window.FilePondRevertRoute = ""

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
    if (window.SweetAlert == true || window.JQAlerts == true) {
        require('./plugins/alerts.js');
    }
    require('./plugins/filepond.js');
    require('./plugins/slimselect.js');
    require('./plugins/tinymce.js');
    require('./plugins/datepicker.js');
    require('./plugins/jexcel.js');
    require('./plugins/spotlight.js');
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


window.onload = function () {
    let ajaxForms = document.querySelectorAll('[data-submit=ajax]')
    function SubmitAjax(e) {
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
            })
        }
        let formRequest = HttpReq(formMethod, formAction, formData);
    }
    ajaxForms.forEach(element =>
        element.addEventListener('submit', function (e) {
            e.preventDefault();
            SubmitAjax(e);
        }));
}


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

$(document).on('click', 'form [data-action=destroy]', function (e) {
    e.preventDefault();
    let element = e.currentTarget;
    let id = element.dataset.id;
    let route = element.dataset.destroyRoute.replace('__id__', id);
    let redirect = (element.dataset.destroyRedirect) ? element.dataset.destroyRedirect : false;
    DeleteModel({
        id: id,
        route: route,
        confirmTitle: (element.dataset.deleteConfirmTitle) ? Lang(element.dataset.deleteConfirmTitle) : Lang('Delete object'),
        confirmMsg: (element.dataset.deleteConfirmMessage) ? Lang(element.dataset.deleteConfirmMessage) : Lang('Are you sure you want to delete this object?'),
        completeTitle: (element.dataset.deleteCompleteTitle) ? Lang(element.dataset.deleteCompleteTitle) : Lang('Deleted object'),
        completeMsg: (element.dataset.deleteCompleteMessage) ? Lang(element.dataset.deleteCompleteMessage) : Lang('The object has been succesfully deleted.'),
        failedTitle: (element.dataset.deleteFailedTitle) ? Lang(element.dataset.deleteFailedTitle) : Lang('Deleting failed'),
        failedMsg: (element.dataset.deleteFailedMessage) ? Lang(element.dataset.deleteFailedMessage) : Lang('This object can\'t be deleted. It might still be required somewhere.'),
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