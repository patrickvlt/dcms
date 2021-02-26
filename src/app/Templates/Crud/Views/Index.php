<?php

return '
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="{{ asset("/css/dcms/dcms.css") }}">
    <title>{{ env("APP_NAME") }}</title>
</head>
<body>
<div class="col-md-4 my-2 my-md-0">
    <label class="filterTitle">{{ __("Search") }}</label>
    <div class="input-icon">
        <input type="text" class="form-control" data-kt-action="search"/>
        <span>
            <i class="flaticon2-search-1 text-muted"></i>
        </span>
    </div>
</div>
<div class="col-md-4 my-2 my-md-0">
    <label class="filterTitle">{{ __("Verified") }}</label>
    <select class="form-control" data-type="slimselect" data-kt-filter="verified">
        <option value="">{{ __("All") }}</option>
        <option value="1">{{ __("Yes") }}</option>
        <option value="0">{{ __("No") }}</option>
    </select>
</div>
<div data-kt-type="controls">
    <button class="btn btn-light font-weight-bold" type="button"
            data-kt-action="reload">{{ __("Refresh") }}</button>
    <button class="btn btn-light font-weight-bold" type="button"
            data-kt-action="check-all">{{ __("Select all rows") }}</button>
    <button class="btn btn-light font-weight-bold" type="button"
            data-kt-action="uncheck-all">{{ __("Deselect all rows") }}</button>
    <button class="btn btn-light font-weight-bold" type="button"
            data-kt-action="remove-rows">{{ __("Delete selected rows") }}</button>
</div>
<div class="datatable datatable-bordered datatable-head-custom"
     data-kt-parent="#tableParent"
     data-kt-route="{{ route("configurationtype.fetch") }}"
     data-kt-edit-route="{{ route("configurationtype.edit","__id__") }}"
     data-kt-destroy-route="{{ route("configurationtype.destroy","__id__") }}"
     data-kt-destroy-multiple-route="{{ route("configurationtype.destroy.multiple") }}"
     data-kt-page-size="10"
     data-kt-pagination="true"
     data-kt-scrolling="false"
     data-kt-include-actions="true"
     data-kt-include-selector="true"
     data-kt-delete-rows-confirm-title="{{ __("Delete configurationtypes") }}"
     data-kt-delete-rows-confirm-message="{{ __("Are you sure you want to delete these configurationtypes?") }}"
     data-kt-delete-rows-complete-title="{{ __("Configurations deleted") }}"
     data-kt-delete-rows-complete-message="{{ __("The configurationtypes have been deleted.") }}"
     data-kt-delete-rows-failed-title="{{ __("Deleting failed") }}"
     data-kt-delete-rows-failed-message="{{ __("The configurationtypes couldn\"t be deleted.") }}"
     data-kt-delete-single-confirm-title="{{ __("Delete configurationtype") }}"
     data-kt-delete-single-confirm-message="{{ __("Are you sure you want to delete this configurationtype?") }}"
     data-kt-delete-single-complete-title="{{ __("Deleted configurationtype") }}"
     data-kt-delete-single-complete-message="{{ __("The configurationtype has been deleted.") }}"
     data-kt-delete-single-failed-title="{{ __("Deleting failed") }}"
     data-kt-delete-single-failed-message="{{ __("This configurationtype couldn\"t be deleted.") }}">
    <div data-kt-type="columns">
        <div data-kt-title="{{ __("Name") }}" data-kt-column="name" data-kt-width="100"></div>
        <div data-kt-title="{{ __("E-mail") }}" data-kt-column="email" data-kt-width="225"></div>
        <div data-kt-title="{{ __("E-mail verified") }}" data-kt-column="email_verified_at"
             data-kt-type="boolean" data-kt-text-color="success" data-kt-width="100"></div>
        <div data-kt-title="{{ __("Account verified") }}" data-kt-column="verified" data-kt-type="boolean"
             data-kt-text-color="success" data-kt-width="100"></div>
        <div data-kt-title="{{ __("Updated at") }}" data-kt-column="updated_at"></div>
        <div data-kt-title="{{ __("Registered at") }}" data-kt-column="created_at"></div>
    </div>
</div>
</body>
</html>

<script src="https://code.jquery.com/jquery-3.5.1.min.js" integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>
<script src="{{ asset("/js/dcms/dcms.js") }}"></script>
<script src="{{ asset("/js/dcms/assets/metronic/ktdatatable.js") }}"></script>

<script>
    var dcmsLanguage = "{{ App::getLocale() }}";
    var dcmsMaxSizeServer = {!! MaxSizeServer("kb") !!};
    $(document).ready(function () {
        DCMS.datatable({
            table: document.querySelector(".datatable")
        });
    });
</script>

';
