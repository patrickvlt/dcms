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
        $rootjsportal = base_path() . '/public/js/dcms/portal';
        $rootscss = base_path() . '/resources/sass/dcms/';
        $rootcssassets = base_path() . '/public/css/dcms/assets';
        $rootcssportal = base_path() . '/public/css/dcms/portal';
        $vendorjs = base_path() . '/vendor/pveltrop/dcms/src/app/Resources/js';
        $vendorjsassets = base_path() . '/vendor/pveltrop/dcms/src/app/Public/js/assets';
        $vendorjsportal = base_path() . '/vendor/pveltrop/dcms/src/app/Public/js/portal';
        $vendorscss = base_path() . '/vendor/pveltrop/dcms/src/app/Resources/sass';
        $vendorcssassets = base_path() . '/vendor/pveltrop/dcms/src/app/Public/css/assets';
        $vendorcssportal = base_path() . '/vendor/pveltrop/dcms/src/app/Public/css/portal';

        if ($console->confirm('Do you want to update the JavaScript files?')){
            // JS
            RemoveDir($rootjs);
            CopyDir($vendorjs,$rootjs);
            RemoveDir($rootjsassets);
            CopyDir($vendorjsassets,$rootjsassets);
            RemoveDir($rootjsportal);
            CopyDir($vendorjsportal,$rootjsportal);
        }

        if ($console->confirm('Do you want to update the CSS files?')){
            // CSS        
            RemoveDir($rootscss);
            CopyDir($vendorscss,$rootscss);
            RemoveDir($rootcssassets);
            CopyDir($vendorcssassets,$rootcssassets);
            RemoveDir($rootcssportal);
            CopyDir($vendorcssportal,$rootcssportal);
        }

        if ($console->confirm('Do you want to update the configs?')){
            // Configs
            copy(base_path() . '/vendor/pveltrop/dcms/src/Config/dcms.php', base_path() . '/config/dcms.php');
            copy(base_path() . '/vendor/pveltrop/dcms/src/dcms.json', base_path() . '/dcms.json');
        }

        print("\n".shell_exec('git status')."\n");

        $console->comment('');
        $console->comment('Published DCMS resources.');
        $console->comment('');
        print("\n");
    }
}
