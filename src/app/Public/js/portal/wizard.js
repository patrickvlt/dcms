"use strict";
var KTWizard4 = function () {
    var t, e, i, o = [];
    return {
        init: function () {
            t = KTUtil.getById("kt_wizard"), e = KTUtil.getById("kt_form"), (i = new KTWizard(t, {
                startStep: 1,
                clickableSteps: !1
            })).on("change", (function (t) {
                if (!(t.getStep() > t.getNewStep())) {
                    var e = o[t.getStep() - 1];
                    return e && e.validate().then((function (e) {
                        "Valid" == e ? (t.goTo(t.getNewStep()), KTUtil.scrollTop()) : Swal.fire({
                            text: "Sorry, looks like there are some errors detected, please try again.",
                            icon: "error",
                            buttonsStyling: !1,
                            confirmButtonText: "Ok, got it!",
                            customClass: {
                                confirmButton: "btn font-weight-bold btn-light"
                            }
                        }).then((function () {
                            KTUtil.scrollTop()
                        }))
                    })), !1
                }
            })), i.on("changed", (function (t) {
                KTUtil.scrollTop()
            })), i.on("submit", (function (t) {

            })), o.push(FormValidation.formValidation(e, {
                fields: {
                    fname: {
                        validators: {
                            notEmpty: {
                                message: "First name is required"
                            }
                        }
                    },
                    lname: {
                        validators: {
                            notEmpty: {
                                message: "Last Name is required"
                            }
                        }
                    },
                    phone: {
                        validators: {
                            notEmpty: {
                                message: "Phone is required"
                            }
                        }
                    },
                    email: {
                        validators: {
                            notEmpty: {
                                message: "Email is required"
                            },
                            emailAddress: {
                                message: "The value is not a valid email address"
                            }
                        }
                    }
                },
                plugins: {
                    trigger: new FormValidation.plugins.Trigger,
                    bootstrap: new FormValidation.plugins.Bootstrap({
                        eleValidClass: ""
                    })
                }
            })), o.push(FormValidation.formValidation(e, {
                fields: {
                    address1: {
                        validators: {
                            notEmpty: {
                                message: "Address is required"
                            }
                        }
                    },
                    postcode: {
                        validators: {
                            notEmpty: {
                                message: "Postcode is required"
                            }
                        }
                    },
                    city: {
                        validators: {
                            notEmpty: {
                                message: "City is required"
                            }
                        }
                    },
                    state: {
                        validators: {
                            notEmpty: {
                                message: "State is required"
                            }
                        }
                    },
                    country: {
                        validators: {
                            notEmpty: {
                                message: "Country is required"
                            }
                        }
                    }
                },
                plugins: {
                    trigger: new FormValidation.plugins.Trigger,
                    bootstrap: new FormValidation.plugins.Bootstrap({
                        eleValidClass: ""
                    })
                }
            })), o.push(FormValidation.formValidation(e, {
                fields: {
                    ccname: {
                        validators: {
                            notEmpty: {
                                message: "Credit card name is required"
                            }
                        }
                    },
                    ccnumber: {
                        validators: {
                            notEmpty: {
                                message: "Credit card number is required"
                            },
                            creditCard: {
                                message: "The credit card number is not valid"
                            }
                        }
                    },
                    ccmonth: {
                        validators: {
                            notEmpty: {
                                message: "Credit card month is required"
                            }
                        }
                    },
                    ccyear: {
                        validators: {
                            notEmpty: {
                                message: "Credit card year is required"
                            }
                        }
                    },
                    cccvv: {
                        validators: {
                            notEmpty: {
                                message: "Credit card CVV is required"
                            },
                            digits: {
                                message: "The CVV value is not valid. Only numbers is allowed"
                            }
                        }
                    }
                },
                plugins: {
                    trigger: new FormValidation.plugins.Trigger,
                    bootstrap: new FormValidation.plugins.Bootstrap({
                        eleValidClass: ""
                    })
                }
            }))
        }
    }
}();

jQuery(document).ready((function () {
    KTWizard4.init();
}));


// DCMS Model Form
var inputDataType, inputDiv, inputType, inputDataTypeDiv, columnDiv, columnNameEl, columnName, currentClasses, emptyColumnDiv, emptyKtColumnDiv, emptyjExcelColumnDiv, changedColumns, keyDiv;

emptyColumnDiv = document.querySelector('[data-column]').outerHTML;
emptyKtColumnDiv = document.querySelector('[data-kt-column]').outerHTML;
$('[data-kt-column]').remove();
emptyjExcelColumnDiv = document.querySelector('[data-jExcel-column]').outerHTML;
$('[data-jExcel-column]').remove();
changedColumns = false;

$(document).on('click', '[data-column-control]', function (e) {
    currentClasses = e.currentTarget.classList.value;
    if (new RegExp(/fa-caret-down/gm).test(currentClasses)) {
        $(e.currentTarget).removeClass('fa-caret-down');
        $(e.currentTarget).addClass('fa-caret-right');
        $(e.currentTarget).parent().find('[data-column-properties]').hide();
    } else {
        $(e.currentTarget).removeClass('fa-caret-right');
        $(e.currentTarget).addClass('fa-caret-down');
        $(e.currentTarget).parent().find('[data-column-properties]').show();
    }
});

$(document).on('click', '[data-add-column]', function (e) {
    changedColumns = true;
    document.querySelector('[data-column]').parentNode.insertAdjacentHTML('beforeend', emptyColumnDiv);
    $(e.currentTarget.parentNode.parentNode).hide();
    window.DCMS.iCheck();
    window.DCMS.slimSelect();
});

$(document).on('click', '[data-delete-column]', function (e) {
    changedColumns = true;
    e.currentTarget.parentNode.parentNode.parentNode.remove();
    window.DCMS.iCheck();
    window.DCMS.slimSelect();
});

$(document).on('keyup', '[name="name"]', function (e) {
    columnDiv = e.currentTarget.parentNode.parentNode.parentNode;
    columnName = e.currentTarget.value.replace(/[^a-zA-Z\_]/gm, '');
    e.currentTarget.value = e.currentTarget.value.replace(/[^a-zA-Z\_]/gm, '');
    columnNameEl = columnDiv.querySelector('[data-column-name]');
    columnNameEl.innerHTML = columnName;
    columnNameEl.dataset.columnName = columnName;

    changedColumns = true;
});

$(document).on('change', '[name="foreign"]', function (e) {
    inputDiv = e.currentTarget.parentNode.parentNode.querySelector('[data-input-div]');
    keyDiv = e.currentTarget.parentNode.parentNode.querySelector('[data-key-div]');
    inputType = inputDiv.querySelector('[name=inputType]');
    inputDataType = inputDiv.querySelector('[name=inputDataType]');

    if (e.currentTarget.value == 'No') {
        $(inputDiv).show();
        inputType.selectedIndex = 0;
        inputDataType.selectedIndex = 0;
        inputType.dispatchEvent(new Event("change"));
        inputDataType.dispatchEvent(new Event("change"));
    } else {
        $(inputDiv).hide();
        inputType.selectedIndex = 2;
        inputDataType.selectedIndex = 4;
        inputType.dispatchEvent(new Event("change"));
        inputDataType.dispatchEvent(new Event("change"));
    }

});

$(document).on('change', '[name="inputType"]', function (e) {
    inputDiv = e.currentTarget.parentNode.parentNode;
    inputDataType = inputDiv.querySelector('[name=inputDataType]');
    inputDataTypeDiv = inputDiv.querySelector('[data-input-datatype-div]');

    $(inputDataTypeDiv).show();
    switch (e.currentTarget.value) {
        case 'checkbox':
            inputDataType.selectedIndex = 3;
            break;
        case 'dropdown':
            inputDataType.selectedIndex = 4;
            break;
        case 'datetime-local':
        case 'time':
        case 'date':
            inputDataType.selectedIndex = 1;
            break;
        case 'file':
            inputDataType.selectedIndex = 2;
            break;
        case 'textarea':
            inputDataType.selectedIndex = 5;
            break;

        default:
            $(inputDataTypeDiv).hide();
            break;
    }

    inputDataType.dispatchEvent(new Event("change"));
});

// Inserting kt column divs function
var ktColumnProperties;
function InsertKTColumns() {
    if (document.querySelector('[data-kt-column]')) {
        $.each(document.querySelector('[data-kt-column]').parentNode.querySelectorAll('[data-kt-column]'), function (x, ktColumn) {
            $(ktColumn).remove();
        });
    }
    $.each($("[data-column-name]"), function (x, columnEl) {
        columnName = columnEl.innerHTML.replace(/[^a-zA-Z\_]/gm, '');
        ktColumnProperties = emptyKtColumnDiv.replace(/ktColumn/gm, columnName);
        if ($(columnEl).parent().find('[name="foreign"]').val() == 'Yes') {
            ktColumnProperties = ktColumnProperties.replace(/<!--optional::Key-->/gm,
                `<!--begin::Input-->
            <div class="form-group fv-plugins-icon-container" data-kt-key-div style="display:none">
                <label>Related key</label>
                <input type="text" class="form-control form-control-solid form-control-lg"
                    name="ktColumn_column_key" placeholder="id">
                <span class="form-text text-muted">Which field to use from the related class?</span>
                <div class="fv-plugins-message-container"></div>
            </div>
            <!--end::Input-->`);
        }
        document.querySelector('[data-kt-columns]').insertAdjacentHTML('beforeend', ktColumnProperties);
    });
    window.DCMS.iCheck();
    window.DCMS.slimSelect();
}
function InsertjExcelColumns() {
    var jExcelProperties;
    $.each($("[data-jexcel-column]"), function (x, jExcelColumn) {
        $(jExcelColumn).remove();
    });
    $.each($("[data-column-name]"), function (x, columnEl) {
        columnName = columnEl.innerHTML.replace(/[^a-zA-Z\_]/gm, '');
        jExcelProperties = emptyjExcelColumnDiv.replace(/jExcel/gm, columnName);
        if ($(columnEl).parent().find('[name="foreign"]').val() == 'Yes') {
            jExcelProperties = jExcelProperties.replace(/<!--optional::Key-->/gm,
                `<!--begin::Input-->
            <div class="form-group fv-plugins-icon-container" data-kt-key-div style="display:none">
                <label>Related key</label>
                <input type="text" class="form-control form-control-solid form-control-lg"
                    name="jExcel_column_key" placeholder="id">
                <span class="form-text text-muted">Which field to use from the related class?</span>
                <div class="fv-plugins-message-container"></div>
            </div>
            <!--end::Input-->`);
        }
        document.querySelector('[data-jexcel-columns]').insertAdjacentHTML('beforeend', jExcelProperties);
    });
}
InsertKTColumns();

// Inserting kt table columns when changing steps
$(document).on('click', '[data-wizard-type="action-next"], [data-wizard-type="action-prev"]', function () {
    // If on step 3
    if (document.querySelector('[data-wizard-step="3"][data-wizard-state="current"]')) {
        InsertKTColumns();
        InsertjExcelColumns();
    }
});

// Enabling kt columns
$(document).on('change', '[data-kt-checkbox]', function (e) {
    var ktTypeDiv = $(e.currentTarget).parent().parent().parent().parent().find('[data-kt-type-div]');
    var ktTitleDiv = $(e.currentTarget).parent().parent().parent().parent().find('[data-kt-title-div]');
    var ktKeyDiv = $(e.currentTarget).parent().parent().parent().parent().find('[data-kt-key-div]');
    if (e.currentTarget.checked) {
        $(ktTypeDiv).show();
        $(ktTitleDiv).show();
        if ($(ktKeyDiv)) {
            $(ktKeyDiv).show();
        }
    } else {
        $(ktTypeDiv).hide();
        $(ktTitleDiv).hide();
        if ($(ktKeyDiv)) {
            $(ktKeyDiv).hide();
        }
    }
});

// Enabling imports
$(document).on('change', '[name="enableImports"]', function (e) {
    if (e.currentTarget.checked) {
        InsertjExcelColumns();
        window.DCMS.iCheck();
        window.DCMS.slimSelect();
    } else {
        $.each($("[data-jexcel-column]"), function (x, jExcelColumn) {
            $(jExcelColumn).remove();
        });
    }
});

function GenerateData() {
    var formData = new FormData();

    streetName = document.querySelector('[name=street_name]') ? document.querySelector('[name=street_name]').value : '';

    formData.append('street_name', streetName);

    return formData;
}

document.querySelector('[data-wizard-type="action-submit"]').addEventListener('click', function (event) {
    event.preventDefault();
    let formAction = event.target.form.action,
        formMethod = event.target.form.method,
        formData = GenerateData();

    window.HttpReq(formMethod, formAction, formData, {
        customBefore: () => {
            window.DisableSubmit('button[data-wizard-type="action-submit"]');
        },
        customBeforeError: () => {
            window.EnableSubmit('button[data-wizard-type="action-submit"]');
        },
        customBeforeSuccess: () => {
            window.EnableSubmit('button[data-wizard-type="action-submit"]');
        },
    });
});