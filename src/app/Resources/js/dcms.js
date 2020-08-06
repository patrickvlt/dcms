/**
 *
 *  AJAX Headers
 *
 */

const { extendWith, isArray } = require('lodash');

window.csrf = document.querySelectorAll('meta[name=csrf-token]')[0].content;


/**
 *
 *  Translations
 *
 */

// There are no translations
try {
    var lang = require('../../../../resources/lang/' + locale + '.json');
} catch (error) {
    console.log('No translation yet for: ' + locale);
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
    require('./plugins/alerts.js');
    require('./plugins/filepond.js');
    require('./plugins/slimselect.js');
    require('./plugins/tinymce.js');
    require('./plugins/datepicker.js');
    require('./plugins/jexcel.js');
    require('./plugins/spotlight.js');
    require('./plugins/jspdftable.js');
    require('./metronic/dcmsdatatable.js');
} catch (error) {
    console.log(error);
}

/**
*
*  Plugin Settings
*
*/

window.FilePondJQAlerts = true;
window.AppDateFormat = "DD/MM/YYYY";

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
        element.innerHTML = element.innerHTML.replace(spinner,'');
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
                    action: function(){
                        if (response.url){
                            window.location.href = response.url;
                        }
                    }
                },
            });
        },
        error: function(response) {
            reply = response.responseJSON;
            if (reply['errors']){
                let errorString = '';
                $.each(reply['errors'], function (key, error) {
                    errorString = errorString + error[0].replace(':','.') + "<br>";
                });
                Alert('error', Lang(reply['message']), errorString, {
                    confirm: {
                        text: Lang('Ok'),
                        btnClass: 'btn-danger',
                    }
                });
            }
        },
        complete: function(){
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
        if (document.querySelectorAll('.filepond--file').length > 0){
            let loopedNames = [];
            let namesToLoop = [];
            Array.from(window.fileArray).forEach(function(fileWindow){
                namesToLoop.push(fileWindow.input);
            });
            namesToLoop.forEach(function(name){
                if (!loopedNames.includes(name)){
                    loopedNames.push(name);
                }
            });
            loopedNames.forEach(function(name){
                let curInputs = document.getElementsByName(name);
                let curFiles = [];
                formData.delete(name);
                curInputs.forEach(function(input){
                    formData.append(name, input.value);
                })
            })
        }
        let formRequest = HttpReq(formMethod,formAction,formData);
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

    Alert('warning', confirmTitle, confirmMsg, {
        confirm: {
            text: Lang('OK'),
            btnClass: 'btn-warning',
            action: function () {
                if (id != null){
                    if (isArray(id)){
                        let success = true;
                        let deleteRows = $.each(id, function (key, x) { 
                            jQuery.ajax({
                                type: "POST",
                                async: false,
                                headers: {
                                    'X-CSRF-TOKEN': window.csrf
                                },
                                url: "/" + route + "/" + x,
                                data: {
                                    _method: "DELETE"
                                },
                                error: function () {
                                    success = false;
                                }
                            });
                        });
                        if (success == true){
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
                            url: "/" + route + "/" + id,
                            data: {
                                _method: "DELETE"
                            },
                            success: function (response) {
                                ReloadDT();
                                Alert('success', completeTitle, completeMsg, {
                                    confirm: {
                                        text: Lang('Ok'),
                                        btnClass: 'btn-success',
                                    }
                                });
                                ReloadDT();
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
*  Merge table columns
*
*/


window.MergeColumns = function(row, column){
    var value = '';
    if(column.split(',').length > 1){
        Array.from(column.split(',')).forEach(function(element){
            value = value + row[element] + ' ';
        });
    } else {
        value = row[column];
    }
    return value;
}