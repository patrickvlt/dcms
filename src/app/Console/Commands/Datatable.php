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
    protected $signature = 'dcms:make-datatable {--model=}';

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
        $model = $console->option('model');
        if ($model == null || $model == ''){
            $console->error('Specify a Model for this Datatable with --model=');
            exit;
        }
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

    public function filter($field=null, $value=null)
    {
        $this->data = array_filter($this->data, function($row) use ($field, $value) {
            // Build your datatable filter here
            switch ($field) {
                // case "nested_field_example":
                //     return ($row["user"]["profile"]["last_token"] == $value) ? $row : null;
                //     break;

                default:
                    return ($row[$field] == $value) ? $row : null;
                    break;
            }
        });
    }
}';
        $path = 'app/Datatables/'.$model.'Datatable.php';
        file_put_contents($path,$content);

        $console->comment('');
        $console->comment('Implement this code to use the new Datatable:');
        $console->comment('');

        $console->info(
'
/**
 * Generate JSON response for KTDatatable.
 *
 * @return \Illuminate\Http\JsonResponse
 */

public function fetch()
{
    $query = '.$model.'::select(\'*\', \'name as something_name\')->selectRaw(\'column_one + column_two AS total_columns\')->with([\'relation\' => function ($query) {
        $query->select(\'*\');
    }]);

    // To simply select everything
    // $query = '.$model.'::query();

    // Specify which columns to search in
    // If no columns are passed as parameter, all columns will be searched
    // $searchInColumns = [\'id\',\'name\',\'email\'];
    // return (new '.$model.'Datatable($query,$searchInColumns))->render();

    return (new '.$model.'Datatable($query))->render();
}

/**
 * Export visible data in Datatable.
 *
 * @return string
 */

public function export(): string
{
    // This is the visible data in the datatable, based on chosen filters/search results
    $data = request()->data;

    // Headers in the excel sheet
    $headers = [
        "id" => "#",
        "name" => __("Name"),
        "user.posts.title" => __("Title"),
        "user.posts.category.name" => __("Category")
    ];

    // return $this->StoreExport($data,$headers);

    // to export all columns and rows for this model
    return $this->StoreExport();
}');

        $console->comment('');
        $console->comment('Generated '.$model.' Datatable in: '.$path);
        $console->comment('');
    }
}
