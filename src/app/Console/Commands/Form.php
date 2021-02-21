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
        $this->model = $console->option('model') ?? null;
        if ($this->model == null || $this->model == '') {
            $console->error('Specify an existing model for this Form with --model=');
            exit;
        }

        $formImports = '';
        $formFieldsStr = '';
        $content = include __DIR__ . './../../Templates/Crud/Form.php';

        $path = 'app/Forms/'.$this->model.'Form.php';
        file_put_contents($path, $content);

        $console->comment('');
        $console->comment('Generated '.$this->model.' Form in: '.$path);
        $console->comment('');
    }
}
