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
        $rootjsassets = base_path() . '/public/js/dcms/assets';
        $rootscss = base_path() . '/resources/sass/dcms/';
        $rootcssassets = base_path() . '/public/css/dcms/assets';
        $vendorjs = base_path() . '/vendor/pveltrop/dcms/src/app/Resources/js';
        $vendorjsassets = base_path() . '/vendor/pveltrop/dcms/src/app/Public/js/assets';
        $vendorscss = base_path() . '/vendor/pveltrop/dcms/src/app/Resources/sass';
        $vendorcssassets = base_path() . '/vendor/pveltrop/dcms/src/app/Public/css/assets';

        RemoveDir($rootjs);
        RemoveDir($rootjsassets);
        RemoveDir($rootscss);
        RemoveDir($rootcssassets);

        CopyDir($vendorjs,$rootjs);
        CopyDir($vendorjsassets,$rootjsassets);
        CopyDir($vendorscss,$rootscss);
        CopyDir($vendorcssassets,$rootcssassets);
        copy(base_path() . '/vendor/pveltrop/dcms/src/Config/dcms.php', base_path() . '/config/dcms.php');
        copy(base_path() . '/vendor/pveltrop/dcms/src/dcms.json', base_path() . '/dcms.json');

        print("\n".shell_exec('git status')."\n");

        $console->comment('');
        $console->comment('Published DCMS resources.');
        $console->comment('');
        print("\n");
    }
}
