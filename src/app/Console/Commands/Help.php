<?php

namespace Pveltrop\DCMS\Console\Commands;

use Illuminate\Console\Command;

class Help extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dcms:help {part=none}';

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
        $part = $this->argument('part');
        if ($part == 'none'){
            $helpInfo = "\n"."php artisan dcms:help (part)\n\n".

            "Available parts:\n\n".

            "SweetAlert\n".
            "https://sweetalert2.github.io/\n\n".

            "datepicker\n".
            "https://jqueryui.com/datepicker/\n\n".

            "slimselect\n".
            "https://slimselectjs.com/\n\n".

            "spotlight\n".
            "https://github.com/nextapps-de/spotlight\n\n".

            "filepond\n".
            "https://github.com/pqina/filepond\n\n".

            "jexcel\n".
            "https://bossanova.uk/jexcel/v3/\n\n".

            "helpers\n".
            "The helpers being used in DCMS.\n\n".

            "dcmscontroller\n".
            "The dynamic Laravel controller from this project.\n\n".

            "dcmsdatatable\n".
            "https://keenthemes.com/keen/?page=docs&section=datatable\n".
            "JS/PHP wrapper around Metronics' KTDatatables.\n\n".

            "";
            $console->info($helpInfo);
        }
        if ($part == 'ktdatatable'){
            $print =
'<head>
    <link href="assets/css/style.bundle.css" rel="stylesheet" type="text/css" />
</head>

<div class="card card-custom mt-5" id="tableParent">
    <div class="card-header flex-wrap border-0 pt-6 pb-0">
        <div class="card-title">
            <h3 class="card-label">{{ __(\'Categories\') }}</h3>
        </div>
    </div>
    <div class=\'card-body\'>
        <div class=\'mb-7\'>
            <div class=\'row align-items-center\'>
                <div class=\'col-lg-9 col-xl-8\'>
                    <div class=\'row align-items-center\'>
                        <div class=\'col-md-4 my-2 my-md-0\'>
                            <div class=\'input-icon\'>
                                <input type=\'text\' class=\'form-control\' placeholder={{ __(\'Search\') }} data-kt-action="search" />
                                <span>
                                    <i class=\'flaticon2-search-1 text-muted\'></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class=\'row py-5\'>
            <div class=\'col-lg-2\'>
            <div data-kt-type="selector" style="display:none">
                <div class=\'input-group\'>
                    <div class=\'input-group-prepend\'>
                        <span class=\'input-group-text\'>{{ __(\'ID:\') }}</span>
                    </div>
                    <input type=\'text\' class=\'form-control\' data-kt-filter="id" />
                </div>
            </div>
            </div>
            <div class=\'col-lg-10\'>
                <div data-kt-type="controls" style="display:none">
                    <button class=\'btn btn-light font-weight-bold\' type=\'button\' data-kt-action="reload">{{ __(\'Refresh\') }}</button>
                    <button class=\'btn btn-light font-weight-bold\' type=\'button\' data-kt-action="check-all">{{ __(\'Select all rows\') }}</button>
                    <button class=\'btn btn-light font-weight-bold\' type=\'button\' data-kt-action="uncheck-all" >{{ __(\'Deselect all rows\') }}</button>
                    <button class=\'btn btn-light font-weight-bold\' type=\'button\' data-kt-action="remove-rows">{{ __(\'Delete selected rows\') }}</button>
                </div>
            </div>
        </div>
        <div class=\'datatable datatable-bordered datatable-head-custom\'
            data-kt-route=\'/category/fetch\'
            data-kt-parent="#tableParent"
            data-kt-edit-route={{ route(\'category.edit\',\'__id__\') }}
            data-kt-destroy-route={{ route(\'category.destroy\',\'__id__\') }}
            data-kt-page-size=10
            data-kt-pagination=true
            data-kt-scrolling=false
            data-kt-include-actions=true
            data-kt-include-selector=true
            data-kt-delete-rows-confirm-title="{{ __(\'Delete categories\') }}"
            data-kt-delete-rows-confirm-message="{{ __(\'Are you sure you want to delete these categories?\') }}"
            data-kt-delete-rows-complete-title="{{ __(\'Categories deleted\') }}"
            data-kt-delete-rows-complete-message="{{ __(\'The categories have been deleted.\') }}"
            data-kt-delete-rows-failed-title="{{ __(\'Deleting failed\') }}"
            data-kt-delete-rows-failed-message="{{ __(\'The categories couldn\\\'t be deleted.\') }}"
            data-kt-delete-single-confirm-title=\'{{ __(\'Delete category\') }}\'
            data-kt-delete-single-confirm-message=\'{{ __(\'Are you sure you want to delete this category?\') }}\'
            data-kt-delete-single-complete-title=\'{{ __(\'Deleted category\') }}\'
            data-kt-delete-single-complete-message=\'{{ __(\'The category is deleted.\') }}\'
            data-kt-delete-single-failed-title=\'{{ __(\'Deleting failed\') }}\'
            data-kt-delete-single-failed-message=\'{{ __(\'This category couldn\\\'t be deleted.\') }}\'
            >
            <div data-kt-type="columns">
                <div data-kt-title="{{ __(\'Category\') }}" data-kt-column="name" data-kt-width="100"></div>
            </div>
        </div>
    </div>
</div>

<script>
    var locale = "{{ App::getLocale() }}";
    var maxSizeServer = {!! MaxSizeServer() !!};
</script>
<script src="/js/dcms/dcms.js"></script>
<script src="assets/js/scripts.bundle.js?v=7.0.5"></script>
<script src="assets/plugins/global/plugins.bundle.js?v=7.0.5"></script>
<script>
    DCMSDatatable({
        table: $(".datatable"),
        //customColumns: [{
        //    field: \'column\',
        //    title: Lang("Column"),
        //    order: 5,
        //    sortable: false,
        //    width: 200,
        //    autoHide: false,
        //    textAlign: "center",
        //    template: function (row) {
        //    }
        //}]
    });
</script>';
            $console->comment('');
            $console->comment('Front-End');
            $console->comment('');

            $console->info($print);

            $console->comment('');
            $console->comment('Back-End');
            $console->comment('');

            $print =
'<?php

namespace Pveltrop\\DCMS\\Classes;

use Pveltrop\\DCMS\\Classes\\Datatable;

class PackageDatatable extends Datatable
{
    /**
     * @param $field
     * @param $value
     */

    public function filter($field=[], $value=[])
    {
        switch ($field) {
            case \'total_mediaboxes\':
                $this->query->whereRaw(\'(mediaboxes_included + mediaboxes_extra) >= \'.$value);
                break;
            case \'price\':
                $this->query->where($field, \'<=\', $value);
                break;
            case \'speed\':
                $this->query->where($field, \'>=\', $value);
                break;
            default:
                $this->query->where($field, \'=\', $value);
        }
    }
}

public function fetch()
{
    // This is an example where a package object has a relation to a provider object
    $query = Package::select(\'*\', \'name as package_name\')->selectRaw(\'mediaboxes_included + mediaboxes_extra AS total_mediaboxes\')->with([\'provider\' => function ($query) {
        $query->select(\'*\',\'name as provider_name\');
    }]);

    return (new PackageDatatable($query))->render();
}';

            $console->info($print);
        }

        if ($part == 'dcmscontroller'){
            $print = '
use DCMSController;

function DCMS()
{
    return [
        \'routePrefix\' => \'post\',
        \'created\' => [
            \'title\' => __(\'Post created\'),
            \'message\' => __(\'__title__ has been created.\'),
            \'url\' => \'/post\'
        ],
        \'updated\' => [
            \'title\' => __(\'__title__ updated\'),
            \'message\' => __(\'The post has been updated.\'),
            \'url\' => \'/post\'
        ],
        \'deleted\' => [
            \'url\' => \'/post\'
        ],
        \'imported\' => [
            \'url\' => \'/post\'
        ],
        \'request\' => \'PostRequest\',
        \'views\' => [
            \'index\' => \'index\',
            \'show\' => \'view\',
            \'edit\' => \'edit\',
            \'create\' => \'create\'
        ],
        // for jExcel imports
        // which request attribute belongs to which jExcel column? e.g. \'name\' => 0, \'created_at\' => 3
        \'import\' => [
            \'columns\' => [
                \'post\' => 0,
                \'user_id\' => 1
            ],
            // which classes/route prefixes to use when trying to autocorrect?
            \'autocorrect\' => [
                \'user\' => [
                    // which column/cell in jExcel
                    \'column\' => 1,
                    // which fields to compare with
                    \'fields\' => [
                        \'first_name\'
                    ]
                ]
            ],
            // finished or failed custom messages
            \'finished\' => [
                \'title\' => __(\'Import succeeded\'),
                \'message\' => __(\'Zipcodes have been imported.\'),
            ],
            \'failed\' => [
                \'title\' => __(\'Import failed\'),
                \'message\' => __(\'Some fields contain invalid data.\'),
            ]
        ]
    ];
}

public function Packages()
{
    // This is an example where a package object has a relation to a provider object
    $query = Package::select(\'*\', \'name as package_name\')->selectRaw(\'mediaboxes_included + mediaboxes_extra AS total_mediaboxes\')->with([\'provider\' => function ($query) {
        $query->select(\'*\',\'name as provider_name\');
    }]);

    return (new PackageDatatable($query))->render();
}';

            $console->info($print);
        }
    }
}
