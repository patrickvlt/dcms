/**
 *
 *  AJAX Submits
 *
 */

window.DCMS.form = {};
window.DCMS.form.validationSelectors = [
    'name', 'data-id'
];
window.DCMS.form.alerts = true;
window.DCMS.form.errorBag = false;

// If the document has a simple div with .dcms-error-parent class, then the errors from the request
// will be appended to this div, provided window.DCMS.form.errorBag is set to true above
if (document.querySelector('.dcms-error-parent')) {
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
    window.DCMS.fillErrorBag = function (args) {
        if (window.DCMS.form.errorBag == true) {
            errorBagParent.appendChild(errorElement);
        }
        errorBagParent = document.querySelector('.dcms-error-parent');
        errorBagParent.style.display = 'block';
        errorBagTitle = errorBagParent.querySelector('.dcms-error-title');
        errorBagTitle.innerHTML = (typeof args.title !== 'undefined') ? args.title : Lang("An error has occurred");
        errorBag = errorBagParent.querySelector('.dcms-errors');
        errorBag.innerHTML = (typeof args.message !== 'undefined') ? args.message : Lang('An unknown error has occurred.') + "<br>" + Lang('Contact support if this problem persists.');
    };
}

window.DCMS.request = function (formMethod, formAction, formData, customSettings = null) {
    // Grab custom functions if these have been defined
    let customBefore = (customSettings) ? customSettings.customBefore : null;
    let customBeforeSuccess = (customSettings) ? customSettings.customBeforeSuccess : null;
    let customBeforeError = (customSettings) ? customSettings.customBeforeError : null;
    let customSuccess = (customSettings) ? customSettings.customSuccess : null;
    let customSuccessMessage = (customSettings) ? customSettings.customSuccessMessage : null;
    let customSuccessRedirect = (customSettings) ? customSettings.customSuccessRedirect : null;
    let customError = (customSettings) ? customSettings.customError : null;
    let customErrorTitle = (customSettings) ? customSettings.customErrorTitle : null;
    let customErrorMessage = (customSettings) ? customSettings.customErrorMessage : null;
    let customComplete = (customSettings) ? customSettings.customComplete : null;
    let customBeforeComplete = (customSettings) ? customSettings.customBeforeComplete : null;
    let dontDisableSubmit = (customSettings) ? customSettings.dontDisableSubmit : null;

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
    if (dontDisableSubmit == false || dontDisableSubmit == null) {
        window.DCMS.disableSubmit();
    }

    window.axios({
        method: formMethod,
        url: formAction,
        data: formData,
        responseType: 'json',
        headers: {
            'X-CSRF-TOKEN': document.querySelectorAll('meta[name=csrf-token]')[0].content,
            "Content-type": "application/x-www-form-urlencoded",
            'X-Requested-With': 'XMLHttpRequest',
        }
    }).then(function (response) {
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
            let swalMessage, swalRedirect;
            // Make message
            if (response.data.message) {
                swalMessage = Lang(response.data.message);
            } else if (customSuccessMessage) {
                swalMessage = Lang(customSuccessMessage);
            } else {
                swalMessage = Lang('Press OK to return to the overview.');
            }
            // Make redirect
            if (response.data.url) {
                swalRedirect = response.data.url;
            } else if (customSuccessRedirect) {
                swalRedirect = customSuccessRedirect;
            }
            if (window.DCMS.form.alerts == true || window.DCMS.form.errorBag == false) {
                window.toastr.success(swalMessage);
                if (swalRedirect) {
                    setTimeout(function () {
                        window.location.href = swalRedirect;
                    }, 2500);
                }
            }
        }
    }).catch(function (error) {
        if (customBeforeError) {
            if (typeof customBeforeError == 'string') {
                window[customBeforeError](error.response);
            } else {
                customBeforeError(error.response);
            }
        }
        if (customError) {
            if (typeof customError == 'string') {
                window[customError](error.response);
            } else {
                customError(error.response);
            }
        } else {
            let errors = error.response.data.errors;
            if (errors) {
                let errorString = '';
                for (let name in errors) {
                    let error = errors[name];
                    errorString = errorString + error[0].replace(':', '.') + "<br>";
                    for (let y in window.DCMS.form.validationSelectors) {
                        let selector = window.DCMS.form.validationSelectors[y];
                        let formElement = (document.querySelector(`[` + selector + `^="` + name + `"`)) ? document.querySelector(`[` + selector + `^="` + name + `"`) : null;
                        if (formElement) {
                            formElement.classList.add('is-invalid');
                        }
                    }
                }
                if (window.DCMS.form.alerts == true || window.DCMS.form.errorBag == false) {
                    Swal.fire({
                        title: Lang(error.response.data.message),
                        html: errorString,
                        confirmButtonColor: (typeof window.DCMS.sweetAlert.confirmButtonColor !== 'undefined') ? window.DCMS.sweetAlert.confirmButtonColor : "var(--primary)",
                        confirmButtonText: (typeof window.DCMS.sweetAlert.confirmButtonText !== 'undefined') ? window.DCMS.sweetAlert.confirmButtonText : Lang("OK"),
                        cancelButtonColor: (typeof window.DCMS.sweetAlert.cancelButtonColor !== 'undefined') ? window.DCMS.sweetAlert.cancelButtonColor : "var(--dark)",
                        cancelButtonText: (typeof window.DCMS.sweetAlert.cancelButtonText !== 'undefined') ? window.DCMS.sweetAlert.cancelButtonText : Lang("Cancel"),
                        icon: "error"
                    });
                }
                if (window.DCMS.form.alerts == false || window.DCMS.form.errorBag == true) {
                    window.fillErrorBag({
                        title: Lang(error.response.data.message),
                        message: errorString
                    });
                }
            } else {
                let swalTitle, swalMessage;
                // Make title
                if (customErrorTitle) {
                    swalTitle = Lang(customErrorTitle);
                } else {
                    swalTitle = Lang('Unknown error');
                }
                // Make message
                if (customErrorMessage) {
                    swalMessage = Lang(customErrorMessage);
                } else {
                    swalMessage = Lang('An unknown error has occurred.') + "<br>" + Lang('Contact support if this problem persists.');
                }
                if (window.DCMS.form.alerts == true || window.DCMS.form.errorBag == false) {
                    Swal.fire({
                        title: swalTitle,
                        html: swalMessage,
                        icon: "error",
                        confirmButtonColor: (typeof window.DCMS.sweetAlert.confirmButtonColor !== 'undefined') ? window.DCMS.sweetAlert.confirmButtonColor : "var(--primary)",
                        confirmButtonText: (typeof window.DCMS.sweetAlert.confirmButtonText !== 'undefined') ? window.DCMS.sweetAlert.confirmButtonText : Lang("OK"),
                        cancelButtonColor: (typeof window.DCMS.sweetAlert.cancelButtonColor !== 'undefined') ? window.DCMS.sweetAlert.cancelButtonColor : "var(--dark)",
                        cancelButtonText: (typeof window.DCMS.sweetAlert.cancelButtonText !== 'undefined') ? window.DCMS.sweetAlert.cancelButtonText : Lang("Cancel"),
                    });
                } else if (window.DCMS.form.alerts == false || window.DCMS.form.errorBag == true) {
                    window.DCMS.fillErrorBag({
                        title: swalTitle,
                        message: swalMessage
                    });
                }

            }
        }
        if (document.querySelector('.dcms-error-parent') && window.DCMS.form.errorBag == true) {
            window.DCMS.scrollIntoView('.dcms-error-parent', -85);
        }
    }).then(function (response) {
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
            window.DCMS.enableSubmit();
        }
    });
};

let ajaxForms = document.querySelectorAll('[data-dcms-action=ajax]');
function submitAjax(e) {
    if (typeof tinymce !== 'undefined') {
        window.tinyMCE.triggerSave();
    }
    let formAction = e.target.action;
    let formMethod = e.target.method;
    let formData = new FormData(e.target);
    if (document.querySelectorAll('.filepond--file').length > 0) {
        let loopedNames = [];
        let namesToLoop = [];
        Array.from(window.DCMS.fileArray).forEach(function (fileWindow) {
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
    window.DCMS.request(formMethod, formAction, formData);
}
ajaxForms.forEach(element =>
    element.addEventListener('submit', function (e) {
        e.preventDefault();
        submitAjax(e);
    }));

/**
 *
 *  Delete a Laravel object dynamically, from a datatable or form
 *
 */

window.DCMS.deleteModel = function (args) {
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
        confirmButtonColor: (typeof window.DCMS.sweetAlert.confirmButtonColor !== 'undefined') ? window.DCMS.sweetAlert.confirmButtonColor : "var(--primary)",
        confirmButtonText: (typeof window.DCMS.sweetAlert.confirmButtonText !== 'undefined') ? window.DCMS.sweetAlert.confirmButtonText : Lang("OK"),
        cancelButtonColor: (typeof window.DCMS.sweetAlert.cancelButtonColor !== 'undefined') ? window.DCMS.sweetAlert.cancelButtonColor : "var(--dark)",
        cancelButtonText: (typeof window.DCMS.sweetAlert.cancelButtonText !== 'undefined') ? window.DCMS.sweetAlert.cancelButtonText : Lang("Cancel"),
    }).then(function (result) {
        if (result.value) {
            if (id != null) {
                if (isArray(id)) {
                    window.axios({
                        method: 'DELETE',
                        url: route,
                        data: {
                            deleteIDs: id
                        },
                        responseType: 'json',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelectorAll('meta[name=csrf-token]')[0].content,
                            "Content-type": "application/x-www-form-urlencoded",
                            'X-Requested-With': 'XMLHttpRequest',
                        }
                    }).then(function (response) {
                        window.DCMS.reloadKTDatatables();
                        window.toastr.success(completeMsg);
                        if (redirect) {
                            setTimeout(function () {
                                window.location.href = redirect;
                            }, 2500);
                        }
                    }).catch(function (error) {
                        Swal.fire({
                            title: failedTitle,
                            text: failedMsg,
                            icon: "error",
                            confirmButtonColor: (typeof window.DCMS.sweetAlert.confirmButtonColor !== 'undefined') ? window.DCMS.sweetAlert.confirmButtonColor : "var(--primary)",
                            confirmButtonText: (typeof window.DCMS.sweetAlert.confirmButtonText !== 'undefined') ? window.DCMS.sweetAlert.confirmButtonText : Lang("OK"),
                            cancelButtonColor: (typeof window.DCMS.sweetAlert.cancelButtonColor !== 'undefined') ? window.DCMS.sweetAlert.cancelButtonColor : "var(--dark)",
                            cancelButtonText: (typeof window.DCMS.sweetAlert.cancelButtonText !== 'undefined') ? window.DCMS.sweetAlert.cancelButtonText : Lang("Cancel"),
                        });
                    });
                } else {
                    window.axios({
                        method: 'DELETE',
                        url: route,
                        responseType: 'json',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelectorAll('meta[name=csrf-token]')[0].content,
                            "Content-type": "application/x-www-form-urlencoded",
                            'X-Requested-With': 'XMLHttpRequest',
                        }
                    }).then(function (response) {
                        window.DCMS.reloadKTDatatables();
                        window.toastr.success(completeMsg);
                        if (redirect) {
                            setTimeout(function () {
                                window.location.href = redirect;
                            }, 2500);
                        }
                    }).catch(function (error) {
                        Swal.fire({
                            title: failedTitle,
                            text: failedMsg,
                            icon: "error",
                            confirmButtonColor: (typeof window.DCMS.sweetAlert.confirmButtonColor !== 'undefined') ? window.DCMS.sweetAlert.confirmButtonColor : "var(--primary)",
                            confirmButtonText: (typeof window.DCMS.sweetAlert.confirmButtonText !== 'undefined') ? window.DCMS.sweetAlert.confirmButtonText : Lang("OK"),
                            cancelButtonColor: (typeof window.DCMS.sweetAlert.cancelButtonColor !== 'undefined') ? window.DCMS.sweetAlert.cancelButtonColor : "var(--dark)",
                            cancelButtonText: (typeof window.DCMS.sweetAlert.cancelButtonText !== 'undefined') ? window.DCMS.sweetAlert.cancelButtonText : Lang("Cancel"),
                        });
                    });
                }
            } else {
                Swal.fire({
                    title: Lang('Unknown error'),
                    html: Lang('An unknown error has occurred.') + "<br>" + Lang('Contact support if this problem persists.'),
                    icon: "error",
                    confirmButtonColor: (typeof window.DCMS.sweetAlert.confirmButtonColor !== 'undefined') ? window.DCMS.sweetAlert.confirmButtonColor : "var(--primary)",
                    confirmButtonText: (typeof window.DCMS.sweetAlert.confirmButtonText !== 'undefined') ? window.DCMS.sweetAlert.confirmButtonText : Lang("OK"),
                    cancelButtonColor: (typeof window.DCMS.sweetAlert.cancelButtonColor !== 'undefined') ? window.DCMS.sweetAlert.cancelButtonColor : "var(--dark)",
                    cancelButtonText: (typeof window.DCMS.sweetAlert.cancelButtonText !== 'undefined') ? window.DCMS.sweetAlert.cancelButtonText : Lang("Cancel"),
                });
            }
        }
    });
};

/**
 *
 *  Call delete method with a simple attribute
 *
 */

if (document.querySelector('[data-dcms-action=destroy]')) {
    document.querySelector('[data-dcms-action=destroy]').addEventListener('click', function (e) {
        e.preventDefault();
        let element = e.currentTarget;
        let id = element.dataset.dcmsId;
        let route = element.dataset.dcmsDestroyRoute.replace('__id__', id);
        let redirect = (element.dataset.dcmsDestroyRedirect) ? element.dataset.dcmsDestroyRedirect : false;
        window.DCMS.deleteModel({
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
}