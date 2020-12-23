"use strict";

window.hasLoaded(['jexcel'], function () {
    var jExcelTrans, sheetData, sheetDynColumns, currentForm, formRows, table;

    if (document.querySelectorAll('[data-type=jexcel]').length > 0) {
        jExcelTrans = {
            // noRecordsFound:"Nenhum registro encontrado",
            // entries:"entradas",
            // insertANewColumnBefore:"Inserir uma nova coluna antes de",
            // insertANewColumnAfter:"Inserir uma nova coluna depois de",
            // deleteSelectedColumns:"Excluir colunas selecionadas",
            // renameThisColumn:"Renomear esta coluna",
            // orderAscending:"ordem ascendente",
            // orderDescending:"Order decrescente",
            // insertANewRowBefore:"Inserir uma nova linha antes de",
            // insertANewRowAfter:"Inserir uma nova linha depois de",
            // deleteSelectedRows:"Excluir linhas selecionadas",
            // editComments:"Editar comentários",
            // addComments:"Adicionar comentários",
            // comments:"Comentarios",
            // clearComments:"Limpar comentários",
            // copy:"Copiar ...",
            // paste:"Colar ...",
            // saveAs: "Salvar como ...",
            // about: "about",
            // areYouSureToDeleteTheSelectedRows:"Tem certeza de excluir as linhas selecionadas?",
            // areYouSureToDeleteTheSelectedColumns:"Tem certeza de excluir as colunas selecionadas?",
            // thisActionWillDestroyAnyExistingMergedCellsAreYouSure:"Esta ação irá destruir todas as células mescladas existentes. Você tem certeza?",
            // thisActionWillClearYourSearchResultsAreYouSure:"Esta ação limpará seus resultados de pesquisa. Você tem certeza?",
            // thereIsAConflictWithAnotherMergedCell:"Há um conflito com outra célula mesclada",
            // invalidMergeProperties:"Propriedades mescladas inválidas",
            // cellAlreadyMerged:"Cell já mesclado",
            // noCellsSelected:"Nenhuma célula selecionada",
        };

        document.querySelectorAll('[data-type=jexcel]').forEach(function (htmlTable) {
            sheetData = '';
            sheetDynColumns = [];
            currentForm = document.querySelector(htmlTable.dataset.jexcelFormSelector);

            // fetch information from htmlTable headers
            Array.from(htmlTable.getElementsByTagName('th')).forEach(function (header) {
                function ColumnPush(ajax = null) {
                    sheetDynColumns.push({
                        type: header.dataset.jexcelType,
                        source: (ajax !== null) ? ajax : '',
                        title: header.textContent,
                        width: header.dataset.jexcelWidth,
                        tableOverflow: true,
                        autocomplete: (header.dataset.jexcelAutocomplete == 'true') ? 'true' : 'false',
                        options: {
                            format: (header.dataset.jexcelDateFormat) ? header.dataset.jexcelDateFormat : window.AppDateFormat
                        }
                    });
                    header.hidden = true;
                }
                if (header.dataset.jexcelFetchUrl !== null && typeof header.dataset.jexcelFetchUrl !== 'undefined') {
                    $.ajax({
                        type: "GET",
                        url: header.dataset.jexcelFetchUrl,
                        async: false,
                        headers: {
                            'X-CSRF-TOKEN': window.csrf
                        },
                        success: function (response) {
                            ColumnPush(response);
                        }
                    });
                } else {
                    ColumnPush();
                }
            });

            function MakeTable(data = null) {
                // construct table
                let rows = parseInt(htmlTable.dataset.jexcelEmptyrows);
                let dataFill = [];
                for (let index = 0; index < rows; index++) {
                    dataFill.push("");
                }
                table = jexcel(htmlTable, {
                    data: (data !== null) ? data : dataFill,
                    columnDrag: true,
                    colWidths: sheetDynColumns.map(function (el) { return (el.width) ? el.width : 100; }),
                    columns: sheetDynColumns,
                    allowInsertColumn: false,
                    allowManualInsertColumn: false,
                    text: jExcelTrans
                });
                if (currentForm) {
                    currentForm.style.display = 'block';
                }

                window.jExcelTables.push(table);

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
                        $.ajax({
                            type: "POST",
                            url: e.target.action,
                            headers: {
                                'X-CSRF-TOKEN': window.csrf
                            },
                            data: {
                                sheetData
                            },
                            complete: function (response) {
                                let reply = response.responseJSON;
                                if (typeof reply !== 'undefined') {
                                    var alertMsg = '';
                                    switch (response.status) {
                                        case 422:
                                            if (reply.message == 'The given data was invalid.') {
                                                $.each(reply.errors, function (key, error) {
                                                    alertMsg += error[0] + "<br>";
                                                    Array.from(document.querySelectorAll('tbody tr td:not(.jexcel_row)')).forEach(function (cell) {
                                                        if (String(error).toLowerCase().indexOf(cell.textContent.toLowerCase()) > -1 && cell.textContent !== "") {
                                                            cell.classList.add('invalid');
                                                        }
                                                    });
                                                });
                                                Swal.fire({
                                                    title: Lang('Import failed'),
                                                    html: alertMsg,
                                                    icon: "error",
                                                    confirmButtonColor: typeof(window.SwalConfirmButtonColor !== 'undefined') ? window.SwalConfirmButtonColor : "var(--primary)",
                                                    confirmButtonText: typeof(window.SwalConfirmButtonText !== 'undefined') ? window.SwalConfirmButtonText : Lang("OK"),
                                                    cancelButtonColor: typeof(window.SwalCancelButtonColor !== 'undefined') ? window.SwalCancelButtonColor : "var(--dark)",
                                                    cancelButtonText: typeof(window.SwalCancelButtonText !== 'undefined') ? window.SwalCancelButtonText : Lang("Cancel"),
                                                });
                                            } else {
                                                Array.from(reply.errors).forEach(function (error) {
                                                    formRows.forEach(function (row) {
                                                        if (error.line == row.rowIndex) {
                                                            row.classList.add('invalid');
                                                        }
                                                    });
                                                });
                                                Swal.fire({
                                                    title: reply.response.title,
                                                    html: reply.response.message,
                                                    icon: "error",
                                                    confirmButtonColor: typeof(window.SwalConfirmButtonColor !== 'undefined') ? window.SwalConfirmButtonColor : "var(--primary)",
                                                    confirmButtonText: typeof(window.SwalConfirmButtonText !== 'undefined') ? window.SwalConfirmButtonText : Lang("OK"),
                                                    cancelButtonColor: typeof(window.SwalCancelButtonColor !== 'undefined') ? window.SwalCancelButtonColor : "var(--dark)",
                                                    cancelButtonText: typeof(window.SwalCancelButtonText !== 'undefined') ? window.SwalCancelButtonText : Lang("Cancel"),
                                                });
                                            }
                                            break;

                                        case 200:
                                            Swal.fire({
                                                title: reply.response.title,
                                                html: reply.response.message,
                                                icon: "success",
                                                confirmButtonColor: typeof(window.SwalConfirmButtonColor !== 'undefined') ? window.SwalConfirmButtonColor : "var(--primary)",
                                                confirmButtonText: typeof(window.SwalConfirmButtonText !== 'undefined') ? window.SwalConfirmButtonText : Lang("OK"),
                                                cancelButtonColor: typeof(window.SwalCancelButtonColor !== 'undefined') ? window.SwalCancelButtonColor : "var(--dark)",
                                                cancelButtonText: typeof(window.SwalCancelButtonText !== 'undefined') ? window.SwalCancelButtonText : Lang("Cancel"),
                                            }).then(function (result) {
                                                if (result.value) {
                                                    if (reply.url) {
                                                        window.location.href = reply.url;
                                                    }
                                                }
                                            });
                                            break;
                                    }
                                }
                            }
                        });
                    });
                }
            }

            MakeTable();

            $(currentForm).on('click', '#fixSheet', function () {
                var dropdownHeaders = [];
                $.each($(htmlTable).find('th'), function (x, th) {
                    if ($(th).data('jexcel-type') == 'dropdown') {
                        dropdownHeaders.push({
                            column: th.cellIndex,
                            text: th.textContent,
                        });
                    }
                });
                $.ajax({
                    type: "POST",
                    headers: {
                        'X-CSRF-TOKEN': window.csrf
                    },
                    url: $(this).data('jexcel-fix-route'),
                    data: {
                        data: table.getData(),
                        th: dropdownHeaders,
                    },
                    success: function (file) {
                        Swal.fire({
                            title: Lang('Are you sure?'),
                            html: Lang('This will try to fix empty dropdown columns.') + "<br>" + Lang('Do you want to continue?'),
                            icon: "warning",
                            confirmButtonColor: typeof(window.SwalConfirmButtonColor !== 'undefined') ? window.SwalConfirmButtonColor : "var(--primary)",
                            confirmButtonText: typeof(window.SwalConfirmButtonText !== 'undefined') ? window.SwalConfirmButtonText : Lang("OK"),
                            cancelButtonColor: typeof(window.SwalCancelButtonColor !== 'undefined') ? window.SwalCancelButtonColor : "var(--dark)",
                            cancelButtonText: typeof(window.SwalCancelButtonText !== 'undefined') ? window.SwalCancelButtonText : Lang("Cancel"),
                        }).then(function (result) {
                            if (result.value) {
                                $(currentForm).find('table').jexcel('setData', file, false);
                                toastr.success(Lang('Sheet has been updated.'));
                            }
                        });
                    },
                    error: function () {
                        Swal.fire({
                            title: Lang('Data correction failed'),
                            text: Lang('The provided data couldn\'t be fixed.'),
                            icon: "error",
                            confirmButtonColor: typeof(window.SwalConfirmButtonColor !== 'undefined') ? window.SwalConfirmButtonColor : "var(--primary)",
                            confirmButtonText: typeof(window.SwalConfirmButtonText !== 'undefined') ? window.SwalConfirmButtonText : Lang("OK"),
                            cancelButtonColor: typeof(window.SwalCancelButtonColor !== 'undefined') ? window.SwalCancelButtonColor : "var(--dark)",
                            cancelButtonText: typeof(window.SwalCancelButtonText !== 'undefined') ? window.SwalCancelButtonText : Lang("Cancel"),
                        });
                    },
                });
            });
        });
    }
});
