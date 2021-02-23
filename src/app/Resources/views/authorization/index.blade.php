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
            <a href="{{ route('dcms.portal.user.index') }}" class="btn btn-primary btn-icon" style="min-width: 150px">
                <span class="menu-text">Users overview</span>
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
    <!--begin::Roles-->
    @include('dcms::authorization.datatables.roles')
    <!--end::Roles-->
    <!--begin::Permissions-->
    @include('dcms::authorization.datatables.permissions')
    <!--end::Permissions-->
</div>
<!--end::Row-->
<!--end::Users-->
@endsection