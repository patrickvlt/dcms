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
        $rootjsassets = base_path() . '/public/js/dcms/assets';
        $rootscss = base_path() . '/resources/sass/dcms/';
        $rootcssassets = base_path() . '/public/css/dcms/assets';
        $vendorjs = base_path() . '/vendor/pveltrop/dcms/src/app/Resources/js';
        $vendorjsassets = base_path() . '/vendor/pveltrop/dcms/src/app/Public/js/assets';
        $vendorscss = base_path() . '/vendor/pveltrop/dcms/src/app/Resources/sass';
        $vendorcssassets = base_path() . '/vendor/pveltrop/dcms/src/app/Public/css/assets';

        if ($console->confirm('Do you want to update the JavaScript files?')){
            // JS
            RemoveDir($vendorjs);
            CopyDir($rootjs,$vendorjs);
            RemoveDir($vendorjsassets);
            CopyDir($rootjsassets,$vendorjsassets);
        }

        if ($console->confirm('Do you want to update the CSS files?')){
            // CSS        
            RemoveDir($vendorscss);
            CopyDir($rootscss,$vendorscss);
            RemoveDir($vendorcssassets);
            CopyDir($rootcssassets,$vendorcssassets);
        }
        
        if ($console->confirm('Do you want to update the configs?')){
            // Configs
            copy(base_path() . '/config/dcms.php', base_path() . '/vendor/pveltrop/dcms/src/Config/dcms.php');
            copy(base_path() . '/dcms.json', base_path() . '/vendor/pveltrop/dcms/src/dcms.json');
        }

        $console->comment('');
        $console->comment('Updated DCMS vendor package. Create a merge request to propose changes.');
        $console->comment('');
    }
}
