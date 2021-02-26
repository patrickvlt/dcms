<?php

return '<?php

namespace App\\Http\\Controllers;

use ' . $this->modelPath . ';
'.$controllerImports.'
use App\\Forms\\' . $this->model . 'Form;
use App\\Http\\Requests\\' . $this->model . 'Request;
use Pveltrop\\DCMS\\Classes\\Datatable;
use Pveltrop\\DCMS\\Traits\\DCMSController;

class ' . $this->model . 'Controller extends Controller
{
    use DCMSController;

    // These properties setup the DCMSController trait.
    // This will initialise basic CRUD methods, Front-End columns for KTDatatable and jExcel, responses and request rules e.d.
    function __construct()
    {
        $this->routePrefix = "' . strtolower($this->prefix) . '";
        $this->model = ' . $this->model . '::class;
        $this->request = ' . $modelRequest . ';
        $this->form = ' . $this->model . 'Form::class;
        $this->responses = ['.$responseStr.'
        ];
        $this->views = ['.$viewStr.'
        ];
        '.$jExcelEntries.'

    }

    // if you want to pass variables to the default Laravel functions, but still use DCMS functions, you can do it as shown in the example below.
    // NOTE: remember to define the same default parameters for these functions.

    public function beforeIndex(){
        return [
            // "posts" => $posts
        ];
    }

    public function beforeCreate(){
        return [
            // "users" => $users
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
        $query = ' . $this->model . '::query();

        return (new Datatable($query))->render();
    }
}';
