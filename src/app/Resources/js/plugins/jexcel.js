"use strict";
import './assets/jexcel/jszip.js';
import './assets/jexcel/jsuites.js';
import jexcel from './assets/jexcel/jexcel.js';
import Papa from './assets/jexcel/papaparse.min.js';

window.addEventListener('DOMContentLoaded', (event) => {
    var jExcelTrans, sheetData, sheetDynColumns, currentForm, formRows, table;

    if (document.querySelectorAll('[data-type=jexcel]').length >= 1){
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
        }
    }

    document.querySelectorAll('[data-type=jexcel]').forEach(function(htmlTable){
        sheetData = '';
        sheetDynColumns = [];
        currentForm = document.querySelector(htmlTable.dataset.jexcelFormSelector);

        // fetch information from htmlTable headers
        Array.from(htmlTable.getElementsByTagName('th')).forEach(function(header){
            function ColumnPush(ajax=null){
                sheetDynColumns.push({
                    type: header.dataset.jexcelType,
                    source: (ajax !== null) ? ajax : '',
                    title: header.textContent,
                    width: header.dataset.jexcelWidth,
                    tableOverflow: true,
                    autocomplete: (header.dataset.jexcelAutocomplete == 'true') ? 'true' : 'false',
                    options: {
                        format: (header.dataset.jexcelDateFormat) ? header.dataset.jexcelDateFormat : AppDateFormat
                    }
                });
                header.hidden = true;
            }
            if (header.dataset.jexcelFetchUrl !== null && typeof header.dataset.jexcelFetchUrl !== 'undefined'){
                let fetchColumn = (header.dataset.jexcelFetchColumn) ? header.dataset.jexcelFetchColumn : 'id';
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

        function MakeTable(data=null){
            // construct table
            let rows = parseInt(htmlTable.dataset.jexcelEmptyrows);
            let dataFill = [];
            for (let index = 0; index < rows; index++) {
                dataFill.push("");
            }
            table = jexcel(htmlTable, {
                data: (data !== null) ? data : dataFill,
                columns: sheetDynColumns,
                columnDrag: true,
                allowInsertColumn: false,
                allowManualInsertColumn: false,
                text: jExcelTrans
            });
            // execute this code after table is initalised
            currentForm.style.display = 'block';

            function ClearInvalid(e) {
                function CleanElement(element){
                    if (element.classList.contains('invalid')) {
                        element.classList.remove('invalid')
                    }
                }
                formRows = Array.from(e.target.getElementsByClassName('jexcel_content')[0].getElementsByTagName('table')[0].getElementsByTagName('tbody')[0].getElementsByTagName('tr'));
                Array.from(formRows).forEach(function(element) {
                    CleanElement(element);
                    Array.from(element.getElementsByTagName('td')).forEach(element => CleanElement(element));
                });
            }

            currentForm.addEventListener("submit", function(e){
                e.preventDefault();
                ClearInvalid(e,true);
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
                                    if (reply.message == 'The given data was invalid.'){
                                        $.each(reply.errors, function (key, error) {
                                            alertMsg += error[0] + "<br>";
                                            Array.from(document.querySelectorAll('tbody tr td:not(.jexcel_row)')).forEach(function(cell){
                                                if(String(error).toLowerCase().indexOf(cell.textContent.toLowerCase()) > -1 && cell.textContent !== ""){
                                                    cell.classList.add('invalid');
                                                };
                                            });
                                        });
                                        Swal.fire({
                                            title: Lang('Import failed'),
                                            html: alertMsg,
                                            icon: "error"
                                        })
                                    } else {
                                        Array.from(reply.errors).forEach(function(error){
                                            formRows.forEach(function(row){
                                                if (error.line == row.rowIndex){
                                                    row.classList.add('invalid');
                                                }
                                            });
                                        });
                                        Swal.fire({
                                            title: reply.response.title,
                                            html: reply.response.message,
                                            icon: "error"
                                        })
                                    }
                                    break;

                                case 200:
                                    Swal.fire({
                                        title: reply.response.title,
                                        html: reply.response.message,
                                        icon: "success"
                                    }).then(function(result){
                                        if (result.value){
                                            if (reply.url){
                                                window.location.href = reply.url;
                                            }
                                        }
                                    });
                                    break;
                            }
                        }
                    }
                });
            })
        }

        $.ajax({
            type: "GET",
            url: "jexcel.csv",
            dataType: "text",
            success: function (file) {
                var parseData, data, dropdownHeaders = [], objects;
                parseData = Papa.parse(file);
                window.parseData = parseData.data;
                MakeTable(parseData.data);
            },
            error: function () {
                MakeTable();
            },
        });

        $(currentForm).on('click','#fixData',function(button){
            var dropdownHeaders = [];
            $.each($(htmlTable).find('th'), function (x, th) {
                if ($(th).data('jexcel-type') == 'dropdown'){
                    let ajUrl = $(th).data('jexcel-fetch-url');
                    dropdownHeaders.push({
                        cell: th.cellIndex, 
                        text: th.textContent,
                        column: $(th).data('jexcel-fetch-column')
                    })
                } 
            });
            $.ajax({
                type: "POST",
                headers: {
                    'X-CSRF-TOKEN': window.csrf
                },
                url: $(this).data('jexcel-fix-route'),
                data: {
                    data: window.parseData,
                    th: dropdownHeaders,
                },
                success: function (file) {
                    Swal.fire({
                        title: Lang('Are you sure?'),
                        html: Lang('This will try to fix empty dropdown columns.') + "<br>" + Lang('Do you want to continue?'),
                        icon: "warning"
                    }).then(function(result){
                        if (result.value){
                            Swal.fire({
                                title: Lang('Data correction complete'),
                                showCancelButton: true,
                                html: Lang('Do you want to replace your current data?'),
                                icon: "success"
                            }).then(function(result){
                                if (result.value){
                                    $(currentForm).find('table').jexcel('setData',file,false);

                                }
                            });
                        }
                    });
                },
                error: function () {
                    Swal.fire(Lang('Autofill failed'),Lang('The provided data couldn\'t be fixed.'),'error')
                },
            });
        })
    });
    // make next table
});
