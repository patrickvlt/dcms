"use strict";
import './assets/jexcel/jszip.js';
import './assets/jexcel/jsuites.js';
import jexcel from './assets/jexcel/jexcel.js';

window.addEventListener('DOMContentLoaded', (event) => {
    var jExcelTrans;
    var sheetData;
    var sheetDynColumns;
    var currentForm;
    var formRows;
    var table;

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
                    source: ajax,
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
            if (header.dataset.jexcelUrl !== null && typeof header.dataset.jexcelUrl !== 'undefined'){
                $.ajax({
                    type: "GET",
                    url: header.dataset.jexcelUrl,
                    async: false,
                    headers: {
                        'X-CSRF-TOKEN': window.csrf
                    },
                    success: function (response) {
                        let columnSource = [];
                        response.forEach(function(object){
                            columnSource.push({"id": String(object.id), "name": String(object.value)});
                        })
                        ColumnPush(columnSource);
                    }
                });
            } else {
                ColumnPush();
            }
        });

        // construct table
        let rows = parseInt(htmlTable.dataset.jexcelEmptyrows);
        let dataFill = [];
        for (let index = 0; index < rows; index++) {
            dataFill.push("");
        }
        table = jexcel(htmlTable, {
            data: dataFill,
            columns: sheetDynColumns,
            columnDrag: true,
            allowInsertColumn: false,
            allowManualInsertColumn: false,
            text: jExcelTrans
        });
        // execute this code after table is initalised
        currentForm.style.display = 'block';

        // execute this code after table is initalised
        htmlTable.addEventListener('change',function (e) {
            UpdateSheetData(e);
        });

        function UpdateSheetData(e) {
            // define form rows from jexcel
            formRows = Array.from(e.target.getElementsByClassName('jexcel_content')[0].getElementsByTagName('table')[0].getElementsByTagName('tbody')[0].getElementsByTagName('tr'));
            sheetData = [];
            formRows.forEach(function(row){
                let rowData = [];
                Array.from(row.cells).forEach(function(cell){
                    if(!cell.classList.contains('jexcel_row')){
                        rowData.push(cell.textContent);
                    }
                });
                sheetData.push(rowData);
            });
            return sheetData;
        }

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
            sheetData = UpdateSheetData(e,true);
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
                                            if(error.indexOf(cell.textContent) > -1 && cell.textContent !== ""){
                                                cell.classList.add('invalid');
                                            };
                                        });
                                    });
                                    Alert('error', Lang('Importeren mislukt'), alertMsg, {
                                        confirm: {
                                            text: Lang('Ok'),
                                            btnClass: 'btn-danger',
                                        },
                                    });
                                } else {
                                    Array.from(reply.errors).forEach(function(error){
                                        formRows.forEach(function(row){
                                            if (error.line == row.rowIndex){
                                                row.classList.add('invalid');
                                            }
                                        });
                                    });
                                    Alert('error', Lang('Importeren mislukt'), reply.message, {
                                        confirm: {
                                            text: Lang('Ok'),
                                            btnClass: 'btn-danger',
                                        },
                                    });
                                }
                                break;

                            case 200:
                                Alert('success', Lang('Importeren gelukt'), reply.message, {
                                    confirm: {
                                        text: Lang('Ok'),
                                        btnClass: 'btn-success',
                                        action: function(){
                                            if (reply.url){
                                                window.location.href = reply.url;
                                            }
                                        }
                                    },
                                });
                                break;
                        }
                    }
                }
            });
        })
    });
});
