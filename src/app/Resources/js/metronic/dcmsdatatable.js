"use strict";

const { sortBy, isSet } = require("lodash");

var tables = $('.datatable');
var custColumns;

window.DCMSDatatable = function (parameters) {
	$.each(parameters.table, function (key, table) {
		let columns = [];

		if (table.dataset.includeSelector !== 'false') {
			columns.push({
				field: '',
				title: '',
				sortable: false,
				width: 30,
				type: 'number',
				selector: { class: 'kt-checkbox--solid' },
				textAlign: 'center'
			});
			$('.kt_datatable_rowcontrols').show();
		}

		let tableColumns = $(table).find('#tableColumns').children();
		$.each(tableColumns, function (index, column) {
			let textColor, value, spotlightClass;
			let newColumn = {
				field: column.dataset.title,
				title: column.dataset.title,
				order: column.dataset.order,
				width: column.dataset.width,
				type: column.dataset.type,
				align: (column.dataset.align) ? column.dataset.align : 'center',
				template: function (row) {
					if (column.dataset.type == 'property' && row[column.dataset.column] !== null){
						value = row[column.dataset.column][column.dataset.property];
					} else {
						value = row[column.dataset.column];
					}
					value = (typeof value == 'undefined' || value == null) ? '' : value;
					var textColor = (column.dataset.textColor) ? column.dataset.textColor : 'dark';
					switch (column.dataset.type) {
						case 'user':
							var userTitle = '';
							var userInfo = '';
							var userImgText = '';
							var userImg;
							// merge multiple columns to show one value
							if (column.dataset.userTitle) {
								userTitle = MergeColumns(row, column.dataset.userTitle);
							}
							if (column.dataset.userInfo) {
								userInfo = MergeColumns(row, column.dataset.userInfo);
							}
							// if data-user-image is set and the column has a filled URL
							if (typeof row[column.dataset.userImage] !== 'undefined' && column.dataset.userImage.length > 1 && (row[column.dataset.userImage] !== column.dataset.userImage && row[column.dataset.userImage] !== null)) {
								jQuery.ajax({
									type: "GET",
									async: false,
									crossDomain: true,
									url: row[column.dataset.userImage],
									success: function (response) {
										userImg = row[column.dataset.userImage];
										userImgText = '';
										return userImgText;
									},
									error: function (response) {
										userImg = 'null';
										userImgText = userTitle[0].toUpperCase();
										return userImgText;
									}
								});
							} else {
								userImgText = userTitle[0].toUpperCase();
							}

							var cardColor = (column.dataset.cardColor) ? column.dataset.cardColor : 'primary';
							var cardTextColor = (column.dataset.cardTextColor) ? column.dataset.cardTextColor : 'primary';
							var titleColor = (column.dataset.titleColor) ? column.dataset.titleColor : 'primary';

							return `<div data-id='` + row['id'] + `'><span style="width: 250px;"><div class="d-flex align-items-center">
									<div class="symbol symbol-40 symbol-`+ userColor + ` flex-shrink-0">
										<div class="symbol-label text-` + cardTextColor + `" style="background-image:url('`+ userImg + `')">` + userImgText + `</div>
									</div>
									<div class="ml-2">
										<div class="text-` + titleColor + ` font-weight-bold line-height-sm">`+ userTitle + `</div> <a href="#"
											class="font-size-sm text-` + textColor + ` text-hover-primary">`+ userInfo + `</a>
									</div>
									</div>
								</div>
							</span>`;
							break;
						case 'boolean':
							if (value !== null && value !== 0 && typeof value !== 'undefined') {
								return `<i data-id='`+row.id+`' class="fas fa-check text-` + textColor + `" style="max-height:`+column.dataset.maxHeight+`"></i>`;
							}
							else {
								return '';
							}
							break;
						case 'text':
							return `<div data-id='`+row.id+`' style="max-height:`+column.dataset.maxHeight+`" class="text-`+textColor+`">`+value+`</div>`;
							break;
						case 'image':
							spotlightClass = (value !== null) ? 'spotlight' : '';
							return `<div class="image-input mb-4 mt-4" data-id='`+row.id+`' style="max-height:`+column.dataset.maxHeight+`">
								<div class="image-input-wrapper `+spotlightClass+`" data-src='`+value+`' style="background-image: url(`+value+`)"></div>
								<label class="btn btn-xs btn-icon btn-circle btn-white btn-hover-text-primary " data-action="change" data-toggle="tooltip" title="" data-original-title="Change avatar">
									<i class="fa fa-pen icon-sm text-muted"></i>
									<input type="file" name="profile_avatar" accept=".png, .jpg, .jpeg">
									<input type="hidden" name="profile_avatar_remove">
								</label>
								<span class="btn btn-xs btn-icon btn-circle btn-white btn-hover-text-primary " data-action="cancel" data-toggle="tooltip" title="" data-original-title="Cancel avatar">
									<i class="ki ki-bold-close icon-xs text-muted"></i>
								</span>
								<span class="btn btn-xs btn-icon btn-circle btn-white btn-hover-text-primary " data-action="remove" data-toggle="tooltip" title="" data-original-title="Remove avatar">
									<i class="ki ki-bold-close icon-xs text-muted"></i>
								</span>
							</div>`;
							break;
						default:
							return `<div data-id='` + row['id'] + `' class="text-`+textColor+`">` + value + `</div>`;
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

		if (table.dataset.includeActions == 'true') {
			columns.push({
				field: 'Actions',
				title: Lang('Actions'),
				sortable: false,
				width: 125,
				overflow: 'visible',
				autoHide: true,
				template: function (row) {
					return `<td class="text-right pr-0">
						<button class="btn btn-icon btn-light btn-hover-primary btn-sm mx-3" data-action="edit" data-id="`+ row['id'] + `">
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
						<button class="btn btn-icon btn-light btn-hover-primary btn-sm" data-action="destroy" data-id="`+ row['id'] + `">
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

		function sortByKey(array, key) {
			return array.sort(function(a, b) {
				var x = a[key]; var y = b[key];
				return ((x < y) ? -1 : ((x > y) ? 1 : 0));
			});
		}

		columns.sort(function(a, b) {
		var keyA = a.order,
			keyB = b.order;
		if (keyA < keyB) return -1;
		if (keyA > keyB) return 1;
		return 0;
		});

		let datatable = $(table).KTDatatable({
			// datasource definition
			data: {
				type: 'remote',
				source: {
					read: {
						method: 'GET',
						url: table.dataset.route,
					},
				},
				pageSize: parseInt(table.dataset.pageSize), // display 20 records per page
				serverPaging: false,
				serverFiltering: false,
				serverSorting: false,
			},

			// layout definition
			layout: {
				scroll: (table.dataset.scrolling) == 'false' ? false : true, // enable/disable datatable scroll both horizontal and vertical when needed.
				height: parseInt(table.dataset.height), // datatable's body's fixed height
				footer: false, // display/hide footer
			},

			// column sorting
			sortable: true,

			pagination: (table.dataset.pagination) == 'false' ? false : true,

			search: {
				input: $(table).parent().find('#kt_datatable_search_query'),
				key: 'generalSearch'
			},

			// columns definition
			columns: columns,

		});

		$(table).parent().find('#kt_datatable_init').on('click', function () {
			datatable = $(table).KTDatatable(options);
		});

		$(table).parent().find('#kt_datatable_reload').on('click', function () {
			$(table).KTDatatable('reload');
		});

		// get checked record and get value by column name
		$(table).parent().find('#kt_datatable_get').on('click', function () {
			// select active rows
			datatable.rows('.datatable-row-active');
			// check selected nodes
			if (datatable.nodes().length > 0) {
				// get column by field name and get the column nodes
				var value = datatable.columns('CompanyName').nodes().text();
			}
		});

		$(table).parent().find('#kt_datatable_check').on('click', function () {
			var input = $('#kt_datatable_check_input').val();
			datatable.setActive(input);
		});

		$(table).parent().find('#kt_datatable_check_all').on('click', function () {
			$(table).KTDatatable('setActiveAll', true);
		});

		$(table).parent().find('#kt_datatable_uncheck_all').on('click', function () {
			$(table).KTDatatable('setActiveAll', false);
		});

		$(table).parent().find('#kt_datatable_remove_row').on('click', function () {
			let activeIds = [];
			let cells = $('.datatable-row-active').find('[data-id');

			$.each(cells, function (x, cell) {
				let cellId = $(cell).data('id');
				if (!activeIds.includes(cellId)) {
					activeIds.push(cellId);
				}
			});
			DeleteModel({
				id: activeIds,
				route: $(table).data('destroy-route'),
				confirmTitle: (table.dataset.deleteRowsConfirmTitle) ? Lang(table.dataset.deleteRowsConfirmTitle) : Lang('Delete rows'),
				confirmMsg: (table.dataset.deleteRowsConfirmMessage) ? Lang(table.dataset.deleteRowsConfirmMessage) : Lang('Are you sure you want to delete these rows?'),
				completeTitle: (table.dataset.deleteRowsCompleteTitle) ? Lang(table.dataset.deleteRowsCompleteTitle) : Lang('Deleted rows'),
				completeMsg: (table.dataset.deleteRowsCompleteMessage) ? Lang(table.dataset.deleteRowsCompleteMessage) : Lang('The rows have been succesfully deleted.'),
				failedTitle: (table.dataset.deleteRowsFailedTitle) ? Lang(table.dataset.deleteRowsFailedTitle) : Lang('Deleting failed'),
				failedMsg: (table.dataset.deleteRowsFailedMessage) ? Lang(table.dataset.deleteRowsFailedMessage) : Lang('The rows can\'t be deleted. They might still be required somewhere.'),
			});
		});

		$.each($(table).parent().find('[data-filter]'), function (key, filter) {
			$(filter).on('change', function () {
				datatable.search(this.value, this.dataset.filter);
			});
		});

		$(table).parent().find('#kt_datatable_search_status, #kt_datatable_search_type').selectpicker();

		$(document).on('click', 'table [data-action=edit]', function (e) {
			e.preventDefault();
			let id = e.currentTarget.dataset.id;
			let route = $(table).data('edit-route').replace('__id__', id);
			if (window.AllowNewTab == false){
				window.location.href = route;
			} else {
				window.open(route, '_blank');
			}
		});

		$(document).on('click', 'table [data-action=destroy]', function (e) {
			e.preventDefault();
			let id = e.currentTarget.dataset.id;
			let route = $(table).data('destroy-route').replace('__id__', id);
			DeleteModel({
				id: id,
				route: route,
				confirmTitle: (table.dataset.deleteSingleConfirmTitle) ? Lang(table.dataset.deleteSingleConfirmTitle) : Lang('Delete object'),
				confirmMsg: (table.dataset.deleteSingleConfirmMessage) ? Lang(table.dataset.deleteSingleConfirmMessage) : Lang('Are you sure you want to delete this object?'),
				completeTitle: (table.dataset.deleteSingleCompleteTitle) ? Lang(table.dataset.deleteSingleCompleteTitle) : Lang('Deleted object'),
				completeMsg: (table.dataset.deleteSingleCompleteMessage) ? Lang(table.dataset.deleteSingleCompleteMessage) : Lang('The object has been succesfully deleted.'),
				failedTitle: (table.dataset.deleteSingleFailedTitle) ? Lang(table.dataset.deleteSingleFailedTitle) : Lang('Deleting failed'),
				failedMsg: (table.dataset.deleteSingleFailedMessage) ? Lang(table.dataset.deleteSingleFailedMessage) : Lang('This object can\'t be deleted. It might still be required somewhere.'),
			});
		});
	});
}