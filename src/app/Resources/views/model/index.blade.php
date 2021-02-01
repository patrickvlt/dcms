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
            <button data-kt-action="reload"
                class="btn btn-bg-white btn-icon-info btn-hover-primary btn-icon mr-3 my-2 my-lg-0"
                style="min-width: 120px;">
                <span class="menu-text">Refresh table</span>
            </button>
            <a href="{{ route('dcms.portal.model.create') }}" class="btn btn-primary btn-icon" style="min-width: 120px">
                <span class="menu-text">Create model</span>
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
<div class="row">
    <div class="col-12">
        <!--begin::Mixed Widget 10-->
        <div class="card card-custom card-stretch gutter-b">
            <!--begin::Body-->
            <div class="card-body d-flex flex-column" id="modelParentDiv">
                <div class="flex-grow-1 pb-5">
                    <!--begin::Link-->
                    <a href="{{ route('dcms.portal.model.index') }}"
                        class="text-dark font-weight-bolder text-hover-primary font-size-h4">Models</a>
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
                <div class='datatable datatable-bordered datatable-head-custom' data-kt-parent="#kt_body"
                    data-kt-route={{ route('dcms.portal.model.fetch') }}
                    data-kt-edit-route={{ route('dcms.portal.model.edit','__id__') }} data-kt-page-size=10
                    data-kt-pagination=true data-kt-scrolling=false data-kt-include-actions=false
                    data-kt-include-selector=false data-kt-delete-rows-confirm-title="{{ __('Delete models') }}"
                    data-kt-delete-rows-confirm-message="{{ __('Are you sure you want to delete these models?') }}"
                    data-kt-delete-rows-complete-title="{{ __('Posts deleted') }}"
                    data-kt-delete-rows-complete-message="{{ __('The models have been deleted.') }}"
                    data-kt-delete-rows-failed-title="{{ __('Deleting failed') }}"
                    data-kt-delete-rows-failed-message="{{ __('The models couldn\'t be deleted.') }}"
                    data-kt-delete-single-confirm-title='{{ __('Delete model') }}'
                    data-kt-delete-single-confirm-message='{{ __('Are you sure you want to delete this model?') }}'
                    data-kt-delete-single-complete-title='{{ __('Deleted model') }}'
                    data-kt-delete-single-complete-message='{{ __('The model has been deleted.') }}'
                    data-kt-delete-single-failed-title='{{ __('Deleting failed') }}'
                    data-kt-delete-single-failed-message='{{ __('This model couldn\'t be deleted.') }}'>
                    <div data-kt-type="columns">
                        <div data-kt-title="{{ __('Class') }}" data-kt-column="class"></div>
                        <div data-kt-title="{{ __('Table') }}" data-kt-column="table"></div>
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
        table: $(".datatable")
    });
</script>
@endpush