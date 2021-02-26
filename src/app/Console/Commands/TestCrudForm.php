<?php

namespace Pveltrop\DCMS\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Pveltrop\DCMS\Http\Controllers\ModelController;
use Pveltrop\DCMS\Http\Requests\ModelRequest;

class TestCrudForm extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dcms:test-crud-form';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mock a request to the CRUD generator.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     * @throws Exception
     */
    public function handle()
    {
        $this->customRequest = new \Illuminate\Http\Request();
        $this->customRequest->setMethod('POST');

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
                "title" => [
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
                "user_id" => [
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
                ],
                "verified" => [
                    "name" => "verified",
                    "title" => "Verified?",
                    "dataType" => "boolean",
                    "class" => "",
                    "table" => "",
                    "relation" => "",
                    "method" => "",
                    "onUpdate" => "",
                    "onDelete" => "",
                    "value" => "",
                    "text" => "",
                    "inputType" => "checkbox",
                    "inputDataType" => "icheck",
                    "filePondMime" => "",
                    "seed" => "",
                    "rules" => [
                        0 => "required",
                        1 => "boolean",
                    ]
                ],
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

        $models = [];
        foreach (GetModels() as $key => $model) {
            $models[] = $model['file'];
        }
        if (in_array($this->customRequest['name'], $models, true)) {
            return response()->json([
                'message' => 'The given data was invalid.',
                'errors' => ['name' => ['This model already exists.']]], 422);
        }

        $this->customRequest = Validator::make($this->customRequest->all(), (new ModelRequest())->rules())->validate();

        (new ModelController())->generateCRUD($this->customRequest);
        (new ModelController())->generateViews($this->customRequest);

        return true;
    }
}
