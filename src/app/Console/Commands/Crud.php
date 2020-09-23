<?php

namespace Pveltrop\DCMS\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class Crud extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */

    protected $signature = 'dcms:make-crud {model}';

    /**
     * The console command description.
     *
     * @var string
     */

    protected $description = 'Generate full crud functionality.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */

    public function handle()
    {
        $console = $this;

        if (Storage::exists('/DCMS/Generate.php')){
            $initManual = false;
            try {
                include(base_path().'/storage/app/DCMS/Generate.php');
            } catch (\Exception $e){
                $console->comment('');
                $console->error('File not found: /storage/app/DCMS/Generate.php');
                $console->comment('Initalising manual creation in two seconds.');
                $console->comment('');
                $initManual = true;
                sleep(2);
            }
            if ($initManual == false){
                try {
                    $fileContent = file_get_contents(base_path().'/storage/app/DCMS/Generate.php');
                    $fileModel = preg_match('/\$model(\s|=)/m', $fileContent) == 1;
                    $fileColumns = preg_match('/\$columns(\s|=)/m', $fileContent) == 1;
                    if (!$fileModel){
                        $console->comment('');
                        $console->error('$model not defined in: /storage/app/DCMS/Generate.php');
                        $console->comment('');
                        $initManual = true;
                    }
                    if (!$fileColumns){
                        $console->comment('');
                        $console->error('$columns not defined in: /storage/app/DCMS/Generate.php');
                        $console->comment('');
                        $initManual = true;
                    }
                } catch (\Exception $e){
                    sleep(2);
                }
            }
        } else {
            $columns = [];
            $model = $console->argument('model');
            $prefix = strtolower($model);
        }

        if ($initManual){
            if (!$console->confirm('Do you want to proceed? Columns have to be defined manually.')){
                exit;
            }
        }

        /**
         *
         * Create the basic Laravel files
         *
         */

        shell_exec('php artisan make:model ' . $model . ' -c -m');
        shell_exec('php artisan make:request ' . $model . 'Request');
        $GLOBALS['enableSeed'] = false;

        $console->comment('');
        $console->comment('Created model, migration, request, seeder, factory and controller for: ' . $model . '.');
        $console->comment('');

        if ($initManual){

            /**
             *
             * Generate columns/fields with user input
             *
             */

            if ($console->confirm('Do you want to generate the columns for this model?')){
                function GenerateColumn($console,&$columns){
                    $column = [];

                    $dbColumn = $console->ask('What\'s the name of the column?');
                    $dbType = $console->ask('What kind of database column is this?');
                    $nullable = ($console->confirm('Make this column nullable?')) ? 1 : 0;
                    $required = ($console->confirm('Make this column required?')) ? 1 : 0;
                    $unsigned = ($console->confirm('Make this column unsigned?')) ? 1 : 0;

                    $column['attributes'] = [
                        'name' => $dbColumn,
                        'type' => $dbType,
                        'nullable' => $nullable,
                        'unsigned' => $unsigned,
                        'required' => $required
                    ];

                    /**
                     * Define relations/foreign keys with user input
                     */

                    if ($console->confirm('Is this column related to another column?')){
                        $foreignColumn = [];
                        $foreignColumnName = $dbColumn;
                        $otherTable = $console->ask('Which table does this column reference to?');
                        $referenceColumnName = $console->ask('Which column from '.$otherTable.' is being referenced to?');
                        $referenceColumnClass = $console->ask('Which class belongs to the '.$otherTable.' table? (e.g. Post)');
                        $console->comment('');
                        $console->comment('Define the relationship below. This is case sensitive. See the Laravel docs for more information.');
                        $console->comment('');
                        $relation = $console->ask('What\'s the relation between these columns? From '.$dbColumn.' to --> '.$referenceColumnName);
                        $relationFunction = $console->ask('Define the function name to call this relationship. Don\'t end with parentheses. (e.g. user)');

                        $foreignColumn = [
                            'foreign_column' => $foreignColumnName,
                            'references' => $referenceColumnName,
                            'class' => $referenceColumnClass,
                            'table' => $otherTable,
                            'relation' => $relation,
                            'relationFunction' => $relationFunction
                        ];

                        $column['foreign'] = $foreignColumn;
                    }

                    /**
                     * Define validation rules with user input
                     */

                    $validationRules = [];
                    if ($console->confirm('Do you want to write the validation rules for: '.$dbColumn.'?')){

                        NewRule:

                        $console->line('Examples:');
                        $console->line('regex:/foobar/');
                        $console->line('min:1');
                        $console->line('required');
                        $console->line('Press enter to finish adding rules.');
                        $newRule = $console->ask('Type the new rule');

                        array_push($validationRules,$newRule);

                        if ($console->confirm('Add another rule?')){
                            goto NewRule;
                        }

                        $column['validation'] = $validationRules;
                    }
                    $GLOBALS['enableSeed'] = $console->confirm('Do you want to seed: '.$dbColumn.'?');
                    if ($GLOBALS['enableSeed']){
                        $column['seed'] = $console->ask('Enter the data to seed. (For example: $faker->word(), or "Seed this sentence"). Don\'t end with a semicolon or parentheses.');
                    }

                    $columns[$dbColumn] = $column;
                }

                NewColumn:
                if ($console->confirm('Do you want to generate a new column?')){
                    GenerateColumn($console,$columns);
                    goto NewColumn;
                }
            }

            /**
             *
             * Generate seeder with user input
             *
             */

            if($GLOBALS['enableSeed']){
                //Adding seeder to database seed
                $file = 'database/seeds/DatabaseSeeder.php';
                $str = file_get_contents($file);
                $str = str_replace('  }
}', ('      $this->call(' . $model . 'Seeder::class);
    }
}'), $str);
                file_put_contents($file, $str);

                $console->comment('');
                $console->comment('Added seeder to DatabaseSeeder.');
                $console->comment('');

                // Generating factory
                $factory = 'database/factories/'.$model.'Factory.php';
                $file = $factory;
                $tab = '        ';
                $fakerEntries = '';
                foreach ($columns as $name => $column){
                    if (array_key_exists('seed',$column)){
                        $fakerEntries .= '"'.$name.'" => '.$column['seed'].','."\n".$tab;
                    }
                }
                $str = '<?php

/** @var \\Illuminate\\Database\\Eloquent\\Factory $factory */

use App\\'.$model.';
use Faker\\Generator as Faker;
use Illuminate\\Support\\Str;

$factory->define('.$model.'::class, function (Faker $faker) {
    return [
        '.$fakerEntries.'
    ];
});';
                file_put_contents($file, $str);

                $console->comment('');
                $console->comment('Generated factory.');
                $console->comment('');

                //configure seeder
                $file = 'database/seeds/'.$model.'Seeder.php';
                $amount = $console->ask('How many objects should be seeded? Enter a number.');
                $str = "<?php

use Illuminate\\Database\\Seeder;

class ".$model."Seeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(App\\".$model."::class, ".$amount.")->create();
    }
}
";
                file_put_contents($file, $str);

                $console->comment('');
                $console->comment('Generated seeder.');
                $console->comment('');
            }
        } else {
            dd($columns);
        }

        /**
         *
         * Generating default route
         *
         */

        $file = 'routes/web.php';
        file_put_contents($file, "

Route::resource('" . $prefix . "', '" . $model . "Controller');", FILE_APPEND);

        $console->comment('');
        $console->comment('Added route.');
        $console->comment('');

        /**
         *
         * Generating controller
         *
         */

        $file = 'app/Http/Controllers/' . $model . 'Controller.php';
        $str = '<?php

namespace App\\Http\\Controllers;

use Illuminate\\Http\\Request;

use App\\'.$model.';
use App\\Traits\\DCMSController;

class '.$model.'Controller extends Controller
{
    use DCMSController;

    // All the code below is optional, you can use this as a reference or to override variables/functions.

    // // This function defines all the settings for DCMS for the current object.
    // // This will help automatically pointing this controller to the right route, class, use the right messages in alerts, etc.
    // function DCMS()
    // {
    //     return [
    //         "routePrefix" => "'.$prefix.'",
    //         "class" => "'.$model.'",
    //         "indexQuery" => '.$model.'::all(),
    //         // DCMS JSON responses and redirects for CRUD
    //         "created" => [
    //             "title" => __("'.$model.' created"),
    //             "message" => __("'.$model.' created on __created_at__"),
    //             "url" => "/'.$prefix.'"
    //         ],
    //         "updated" => [
    //             "title" => __("__name__ updated"),
    //             "message" => __("__name__ updated on __created_at__"),
    //             "url" => "/'.$prefix.'"
    //         ],
    //         "deleted" => [
    //             "url" => "/'.$prefix.'"
    //         ],
    //         "imported" => [
    //             "url" => "/'.$prefix.'"
    //         ],
    //         // Optional request file or view(s)
    //         "request" => "'.$model.'Request",
    //         "views" => [
    //             "index" => "index",
    //             "show" => "crud",
    //             "edit" => "crud",
    //             "create" => "crud"
    //         ],
    //         // for jExcel imports
    //         "import" => [
    //             // which request attribute belongs to which jExcel column? e.g. "name" => 0, "created_at" => 3
    //             "columns" => [
    //                 "name" => 0,
    //                 "created_at" => 5
    //             ],
    //             // which classes/route prefixes to use when trying to autocorrect?
    //             "autocorrect" => [
    //                 "foo" => [
    //                     // which column/cell in jExcel
    //                     "column" => 1,
    //                     // which fields to compare with
    //                     "fields" => [
    //                         "bar"
    //                     ]
    //                 ]
    //             ],
    //             // finished or failed custom messages
    //             "finished" => [
    //                 "title" => __("Import succeeded"),
    //                 "message" => __("All data has been imported."),
    //             ],
    //             "failed" => [
    //                 "title" => __("Import failed"),
    //                 "message" => __("Some fields contain invalid data."),
    //             ]
    //         ]
    //     ];
    // }

    // // if you want to override store or update functions, uncomment and override the according function, two examples can be found below
    // // DCMSJSON returns the dynamic JSON response after creating/updating

    // public function store('.$model.'Request $request, '.$model.' $'.$prefix.'){
    //     return $this->DCMSJSON($'.$prefix.',"created");
    // }

    // public function update('.$model.'Request $request, '.$model.' $'.$prefix.'){
    //     return $this->DCMSJSON($'.$prefix.',"updated");
    // }

    // // if you want to pass variables to the default Laravel functions, but still use DCMS functions, you can do it like below:
    // // NOTE: remember to define the same default parameters for these functions.

    // public function beforeIndex(){
    //     $someVar = "someValue";
    //     $someArr = [];
    //     return compact("someVar","someArr");
    // }

    // public function beforeEdit($id){
    //     $someVar = "someValue";
    //     $someArr = [];
    //     return compact("someVar","someArr");
    // }

    // // If you plan to use server side filtering/sorting/paging in the DCMS KTDatatables wrapper, define the base query below
    // public function fetch()
    // {
    //     // Get class to make a query for
    //     $query = '.$model.'::query();
    //
    //     return (new '.$model.'Datatable($query))->render();
    // }
}';
        file_put_contents($file, $str);

        $console->comment('');
        $console->comment('Generated controller.');
        $console->comment('');

        /**
         *
         * Generate migration
         *
         */

        $files = scandir(base_path().'/database/migrations', SCANDIR_SORT_DESCENDING);
        $migration = base_path().'/database/migrations/'.$files[0];
        $file = $migration;
        $tab = '        ';
        $migEntries = '';
        foreach ($columns as $name => $column){
            $rowNullable = ($column['attributes']['nullable'] == 1) ? '->nullable()' : '';
            $rowUnsigned = ($column['attributes']['unsigned'] == 1) ? '->unsigned()' : '';
            $migEntries .= '$table->'.$column['attributes']['type'].'("'.$column['attributes']['name'].'")'.$rowNullable.$rowUnsigned.';'."\n".'            ';
            if (array_key_exists('foreign',$column)){
                $console->info('Generating migration row for relation from: '.$column['foreign']['foreign_column'].' to '.$column['foreign']['table'].': '.$column['foreign']['references'] );
                $onUpdate = $console->ask('What to do on update? (cascade, no action, restrict, set null)');
                $onUpdate = '->onUpdate("'.$onUpdate.'")';
                $onDelete = ($console->ask('What to do on delete? (cascade, no action, restrict, set null)'));
                $onDelete = '->onDelete("'.$onDelete.'")';
                $migEntries .= '$table->foreign("'.$column['foreign']['foreign_column'].'")->references("'.$column['foreign']['references'].'")->on("'.$column['foreign']['table'].'")'.$onUpdate.$onDelete.';'."\n".'            ';
            }
        }
        $migContent = '$table->id();
            '.$migEntries.'';
        $str = file_get_contents($file);
        $str = str_replace('$table->id();',$migContent, $str);
        file_put_contents($file, $str);

        $console->comment('');
        $console->comment('Configured migration.');
        $console->comment('');

        /**
         *
         * Generate model
         *
         */

        $modelFile = 'app/' . $model . '.php';
        $file = $modelFile;
        $relEntries = '';
        foreach ($columns as $name => $column){
            try {
                if (array_key_exists('foreign',$column)){
                        $relEntries .= 'public function '.$column['foreign']['relationFunction'].'()
    {
        return $this->'.$column['foreign']['relation'].'('.$column['foreign']['class'].'::class, \''.$column['foreign']['foreign_column'].'\', \''.$column['foreign']['references'].'\');
    }

    ';
                    }
                } catch (\Throwable $th) {
                    //throw $th;
                }
        }
        $str = '<?php

namespace App;

use Illuminate\\Database\\Eloquent\\Model;

class '.$model.' extends Model
{
    protected $guarded = ["id"];

    '.$relEntries.'
}';

        file_put_contents($file, $str);
        $console->comment('');
        $console->comment('Generated model.');
        $console->comment('');

        //configure request
        $request = 'app/Http/Requests/'.$model.'Request.php';
        $file = $request;
        $reqEntries = '';
        foreach ($columns as $column){
            if (array_key_exists('validation',$column)){
                $ruleRow = '';
                $rules = ($column['attributes']['nullable'] == 1) ? '"nullable", ' : '';
                $rules = ($column['attributes']['required'] == 1) ? '"required", ' : '';
                foreach ($column['validation'] as $x => $rule){
                    $rules .= '"'.$rule.'", ';
                }
                $ruleRow = '"'.$column["attributes"]["name"].'" => ['.$rules.']';
                $reqEntries .= $ruleRow.',
            ';
            }
        }
        $str = '<?php

namespace App\\Http\\Requests;

use Illuminate\\Foundation\\Http\\FormRequest;

class '.$model.'Request extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
    *
    * DCMS: Modify a request before it is validated, make sure to return an array with keys the request will recognize
    *
    */

    public function beforeValidation()
    {
        $request = request()->all();

        // // Modify all requests
        // $request["foo"] = "bar";
        // // Modify store request
        // if (FormMethod() == "POST"){
        // }
        // // Modify update request
        // else if (FormMethod() == "PUT"){
        // }

        return $request;
    }

    /**
    *
    * DCMS: Place validation for file uploads here, refer to the Laravel documentation. You can still use messages() to return custom messages.
    *
    */

    public function uploadRules()
    {
        return [
            // "logo.*" => ["nullable","mimes:jpeg, jpg, png, jpg, gif, svg, webp", "max:2000"],
            // "sheet" => ["nullable","mimes:octet-stream, vnd.ms-excel, msexcel, x-msexcel, x-excel, x-dos_ms_excel, xls, x-xls, , vnd.openxmlformats-officedocument.spreadsheetml.sheet", "max:2000"],
        ];
    }


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            '.$reqEntries.'
        ];
    }

    public function messages()
    {
        return [
            //
        ];
    }
}
        ';

        file_put_contents($file, $str);
        $console->comment('');
        $console->comment('Generated custom request.');
        $console->comment('');

        $console->comment('');
        $console->comment('Generated full CRUD for: ' . $model . '.');
        $console->comment('');
    }
}
