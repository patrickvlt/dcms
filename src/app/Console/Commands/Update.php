<?php

namespace Pveltrop\DCMS\Console\Commands;

use Illuminate\Console\Command;

class Update extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dcms:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Easily update this package, including it\'s resources.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $console = $this;
        shell_exec('composer clear-cache; composer require pveltrop/dcms --prefer-source -vvv');
        $console->info('Updated DCMS package.');
        
        if ($console->confirm('Do you want to copy the (updated) JS and SASS resources?')){
            shell_exec('php artisan vendor:publish --provider="Pveltrop\DCMS\DCMSProvider" --tag=resources');
            $console->info('Updated DCMS resources.');
        }
        
        $console->info('Finished updating DCMS.');
    }
}
