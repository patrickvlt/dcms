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
        if ($model == null || $model == '') {
            $console->error('Specify an existing model for this Form with --model=');
            exit;
        }
        try {
            $class = FindClass($model)['class'];
            $class = new $class;
        } catch (\Exception $e) {
            throw new \Exception('Model not found: '.$model);
        }

        $content = include __DIR__ . '/Code/Form/Class.php';

        $path = 'app/Forms/'.$model.'Form.php';
        file_put_contents($path, $content);

        $console->comment('');
        $console->comment('Generated '.$model.' Form in: '.$path);
        $console->comment('');
    }
}
