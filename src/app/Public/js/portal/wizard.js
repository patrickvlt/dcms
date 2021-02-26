"use strict";
let KTWizard4 = function () {
    let t, e, i, o = [];
    return {
        init: function () {
            t = KTUtil.getById("kt_wizard"), e = KTUtil.getById("kt_form"), (i = new KTWizard(t, {
                startStep: 1,
                clickableSteps: !1
            })).on("change", (function (t) {
                if (!(t.getStep() > t.getNewStep())) {
                    let e = o[t.getStep() - 1];
                    return e && e.validate().then((function (e) {
                        "Valid" == e ? (t.goTo(t.getNewStep()), KTUtil.scrollTop()) : KTUtil.scrollTop()
                    })), !1
                }
            })), i.on("changed", (function (t) {
                KTUtil.scrollTop()
            })), i.on("submit", (function (t) {

            })), o.push(FormValidation.formValidation(e, {
                fields: {

                },
                plugins: {
                    trigger: new FormValidation.plugins.Trigger,
                    bootstrap: new FormValidation.plugins.Bootstrap({
                        eleValidClass: ""
                    })
                }
            })), o.push(FormValidation.formValidation(e, {
                fields: {

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

let boxCount = 10;

let emptyColumnDiv = document.querySelector('[data-column]').outerHTML;
let emptyRuleDiv = document.querySelector('[data-validation-rule]').outerHTML;
let emptyKtColumnDiv = document.querySelector('[data-kt-column]').outerHTML;
let emptyjExcelColumnDiv = document.querySelector('[data-jExcel-column]').outerHTML;

$('[data-kt-column]').remove();
$('[data-jExcel-column]').remove();
let changedColumns = false;

// DCMS Model Form
// When the user is at step 2, generate the column inputs
$(document).on('click', '[data-wizard-type="action-next"], [data-wizard-type="action-prev"]', function () {
    if (document.querySelector('[data-wizard-step="2"][data-wizard-state="current"]')) {
        if (document.querySelector('#seedBox0').checked) {
            $('[data-seed-div]').show();
        } else {
            $('[data-seed-div]').hide();
        }
    }
});

$(document).on('click', '[data-column-control]', function (e) {
    let currentClasses = e.currentTarget.classList.value;
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
    boxCount++;
    document.querySelector('[data-column]').parentNode.insertAdjacentHTML('beforeend', emptyColumnDiv.replace(/Box[0-9]/g,"Box"+boxCount));
    $(e.currentTarget.parentNode.parentNode).hide();
    window.DCMS.slimSelect();
});

$(document).on('click', '[data-delete-column]', function (e) {
    changedColumns = true;
    e.currentTarget.parentNode.parentNode.parentNode.remove();
    window.DCMS.slimSelect();
});

$(document).on('keyup', '[name="name"]', function (e) {
    let columnDiv = e.currentTarget.parentNode.parentNode.parentNode;
    let columnName = e.currentTarget.value.replace(/[^a-zA-Z\_]/gm, '');
    e.currentTarget.value = e.currentTarget.value.replace(/[^a-zA-Z\_]/gm, '');
    let columnNameEl = columnDiv.querySelector('[data-column-name]');
    columnNameEl.innerHTML = columnName;
    columnNameEl.dataset.columnName = columnName;

    changedColumns = true;
});

$(document).on('change', '[name="foreign"]', function (e) {
    let inputDiv = e.currentTarget.parentNode.parentNode.parentNode.parentNode.querySelector('[data-input-div]');
    let relationDiv = e.currentTarget.parentNode.parentNode.parentNode.parentNode.querySelector('[data-relation-div]');
    let inputType = inputDiv.querySelector('[name=inputType]');
    let inputDataType = inputDiv.querySelector('[name=inputDataType]');

    let seedDiv = e.currentTarget.parentNode.parentNode.parentNode.parentNode.querySelector('[data-seed-div]');
    let seedDataDiv = seedDiv.querySelector('[data-seed-input]');
    let seedAutoDiv = seedDiv.querySelector('[data-seed-auto]');

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
    let inputDiv = e.currentTarget.parentNode.parentNode;
    let inputDataType = inputDiv.querySelector('[name=inputDataType]');
    let inputDataTypeDiv = inputDiv.querySelector('[data-input-datatype-div]');

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
    let inputDiv = e.currentTarget.parentNode.parentNode;
    let inputType = inputDiv.querySelector('[name=inputType]');
    let filepondDiv = inputDiv.querySelector('[data-filepond-div]');

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
    let validationDiv = e.currentTarget.parentNode.parentNode.parentNode.parentNode.querySelector('[data-validation-div]');
    let addRuleDiv = e.currentTarget.parentNode.parentNode.parentNode.parentNode.querySelector('[data-add-rule-div]');
    if (e.currentTarget.checked) {
        $(validationDiv).show();
        $(addRuleDiv).show();
    } else {
        $(validationDiv).hide();
        $(addRuleDiv).hide();
    }
});

$(document).on('click', '[data-add-rule]', function (e) {
    let validationDiv = e.currentTarget.parentNode.parentNode.querySelector('[data-validation-div]');
    boxCount++;
    validationDiv.insertAdjacentHTML('beforeend', emptyRuleDiv.replace(/Box[0-9]/g,"Box"+boxCount));
});

$(document).on('click', '[data-delete-rule]', function (e) {
    $(e.currentTarget.parentNode.parentNode).remove();
});

// Check if theres empty columns
function existingColumns() {
    let existingColumns = true;
    // If on step 3
    $.each($("[data-column-name]"), function (x, column) {
        if (column.innerHTML == "") {
            existingColumns = false;
        }
    });
    return existingColumns;
}

// Inserting kt column divs function
let ktColumnProperties;
function InsertKTColumns() {
    if (document.querySelector('[data-kt-column]')) {
        $.each(document.querySelector('[data-kt-column]').parentNode.querySelectorAll('[data-kt-column]'), function (x, ktColumn) {
            $(ktColumn).remove();
        });
    }
    $.each($("[data-column-name]"), function (x, columnEl) {
        let columnName = columnEl.innerHTML.replace(/[^a-zA-Z\_]/gm, '');
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
        boxCount++;
        document.querySelector('[data-kt-columns]').insertAdjacentHTML('beforeend', ktColumnProperties.replace(/Box[0-9]/g,"Box"+boxCount));
    });
    window.DCMS.slimSelect();
}

function InsertjExcelColumns() {
    let jExcelProperties;
    $.each($("[data-jexcel-column]"), function (x, jExcelColumn) {
        $(jExcelColumn).remove();
    });
    $.each($("[data-column-name]"), function (x, columnEl) {
        let columnName = columnEl.innerHTML.replace(/[^a-zA-Z\_]/gm, '');
        boxCount++;
        jExcelProperties = emptyjExcelColumnDiv.replace(/jExcel/gm, columnName).replace(/Box[0-9]/g,"Box"+boxCount);
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
        boxCount++;
        document.querySelector('[data-jexcel-columns]').insertAdjacentHTML('beforeend', jExcelProperties.replace(/Box[0-9]/g,"Box"+boxCount));
    });
    if ($("#jExcel_enableImportsBox0").prop('checked')) {
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
    let ktTypeDiv = $(e.currentTarget).parent().parent().parent().parent().find('[data-kt-type-div]');
    let ktTitleDiv = $(e.currentTarget).parent().parent().parent().parent().find('[data-kt-title-div]');
    let ktKeyDiv = $(e.currentTarget).parent().parent().parent().parent().find('[data-kt-key-div]');
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
    let jExcelTypeDiv = $(e.currentTarget).parent().parent().parent().find('[data-jexcel-type-div]');
    let jExcelTitleDiv = $(e.currentTarget).parent().parent().parent().find('[data-jexcel-title-div]');
    let jExcelKeyDiv = $(e.currentTarget).parent().parent().parent().find('[data-jexcel-key-div]');
    let jExcelTextDiv = $(e.currentTarget).parent().parent().parent().find('[data-jexcel-text-div]');

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
    let amountToSeed = $('[data-seed-amount]');
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
    let formData = new FormData();

    formData.append('name', $('[name="model"]').val());
    formData.append('seed', $('[name="seed"]').val());
    formData.append('amountToSeed', $('[name="amountToSeed"]').val());

    // Add responses as array
    let responseGroups = ['created', 'updated', 'deleted'];
    let responses = {};
    let thisResponse = {};
    $.each(responseGroups, function (y, thisResponseGroup) {
        $.each($('[data-wizard-step="1"] [data-response-' + thisResponseGroup + ']'), function (x, response) {
            thisResponse = {};
            $.each($(response).find('[name]'), function (y, responseProperty) {
                thisResponse[responseProperty.name] = responseProperty.value;
            });
            responses[thisResponseGroup] = thisResponse;
        });
    });
    formData.append('responses', JSON.stringify(responses));

    // Add views as array
    let views = {};
    $.each($('[data-wizard-step="1"] [data-view]'), function (x, viewProperty) {
        views[viewProperty.name] = viewProperty.value;
    });
    formData.append('views', JSON.stringify(views));

    // Add columns with defined options/properties
    let columns = {};
    let thisColumn = {};
    $.each($('[data-wizard-step="2"] [data-column]'), function (x, column) {
        let rules = {};
        thisColumn = {};
        $.each($(column).find('[name]'), function (y, columnElement) {
            if (columnElement.type == 'checkbox') {
                if (columnElement.checked && columnElement.style.display !== 'none') {
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
    let ktColumns = {};
    let thisKtColumn = {};
    $.each($('[data-wizard-step="3"] [data-kt-column]'), function (x, column) {
        let isEnabled = $(column).find('[data-kt-checkbox]')[0].checked;
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
    let jExcelColumns = {};
    let thisjExcelColumn = {};
    $.each($('[data-wizard-step="3"] [data-jexcel-column]'), function (x, column) {
        let isEnabled = $(column).find('[data-jexcel-checkbox]')[0].checked;
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
    let jExcelResponses = {};
    let thisjExcelResponse = {};
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
    window.DCMS.request(formMethod, formAction, formData, {
        customBefore: () => {
            window.DCMS.disableSubmit('button[data-wizard-type="action-submit"]');
        },
        customBeforeError: () => {
            window.DCMS.enableSubmit('button[data-wizard-type="action-submit"]');
        },
        customBeforeSuccess: () => {
            window.DCMS.enableSubmit('button[data-wizard-type="action-submit"]');
        },
    });
});
