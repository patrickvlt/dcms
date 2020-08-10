# DCMS

DCMS is a package made to boost productivity, and speed up the usual workflow when developing CRUD applications.

# JS plugins

DCMS has a lot of JS plugins, ready to use (based on the data attributes you specify in HTML elements).

## JQ confirm alerts
https://craftpip.github.io/jquery-confirm/

These alerts are used by default by DCMS. To make alerts show up, specify three parameters.

The first parameter is the kind of alert, you can choose between: success, error, warning, info, question. This will make the alerts' border and text use Bootstraps' color.
The second parameter is the title.
The third parameter is the message.

You can also easily add buttons, specify their text, class and/or action when you click on them.

```
Alert('error', 'Something went wrong!', 'Please check all filled in elements. You might have forgotten a required one.', {
    confirm: {
        text: 'Ok',
        btnClass: 'btn-danger',
    },
    cancel: {
        text: 'Go back',
        btnClass: 'btn-dark',
        action: function(){
            window.location.href = 'http://www.foo.com/'
        }
    }
});
```

## Datepicker
https://jqueryui.com/datepicker/

Use:

```
data-type="datepicker"
```

To initialise datepicker on an input element.
You can pass additional options with the following attributes:
```
data-auto-close=false 
data-format="DD/MM/YYYY" 
data-week-start=1 
```

If you don't specify a date format, you can also specify this in the DCMS JS settings file.

```
window.AppDateFormat = 'DD/MM/YYYY'
```

## Slimselect
https://slimselectjs.com/

Use:

```
data-type="slimselect"
```

On a select element to initialise slimselect.
You can pass a route to add new options (this will add a + symbol in the element):
```
data-add-route="/url"
```

## Spotlight
https://github.com/nextapps-de/spotlight

Use:

```
data-src="img1.jpg"
```
On any element, or:

```
class="spotlight"
```

On an anchor element, to initialise spotlight.
When you click on this element, the image will be shown in fullscreen.

## Filepond

## jExcel
https://bossanova.uk/jexcel/v3/

Use:
```
data-type="jexcel"
```

On a table element, to initialise jExcel.
To easily submit the data to a Laravel controller, put this table element in a form.
Don't forget to use enctype="multipart/form-data" in your table element.

Then use this in the table element:

```
data-jexcel-form-selector=""
```

To specify how many empty rows you want when jExclel loads:

```
data-jexcel-emptyrows="1" 
```

To prepare the columns, you can add one tr with th's, and use data attributes to set up the plugin.
Example code:

```
<table id="fooTable" data-type="jexcel" data-jexcel-emptyrows="1" data-jexcel-form-selector="#fooSubmit">
    <tr>
        <th data-jexcel-type="text" data-jexcel-width="200">{{ __('Foo') }}</th>
        <th data-jexcel-type="dropdown" data-jexcel-width="200" data-jexcel-url="/bar/sources" data-jexcel-autocomplete="true">{{ __('Bar') }}</th>
    </tr>
</table>
```
You can use the data-types from jExcel.

To easily retrieve sources for a dropdown, link a dropdown data attribute to a URL/route:

```
data-jexcel-type="dropdown" data-jexcel-url="/bar/sources"
```

# Commands

DCMS provides a few commands to make things easier, such as updating the composer package (small cmd), but also the CRUD generator.
This command will guide you through making a new object, including migrations, routes, validation rules, all of it.
Based on the answers you provide, the same files your normally work with will be filled.

To generate CRUD functionality for a new model, run:
```
php artisan dcms:crud {Name}
```
To update this package easily:
```
php artisan dcms:update
```
To just publish the JS and SASS resources (again), run:
```
php artisan dcms:publish
```

# Helpers

Remember, most of these helpers will work correctly if you use the normal Laravel routes.
If for instance, you're using slugs, these might not work correctly, as they depend on Laravels' routes.

```
MaxSizeServer('mb')
```

Returns the current max file size allowed on the server (PHP.ini). In the parameter you can pass MB or bytes.

```
GetPrefix()
```

This gets the prefix of a model being used in a route. So if you're on route /url/post/3/edit, the prefix would return post.

```
FindClass($prefix)
```

This returns the class which belongs to the prefix you pass in. FindClass('post') would return Post.
This helps making the controllers dynamic.

```
Model()
```

This returns the current object being used. For instance, if you're on route /url/post/1/edit, Model() would be the same as $post.
This also helps various back-end functions being able to access the current object being used. It can also be used in views.

```
FormMethod()
```

This returns the current method the form will use. If you're on route /url/post/1/edit, FormMethod() would return PUT.
On /url/post (when creating a new post), this would return POST.
Helpful to once again let the back-end of your application act different on the current action you're trying to perform on an object.

```
FormRoute() or DeleteRoute()
```

This returns the action the form will send a request to. This will grab the correct route for the object you're working on.

```
ValidateFile()
```

This will validate a file based on the max filesize on a server, extensions you will allow, and how many files at once are allowed.
This will also store the file in a folder and return the url to that same file.

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
                <div class='col-2 my-2 my-md-0'>
                    <div class='d-flex align-items-center'>
                        <label class='mr-3 mb-0 d-none d-md-block'>Name:</label>
                        <select class='form-control' data-type='slimselect' data-filter="name">
                                <option value="">{{ __('All') }}</option>
                            {{-- @foreach($userNames as $name) --}}
                                {{-- <option value="{{ $name }}">{{ $name }}</option> --}}
                            {{-- @endforeach --}}
                        </select>
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
