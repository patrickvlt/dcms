"use strict";

if (typeof jexcel == 'undefined' && document.querySelectorAll('[data-type=jexcel]').length > 0 && (window.DCMS.config.plugins.jexcel && window.DCMS.config.plugins.jexcel.enable !== false)) {
    window.DCMS.loadCSS(window.DCMS.config.plugins.jexcel);
    window.DCMS.loadJS(window.DCMS.config.plugins.jexcel);
}
if (typeof jsuites == 'undefined' && document.querySelectorAll('[data-type=jexcel]').length > 0 && (window.DCMS.config.plugins.jsuites && window.DCMS.config.plugins.jsuites.enable !== false)) {
    window.DCMS.loadCSS(window.DCMS.config.plugins.jsuites);
    window.DCMS.loadJS(window.DCMS.config.plugins.jsuites);
}

window.DCMS.jExcelInit = function () {
    window.DCMS.hasLoaded(['jexcel'], function () {
        var sheetData, sheetDynColumns, currentForm, formRows, table, alertMsg;

        window.DCMS.jExcelTables = [];
        if (document.querySelectorAll('[data-type=jexcel]').length > 0) {
            require('./../translations/_jexcel.js');
            document.querySelectorAll('[data-type=jexcel]').forEach(function (htmlTable) {

                sheetData = '';
                sheetDynColumns = [];
                currentForm = document.querySelector(htmlTable.dataset.jexcelFormSelector);

                // fetch information from htmlTable headers
                Array.from(htmlTable.getElementsByTagName('th')).forEach(function (header) {
                    function ColumnPush(ajax = null) {
                        let dynColumn = {};

                        dynColumn.type = header.dataset.jexcelType;
                        if (ajax) {
                            dynColumn.source = ajax;
                        }
                        dynColumn.title = header.textContent;
                        dynColumn.width = header.dataset.jexcelWidth;
                        dynColumn.tableoverflow = true;
                        dynColumn.autocomplete = (header.dataset.jexcelAutocomplete == 'true') ? 'true' : 'false';
                        if (header.dataset.jExcelType == 'calendar') {
                            dynColumn.options = {
                                format: (header.dataset.jexcelDateFormat) ? header.dataset.jexcelDateFormat : window.AppDateFormat
                            };
                        }

                        sheetDynColumns.push(dynColumn);
                        header.hidden = true;
                    }
                    if (header.dataset.jexcelFetchUrl !== null && typeof header.dataset.jexcelFetchUrl !== 'undefined') {
                        window.axios({
                            method: 'GET',
                            url: header.dataset.jexcelFetchUrl,
                            responseType: 'json',
                            headers: {
                                'X-CSRF-TOKEN': window.DCMS.csrf,
                                "Content-type": "application/x-www-form-urlencoded",
                                'X-Requested-With': 'XMLHttpRequest',
                            }
                        }).then(function (response) {
                            ColumnPush(response.data);
                            if (sheetDynColumns.length == htmlTable.getElementsByTagName('th').length) {
                                MakeTable(htmlTable);
                            }
                        });
                    } else {
                        ColumnPush();
                        if (sheetDynColumns.length == htmlTable.getElementsByTagName('th').length) {
                            MakeTable(htmlTable);
                        }
                    }
                });
                function MakeTable(tableToMake) {
                    // construct table
                    let rows = parseInt(htmlTable.dataset.jexcelEmptyrows);
                    let dataFill = [];
                    for (let index = 0; index < rows; index++) {
                        dataFill.push("");
                    }
                    table = jexcel(tableToMake, {
                        columns: sheetDynColumns,
                        data: dataFill,
                        columnDrag: true,
                        colWidths: sheetDynColumns.map(function (el) { return (el.width) ? el.width : 100; }),
                        allowInsertColumn: false,
                        allowManualInsertColumn: false,
                        text: window.DCMS.jExcel.translations
                    });

                    if (currentForm) {
                        currentForm.style.display = 'block';
                    }

                    window.DCMS.jExcelTables.push(table);

                    function ClearInvalid(e) {
                        function CleanElement(element) {
                            if (element.classList.contains('invalid')) {
                                element.classList.remove('invalid');
                            }
                        }
                        formRows = Array.from(e.target.getElementsByClassName('jexcel_content')[0].getElementsByTagName('table')[0].getElementsByTagName('tbody')[0].getElementsByTagName('tr'));
                        Array.from(formRows).forEach(function (element) {
                            CleanElement(element);
                            Array.from(element.getElementsByTagName('td')).forEach(element => CleanElement(element));
                        });
                    }

                    if (currentForm) {
                        currentForm.addEventListener("submit", function (e) {
                            e.preventDefault();
                            ClearInvalid(e, true);
                            sheetData = table.getData();

                            window.axios({
                                method: 'POST',
                                url: e.target.action,
                                data: sheetData,
                                responseType: 'json',
                                headers: {
                                    'X-CSRF-TOKEN': window.DCMS.csrf,
                                    "Content-type": "application/x-www-form-urlencoded",
                                    'X-Requested-With': 'XMLHttpRequest',
                                }
                            }).then(function (response) {
                                window.toastr.success(response.data.response.message);
                                if (response.data.url) {
                                    setTimeout(function () {
                                        window.location.href = response.data.url;
                                    }, 2500);
                                }
                            }).catch(function (error) {
                                if (error.response.data.message == 'The given data was invalid.') {
                                    try {
                                        alertMsg = '';
                                        for (const z in error.response.data.errors) {
                                            alertMsg += error.response.data.errors[z][0] + "<br>";
                                            Array.from(document.querySelectorAll('tbody tr td:not(.jexcel_row)')).forEach(function (cell) {
                                                if (String(error[z]).toLowerCase().indexOf(cell.textContent.toLowerCase()) > -1 && cell.textContent !== "") {
                                                    cell.classList.add('invalid');
                                                }
                                            });
                                        }
                                        Swal.fire({
                                            title: Lang('Import failed'),
                                            html: alertMsg,
                                            icon: "error",
                                            confirmButtonColor: typeof (window.DCMS.sweetAlert.confirmButtonColor !== 'undefined') ? window.DCMS.sweetAlert.confirmButtonColor : "var(--primary)",
                                            confirmButtonText: typeof (window.DCMS.sweetAlert.confirmButtonText !== 'undefined') ? window.DCMS.sweetAlert.confirmButtonText : Lang("OK"),
                                            cancelButtonColor: typeof (window.DCMS.sweetAlert.cancelButtonColor !== 'undefined') ? window.DCMS.sweetAlert.cancelButtonColor : "var(--dark)",
                                            cancelButtonText: typeof (window.DCMS.sweetAlert.cancelButtonText !== 'undefined') ? window.DCMS.sweetAlert.cancelButtonText : Lang("Cancel"),
                                        });
                                    } catch (error) {
                                    }
                                } else {
                                    Swal.fire({
                                        title: error.response.data.response.title,
                                        html: error.response.data.response.message,
                                        icon: "error",
                                        confirmButtonColor: typeof (window.DCMS.sweetAlert.confirmButtonColor !== 'undefined') ? window.DCMS.sweetAlert.confirmButtonColor : "var(--primary)",
                                        confirmButtonText: typeof (window.DCMS.sweetAlert.confirmButtonText !== 'undefined') ? window.DCMS.sweetAlert.confirmButtonText : Lang("OK"),
                                        cancelButtonColor: typeof (window.DCMS.sweetAlert.cancelButtonColor !== 'undefined') ? window.DCMS.sweetAlert.cancelButtonColor : "var(--dark)",
                                        cancelButtonText: typeof (window.DCMS.sweetAlert.cancelButtonText !== 'undefined') ? window.DCMS.sweetAlert.cancelButtonText : Lang("Cancel"),
                                    });
                                }
                            });
                        });
                    }
                }
                currentForm.querySelector('#fixSheet').addEventListener('click', function (e) {
                    var dropdownHeaders = [];
                    Array.from(htmlTable.getElementsByTagName('th')).forEach((th) => {
                        if (th.dataset.jexcelType == 'dropdown') {
                            dropdownHeaders.push({
                                column: th.cellIndex,
                                text: th.textContent,
                            });
                        }
                    });
                    window.axios({
                        method: 'POST',
                        url: e.target.dataset.jexcelFixRoute,
                        data: {
                            data: table.getData(),
                            th: dropdownHeaders,
                        },
                        responseType: 'json',
                        headers: {
                            'X-CSRF-TOKEN': window.DCMS.csrf,
                            "Content-type": "application/x-www-form-urlencoded",
                            'X-Requested-With': 'XMLHttpRequest',
                        }
                    }).then(function (response) {
                        Swal.fire({
                            title: Lang('Are you sure?'),
                            html: Lang('This will try to fix empty dropdown columns.') + "<br>" + Lang('Do you want to continue?'),
                            icon: "warning",
                            confirmButtonColor: typeof (window.DCMS.sweetAlert.confirmButtonColor !== 'undefined') ? window.DCMS.sweetAlert.confirmButtonColor : "var(--primary)",
                            confirmButtonText: typeof (window.DCMS.sweetAlert.confirmButtonText !== 'undefined') ? window.DCMS.sweetAlert.confirmButtonText : Lang("OK"),
                            cancelButtonColor: typeof (window.DCMS.sweetAlert.cancelButtonColor !== 'undefined') ? window.DCMS.sweetAlert.cancelButtonColor : "var(--dark)",
                            cancelButtonText: typeof (window.DCMS.sweetAlert.cancelButtonText !== 'undefined') ? window.DCMS.sweetAlert.cancelButtonText : Lang("Cancel"),
                        }).then(function (result) {
                            if (result.value) {
                                for (const t in window.DCMS.jExcelTables) {
                                    let currentTable = currentForm.querySelector('table');
                                    let jExcelTable = window.DCMS.jExcelTables[t];
                                    if (jExcelTable.el == currentTable) {
                                        jExcelTable.setData(response.data, false);
                                        window.toastr.success(Lang('Sheet has been updated.'));
                                    }
                                }
                            }
                        });
                    }).catch(function () {
                        Swal.fire({
                            title: Lang('Data correction failed'),
                            text: Lang('The provided data couldn\'t be fixed.'),
                            icon: "error",
                            confirmButtonColor: typeof (window.DCMS.sweetAlert.confirmButtonColor !== 'undefined') ? window.DCMS.sweetAlert.confirmButtonColor : "var(--primary)",
                            confirmButtonText: typeof (window.DCMS.sweetAlert.confirmButtonText !== 'undefined') ? window.DCMS.sweetAlert.confirmButtonText : Lang("OK"),
                            cancelButtonColor: typeof (window.DCMS.sweetAlert.cancelButtonColor !== 'undefined') ? window.DCMS.sweetAlert.cancelButtonColor : "var(--dark)",
                            cancelButtonText: typeof (window.DCMS.sweetAlert.cancelButtonText !== 'undefined') ? window.DCMS.sweetAlert.cancelButtonText : Lang("Cancel"),
                        });
                    });
                });
            });
        }
    });
};
window.DCMS.onComplete(function () {
    window.DCMS.jExcelInit();
});