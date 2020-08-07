# DCMS

DCMS is a package made to boost productivity, and speed up the usual workflow when developing CRUD applications.

# DCMS Datatables

This is a simple JS function which easily initalises KTDatatable, working with data attributes. Purchase a Metronic license if you wish to use these tables.

Include these plugins:
```
<script src="assets/plugins/global/plugins.bundle.js?v=7.0.5"></script>
<script src="assets/plugins/custom/prismjs/prismjs.bundle.js?v=7.0.5"></script>
<script src="assets/js/scripts.bundle.js?v=7.0.5"></script>
```

Example HTML code:

```
@extends('layout.app')
@section('content')
<!--begin::Card-->
<div class="card card-custom gutter-b">
    <div class="card-header flex-wrap py-3">
        <div class="card-title">
            <h3 class="card-label">{{ $district->name ?? __('Wijk') }}</h3>
        </div>
    </div>
    <div class="card-body">
        <form id="modelForm" action="{{ FormRoute() }}" method="POST" data-submit="ajax">
            @method(FormMethod())
            @csrf
            <div class="form-group">
                <label for="name">{{ __('Naam') }}</label>
                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name"
                    aria-describedby="name" value="{{ Model()->name ?? old('name') }}">
            </div>
            @error('name')
            <div class="invalid-feedback">{{ $errors->first('name') ?? '' }}</div>
            @enderror
            <div class="form-group">
                <label for="activity_id">{{ __('Status') }}</label>
                <div class="input-group d-flex">
                    <div class="input-group-prepend"><span class="input-group-text"><i class="las la-tasks"></i></span>
                    </div>
                    <select class="form-control" data-type="slimselect" data-add-method="post"
                        data-add-action="/activity" id="activity_id" name="activity_id" aria-describedby="activity_id">
                        <option>{{ __('Geen') }}</option>
                        @foreach (\App\Activity::all() as $key => $relatedModel)
                        <option value="{{ $relatedModel->id }}" @if((Model()==true && Model()->activity_id == $relatedModel->id) || old('activity_id') ==
                            $relatedModel->id) selected @endif>{{ $relatedModel->status }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            @error('activity_id')
            <div class="invalid-feedback">{{ $errors->first('activity_id') ?? '' }}</div>
            @enderror
            <div class="form-group">
                <label for="activities_start">{{ __('Startdatum') }}</label>
                <div class="input-group">
                    <div class="input-group-prepend"><span class="input-group-text"><i
                                class="la la-calendar-o"></i></span>
                    </div>
                    <input class="form-control @error('activities_start') is-invalid @enderror" data-type="datepicker"
                        name="activities_start" autocomplete="off"
                        value="{{ Model()->activities_start ?? old('activities_start') }}">
                </div>
                <small id="activities_startHelp"
                    class="form-text text-muted">{{ __('Wanneer beginnen de werkzaamheden?') }}</small>
            </div>
            @error('activities_start')
            <div class="invalid-feedback">{{ $errors->first('activities_start') ?? '' }}</div>
            @enderror
            <div class="form-group">
                <label for="activities_end">{{ __('Einddatum') }}</label>
                <div class="input-group">
                    <div class="input-group-prepend"><span class="input-group-text"><i
                                class="la la-calendar-o"></i></span>
                    </div>
                    <input class="form-control @error('activities_end') is-invalid @enderror" data-type="datepicker"
                        name="activities_end" autocomplete="off"
                        value="{{ Model()->activities_end ?? old('activities_end') }}">
                </div>
                <small id="activities_endHelp"
                    class="form-text text-muted">{{ __('Wanneer zijn de werkzaamheden afgerond?') }}</small>
            </div>
            @error('activities_end')
            <div class="invalid-feedback">{{ $errors->first('activities_end') ?? '' }}</div>
            @enderror
            <div class="form-group">
                <label for="started">{{ __('Zijn de werkzaamheden al gestart?') }}</label>
                <label class="checkbox checkbox-primary" style="display:none;">
                    <input type="checkbox" id="started" name="started" aria-describedby="started" value=0 checked>
                    <span></span>
                </label>
                <label class="checkbox checkbox-primary d-flex">
                    <input type="checkbox" id="started" name="started" aria-describedby="started" value=1
                        @if((isset(Model()->started) && Model()->started == true) ||
                    old('started') == 1) checked @endif>
                    <span class="mr-2"></span>
                    {{ __('Ja') }}
                </label>
                <label for="started" class="form-check-label"></label>
            </div>
            @error('started')
            <div class="invalid-feedback">{{ $errors->first('started') ?? '' }}</div>
            @enderror
            <div class="form-group">
                <label for="finished">{{ __('Zijn de werkzaamheden afgerond?') }}</label>
                <label class="checkbox checkbox-primary" style="display:none;">
                    <input type="checkbox" id="finished" name="finished" aria-describedby="finished" value=0 checked>
                    <span></span>
                </label>
                <label class="checkbox checkbox-primary d-flex">
                    <input type="checkbox" id="finished" name="finished" aria-describedby="finished" value=1
                        @if((isset(Model()->finished) && Model()->finished == true) ||
                    old('finished') == 1) checked @endif>
                    <span class="mr-2"></span>
                    {{ __('Ja') }}
                </label>
                <label for="finished" class="form-check-label"></label>
            </div>
            @error('finished')
            <div class="invalid-feedback">{{ $errors->first('finished') ?? '' }}</div>
            @enderror
            <div class="form-group">
                <label for="contractor_id">{{ __('Aannemer') }}</label>
                <div class="input-group d-flex">
                    <div class="input-group-prepend"><span class="input-group-text"><i
                                class="fas fa-user-hard-hat"></i></span>
                    </div>
                    <select class="form-control @error('contractor_id') is-invalid @enderror" data-type="slimselect"
                        id="contractor_id" name="contractor_id" aria-describedby="contractor_id">
                        <option>{{ __('Geen') }}</option>
                        @foreach (\App\Contractor::all() as $key => $relatedModel)
                        <option value="{{ $relatedModel->id }}" @if((Model()==true && Model()->
                            contractor_id == $relatedModel->id) || old('contractor_id') ==
                            $relatedModel->id) selected @endif>{{ $relatedModel->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            @error('contractor_id')
            <div class="invalid-feedback">{{ $errors->first('contractor_id') ?? '' }}</div>
            @enderror
            <div class="form-group">
                <label for="price_construction">{{ __('Eenmalige aanlegkosten') }}</label>
                <div class="input-group">
                    <div class="input-group-prepend"><span class="input-group-text"><i
                                class="fas fa-euro-sign"></i></span>
                    </div>
                    <input type="number" class="form-control @error('price_construction') is-invalid @enderror"
                        id="price_construction" name="price_construction" aria-describedby="price_construction"
                        placeholder="{{ __('') }}"
                        value="{{ Model()->price_construction ?? old('price_construction') }}" step="0.01">
                </div>
                <small id="price_constructionHelp"
                    class="form-text text-muted">{{ __('Wat zijn de kosten voor het aanleggen van de glasvezel?') }}</small>
            </div>
            @error('price_construction')
            <div class="invalid-feedback">{{ $errors->first('price_construction') ?? '' }}</div>
            @enderror

            @include('field_extra')
            <br>
            <div class="form-group">
                <button type="submit" class="btn btn-success">{{ __('Opslaan') }}</button>
            </div>
            @if(Model())
            <button type="button" class="btn btn-danger"
                data-id="{{ Model()->id }}" 
                data-action="destroy"
                data-destroy-route={{ route('district.destroy','__id__') }}
                data-destroy-redirect={{ route('district.index') }}
                data-delete-confirm-title="Verwijder wijk"
                data-delete-confirm-message="Weet u zeker dat u deze wijk wil verwijderen?"
                data-delete-complete-title="Wijk verwijderd"
                data-delete-complete-message="De wijk is verwijderd."
                data-delete-failed-title="Verwijderen mislukt"
                data-delete-failed-message="De wijk kan niet verwijderd worden."
                >{{ __('Verwijder') }}</button>
            @endif
        </form>
    </div>
</div>
<!--end::Card-->
@endsection
```
