"use strict";

window.DCMS.KTDatatable = {};

/**
 *
 *  Reload datatables
 *
 */

window.DCMS.reloadKTDatatables = function () {
    Array.from(document.querySelectorAll('.datatable')).forEach(table => {
        $(table).KTDatatable('reload');
    });
};

/**
 *
 *  Merge table columns
 *
 */


window.DCMS.KTMergeColumns = function (row, column) {
    let value = '';
    if (column.split(',').length > 1) {
        Array.from(column.split(',')).forEach(function (element) {
            value = value + row[element] + ' ';
        });
    } else {
        value = row[column];
    }
    return value;
};

/**
*
*  Global function to dynamically generate datatable(s)
*
*/

window.DCMS.datatable = function (parameters) {
    $(document).ready(function () {
        window.KTDebug = false;

        let table = parameters.table;
        let columns = [];

            // Display client-side errors
            if (table.dataset.ktDebug) {
                window.KTDebug = true;
            }

            // First column will be a selector, to select multiple rows
            if (table.dataset.ktIncludeSelector == 'true') {
                columns.push({
                    field: '',
                    title: '',
                    sortable: false,
                    width: 30,
                    type: 'number',
                    selector: { class: 'kt-checkbox--solid' },
                    textAlign: 'center'
                });
                $('[data-kt-type="selector"]').show();
            }

            // Show the controls to refresh, reload e.d.
            if (table.dataset.ktIncludeControls !== 'false') {
                $('[data-kt-type="controls"]').show();
            }

            // Get all columns defined in the HTML table element
            let tableColumns = $(table).find('[data-kt-type="columns"]').children();

            // Generate columns from HTML elements
            $.each(tableColumns, function (index, column) {

                let textColor, value, spotlightClass, prepend, append, target, useRow, sortable, columnField, splitColumns, eagerColumns;

                if (column.dataset.ktSortable == 'false') {
                    sortable = false;
                } else if (typeof column.dataset.ktSortable == 'undefined' || column.dataset.ktSortable == null) {
                    sortable = true;
                }
                else {
                    sortable = true;
                }

                if (column.dataset.ktType == 'object' && column.dataset.ktObject) {
                    columnField = column.dataset.ktObject + "." + column.dataset.ktColum;
                } else if (column.dataset.ktField) {
                    columnField = column.dataset.ktField;
                } else {
                    columnField = column.dataset.ktColumn;
                }

                let newColumn = {
                    // Default column properties
                    field: (column.dataset.ktType == 'object' && column.dataset.ktObject) ? column.dataset.ktObject + "." + column.dataset.ktColumn : column.dataset.ktColumn,
                    title: column.dataset.ktTitle,
                    order: column.dataset.ktOrder,
                    width: column.dataset.ktWidth,
                    autoHide: column.dataset.ktAutoHide,
                    type: column.dataset.ktType,
                    sortable: sortable,
                    align: (column.dataset.ktAlign) ? column.dataset.ktAlign : 'center',
                    template: function (row) {
                        // Check if a nested column should be the row instead
                        useRow = row;
                        if (typeof column.dataset.ktObject !== 'undefined' && row[column.dataset.ktObject] !== null) {
                            useRow = row[column.dataset.ktObject];
                        }

                        if (column.dataset.ktColumn.split('.').length > 1) {
                            eagerColumns = '';
                            splitColumns = column.dataset.ktColumn.split('.');
                            splitColumns.map((column) => {
                                eagerColumns += "['" + column + "']";
                            });
                            value = eval('row' + eagerColumns);
                        } else {
                            value = useRow[column.dataset.ktColumn];
                        }

                        // Check if the default value should have appended or prepended information
                        value = (typeof value == 'undefined' || value == null) ? '' : value;
                        prepend = (typeof column.dataset.ktPrepend !== 'undefined' && column.dataset.ktPrepend !== null) ? column.dataset.ktPrepend : '';
                        append = (typeof column.dataset.ktAppend !== 'undefined' && column.dataset.ktAppend !== null) ? column.dataset.ktAppend : '';

                        // Check if a link has to be generated
                        if (column.dataset.ktHref) {
                            let link, columnMatch, linkFromMatch;
                            link = column.dataset.ktHref;
                            target = (column.dataset.ktTarget) ? column.dataset.ktTarget : '';
                            if (link.match(/__.*__/gm)) {
                                columnMatch = link.match(/__.*__/gm);
                                linkFromMatch = columnMatch[0].replace(/__/g, '');
                                link = link.replace(/__/g, '');
                                if (useRow[linkFromMatch]) {
                                    link = link.replace(linkFromMatch, '');
                                    link = link + useRow[linkFromMatch];
                                }
                            }
                            value = (value !== '') ? `<a data-kt-target='` + target + `' data-kt-action="link" href='` + link + `'>` + value + `</a>` : '';
                        }

                        // Check if the default text color has to be changed
                        textColor = (column.dataset.ktTextColor) ? column.dataset.ktTextColor : 'dark';
                        switch (column.dataset.ktType) {
                            // Generate a simple card in the column
                            case 'card':
                                let cardTitle = (useRow[column.dataset.ktCardTitle]) ? useRow[column.dataset.ktCardTitle] : '';
                                let cardInfo = (useRow[column.dataset.ktCardInfo]) ? useRow[column.dataset.ktCardInfo] : '';
                                let cardImgText = '';
                                let cardImg;
                                // if data-card-image is set and the column has a filled URL
                                if (typeof useRow[column.dataset.ktCardImage] !== 'undefined' && column.dataset.ktCardImage.length > 1 && (useRow[column.dataset.ktCardImage] !== column.dataset.ktCardImage && useRow[column.dataset.ktCardImage] !== null)) {
                                    jQuery.ajax({
                                        type: "GET",
                                        async: false,
                                        crossDomain: true,
                                        url: useRow[column.dataset.ktCardImage],
                                        success: function () {
                                            cardImg = useRow[column.dataset.ktCardImage];
                                            cardImgText = '';
                                            return cardImgText;
                                        },
                                        error: function () {
                                            cardImg = 'null';
                                            cardImgText = cardTitle[0].toUpperCase();
                                            return cardImgText;
                                        }
                                    });
                                } else {
                                    cardImgText = cardTitle[0].toUpperCase();
                                }

                                // Card properties
                                let cardColor = (column.dataset.ktCardColor) ? column.dataset.ktCardColor : '';
                                let cardTextColor = (column.dataset.ktCardTextColor) ? column.dataset.ktCardTextColor : 'white';
                                let titleColor = (column.dataset.ktTitleColor) ? column.dataset.ktTitleColor : 'primary';

                                return `<div data-id='` + row.id + `'><span style="width: 250px;"><div class="d-flex align-items-center">
									<div class="symbol symbol-40 symbol-`+ cardColor + ` flex-shrink-0">
										<div class="symbol-label text-` + cardTextColor + `" style="background-image:url('` + cardImg + `')">` + cardImgText + `</div>
									</div>
									<div class="ml-2">
										<div class="text-` + titleColor + ` font-weight-bold line-height-sm">` + cardTitle + `</div> <a class="font-size-sm text-` + textColor + ` text-hover-primary">` + cardInfo + `</a>
									</div>
									</div>
								</div>
                            </span>`;
                            // Generate a simple checkbox column, works best with boolean fields
                            case 'boolean':
                                if (useRow[column.dataset.ktColumn] !== null && useRow[column.dataset.ktColumn] !== 0 && typeof useRow[column.dataset.ktColumn] !== 'undefined') {
                                    return `<i data-id='` + row.id + `' class="fas fa-check text-` + textColor + `" style="max-height:` + column.dataset.ktMaxHeight + `"></i>`;
                                }
                                else {
                                    return '';
                                }
                                break;
                            // Simple text column
                            case 'text':
                                return `<div data-id='` + row.id + `' style="max-height:` + column.dataset.ktMaxHeight + `" class="text-` + textColor + `">` + prepend + value + append + `</div>`;
                            // Prepend an icon to the visible value
                            case 'icon':
                                let icon = '';
                                if (column.dataset.ktIconClass && value !== '') {
                                    icon = `<i class="d-inline ` + column.dataset.ktIconClass + ` text-muted"></i>`;
                                }
                                return `<div data-id='` + row.id + `' style="max-height:` + column.dataset.ktMaxHeight + `" class="text-` + textColor + `">` + icon + prepend + value + append + `</div>`;
                            // Simple price column
                            case 'price':
                                let currency = (column.dataset.ktCurrency) ? column.dataset.ktCurrency : '€';
                                value = (value == '') ? 0 : value;
                                return `<div data-id='` + row.id + `' style="max-height:` + column.dataset.ktMaxHeight + `" class="text-` + textColor + `">` + currency + prepend + value + append + `,-` + `</div>`;
                            // Image column which can be made fullscreen if spotlight is also included
                            case 'image':
                                let changeControl = `<label class="btn btn-xs btn-icon btn-circle btn-white btn-hover-text-primary " data-kt-action="change" data-toggle="tooltip" title="" data-original-title="Change avatar">
								<i class="fa fa-pen icon-sm text-muted"></i>
								<input type="file" name="profile_avatar" accept=".png, .jpg, .jpeg">
								<input type="hidden" name="profile_avatar_remove">
							</label>`;
                                let deleteControl = `<span class="btn btn-xs btn-icon btn-circle btn-white btn-hover-text-primary " data-kt-action="remove" data-toggle="tooltip" title="" data-original-title="Remove avatar">
								<i class="ki ki-bold-close icon-xs text-muted"></i>
							</span>`;
                                if (column.dataset.ktAllowControls !== 'true') {
                                    changeControl = '';
                                    deleteControl = '';
                                }
                                spotlightClass = (value !== null) ? 'spotlight' : '';

                                if (value instanceof Array && value.length > 1) {
                                    value = value[0];
                                }

                                return `<div class="dcmstable-image" data-id='` + row.id + `'>
								<div class="dcmstable-image-wrapper `+ spotlightClass + `" data-src='` + prepend + value + append + `' style="background-image: url(` + prepend + value + append + `); max-height:` + column.dataset.ktMaxHeight + `"></div>
								`+ changeControl + `
								`+ deleteControl + `
							</div>`;
                            default:
                                // return prepend+value+append;
                                return `<div data-id='` + row.id + `' style="max-height:` + column.dataset.ktMaxHeight + `" class="text-` + textColor + `">` + prepend + value + append + `</div>`;
                        }
                    },
                };
                columns.push(newColumn);
            });

            // Push custom columns if defined in JavaScript method
            if (typeof parameters.columns !== 'undefined' && parameters.columns !== null) {
                $.each(parameters.columns, function (key, paraColumn) {
                    columns.push(paraColumn);
                });
            }

            // Generate simple edit and delete column at the end of the row
            if (table.dataset.ktIncludeActions == 'true') {
                columns.push({
                    field: 'Actions',
                    title: Lang('Actions'),
                    sortable: false,
                    width: 125,
                    overflow: 'visible',
                    autoHide: false,
                    template: function (row) {
                        return `<td class="text-right pr-0">
						<button class="btn btn-icon btn-light btn-hover-primary btn-sm mx-3" data-kt-action="edit" data-id="`+ row['id'] + `">
							<span class="svg-icon svg-icon-md svg-icon-primary">
								<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px"
									height="24px" viewBox="0 0 24 24" version="1.1">
									<g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
										<rect x="0" y="0" width="24" height="24"></rect>
										<path
											d="M12.2674799,18.2323597 L12.0084872,5.45852451 C12.0004303,5.06114792 12.1504154,4.6768183 12.4255037,4.38993949 L15.0030167,1.70195304 L17.5910752,4.40093695 C17.8599071,4.6812911 18.0095067,5.05499603 18.0083938,5.44341307 L17.9718262,18.2062508 C17.9694575,19.0329966 17.2985816,19.701953 16.4718324,19.701953 L13.7671717,19.701953 C12.9505952,19.701953 12.2840328,19.0487684 12.2674799,18.2323597 Z"
											fill="#000000" fill-rule="nonzero"
											transform="translate(14.701953, 10.701953) rotate(-135.000000) translate(-14.701953, -10.701953)">
										</path>
										<path
											d="M12.9,2 C13.4522847,2 13.9,2.44771525 13.9,3 C13.9,3.55228475 13.4522847,4 12.9,4 L6,4 C4.8954305,4 4,4.8954305 4,6 L4,18 C4,19.1045695 4.8954305,20 6,20 L18,20 C19.1045695,20 20,19.1045695 20,18 L20,13 C20,12.4477153 20.4477153,12 21,12 C21.5522847,12 22,12.4477153 22,13 L22,18 C22,20.209139 20.209139,22 18,22 L6,22 C3.790861,22 2,20.209139 2,18 L2,6 C2,3.790861 3.790861,2 6,2 L12.9,2 Z"
											fill="#000000" fill-rule="nonzero" opacity="0.3"></path>
									</g>
								</svg>
							</span>
						</button>
						<button class="btn btn-icon btn-light btn-hover-primary btn-sm" data-kt-action="destroy" data-id="`+ row['id'] + `">
							<span class="svg-icon svg-icon-md svg-icon-primary">
								<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px"
									height="24px" viewBox="0 0 24 24" version="1.1">
									<g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
										<rect x="0" y="0" width="24" height="24"></rect>
										<path
											d="M6,8 L6,20.5 C6,21.3284271 6.67157288,22 7.5,22 L16.5,22 C17.3284271,22 18,21.3284271 18,20.5 L18,8 L6,8 Z"
											fill="#000000" fill-rule="nonzero"></path>
										<path
											d="M14,4.5 L14,4 C14,3.44771525 13.5522847,3 13,3 L11,3 C10.4477153,3 10,3.44771525 10,4 L10,4.5 L5.5,4.5 C5.22385763,4.5 5,4.72385763 5,5 L5,5.5 C5,5.77614237 5.22385763,6 5.5,6 L18.5,6 C18.7761424,6 19,5.77614237 19,5.5 L19,5 C19,4.72385763 18.7761424,4.5 18.5,4.5 L14,4.5 Z"
											fill="#000000" opacity="0.3"></path>
									</g>
								</svg>
							</span>
						</button>
					</td>`;
                    },
                });
            }

            // If orders have been specified, sort columns
            columns.sort((a, b) => {
                if (a.order < b.order) return -1;
                return a.order > b.order ? 1 : 0;
            });

            // Custom datatable options such as pagination, page size, max height, allow scrolling
            let pagination = (table.dataset.ktPagination) == 'false' ? false : true;
            let pageSize = (table.dataset.ktPageSize) ? parseInt(table.dataset.ktPageSize) : 10;
            let defaultProperties = {
                // datasource definition
                data: {
                    type: 'remote',
                    source: {
                        read: {
                            method: 'GET',
                            url: table.dataset.ktRoute,
                        }
                    },
                    pageSize: pageSize, // display 20 records per page
                    serverPaging: (table.dataset.ktPagination) == 'false' ? false : true,
                    serverFiltering: true,
                    serverSorting: true,
                    saveState: false,
                },

                // layout definition
                layout: {
                    scroll: (table.dataset.ktScrolling) == 'false' ? false : true, // enable/disable datatable scroll both horizontal and vertical when needed.
                    height: parseInt(table.dataset.ktHeight), // datatable's body's fixed height
                    footer: false,
                    spinner: {
                        state: 'brand',
                        type: false,
                        message: false
                    },
                    icons: {
                        sort: {
                            asc: 'fas fa-arrow-up',
                            desc: 'fas fa-arrow-down',
                        },
                        pagination: {
                            next: 'fas fa-angle-right',
                            prev: 'fas fa-angle-left',
                            first: 'fas fa-angle-double-left',
                            last: 'fas fa-angle-double-right',
                            more: 'fas fa-ellipsis-h',
                        },
                        rowDetail: {
                            expand: 'fas fa-caret-down',
                            collapse: 'fas fa-caret-right'
                        }
                    },
                },

                // column sorting
                sortable: true,
                pagination: pagination,

                search: {
                    input: $($(table).data('kt-parent')).find('[data-kt-action="search"]'),
                    key: 'generalSearch'
                },

                // columns definition
                columns: columns,

            };

            // Get default, global and custom datatable properties
            let defaultWithGlobalProperties = (typeof window.DCMS.KTDatatable.config !== 'undefined') ? Object.assign(defaultProperties, window.DCMS.KTDatatable.config) : defaultProperties;
            let customProperties = (typeof parameters.properties !== 'undefined') ? Object.assign(defaultWithGlobalProperties, parameters.properties) : defaultWithGlobalProperties;
            let datatable = $(table).KTDatatable(customProperties);

            // Reload datatable
            $($(table).data('kt-parent')).find('[data-kt-action="reload"]').on('click', function () {
                if (window.DCMS.KTDatatable.BeforeRefresh) {
                    window.DCMS.KTDatatable.BeforeRefresh();
                }
                $(table).KTDatatable().setDataSourceParam('query', '');
                $(table).KTDatatable('reload');
            });

            // Check all visible rows
            $($(table).data('kt-parent')).find('[data-kt-action="check-all"]').on('click', function () {
                $(table).KTDatatable('setActiveAll', true);
            });

            // Uncheck all selected rows
            $($(table).data('kt-parent')).find('[data-kt-action="uncheck-all"]').on('click', function () {
                $(table).KTDatatable('setActiveAll', false);
            });

            // Remove row(s)
            document.addEventListener('click',function(e){
                let clickedElement = (e.target.tagName == 'button') ? e.target : e.target.closest('button');
                let thisTable = (clickedElement) ? clickedElement.closest('.datatable') : null;

                if (clickedElement && clickedElement.dataset.ktAction == 'remove-rows'){
                    e.preventDefault();
                
                    let activeIds = [];
                    let cells = thisTable.querySelectorAll('.datatable-row-active:visible').querySelector('[data-id]');

                    for (const c in cells) {
                        let cellId = cells[c].dataset.id;
                        if (!activeIds.includes(cellId)) {
                            activeIds.push(cellId);
                        }
                    }

                    window.DCMS.deleteModel({
                        id: activeIds,
                        route: thisTable.dataset.ktDestroyMultipleRoute,
                        confirmTitle: (thisTable.dataset.ktDeleteRowsConfirmTitle) ? Lang(thisTable.dataset.ktDeleteRowsConfirmTitle) : Lang('Delete rows'),
                        confirmMsg: (thisTable.dataset.ktDeleteRowsConfirmMessage) ? Lang(thisTable.dataset.ktDeleteRowsConfirmMessage) : Lang('Are you sure you want to delete these rows?'),
                        completeTitle: (thisTable.dataset.ktDeleteRowsCompleteTitle) ? Lang(thisTable.dataset.ktDeleteRowsCompleteTitle) : Lang('Deleted rows'),
                        completeMsg: (thisTable.dataset.ktDeleteRowsCompleteMessage) ? Lang(thisTable.dataset.ktDeleteRowsCompleteMessage) : Lang('The rows have been succesfully deleted.'),
                        failedTitle: (thisTable.dataset.ktDeleteRowsFailedTitle) ? Lang(thisTable.dataset.ktDeleteRowsFailedTitle) : Lang('Deleting failed'),
                        failedMsg: (thisTable.dataset.ktDeleteRowsFailedMessage) ? Lang(thisTable.dataset.ktDeleteRowsFailedMessage) : Lang('The rows can\'t be deleted. They might still be required somewhere.'),
                    });
                }
            });

            // Export all data to an excel sheet
            document.addEventListener('click',function(e){
                let clickedElement = (e.target.tagName == 'button') ? e.target : e.target.closest('button');
                let thisTable = (clickedElement) ? clickedElement.closest('.datatable') : null;

                if (clickedElement && clickedElement.dataset.ktAction == 'export'){
                    e.preventDefault();
                
                    let data = datatable.dataSet;
                    let route = thisTable.dataset.ktExportRoute;

                    window.axios({
                        method: 'POST',
                        url: route,
                        data: datatable.dataSet,
                        headers: {
                            'X-CSRF-TOKEN': document.querySelectorAll('meta[name=csrf-token]')[0].content,
                            "Content-type": "application/x-www-form-urlencoded",
                            'X-Requested-With': 'XMLHttpRequest',
                        }
                    }).then(function (response) {
                        window.open(response, '_blank');
                    });
                }
            });

            // Create a new object, route has to be defined
            document.addEventListener('click',function(e){
                let clickedElement = (e.target.tagName == 'button' || e.target.tagName == 'a') ? e.target : e.target.closest('button');
                let thisTable = (clickedElement) ? clickedElement.closest('.datatable') : null;

                if (clickedElement && clickedElement.dataset.ktAction == 'create'){
                    e.preventDefault();
                
                    let id = clickedElement.dataset.id;
                    let route = thisTable.dataset.ktCreateRoute;

                    if (window.DCMS.AllowNewTab == false) {
                        window.location.href = route;
                    } else {
                        window.open(route, '_blank');
                    }
                }
            });

            // Edit an object, route has to be defined
            document.addEventListener('click',function(e){
                let clickedElement = (e.target.tagName == 'button') ? e.target : e.target.closest('button');
                let thisTable = (clickedElement) ? clickedElement.closest('.datatable') : null;

                if (clickedElement && clickedElement.dataset.ktAction == 'edit'){
                    e.preventDefault();
                
                    let id = clickedElement.dataset.id;
                    let route = thisTable.dataset.ktEditRoute.replace('__id__', id);

                    if (window.DCMS.AllowNewTab == false) {
                        window.location.href = route;
                    } else {
                        window.open(route, '_blank');
                    }
                }
            });

            // Open a link generated in the datatable
            document.addEventListener('click',function(e){
                let clickedElement = (e.target.tagName == 'button') ? e.target : e.target.closest('button');

                if (clickedElement && clickedElement.dataset.ktAction == 'link'){
                    e.preventDefault();
                
                    let link = clickedElement.href;
                    let target = clickedElement.dataset.ktTarget;
                    if (target == '_blank') {
                        window.open(link, '_blank');
                    } else {
                        window.location.href = link;
                    }
                }
            });

            // Dynamically delete row(s)
            document.addEventListener('click',function(e){
                let clickedElement = (e.target.tagName == 'button') ? e.target : e.target.closest('button');
                let thisTable = (clickedElement) ? clickedElement.closest('.datatable') : null;

                if (clickedElement && clickedElement.dataset.ktAction == 'destroy'){
                    e.preventDefault();
                
                    let id = clickedElement.dataset.id;
                    let route = thisTable.dataset.ktDestroyRoute.replace('__id__', id);
                    window.DCMS.deleteModel({
                        id: id,
                        route: route,
                        confirmTitle: (thisTable.dataset.ktDeleteSingleConfirmTitle) ? Lang(thisTable.dataset.ktDeleteSingleConfirmTitle) : Lang('Delete object'),
                        confirmMsg: (thisTable.dataset.ktDeleteSingleConfirmMessage) ? Lang(thisTable.dataset.ktDeleteSingleConfirmMessage) : Lang('Are you sure you want to delete this object?'),
                        completeTitle: (thisTable.dataset.ktDeleteSingleCompleteTitle) ? Lang(thisTable.dataset.ktDeleteSingleCompleteTitle) : Lang('Deleted object'),
                        completeMsg: (thisTable.dataset.ktDeleteSingleCompleteMessage) ? Lang(thisTable.dataset.ktDeleteSingleCompleteMessage) : Lang('The object has been succesfully deleted.'),
                        failedTitle: (thisTable.dataset.ktDeleteSingleFailedTitle) ? Lang(thisTable.dataset.ktDeleteSingleFailedTitle) : Lang('Deleting failed'),
                        failedMsg: (thisTable.dataset.ktDeleteSingleFailedMessage) ? Lang(thisTable.dataset.ktDeleteSingleFailedMessage) : Lang('This object can\'t be deleted. It might still be required somewhere.'),
                    });
                }
            });
    });
};