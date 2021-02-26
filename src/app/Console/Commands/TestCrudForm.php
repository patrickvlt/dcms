<?php

namespace Pveltrop\DCMS\Console\Commands;

use Exception;
use Illuminate\Console\Command;

class TestCrudForm extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dcms:test-crud-form';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mock a request to the CRUD generator.';

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
     * @throws Exception
     */
    public function handle()
    {

    }
}
