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
            <a href="{{ route('dcms.portal.authorization.index') }}" class="btn btn-primary btn-icon"
                style="min-width: 150px">
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
<!--begin::Role-->
<!--begin::Row-->
<div class="row">
    <div class="col-12">
        <!--begin::Mixed Widget 10-->
        <div class="card card-custom card-stretch gutter-b">
            <!--begin::Body-->
            <div class="card-body d-flex flex-column w-50">
                <form action="http://pvportal.test/dcms/permission" method="POST" data-dcms-action="ajax"
                    enctype="multipart/form-data">
                    <input type="hidden" name="_method" value="POST">
                    <input type="hidden" name="_token">
                    <div class="form-group" id="selectRoute">
                        <label for="route" text="Route">{{ __('Route') }}</label>
                        <select class="form-control" name="route" data-slimselect-addable="false"
                            data-type="slimselect" data-slimselect-auto-close="true">
                            @foreach($namedRoutes as $route)
                            <option value="{{ $route->name }}" @if(Model() && Model()->route == $route->name) selected @endif>{{ $route->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group" id="typeRoute" style="display:none">
                        <label for="route" text="Route">{{ __('Route') }}</label>
                        <input class="form-control" type="text" name="route" @if(Model() && Model()->route) value="{{ Model()->route }}" @endif>
                    </div>
                    <div class="form-group fv-plugins-icon-container">
                        <div>
                            <input name="typeRoute" type="checkbox" checked="checked" value="0" style="display:none !important">
                            <input name="typeRoute" type="checkbox" value="1" id="routeBox0" data-type="iCheck" style="display:none">
                            <label class="form-check-label" for="routeBox0">Do you want to manually enter the route name?</label>
                        </div>
                        <div class="fv-plugins-message-container"></div>
                    </div>
                    <div class="form-group">
                        <label for="route" text="Name">{{ __('Name') }}</label>
                        <input class="form-control" type="text" name="name" @if(Model() && Model()->name) value="{{ Model()->name }}" @endif>
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
                    </div>
                    @if(Model())
                    <button type="button" class="btn btn-danger" data-dcms-id="{{ Model()->id }}" data-dcms-action="destroy"
                        data-dcms-destroy-route={{ route('dcms.portal.permission.destroy','__id__') }}
                        data-dcms-destroy-redirect={{ route('dcms.portal.permission.index') }}
                        data-dcms-delete-confirm-title="{{ __('Delete permission?') }}"
                        data-dcms-delete-confirm-message="{{ __('Are you sure you want to delete this permission?') }}"
                        data-dcms-delete-complete-title="{{ __('Deleted permission') }}"
                        data-dcms-delete-complete-message="{{ __('The permission has been deleted.') }}"
                        data-dcms-delete-failed-title="{{ __('Failed to delete') }}"
                        data-dcms-delete-failed-message="{{ __('This permission can\'t be deleted.') }}">{{ __('Delete') }}</button>
                    @endif
                </form>
            </div>
            <!--end::Body-->
        </div>
        <!--end::Mixed Widget 10-->
    </div>
</div>
<script>
    document.addEventListener('click',function(e){
        // Get actually element which should be clicked
        let clickedElement = (e.target.type == 'checkbox') ? e.target : null;
    
        if (clickedElement){
            if (clickedElement.checked){
                document.querySelector('#selectRoute').style.display = 'none';
                document.querySelector('#selectRoute').querySelector('select').value = null;
                document.querySelector('#typeRoute').style.display = 'inherit';
            }  else {
                document.querySelector('#typeRoute').querySelector('input').value = null;
                document.querySelector('#typeRoute').style.display = 'none';
                document.querySelector('#selectRoute').style.display = 'inherit';
            }
        }
    });
</script>
<!--end::Row-->
<!--end::Role-->
@endsection