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
     * @var string
     */
    public $vendorPath;

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $console = $this;
        $this->vendorPath = base_path().'/vendor/pveltrop/dcms/';

        $rootjs = base_path() . '/resources/js/dcms';
        $rootjsassets = base_path() . '/public/js/dcms/assets';
        $rootjsportal = base_path() . '/public/js/dcms/portal';
        $rootscss = base_path() . '/resources/sass/dcms';
        $rootcssassets = base_path() . '/public/css/dcms/assets';
        $rootcssportal = base_path() . '/public/css/dcms/portal';
        $vendorjs = $this->vendorPath.'/src/app/Resources/js';
        $vendorjsassets = $this->vendorPath.'/src/app/Public/js/assets';
        $vendorjsportal = $this->vendorPath.'/src/app/Public/js/portal';
        $vendorscss = $this->vendorPath.'/src/app/Resources/sass';
        $vendorcssassets = $this->vendorPath.'/src/app/Public/css/assets';
        $vendorcssportal = $this->vendorPath.'/src/app/Public/css/portal';

        if ($console->confirm('Do you want to update the JavaScript files?')) {
            // JS
            RemoveDir($vendorjs);
            CopyDir($rootjs, $vendorjs);
            RemoveDir($vendorjsassets);
            CopyDir($rootjsassets, $vendorjsassets);
            if (config('dcms.portal') == 'true') {
                RemoveDir($vendorjsportal);
                CopyDir($rootjsportal, $vendorjsportal);
            }
        }

        if ($console->confirm('Do you want to update the CSS files?')) {
            // CSS
            RemoveDir($vendorscss);
            CopyDir($rootscss, $vendorscss);
            RemoveDir($vendorcssassets);
            CopyDir($rootcssassets, $vendorcssassets);
            if (config('dcms.portal') == 'true') {
                RemoveDir($vendorcssportal);
                CopyDir($rootcssportal, $vendorcssportal);
            }
        }
        
        if ($console->confirm('Do you want to update the configs?')) {
            // Configs
            copy(base_path() . '/config/dcms.php', base_path() . '/vendor/pveltrop/dcms/src/Config/dcms.php');
            copy(base_path() . '/dcms.json', base_path() . '/vendor/pveltrop/dcms/src/dcms.json');
        }

        $console->comment('');
        $console->comment('Updated DCMS vendor package. Create a merge request to propose changes.');
        $console->comment('');
    }
}
