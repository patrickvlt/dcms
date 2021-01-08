<?php

/**
 * Created by Patrick Veltrop
 * Date: 03-10-2020
 * Time: 17:55
 */

namespace Pveltrop\DCMS\Classes;

use HtmlGenerator\HtmlTag;
use Illuminate\Database\Eloquent\Collection;

class Form extends HtmlTag
{
    /**
     * Generate an HTML form based on passed in form properties from the main controller.
     * Uses columns which are defined in custom request in second parameter.
     * @param $model
     * @return Form|null
     */

    public static function create($request, $routePrefix, $formClass, $responses)
    {
        $formFields = (new $formClass())->fields();
        $formRoutes = (new $formClass())->routes();
        $model = null;
        if (FormMethod() == ('PUT')){
            $model = Model($routePrefix) ?? Model();
            if(is_string($model)){
                $class = FindClass($routePrefix)['class'];
                $model = (new $class())->find(request()->route()->parameters[$routePrefix]);
            }
        }

        $modelRequest = (new $request())->rules() ?? null;
        if (!isset($modelRequest)) {
            throw new \RuntimeException("No custom request defined and/or assigned to DCMS for: " . $routePrefix);
        }
        $columns = [];

        foreach ($formFields as $requestCol => $rules) {
            $column['name'] = $requestCol;
            $columns[] = $column;
        }

        // Start creating form
        $method = ($model) ? 'PUT' : 'POST';
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
            if (preg_match('/\.\*/',$column['name'])){
                continue;
            }
            $makeFormGroup = true;
            $makeLabel = true;
            $makeInputGroup = true;

            $makeInput = true;
            $makeSelect = false;
            $makeCheckbox = false;
            $makeRadio = false;
            $makeTextarea = false;

            $definedAttr = $formFields[$column['name']] ?? null;
            // Take different steps according to various data-types
            if (isset($definedAttr['select'])) {
                $makeInput = false;
                $makeSelect = true;
            } else if (isset($definedAttr['checkbox'])) {
                $makeInputGroup = false;
                $makeInput = false;
                $makeCheckbox = true;
            } else if (isset($definedAttr['radio'])){
                $makeInputGroup = false;
                $makeInput = false;
                $makeRadio = true;
            } else if (isset($definedAttr['textarea'])){
                $makeInputGroup = false;
                $makeInput = false;
                $makeTextarea = true;
            }
            if (isset($definedAttr['input']['data-type'])) {
                switch ($definedAttr['input']['data-type']) {
                    case 'filepond':
                        $definedAttr['input']['type'] = 'file';
                        $definedAttr['input']['data-filepond-prefix'] = $routePrefix;
                        $definedAttr['input']['data-filepond-column'] = $column['name'];
                        $minFiles = (isset($modelRequest[$column['name']])) ? GetRule($modelRequest[$column['name']], 'min') : 0;
                        $maxFiles = (isset($modelRequest[$column['name']])) ? GetRule($modelRequest[$column['name']], 'max') : 1;
                        $maxFileSize = (isset($modelRequest[$column['name'] . '.*'])) ? GetRule($modelRequest[$column['name'] . '.*'], 'max')."KB" : MaxSizeServer('kb')."KB";
                        $definedAttr['input']['data-filepond-min-files'] = $minFiles;
                        $definedAttr['input']['data-filepond-max-files'] = $maxFiles;
                        $definedAttr['input']['data-filepond-max-file-size'] = $maxFileSize;
                        $definedAttr['input']['class'] = null;
                        $makeInputGroup = false;
                    break;
                    case 'slimselect':
                        $makeInput = false;
                        $makeSelect = true;
                    break;
                }
            }

            // Create carousel before the input element
            if(isset($definedAttr['carousel']) && $model){
                $carouselArr = $model->{$column['name']};
                if (!is_array($carouselArr)){
                    $carouselArr = explode(',',$carouselArr);
                }
                if (count($carouselArr) > 0 && $carouselArr[0] !== ""){
                    $carousel = $form->addElement('div')->attr([
                        'data-type' => 'dcarousel',
                        'data-dcar-src' => $model->{$column['name']},
                        'data-dcar-prefix' => $routePrefix,
                        'data-dcar-column' => $column['name'],
                        'data-dcar-height' => $definedAttr['carousel']['height'] ?? '200px' 
                    ]);       
                }
            }

            // Form group
            if($makeFormGroup){
                $customAttr = $definedAttr['form-group'] ?? null;
                $formGroup = $form->addElement('div')->attr([
                    'class' => 'form-group',
                ])->attr($customAttr);
            }

            // Label
            if ($makeLabel){
                $labelText = $definedAttr['label']['text'] ?? null;
                if ($labelText) {
                    $customAttr = $definedAttr['label'] ?? null;
                    $label = $formGroup->addElement('label')->attr([
                        'for' => $column['name'],
                    ])->attr($customAttr);
                    $label->text(__(ucfirst($labelText)));
                }
            }

            // Input group
            if($makeInputGroup){
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
            }

            // Input field
            if ($makeInput) {
                $inputCustomAttr = $definedAttr['input'] ?? null;
                $inputType = $inputCustomAttr['type'] ?? 'text';
                $inputPlaceholder = $definedAttr['placeholder'] ?? null;
                $defaultInputAttr = [
                    'id' => $column['name'],
                    'class' => 'form-control',
                    'type' => $inputType,
                    'name' => $column['name'],
                    'placeholder' => __($inputPlaceholder),
                    'value' => $model->{$column['name']} ?? old($column['name'])
                ];
                $addToEl = ($makeInputGroup) ? $inputGroup : $formGroup;
                $addToEl->addElement('input')->attr($defaultInputAttr)->attr($inputCustomAttr);
            // Select element
            } else if ($makeSelect) {
                $selectCustomAttr = $definedAttr['select'];
                unset($selectCustomAttr['options']);
                $multiple = (in_array('multiple',array_keys($selectCustomAttr))) ? true : false;
                $selectElement = $formGroup->addElement('select')->attr([
                    'id' => $column['name'],
                    'class' => ($multiple) ? 'form-control ss-main-multiple' : 'form-control',
                    'name' => ($multiple) ? $column['name'].'[]' : $column['name'],
                    'data-slimselect-addable' => isset($selectCustomAttr['addable']) && $selectCustomAttr['addable'] == true ? 'true' : 'false',
                    'data-slimselect-placeholder' => $selectCustomAttr['placeholder'] ?? null,
                ])->attr($selectCustomAttr);
                // Options in select element
                if (isset($definedAttr['select']['options']['data'])){
                    $optionAttrs = $definedAttr['select']['options'];
                    $optionOptionalAttr = $optionAttrs;

                    $primaryKey = isset($optionAttrs['primaryKey']) ? $optionAttrs['primaryKey'] : null;
                    $showKey = isset($optionAttrs['showKey']) ? $optionAttrs['showKey'] : null;
                    $foreignKey = isset($optionAttrs['foreignKey']) ? $optionAttrs['foreignKey'] : null;

                    unset($optionOptionalAttr['data'],$optionOptionalAttr['primaryKey'],$optionOptionalAttr['foreignKey'],$optionOptionalAttr['showKey']);

                    if (!is_array($optionAttrs['data']) && !$optionAttrs['data'] instanceof Collection){
                        $optionAttrs['data'] = $model->{$optionAttrs['data']};
                    }

                    foreach ($optionAttrs['data'] as $key => $data){
                        $option = $selectElement->addElement('option')->attr([
                            'value' => $primaryKey ? $data->{$primaryKey} : $data,
                        ])->text(__($showKey ? $data->{$showKey} : $data));
                        $dataValue = $primaryKey ? $data->{$primaryKey} : $key;
                        if (FormMethod() == 'POST'){
                            $modelValue = $foreignKey ? $data->{$foreignKey} : $data;
                        } else {
                            $modelValue = $foreignKey ? $model->{$foreignKey} : $model->{$column['name']};
                        }
                        if (is_array($modelValue) || $modelValue instanceof Collection){
                            foreach($modelValue as $modelValueRow){
                                // Compare with property if this exists
                                $modelValueRow = $modelValueRow->{$primaryKey} ? $modelValueRow->{$primaryKey} : $modelValueRow;
                                if ($modelValueRow == $dataValue){
                                    $option->attr(['selected' => 'selected']);
                                }
                            }
                        } 
                        else if ((string)$modelValue == (string)$dataValue) {
                            $option->attr(['selected' => 'selected']);
                        }
                    }
                }
            } else if ($makeCheckbox || $makeRadio) {
                // Check if checkboxes have been defined
                $properties = ($makeCheckbox) ? $definedAttr['checkbox'] : $definedAttr['radio'];
                if (isset($properties)){
                    $customParentElAttr = [];
                    foreach ($properties as $propKey => $property){
                        if (!is_array($property)){
                            $customParentElAttr[$propKey] = $property;
                        }
                    }
                    foreach ($properties as $x => $property){
                        if (is_array($property)){
                            $addToEl = $formGroup->addElement('div')->attr($customParentElAttr);
                            $propertyText = $property['text'] ?? null;
                            $propertyValue = $property['value'] ?? null;
                            $inputCustomAttr = $property['input'] ?? null;
                            $labelCustomAttr = $property['label'] ?? null;

                            $name = (count($properties) > 1 && !$makeRadio) ? $column['name'].'[]' : $column['name'];
                            $checked = null;
                            if($model){
                                $checked = (($model->{$column['name']} && $model->{$column['name']} == $propertyValue) || old($column['name']) == $propertyValue) ? 'checked' : null;
                            }

                            if ($makeCheckbox){
                                $hiddenInput = $addToEl->addElement('input')->attr([
                                    'name' => $name,
                                    'type' => 'checkbox',
                                    'checked' => 'checked',
                                    'value' => 0,
                                    'style' => 'display:none !important'
                                ]);
                            }

                            $boxInput = $addToEl->addElement('input')->attr([
                                'name' => $name,
                                'type' => ($makeCheckbox) ? 'checkbox' : 'radio',
                                'checked' => $checked,
                                'value' => $propertyValue ?? '',
                                'id' => $column['name'].'Box'.$x,
                                'data-type' => 'iCheck',
                                'style' => 'display:none'
                            ])->attr($inputCustomAttr);

                            $boxLabel = $addToEl->addElement('label')->attr([
                                'class' => 'form-check-label',
                                'for' => $column['name'].'Box'.$x
                            ])->attr($labelCustomAttr)->text(__($propertyText));
                        }
                    }
                }
            } else if ($makeTextarea) { 
                $textareaCustomAttr = $definedAttr['textarea'] ?? null;
                $textareaType = $textareaCustomAttr['type'] ?? 'text';
                $textareaPlaceholder = $definedAttr['placeholder'] ?? null;
                $defaultInputAttr = [
                    'id' => $column['name'],
                    'class' => 'form-control',
                    'type' => $textareaType,
                    'name' => $column['name'],
                    'placeholder' => __($textareaPlaceholder),
                ];
                $addToEl = ($makeInputGroup) ? $inputGroup : $formGroup;
                $addToEl->addElement('textarea')->attr($defaultInputAttr)->attr($textareaCustomAttr)->text($model->{$column['name']} ?? old($column['name']));
            }

            // Small text
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

        $customAttr = $definedAttr['form-group'] ?? null;
        $formGroup = $form->addElement('div')->attr([
            'class' => 'form-group pt-3 mb-0',
        ])->attr($customAttr);

        // Save button: If creating a model
        if (!$model) {
            $saveRedirect = $responses['created']['url'] ?? route($routePrefix . '.index');
            $saveRoute = $formRoutes['store'] ?? route($routePrefix . '.store');
            $saveID = null;
            $saveText = $formFields['formButtons']['create']['text'] ?? __('Create');
            $saveBtnAttr = $formFields['formButtons']['create'] ?? null;
        }
        // Save button: If updating a model
        else {
            $saveRedirect = $responses['updated']['url'] ?? route($routePrefix . '.index');
            $saveRoute = $formRoutes['update'] ?? route($routePrefix . '.update', $model->id);
            $saveID = $model->id;
            $saveText = $formFields['formButtons']['update']['text'] ?? __('Update');
            $saveBtnAttr = $formFields['formButtons']['update'] ?? null;
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
        $formGroup->addElement($saveBtn);

        // Get custom attributes for delete button, dont use text as an attribute
        $deleteBtnAttr = $formFields['formButtons']['delete'] ?? null;
        $deleteBtnText = $formFields['formButtons']['delete']['text'] ?? null;
        $x = 0;
        if ($deleteBtnAttr) {
            foreach ($deleteBtnAttr as $key => $attr) {
                if ($key == 'text') {
                    unset($deleteBtnAttr[$x]);
                }
                $x++;
            }
        }
        
        // Delete button
        if ($model) {
            $formGroup->addElement('br');
            $deleteBtn = self::createElement('button');
            $deleteBtn->attr([
                'type' => 'button',
                'class' => 'btn btn-danger mt-2',
                'data-dcms-id' => $model->id,
                'data-dcms-action' => 'destroy',
                'data-dcms-destroy-redirect' => $responses['deleted']['url'] ?? route($routePrefix . '.index'),
                'data-dcms-destroy-route' => $formRoutes['destroy'] ?? route($routePrefix . '.destroy', '__id__'),
                'data-dcms-delete-confirm-title' => (isset($responses['confirmDelete']['title'])) ? ReplaceWithAttr($responses['confirmDelete']['title'],$model) : __('Delete object'),
                'data-dcms-delete-confirm-message' => (isset($responses['confirmDelete']['message'])) ?ReplaceWithAttr($responses['confirmDelete']['message'],$model) : __('Are you sure you want to delete this object?'),
                'data-dcms-delete-complete-title' => (isset($responses['deleted']['title'])) ?ReplaceWithAttr($responses['deleted']['title'],$model) : __('Deleted object'),
                'data-dcms-delete-complete-message' => (isset($responses['deleted']['message'])) ?ReplaceWithAttr($responses['deleted']['message'],$model) : __('This object has been succesfully deleted.'),
                'data-dcms-delete-failed-title' => (isset($responses['failedDelete']['title'])) ?ReplaceWithAttr($responses['failedDelete']['title'],$model) : __('Deleting failed'),
                'data-dcms-delete-failed-message' => (isset($responses['failedDelete']['message'])) ?ReplaceWithAttr($responses['failedDelete']['message'],$model) : __('Failed to delete this object. An unknown error has occurred.'),
            ])->attr($deleteBtnAttr);
            if ($deleteBtnText) {
                $deleteBtn->text(__($deleteBtnText));
            } else {
                $deleteBtn->text(__('Delete'));
            }
            $formGroup->addElement($deleteBtn);
        }
        return $form;
    }
}
