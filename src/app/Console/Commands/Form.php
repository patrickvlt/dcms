<?php

namespace Pveltrop\DCMS\Console\Commands;

use Illuminate\Console\Command;

class Form extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dcms:make-form {--model=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a new form to use with the DCMSController trait.';

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
        $model = $console->option('model') ?? null;
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

        $content = '<?php

namespace App\Forms;

class '.$model.'Form
{
    public static function properties(){
        return [
            //
        ];
    }
}
';

        $path = 'app/Forms/'.$model.'Form.php';
        file_put_contents($path,$content);

        $console->comment('');
        $console->comment('Generated '.$model.' Form in: '.$path);
        $console->comment('');
    }
}
