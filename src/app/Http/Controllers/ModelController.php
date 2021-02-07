<?php

namespace Pveltrop\DCMS\Http\Controllers;

use Illuminate\Http\Request;
use Pveltrop\DCMS\Classes\Crud;
use App\Http\Controllers\Controller;
use Pveltrop\DCMS\Classes\Datatable;

class ModelController extends Controller
{
    // If you plan to use server side filtering/sorting/paging in the DCMS KTDatatables wrapper, define the base query below
    public function fetch(): \Illuminate\Http\JsonResponse
    {
        // Get class to make a query for
        $models = [];
        foreach (GetModels() as $x => $model) {
            $class = $model['class'];
            $retrievedModel = (new $class());
            $models[$x]['class'] = $model['class'];
            $models[$x]['table'] = $retrievedModel->getTable();
            $models[$x]['model'] = $retrievedModel;
        }
        $models = collect($models);
        return (new Datatable($models))->render();
    }

    public function index()
    {
        return view('dcms::model.index');
    }

    public function create()
    {
        return view('dcms::model.create');
    }

    // public function store(Request $request)
    public function store(Request $request)
    {
        $formatRequest = $request->all();
        
        $formatRequest['responses'] = json_decode($formatRequest['responses'],true);
        $formatRequest['views'] = json_decode($formatRequest['views'],true);
        $formatRequest['columns'] = json_decode($formatRequest['columns'],true);
        $formatRequest['ktColumns'] = json_decode($formatRequest['ktColumns'],true);
        $formatRequest['jExcelColumns'] = json_decode($formatRequest['jExcelColumns'],true);

        $customRequest = new \Illuminate\Http\Request();
        $customRequest->setMethod('POST');

        // $customRequest->request->add($formatRequest);
        $customRequest->request->add([
            "name" => "Post",
            "responses" => [
                'created' => [
                    'message' => 'created post',
                    'url' => '/post/1',
                ],
                'updated' => [
                    'message' => 'updated post',
                    'url' => '/post/3',
                ],
                'deleted' => [
                    'message' => 'deleted post',
                    'url' => '/post/2',
                ],
            ],
            "views" => [
                'create' => 'post.create',
                'index' => 'post.index',
                'show' => 'post.show',
                'edit' => 'post.edit',
            ],
            "columns" => [
                'title' => [
                    'name' => 'title',
                    'title' => 'Title',
                    'dataType' => 'string',
                    'nullable' => '1',
                    'required' => '1',
                    'foreign' => '1',
                    'value' => '',
                    'text' => '',
                    'inputType' => 'text',
                    'inputDataType' => '',
                    'rules' => [
                        0 => 'min:5',
                        1 => 'max:10'
                    ]
                ],
                'user_id' => [
                    'name' => 'user_id',
                    'title' => 'User',
                    'dataType' => 'string',
                    'nullable' => '1',
                    'required' => '1',
                    'foreign' => '1',
                    'value' => 'id',
                    'text' => 'name',
                    'inputType' => 'dropdown',
                    'inputDataType' => 'slimselect',
                    'rules' => [
                        0 => 'exists:users,id',
                    ]
                ],
            ],
            "ktColumns" => [
                'title' => [
                    'name' => 'title',
                    'enable' => '1',
                    'title' => 'Title',
                    'type' => 'text',
                ],
                'user_id' => [
                    'name' => 'user_id',
                    'enable' => '1',
                    'title' => 'User',
                    'value' => 'id',
                    'type' => 'text',
                ],
            ],
            "jExcelColumns" => [
                'title' => [
                    'name' => 'title',
                    'enable' => '1',
                    'title' => 'Title',
                    'type' => 'text',
                ],
                'user_id' => [
                    'name' => 'user_id',
                    'enable' => '1',
                    'title' => 'User',
                    'value' => 'id',
                    'text' => 'name',
                    'type' => 'text',
                ],
            ]
        ]);

        $noCodeString = 'not_regex:/(;|")/';

        $this->validate($customRequest, [
            'name' => ['required', 'string', $noCodeString, 'max:255'],

            'responses' => ['required', 'array', 'min:3', 'max:3'],
            'responses.*' => ['required', 'array', 'min:2', 'max:2'],
            'responses.*.message' => ['required', 'string', $noCodeString, 'min:1', 'max:255'],
            'responses.*.url' => ['required', 'string', $noCodeString, 'min:1', 'max:255'],

            'views' => ['required', 'array', 'min:4', 'max:4'],
            'views.*' => ['required', 'string', 'regex:/(\.)/', $noCodeString, 'min:1', 'max:255'],
            
            'columns' => ['required', 'array', 'min:1'],
            'columns.*.name' => ['required', 'string', $noCodeString, 'min:1', 'max:255'],
            'columns.*.title' => ['required', 'string', $noCodeString, 'min:1', 'max:255'],
            'columns.*.dataType' => ['required', 'string', $noCodeString, 'min:1', 'max:255'],
            'columns.*.nullable' => ['required', 'boolean'],
            'columns.*.required' => ['required', 'boolean'],
            'columns.*.foreign' => ['required', 'boolean'],
            'columns.*.text' => ['string', 'min:1', $noCodeString, 'max:25'],
            'columns.*.value' => ['string', 'min:1', $noCodeString, 'max:25'],
            'columns.*.inputType' => ['required', 'string', $noCodeString, 'min:1', 'max:255'],
            'columns.*.inputDataType' => ['nullable','string', $noCodeString, 'max:25'],
            
            'ktColumns' => ['required', 'array', 'min:1'],
            'ktColumns.*.name' => ['required', 'string', $noCodeString, 'min:1', 'max:255'],
            'ktColumns.*.enable' => ['required', 'boolean'],
            'ktColumns.*.title' => ['required', 'string', $noCodeString, 'min:1', 'max:255'],
            'ktColumns.*.type' => ['required', 'string', $noCodeString, 'min:1', 'max:255'],
            'ktColumns.*.value' => ['nullable','string', $noCodeString, 'min:1', 'max:255'],

            'jExcelColumns' => ['required', 'array', 'min:1'],
            'jExcelColumns.*.name' => ['required', 'string', $noCodeString, 'min:1', 'max:255'],
            'jExcelColumns.*.enable' => ['required', 'boolean'],
            'jExcelColumns.*.title' => ['required', 'string', $noCodeString, 'min:1', 'max:255'],
            'jExcelColumns.*.type' => ['required', 'string', $noCodeString, 'min:1', 'max:255'],
            'jExcelColumns.*.value' => ['nullable','string', $noCodeString, 'min:1', 'max:255'],
        ]);

        // (new Crud())->generate([
        //     'model' => 'Foo',
        //     'amountToSeed' => 10,
        //     'columns' => [
        //         "title" => [
        //             "attributes" => [
        //                 "name" => "title",
        //                 "type" => "string",
        //                 "nullable" => 0,
        //                 "unsigned" => 0,
        //                 "required" => 1,
        //             ],
        //             "validation" => [
        //                 0 => "min:1",
        //                 1 => "string"
        //             ],
        //             "seed" => 'faker->word()',
        //         ],
        //         "user_id" => [
        //             "attributes" => [
        //                 "name" => "user_id",
        //                 "type" => "bigInteger",
        //                 "nullable" => 0,
        //                 "unsigned" => 1,
        //                 "required" => 1,
        //             ],
        //             "foreign" => [
        //                 "foreign_column" => "user_id",
        //                 "references" => "id",
        //                 "class" => "User",
        //                 "table" => "users",
        //                 "relation" => "belongsTo",
        //                 "relationFunction" => "user",
        //                 "onUpdate" => "cascade",
        //                 "onDelete" => "cascade",
        //             ],
        //             "validation" => [
        //                 0 => "min:0",
        //                 1 => "exists:users,id",
        //             ],
        //             "seed" => 'faker->randomElement(User::all()->pluck("id"))'
        //         ],
        //     ]
        // ]);
    }
}
