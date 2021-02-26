<?php

namespace Pveltrop\DCMS\Classes;

use DirectoryIterator;
use Exception;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use Illuminate\Support\Facades\Artisan;

class Crud
{
    private $mainVersion;
    /**
     * @var string
     */
    private $modelPath;
    private $model;
    private $columns;
    /**
     * @var mixed
     */
    private $responses;
    private $amountToSeed;
    /**
     * @var mixed
     */
    private $views;
    private $prefix;
    private $jExcelColumns;
    private $jExcelResponses;

    /**
     * Find and return path to file
     *
     * @param string $name
     * @return mixed
     */
    public function findFile(string $name)
    {
        $rootFolders = [];
        $excludeDirs = array('.git', 'vendor', 'node_modules');

        // Make array with folders to search in
        $dir = new DirectoryIterator(base_path());
        foreach ($dir as $file) {
            if ($file->isDir() && !$file->isDot() && !in_array($file->getBasename(), $excludeDirs, true)) {
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

    public function versionSpecificVariables(): void
    {
        // Model path
        $this->modelPath = '';
        $this->modelsPath = '';
        if ($this->mainVersion >= 8 && is_dir(base_path().'/app/Models')) {
            $this->modelsPath = 'App\\Models\\';
            $this->modelPath = $this->modelsPath.$this->model;
        } else {
            $this->modelsPath = 'App\\';
            $this->modelPath = $this->modelsPath.$this->model;
        }
    }

    /**
     *
     * Generate factory file and fill it with code
     *
     */
    public function generateFactory(): void
    {
        $fakerEntries = '';
        $tab = '            ';

        if ($this->mainVersion <= 7) {
            $factoryLine = 10;
        } elseif ($this->mainVersion >= 8) {
            $factoryLine = 25;
        }

        foreach ($this->columns as $name => $column) {
            if (array_key_exists('seed', $column) && isset($column['seed']) && !isset($column['foreign'])) {
                $fakerEntries .= $tab.'"'.$name.'" => '.$column['seed'].','."\n";
            } elseif (isset($column['foreign'])) {
                $fakerEntries = '           "'.$name.'" => '.$this->modelPath.$column['class'].'::inRandomOrder()->first()->'.$column['value'].',';
            }
        }

        $factoryFile = ($this->findFile($this->model.'Factory.php')) ? $this->findFile($this->model.'Factory.php')->getPathname() : null;

        // File
        $contentToAdd = $fakerEntries;
        // Modify the content
        $content = file_get_contents($factoryFile);
        $newContent = WriteContent($content, $factoryLine, $contentToAdd);
        $newContent = str_replace($content, $newContent, file_get_contents($factoryFile));
        // Write to file
        file_put_contents($factoryFile, '');
        file_put_contents($factoryFile, $newContent);
    }

    /**
     * Generate seeder file, fill it with code and append the file in DatabaseSeeder.php
     *
     * @return void
     */
    public function generateSeed(): void
    {
        $enableSeed = false;
        foreach ($this->columns as $column) {
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

            $file = ($this->findFile('DatabaseSeeder.php')) ? $this->findFile('DatabaseSeeder.php')->getPathname() : null;
            $contentToAdd = '        $this->call(' . $this->model . 'Seeder::class);';

            $content = file_get_contents($file);
            $newContent = AppendContent($content, 2, $contentToAdd);
            $newContent = str_replace($content, $newContent, file_get_contents($file));
            // Write to file
            file_put_contents($file, '');
            file_put_contents($file, $newContent);

            /**
             *
             * Generate Seeder
             *
             */

            $seederFile = ($this->findFile($this->model.'Seeder.php')) ? $this->findFile($this->model.'Seeder.php')->getPathname() : null;
            if ($this->mainVersion <= 7) {
                $contentToAdd = "        factory(App\\".$this->model."::class, ".$this->amountToSeed.")->create();";
            } elseif ($this->mainVersion >= 8) {
                $contentToAdd = '        \\'.$this->modelPath.'::factory()->count('.$this->amountToSeed.')->create();';
            }
            // Modify the content
            $content = file_get_contents($seederFile);
            $newContent = AppendContent($content, 3, $contentToAdd);
            $newContent = str_replace($content, $newContent, file_get_contents($seederFile));
            // Write to file
            file_put_contents($seederFile, '');
            file_put_contents($seederFile, $newContent);
        }
    }

    /**
     * Append a resource route to web.php for the new model
     *
     * @return void
     */
    public function generateRoute(): void
    {
        $definedNameSpace = null;
        try {
            $routeProviderContent = ReflectClass(\App\Providers\RouteServiceProvider::class)->body;
            $routeNameSpaceCode = preg_match_all('/\$namespace[^\'"]*[\'"]([^\'"]*)[\'"]/m', $routeProviderContent, $matches);
            $definedNameSpace = rtrim($matches[1][0], '\\').'\\';
        } catch (Exception $e) {
            // continue
        }
        $routeNameSpace = ($definedNameSpace) ? '' : 'App\Http\Controllers';

        $routeFile = ($this->findFile('web.php')) ? $this->findFile('web.php')->getPathname() : null;
        if ($this->mainVersion <= 7) {
            $contentToAdd = "\nRoute::get('/{$this->prefix}/fetch', '{$this->model}Controller@fetch')->name('{$this->prefix}.fetch');";
            $contentToAdd .= "\nRoute::delete('/{$this->prefix}/multiple', '{$this->model}Controller@destroyMultiple')->name('{$this->prefix}.destroy.multiple');";
            $contentToAdd .= "\nRoute::resource('{$this->prefix}}', '{$this->model}Controller');";
        } elseif ($this->mainVersion >= 8) {
            $contentToAdd = "Route::get('/{$this->prefix}/fetch', [{$routeNameSpace}{$this->model}Controller::class, 'fetch'])->name('{$this->prefix}.fetch');";
            $contentToAdd .= "\nRoute::delete('/{$this->prefix}/multiple', [{$routeNameSpace}{$this->model}Controller::class, 'destroyMultiple'])->name('{$this->prefix}.destroy.multiple');";
            $contentToAdd .= "\nRoute::resource('{$this->prefix }', ".$routeNameSpace.$this->model."Controller::class);";
        }

        // Modify the content
        $content = file_get_contents($routeFile);
        $newContent = AppendContent($content, 0, $contentToAdd);
        $newContent = str_replace($content, $newContent, file_get_contents($routeFile));
        // Write to file
        file_put_contents($routeFile, '');
        file_put_contents($routeFile, $newContent);
    }

    /**
     * Generate controller file, and fill it with code
     *
     * @return void
     */
    public function generateController(): void
    {
        $controllerFile = ($this->findFile($this->model . 'Controller.php')) ? $this->findFile($this->model . 'Controller.php')->getPathname() : null;

        if ($this->mainVersion >= 8 && is_dir(base_path().'/app/Models')) {
            $nameSpace = 'App\\Models\\';
        } else {
            $nameSpace = 'App\\';
        }

        // CRUD responses
        $responseStr = '';
        foreach ($this->responses as $groupName => $responseGroup) {
            $responseGrp = '';
            foreach ($responseGroup as $key => $value) {
                $responseGrp .= "\n                ".'"'.$key.'" => '.($key === 'message' ? '__("'.$value.'")' : '"'.$value.'"').',';
            }
            $responseStr .= "\n            ".'"'.$groupName.'" => ['. $responseGrp .'
            ],';
        }

        // jExcel columns
        $jExcelColumnsStr = '';
        $x = 0;
        if (is_countable($this->jExcelColumns) && count($this->jExcelColumns) > 0){
            foreach ($this->jExcelColumns as $key => $column) {
                $jExcelColumnsStr .= "\n                ".'"'.$key.'" => '.$x.',';
                $x++;
            }
        }

        // jExcel autocorrect
        $jExcelCorrectStr = '';
        $jExcelGrp = '';
        $controllerImports = '';
        $x = 0;
        if (is_countable($this->jExcelColumns) && count($this->jExcelColumns) > 0) {
            foreach ($this->jExcelColumns as $key => $jExcelColumn) {
                if (isset($this->columns[$jExcelColumn['name']]['foreign'])) {
                    $controllerImports .= 'Use '.$nameSpace.$this->columns[$jExcelColumn['name']]['class'].';';
                    $jExcelGrp = '';
                    $jExcelGrp .= "\n                    ".'"column" => "'.$x.'",';
                    $jExcelGrp .= "\n                    ".'"class" => '.$this->columns[$jExcelColumn['name']]['class'].'::class,';
                    $jExcelGrp .= "\n                    ".'"searchAttributes" => [
                        "'.$jExcelColumn['text'].'"
                    ],';
                    $jExcelGrp .= "\n                    ".'"returnAttribute" => "'.$this->columns[$jExcelColumn['name']]['value'].'",';
                }
                if ($jExcelGrp !== '') {
                    $jExcelCorrectStr .= "\n                ".'"'.$jExcelColumn['name'].'" => ['. $jExcelGrp .'
                ],';
                }
                $x++;
            }
        }

        // jExcel responses
        $jExcelResponseStr = '';
        if (is_countable($this->jExcelResponses) && count($this->jExcelResponses) > 0) {
            foreach ($this->jExcelResponses as $groupName => $jExcelResponseGroup) {
                $jExcelResponseGrp = '';
                foreach ($jExcelResponseGroup as $key => $value) {
                    $jExcelResponseGrp .= "\n                    ".'"'.$key.'" => '.($key === 'message' || $key === 'title' ? '__("'.$value.'")' : '"'.$value.'"').',';
                }
                $jExcelResponseStr .= "\n                ".'"'.$groupName.'" => ['. $jExcelResponseGrp .'
                ],';
            }
        }

        $jExcelEntries = ($this->jExcelColumns) ? include __DIR__ . '/../Templates/Crud/Controller/jExcel.php' : '';

        // Views
        $viewStr = '';
        foreach ($this->views as $key => $value) {
            $viewStr .= "\n            ".'"'.$key.'" => "'.$value.'",';
        }

        $modelRequest = $this->model.'Request::class';

        // Modify the content
        $newContent = include __DIR__ . '/../Templates/Crud/Controller/Controller.php';
        // Write to file
        file_put_contents($controllerFile, '');
        file_put_contents($controllerFile, $newContent);
    }

    /**
     * Generate Form class, which can be used to easily render a HTML form by using the $form variable
     *
     * @return void
     */
    public function generateForm(): void
    {
        // Form fields
        $formFieldsStr = "";
        $formImports = "";

        foreach ($this->columns as $columnName => $column) {
            $columnStr = "";

            $carouselStr = "";
            $labelStr = "";
            $optionsStr = "";
            $inputStr = "";
            $inputGroup = "";
            $smallStr = "";

            $formInputType = "input";

            /**
             * Optional carousel properties
             */

            if ($column['inputDataType'] === 'filepond') {
                $carouselStr .= "\n".'                "carousel" => ['."\n".'                    "height" => "200px"'."\n".'                ],';
                $inputGroup .= $carouselStr;
            }

            /**
            * Label properties
            */

            $labelProps = "\n".'                    "text" => __("'.$column['title'].'")';
            // Final label strings
            $labelStr .= "\n".'                "label" => ['.$labelProps."\n".'                ],';
            $inputGroup .= $labelStr;

            /**
             * Input properties
             */

            switch ($column['inputDataType']) {
                case 'datetimepicker':
                    $column['type'] = 'text';
                    break;
            }

            $inputProps = "\n".'                    "type" => "'.$column['inputType'].'",';
            $inputProps .= "\n".'                    "data-type" => "'.$column['inputDataType'].'",';
            // If generating a filepond element
            if ($column['inputDataType'] === 'filepond') {
                $inputProps .= "\n".'                    "data-filepond-prefix" => "'.$this->prefix.'",';
                $inputProps .= "\n".'                    "data-filepond-mime" => "'.$column['filePondMime'].'",';
            }
            // If generating a dropdown for relation
            if ($column['inputType'] === 'select') {
                $formImports .= "use ".$this->modelsPath."".$column['class'].";\n";
                $optionsStr = "\n".'                        "data" => '.$column['class'].'::all(),';
                $optionsStr .= "\n".'                        "value" => "'.$column['value'].'",';
                $optionsStr .= "\n".'                        "text" => "'.$column['text'].'",';
                $optionsStr .= "\n".'                        "foreignKey" => "'.$column['name'].'",';
                $inputProps .= "\n".'                    "multiple" => false,';
                $inputProps .= "\n".'                    "options" => ['.$optionsStr."\n".'                    ],';
            }
            // If generating a checkbox or radio element
            if ($column['inputType'] === 'checkbox') {
                $optionsStr .= "\n"."                        // DCMS creates an invisible checkbox with value 0 automatically";
                $optionsStr .= "\n".'                        "text" => __("Yes"),';
                $optionsStr .= "\n".'                        "value" => 1,';
                $inputProps .= "\n".'                    ['.$optionsStr."\n".'                    ],';
            }
            // If generating a checkbox or radio element
            if ($column['inputType'] === 'radio') {
                for ($i=0; $i < 3; $i++) {
                    $optionsStr .= "\n".'                        "text" => __("Yes"),';
                    $optionsStr .= "\n".'                        "value" => 1,';
                    $inputProps .= "\n".'                    ['.$optionsStr."\n".'                    ],';
                }
            }

            /**
             * Final entry
             */
            switch ($column['inputType']) {
                case 'select':
                    $formInputType = 'select';
                    break;
                case 'checkbox':
                    $formInputType = 'checkbox';
                    break;
                case 'radio':
                    $formInputType = 'radio';
                    break;
                case 'textarea':
                    $formInputType = 'textarea';
                    break;
                default:
                    $formInputType = $column['inputType'];
                    break;
            }

            $inputStr .= "\n".'                "'.$formInputType.'" => ['.$inputProps."\n".'                ],';
            $inputGroup .= $inputStr;

            // Small text for extra info
            $smallStr .= "\n".'                "small" => __("Extra information.")'.",";
            $inputGroup .= $smallStr;

            $columnStr .= "\n".'            "'.$columnName.'" => ['.$inputGroup."\n".'            ],';

            $formFieldsStr .= $columnStr;
        }

        // Modify the content
        $newContent = include __DIR__ . '/../Templates/Crud/Form.php';
        // Write to file
        MakeDir(base_path().'/app/Forms');
        file_put_contents(base_path('app/Forms/' . $this->model . 'Form.php'), '');
        file_put_contents(base_path('app/Forms/' . $this->model . 'Form.php'), $newContent);
    }

    /**
     * Generate migration file and fill it with code
     *
     * @return void
     */
    public function generateMigration(): void
    {
        $migEntries = '            ';
        foreach ($this->columns as $name => $column) {
            $rowNullable = (isset($column['nullable']) && $column['nullable'] === 1) ? '->nullable()' : '';
            $migEntries .= '$table->'.$column['dataType'].'("'.$column['name'].'")'.$rowNullable.';'."\n".'            ';
            if (array_key_exists('foreign', $column)) {
                $onUpdate = $column['onUpdate'];
                $onUpdate = '->onUpdate("'.$onUpdate.'")';
                $onDelete = $column['onDelete'];
                $onDelete = '->onDelete("'.$onDelete.'")';
                $migEntries .= '$table->foreign("'.$column['name'].'")->references("'.$column['value'].'")->on("'.$column['table'].'")'.$onUpdate.$onDelete.';'."\n".'            ';
            }
        }

        $files = scandir(base_path().'/database/migrations', SCANDIR_SORT_DESCENDING);
        $migrationFile = '/database/migrations/'.$files[0];
        // Modify the content
        $content = file_get_contents(base_path($migrationFile));
        $newContent = WriteContent($content, 18, $migEntries);
        $newContent = str_replace($content, $newContent, file_get_contents(base_path($migrationFile)));
        // Write to file
        file_put_contents(base_path($migrationFile), '');
        file_put_contents(base_path($migrationFile), $newContent);
    }

    /**
     * Generate model class file
     *
     * @return void
     */
    public function generateModel(): void
    {
        $modelFile = ($this->findFile($this->model . '.php')) ? $this->findFile($this->model . '.php')->getPathname() : null;
        if ($this->mainVersion <= 7) {
            $relLine = 9;
        } elseif ($this->mainVersion >= 8) {
            $relLine = 11;
        }

        $relEntries = '';
        // Prepare relation content
        foreach ($this->columns as $name => $column) {
            if (array_key_exists('foreign', $column)) {
                $relEntries .= include __DIR__ . '/../Templates/Crud/Relation.php';
            }
        }

        $contentToAdd = $relEntries;
        // Modify the content
        $content = file_get_contents($modelFile);
        $newContent = WriteContent($content, $relLine, $contentToAdd);
        $newContent = str_replace($content, $newContent, file_get_contents($modelFile));
        // Write to file
        file_put_contents($modelFile, '');
        file_put_contents($modelFile, $newContent);
    }

    /**
     * Generate request file and fill it with the predefined validation rules
     *
     * @return void
     */
    public function generateRequest(): void
    {
        $requestFile = ($this->findFile($this->model.'Request.php')) ? $this->findFile($this->model.'Request.php')->getPathname() : null;
        $reqEntries = '    ';
        foreach ($this->columns as $column) {
            if (array_key_exists('rules', $column)) {
                $rules = (isset($column['nullable']) && $column['nullable'] === 1) ? '"nullable", ' : '';
                $rules .= (isset($column['required']) && $column['required'] === 1) ? '"required", ' : '';
                foreach ($column['rules'] as $x => $rule) {
                    $rules .= '"'.$rule.'", ';
                }
                $ruleRow = '"'.$column["name"].'" => ['.$rules.']';
                $reqEntries .= $ruleRow.',
            ';
            }
        }

        $contentToAdd = include __DIR__ . '/../Templates/Crud/Request.php';
        // Write to file
        file_put_contents($requestFile, '');
        file_put_contents($requestFile, $contentToAdd);
    }

    /**
     * Generate Laravel files and automatically fill them with code
     *
     * @param $data
     * @return void
     */

    public function generate($data): void
    {
        $this->mainVersion = app()->version()[0];
        $this->model = $data['name'];
        $this->columns = $data['columns'];
        $this->responses = $data['responses'];
        $this->views = $data['views'];
        $this->prefix = strtolower($this->model);
        $this->amountToSeed = $data['amountToSeed'];
        $this->jExcelColumns = $data['jExcelColumns'] ?? null;
        $this->jExcelResponses = $data['jExcelResponses'] ?? null;

        // Create the basic Laravel files (model, migration, controller, factory, seeder, request)
        Artisan::call('make:model', [
            'name' => $this->model,
            '-c' => true,
            '-m' => true,
            '-f' => true,
            '-s' => true,
        ]);
        Artisan::call('make:request', [
            'name' => $this->model.'Request'
        ]);

        $this->versionSpecificVariables();
        $this->generateSeed();
        $this->generateFactory();
        $this->generateRoute();
        $this->generateController();
        $this->generateForm();
        $this->generateMigration();
        $this->generateModel();
        $this->generateRequest();
    }
}
