<?php

namespace Pveltrop\DCMS\Console\Commands;

use Illuminate\Console\Command;

class Datatable extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dcms:make-datatable {model}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a new (DCMS) Datatable instance.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $console = $this;
        $model = $console->argument('model');
        try {
            $class = FindClass($model)['class'];
            $class = new $class;
        } catch (\Exception $e) {
            throw new \Exception ('No class found for: '.$model);
        }

        $content =
            '<?php

namespace App\\Datatables;

use Pveltrop\\DCMS\\Classes\\Datatable;

class '.$model.'Datatable extends Datatable
{
    /**
     * @param $field
     * @param $value
     */

    public function filter($field=[], $value=[])
    {
        switch ($field) {
//            case \'total_columns\':
//                $this->query->whereRaw(\'(column_one + column_two) >= \'.$value);
//                break;
//            case \'another_column\':
//                $this->query->where($field, \'>=\', $value);
//                break;
            default:
                $this->query->where($field, \'=\', $value);
        }
    }
}';
        $path = 'app/Datatables/'.$model.'Datatable.php';
        file_put_contents($path,$content);

        $console->info('Use this code to call the new Datatable:');
        $console->info(
'public function fetch()
{
    $query = '.$model.'::select(\'*\', \'name as something_name\')->selectRaw(\'column_one + column_two AS total_columns\')->with([\'relation\' => function ($query) {
        $query->select(\'*\');
    }]);

    // Specify which columns to search in
    // If no columns are passed as parameter, all columns will be searched
    // $searchInColumns = [\'id\',\'name\',\'email\'];
    // return (new '.$model.'Datatable($query,$searchInColumns))->render();

    return (new '.$model.'Datatable($query))->render();
}');

        $console->info('Generated a '.$model.' Datatable instance in: '.$path);
    }
}