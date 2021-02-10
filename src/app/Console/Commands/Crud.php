<?php

namespace Pveltrop\DCMS\Console\Commands;

use DirectoryIterator;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use Illuminate\Console\Command;

class Crud extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */

    protected $signature = 'dcms:make-crud {model?}';

    /**
     * The console command description.
     *
     * @var string
     */

    protected $description = 'Generate full crud functionality.';

    /**
     * Find and return path to file
     *
     * @param string $name
     * @return void
     */
    public function findFile($name)
    {
        $rootFolders = [];
        $excludeDirs = array('.git', 'vendor', 'node_modules');

        // Make array with folders to search in
        $dir = new DirectoryIterator(base_path());
        foreach ($dir as $file) {
            if ($file->isDir() && !$file->isDot() && !in_array($file->getBasename(), $excludeDirs)) {
                $rootFolders[] = $file->getPathname();
            }
        }

        // Loop through array with folders
        foreach ($rootFolders as $key => $rootFolder) {
            $it = new RecursiveDirectoryIterator($rootFolder);
            foreach (new RecursiveIteratorIterator($it) as $file) {
                if (preg_match('~'.$name.'~', $file)) {
                    return $file;
                }
            }
        }
    }

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

        $crudFile = ($this->findFile('DCMSCrud.php')) ? $this->findFile('DCMSCrud.php')->getPathname() : null;

        if ($crudFile) {
            $initManual = false;
            try {
                include($crudFile);
            } catch (\Exception $e) {
                $console->comment('');
                $console->error('File not found: DCMSCrud.php');
                $console->comment('Initalising manual creation in two seconds.');
                $console->comment('');
                $initManual = true;
                sleep(2);
            }
            if ($initManual == false) {
                $fileContent = file_get_contents($crudFile);
                $fileModel = preg_match('/\$model(\s|=)/m', $fileContent) == 1;
                $filePrefix = preg_match('/\$prefix(\s|=)/m', $fileContent) == 1;
                $fileColumns = preg_match('/\$columns(\s|=)/m', $fileContent) == 1;
                if (!$fileModel) {
                    $console->comment('');
                    $console->error('$model not defined in PHP file.');
                    $console->comment('');
                    $initManual = true;
                }
                if (!$filePrefix) {
                    $console->comment('');
                    $console->error('$prefix not defined in PHP file.');
                    $console->comment('');
                    $initManual = true;
                }
                if (!$fileColumns) {
                    $console->comment('');
                    $console->error('$columns not defined in PHP file.');
                    $console->comment('');
                    $initManual = true;
                }
            }
        } else {
            $columns = [];
            $model = $console->argument('model');
            $prefix = strtolower($model);
        }

        if ($initManual) {
            if (!$console->confirm('Couldn\'t find file with defined attributes. Do you want to proceed? ')) {
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
        if ($mainVersion <= 7) {
            $modelPath = 'App\\'.$model;
        } elseif ($mainVersion >= 8) {
            $modelPath = 'App\\Models\\'.$model;
        }

        $console->comment('');
        $console->comment('Created model, migration, request, factory, seeder and controller for: ' . $model . '.');
        $console->comment('');

        if ($initManual) {

            /**
             *
             * Generate columns/fields with user input
             *
             */

            if ($console->confirm('Do you want to generate the columns for this model?')) {
                function GenerateColumn($console, &$columns, $mainVersion)
                {
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

                    if ($console->confirm('Is this column related to another column?')) {
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
                    if ($console->confirm('Do you want to write the validation rules for: '.$dbColumn.'?')) {
                        NewRule:

                        $console->line('Examples:');
                        $console->line('regex:/foobar/');
                        $console->line('min:1');
                        $console->line('required');
                        $console->line('Press enter to finish adding rules.');
                        $newRule = $console->ask('Type the new rule');

                        array_push($validationRules, $newRule);

                        if ($console->confirm('Add another rule?')) {
                            goto NewRule;
                        }

                        $column['validation'] = $validationRules;
                    }
                    if ($console->confirm('Do you want to seed: '.$dbColumn.' with a factory?')) {
                        if ($mainVersion <= 7) {
                            $fakerExample = '$faker->word()';
                        } elseif ($mainVersion >= 8) {
                            $fakerExample = '$this->faker->word()';
                        }
                        $column['seed']['value'] = $console->ask('Enter the data to seed. (For example: '.$fakerExample.', or "Seed this sentence").');
                    }

                    $columns[$dbColumn] = $column;
                }

                NewColumn:
                if ($console->confirm('Do you want to generate a new column?')) {
                    GenerateColumn($console, $columns, $mainVersion);
                    goto NewColumn;
                }
            }
        }

        $enableSeed = false;
        foreach ($columns as $column) {
            if (isset($column['seed'])) {
                $enableSeed = true;
            }
        }

        if ($enableSeed) {

            /**
             *
             * Add entry to DatabaseSeeder
             *
             */

            $seedAmount = $amountToSeed ?? $console->ask('How many objects should be seeded through factories? Enter a number.');

            $file = ($this->findFile('DatabaseSeeder.php')) ? $this->findFile('DatabaseSeeder.php')->getPathname() : null;
            $contentToAdd = '        $this->call(' . $model . 'Seeder::class);';

            // Modify the content
            try {
                $content = file_get_contents($file);
                $newContent = AppendContent($content, 2, $contentToAdd);
                // Write to file
                file_put_contents($file, str_replace($content, $newContent, file_get_contents($file)));
            } catch (\Throwable $th) {
                $console->error("Couldn't write to DatabaseSeeder. Folder structure might be incorrect.");
            }

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

            foreach ($columns as $name => $column) {
                if (array_key_exists('seed', $column)) {
                    $fakerEntries .= $tab.'"'.$name.'" => '.$column['seed'].','."\n";
                }
            }

            $factoryFile = ($this->findFile($model.'Factory.php')) ? $this->findFile($model.'Factory.php')->getPathname() : null;
            if ($mainVersion <= 7) {
                $factoryLine = 10;
            } elseif ($mainVersion >= 8) {
                $factoryLine = 25;
            }

            try {
                // File
                $contentToAdd = $fakerEntries;
                // Modify the content
                $content = file_get_contents($factoryFile);
                $newContent = WriteContent($content, $factoryLine, $contentToAdd);
                // Write to file
                file_put_contents($factoryFile, str_replace($content, $newContent, file_get_contents($factoryFile)));
            } catch (\Throwable $th) {
                $console->error("Couldn't write Factory. Folder structure might be incorrect.");
            }

            $console->comment('');
            $console->comment('Generated factory.');
            $console->comment('');

            if ($makeSeeder) {

                /**
                 *
                 * Generate Seeder
                 *
                 */

                $seederFile = ($this->findFile($model.'Seeder.php')) ? $this->findFile($model.'Seeder.php')->getPathname() : null;
                if ($mainVersion <= 7) {
                    $contentToAdd = "        factory(App\\".$model."::class, ".$seedAmount.")->create();";
                } elseif ($mainVersion >= 8) {
                    $contentToAdd = '        \App\Models\\'.$model.'::factory()->count('.$seedAmount.')->create();';
                }
                try {
                    // Modify the content
                    $content = file_get_contents($seederFile);
                    $newContent = AppendContent($content, 3, $contentToAdd);
                    // Write to file
                    file_put_contents($seederFile, str_replace($content, $newContent, file_get_contents($seederFile)));
                } catch (\Throwable $th) {
                    $console->error("Couldn't write Seeder. Folder structure might be incorrect.");
                }

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

        $routeFile = ($this->findFile('web.php')) ? $this->findFile('web.php')->getPathname() : null;
        if ($mainVersion <= 7) {
            $contentToAdd = "Route::resource('" . $prefix . "', '" . $model . "Controller');";
        } elseif ($mainVersion >= 8) {
            $contentToAdd = "Route::resource('" . $prefix . "', \App\Http\Controllers\\".$model."Controller::class);";
        }

        try {
            // Modify the content
            $content = file_get_contents($routeFile);
            $newContent = AppendContent($content, 0, $contentToAdd);
            // Write to file
            file_put_contents($routeFile, str_replace($content, $newContent, file_get_contents($routeFile)));
        } catch (\Throwable $th) {
            $console->error("Couldn't write to routes. Folder structure might be incorrect.");
        }

        $console->comment('');
        $console->comment('Added route.');
        $console->comment('');

        /**
         *
         * Generating controller
         *
         */

        $controllerFile = ($this->findFile($model . 'Controller.php')) ? $this->findFile($model . 'Controller.php')->getPathname() : null;
        
        if ($mainVersion >= 8 && is_dir(base_path().'/app/Models')) {
            $modelPath = '\\App\\Models\\'.$model.'::class';
        } else {
            $modelPath = '\\App\\'.$model.'::class';
        }

        $modelRequestPath = '\\App\\Http\\Requests\\'.$model.'Request::class';
        $modelImport = str_replace('::class', '', $modelPath);
        
        // Modify the content
        $newContent = include __DIR__ . '/Code/Crud/Controller.php';
        // Write to file
        try {
            file_put_contents($controllerFile, $newContent);
        } catch (\Throwable $th) {
            $console->error("Couldn't write Controller. Folder structure might be incorrect.");
        }

        $console->comment('');
        $console->comment('Generated controller.');
        $console->comment('');

        /**
         *
         * Generating form
         *
         */

        // Modify the content
        $newContent = include __DIR__ . '/Code/Form/Class.php';
        // Write to file
        try {
            file_put_contents(base_path('app/Forms/' . $model . 'Form.php'), $newContent);
        } catch (\Throwable $th) {
            $console->error("Couldn't write Form. Folder structure might be incorrect.");
        }

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
        foreach ($columns as $name => $column) {
            $rowNullable = ($column['attributes']['nullable'] == 1) ? '->nullable()' : '';
            $rowUnsigned = ($column['attributes']['unsigned'] == 1) ? '->unsigned()' : '';
            $migEntries .= '$table->'.$column['attributes']['type'].'("'.$column['attributes']['name'].'")'.$rowNullable.$rowUnsigned.';'."\n".'            ';
            if (array_key_exists('foreign', $column)) {
                $console->info('Generating migration row for relation from: '.$column['foreign']['foreign_column'].' to '.$column['foreign']['table'].': '.$column['foreign']['references']);
                $onUpdate = $column['foreign']['onUpdate'] ?? $console->ask('What to do on update? (cascade, no action, restrict, set null)');
                $onUpdate = '->onUpdate("'.$onUpdate.'")';
                $onDelete = $column['foreign']['onDelete'] ?? ($console->ask('What to do on delete? (cascade, no action, restrict, set null)'));
                $onDelete = '->onDelete("'.$onDelete.'")';
                $migEntries .= '$table->foreign("'.$column['foreign']['foreign_column'].'")->references("'.$column['foreign']['references'].'")->on("'.$column['foreign']['table'].'")'.$onUpdate.$onDelete.';'."\n".'            ';
            }
        }

        try {
            $files = scandir(base_path().'/database/migrations', SCANDIR_SORT_DESCENDING);
            $migrationFile = '/database/migrations/'.$files[0];
            // Modify the content
            $content = file_get_contents(base_path($migrationFile));
            $newContent = WriteContent($content, 18, $migEntries);
            // Write to file
            file_put_contents(base_path($migrationFile), str_replace($content, $newContent, file_get_contents(base_path($migrationFile))));
        } catch (\Throwable $th) {
            $console->error("Couldn't write Migration. Folder structure might be incorrect.");
        }

        $console->comment('');
        $console->comment('Configured migration.');
        $console->comment('');

        /**
         *
         * Generate model
         *
         */

        $modelFile = ($this->findFile($model . '.php')) ? $this->findFile($model . '.php')->getPathname() : null;
        if ($mainVersion <= 7) {
            $relLine = 9;
        } elseif ($mainVersion >= 8) {
            $relLine = 11;
        }

        $relEntries = '';
        // Prepare relation content
        foreach ($columns as $name => $column) {
            try {
                if (array_key_exists('foreign', $column)) {
                    $relEntries .= include __DIR__ . '/Code/Crud/Relation.php';
                }
            } catch (\Throwable $th) {
                //
            }
        }

        try {
            $contentToAdd = $relEntries;
            // Modify the content
            $content = file_get_contents($modelFile);
            $newContent = WriteContent($content, $relLine, $contentToAdd);
            // Write to file
            file_put_contents($modelFile, str_replace($content, $newContent, file_get_contents($modelFile)));
        } catch (\Throwable $th) {
            $console->error("Couldn't write to Model. Folder structure might be incorrect.");
        }

        $console->comment('');
        $console->comment('Generated model.');
        $console->comment('');

        //configure request
        $requestFile = ($this->findFile($model.'Request.php')) ? $this->findFile($model.'Request.php')->getPathname() : null;
        $reqEntries = '    ';
        foreach ($columns as $column) {
            if (array_key_exists('validation', $column)) {
                $ruleRow = '';
                $rules = ($column['attributes']['nullable'] == 1) ? '"nullable", ' : '';
                $rules = ($column['attributes']['required'] == 1) ? '"required", ' : '';
                foreach ($column['validation'] as $x => $rule) {
                    $rules .= '"'.$rule.'", ';
                }
                $ruleRow = '"'.$column["attributes"]["name"].'" => ['.$rules.']';
                $reqEntries .= $ruleRow.',
            ';
            }
        }

        $contentToAdd = include __DIR__ . '/Code/Crud/Request.php';
        // Write to file
        file_put_contents($requestFile, $contentToAdd);

        $console->comment('');
        $console->comment('Generated custom request.');
        $console->comment('');

        $console->comment('');
        $console->comment('Generated full CRUD for: ' . $model . '.');
        $console->comment('');
    }
}
