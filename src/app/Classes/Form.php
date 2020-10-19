<?php

/**
 * Created by Patrick Veltrop
 * Date: 03-10-2020
 * Time: 17:55
 */

namespace Pveltrop\DCMS\Classes;

use HtmlGenerator\HtmlTag;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class Form extends HtmlTag
{
    /**
     * Generate form for mass assignment, quick setup.
     * Uses columns which are defined in custom request in second parameter.
     * @param $model
     * @return Form|null
     */

    public static function create($model, $request, $routePrefix, $DCMS)
    {
        // $modelColumns = Schema::getColumnListing((new $model())->getTable());
        $modelRequest = (new $request())->rules() ?? null;
        $modelFiles = method_exists((new $request()),'uploadRules') ?? null;
        if (!isset($modelRequest)) {
            throw new \RuntimeException("No custom request defined and/or assigned to DCMS for: " . $routePrefix);
        }
        $table = (new $model())->getTable();
        $builder = DB::getSchemaBuilder();
        $columns = [];
        foreach ($modelRequest as $requestCol => $rules) {
            $column['name'] = $requestCol;
            $column['type'] = $builder->getColumnType($table, $requestCol);
            $column['rules'] = $rules;
            $columns[] = $column;
        }

        // Start creating form
        $method = (Model()) ? 'PUT' : 'POST';
        $form = self::createElement('form')->attr([
            'action' => FormRoute(),
            'method' => 'POST',
            'data-dcms-action' => 'ajax',
            'enctype' => 'multipart/form-data'
        ]);
        $form->addElement('input')->attr([
            'type' => 'hidden',
            'name' => '_method',
            'value' => $method
        ]);
        $form->addElement('input')->attr([
            'type' => 'hidden',
            'name' => '_token',
            'value' => csrf_token()
        ]);

        foreach ($columns as $column) {
            $noDivs = false;
            $makeInput = true;
            $makeSelect = false;
            $definedAttr = $DCMS['formProperties'][$column['name']] ?? null;

            // Take different steps according to various data-types
            if (isset($definedAttr['select'])) {
                $makeInput = false;
                $makeSelect = true;
            }
            if (isset($definedAttr['input']['data-type'])) {
                switch ($definedAttr['input']['data-type']) {
                    case 'filepond':
                        $definedAttr['input']['type'] = 'file';
                        $definedAttr['input']['data-filepond-prefix'] = $routePrefix;
                        $definedAttr['input']['data-filepond-column'] = $column['name'];
                        $minFiles = (isset($modelRequest[$column['name']])) ? GetRule($modelRequest[$column['name']], 'min') : 1;
                        $maxFiles = (isset($modelRequest[$column['name']])) ? GetRule($modelRequest[$column['name']], 'max') : 1;
                        $maxFileSize = (isset($modelRequest[$column['name'] . '*'])) ? GetRule($modelFiles[$column['name'] . '*'], 'max') : MaxSizeServer('kb');
                        $definedAttr['input']['data-filepond-min-files'] = $minFiles;
                        $definedAttr['input']['data-filepond-max-files'] = $maxFiles;
                        $definedAttr['input']['data-filepond-max-file-size'] = $maxFileSize;
                        $noDivs = true;
                        goto MakeLabel;
                    case 'slimselect':
                        $makeInput = false;
                        $makeSelect = true;
                }
            }
            // Form group
            $customAttr = $definedAttr['form-group'] ?? null;
            $formGroup = $form->addElement('div')->attr([
                'class' => 'form-group',
            ])->attr($customAttr);

            // Label
            MakeLabel:
            $customAttr = $definedAttr['label'] ?? null;
            $label = $formGroup->addElement('label')->attr([
                'for' => $column['name'],
            ])->attr($customAttr);
            $labelText = $definedAttr['label']['text'] ?? null;
            if ($labelText) {
                $label->text(__(ucfirst($labelText)));
            } else {
                $label->text(__(ucfirst($column['name'])));
            }
            if ($noDivs == true) {
                goto MakeInput;
            }

            // Input group
            $inputGrCustomAttr = $definedAttr['input-group'] ?? null;
            $inputGroup = $formGroup->addElement('div')->attr([
                'class' => 'input-group',
            ])->attr($inputGrCustomAttr);

            // Input group prepend
            $inputPrepend = $definedAttr['input-group-prepend'] ?? null;
            if ($inputPrepend) {
                $inputPrepend = $inputGroup->addElement('div')->attr([
                    'class' => 'input-group-prepend',
                ])->addElement('span')->attr([
                    'class' => 'input-group-text',
                ]);
                $inputText = $definedAttr['input-group-prepend']['text'] ?? null;
                $inputIcon = $definedAttr['input-group-prepend']['icon'] ?? null;
                if ($inputText && !$inputIcon) {
                    $inputPrepend->text(__($inputText));
                } else if ($inputIcon && !$inputText) {
                    $inputPrepend->addElement('i')->attr($inputIcon);
                }
            }
            if ($makeInput) {
                // Input field
                MakeInput:
                $inputCustomAttr = $definedAttr['input'] ?? null;
                $inputType = $inputCustomAttr['type'] ?? 'text';
                $inputPlaceholder = $definedAttr['placeholder'] ?? null;
                $defaultInputAttr = [
                    'id' => $column['name'],
                    'class' => 'form-control',
                    'type' => $inputType,
                    'name' => $column['name'],
                    'placeholder' => __($inputPlaceholder),
                    'value' => Model()->{$column['name']} ?? old($column['name'])
                ];

                // Don't create any divs if this is a filepond input
                if (isset($definedAttr['input']['data-type']) && $definedAttr['input']['data-type'] == 'filepond') {
                    $defaultInputAttr['class'] = null;
                    $formGroup = $formGroup->addElement('input')->attr($defaultInputAttr)->attr($inputCustomAttr);
                } else {
                    // Default input to insert in the input group
                    $inputGroup->addElement('input')->attr($defaultInputAttr)->attr($inputCustomAttr);
                }
            } else if ($makeSelect) {
                // Select element
                $selectCustomAttr = $definedAttr['select'];
                unset($selectCustomAttr['options']);
                $multiple = (in_array('multiple',array_keys($selectCustomAttr))) ? true : false;
                $selectElement = $formGroup->addElement('select')->attr([
                    'id' => $column['name'],
                    'class' => ($multiple) ? 'form-control ss-main-multiple' : 'form-control',
                    'name' => ($multiple) ? $column['name'].'[]' : $column['name'],
                ])->attr($selectCustomAttr);
                if (isset($definedAttr['select']['options']['data'])){
                    $optionAttrs = $definedAttr['select']['options'];
                    $optionOptionalAttr = $optionAttrs;
                    unset($optionOptionalAttr['data'],$optionOptionalAttr['primaryKey'],$optionOptionalAttr['foreignKey'],$optionOptionalAttr['showKey']);
                    foreach ($optionAttrs['data'] as $key => $data){
                        $option = $selectElement->addElement('option')->attr([
                            'value' => $data['primaryKey'] ?? null,
                        ])->text(__($data->{$optionAttrs['showKey']}));
                        try {
                            $selected = false;
                            $dataValue = $data->{$optionAttrs['primaryKey']};
                            $modelValue = Model()->{$optionAttrs['foreignKey']};
                            if (is_array($modelValue)){
                                foreach($modelValue as $modelValueRow){
                                    if ($modelValueRow == $dataValue){
                                        $option->attr(['selected' => 'selected']);
                                    }
                                }
                            } else if ($modelValue == $dataValue) {
                                $option->attr(['selected' => 'selected']);
                            }
                        } catch (\Exception $e) {
                            dd($e);
                        }
                    }
                }
            }

            $customSmall = $definedAttr['small'] ?? null;
            if ($customSmall) {
                $customText = $customSmall['text'] ?? null;
                $inputSmall = $formGroup->addElement('small')->attr([
                    'id' => $column['name'] . 'Help',
                    'class' => 'form-text text-muted',
                ])->attr($customSmall);
                if ($customText) {
                    $inputSmall->text(__($customText));
                }
            }
        }
        // If creating a model
        if (!Model()) {
            $saveRedirect = $DCMS['created']['url'] ?? route($routePrefix . '.index');
            $saveRoute = route($routePrefix . '.store');
            $saveID = null;
            $saveText = $DCMS['formProperties']['formButtons']['create']['text'] ?? __('Create');
            $saveBtnAttr = $DCMS['formProperties']['formButtons']['create'] ?? null;
        }
        // If updating a model
        else {
            $saveRedirect = $DCMS['updated']['url'] ?? route($routePrefix . '.index');
            $saveRoute = route($routePrefix . '.update', Model()->id);
            $saveID = Model()->id;
            $saveText = $DCMS['formProperties']['formButtons']['update']['text'] ?? __('Update');
            $saveBtnAttr = $DCMS['formProperties']['formButtons']['update'] ?? null;
        }
        // Get custom attributes for save button, dont use text as an attribute
        $x = 0;
        if ($saveBtnAttr) {
            foreach ($saveBtnAttr as $key => $attr) {
                if ($key == 'text') {
                    unset($saveBtnAttr[$x]);
                }
                $x++;
            }
        }
        // Save button
        $saveBtn = self::createElement('button');
        $saveBtn->attr([
            'type' => 'submit',
            'class' => 'btn btn-primary',
            'data-dcms-id' => $saveID,
            'data-dcms-save-redirect' => $saveRedirect,
            'data-dcms-save-route' => $saveRoute,
        ])->attr($saveBtnAttr);
        $saveBtn->text(__($saveText));
        $form->addElement($saveBtn);
        // Get custom attributes for delete button, dont use text as an attribute
        $deleteBtnAttr = $DCMS['formProperties']['formButtons']['delete'] ?? null;
        $x = 0;
        if ($deleteBtnAttr) {
            foreach ($deleteBtnAttr as $key => $attr) {
                if ($key == 'text') {
                    unset($deleteBtnAttr[$x]);
                }
                $x++;
            }
        }
        // Generate delete button
        if (Model()) {
            $form->addElement('br');
            $deleteBtn = self::createElement('button');
            $deleteBtn->attr([
                'type' => 'button',
                'class' => 'btn btn-danger mt-2',
                'data-dcms-id' => Model()->id,
                'data-dcms-action' => 'destroy',
                'data-dcms-destroy-redirect' => route($routePrefix . '.index'),
                'data-dcms-destroy-route' => route($routePrefix . '.destroy', '__id__'),
                'data-dcms-delete-confirm-title' => $DCMS['confirmDeleteTitle'] ?? __('Delete object'),
                'data-dcms-delete-confirm-message' => $DCMS['confirmDeleteMessage'] ?? __('Are you sure you want to delete this object?'),
                'data-dcms-delete-complete-title' => $DCMS['deletedTitle'] ?? __('Deleted object'),
                'data-dcms-delete-complete-message' => $DCMS['deletedMessage'] ?? __('This object has been succesfully deleted.'),
                'data-dcms-delete-failed-title' => $DCMS['failedDeleteTitle'] ?? __('Deleting failed'),
                'data-dcms-delete-failed-message' => $DCMS['failedDeleteMessage'] ?? __('Failed to delete this object. An unknown error has occurred.'),
            ])->attr($deleteBtnAttr);
            $deleteBtnText = $customAttr['text'] ?? null;
            if ($deleteBtnText) {
                $deleteBtn->text(__($deleteBtnText));
            } else {
                $deleteBtn->text(__('Delete'));
            }
            $form->addElement($deleteBtn);
        }
        return $form;
    }
}
