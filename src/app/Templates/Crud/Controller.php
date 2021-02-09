<?php

return '<?php

namespace App\\Http\\Controllers;

use ' . $this->modelImport . ';
use App\\Forms\\' . $this->model . 'Form;
use App\\Traits\\DCMSController;
use App\\Http\\Requests\\' . $this->model . 'Request;
use Pveltrop\DCMS\Classes\Datatable;

class ' . $this->model . 'Controller extends Controller
{
    use DCMSController;

    // This function defines all the settings for DCMS for the current model which belongs to this controller.
    // This will help automatically pointing this controller to the right route, class, use the right messages in alerts, etc.
    function __construct()
    {
        $this->routePrefix = "' . strtolower($this->prefix) . '";
        $this->model = ' . $this->model . '::class;
        $this->request = ' . $this->modelRequestPath . ';
        $this->form = ' . $this->model . 'Form::class;
        $this->responses = ['.$this->responseStr.'
        ];
        $this->views = ['.$this->viewStr.'
        ];
        $this->jExcel = [
            // which request attribute belongs to which jExcel column? e.g. "name" => 0, "created_at" => 3
            "columns" => [
                // "name" => 0,
                // "title" => 1,
                // "email" => 2
            ],
            // which class to use when trying to autocorrect/compare?
            // which classes/route prefixes to use when trying to autocorrect?
            "autocorrect" => [
                "user" => [
                    // which column/cell in jExcel
                    "column" => 1,
                    // class which belongs to this column
                    "class" => User::class,
                    // which attribute to use when searching to autocorrect
                    "searchAttributes" => [
                        "name"
                    ],
                    // which attribute to return back to jExcel
                    "returnAttribute" => "id",
                ]
            ],
            // finished or failed custom messages
            "responses" => [
                "finished" => [
                    "title" => __("Import succeeded"),
                    "message" => __("All data has been imported."),
                    "url" => route("'.$this->prefix.'.index")
                ],
                "failed" => [
                    "title" => __("Import failed"),
                    "message" => __("Some fields contain invalid data."),
                ]
            ]
        ];
    }

    // if you want to pass variables to the default Laravel functions, but still use DCMS functions, you can do it like below:
    // NOTE: remember to define the same default parameters for these functions.

    public function beforeIndex(){
        return [
            // "options" => $options 
        ];
    }

    public function beforeEdit($id){
        return [
            // "users" => $users 
        ];
    }

    // Define code to be executed after a model has been created/updated/deleted
    public function afterCreate(' . $this->model . 'Request $request, ' . ucfirst($this->model) . ' $' . $this->prefix . '){
        // logger("A new ' . $this->prefix . ' has been created.");
    }

    public function afterUpdate(' . $this->model . 'Request $request, ' . ucfirst($this->model) . ' $' . $this->prefix . '){
        //
    }

    public function afterCreateOrUpdate(' . $this->model . 'Request $request, ' . ucfirst($this->model) . ' $' . $this->prefix . '){
        //
    }

    public function afterDestroy($id, ' . ucfirst($this->model) . ' $' . $this->prefix . '){
        //
    }

    // If you want to use server side filtering/sorting/paging in the DCMS KTDatatables wrapper, define the base query below
    public function fetch(): \Illuminate\Http\JsonResponse
    {
        // Get class to make a query for
        $query = ' . $this->model . '::query();

        return (new Datatable($query))->render();
    }
}';
