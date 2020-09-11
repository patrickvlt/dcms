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
        $rootjs = base_path() . '/resources/js/dcms/';
        $rootscss = base_path() . '/resources/sass/dcms/';
        $vendorjs = base_path() . '/vendor/pveltrop/dcms/src/app/Resources/js';
        $vendorscss = base_path() . '/vendor/pveltrop/dcms/src/app/Resources/sass';

        RemoveDir($vendorjs);
        RemoveDir($vendorscss);

        CopyDir($rootjs,$vendorjs);
        CopyDir($rootscss,$vendorscss);
        copy(base_path() . '/config/dcms.php', base_path() . '/vendor/pveltrop/dcms/src/Config/dcms.php');

        print("\n".shell_exec('composer show pveltrop/dcms --all')."\n");

        $console->info('Updated DCMS vendor package. Make a merge request to propose changes.');
        print("\n");
    }
}
