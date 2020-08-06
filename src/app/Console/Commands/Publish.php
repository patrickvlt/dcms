<?php

namespace Pveltrop\DCMS\Console\Commands;

use Illuminate\Console\Command;

class Publish extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dcms:publish';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Easily publish resources from this package.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $console = $this;
        shell_exec('php artisan vendor:publish --provider="Pveltrop\DCMS\DCMSProvider" --tag=resources');
        $console->info('Updated DCMS resources.');
    }
}
