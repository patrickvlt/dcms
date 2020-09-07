"use strict";

const { sortBy, isSet } = require("lodash");

var tables = $('.datatable');
var custColumns;

require('./ktdatatable.js');

window.DCMSDatatable = function (parameters) {
	window.KTDebug = false;
	$.each(parameters.table, function (key, table) {
		let columns = [];

		if (table.dataset.ktDebug){
			window.KTDebug = true;
		}

		if (table.dataset.ktIncludeSelector !== 'false') {
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

		if (table.dataset.ktIncludeControls !== 'false') {
			$('[data-kt-type="controls"]').show();
		}

		let tableColumns = $(table).find('[data-kt-type="columns"]').children();

		$.each(tableColumns, function (index, column) {
			
			let textColor, value, spotlightClass, prepend, append, target, useRow, sortable;

			if (column.dataset.ktSortable == 'false'){
				sortable = false;
			} else if (typeof column.dataset.ktSortable == 'undefined' || column.dataset.ktSortable == null){
				sortable = true;
			} 
			else {
				sortable = true;
			}

			let newColumn = {
				field: column.dataset.ktColumn,
				title: column.dataset.ktTitle,
				order: column.dataset.ktOrder,
				width: column.dataset.ktWidth,
				autoHide: column.dataset.ktAutoHide,
				type: column.dataset.ktType,
				sortable: sortable,
				align: (column.dataset.ktAlign) ? column.dataset.ktAlign : 'center',
				template: function (row) {
					if (typeof column.dataset.ktObject !== 'undefined' && row[column.dataset.ktObject] !== null){
						useRow = row[column.dataset.ktObject];
					} else {
						useRow = row;
					}
					
					value = useRow[column.dataset.ktColumn];
					value = (typeof value == 'undefined' || value == null) ? '' : value;
					
					prepend = (typeof column.dataset.ktPrepend !== 'undefined' && column.dataset.ktPrepend !== null) ? column.dataset.ktPrepend : '';
					append = (typeof column.dataset.ktAppend !== 'undefined' && column.dataset.ktAppend !== null) ? column.dataset.ktAppend : '';

					if (column.dataset.ktHref){
						let link,columnMatch,linkFromMatch;
						link = column.dataset.ktHref;
						target = (column.dataset.ktTarget) ? column.dataset.ktTarget : '';
						if (link.match(/__.*__/gm)){
							columnMatch = link.match(/__.*__/gm);
							linkFromMatch = columnMatch[0].replace(/__/g, '');
							link = link.replace(/__/g, '');
							if (useRow[linkFromMatch]){
								link = link.replace(linkFromMatch,'');
								link = link + useRow[linkFromMatch];
							}
						}
						value = `<a data-kt-target='`+target+`' data-kt-action="link" href='`+link+`'>`+value+`</a>`;
					}

					textColor = (column.dataset.ktTextColor) ? column.dataset.ktTextColor : 'dark';
					switch (column.dataset.ktType) {
						case 'card':
							var cardTitle = (useRow[column.dataset.ktCardTitle]) ? useRow[column.dataset.ktCardTitle] : '';
							var cardInfo = (useRow[column.dataset.ktCardInfo]) ? useRow[column.dataset.ktCardInfo] : '';;
							var cardImgText = '';
							var cardImg;
							// if data-card-image is set and the column has a filled URL
							if (typeof useRow[column.dataset.ktCardImage] !== 'undefined' && column.dataset.ktCardImage.length > 1 && (useRow[column.dataset.ktCardImage] !== column.dataset.ktCardImage && useRow[column.dataset.ktCardImage] !== null)) {
								jQuery.ajax({
									type: "GET",
									async: false,
									crossDomain: true,
									url: useRow[column.dataset.ktCardImage],
									success: function (response) {
										cardImg = useRow[column.dataset.ktCardImage];
										cardImgText = '';
										return cardImgText;
									},
									error: function (response) {
										cardImg = 'null';
										cardImgText = cardTitle[0].toUpperCase();
										return cardImgText;
									}
								});
							} else {
								cardImgText = cardTitle[0].toUpperCase();
							}

							var cardColor = (column.dataset.ktCardColor) ? column.dataset.ktCardColor : '';
							var cardTextColor = (column.dataset.ktCardTextColor) ? column.dataset.ktCardTextColor : 'white';
							var titleColor = (column.dataset.ktTitleColor) ? column.dataset.ktTitleColor : 'primary';

							return `<div data-id='` + useRow['id'] + `'><span style="width: 250px;"><div class="d-flex align-items-center">
									<div class="symbol symbol-40 symbol-`+ cardColor + ` flex-shrink-0">
										<div class="symbol-label text-` + cardTextColor + `" style="background-image:url('`+ cardImg + `')">` + cardImgText + `</div>
									</div>
									<div class="ml-2">
										<div class="text-` + titleColor + ` font-weight-bold line-height-sm">`+ cardTitle + `</div> <a class="font-size-sm text-` + textColor + ` text-hover-primary">`+ cardInfo + `</a>
									</div>
									</div>
								</div>
							</span>`;
							break;
						case 'boolean':
							if (useRow[column.dataset.ktColumn] !== null && useRow[column.dataset.ktColumn] !== 0 && typeof useRow[column.dataset.ktColumn] !== 'undefined') {
								return `<i data-id='`+useRow.id+`' class="fas fa-check text-` + textColor + `" style="max-height:`+column.dataset.ktMaxHeight+`"></i>`;
							}
							else {
								return '';
							}
							break;
						case 'text':
							return `<div data-id='`+useRow.id+`' style="max-height:`+column.dataset.ktMaxHeight+`" class="text-`+textColor+`">`+prepend+value+append+`</div>`;
							break;
						case 'icon':
							let icon = (column.dataset.iconClass && (typeof useRow[column.dataset.ktColumn] !== 'undefined' && useRow[column.dataset.ktColumn] !== null)) ? `<i class="d-inline `+column.dataset.ktIconClass+` text-muted"></i>` : ``;
							return `<div data-id='`+useRow.id+`' style="max-height:`+column.dataset.ktMaxHeight+`" class="text-`+textColor+`">`+icon+prepend+value+append+`</div>`;
							break;
						case 'price':
							let currency = (column.dataset.ktCurrency) ? column.dataset.ktCurrency : '€';
							value = (value == '') ? 0 : value;
							return `<div data-id='`+useRow.id+`' style="max-height:`+column.dataset.ktMaxHeight+`" class="text-`+textColor+`">`+currency+prepend+value+append+`,-`+`</div>`;
							break;
						case 'image':
							var changeControl = `<label class="btn btn-xs btn-icon btn-circle btn-white btn-hover-text-primary " data-kt-action="change" data-toggle="tooltip" title="" data-original-title="Change avatar">
								<i class="fa fa-pen icon-sm text-muted"></i>
								<input type="file" name="profile_avatar" accept=".png, .jpg, .jpeg">
								<input type="hidden" name="profile_avatar_remove">
							</label>`;
							var deleteControl = `<span class="btn btn-xs btn-icon btn-circle btn-white btn-hover-text-primary " data-kt-action="remove" data-toggle="tooltip" title="" data-original-title="Remove avatar">
								<i class="ki ki-bold-close icon-xs text-muted"></i>
							</span>`;
							if (column.dataset.ktAllowControls !== 'true'){
								changeControl = '';
								deleteControl = '';
							} 
							spotlightClass = (value !== null) ? 'spotlight' : '';

							if (value instanceof Array && value.length > 1){
								value = value[0];
							}

							return `<div class="image-input mb-4 mt-4" data-id='`+useRow.id+`' style="max-height:`+column.dataset.ktMaxHeight+`">
								<div class="image-input-wrapper `+spotlightClass+`" data-src='`+prepend+value+append+`' style="background-image: url(`+prepend+value+append+`)"></div>
								`+changeControl+`
								`+deleteControl+`
							</div>`;
							break;
						default:
							return prepend+value+append;
							break;
					}
				},
			};
			columns.push(newColumn);
		});

		if (typeof parameters.customColumns !== 'undefined' && parameters.customColumns !== null) {
			$.each(parameters.customColumns, function (key, customCol) {
				columns.push(customCol);
			});
		}

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

		columns.sort((a, b) => {
		if (a.order < b.order) return -1
		return a.order > b.order ? 1 : 0
		})

		let datatable = $(table).KTDatatable({
			// datasource definition
			data: {
				type: 'remote',
				source: {
					read: {
						method: 'GET',
						url: table.dataset.ktRoute,
					},
				},
				pageSize: parseInt(table.dataset.ktPageSize), // display 20 records per page
				serverPaging: false,
				serverFiltering: false,
				serverSorting: false,
			},

			// layout definition
			layout: {
				scroll: (table.dataset.ktScrolling) == 'false' ? false : true, // enable/disable datatable scroll both horizontal and vertical when needed.
				height: parseInt(table.dataset.ktHeight), // datatable's body's fixed height
				footer: false, // display/hide footer
			},

			// column sorting
			sortable: true,

			pagination: (table.dataset.ktPagination) == 'false' ? false : true,

			search: {
				input: $($(table).data('kt-parent')).find('[data-kt-action="search"]'),
				key: 'generalSearch'
			},

			// columns definition
			columns: columns,

		});

		$($(table).data('kt-parent')).find('[data-kt-action="init"]').on('click', function () {
			datatable = $(table).KTDatatable(options);
		});

		$($(table).data('kt-parent')).find('[data-kt-action="reload"]').on('click', function () {
			$(table).KTDatatable('reload');
		});

		$($(table).data('kt-parent')).find('[data-kt-action="check-all"]').on('click', function () {
			$(table).KTDatatable('setActiveAll', true);
		});

		$($(table).data('kt-parent')).find('[data-kt-action="uncheck-all"]').on('click', function () {
			$(table).KTDatatable('setActiveAll', false);
		});

		$($(table).data('kt-parent')).find('[data-kt-action="sort-asc"]').on('click', function() {
			datatable.sort('name', 'asc');
		});

		$($(table).data('kt-parent')).find('[data-kt-action="sort-desc"]').on('click', function() {
			datatable.sort('name', 'desc');
		});

		$($(table).data('kt-parent')).find('[data-kt-action="remove-rows"]').on('click', function () {
			let activeIds = [];
			let cells = $($(table).data('kt-parent')).find('.datatable-row-active').find('[data-id');

			$.each(cells, function (x, cell) {
				let cellId = $(cell).data('id');
				if (!activeIds.includes(cellId)) {
					activeIds.push(cellId);
				}
			});
			
			DeleteModel({
				id: activeIds,
				route: $(table).data('kt-destroy-route'),
				confirmTitle: (table.dataset.ktDeleteRowsConfirmTitle) ? Lang(table.dataset.ktDeleteRowsConfirmTitle) : Lang('Delete rows'),
				confirmMsg: (table.dataset.ktDeleteRowsConfirmMessage) ? Lang(table.dataset.ktDeleteRowsConfirmMessage) : Lang('Are you sure you want to delete these rows?'),
				completeTitle: (table.dataset.ktDeleteRowsCompleteTitle) ? Lang(table.dataset.ktDeleteRowsCompleteTitle) : Lang('Deleted rows'),
				completeMsg: (table.dataset.ktDeleteRowsCompleteMessage) ? Lang(table.dataset.ktDeleteRowsCompleteMessage) : Lang('The rows have been succesfully deleted.'),
				failedTitle: (table.dataset.ktDeleteRowsFailedTitle) ? Lang(table.dataset.ktDeleteRowsFailedTitle) : Lang('Deleting failed'),
				failedMsg: (table.dataset.ktDeleteRowsFailedMessage) ? Lang(table.dataset.ktDeleteRowsFailedMessage) : Lang('The rows can\'t be deleted. They might still be required somewhere.'),
			});
		});

		window.KTAllowMoreOn = [];
		window.KTAllowLessOn = [];
		window.KTForceExactOn = [];
		window.KTRemoveFilters = [];

		$.each($($(table).data('kt-parent')).find('[data-kt-filter]'), function (key, filter) {
		    $(filter).on('change', function (filter) {
				if (!filter.currentTarget.dataset.ktFilterCustom){
					if (filter.currentTarget.type !== 'checkbox') {
						(filter.currentTarget.dataset.ktAllowMore == 'true' && !window.KTAllowMoreOn.includes(filter.currentTarget.dataset.ktFilter)) ? window.KTAllowMoreOn.push(filter.currentTarget.dataset.ktFilter): '';
						(filter.currentTarget.dataset.ktAllowLess == 'true' && !window.KTAllowLessOn.includes(filter.currentTarget.dataset.ktFilter)) ? window.KTAllowLessOn.push(filter.currentTarget.dataset.ktFilter): '';
						(filter.currentTarget.dataset.ktForceExact == 'true' && !window.KTForceExactOn.includes(filter.currentTarget.dataset.ktFilter)) ? window.KTForceExactOn.push(filter.currentTarget.dataset.ktFilter): '';
						datatable.search(filter.currentTarget.value, filter.currentTarget.dataset.ktFilter);
					} else {
						if (this.checked == true) {
							if (this.value && this.value !== 'on'){
								datatable.search(this.value, this.dataset.ktFilter);
							} else {
								datatable.search("1", this.dataset.ktFilter);
							}
						} else {
							datatable.search("", this.dataset.ktFilter);
						}
					}
				}
		    });
		});

		$(document).on('click', 'table [data-kt-action=edit]', function (e) {
			e.preventDefault();
			let id = e.currentTarget.dataset.id;
			let route = $(table).data('kt-edit-route').replace('__id__', id);
			if (window.AllowNewTab == false){
				if ($(this).data('kt-load-in-modal')){
					window.LoadInModal(route,$(this).data('kt-load-in-modal'))
				} else {
					window.location.href = route;
				}
			} else {
				window.open(route, '_blank');
			}
		});

		$(document).on('click', 'table [data-kt-action=select]', function (e) {
			e.preventDefault();
			let id = e.currentTarget.dataset.id;
			let route = $(table).data('kt-edit-route').replace('__id__', id);
			if (window.AllowNewTab == false){
				window.location.href = route;
			} else {
				window.open(route, '_blank');
			}
		});
		
		$(document).on('click', 'table [data-kt-action=link]', function (e) {
			e.preventDefault();
			let link = e.currentTarget.href;
			let target = e.currentTarget.dataset.ktTarget;
			if (target == '_blank'){
				window.open(link, '_blank');
			} else {
				window.location.href = link;
			}
		});

		$(document).on('click', 'table [data-kt-action=destroy]', function (e) {
			e.preventDefault();
			let id = e.currentTarget.dataset.id;
			let route = $(table).data('kt-destroy-route').replace('__id__', id);
			DeleteModel({
				id: id,
				route: route,
				confirmTitle: (table.dataset.ktDeleteSingleConfirmTitle) ? Lang(table.dataset.ktDeleteSingleConfirmTitle) : Lang('Delete object'),
				confirmMsg: (table.dataset.ktDeleteSingleConfirmMessage) ? Lang(table.dataset.ktDeleteSingleConfirmMessage) : Lang('Are you sure you want to delete this object?'),
				completeTitle: (table.dataset.ktDeleteSingleCompleteTitle) ? Lang(table.dataset.ktDeleteSingleCompleteTitle) : Lang('Deleted object'),
				completeMsg: (table.dataset.ktDeleteSingleCompleteMessage) ? Lang(table.dataset.ktDeleteSingleCompleteMessage) : Lang('The object has been succesfully deleted.'),
				failedTitle: (table.dataset.ktDeleteSingleFailedTitle) ? Lang(table.dataset.ktDeleteSingleFailedTitle) : Lang('Deleting failed'),
				failedMsg: (table.dataset.ktDeleteSingleFailedMessage) ? Lang(table.dataset.ktDeleteSingleFailedMessage) : Lang('This object can\'t be deleted. It might still be required somewhere.'),
			});
		});
	});
}