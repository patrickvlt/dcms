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
https://github.com/pqina/filepond

Use:
```
data-type="filepond"
data-prefix="brand"
data-column="logo"
data-mime="image"
```

On an input element, to initialise Filepond.
You can also setup other options through data attributes, such as:

```
data-max-files="1"
data-instant-upload="true"
```

Filepond automatically uploads files to a route based on their mime type. This way files are organized, and assigned by file name to objects.
This also makes uploading and deleting files dynamic, to set everything up efficiently.
These routes can be changed in the plugin settings in dcms.js.

The route for uploading files is defined in filepond.js:

```
'/dcms/file/process/'+inputElement.dataset.mime
```

The route for deleting files is also defined in filepond.js:

```
'/dcms/file/delete/'+inputElement.dataset.mime
```

## Splide JS

To easily view a gallery of uploaded files (right above a Filepond input, for example), you can use SplideJS like this:
```
@if($album && $album->pictures)
<div data-type="splide" data-height="300px" data-per-page=4 data-source={{ $album->pictures ?? '' }} data-prefix="album" data-column="pictures"></div>
@endif
```

The if statement is not necessary, but in this example the element won't show unless a defined Album class has pictures in it's column.

Just use this on a single div element:
```
data-type="splide"
```

Other options to pass to Splide:
```
data-height="300px" 
data-per-page=4 
data-source={{ $album->pictures ?? '' }} 
data-source=https://i.picsum.photos/id/190/200/200.jpg?hmac=WWXFTlTLvsXZseURWcXXDOC8Ie54it2IFL1gasrgrMQ
data-prefix="album" 
data-column="pictures"
```

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
            <h3 class="card-label">{{ __('Alles In Een Pakketten') }}</h3>
        </div>
    </div>
    <div class='card-body'>
        <div class='mb-7'>
            <div class='row align-items-center'>
                <div class='col-lg-9 col-xl-8'>
                    <div class='row align-items-center'>
                        <div class='col-md-4 my-2 my-md-0'>
                            <div class='input-icon'>
                                <input type='text' class='form-control' placeholder={{ __('Zoeken') }}
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
            <div class="kt_datatable_rowcontrols" style="display:none">
                <div class='input-group'>
                    <div class='input-group-prepend'>
                        <span class='input-group-text'>{{ __('ID:') }}</span>
                    </div>
                    <input type='text' class='form-control' id='kt_datatable_check_input' value='1' />
                        <div class='input-group-append'>
                            <button class='btn btn-secondary font-weight-bold' type='button'
                                id='kt_datatable_check'>{{ __('Selecteer rij') }}</button>
                        </div>
                </div>
            </div>
            </div>
            <div class='col-lg-10'>
                <button class='btn btn-light font-weight-bold' type='button'
                    id='kt_datatable_reload'>{{ __('Vernieuwen') }}</button>
                <div class="kt_datatable_rowcontrols" style="display:none">
                    <button class='btn btn-light font-weight-bold' type='button'
                        id='kt_datatable_check_all'>{{ __('Selecteer alle rijen') }}</button>
                    <button class='btn btn-light font-weight-bold' type='button'
                        id='kt_datatable_uncheck_all'>{{ __('Deselecteer alle rijen') }}</button>
                    <button class='btn btn-light font-weight-bold' type='button'
                        id='kt_datatable_remove_row'>{{ __('Verwijder geselecteerde rijen') }}</button>
                </div>
            </div>
        </div>
        <div class='datatable datatable-bordered datatable-head-custom' id='pkgaioDT' data-route='/pkgaio'
            data-edit-route={{ route('pkgaio.edit','__id__') }} 
            data-destroy-route={{ route('pkgaio.destroy','__id__') }} 
            data-page-size=20 
            data-pagination=true 
            data-scrolling=false
            data-include-actions=true 
            data-include-selector=true 
            data-delete-rows-confirm-title='{{ __('Verwijder pakketten') }}'
            data-delete-rows-confirm-message='{{ __('Weet u zeker dat u deze pakketten wil verwijderen?') }}'
            data-delete-rows-complete-title='{{ __('Pakketten verwijderd') }}'
            data-delete-rows-complete-message='{{ __('De pakketten zijn verwijderd.') }}'
            data-delete-rows-failed-title='{{ __('Verwijderen mislukt') }}'
            data-delete-rows-failed-message='{{ __('De pakketten konden niet verwijderd worden.') }}'
            data-delete-single-confirm-title="{{ __('Verwijder pakket') }}"
            data-delete-single-confirm-message="{{ __('Weet u zeker dat u dit pakket wil verwijderen?') }}"
            data-delete-single-complete-title="{{ __('Pakket verwijderd') }}"
            data-delete-single-complete-message="{{ __('Het pakket is verwijderd.') }}"
            data-delete-single-failed-title="{{ __('Verwijderen mislukt') }}"
            data-delete-single-failed-message="{{ __('Het pakket kan niet verwijderd worden.') }}"
            >
            <div id='tableColumns'>
                <div data-title="{{ __('Provider') }}" data-column='provider' data-type="property" data-property="name" data-width="150"></div>
                <div data-title="{{ __('Naam') }}" data-column='name' data-width="150"></div>
                <div data-title="{{ __('Prijs') }}" data-column='price' data-type='price' data-currency='€' data-width="150"></div>
                <div data-title="{{ __('Snelheid') }}" data-column='speed' data-append=' {{ __('Mbit/s') }}' data-width="150"></div>
                <div data-title="{{ __('Kanalen') }}" data-column='channels' data-width="150"></div>
                <div data-title="{{ __('Belminuten') }}" data-column='minutes' data-width="150"></div>
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
