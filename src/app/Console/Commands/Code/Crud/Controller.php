<?php

return '<?php

namespace App\\Http\\Controllers;

use Illuminate\\Http\\Request;
use App\\Http\\Requests\\'.$model.'Request;

use '.$modelImport.';
use App\\Traits\\DCMSController;
use Pveltrop\DCMS\Classes\Datatable;

class '.$model.'Controller extends Controller
{
    use DCMSController;

    // This function defines all the settings for DCMS for the current model which belongs to this controller.
    // This will help automatically pointing this controller to the right route, class, use the right messages in alerts, etc.
    function DCMS()
    {
        return [
            // Class and request should be defined, these are required
            // Define classes with complete namespace below
            "model" => '.$modelPath.',
            "request" => '.$modelRequestPath.',

            // All keys below are optional

            "routePrefix" => "'.$prefix.'",
            // DCMS JSON responses and redirects for CRUD
            "created" => [
                "title" => __("'.$model.' created"),
                "message" => __("'.$model.' created on __created_at__"),
                "url" => "/'.$prefix.'"
            ],
            "updated" => [
                "title" => __("__name__ updated"),
                "message" => __("__name__ updated on __created_at__"),
                "url" => "/'.$prefix.'"
            ],
            "deleted" => [
                "url" => "/'.$prefix.'"
            ],
            "imported" => [
                "url" => "/'.$prefix.'"
            ],           
            "views" => [
                "index" => "index",
                "show" => "crud",
                "edit" => "crud",
                "create" => "crud"
            ],
            // for jExcel imports
            "import" => [
                // which request attribute belongs to which jExcel column? e.g. "name" => 0, "created_at" => 3
                "columns" => [
                    "name" => 0,
                    "created_at" => 5
                ],
                // which classes/route prefixes to use when trying to autocorrect?
                "autocorrect" => [
                    "foo" => [
                        // which column/cell in jExcel
                        "column" => 1,
                        // which fields to compare with
                        "fields" => [
                            "bar"
                        ]
                    ]
                ],
                // finished or failed custom messages
                "finished" => [
                    "title" => __("Import succeeded"),
                    "message" => __("All data has been imported."),
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

    public function store('.$model.'Request $request, '.$model.' $'.$prefix.'){
        return $this->DCMSJSON($'.$prefix.',"created");
    }

    public function update('.$model.'Request $request, '.$model.' $'.$prefix.'){
        return $this->DCMSJSON($'.$prefix.',"updated");
    }

    // if you want to pass variables to the default Laravel functions, but still use DCMS functions, you can do it like below:
    // NOTE: remember to define the same default parameters for these functions.

    public function beforeIndex(){
        $someVar = "someValue";
        $someArr = [];
        return compact("someVar","someArr");
    }

    public function beforeEdit($id){
        $someVar = "someValue";
        $someArr = [];
        return compact("someVar","someArr");
    }

    // If you plan to use server side filtering/sorting/paging in the DCMS KTDatatables wrapper, define the base query below
    public function fetch(): \Illuminate\Http\JsonResponse
    {
        // Get class to make a query for
        $query = '.$model.'::query();

        return (new Datatable($query))->render();
    }
}';
