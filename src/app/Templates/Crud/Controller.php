<?php

return '<?php

namespace App\\Http\\Controllers;

use ' . $this->modelPath . ';
'.$this->controllerImports.'
use App\\Forms\\' . $this->model . 'Form;
use App\\Traits\\DCMSController;
use App\\Http\\Requests\\' . $this->model . 'Request;
use Pveltrop\DCMS\Classes\Datatable;

class ' . $this->model . 'Controller extends Controller
{
    use DCMSController;

    // These properties setup the DCMSController trait.
    // This will initialise basic CRUD methods, Front-End columns for KTDatatable and jExcel, responses and request rules e.d.
    function __construct()
    {
        $this->routePrefix = "' . strtolower($this->prefix) . '";
        $this->model = ' . $this->model . '::class;
        $this->request = ' . $this->modelRequest . ';
        $this->form = ' . $this->model . 'Form::class;
        $this->responses = ['.$this->responseStr.'
        ];
        $this->views = ['.$this->viewStr.'
        ];
        $this->jExcel = [
            // Which request attribute belongs to which jExcel column? e.g. "name" => 0, "created_at" => 3
            "columns" => ['.$this->jExcelColumnsStr.'
            ],
            // How to autocorrect data?
            "autocorrect" => ['.$this->jExcelCorrectStr.'
            ],
            // Responses when attempting to import
            "responses" => ['.$this->jExcelResponseStr.'
            ]
        ];
    }

    // if you want to pass variables to the default Laravel functions, but still use DCMS functions, you can do it like below:
    // NOTE: remember to define the same default parameters for these functions.

    public function beforeIndex(){
        return [
            // "posts" => $posts 
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

    // Define the query for the index page
    public function fetch(): \Illuminate\Http\JsonResponse
    {
        // Get class to make a query for
        $query = ' . $this->model . '::query();

        return (new Datatable($query))->render();
    }
}';
