<?php

return '<?php

namespace App\\Http\\Controllers;

use '.$modelImport.';
use Illuminate\\Http\\Request;
use App\\Forms\\'.$model.'Form;
use App\\Traits\\DCMSController;
use Pveltrop\DCMS\Classes\Datatable;
use App\\Http\\Requests\\'.$model.'Request;

class '.$model.'Controller extends Controller
{
    use DCMSController;

    // This function defines all the settings for DCMS for the current model which belongs to this controller.
    // This will help automatically pointing this controller to the right route, class, use the right messages in alerts, etc.
    function __construct()
    {
        $this->routePrefix = "'.$prefix.'";
        $this->model => '.$modelPath.',
        $this->request => '.$modelRequestPath.',
        $this->form = '.$model.'Form::class;
        $this->responses = [
            "created" => [
                "title" => __("'.ucfirst($model).' created"),
                "message" => __("'.ucfirst($model).' created on __created_at__"),
            ],
            "updated" => [
                "title" => __("'.ucfirst($model).' updated"),
                "message" => __("'.ucfirst($model).' updated on __created_at__"),
            ]
        ];
        $this->views = [
            "index" => "index",
            "show" => "show",
            "create" => "create",
            "edit" => "edit"
        ];
        $this->jExcel = [
            // which request attribute belongs to which jExcel column? e.g. "name" => 0, "created_at" => 3
            "columns" => [
                "name" => 0,
                "title" => 1,
                "email" => 2
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
                    "url" => 
                ],
                "failed" => [
                    "title" => __("Import failed"),
                    "message" => __("Some fields contain invalid data."),
                ]
            ]
        ];
    }

    // if you want to override store or update functions, uncomment and override the according function, two examples can be found below
    // DCMSJSON returns the dynamic JSON response after creating/updating

    public function store('.$model.'Request $request, '.ucfirst($model).' $'.$prefix.'){
        return $this->DCMSJSON($'.$prefix.',"created");
    }

    public function update('.$model.'Request $request, '.ucfirst($model).' $'.$prefix.'){
        return $this->DCMSJSON($'.$prefix.',"updated");
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
    public function afterCreate('.$model.'Request $request, '.ucfirst($model).' $'.$prefix.'){
        // logger("A new '.$prefix.' has been created.");
    }

    public function afterCreateOrUpdate('.$model.'Request $request, '.ucfirst($model).' $'.$prefix.'){
        // logger("A new '.$prefix.' has been created.");
    }

    // If you want to use server side filtering/sorting/paging in the DCMS KTDatatables wrapper, define the base query below
    public function fetch(): \Illuminate\Http\JsonResponse
    {
        // Get class to make a query for
        $query = '.$model.'::query();

        return (new Datatable($query))->render();
    }
}';
