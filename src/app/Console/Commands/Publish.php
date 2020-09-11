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
        $rootjs = base_path() . '/resources/js/dcms/';
        $rootscss = base_path() . '/resources/sass/dcms/';
        $vendorjs = base_path() . '/vendor/pveltrop/dcms/src/app/Resources/js';
        $vendorscss = base_path() . '/vendor/pveltrop/dcms/src/app/Resources/sass';

        RemoveDir($rootjs);
        RemoveDir($rootscss);

        CopyDir($vendorjs,$rootjs);
        CopyDir($vendorscss,$rootscss);
        copy(base_path() . '/vendor/pveltrop/dcms/src/Config/dcms.php', base_path() . '/config/dcms.php');

        print("\n".shell_exec('composer show pveltrop/dcms --all')."\n");
        print("\n".shell_exec('git status')."\n");

        $console->info('Published DCMS resources.');
        print("\n");
    }
}
