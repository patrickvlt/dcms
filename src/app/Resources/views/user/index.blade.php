@extends('dcms::layout.app')
@section('subheader')
<!--begin::Subheader-->
<div class="subheader py-2 py-lg-6 subheader-transparent" id="kt_subheader">
    <div class="container d-flex align-items-center justify-content-between flex-wrap flex-sm-nowrap">
        <!--begin::Info-->
        <div class="d-flex align-items-center flex-wrap mr-2">
        </div>
        <!--end::Info-->
        <!--begin::Toolbar-->
        <div class="d-flex align-items-center flex-wrap">
            <!--begin::Actions-->
            <a href="{{ route('dcms.portal.authorization.index') }}" class="btn btn-bg-white btn-icon-info btn-hover-primary btn-icon mr-3 my-2 my-lg-0" style="min-width: 180px">
                <span class="menu-text">Authorization overview</span>
            </a>
            <button data-kt-action="reload"
                class="btn btn-bg-white btn-icon-info btn-hover-primary btn-icon mr-3 my-2 my-lg-0"
                style="min-width: 120px;">
                <span class="menu-text">Refresh table</span>
            </button>
            <a href="{{ route('dcms.portal.user.create') }}" class="btn btn-primary btn-icon" style="min-width: 125px">
                <span class="menu-text">Create user</span>
            </a>
            <!--end::Actions-->
        </div>
        <!--end::Toolbar-->
    </div>
</div>
<!--end::Subheader-->
@endsection
@section('content')
<!--begin::Users-->
<!--begin::Row-->
<div class="row" data-kt-parent>
    <div class="col-12">
        <!--begin::Mixed Widget 10-->
        <div class="card card-custom card-stretch gutter-b">
            <!--begin::Body-->
            <div class="card-body d-flex flex-column" id="userParentDiv">
                <div class="flex-grow-1 pb-5">
                    <!--begin::Link-->
                    <label class="text-dark font-weight-bolder font-size-h4">Users</label>
                    <!--end::Link-->
                    <!--begin::Desc-->
                    <p class="text-dark-50 font-weight-normal font-size-lg mt-6"></p>
                    <!--end::Desc-->
                </div>
                <div class='mb-7'>
                    <div class='row align-items-center d-flex'>
                        <div class='col-lg-2 col-md-4'>
                            <label class='filterTitle'>{{ __('Search') }}</label>
                            <div class='input-icon'>
                                <input type='text' class='form-control' data-kt-action="search" />
                                <span>
                                    <i class='fas fa-search text-muted'></i>
                                </span>
                            </div>
                        </div>
                        <div class='col-lg-2 col-md-4 my-2 my-md-0'>
                            <label class='filterTitle'>{{ __('Verified') }}</label>
                            <select class='form-control' data-type="slimselect" data-kt-filter='verified'>
                                <option value="">{{ __('All') }}</option>
                                <option value="1">{{ __('Yes') }}</option>
                                <option value="0">{{ __('No') }}</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class='datatable datatable-bordered datatable-head-custom' 
                    data-users-table 
                    data-kt-parent="[data-kt-parent]"
                    data-kt-route={{ route('dcms.portal.user.fetch') }}
                    data-kt-edit-route={{ route('dcms.portal.user.edit','__id__') }}
                    data-kt-destroy-route={{ route('dcms.portal.user.destroy','__id__') }}
                    data-kt-destroy-multiple-route={{ route('dcms.portal.user.destroy.multiple') }} 
                    data-kt-page-size=10
                    data-kt-pagination=true 
                    data-kt-scrolling=false 
                    data-kt-include-actions=true
                    data-kt-include-selector=true 
                    data-kt-delete-rows-confirm-title="{{ __('Delete users') }}"
                    data-kt-delete-rows-confirm-message="{{ __('Are you sure you want to delete these users?') }}"
                    data-kt-delete-rows-complete-title="{{ __('Posts deleted') }}"
                    data-kt-delete-rows-complete-message="{{ __('The users have been deleted.') }}"
                    data-kt-delete-rows-failed-title="{{ __('Deleting failed') }}"
                    data-kt-delete-rows-failed-message="{{ __('The users couldn\'t be deleted.') }}"
                    data-kt-delete-single-confirm-title='{{ __('Delete user') }}'
                    data-kt-delete-single-confirm-message='{{ __('Are you sure you want to delete this user?') }}'
                    data-kt-delete-single-complete-title='{{ __('Deleted user') }}'
                    data-kt-delete-single-complete-message='{{ __('The user has been deleted.') }}'
                    data-kt-delete-single-failed-title='{{ __('Deleting failed') }}'
                    data-kt-delete-single-failed-message='{{ __('This user couldn\'t be deleted.') }}'>
                    <div data-kt-type="columns">
                        <div data-kt-title="{{ __('Name') }}" data-kt-order="1" data-kt-column="name" data-kt-width="100"></div>
                        <div data-kt-title="{{ __('E-mail') }}" data-kt-order="3" data-kt-column="email" data-kt-width="225"></div>
                        <div data-kt-title="{{ __('E-mail verified') }}" data-kt-order="4" data-kt-column="email_verified_at"
                            data-kt-type="boolean" data-kt-text-color='success' data-kt-width="100"></div>
                        <div data-kt-title="{{ __('Account verified') }}" data-kt-order="5" data-kt-column="verified"
                            data-kt-type="boolean" data-kt-text-color='success' data-kt-width="100"></div>
                        <div data-kt-title="{{ __('Updated at') }}" data-kt-order="6" data-kt-column="updated_at"></div>
                        <div data-kt-title="{{ __('Registered at') }}" data-kt-order="7" data-kt-column="created_at"></div>
                    </div>
                </div>
            </div>
            <!--end::Body-->
        </div>
        <!--end::Mixed Widget 10-->
    </div>
</div>
<!--end::Row-->
<!--end::Users-->
@endsection
@push('footer-scripts')
<script>
    DCMS.datatable({
        table: document.querySelector(".datatable"),
        columns: [
            {
                field: 'roles',
                order: 2,
                title: Lang('Roles'),
                textAlign: 'center',
                sortable: false,
                template: function (row) {
                    if (row.roles){
                        let roleStr = '';
                        for (const r in row.roles) {
                            roleStr = roleStr + `<span class="label label-lg label-primary label-pill label-inline mt-1">${Lang(row.roles[r].name)}</span>` + "\n";
                        }
                        return roleStr;
                    }
                }
            },
        ]
    });
</script>
@endpush