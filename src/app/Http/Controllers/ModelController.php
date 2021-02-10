<?php

namespace Pveltrop\DCMS\Http\Controllers;

use Illuminate\Http\Request;
use Pveltrop\DCMS\Classes\Crud;
use App\Http\Controllers\Controller;
use Pveltrop\DCMS\Classes\Datatable;

class ModelController extends Controller
{
    // If you plan to use server side filtering/sorting/paging in the DCMS KTDatatables wrapper, define the base query below
    private $customRequest;

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

    public function generateCRUD()
    {
        (new Crud())->generate($this->customRequest);
    }

    public function generateViews()
    {
        $views = $this->customRequest['views'];
        foreach ($views as $x => $path) {
            $file = resource_path().'/views/'.str_replace('.', '/', $path).'.blade.php';
            $folder = preg_replace('/\/[^]\/[^\s]*\.blade\.php/m', '', $file);
            if (!is_dir($folder) && !mkdir($folder, 0755, true) && !is_dir($folder)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $folder));
            }
            file_put_contents($file, '');
        }
    }

    // public function store(Request $request)
    public function store()
    {
        // $formatRequest = $request->all();

        // $formatRequest['responses'] = json_decode($formatRequest['responses'],true);
        // $formatRequest['views'] = json_decode($formatRequest['views'],true);
        // $formatRequest['columns'] = json_decode($formatRequest['columns'],true);
        // $formatRequest['ktColumns'] = json_decode($formatRequest['ktColumns'],true);
        // $formatRequest['jExcelColumns'] = json_decode($formatRequest['jExcelColumns'],true);
        // $formatRequest['jExcelResponses'] = json_decode($formatRequest['jExcelResponses'],true);

        $this->customRequest = new \Illuminate\Http\Request();
        $this->customRequest->setMethod('POST');

        // $this->customRequest->request->add($formatRequest);
        $this->customRequest->request->add([
      "name" => "Foo",
      "seed" => "1",
      "amountToSeed" => "15",
      "responses" => [
        "created" => [
          "message" => "created foo",
          "url" => "/foo/index",
        ],
        "updated" => [
          "message" => "updated foo",
          "url" => "/foo/index",
        ],
        "deleted" => [
          "message" => "deleted foo",
          "url" => "/foo/index",
        ],
      ],
      "views" => [
        "create" => "foo.create",
        "index" => "foo.index",
        "show" => "foo.show",
        "edit" => "foo.edit",
      ],
      "columns" => [
        "title" =>  [
          "name" => "title",
          "title" => "Title",
          "dataType" => "string",
          "class" => "",
          "table" => "",
          "relation" => "",
          "method" => "",
          "onUpdate" => "",
          "onDelete" => "",
          "value" => "",
          "text" => "",
          "inputType" => "file",
          "inputDataType" => "filepond",
          "filePondMime" => "image",
          "seed" => "",
          "rules" => [
            0 => "min:5",
            1 => "max:25",
          ]
        ],
        "user_id" =>  [
          "name" => "user_id",
          "title" => "User",
          "dataType" => "bigInteger",
          "foreign" => 1,
          "class" => "User",
          "table" => "users",
          "relation" => "belongsTo",
          "method" => "user",
          "onUpdate" => "cascade",
          "onDelete" => "cascade",
          "value" => "id",
          "text" => "name",
          "inputType" => "select",
          "inputDataType" => "slimselect",
          "seed" => "",
          "rules" => [
            0 => "exists:users,id",
          ]
        ]
      ],
      "ktColumns" => [
        "title" => [
          "name" => "title",
          "enable" => "1",
          "title" => "Title",
          "type" => "text",
        ],
        "user_id" => [
          "name" => "user_id",
          "enable" => "1",
          "title" => "User",
          "value" => "id",
          "type" => "price",
        ]
      ],
      "jExcelColumns" => [
        "title" => [
          "name" => "title",
          "enable" => "1",
          "title" => "Title",
          "type" => "text",
        ],
        "user_id" => [
          "name" => "user_id",
          "enable" => "1",
          "title" => "User",
          "value" => "id",
          "text" => "name",
          "type" => "text",
        ]
        ],
        "jExcelResponses" => [
          "success" => [
            "name" => "success",
            "message" => "1",
            "url" => "2",
          ],
          "failed" => [
            "name" => "failed",
            "title" => "3",
            "message" => "4",
          ]
        ]
    ]);

        $noCodeString = 'not_regex:/(;|")/';

        $models = [];
        foreach (GetModels() as $key => $model) {
            $models[] = $model['file'];
        }

        if (in_array($this->customRequest['name'], $models)) {
            return response()->json([
        'message' => 'The given data was invalid.',
        'errors' => ['name' => ['This model already exists.']]], 422);
        }

        $this->customRequest = $this->validate($this->customRequest, [
      'name' => ['required', 'string', $noCodeString, 'max:255'],
      'seed' => ['nullable', 'boolean'],
      'amountToSeed' => [($this->customRequest->seed) ? 'required' : 'nullable', 'integer', 'min:0'],

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
      'columns.*.nullable' => ['nullable', 'boolean'],
      'columns.*.required' => ['nullable', 'boolean'],
      'columns.*.foreign' => ['nullable', 'boolean'],
      'columns.*.text' => ['string', 'min:1', $noCodeString, 'max:25'],
      'columns.*.value' => ['string', 'min:1', $noCodeString, 'max:25'],
      'columns.*.inputType' => ['required', 'string', $noCodeString, 'min:1', 'max:255'],
      'columns.*.inputDataType' => ['nullable', 'string', $noCodeString, 'max:25'],
      'columns.*.filePondMime' => ['nullable', 'string', $noCodeString, 'max:25'],

      'columns.*.class' => ['string', $noCodeString, 'min:1', 'max:255'],
      'columns.*.table' => ['string', $noCodeString, 'min:1', 'max:255'],
      'columns.*.relation' => ['string', $noCodeString, 'min:1', 'max:255'],
      'columns.*.method' => ['string', $noCodeString, 'min:1', 'max:255'],
      'columns.*.onUpdate' => ['string', $noCodeString, 'min:1', 'max:255'],
      'columns.*.onDelete' => ['string', $noCodeString, 'min:1', 'max:255'],

      'columns.*.seed' => ['nullable', 'string', $noCodeString, 'min:1', 'max:255'],
      'columns.*.rules' => ['nullable', 'array', 'min:0', 'max:50'],
      'columns.*.rules.*' => ['nullable', 'string', $noCodeString, 'min:1', 'max:255'],

      'ktColumns' => ['required', 'array', 'min:1'],
      'ktColumns.*.name' => ['required', 'string', $noCodeString, 'min:1', 'max:255'],
      'ktColumns.*.enable' => ['required', 'boolean'],
      'ktColumns.*.title' => ['required', 'string', $noCodeString, 'min:1', 'max:255'],
      'ktColumns.*.type' => ['required', 'string', $noCodeString, 'min:1', 'max:255'],
      'ktColumns.*.value' => ['nullable', 'string', $noCodeString, 'min:1', 'max:255'],

      'jExcelColumns' => ['required', 'array', 'min:1'],
      'jExcelColumns.*.name' => ['required', 'string', $noCodeString, 'min:1', 'max:255'],
      'jExcelColumns.*.enable' => ['required', 'boolean'],
      'jExcelColumns.*.title' => ['required', 'string', $noCodeString, 'min:1', 'max:255'],
      'jExcelColumns.*.type' => ['required', 'string', $noCodeString, 'min:1', 'max:255'],
      'jExcelColumns.*.value' => ['nullable', 'string', $noCodeString, 'min:1', 'max:255'],

      'jExcelResponses.*.title' => ['nullable', 'string', $noCodeString, 'min:1', 'max:255'],
      'jExcelResponses.*.message' => ['nullable', 'string', $noCodeString, 'min:1', 'max:255'],
      'jExcelResponses.*.url' => ['nullable', 'string', $noCodeString, 'min:1', 'max:255'],
    ]);

        $this->generateCRUD();
        $this->generateViews();
    }
}
