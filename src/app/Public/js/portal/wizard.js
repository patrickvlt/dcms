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

var emptyColumnDiv = document.querySelector('[data-column]').outerHTML;
var emptyRuleDiv = document.querySelector('[data-validation-rule]').outerHTML;

var emptyKtColumnDiv = document.querySelector('[data-kt-column]').outerHTML;
$('[data-kt-column]').remove();
var emptyjExcelColumnDiv = document.querySelector('[data-jExcel-column]').outerHTML;
$('[data-jExcel-column]').remove();
var changedColumns = false;

// DCMS Model Form
// When the user is at step 2, generate the column inputs
$(document).on('click', '[data-wizard-type="action-next"], [data-wizard-type="action-prev"]', function () {
    if (document.querySelector('[data-wizard-step="2"][data-wizard-state="current"]')) {
        if (document.querySelector('#seedBox0').checked){
            $('[data-seed-div]').show();
        } else {
            $('[data-seed-div]').hide();
        }
    }
});

$(document).on('click', '[data-column-control]', function (e) {
    var currentClasses = e.currentTarget.classList.value;
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
    var columnDiv = e.currentTarget.parentNode.parentNode.parentNode;
    var columnName = e.currentTarget.value.replace(/[^a-zA-Z\_]/gm, '');
    e.currentTarget.value = e.currentTarget.value.replace(/[^a-zA-Z\_]/gm, '');
    var columnNameEl = columnDiv.querySelector('[data-column-name]');
    columnNameEl.innerHTML = columnName;
    columnNameEl.dataset.columnName = columnName;

    changedColumns = true;
});

$(document).on('change', '[name="foreign"]', function (e) {
    var inputDiv = e.currentTarget.parentNode.parentNode.parentNode.parentNode.querySelector('[data-input-div]');
    var relationDiv = e.currentTarget.parentNode.parentNode.parentNode.parentNode.querySelector('[data-relation-div]');
    var inputType = inputDiv.querySelector('[name=inputType]');
    var inputDataType = inputDiv.querySelector('[name=inputDataType]');

    var seedDiv = e.currentTarget.parentNode.parentNode.parentNode.parentNode.querySelector('[data-seed-div]');
    var seedDataDiv = seedDiv.querySelector('[data-seed-input]');
    var seedAutoDiv = seedDiv.querySelector('[data-seed-auto]');

    if (e.currentTarget.checked) {
        $(inputDiv).hide();
        $(relationDiv).show();
        $(seedDataDiv).hide();
        $(seedAutoDiv).show();
        inputType.selectedIndex = 2;
        inputDataType.selectedIndex = 4;
        inputType.dispatchEvent(new Event("change"));
        inputDataType.dispatchEvent(new Event("change"));
    } else {
        $(inputDiv).show();
        $(relationDiv).hide();
        $(seedDataDiv).show();
        $(seedAutoDiv).hide();
        inputType.selectedIndex = 0;
        inputDataType.selectedIndex = 0;
        inputType.dispatchEvent(new Event("change"));
        inputDataType.dispatchEvent(new Event("change"));
    }

});

$(document).on('change', '[name="inputType"]', function (e) {
    var inputDiv = e.currentTarget.parentNode.parentNode;
    var inputDataType = inputDiv.querySelector('[name=inputDataType]');
    var inputDataTypeDiv = inputDiv.querySelector('[data-input-datatype-div]');

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

$(document).on('change', '[name="inputDataType"]', function (e) {
    var inputDiv = e.currentTarget.parentNode.parentNode;
    var inputType = inputDiv.querySelector('[name=inputType]');
    var filepondDiv = inputDiv.querySelector('[data-filepond-div]');

    switch (e.currentTarget.value) {
        case 'filepond':
            inputType.selectedIndex = 8;
            inputType.dispatchEvent(new Event("change"));
            $(filepondDiv).show();
            break;
            
        default:
            $(filepondDiv).hide();
            break;
    }
});

$(document).on('change', '[name="validation"]', function (e) {
    var validationDiv = e.currentTarget.parentNode.parentNode.parentNode.parentNode.querySelector('[data-validation-div]');
    var addRuleDiv = e.currentTarget.parentNode.parentNode.parentNode.parentNode.querySelector('[data-add-rule-div]');
    if (e.currentTarget.checked) {
        $(validationDiv).show();
        $(addRuleDiv).show();
    } else {
        $(validationDiv).hide();
        $(addRuleDiv).hide();
    }
});

$(document).on('click', '[data-add-rule]', function (e) {
    var validationDiv = e.currentTarget.parentNode.parentNode.querySelector('[data-validation-div]');
    validationDiv.insertAdjacentHTML('beforeend', emptyRuleDiv);
});

$(document).on('click', '[data-delete-rule]', function (e) {
    $(e.currentTarget.parentNode.parentNode).remove();
});

// Check if theres empty columns
function existingColumns(){
    var existingColumns = true;
    // If on step 3
    $.each($("[data-column-name]"), function (x, column) { 
        if (column.innerHTML == ""){
            existingColumns = false;
        }
    });
    return existingColumns;
}

// Inserting kt column divs function
var ktColumnProperties;
function InsertKTColumns() {
    if (document.querySelector('[data-kt-column]')) {
        $.each(document.querySelector('[data-kt-column]').parentNode.querySelectorAll('[data-kt-column]'), function (x, ktColumn) {
            $(ktColumn).remove();
        });
    }
    $.each($("[data-column-name]"), function (x, columnEl) {
        var columnName = columnEl.innerHTML.replace(/[^a-zA-Z\_]/gm, '');
        ktColumnProperties = emptyKtColumnDiv.replace(/ktColumn/gm, columnName);
        if ($(columnEl).parent().find('[data-foreign-checkbox]')[0].checked) {
            ktColumnProperties = ktColumnProperties.replace(/<!--optional::Key-->/gm,
            `<!--begin::Input-->
            <div class="form-group fv-plugins-icon-container" data-kt-key-div style="display:none">
                <label>Value</label>
                <input type="text" class="form-control form-control-solid form-control-lg"
                    name="value" placeholder="id">
                <span class="form-text text-muted">Which field should be used as the value?</span>
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
        var columnName = columnEl.innerHTML.replace(/[^a-zA-Z\_]/gm, '');
        jExcelProperties = emptyjExcelColumnDiv.replace(/jExcel/gm, columnName);
        if ($(columnEl).parent().find('[data-foreign-checkbox]')[0].checked) {
            jExcelProperties = jExcelProperties.replace(/<!--optional::Key-->/gm,
            `<!--begin::Input-->
            <div class="form-group fv-plugins-icon-container" data-jexcel-key-div style="display:none">
                <label>Value</label>
                <input type="text" class="form-control form-control-solid form-control-lg"
                    name="value" placeholder="id">
                <span class="form-text text-muted">Which field should be used as the value?</span>
                <div class="fv-plugins-message-container"></div>
            </div>
            <!--end::Input-->
            <!--begin::Input-->
            <div class="form-group fv-plugins-icon-container" data-jexcel-text-div style="display:none">
                <label>Text</label>
                <input type="text" class="form-control form-control-solid form-control-lg"
                    name="text" placeholder="name">
                <span class="form-text text-muted">Which field should be shown to the user?</span>
                <div class="fv-plugins-message-container"></div>
            </div>
            <!--end::Input-->`);
        }
        document.querySelector('[data-jexcel-columns]').insertAdjacentHTML('beforeend', jExcelProperties);
    });
    if ($("#jExcel_enableImportsBox0").prop('checked')){
        $('[data-jexcel-columns]').show();
    } else {
        $('[data-jexcel-columns]').hide();
    }
}

// When the user is at step 3, generate the available columns for KT Datatable
$(document).on('click', '[data-wizard-type="action-next"], [data-wizard-type="action-prev"]', function () {
    if (document.querySelector('[data-wizard-step="3"][data-wizard-state="current"]') && existingColumns() && changedColumns) {
        InsertKTColumns();
        InsertjExcelColumns();
        changedColumns = false;
    }
});

// When the user wants to include a column in the datatable, show the related inputs
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

// When the user enables a column for jExcel, generate the related inputs
$(document).on('change', '[data-jexcel-checkbox]', function (e) {
    var jExcelTypeDiv = $(e.currentTarget).parent().parent().parent().parent().find('[data-jexcel-type-div]');
    var jExcelTitleDiv = $(e.currentTarget).parent().parent().parent().parent().find('[data-jexcel-title-div]');
    var jExcelKeyDiv = $(e.currentTarget).parent().parent().parent().parent().find('[data-jexcel-key-div]');
    var jExcelTextDiv = $(e.currentTarget).parent().parent().parent().parent().find('[data-jexcel-text-div]');
    if (e.currentTarget.checked) {
        $(jExcelTypeDiv).show();
        $(jExcelTitleDiv).show();
        if ($(jExcelKeyDiv) && $(jExcelTextDiv)) {
            $(jExcelKeyDiv).show();
            $(jExcelTextDiv).show();
        }
    } else {
        $(jExcelTypeDiv).hide();
        $(jExcelTitleDiv).hide();
        if ($(jExcelKeyDiv) && $(jExcelTextDiv)) {
            $(jExcelKeyDiv).hide();
            $(jExcelTextDiv).hide();
        }
    }
});

// When the user is at step 3 and enables imports, generate the jExcel columns
$(document).on('change', '[name="enableImports"]', function (e) {
    if (e.currentTarget.checked && existingColumns()) {
        InsertjExcelColumns();
        window.DCMS.iCheck();
        window.DCMS.slimSelect();
        $("[data-jexcel-responses]").show();
    } else {
        $.each($("[data-jexcel-column]"), function (x, jExcelColumn) {
            $(jExcelColumn).remove();
        });
        $("[data-jexcel-responses]").hide();
    }
});

// Show seeding column if the user enables them
$(document).on('change', '[name="seed"]', function (e) {
    var amountToSeed = $('[data-seed-amount]');
    if (e.currentTarget.checked) {
        console.log(1);
        $(amountToSeed).show();
    } else {
        console.log(2);
        $(amountToSeed).hide();
    }
});

// Generate the form data, merge some properties into arrays etc.
// This provides more readabilty server-side
function GenerateData() {
    var formData = new FormData();

    formData.append('name', $('[name="model"]').val());
    formData.append('seed', $('[name="seed"]').val());
    formData.append('amountToSeed', $('[name="amountToSeed"]').val());

    // Add responses as array
    var responseGroups = ['created','updated','deleted'];
    var responses = {};
    var thisResponse = {};
    $.each(responseGroups, function (y, thisResponseGroup) { 
        $.each($('[data-wizard-step="1"] [data-response-'+thisResponseGroup+']'), function (x, response) {
            thisResponse = {};
            $.each($(response).find('[name]'), function (y, responseProperty) {
                thisResponse[responseProperty.name] = responseProperty.value;
            });
            responses[thisResponseGroup] = thisResponse;
        });  
    });
    formData.append('responses', JSON.stringify(responses));

    // Add views as array
    var views = {};
    $.each($('[data-wizard-step="1"] [data-view]'), function (x, viewProperty) {
        views[viewProperty.name] = viewProperty.value;
    });
    formData.append('views', JSON.stringify(views));

    // Add columns with defined options/properties
    var columns = {};
    var thisColumn = {};
    $.each($('[data-wizard-step="2"] [data-column]'), function (x, column) {
        var rules = {};
        thisColumn = {};
        $.each($(column).find('[name]'), function (y, columnElement) {
            if (columnElement.type == 'checkbox'){
                if (columnElement.checked && columnElement.style.display !== 'none'){
                    thisColumn[columnElement.name] = columnElement.value;
                }
            } else if (columnElement.name !== 'rule') {
                thisColumn[columnElement.name] = columnElement.value;
            }
        });
        $.each($(column).find('[name="rule"]'), function (x, columnElement) { 
            rules[x] = columnElement.value;
        });
        thisColumn['rules'] = rules;
        columns[thisColumn['name']] = thisColumn;
    });
    formData.append('columns', JSON.stringify(columns));

    // Add kt columns
    var ktColumns = {};
    var thisKtColumn = {};
    $.each($('[data-wizard-step="3"] [data-kt-column]'), function (x, column) {
        var isEnabled = $(column).find('[data-kt-checkbox]')[0].checked;
        if (isEnabled) {
            thisKtColumn = {};
            thisKtColumn['name'] = $(column).data('kt-column-name');
            $.each($(column).find('[name]'), function (y, columnElement) {
                thisKtColumn[columnElement.name] = columnElement.value;
            });
            ktColumns[thisKtColumn['name']] = thisKtColumn;
        }
    });
    formData.append('ktColumns', JSON.stringify(ktColumns));

    // Add jExcel columns
    var jExcelColumns = {};
    var thisjExcelColumn = {};
    $.each($('[data-wizard-step="3"] [data-jexcel-column]'), function (x, column) {
        var isEnabled = $(column).find('[data-jexcel-checkbox]')[0].checked;
        if (isEnabled) {
            thisjExcelColumn = {};
            thisjExcelColumn['name'] = $(column).data('jexcel-column-name');
            $.each($(column).find('[name]'), function (y, columnElement) {
                thisjExcelColumn[columnElement.name] = columnElement.value;
            });
            jExcelColumns[thisjExcelColumn['name']] = thisjExcelColumn;
        }
    });
    formData.append('jExcelColumns', JSON.stringify(jExcelColumns));

    // Add jExcel responses
    var jExcelResponses = {};
    var thisjExcelResponse = {};
    $.each($('[data-wizard-step="3"] [data-jexcel-response]'), function (x, response) {
        thisjExcelResponse = {};
        $.each($(response).find('[name]'), function (y, responseElement) {
            thisjExcelResponse[responseElement.name] = responseElement.value;
        });
        jExcelResponses[thisjExcelResponse['name']] = thisjExcelResponse;
    });
    formData.append('jExcelResponses', JSON.stringify(jExcelResponses));

    return formData;
}

document.querySelector('[data-wizard-type="action-submit"]').addEventListener('click', function (event) {
    event.preventDefault();
    let formAction = event.target.form.action,
        formMethod = 'POST',
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