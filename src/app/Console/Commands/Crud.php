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
        $initManual = true;
        $mainVersion = app()->version()[0];

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
                    $filePrefix = preg_match('/\$prefix(\s|=)/m', $fileContent) == 1;
                    $fileColumns = preg_match('/\$columns(\s|=)/m', $fileContent) == 1;
                    if (!$fileModel){
                        $console->comment('');
                        $console->error('$model not defined in: /storage/app/DCMS/Generate.php');
                        $console->comment('');
                        $initManual = true;
                    }
                    if (!$filePrefix){
                        $console->comment('');
                        $console->error('$prefix not defined in: /storage/app/DCMS/Generate.php');
                        $console->comment('');
                        $initManual = true;
                    }
                    if (!$fileColumns){
                        $console->comment('');
                        $console->error('$columns not defined in: /storage/app/DCMS/Generate.php');
                        $console->comment('');
                        $initManual = true;
                    }
                    include (base_path().'/storage/app/DCMS/Generate.php');
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
            if (!$console->confirm('Couldn\'t find predefined columns. Do you want to proceed? ')){
                exit;
            }
        }

        /**
         *
         * Create the basic Laravel files (model, controller, factory, seeder, request)
         *
         */

        shell_exec('php artisan make:model ' . $model . ' -c -m -f -s');
        shell_exec('php artisan make:request ' . $model . 'Request');

        // Use different settings per Laravel version
        $modelPath = '';
        $makeSeeder = true;
        if ($mainVersion <= 7){
            $modelPath = 'App\\'.$model;
        } else if ($mainVersion >= 8){
            $modelPath = 'App\\Models\\'.$model;
        }

        $console->comment('');
        $console->comment('Created model, migration, request, factory, seeder and controller for: ' . $model . '.');
        $console->comment('');

        if ($initManual){

            /**
             *
             * Generate columns/fields with user input
             *
             */

            if ($console->confirm('Do you want to generate the columns for this model?')){
                function GenerateColumn($console,&$columns,$mainVersion) {
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
                    if ($console->confirm('Do you want to seed: '.$dbColumn.' with a factory?')){
                        if ($mainVersion <= 7){
                            $fakerExample = '$faker->word()';
                        } else if ($mainVersion >= 8){
                            $fakerExample = '$this->faker->word()';
                        }
                        $column['seed'] = $console->ask('Enter the data to seed. (For example: '.$fakerExample.', or "Seed this sentence"). Don\'t end with a semicolon or parentheses.');
                    }

                    $columns[$dbColumn] = $column;
                }

                NewColumn:
                if ($console->confirm('Do you want to generate a new column?')){
                    GenerateColumn($console,$columns,$mainVersion);
                    goto NewColumn;
                }
            }
        }

        $enableSeed = false;
        foreach($columns as $column){
            if (isset($column['seed'])){
                $enableSeed = true;
            }
        }

        if($enableSeed){

            /**
             *
             * Add entry to DatabaseSeeder
             *
             */

            $seedAmount = $console->ask('How many objects should be seeded through factories? Enter a number.');

            if ($mainVersion <= 7){
                $file = 'database/seeds/DatabaseSeeder.php';
            } else if ($mainVersion >= 8){
                $file = 'database/seeders/DatabaseSeeder.php';
            }

            $contentToAdd = '        $this->call(' . $model . 'Seeder::class);';

            // Modify the content
            $content = file_get_contents(base_path($file));
            $newContent = AppendContent($content,2,$contentToAdd);
            // Write to file
            file_put_contents(base_path($file),str_replace($content,$newContent,file_get_contents(base_path($file))));

            $console->comment('');
            $console->comment('Added entry to DatabaseSeeder.');
            $console->comment('');


            /**
             *
             * Generate Factory
             *
             */

            $fakerEntries = '';
            $tab = '            ';

            foreach ($columns as $name => $column){
                if (array_key_exists('seed',$column)){
                    $fakerEntries .= $tab.'"'.$name.'" => '.$column['seed'].','."\n";
                }
            }

            $factoryFile = 'database/factories/'.$model.'Factory.php';
            if ($mainVersion <= 7){
                $factoryLine = 10;
            } else if ($mainVersion >= 8){
                $factoryLine = 25;
            }

            // File
            $contentToAdd = $fakerEntries;
            // Modify the content
            $content = file_get_contents(base_path($factoryFile));
            $newContent = WriteContent($content,$factoryLine,$contentToAdd);
            // Write to file
            file_put_contents(base_path($factoryFile),str_replace($content,$newContent,file_get_contents(base_path($factoryFile))));

            $console->comment('');
            $console->comment('Generated factory.');
            $console->comment('');

            if ($makeSeeder){

                /**
                 *
                 * Generate Seeder
                 *
                 */

                if ($mainVersion <= 7){
                    $seederFile = 'database/seeds/'.$model.'Seeder.php';
                    $contentToAdd = "        factory(App\\".$model."::class, ".$seedAmount.")->create();";
                } else if ($mainVersion >= 8){
                    $seederFile = 'database/seeders/'.$model.'Seeder.php';
                    $contentToAdd = '        \App\Models\\'.$model.'::factory()->count('.$seedAmount.')->create();';
                }
                // Modify the content
                $content = file_get_contents(base_path($seederFile));
                $newContent = AppendContent($content,3,$contentToAdd);
                // Write to file
                file_put_contents(base_path($seederFile),str_replace($content,$newContent,file_get_contents(base_path($seederFile))));

                $console->comment('');
                $console->comment('Generated seeder.');
                $console->comment('');
            }
        }

        /**
         *
         * Generating default route
         *
         */

        $routeFile = 'routes/web.php';
        if ($mainVersion <= 7){
            $contentToAdd = "Route::resource('" . $prefix . "', '" . $model . "Controller');";
        } else if ($mainVersion >= 8){
            $contentToAdd = "Route::resource('" . $prefix . "', \App\Http\Controllers\\".$model."Controller::class);";
        }

        // Modify the content
        $content = file_get_contents(base_path($routeFile));
        $newContent = AppendContent($content,0,$contentToAdd);
        // Write to file
        file_put_contents(base_path($routeFile),str_replace($content,$newContent,file_get_contents(base_path($routeFile))));

        $console->comment('');
        $console->comment('Added route.');
        $console->comment('');

        /**
         *
         * Generating controller
         *
         */

        $controllerFile = 'app/Http/Controllers/' . $model . 'Controller.php';
        $modelRequestPath = '\\App\\Http\\Requests\\'.$model.'Request::class';

        if ($mainVersion <= 7){
            $modelPath = '\\App\\'.$model.'::class';
        } else if ($mainVersion >= 8){
            $modelPath = '\\App\\Models\\'.$model.'::class';
        }
        $modelImport = str_replace('::class','',$modelPath);
        
        // Modify the content
        $newContent = include __DIR__ . '/Code/Crud/Controller.php';
        // Write to file
        file_put_contents(base_path($controllerFile),$contentToAdd);

        $console->comment('');
        $console->comment('Generated controller.');
        $console->comment('');

        /**
         *
         * Generating form
         *
         */

        // Modify the content
        $newContent = include __DIR__ . '/Code/Crud/Form.php';
        // Write to file
        file_put_contents(base_path('app/Forms/' . $model . 'Form.php'),$contentToAdd);

        $console->comment('');
        $console->comment('Generated form.');
        $console->comment('');


        /**
         *
         * Generate migration
         *
         */

        $tab = '        ';
        $migEntries = '            ';
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

        $files = scandir(base_path().'/database/migrations', SCANDIR_SORT_DESCENDING);
        $migrationFile = '/database/migrations/'.$files[0];
        // Modify the content
        $content = file_get_contents(base_path($migrationFile));
        $newContent = WriteContent($content,18,$migEntries);
        // Write to file
        file_put_contents(base_path($migrationFile),str_replace($content,$newContent,file_get_contents(base_path($migrationFile))));

        $console->comment('');
        $console->comment('Configured migration.');
        $console->comment('');

        /**
         *
         * Generate model
         *
         */

        if ($mainVersion <= 7){
            $modelFile = 'app/' . $model . '.php';
            $relLine = 9;
        } else if ($mainVersion >= 8){
            $modelFile = 'app/Models/' . $model . '.php';
            $relLine = 11;
        }

        $relEntries = '';
        // Prepare relation content
        foreach ($columns as $name => $column){
            try {
                if (array_key_exists('foreign',$column)){
                        $relEntries .= include __DIR__ . '/../../Templates/Relation.php';;
                    }
                } catch (\Throwable $th) {
                    //
                }
        }

        $contentToAdd = $relEntries;
        // Modify the content
        $content = file_get_contents(base_path($modelFile));
        $newContent = WriteContent($content,$relLine,$contentToAdd);
        // Write to file
        file_put_contents(base_path($modelFile),str_replace($content,$newContent,file_get_contents(base_path($modelFile))));

        $console->comment('');
        $console->comment('Generated model.');
        $console->comment('');

        //configure request
        $requestFile = 'app/Http/Requests/'.$model.'Request.php';
        $reqEntries = '    ';
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

        $contentToAdd = include __DIR__ . '/../../Templates/Request.php';
        // Write to file
        file_put_contents(base_path($requestFile),$requestContent);

        $console->comment('');
        $console->comment('Generated custom request.');
        $console->comment('');

        $console->comment('');
        $console->comment('Generated full CRUD for: ' . $model . '.');
        $console->comment('');
    }
}
