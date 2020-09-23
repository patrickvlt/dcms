# DCMS
DCMS is a package made to boost productivity, and speed up the usual workflow when developing CRUD applications.

# JS plugins
DCMS has a lot of JS plugins, ready to use (based on the data attributes you specify in HTML elements).

## SweetAlerts
https://sweetalert2.github.io/


## Datepicker
https://jqueryui.com/datepicker/

Use:

```
data-type="datepicker"
```

To initialise datepicker on an input element.
You can pass additional options with the following attributes:
```
data-datepicker-auto-close=false 
data-datepicker-format="DD/MM/YYYY" 
data-datepicker-week-start=1 
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
data-slimselect-add-route="/url"
data-slimselect-add-column="status"
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
data-filepond-prefix="brand"
data-filepond-column="logo"
data-filepond-mime="image"
```

On an input element, to initialise Filepond.
You can also setup other options through data attributes, such as:

```
data-filepond-max-files="1"
data-filepond-instant-upload="true"
data-filepond-revert-key="value"
data-filepond-table-selector="#addressTable"
```

Filepond automatically uploads files to a route based on their mime type. This way files are organized, and assigned by file name to objects.
This also makes uploading and deleting files dynamic, to set everything up efficiently.
These routes can be changed in the plugin settings in dcms.js.

Processing files is automatically done by Filepond. When you specify a prefix, the DCMS Filepond controller will try to find a class which belongs to this prefix. Then it fetches a custom request file which belongs to this class. So if you follow Laravels naming convention, you wont have to customize this.
If you wish to use custom process and delete routes, specify them with:

```
data-filepond-process-url=""
data-filepond-revert-url=""
```

If you wish to upload a sheet automatically to a jExcel table, you can specify the table to use with this attribute:
```
data-filepond-table-selector="#addressTable"
```

Complete code example:
```html
<input data-type="filepond" data-filepond-mime="sheet" data-filepond-prefix="address" data-filepond-max-files="1" data-filepond-column="addressSheet" data-filepond-table-selector="#addressTable" type="file" id="sheet" name="sheet[]" aria-describedby="sheet" />
```

## Carousel
To easily view a gallery of uploaded files (right above a Filepond input, for example), you can use a dynamic Carousel function like this:
```html
@if(Model() && Model()->banner)
<div data-type="dcarousel" 
data-dcar-src="{{ json_encode(Model()->banner) }}"
data-dcar-prefix="post"
data-dcar-column="banner"
>
</div>
@endif
```

You can also set the height:
```
data-dcar-height="200px"
```

## jExcel
https://bossanova.uk/jexcel/v3/

Use:
```
data-type="jexcel"
```

On a table element, to initialise jExcel.
To easily submit the data to a Laravel controller, put this table element in a form (complete example code below).
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
Example code for an Address import:

```html
<table id="fooTable" data-type="jexcel" data-jexcel-emptyrows="1" data-jexcel-form-selector="#fooSubmit">
    <tr>
        <th data-jexcel-type="text" data-jexcel-width="200">{{ __('Foo') }}</th>
        <th data-jexcel-type="dropdown" data-jexcel-width="200" data-jexcel-url="/bar/sources" data-jexcel-autocomplete="true">{{ __('Bar') }}</th>
    </tr>
</table>
```
You can use the data-types from jExcel.

To easily retrieve sources for a dropdown, link a dropdown data attribute to a URL/route:

```html
data-jexcel-type="dropdown" data-jexcel-url="/bar/sources"
```

To automatically fix empty dropdown columns (when trying to use foreign keys/relationships, for example) you can try to submit the table to the DCMS controller, which contains a fix route.

```html
<button id="fixSheet" data-jexcel-fix-route={{ route('address.fixsheet') }} type="button" class="btn btn-secondary mr-2">{{ __('Autocorrect data') }}</button>
```

This is called autocorrect in DCMS. If you wish to use this, make sure you assign the correct columns/cell indexes to the fields you want to fix.
You can define these in your controller, in the DCMS function:

```php
// for jExcel imports
// which request attribute belongs to which jExcel column? e.g. 'name' => 0, 'created_at' => 3
'import' => [
    'columns' => [
        'address' => 0,
        'zipcode_id' => 1
    ],
    // which classes/route prefixes to use when trying to autocorrect?
     'autocorrect' => [
        'zipcode' => [
            // which column/cell in jExcel
            'column' => 1,
            // which fields to compare with
            'fields' => [
                'zipcode'
            ]
        ]
    ]
]
```

Complete code example:

```html
<form id="addressSubmit" action="{{ route('address.importsheet') }}" method="POST" enctype="multipart/form-data"  style="display:none">
    @method(FormMethod())
    @csrf
    <div class="form-group" data-type="filepond">
        <label>{{ __('Upload csv') }}</label>
        <input data-type="filepond" data-filepond-mime="sheet" data-filepond-prefix="address" data-filepond-max-files="1" data-filepond-column="addressSheet"
            type="file" id="sheet" name="sheet[]" aria-describedby="sheet" />
    </div>
    @error('sheet')
    <div class="invalid-feedback">{{ $errors->first('sheet') ?? '' }}</div>
    @enderror
    <div class="form-group">
        <table id="addressTable" data-type="jexcel" data-jexcel-emptyrows="1" data-jexcel-filepond="sheet"
            data-jexcel-form-selector="#addressSubmit">
            <tr>
                <th data-jexcel-type="text" data-jexcel-width="200">{{ __('Adres') }}</th>
                <th data-jexcel-type="dropdown" data-jexcel-width="200" data-jexcel-fetch-url="/zipcode/all" data-jexcel-fetch-column="zipcode" data-jexcel-autocomplete="true">{{ __('Postcode') }}</th>
            </tr>
        </table>
        <span class="form-text text-muted">{{ __('Controleer of de gegevens kloppen. U kunt ze hierin aanpassen.') }}</span>
    </div>
    <div class="form-group">
        <button id="submitTable" type="submit" class="btn btn-success mr-2">{{ __('Importeren') }}</button>
        <button id="fixSheet" data-jexcel-fix-route={{ route('address.fixsheet') }} type="button" class="btn btn-secondary mr-2">{{ __('Autocorrect data') }}</button>
    </div>
</form>
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


# DCMS (KT) Datatables
This is a simple JS function which easily initalises KTDatatable, working with data attributes. 
Purchase a Metronic license if you wish to use these tables.

To initialise a dynamic KTDatatable, use this code in your HTML as a reference:

```html
<div id="tableDiv">
    <div class='datatable datatable-bordered datatable-head-custom'
    data-kt-route='/api/fetch'
    data-kt-parent="#tableParentDiv">
        <div data-kt-type='columns'>
            <div data-kt-title="{{ __('Speed') }}" data-kt-order=1 data-kt-column='speed' data-kt-append=' Mbit/s' data-kt-width='100'></div>
            <div data-kt-title="{{ __('Price p.m.') }}" data-kt-order=2 data-kt-column='price' data-kt-prepend="€" data-kt-width='100'></div>
        </div>
    </div>
</div>
<script src="/js/jquery.js"></script>
<script src="/assets/plugins/global/plugins.bundle.js?v=7.0.5"></script>
<script src="/assets/plugins/custom/prismjs/prismjs.bundle.js?v=7.0.5"></script>
<script>
    // Initalise the table
    DCMSDatatable({
        table: $('.datatable')
    });
</script>
```

You can pass optional data-kt attributes to the datatable element:

Enable debug, which outputs filters and record values
```
data-kt-debug=true
```

Enable selectors, to select multiple rows
```
data-kt-include-selector=true
```

Enable a default Actions field, with an Edit and Delete button, which already have working functions in DCMS
```
data-kt-include-controls=true
```

Set the default page size
```
data-kt-page-size=20
```

To enable/disable pagination
```
data-kt-pagination=true
```

To enable/disable scrolling
```
data-kt-scrolling=true
```

You can also define custom messages when deleting a single row, or multiple rows (separately).
Use this as a reference:

```
data-kt-delete-rows-confirm-title='{{ __('Delete object?') }}'
data-kt-delete-rows-confirm-message='{{ __('Are you sure you want to delete this object?') }}'
data-kt-delete-rows-complete-title='{{ __('Object deleted') }}'
data-kt-delete-rows-complete-message='{{ __('The objects have been deleted succesfully.') }}'
data-kt-delete-rows-failed-title='{{ __('Deleting failed') }}'
data-kt-delete-rows-failed-message='{{ __('The objects couldn\'t be deleted.') }}'

data-kt-delete-single-confirm-title="{{ __('Delete object') }}"
data-kt-delete-single-confirm-message="{{ __('Are you sure you want to delete this object?') }}"
data-kt-delete-single-complete-title="{{ __('Object deleted') }}"
data-kt-delete-single-complete-message="{{ __('The object has been deleted.') }}"
data-kt-delete-single-failed-title="{{ __('Deleting failed') }}"
data-kt-delete-single-failed-message="{{ __('The object couldn\'t be deleted.') }}"
```

You can define the columns in your HTML element, and in JavaScript (example below).
These columns will work together, you can order them with the data-kt-order attribute or order property in JavaScript.

## To define columns in your HTML element:

```html
<!-- Place this div in your datatable element, make sure data-kt-type="columns" is set -->
<div data-kt-type="columns">
    <!-- Define your columns in HTML here -->
    <div data-kt-title="{{ __('Name') }}" data-kt-column="name" data-kt-width="100"></div>
</div>
```

Optional attributes which you can pass to your HTML columns:

Set the text color for this column
```
data-kt-text-color="primary"
```

Enable/disable sorting for this column
```
data-kt-sortable=false
```
Define the order/position of this column
```
data-kt-order=3
```
Define the width of this column
```
data-kt-width=250
```
Make the column hide automatically, or let it stay on top
```
data-kt-auto-hide=false
```

### Turn value into clickable link:

To turn the value into a link, make sure your column has a valid URL.
For example, if your column is "website": use data-kt-href="__website__" to generate the URL.
The low dashes will trigger DCMS to replace the link with the correct value.
If you don't use low dashes, you have to define a static URL, or else you will redirect users literally to __website__.
Then finally, use data-kt-target to specify if you want the link to open in a new tab.
Example:

```html
<div data-kt-title="{{ __('Website') }}" data-kt-column='website' data-kt-href="__website__" data-kt-target="_blank"></div>
```

You can also specify a type. These are predefined templates in DCMS.
Choose one with the data-kt-type="" attribute.

### The following types (data-kt-type) are available:

<h4>card</h4>

Generate a small card with a title and/or image with text in the column. (Note: data-kt-card-image should point to a column in your database)
```
data-kt-card-title=""
data-kt-card-info=""
data-kt-card-image=""
data-kt-card-color=""
data-kt-card-title-color=""
data-kt-card-text-color=""
```
<h4>boolean</h4> 
Generate a column which checks itself if the value is 1

<h4>icon</h4> 
Place an icon before the text

<h4>price</h4>
Turn the value into a price, combine with data-kt-currency to place a currency sign in front of it:

```
data-kt-type="price" data-kt-currency="€"
```
<h4>image</h4>
Show an image in this column, clickable since it has a spotlight class.


### Define KTDatatable columns in JavaScript
Note: It doesn't matter if you define them in HTML or JavaScript. The columns will be merged together into one datatable.

```html
<script>
    DCMSDatatable({
        table: $('.datatable'),
        // You can define columns in JavaScript aswell, these will be "merged" with your defined columns in your HTML element
        customColumns: [
        {
            field: 'package_name',
            title: Lang('Package'),
            // This will order your columns.
            order: 5,
            // Make column sortable
            sortable: false,
            // Set the width of the column
            width: 150,
            // If the column should always stay on top
            autoHide: false,
            textAlign: 'center',
            // The data you will actually return
            template: function (row) {
                return row.name;
            }
        }]
    });
</script>
```

### KTDatatable (backend)
Controller:

```php
public function fetch()
{
    $query = Package::select('*', 'name as package_name')->selectRaw('mediaboxes_included + mediaboxes_extra AS total_mediaboxes')->with(['provider' => function ($query) {
        $query->select('*','name as provider_name');
    }]);

    // Specify which columns to search in
    // If no columns are passed as parameter, all columns will be searched
    $searchInColumns = ['id','name','email'];
    return (new PackageDatatable($query,$searchInColumns))->render();

    return (new PackageDatatable($query))->render();
}
```

Datatable:

```php
<?php

namespace App\Datatables;

use Pveltrop\DCMS\Classes\Datatable;

class PackageDatatable extends Datatable
{
    /**
     * @param $field
     * @param $value
     */

    public function filter($field=[], $value=[])
    {
        switch ($field) {
            case 'total_mediaboxes':
                $this->query->whereRaw('(mediaboxes_included + mediaboxes_extra) >= '.$value);
                break;
            case 'price':
                $this->query->where($field, '<=', $value);
                break;
            case 'speed':
                $this->query->where($field, '>=', $value);
                break;
            default:
                $this->query->where($field, '=', $value);
        }
    }
}

```
