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
        $model = $console->option('model') ?? null;
        if ($model == null || $model == ''){
            $console->error('Specify an existing model for this Datatable with --model=');
            exit;
        }
        try {
            $class = FindClass($model)['class'];
            $class = new $class;
        } catch (\Exception $e) {
            throw new \Exception ('Model not found: '.$model);
        }

        // Write content to new Datatable file
        $content = include __DIR__ . '/Code/Datatable/Class.php';
        $path = 'app/Datatables/'.$model.'Datatable.php';
        file_put_contents($path,$content);
        
        $console->comment('');
        $console->comment('Implement this code to use the new Datatable:');
        $console->comment('');

        // Show sample code in console
        $sampleCode = include __DIR__ . '/Code/Datatable/SampleCode.php';
        $console->info($sampleCode);

        $console->comment('');
        $console->comment('Generated '.$model.' Datatable in: '.$path);
        $console->comment('');
    }
}
