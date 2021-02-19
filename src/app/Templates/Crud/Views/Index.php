<?php

return '
<div class="col-md-4 my-2 my-md-0">
    <label class="filterTitle">{{ __("Search") }}</label>
    <div class="input-icon">
        <input type="text" class="form-control" data-kt-action="search" />
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
<div class="datatable datatable-bordered datatable-head-custom" data-kt-route="/user/fetch"
    data-kt-parent="#tableParent" data-kt-edit-route={{ route("user.edit","__id__") }}
    data-kt-destroy-route={{ route("user.destroy","__id__") }}
    data-kt-destroy-multiple-route={{ route("user.destroy.multiple") }} data-kt-page-size=10
    data-kt-pagination=true data-kt-scrolling=false data-kt-include-actions=true data-kt-include-selector=true
    data-kt-delete-rows-confirm-title="{{ __("Delete users") }}"
    data-kt-delete-rows-confirm-message="{{ __("Are you sure you want to delete these users?") }}"
    data-kt-delete-rows-complete-title="{{ __("Users deleted") }}"
    data-kt-delete-rows-complete-message="{{ __("The users have been deleted.") }}"
    data-kt-delete-rows-failed-title="{{ __("Deleting failed") }}"
    data-kt-delete-rows-failed-message="{{ __("The users couldn\"t be deleted.") }}"
    data-kt-delete-single-confirm-title="{{ __("Delete user") }}"
    data-kt-delete-single-confirm-message="{{ __("Are you sure you want to delete this user?") }}"
    data-kt-delete-single-complete-title="{{ __("Deleted user") }}"
    data-kt-delete-single-complete-message="{{ __("The user has been deleted.") }}"
    data-kt-delete-single-failed-title="{{ __("Deleting failed") }}"
    data-kt-delete-single-failed-message="{{ __("This user couldn\"t be deleted.") }}">
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
<script>
    DCMS.datatable({
        table: $(".datatable"),
    });
</script>
';