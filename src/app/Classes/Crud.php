<?php

namespace Pveltrop\DCMS\Classes;

use DirectoryIterator;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use Illuminate\Support\Facades\Artisan;

class Crud
{
    /**
     * Find and return path to file
     *
     * @param string $name
     * @return void
     */
    public function findFile($name){
        $rootFolders = [];
        $excludeDirs = array('.git', 'vendor', 'node_modules');

        // Make array with folders to search in
        $dir = new DirectoryIterator(base_path());
        foreach ($dir as $file) {
            if ($file->isDir() && !$file->isDot() && !in_array($file->getBasename(),$excludeDirs)) {
                $rootFolders[] = $file->getPathname();
            }
        }

        // Loop through array with folders
        foreach ($rootFolders as $key => $rootFolder) {
            $it = new RecursiveDirectoryIterator($rootFolder);
            foreach(new RecursiveIteratorIterator($it) as $file) {
                if(preg_match('~'.$name.'~',$file)){
                    return $file;
                }
            }
        }
    }

    public function versionSpecificVariables()
    {
        // Model path
        $this->modelPath = '';
        if ($this->mainVersion >= 8 && is_dir(base_path().'/app/Models')){
            $this->modelPath = 'App\\Models\\'.$this->model;
        } else {
            $this->modelPath = 'App\\'.$this->model;
        }
    }

    /**
     *
     * Generate factory file and fill it with code
     *
     */
    public function generateFactory()
    {

        $fakerEntries = '';
        $tab = '            ';

        if ($this->mainVersion >= 8 && is_dir(base_path().'/app/Models')){
            $modelPath = '\\App\\Models\\';
        } else {
            $modelPath = '\\App\\';
        }

        if ($this->mainVersion <= 7){
            $factoryLine = 10;
        } else if ($this->mainVersion >= 8){
            $factoryLine = 25;
        }

        foreach ($this->columns as $name => $column){
            if (array_key_exists('seed',$column) && isset($column['seed']) && !isset($column['foreign'])){
                $fakerEntries .= $tab.'"'.$name.'" => '.$column['seed'].','."\n";
            } else if (isset($column['foreign'])) {
                $fakerEntries = '           "'.$name.'" => '.$modelPath.$column['foreign']['class'].'::inRandomOrder()->first()->'.$column['foreign']['references'].',';
            }
        }

        $factoryFile = ($this->findFile($this->model.'Factory.php')) ? $this->findFile($this->model.'Factory.php')->getPathname() : null;

        // File
        $contentToAdd = $fakerEntries;
        // Modify the content
        $content = file_get_contents($factoryFile);
        $newContent = WriteContent($content,$factoryLine,$contentToAdd);
        // Write to file
        file_put_contents($factoryFile,str_replace($content,$newContent,file_get_contents($factoryFile)));
    }

    /**
     * Generate seeder file, fill it with code and append the file in DatabaseSeeder.php
     *
     * @return void
     */
    public function generateSeed()
    {
        $this->enableSeed = false;
        foreach($this->columns as $column){
            if (isset($column['seed'])){
                $this->enableSeed = true;
            }
        }

        if($this->enableSeed){

            /**
             *
             * Add entry to DatabaseSeeder
             *
             */

            $file = ($this->findFile('DatabaseSeeder.php')) ? $this->findFile('DatabaseSeeder.php')->getPathname() : null;
            $contentToAdd = '        $this->call(' . $this->model . 'Seeder::class);';

            $content = file_get_contents($file);
            $newContent = AppendContent($content,2,$contentToAdd);
            // Write to file
            file_put_contents($file,str_replace($content,$newContent,file_get_contents($file)));

            /**
             *
             * Generate Seeder
             *
             */

            $seederFile = ($this->findFile($this->model.'Seeder.php')) ? $this->findFile($this->model.'Seeder.php')->getPathname() : null;
            if ($this->mainVersion <= 7){
                $contentToAdd = "        factory(App\\".$this->model."::class, ".$this->amountToSeed.")->create();";
            } else if ($this->mainVersion >= 8){
                $contentToAdd = '        \App\Models\\'.$this->model.'::factory()->count('.$this->amountToSeed.')->create();';
            }
            // Modify the content
            $content = file_get_contents($seederFile);
            $newContent = AppendContent($content,3,$contentToAdd);
            // Write to file
            file_put_contents($seederFile,str_replace($content,$newContent,file_get_contents($seederFile)));
        }
    }

    /**
     * Append a resource route to web.php for the new model
     *
     * @return void
     */
    public function generateRoute()
    {
        $routeFile = ($this->findFile('web.php')) ? $this->findFile('web.php')->getPathname() : null;
        if ($this->mainVersion <= 7){
            $contentToAdd = "Route::resource('" . $this->prefix . "', '" . $this->model . "Controller');";
        } else if ($this->mainVersion >= 8){
            $contentToAdd = "Route::resource('" . $this->prefix . "', \App\Http\Controllers\\".$this->model."Controller::class);";
        }

        // Modify the content
        $content = file_get_contents($routeFile);
        $newContent = AppendContent($content,0,$contentToAdd);
        // Write to file
        file_put_contents($routeFile,str_replace($content,$newContent,file_get_contents($routeFile)));
    }

    /**
     * Generate controller file, and fill it with code
     *
     * @return void
     */
    public function generateController()
    {
        $controllerFile = ($this->findFile($this->model . 'Controller.php')) ? $this->findFile($this->model . 'Controller.php')->getPathname() : null;
        
        if ($this->mainVersion >= 8 && is_dir(base_path().'/app/Models')){
            $nameSpace = 'App\\Models\\';
        } else {
            $nameSpace = 'App\\';
        }

        // CRUD responses
        $this->responseStr = '';
        foreach ($this->responses as $groupName => $responseGroup) {
            $this->responseGrp = '';
            foreach ($responseGroup as $key => $value) {
                $this->responseGrp .= "\n                ".'"'.$key.'" => '.($key == 'message' ? '__("'.$value.'")' : '"'.$value.'"').',';
            }
            $this->responseStr .= "\n            ".'"'.$groupName.'" => ['.$this->responseGrp.'
            ],';
        }

        // jExcel columns
        $this->jExcelColumnsStr = '';
        $x = 0;
        foreach ($this->jExcelColumns as $key => $column) {
            $this->jExcelColumnsStr .= "\n                ".'"'.$key.'" => '.$x.',';
            $x++;
        }

        // jExcel autocorrect
        $this->jExcelCorrectStr = '';
        $this->jExcelGrp = '';
        $this->controllerImports = '';
        $x = 0;
        foreach ($this->jExcelColumns as $key => $jExcelColumn) {
            if (isset($this->columns[$jExcelColumn['name']]['foreign'])){
                $this->controllerImports .= 'Use '.$nameSpace.$this->columns[$jExcelColumn['name']]['foreign']['class'].';';
                $this->jExcelGrp = '';
                $this->jExcelGrp .= "\n                    ".'"column" => "'.$x.'",';
                $this->jExcelGrp .= "\n                    ".'"class" => '.$this->columns[$jExcelColumn['name']]['foreign']['class'].'::class,';
                $this->jExcelGrp .= "\n                    ".'"searchAttributes" => [
                        "'.$jExcelColumn['text'].'"
                    ],';
                $this->jExcelGrp .= "\n                    ".'"returnAttribute" => "'.$this->columns[$jExcelColumn['name']]['foreign']['references'].'",';
            }
            if ($this->jExcelGrp !== ''){
                $this->jExcelCorrectStr .= "\n                ".'"'.$jExcelColumn['name'].'" => ['.$this->jExcelGrp.'
                ],';
            }
            $x++;
        }

        // jExcel responses
        $this->jExcelResponseStr = '';
        foreach ($this->jExcelResponses as $groupName => $jExcelResponseGroup) {
            $this->jExcelResponseGrp = '';
            foreach ($jExcelResponseGroup as $key => $value) {
                $this->jExcelResponseGrp .= "\n                    ".'"'.$key.'" => '.($key == 'message' || $key == 'title' ? '__("'.$value.'")' : '"'.$value.'"').',';
            }
            $this->jExcelResponseStr .= "\n                ".'"'.$groupName.'" => ['.$this->jExcelResponseGrp.'
                ],';
        }

        // Views
        $this->viewStr = '';
        foreach ($this->views as $key => $value) {
            $this->viewStr .= "\n            ".'"'.$key.'" => "'.$value.'",';
        }

        $this->modelRequest = $this->model.'Request::class';
        
        // Modify the content
        $newContent = include __DIR__ . '/../Templates/Crud/Controller.php';
        // Write to file
        file_put_contents($controllerFile,$newContent);
    }

    /**
     * Generate Form class, which can be used to easily render a HTML form from a variable
     *
     * @return void
     */
    public function generateForm()
    {
        // Modify the content
        $newContent = include __DIR__ . '/../Templates/Form/Class.php';
        // Write to file
        file_put_contents(base_path('app/Forms/' . $this->model . 'Form.php'),$newContent);
    }

    /**
     * Generate migration file and fill it with code
     *
     * @return void
     */
    public function generateMigration()
    {
        $tab = '        ';
        $migEntries = '            ';
        foreach ($this->columns as $name => $column){
            $rowNullable = ($column['attributes']['nullable'] == 1) ? '->nullable()' : '';
            $migEntries .= '$table->'.$column['attributes']['type'].'("'.$column['attributes']['name'].'")'.$rowNullable.';'."\n".'            ';
            if (array_key_exists('foreign',$column)){
                $onUpdate = $column['foreign']['onUpdate'];
                $onUpdate = '->onUpdate("'.$onUpdate.'")';
                $onDelete = $column['foreign']['onDelete'];
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
    }

    /**
     * Generate model class file
     *
     * @return void
     */
    public function generateModel()
    {
        $modelFile = ($this->findFile($this->model . '.php')) ? $this->findFile($this->model . '.php')->getPathname() : null;
        if ($this->mainVersion <= 7){
            $relLine = 9;
        } else if ($this->mainVersion >= 8){
            $relLine = 11;
        }

        $relEntries = '';
        // Prepare relation content
        foreach ($this->columns as $name => $column){
            if (array_key_exists('foreign',$column)){
                $relEntries .= include __DIR__ . '/../Templates/Crud/Relation.php';
            }
        }

        $contentToAdd = $relEntries;
        // Modify the content
        $content = file_get_contents($modelFile);
        $newContent = WriteContent($content,$relLine,$contentToAdd);
        // Write to file
        file_put_contents($modelFile,str_replace($content,$newContent,file_get_contents($modelFile)));
    }

    /**
     * Generate request file and fill it with the predefined validation rules
     *
     * @return void
     */
    public function generateRequest()
    {
        $requestFile = ($this->findFile($this->model.'Request.php')) ? $this->findFile($this->model.'Request.php')->getPathname() : null;
        $reqEntries = '    ';
        foreach ($this->columns as $column){
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

        $contentToAdd = include __DIR__ . '/../Templates/Crud/Request.php';
        // Write to file
        file_put_contents($requestFile,$contentToAdd);
    }

    /**
     * Generate Laravel files and automatically fill them with code
     *
     * @return void
     */

    public function generate($data)
    {
        $this->mainVersion = app()->version()[0];
        $this->model = $data['model'];
        $this->columns = $data['columns'];
        $this->responses = $data['responses'];
        $this->views = $data['views'];
        $this->prefix = strtolower($this->model);
        $this->amountToSeed = $data['amountToSeed'];
        $this->jExcelColumns = $data['jExcelColumns'];
        $this->jExcelResponses = $data['jExcelResponses'];

        // Create the basic Laravel files (model, migration, controller, factory, seeder, request)
        Artisan::call('make:model',[
            'name' => $this->model,
            '-c' => true,
            '-m' => true,
            '-f' => true,
            '-s' => true,
        ]);
        Artisan::call('make:request',[
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
