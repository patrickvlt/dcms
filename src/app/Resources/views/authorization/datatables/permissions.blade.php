<div class="col-lg-6 col-12" id="permissionsDiv">
    <!--begin::Mixed Widget 10-->
    <div class="card card-custom card-stretch gutter-b">
        <!--begin::Body-->
        <div class="card-body d-flex flex-column">
            <div class="pb-5">
                <!--begin::Link-->
                <label class="text-dark font-weight-bolder font-size-h4">Permissions</label>
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
                                style="min-width: 85px;">
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
                    <div data-kt-title="{{ __('Name') }}" data-kt-column="name" data-kt-width="125"></div>
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