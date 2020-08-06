# DCMS

DCMS is a package made to boost productivity, dynamic easily reusable functions to maintain a structured back-end in Laravel projects.

# DCMS Datatables

This is a simple JS function which easily initalises KTDatatable, working with data attributes. Purchase a Metronic license if you wish to use these tables.

Include these plugins:
```
<script src="assets/plugins/global/plugins.bundle.js?v=7.0.5"></script>
<script src="assets/plugins/custom/prismjs/prismjs.bundle.js?v=7.0.5"></script>
<script src="assets/js/scripts.bundle.js?v=7.0.5"></script>
```

Example HTML code:

```
<div class="card card-custom mt-5">
    <div class="card-header flex-wrap border-0 pt-6 pb-0">
        <div class="card-title">
            <h3 class="card-label">{{ __('Posts') }}</h3>
        </div>
        <div class="card-toolbar">
            <div class="dropdown dropdown-inline mr-2">
                <button type="button" class="btn btn-light-primary font-weight-bolder dropdown-toggle"
                    data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <span class="svg-icon svg-icon-md">
                        <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px"
                            height="24px" viewBox="0 0 24 24" version="1.1">
                            <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                <rect x="0" y="0" width="24" height="24" />
                                <path
                                    d="M3,16 L5,16 C5.55228475,16 6,15.5522847 6,15 C6,14.4477153 5.55228475,14 5,14 L3,14 L3,12 L5,12 C5.55228475,12 6,11.5522847 6,11 C6,10.4477153 5.55228475,10 5,10 L3,10 L3,8 L5,8 C5.55228475,8 6,7.55228475 6,7 C6,6.44771525 5.55228475,6 5,6 L3,6 L3,4 C3,3.44771525 3.44771525,3 4,3 L10,3 C10.5522847,3 11,3.44771525 11,4 L11,19 C11,19.5522847 10.5522847,20 10,20 L4,20 C3.44771525,20 3,19.5522847 3,19 L3,16 Z"
                                    fill="#000000" opacity="0.3" />
                                <path
                                    d="M16,3 L19,3 C20.1045695,3 21,3.8954305 21,5 L21,15.2485298 C21,15.7329761 20.8241635,16.200956 20.5051534,16.565539 L17.8762883,19.5699562 C17.6944473,19.7777745 17.378566,19.7988332 17.1707477,19.6169922 C17.1540423,19.602375 17.1383289,19.5866616 17.1237117,19.5699562 L14.4948466,16.565539 C14.1758365,16.200956 14,15.7329761 14,15.2485298 L14,5 C14,3.8954305 14.8954305,3 16,3 Z"
                                    fill="#000000" />
                            </g>
                        </svg>
                    </span>Export</button>
                <div class="dropdown-menu dropdown-menu-sm dropdown-menu-right">
                    <ul class="navi flex-column navi-hover py-2">
                        <li class="navi-header font-weight-bolder text-uppercase font-size-sm text-primary pb-2">
                            Choose an option:</li>
                        <li class="navi-item">
                            <a href="#" class="navi-link">
                                <span class="navi-icon">
                                    <i class="la la-print"></i>
                                </span>
                                <span class="navi-text">Print</span>
                            </a>
                        </li>
                        <li class="navi-item">
                            <a href="#" class="navi-link">
                                <span class="navi-icon">
                                    <i class="la la-copy"></i>
                                </span>
                                <span class="navi-text">Copy</span>
                            </a>
                        </li>
                        <li class="navi-item">
                            <a href="#" class="navi-link">
                                <span class="navi-icon">
                                    <i class="la la-file-excel-o"></i>
                                </span>
                                <span class="navi-text">Excel</span>
                            </a>
                        </li>
                        <li class="navi-item">
                            <a href="#" class="navi-link">
                                <span class="navi-icon">
                                    <i class="la la-file-text-o"></i>
                                </span>
                                <span class="navi-text">CSV</span>
                            </a>
                        </li>
                        <li class="navi-item">
                            <a href="#" class="navi-link pdfTableBtn" data-table="#kt_datatable">
                                <span class="navi-icon">
                                    <i class="la la-file-pdf-o"></i>
                                </span>
                                <span class="navi-text">PDF</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <div class='card-body'>
        <div class='mb-7'>
            <div class='row align-items-center'>
                <div class='col-lg-9 col-xl-8'>
                    <div class='row align-items-center'>
                        <div class='col-md-4 my-2 my-md-0'>
                            <div class='input-icon'>
                                <input type='text' class='form-control' placeholder={{ __('Search') }}
                                    id='kt_datatable_search_query' />
                                <span>
                                    <i class='flaticon2-search-1 text-muted'></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class='row py-5'>
            <div class='col-lg-2'>
                <div class='input-group'>
                    <div class='input-group-prepend'>
                        <span class='input-group-text'>{{ __('ID:') }}</span>
                    </div>
                    <input type='text' class='form-control' id='kt_datatable_check_input' value='1' />
                    <div class='input-group-append'>
                        <button class='btn btn-secondary font-weight-bold' type='button'
                            id='kt_datatable_check'>{{ __('Select row') }}</button>
                    </div>
                </div>
            </div>
            <div class='col-lg-10'>
                <button class='btn btn-light font-weight-bold' type='button'
                    id='kt_datatable_reload'>{{ __('Reload') }}</button>
                <button class='btn btn-light font-weight-bold' type='button'
                    id='kt_datatable_check_all'>{{ __('Select all rows') }}</button>
                <button class='btn btn-light font-weight-bold' type='button'
                    id='kt_datatable_uncheck_all'>{{ __('Unselect all rows') }}</button>
                <button class='btn btn-light font-weight-bold' type='button'
                    id='kt_datatable_remove_row'>{{ __('Remove active row(s)') }}</button>
            </div>
        </div>
        <div class='datatable datatable-bordered datatable-head-custom' id='post_datatable' data-route='/post'
            data-edit-route={{ route('post.edit','__id__') }} data-page-size=5 data-pagination=true data-scrolling=true
            data-include-actions=false data-delete-rows-confirm-title='Delete posts'
            data-delete-rows-confirm-message='Are you sure you want to delete these rows?'
            data-delete-rows-complete-title='Deleted posts'
            data-delete-rows-complete-message='The posts have been deleted.'
            data-delete-rows-failed-title='Deleting failed'
            data-delete-rows-failed-message='Deleting has failed. Maybe some posts are still being used.'
            data-height=500>
            <div id='tableColumns'>
                <div data-title='Post' data-column='title' data-width='50' data-type='text'></div>
                <div data-title='Content' data-column='content' data-width='200' data-max-height='75px'
                    data-type='text'></div>
                <div data-title='Thumbnail' data-column='thumbnail' data-width='200' data-type='image'></div>
            </div>
        </div>
    </div>
</div>
@endsection
@push('footer-scripts')
<script>
    DCMSDatatable({
        table: $('#user_datatable'),
        // customColumns: [{
        //     field: 'foo',
        //     title: 'Example',
        //     sortable: false,
        //     order: 1,
        //     width: 125,
        //     maxHeight: 10px,
        //     overflow: 'visible',
        //     autoHide: true,
        //     template: function (row) {
        //         return row.id;
        //     }
        // }],
    });
    DCMSDatatable({
        table: $('#post_datatable')
    });
</script>
@endpush
```
