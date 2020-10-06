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
     * Generate form for mass assignment, based on Laravel configurations, similar to Symfony
     * Grabs columns which are fillable, or undefined in guarded
     * @param $class
     * @return Form|null
     */

    public static function create($class,$request,$properties=[])
    {
        $modelColumns = Schema::getColumnListing((new $class())->getTable());
        $modelRequest = (new $request())->rules();
        $table = (new $class())->getTable();
        $builder = DB::getSchemaBuilder();
        $columns = [];
        foreach ($modelRequest as $requestCol => $rules){
            $column['name'] = $requestCol;
            $column['type'] = $builder->getColumnType($table, $requestCol);
            $column['rules'] = $rules;
            $columns[] = $column;
        }
        $form = self::createElement('form')->attr([
            'action' => FormRoute(),
            'method' => 'POST',
            'data-dcms-action' => 'ajax'
        ]);
        $form->addElement('input')->attr([
            'type' => 'hidden',
            'name' => '_method',
            'value' => 'POST'
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
                if ($inputText){
                    $inputPrepend->text(__($inputText));
                } else if ($inputIcon){
                    $inputPrepend->addElement('i')->attr($inputIcon);
                }
            }

            // Input field
            $customAttr = $definedAttr['input-group-prepend'] ?? null;
            $inputType = $definedAttr['type'] ?? null;
            $inputDataType = $definedAttr['data-type'] ?? null;
            if (!$inputType){
                switch ($inputType) {
                    case 'binary':
                        $inputType = 'number';
                        break;
                }
            }
            if (!$inputDataType){
                $inputDataType = 'text';
            }
            $inputField = $inputGroup->addElement('input')->attr([
                'id' => $column['name'],
                'class' => 'form-control',
                'type' => 'text',
                'name' => $column['name'],
                'placeholder' => __('1.0.0'),
                'value' => Model()->version ?? old('version')
            ])->attr($customAttr);

            if (1 == 1){
                $formGroup->addElement('small')->attr([
                    'id' => 'versionHelp',
                    'class' => 'form-text text-muted',
                ])->text(__('Enter a version number, which will be linked to your pages.'));
            }
        }
        return $form;
    }
}
