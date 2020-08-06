<?php

namespace Pveltrop\DCMS\Console\Commands;

use Illuminate\Console\Command;

class Crud extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */

    protected $signature = 'dcms:crud {model}';

    /**
     * The console command description.
     *
     * @var string
     */

    protected $description = 'Generate a full crud.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */

    public function handle()
    {
        $console = $this;

        $model = ucfirst($console->argument('model'));
        shell_exec('php artisan make:model ' . $model . ' -c -m -s -f');
        shell_exec('php artisan make:request ' . $model . 'Request');

        $console->info('Created model, migration, request, seeder, factory and controller for: ' . $model . '.');

        //Adding DCMS Trait to controller
        $file = 'app/Http/Controllers/' . $model . 'Controller.php';
        $str = '<?php

namespace App\\Http\\Controllers;

use Illuminate\\Http\\Request;

use App\\Traits\\DCMSController; 

class '.$model.'Controller extends Controller
{
    use DCMSController;
}';
        file_put_contents($file, $str);
        $console->info('Added DCMS trait to controller.');

        //Adding seeder to database seed
        $file = 'database/seeds/DatabaseSeeder.php';
        $str = file_get_contents($file);
        $str = str_replace('  }
}', ('      $this->call(' . $model . 'Seeder::class);
    }
}'), $str);
        file_put_contents($file, $str);
        $console->info('Added seeder to DatabaseSeeder.');

        //Adding route
        $file = 'routes/web.php';
        file_put_contents($file, "

Route::resource('" . $model . "', '" . $model . "Controller');", FILE_APPEND);
        $console->info('Added route.');

        //Generating columns
        $columns = [];

        if ($console->confirm('Do you want to generate the columns for this model?')){
            function GenerateColumn($console,&$columns){
                $column = [];

                $dbColumn = $console->ask('What\'s the name of the column?');
                $dbType = $console->ask('What kind of database column is this?');
                $nullable = ($console->confirm('Make this column nullable?')) ? 1 : 0;
                $unsigned = ($console->confirm('Make this column unsigned?')) ? 1 : 0;

                $column['attributes'] = [
                    'name' => $dbColumn,
                    'type' => $dbType,
                    'nullable' => $nullable,
                    'unsigned' => $unsigned
                ];

                if ($console->confirm('Is this column related to another column?')){
                    $foreignColumn = [];
                    $foreignColumnName = $dbColumn;
                    $otherTable = $console->ask('Which table does this column reference to?');
                    $referenceColumnName = $console->ask('Which column from '.$otherTable.' is being referenced to?');
                    $referenceColumnClass = $console->ask('Which class does '.$referenceColumnName.' use? (e.g. Post)');
                    $console->info('Define the relationship below. This is case sensitive. See the Laravel docs for more information');
                    $relation = $console->ask('What\'s the relation between these columns? From '.$dbColumn.' to --> '.$referenceColumnName);
                    $relationFunction = $console->ask('Define the function name to call this relationship from: '.$foreignColumnName);

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

                if ($console->confirm('Do you want to seed: '.$dbColumn.'?')){
                    $column['seed'] = $console->ask('Enter the seed data (faker function, string, anything you want)');
                }

                $columns[$dbColumn] = $column;
            }

            NewColumn:
            if ($console->confirm('Do you want to generate a new column?')){
                GenerateColumn($console,$columns);
                goto NewColumn;
            }
        }

        $migration = $console->ask('Paste the path to the migration file for: '.$model);
        $request = 'app/Http/Requests/'.$model.'Request.php';
        $factory = 'database/factories/'.$model.'Factory.php';

        // $columns = [
        //     "amount" => [
        //         "attributes" => [
        //             "name" => "amount",
        //             "type" => "bigInteger",
        //             "nullable" => 0,
        //             "unsigned" => 1,
        //         ],
        //         "foreign" => [
        //             "foreign_column" => "user_id",
        //             "references" => "id",
        //             "class" => "User",
        //             "table" => "users",
        //             "relation" => "BelongsTo",
        //             "relationFunction" => "user",
        //         ],
        //         "validation" => [
        //             0 => "integer",
        //             1 => "min:1",
        //             2 => "max:100",
        //         ],
        //         "seed" => '$faker->lastName()'
        //     ],
        //     "user_id" => [
        //         "attributes" => [
        //             "name" => "user_id",
        //             "type" => "bigInteger",
        //             "nullable" => 1,
        //             "unsigned" => 1,
        //         ],
        //         "foreign" => [
        //             "foreign_column" => "user_id",
        //             "references" => "id",
        //             "class" => "User",
        //             "table" => "users",
        //             "relation" => "BelongsTo",
        //             "relationFunction" => "user",
        //         ],
        //         "validation" => [
        //             0 => "integer",
        //             1 => "unique:users",
        //         ],
        //         "seed" => '"someotherseed"'
        //     ]
        // ];

        //Adding DCMS Trait and relationship(s) to model
        $modelFile = 'app/' . $model . '.php';
        
        // Generating factory
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

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application\'s database.
|
*/

$factory->define('.$model.'::class, function (Faker $faker) {
    return [
        '.$fakerEntries.'
    ];
});';

        file_put_contents($file, $str);
        $console->info('Configured factory.');

        // Generating migration
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
        $str = '<?php

use Illuminate\\Database\\Migrations\\Migration;
use Illuminate\\Database\\Schema\\Blueprint;
use Illuminate\\Support\\Facades\\Schema;

class Create'.$model.'sTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create("'.strtolower($model).'s", function (Blueprint $table) {
            $table->id();
            '.$migEntries.'
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists("'.strtolower($model).'s");
    }
}
';
        file_put_contents($file, $str);
        $console->info('Configured migration.');

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
        $console->info('Configured migration.');

        //configure model
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

use App\\Traits\\DCMSModel;
use Illuminate\\Database\\Eloquent\\Model;

class '.$model.' extends Model
{
    use DCMSModel;
    
    '.$relEntries.'
}';

        file_put_contents($file, $str);
        $console->info('Configured model.');

        $console->info('Generated full CRUD for: ' . $model . '.');

        //configure model
        $file = $request;
        $reqEntries = '';
        foreach ($columns as $column){
            if (array_key_exists('validation',$column)){
                $ruleRow = '';
                $rules = ($column['attributes']['nullable'] == 1) ? '"nullable", ' : '';
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
        $console->info('Configured request.');



        $console->info('Generated full CRUD for: ' . $model . '.');
    }
}
