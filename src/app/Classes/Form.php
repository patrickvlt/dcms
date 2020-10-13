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

    public static function create($model,$request,$routePrefix,$DCMS)
    {
        // $modelColumns = Schema::getColumnListing((new $model())->getTable());
        $modelRequest = (new $request())->rules();
        $table = (new $model())->getTable();
        $builder = DB::getSchemaBuilder();
        $columns = [];
        foreach ($modelRequest as $requestCol => $rules){
            $column['name'] = $requestCol;
            $column['type'] = $builder->getColumnType($table, $requestCol);
            $column['rules'] = $rules;
            $columns[] = $column;
        }
        $method = (Model()) ? 'PUT' : 'POST';
        $form = self::createElement('form')->attr([
            'action' => FormRoute(),
            'method' => 'POST',
            'data-dcms-action' => 'ajax'
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

        foreach($columns as $column){
            $definedAttr = $properties[$column['name']] ?? null;
            // Form group
            $customAttr = $definedAttr['form-group'] ?? null;
            $formGroup = $form->addElement('div')->attr([
                'class' => 'form-group',
            ])->attr($customAttr);

            // Label
            $customAttr = $definedAttr['label'] ?? null;
            $label = $formGroup->addElement('label')->attr([
                'for' => $column['name'],
            ])->attr($customAttr);
            $labelText = $definedAttr['label']['text'] ?? null;
            if ($labelText){
                $label->text(__(ucfirst($labelText)));
            } else {
                $label->text(__(ucfirst($column['name'])));
            }

            // Input group
            $customAttr = $definedAttr['input-group'] ?? null;
            $inputGroup = $formGroup->addElement('div')->attr([
                'class' => 'input-group',
            ])->attr($customAttr);

            // Input group prepend
            $inputPrepend = $definedAttr['input-group-prepend'] ?? null;
            if ($inputPrepend){
                $inputPrepend = $inputGroup->addElement('div')->attr([
                    'class' => 'input-group-prepend',
                ])->addElement('span')->attr([
                    'class' => 'input-group-text',
                ]);
                $inputText = $definedAttr['input-group-prepend']['text'] ?? null;
                $inputIcon = $definedAttr['input-group-prepend']['icon'] ?? null;
                if ($inputText && !$inputIcon){
                    $inputPrepend->text(__($inputText));
                } else if ($inputIcon && !$inputText){
                    $inputPrepend->addElement('i')->attr($inputIcon);
                }
            }

            // Input field
            $customAttr = $definedAttr['input-group-prepend'] ?? null;
            $inputType = $definedAttr['type'] ?? 'text';
            $inputPlaceholder = $definedAttr['placeholder'] ?? null;
            $inputGroup->addElement('input')->attr([
                'id' => $column['name'],
                'class' => 'form-control',
                'type' => $inputType,
                'name' => $column['name'],
                'placeholder' => __($inputPlaceholder),
                'value' => Model()->version ?? old('version')
            ])->attr($customAttr);

            $customSmall = $definedAttr['small'] ?? null;
            if ($customSmall){
                $customText = $customSmall['text'] ?? null;
                $inputSmall = $formGroup->addElement('small')->attr([
                    'id' => $column['name'].'Help',
                    'class' => 'form-text text-muted',
                ])->attr($customSmall);
                if ($customText){
                    $inputSmall->text(__($customText));
                }
            }
            
        }
        // If creating a model
        if (!Model()){
            $saveRedirect = $DCMS['created']['url'] ?? route($routePrefix.'.index');
            $saveRoute = route($routePrefix.'.store');
            $saveID = null;
            $saveText = $definedAttr['formButtons']['create']['text'] ?? __('Create');
        }
        // If updating a model
        else {
            $saveRedirect = $DCMS['updated']['url'] ?? route($routePrefix.'.index');
            $saveRoute = route($routePrefix.'.update',Model()->id);
            $saveID = Model()->id;
            $saveText = $definedAttr['formButtons']['update']['text'] ?? __('Update');
        }
        // Save button
        $saveBtn = self::createElement('button');
        $saveBtn->attr([
            'type' => 'submit',
            'class' => 'btn btn-primary',
            'data-dcms-id' => $saveID,
            'data-dcms-save-redirect' => $saveRedirect,
            'data-dcms-save-route' => $saveRoute,
        ]);
        $saveBtn->text(__($saveText));
        $form->addElement($saveBtn);

        // Generate delete button
        if (Model()){
            $form->addElement('br');
            $deleteBtn = self::createElement('button');
            $deleteBtn->attr([
                'type' => 'button',
                'class' => 'btn btn-danger mt-2',
                'data-dcms-id' => Model()->id,
                'data-dcms-action' => 'destroy',
                'data-dcms-destroy-redirect' => route($routePrefix.'.index'),
                'data-dcms-destroy-route' => route($routePrefix.'.destroy','__id__'),
                'data-dcms-delete-confirm-title' => $DCMS['confirmDeleteTitle'] ?? __('Delete object'),
                'data-dcms-delete-confirm-message' => $DCMS['confirmDeleteMessage'] ?? __('Are you sure you want to delete this object?'),
                'data-dcms-delete-complete-title' => $DCMS['deletedTitle'] ?? __('Deleted object'),
                'data-dcms-delete-complete-message' => $DCMS['deletedMessage'] ?? __('This object has been succesfully deleted.'),
                'data-dcms-delete-failed-title' => $DCMS['failedDeleteTitle'] ?? __('Deleting failed'),
                'data-dcms-delete-failed-message' => $DCMS['failedDeleteMessage'] ?? __('Failed to delete this object. An unknown error has occurred.'),
            ]);
            $deleteBtnText = $customAttr['text'] ?? null;
            if ($deleteBtnText){
                $deleteBtn->text(__($deleteBtnText));
            } else {
                $deleteBtn->text(__('Delete'));
            }
            $form->addElement($deleteBtn);
        }

        return $form;
    }
}
