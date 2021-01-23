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
            <a href="{{ route('dcms.portal.model.index') }}" class="btn btn-primary btn-icon" style="min-width: 150px">
                <span class="menu-text">Return to overview</span>
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
                    <label class="text-dark font-weight-bolder font-size-h4">Create model</label>
                    <!--end::Link-->
                    <!--begin::Desc-->
                    <p class="text-dark-50 font-weight-normal font-size-lg mt-6"></p>
                    <!--end::Desc-->
                </div>
                <div class='mb-7'>
                    <div class='row align-items-center d-flex'>
                        <div class="col-6">
                            <form action="http://pvportal.test/dcms/user" method="POST" data-dcms-action="ajax" enctype="multipart/form-data">
                            <input type="hidden" name="_method" value="POST">
                            <input type="hidden" name="_token">
                            <div class="form-group">
                                <label for="name" text="Name">Name</label>
                                <div class="input-group">
                                    <input id="name" class="form-control" type="text" name="name">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="email" text="E-mail">E-mail</label>
                                <div class="input-group">
                                    <input id="email" class="form-control" type="text" name="email">
                                </div>
                            </div>
                            <div class="form-group">
                                <div>
                                    <input name="verified" type="checkbox" checked="checked" value="0" style="display:none !important"><input name="verified" type="checkbox" value="1" id="verifiedBox0" data-type="iCheck" style="display:none">
                                    <label class="form-check-label" for="verifiedBox0">Verified</label>
                                </div>
                                <small id="verifiedHelp" class="form-text text-muted" text="Click here to verify this user.">Click here to verify this user.</small>
                            </div>
                            <div class="form-group pt-3 mb-0"><button type="submit" class="btn btn-primary" data-dcms-save-redirect="http://pvportal.test/dcms/user" data-dcms-save-route="http://pvportal.test/user">Create</button></div>
                        </form>
                        </div>
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

@endpush
