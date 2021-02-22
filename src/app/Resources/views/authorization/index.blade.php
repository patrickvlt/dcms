@extends('dcms::layout.app')
@section('subheader')
<!--begin::Subheader-->
<div class="subheader py-2 py-lg-6 subheader-transparent" id="kt_subheader">
    <div class="container d-flex align-items-center justify-content-between flex-wrap flex-sm-nowrap">
        <!--begin::Info-->
        <div class="d-flex align-items-center flex-wrap mr-2">
        </div>
        <!--end::Info-->
    </div>
</div>
<!--end::Subheader-->
@endsection
@section('content')
<!--begin::Users-->
<!--begin::Row-->
<div class="row">
    <!--begin::Roles-->
    <div class="col-lg-6 col-12" id="rolesDiv">
        <!--begin::Mixed Widget 10-->
        <div class="card card-custom card-stretch gutter-b">
            <!--begin::Body-->
            <div class="card-body d-flex flex-column" id="roleParentDiv">
                <div class="pb-5">
                    <!--begin::Link-->
                    <a href="{{ route('dcms.portal.role.index') }}"
                        class="text-dark font-weight-bolder text-hover-primary font-size-h4">Roles</a>
                    <!--end::Link-->
                    <!--begin::Desc-->
                    <p class="text-dark-50 font-weight-normal font-size-lg mt-6"></p>
                    <!--end::Desc-->
                </div>
                <div class='mb-7'>
                    <div class='row align-items-center d-flex'>
                        <div class='col-lg-2 col-md-4'>
                            <!--begin::Actions-->
                            <div class="d-lg-flex">
                                <a href="{{ route('dcms.portal.role.create') }}"
                                    class="btn btn-primary btn-icon mr-3 my-2 my-lg-0"
                                    style="min-width: 120px;">
                                    <span class="menu-text">Create</span>
                                </a>
                                <button data-kt-action="reload"
                                    class="btn btn-secondary btn-icon mr-3 my-2 my-lg-0"
                                    style="min-width: 120px;">
                                    <span class="menu-text">Refresh table</span>
                                </button>
                            </div>
                            <!--end::Actions-->
                        </div>
                    </div>
                </div>
                <div class='mb-7'>
                    <div class='row align-items-center d-flex'>
                        <div class='col-md-6'>
                            <label class='filterTitle'>{{ __('Search') }}</label>
                            <div class='input-icon'>
                                <input type='text' class='form-control' data-kt-action="search" />
                                <span>
                                    <i class='fas fa-search text-muted'></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class='datatable datatable-bordered datatable-head-custom' data-roles-table data-kt-parent="#rolesDiv"
                    data-kt-route={{ route('dcms.portal.role.fetch') }}
                    data-kt-edit-route={{ route('dcms.portal.role.edit','__id__') }}
                    data-kt-destroy-route={{ route('dcms.portal.role.destroy','__id__') }}
                    data-kt-destroy-multiple-route={{ route('dcms.portal.role.destroy.multiple') }} data-kt-page-size=10
                    data-kt-pagination=true data-kt-scrolling=false data-kt-include-actions=true
                    data-kt-include-selector=true data-kt-delete-rows-confirm-title="{{ __('Delete roles') }}"
                    data-kt-delete-rows-confirm-message="{{ __('Are you sure you want to delete these roles?') }}"
                    data-kt-delete-rows-complete-title="{{ __('Roles deleted') }}"
                    data-kt-delete-rows-complete-message="{{ __('The roles have been deleted.') }}"
                    data-kt-delete-rows-failed-title="{{ __('Deleting failed') }}"
                    data-kt-delete-rows-failed-message="{{ __('The roles couldn\'t be deleted.') }}"
                    data-kt-delete-single-confirm-title='{{ __('Delete role') }}'
                    data-kt-delete-single-confirm-message='{{ __('Are you sure you want to delete this role?') }}'
                    data-kt-delete-single-complete-title='{{ __('Deleted role') }}'
                    data-kt-delete-single-complete-message='{{ __('The role has been deleted.') }}'
                    data-kt-delete-single-failed-title='{{ __('Deleting failed') }}'
                    data-kt-delete-single-failed-message='{{ __('This role couldn\'t be deleted.') }}'>
                    <div data-kt-type="columns">
                        <div data-kt-title="{{ __('Name') }}" data-kt-column="name"></div>
                    </div>
                </div>
            </div>
            @push('footer-scripts')
            <script>
                DCMS.datatable({table: document.querySelector("[data-roles-table]")});
            </script>
            @endpush
            <!--end::Body-->
        </div>
        <!--end::Mixed Widget 10-->
    </div>
    <!--end::Roles-->
    <!--begin::Permissions-->
    <div class="col-lg-6 col-12" id="permissionsDiv">
        <!--begin::Mixed Widget 10-->
        <div class="card card-custom card-stretch gutter-b">
            <!--begin::Body-->
            <div class="card-body d-flex flex-column">
                <div class="pb-5">
                    <!--begin::Link-->
                    <a href="{{ route('dcms.portal.permission.index') }}"
                        class="text-dark font-weight-bolder text-hover-primary font-size-h4">Permissions</a>
                    <!--end::Link-->
                    <!--begin::Desc-->
                    <p class="text-dark-50 font-weight-normal font-size-lg mt-6"></p>
                    <!--end::Desc-->
                </div>
                <div class='mb-7'>
                    <div class='row align-items-center d-flex'>
                        <div class='col-lg-2 col-md-4'>
                            <!--begin::Actions-->
                            <div class="d-lg-flex">
                                <a href="{{ route('dcms.portal.permission.create') }}"
                                    class="btn btn-primary btn-icon mr-3 my-2 my-lg-0"
                                    style="min-width: 120px;">
                                    <span class="menu-text">Create</span>
                                </a>
                                <button data-kt-action="reload"
                                    class="btn btn-secondary btn-icon mr-3 my-2 my-lg-0"
                                    style="min-width: 120px;">
                                    <span class="menu-text">Refresh table</span>
                                </button>
                            </div>
                            <!--end::Actions-->
                        </div>
                    </div>
                </div>
                <div class='mb-7'>
                    <div class='row align-items-center d-flex'>
                        <div class='col-md-6'>
                            <label class='filterTitle'>{{ __('Search') }}</label>
                            <div class='input-icon'>
                                <input type='text' class='form-control' data-kt-action="search" />
                                <span>
                                    <i class='fas fa-search text-muted'></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class='datatable datatable-bordered datatable-head-custom' 
                    data-permissions-table 
                    data-kt-parent="#permissionsDiv"
                    data-kt-route={{ route('dcms.portal.permission.fetch') }}
                    data-kt-edit-route={{ route('dcms.portal.permission.edit','__id__') }}
                    data-kt-destroy-route={{ route('dcms.portal.permission.destroy','__id__') }}
                    data-kt-destroy-multiple-route={{ route('dcms.portal.permission.destroy.multiple') }} 
                    data-kt-page-size=10
                    data-kt-pagination=true 
                    data-kt-scrolling=false 
                    data-kt-include-actions=true
                    data-kt-include-selector=true 
                    data-kt-delete-rows-confirm-title="{{ __('Delete permissions') }}"
                    data-kt-delete-rows-confirm-message="{{ __('Are you sure you want to delete these permissions?') }}"
                    data-kt-delete-rows-complete-title="{{ __('Permissions deleted') }}"
                    data-kt-delete-rows-complete-message="{{ __('The permissions have been deleted.') }}"
                    data-kt-delete-rows-failed-title="{{ __('Deleting failed') }}"
                    data-kt-delete-rows-failed-message="{{ __('The permissions couldn\'t be deleted.') }}"
                    data-kt-delete-single-confirm-title='{{ __('Delete permission') }}'
                    data-kt-delete-single-confirm-message='{{ __('Are you sure you want to delete this permission?') }}'
                    data-kt-delete-single-complete-title='{{ __('Deleted permission') }}'
                    data-kt-delete-single-complete-message='{{ __('The permission has been deleted.') }}'
                    data-kt-delete-single-failed-title='{{ __('Deleting failed') }}'
                    data-kt-delete-single-failed-message='{{ __('This permission couldn\'t be deleted.') }}'>
                    <div data-kt-type="columns">
                        <div data-kt-title="{{ __('Name') }}" data-kt-column="name"></div>
                        <div data-kt-title="{{ __('Route') }}" data-kt-column="route"></div>
                    </div>
                </div>
                @push('footer-scripts')
                <script>
                    DCMS.datatable({table: document.querySelector("[data-permissions-table]")});
                </script>
                @endpush
            </div>
            <!--end::Body-->
        </div>
        <!--end::Mixed Widget 10-->
    </div>
    <!--end::Permissions-->
</div>
<!--end::Row-->
<!--end::Users-->
@endsection