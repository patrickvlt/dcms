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
        shell_exec('cp -a '.base_path().'/resources/js/dcms/. '. base_path().'/vendor/pveltrop/dcms/src/app/Resources/js');
        shell_exec('cp -a '.base_path().'/resources/sass/dcms/. '. base_path().'/vendor/pveltrop/dcms/src/app/Resources/sass');
        $console->info('Updated DCMS package. You can make a merge request for your changes.');
    }
}
