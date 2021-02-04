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
@push('extra-styles')
<link href="/css/dcms/wizard.css" rel="stylesheet" type="text/css" />
@endpush
@section('content')
<!--begin::Users-->
<!--begin::Row-->
<div class="card card-custom card-transparent">
    <div class="card-body p-0">
        <!--begin: Wizard-->
        <div class="wizard wizard-4" id="kt_wizard" data-wizard-state="first" data-wizard-clickable="true">
            <!--begin: Wizard Nav-->
            <div class="wizard-nav">
                <div class="wizard-steps">
                    <!--begin::Wizard Step 1 Nav-->
                    <div class="wizard-step" data-wizard-type="step" data-wizard-state="current">
                        <div class="wizard-wrapper">
                            <div class="wizard-number">1</div>
                            <div class="wizard-label">
                                <div class="wizard-title">Visual Information</div>
                                <div class="wizard-desc">Name, views & messages</div>
                            </div>
                        </div>
                    </div>
                    <!--end::Wizard Step 1 Nav-->
                    <!--begin::Wizard Step 2 Nav-->
                    <div class="wizard-step" data-wizard-type="step" data-wizard-state="pending">
                        <div class="wizard-wrapper">
                            <div class="wizard-number">2</div>
                            <div class="wizard-label">
                                <div class="wizard-title">Columns</div>
                                <div class="wizard-desc">The fields of this model</div>
                            </div>
                        </div>
                    </div>
                    <!--end::Wizard Step 2 Nav-->
                    <!--begin::Wizard Step 3 Nav-->
                    <div class="wizard-step" data-wizard-type="step" data-wizard-state="pending">
                        <div class="wizard-wrapper">
                            <div class="wizard-number">3</div>
                            <div class="wizard-label">
                                <div class="wizard-title">Extra</div>
                                <div class="wizard-desc">Datatable & imports</div>
                            </div>
                        </div>
                    </div>
                    <!--end::Wizard Step 3 Nav-->
                    <!--begin::Wizard Step 4 Nav-->
                    <div class="wizard-step" data-wizard-type="step" data-wizard-state="pending">
                        <div class="wizard-wrapper">
                            <div class="wizard-number">4</div>
                            <div class="wizard-label">
                                <div class="wizard-title">Summary</div>
                                <div class="wizard-desc"></div>
                            </div>
                        </div>
                    </div>
                    <!--end::Wizard Step 4 Nav-->
                </div>
            </div>
            <!--end: Wizard Nav-->
            <!--begin: Wizard Body-->
            <div class="card card-custom card-shadowless rounded-top-0">
                <div class="card-body p-0">
                    <div class="row justify-content-center py-8 px-8 py-lg-15 px-lg-10">
                        <div class="col-xl-12 col-xxl-7">
                            <!--begin: Wizard Form-->
                            <form class="form mt-0 mt-lg-10 fv-plugins-bootstrap fv-plugins-framework" id="kt_form" action="{{ route('dcms.portal.model.store') }}" method="POST" data-dcms-action="ajax" enctype="multipart/form-data">
                                <!--begin: Wizard Step 1-->
                                <div class="pb-5" data-wizard-step="1" data-wizard-type="step-content" data-wizard-state="current">
                                    <!--begin::Input-->
                                    <div class="form-group fv-plugins-icon-container col-12">
                                        <label>Name</label>
                                        <input type="text" class="form-control form-control-solid form-control-lg"
                                            name="model" placeholder="User">
                                        <span class="form-text text-muted">Enter the name of the model.</span>
                                        <div class="fv-plugins-message-container"></div>
                                    </div>
                                    <!--end::Input-->
                                    <div class="mb-10 font-weight-bold text-dark col-12">Responses</div>
                                    <div class="d-sm-flex" data-response-created>
                                        <!--begin::Input-->
                                        <div class="form-group fv-plugins-icon-container col-sm-6">
                                            <label>Message for new entry</label>
                                            <input type="text" class="form-control form-control-solid form-control-lg"
                                                name="message" placeholder="Created user">
                                            <span class="form-text text-muted">Which message to show when user creates a new entry?</span>
                                            <div class="fv-plugins-message-container"></div>
                                        </div>
                                        <!--end::Input-->
                                        <!--begin::Input-->
                                        <div class="form-group fv-plugins-icon-container col-sm-6">
                                            <label>Redirect</label>
                                            <input type="text" class="form-control form-control-solid form-control-lg"
                                                name="url" placeholder="/user/index">
                                            <span class="form-text text-muted">Enter a relative path to redirect the user after
                                                creating a new entry.</span>
                                            <div class="fv-plugins-message-container"></div>
                                        </div>
                                        <!--end::Input-->
                                    </div>
                                    <div class="d-sm-flex" data-response-updated>
                                        <!--begin::Input-->
                                        <div class="form-group fv-plugins-icon-container col-sm-6">
                                            <label>Message for updated entry</label>
                                            <input type="text" class="form-control form-control-solid form-control-lg"
                                                name="message" placeholder="Updated user">
                                            <span class="form-text text-muted">Which message to show when user has edited an entry?</span>
                                            <div class="fv-plugins-message-container"></div>
                                        </div>
                                        <!--end::Input-->
                                        <!--begin::Input-->
                                        <div class="form-group fv-plugins-icon-container col-sm-6">
                                            <label>Redirect</label>
                                            <input type="text" class="form-control form-control-solid form-control-lg"
                                                name="url" placeholder="/user/index">
                                            <span class="form-text text-muted">Enter a relative path to redirect the user after
                                                editing an entry.</span>
                                            <div class="fv-plugins-message-container"></div>
                                        </div>
                                        <!--end::Input-->
                                    </div>
                                    <div class="d-sm-flex" data-response-deleted>
                                        <!--begin::Input-->
                                        <div class="form-group fv-plugins-icon-container col-sm-6">
                                            <label>Message for deleted entry</label>
                                            <input type="text" class="form-control form-control-solid form-control-lg"
                                                name="message" placeholder="Deleted user">
                                            <span class="form-text text-muted">Which message to show when user has deleted an entry?</span>
                                            <div class="fv-plugins-message-container"></div>
                                        </div>
                                        <!--end::Input-->
                                        <!--begin::Input-->
                                        <div class="form-group fv-plugins-icon-container col-sm-6">
                                            <label>Redirect</label>
                                            <input type="text" class="form-control form-control-solid form-control-lg"
                                                name="url" placeholder="/user/index">
                                            <span class="form-text text-muted">Enter a path to redirect the user after
                                                deleting an entry.</span>
                                            <div class="fv-plugins-message-container"></div>
                                        </div>
                                        <!--end::Input-->
                                    </div>
                                    <div class="mb-10 font-weight-bold text-dark col-12">Views</div>
                                    <div class="d-sm-flex">
                                        <!--begin::Input-->
                                        <div class="form-group fv-plugins-icon-container col-sm-6">
                                            <label>Create</label>
                                            <input type="text" class="form-control form-control-solid form-control-lg"
                                                name="create" data-view placeholder="user.create">
                                            <span class="form-text text-muted">Which view to return when creating a new entry?</span>
                                            <div class="fv-plugins-message-container"></div>
                                        </div>
                                        <!--end::Input-->
                                        <!--begin::Input-->
                                        <div class="form-group fv-plugins-icon-container col-sm-6">
                                            <label>Index</label>
                                            <input type="text" class="form-control form-control-solid form-control-lg"
                                                name="index" data-view placeholder="user.index">
                                            <span class="form-text text-muted">Which view to return for the index route?</span>
                                            <div class="fv-plugins-message-container"></div>
                                        </div>
                                        <!--end::Input-->
                                    </div>
                                    <div class="d-sm-flex">
                                        <!--begin::Input-->
                                        <div class="form-group fv-plugins-icon-container col-sm-6">
                                            <label>Show</label>
                                            <input type="text" class="form-control form-control-solid form-control-lg"
                                                name="show" data-view placeholder="user.show">
                                            <span class="form-text text-muted">Which view to return when viewing an entry?</span>
                                            <div class="fv-plugins-message-container"></div>
                                        </div>
                                        <!--end::Input-->
                                        <!--begin::Input-->
                                        <div class="form-group fv-plugins-icon-container col-sm-6">
                                            <label>Edit</label>
                                            <input type="text" class="form-control form-control-solid form-control-lg"
                                                name="edit" data-view placeholder="user.edit">
                                            <span class="form-text text-muted">Which view to return when editing an entry?</span>
                                            <div class="fv-plugins-message-container"></div>
                                        </div>
                                        <!--end::Input-->
                                    </div>
                                </div>
                                <!--end: Wizard Step 1-->
                                <!--begin: Wizard Step 2-->
                                <div class="pb-5" data-wizard-step="2" data-wizard-type="step-content">
                                    <div data-column>
                                        <i class="fas fa-caret-down" data-column-control></i>
                                        <h4 class="mb-10 font-weight-bold text-dark d-inline ml-2" data-column-name></h4>
                                        <div class="mt-5" data-column-properties>
                                            <!--begin::Input-->
                                            <div class="form-group fv-plugins-icon-container">
                                                <label>Column</label>
                                                <input type="text" class="form-control form-control-solid form-control-lg"
                                                    name="name" placeholder="email">
                                                <span class="form-text text-muted">The name of the column, to insert in the database.</span>
                                                <div class="fv-plugins-message-container"></div>
                                            </div>
                                            <!--end::Input-->
                                            <!--begin::Input-->
                                            <div class="form-group fv-plugins-icon-container">
                                                <label>Title</label>
                                                <input type="text" class="form-control form-control-solid form-control-lg"
                                                    name="title" placeholder="E-mail address">
                                                <span class="form-text text-muted">The title of the column, to show above it's input in a form.</span>
                                                <div class="fv-plugins-message-container"></div>
                                            </div>
                                            <!--end::Input-->
                                            <!--begin::Input-->
                                            <div class="form-group fv-plugins-icon-container">
                                                <label>Data Type</label>
                                                <select name="dataType" data-type="slimselect" id="dataType" class="form-control form-control-solid form-control-lg">
                                                    <option value="">..</option>
                                                    <option value="bigIncrements">bigIncrements</option>
                                                    <option value="bigInteger">bigInteger</option>
                                                    <option value="binary">binary</option>
                                                    <option value="boolean">boolean</option>
                                                    <option value="char">char</option>
                                                    <option value="dateTimeTz">dateTimeTz</option>
                                                    <option value="dateTime">dateTime</option>
                                                    <option value="date">date</option>
                                                    <option value="decimal">decimal</option>
                                                    <option value="double">double</option>
                                                    <option value="enum">enum</option>
                                                    <option value="float">float</option>
                                                    <option value="foreignId">foreignId</option>
                                                    <option value="geometryCollection">geometryCollection</option>
                                                    <option value="geometry">geometry</option>
                                                    <option value="id">id</option>
                                                    <option value="increments">increments</option>
                                                    <option value="integer">integer</option>
                                                    <option value="ipAddress">ipAddress</option>
                                                    <option value="json">json</option>
                                                    <option value="jsonb">jsonb</option>
                                                    <option value="lineString">lineString</option>
                                                    <option value="longText">longText</option>
                                                    <option value="macAddress">macAddress</option>
                                                    <option value="mediumIncrements">mediumIncrements</option>
                                                    <option value="mediumInteger">mediumInteger</option>
                                                    <option value="mediumText">mediumText</option>
                                                    <option value="morphs">morphs</option>
                                                    <option value="multiLineString">multiLineString</option>
                                                    <option value="multiPoint">multiPoint</option>
                                                    <option value="multiPolygon">multiPolygon</option>
                                                    <option value="nullableMorphs">nullableMorphs</option>
                                                    <option value="nullableTimestamps">nullableTimestamps</option>
                                                    <option value="nullableUuidMorphs">nullableUuidMorphs</option>
                                                    <option value="point">point</option>
                                                    <option value="polygon">polygon</option>
                                                    <option value="rememberToken">rememberToken</option>
                                                    <option value="set">set</option>
                                                    <option value="smallIncrements">smallIncrements</option>
                                                    <option value="smallInteger">smallInteger</option>
                                                    <option value="softDeletesTz">softDeletesTz</option>
                                                    <option value="softDeletes">softDeletes</option>
                                                    <option value="string">string</option>
                                                    <option value="text">text</option>
                                                    <option value="timeTz">timeTz</option>
                                                    <option value="time">time</option>
                                                    <option value="timestampTz">timestampTz</option>
                                                    <option value="timestamp">timestamp</option>
                                                    <option value="timestampsTz">timestampsTz</option>
                                                    <option value="timestamps">timestamps</option>
                                                    <option value="tinyIncrements">tinyIncrements</option>
                                                    <option value="tinyInteger">tinyInteger</option>
                                                    <option value="unsignedBigInteger">unsignedBigInteger</option>
                                                    <option value="unsignedDecimal">unsignedDecimal</option>
                                                    <option value="unsignedInteger">unsignedInteger</option>
                                                    <option value="unsignedMediumInteger">unsignedMediumInteger</option>
                                                    <option value="unsignedSmallInteger">unsignedSmallInteger</option>
                                                    <option value="unsignedTinyInteger">unsignedTinyInteger</option>
                                                    <option value="uuidMorphs">uuidMorphs</option>
                                                    <option value="uuid">uuid</option>
                                                    <option value="yea">yea</option>
                                                </select>
                                                <span class="form-text text-muted">Select the data type for this column.</span>
                                                <div class="fv-plugins-message-container"></div>
                                            </div>
                                            <!--end::Input-->
                                            <!--begin::Input-->
                                            <div class="form-group fv-plugins-icon-container">
                                                <div>
                                                    <input name="nullable" type="checkbox" checked="checked" value="0" style="display:none !important">
                                                    <input name="nullable" type="checkbox" value="1" id="nullableBox0" data-type="iCheck" style="display:none">
                                                    <label class="form-check-label" for="nullableBox0">Is this column nullable?</label>
                                                </div>
                                                <div class="fv-plugins-message-container"></div>
                                            </div>
                                            <!--end::Input-->
                                            <!--begin::Input-->
                                            <div class="form-group fv-plugins-icon-container">
                                                <div>
                                                    <input name="required" type="checkbox" checked="checked" value="0" style="display:none !important">
                                                    <input name="required" type="checkbox" value="1" id="requiredBox0" data-type="iCheck" style="display:none">
                                                    <label class="form-check-label" for="requiredBox0">Is this column required?</label>
                                                </div>
                                                <div class="fv-plugins-message-container"></div>
                                            </div>
                                            <!--begin::Input-->
                                            <div class="form-group fv-plugins-icon-container">
                                                <div>
                                                    <input name="foreign" type="checkbox" checked="checked" value="0" style="display:none !important">
                                                    <input name="foreign" type="checkbox" value="1" id="foreignBox0" data-type="iCheck" data-foreign-checkbox style="display:none">
                                                    <label class="form-check-label" for="foreignBox0">Is this a foreign key?</label>
                                                </div>
                                                <div class="fv-plugins-message-container"></div>
                                            </div>
                                            <!--end::Input-->
                                            <!--begin::Relation-->
                                            <div data-relation-div style="display:none">
                                                <div class="form-group fv-plugins-icon-container">
                                                    <label>Value</label>
                                                    <input type="text" class="form-control form-control-solid form-control-lg"
                                                        name="value" placeholder="id">
                                                    <span class="form-text text-muted">A dropdown element will be generated in the form with all related objects. Which field should be used as the value in the option element?</span>
                                                    <div class="fv-plugins-message-container"></div>
                                                </div>
                                                <div class="form-group fv-plugins-icon-container">
                                                    <label>Text</label>
                                                    <input type="text" class="form-control form-control-solid form-control-lg"
                                                        name="text" placeholder="name">
                                                    <span class="form-text text-muted">Which field should be shown to the user in the option element?</span>
                                                    <div class="fv-plugins-message-container"></div>
                                                </div>
                                            </div>
                                            <!--end::Relation-->
                                            <div data-input-div style="display:none">
                                                <!--begin::Input-->
                                                <div class="form-group fv-plugins-icon-container">
                                                    <label>Input Type</label>
                                                    <select name="inputType" data-type="slimselect" id="inputType" class="form-control form-control-solid form-control-lg">
                                                        <option value="text">Text</option>
                                                        <option value="checkbox">Checkbox</option>
                                                        <option value="dropdown">Dropdown</option>
                                                        <option value="textarea">Textarea</option>
                                                        <option value="color">Color</option>
                                                        <option value="date">Date</option>
                                                        <option value="datetime-local">Datetime-local</option>
                                                        <option value="email">Email</option>
                                                        <option value="file">File</option>
                                                        <option value="number">Number</option>
                                                        <option value="password">Password</option>
                                                        <option value="time">Time</option>
                                                    </select>
                                                    <span class="form-text text-muted">Select the input type for this column to use in the form.</span>
                                                    <div class="fv-plugins-message-container"></div>
                                                </div>
                                                <!--end::Input-->
                                                <!--begin::Input-->
                                                <div class="form-group fv-plugins-icon-container" data-input-datatype-div>
                                                    <label>DCMS Plugin</label>
                                                    <select name="inputDataType" data-type="slimselect" id="inputDataType" class="form-control form-control-solid form-control-lg">
                                                        <option value="">..</option>
                                                        <option value="datetimepicker">Datetime picker</option>
                                                        <option value="filepond">FilePond</option>
                                                        <option value="icheck">iCheck</option>
                                                        <option value="slimselect">Slimselect</option>
                                                        <option value="tinymce">TinyMCE</option>
                                                    </select>
                                                    <span class="form-text text-muted">DCMS ships with various Front-End plugins to improve input elements.</span>
                                                    <div class="fv-plugins-message-container"></div>
                                                </div>
                                                <!--end::Input-->
                                            </div>
                                            <!--begin::Input-->
                                            <div class="form-group fv-plugins-icon-container" data-input-datatype-div>
                                                <label>Add another column?</label>
                                                <br>
                                                <button class="btn btn-primary btn-icon" style="min-width: 90px" data-add-column type="button">
                                                    <span class="menu-text">Yes</span>
                                                </button>
                                                <div class="fv-plugins-message-container"></div>
                                            </div>
                                            <!--end::Input-->
                                            <!--begin::Input-->
                                            <div class="form-group fv-plugins-icon-container" data-input-datatype-div>
                                                <label>Delete this column?</label>
                                                <br>
                                                <button class="btn btn-danger btn-icon" style="min-width: 90px" data-delete-column type="button">
                                                    <span class="menu-text">Yes</span>
                                                </button>
                                                <div class="fv-plugins-message-container"></div>
                                            </div>
                                            <!--end::Input-->
                                        </div>
                                    </div>
                                </div>
                                <!--end: Wizard Step 2-->
                                <!--begin: Wizard Step 3-->
                                <div class="pb-5" data-wizard-step="3" data-wizard-type="step-content">
                                    <h4 class="mb-10 font-weight-bold text-dark">Datatable</h4>
                                    <div class="mb-10 font-weight-bold text-dark">Select which columns you want to show in the main datatable for this model.</div>
                                    <!--begin::KT columns-->
                                    <div data-kt-columns class="mb-5 pt-5">
                                        <div data-kt-column data-kt-column-name="ktColumn">
                                            <div class="row">
                                                <div class="col-12">
                                                    <!--begin::Enable column-->
                                                    <div class="form-group fv-plugins-icon-container">
                                                        <div>
                                                            <input name="enable" type="checkbox" checked="checked" value="0" style="display:none !important">
                                                            <input name="enable" type="checkbox" value="1" id="ktColumn_enableKtBox0" data-type="iCheck" data-kt-checkbox style="display:none">
                                                            <label class="form-check-label" for="ktColumn_enableKtBox0">ktColumn</label>
                                                        </div>
                                                    </div>
                                                    <!--end::Enable column-->
                                                    <!--begin::Title-->
                                                    <div class="form-group fv-plugins-icon-container" data-kt-title-div style="display:none">
                                                        <label>Title</label>
                                                        <input type="text" class="form-control form-control-solid form-control-lg"
                                                            name="title" placeholder="ktColumn">
                                                        <span class="form-text text-muted">Enter the title of the table header for this column.</span>
                                                        <div class="fv-plugins-message-container"></div>
                                                    </div>
                                                    <!--end::Title-->
                                                    <!--optional::Key-->
                                                    <!--begin::Type-->
                                                    <div class="form-group fv-plugins-icon-container" data-kt-type-div style="display:none">
                                                        <label class="form-check-label">Column type</label>
                                                        <select name="type" data-type="slimselect" data-kt-column-type class="form-control form-control-solid form-control-lg mt-2">
                                                            <option value="">..</option>
                                                            <option value="text">Text</option>
                                                            <option value="boolean">Boolean</option>
                                                            <option value="card">Card</option>
                                                            <option value="image">Image</option>
                                                            <option value="icon">Icon</option>
                                                            <option value="price">Price</option>
                                                        </select>
                                                        <span class="form-text text-muted">Choose how you wish to show the value of this column in the datatable.</span>
                                                    </div>
                                                    <div class="fv-plugins-message-container"></div>
                                                    <!--end::Type-->
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!--end::KT columns-->
                                    <hr>
                                    <!--begin::jExcel imports-->
                                    <h4 class="mb-10 font-weight-bold text-dark mt-5 pt-5">Imports</h4>
                                    <!--begin::enable jExcel-->
                                    <div class="form-group fv-plugins-icon-container pb-5">
                                        <div>
                                            <input name="enableImports" type="checkbox" checked="checked" value="0" style="display:none !important">
                                            <input name="enableImports" type="checkbox" value="1" id="jExcel_enableImportsBox0" data-type="iCheck" style="display:none">
                                            <label class="form-check-label" for="jExcel_enableImportsBox0">Do you want to enable jExcel imports?</label>
                                        </div>
                                        <div class="fv-plugins-message-container"></div>
                                    </div>
                                    <!--end::enable jExcel-->
                                    <div data-jexcel-columns class="mb-5 pt-5" style="display:none">
                                        <div data-jexcel-column data-jexcel-column-name="jExcel">
                                            <!--begin::Enable column-->
                                            <div class="form-group fv-plugins-icon-container">
                                                <div>
                                                    <input name="enable" type="checkbox" checked="checked" value="0" style="display:none !important">
                                                    <input name="enable" type="checkbox" value="1" id="jExcel_enableImportBox0" data-type="iCheck" data-jexcel-checkbox style="display:none">
                                                    <label class="form-check-label" for="jExcel_enableImportBox0">jExcel</label>
                                                </div>
                                            </div>
                                            <!--end::Enable column-->
                                            <!--begin::Title-->
                                            <div class="form-group fv-plugins-icon-container" data-jexcel-title-div style="display:none">
                                                <label>Title</label>
                                                <input type="text" class="form-control form-control-solid form-control-lg"
                                                    name="title" placeholder="jExcel">                                                
                                                    <span class="form-text text-muted">Enter the title of the table header for this column.</span>
                                                <div class="fv-plugins-message-container"></div>
                                            </div>
                                            <!--end::Title-->
                                            <!--optional::Key-->
                                            <!--begin::Title-->
                                            <div class="form-group fv-plugins-icon-container" data-jexcel-type-div style="display:none">
                                                <label class="form-check-label">Column type</label>
                                                <select name="type" data-type="slimselect" data-jexcel-column-type class="form-control form-control-solid form-control-lg mt-2">
                                                    <option value="">..</option>
                                                    <option value="text">Text</option>
                                                    <option value="numeric">Numeric</option>
                                                    <option value="hidden">Hidden</option>
                                                    <option value="dropdown">Dropdown</option>
                                                    <option value="autocomplete">Autocomplete</option>
                                                    <option value="checkbox">Checkbox</option>
                                                    <option value="radio">Radio</option>
                                                    <option value="calendar">Calendar</option>
                                                    <option value="image">Image</option>
                                                    <option value="color">Color</option>
                                                </select>
                                                <span class="form-text text-muted">What kind of data type belongs to the column?</span>
                                            </div>
                                            <div class="fv-plugins-message-container"></div>
                                            <!--end::Title-->
                                        </div>
                                    </div>
                                    <!--end::jExcel imports-->
                                </div>
                                <!--end: Wizard Step 3-->
                                <!--begin: Wizard Step 4-->
                                <div class="pb-5" data-wizard-step="4" data-wizard-type="step-content">
                                    <!--begin::Section-->
                                    <h4 class="mb-10 font-weight-bold text-dark">Review your Details and Submit</h4>
                                    <h6 class="font-weight-bolder mb-3">Current Address:</h6>
                                    <div class="text-dark-50 line-height-lg">
                                        <div>Address Line 1</div>
                                        <div>Address Line 2</div>
                                        <div>Melbourne 3000, VIC, Australia</div>
                                    </div>
                                    <div class="separator separator-dashed my-5"></div>
                                    <!--end::Section-->
                                    <!--begin::Section-->
                                    <h6 class="font-weight-bolder mb-3">Delivery Details:</h6>
                                    <div class="text-dark-50 line-height-lg">
                                        <div>Package: Complete Workstation (Monitor, Computer, Keyboard &amp; Mouse)
                                        </div>
                                        <div>Weight: 25kg</div>
                                        <div>Dimensions: 110cm (w) x 90cm (h) x 150cm (L)</div>
                                    </div>
                                    <div class="separator separator-dashed my-5"></div>
                                    <!--end::Section-->
                                    <!--begin::Section-->
                                    <h6 class="font-weight-bolder mb-3">Delivery Service Type:</h6>
                                    <div class="text-dark-50 line-height-lg">
                                        <div>Overnight Delivery with Regular Packaging</div>
                                        <div>Preferred Morning (8:00AM - 11:00AM) Delivery</div>
                                    </div>
                                    <div class="separator separator-dashed my-5"></div>
                                    <!--end::Section-->
                                    <!--begin::Section-->
                                    <h6 class="font-weight-bolder mb-3">Delivery Address:</h6>
                                    <div class="text-dark-50 line-height-lg">
                                        <div>Address Line 1</div>
                                        <div>Address Line 2</div>
                                        <div>Preston 3072, VIC, Australia</div>
                                    </div>
                                    <!--end::Section-->
                                </div>
                                <!--end: Wizard Step 4-->
                                <!--begin: Wizard Actions-->
                                <div class="d-flex justify-content-between border-top mt-5 pt-10">
                                    <div class="mr-2">
                                        <button type="button"
                                            class="btn btn-light-primary font-weight-bolder text-uppercase px-9 py-4"
                                            data-wizard-type="action-prev">Previous</button>
                                    </div>
                                    <div>
                                        <button type="button"
                                            class="btn btn-success font-weight-bolder text-uppercase px-9 py-4"
                                            data-wizard-type="action-submit">Submit</button>
                                        <button type="button"
                                            class="btn btn-primary font-weight-bolder text-uppercase px-9 py-4"
                                            data-wizard-type="action-next">Next</button>
                                    </div>
                                </div>
                                <!--end: Wizard Actions-->
                                <div></div>
                                <div></div>
                                <div></div>
                            </form>
                            <!--end: Wizard Form-->
                        </div>
                    </div>
                </div>
            </div>
            <!--end: Wizard Bpdy-->
        </div>
        <!--end: Wizard-->
    </div>
</div>
<!--end::Row-->
<!--end::Users-->
@endsection
@push('footer-scripts')
<script type="text/javascript" src="{{ asset('/js/dcms/portal/wizard.js') }}"></script>
@endpush