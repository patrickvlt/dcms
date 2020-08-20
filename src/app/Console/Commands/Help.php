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

            "jqconfirm\n".
            "https://craftpip.github.io/jquery-confirm/\n\n".

            "datepicker\n".
            "https://jqueryui.com/datepicker/\n\n".

            "slimselect\n".
            "https://slimselectjs.com/\n\n".

            "spotlight\n".
            "https://github.com/nextapps-de/spotlight\n\n".

            "filepond\n".
            "https://github.com/pqina/filepond\n\n".

            "splide\n".
            "https://splidejs.com/\n\n".

            "jexcel\n".
            "https://bossanova.uk/jexcel/v3/\n\n".

            "helpers\n".
            "The helpers being used in DCMS.\n\n".

            "dcmscontroller\n".
            "The dynamic Laravel controller from this project.\n\n".

            "dcmsdatatable\n".
            "https://keenthemes.com/keen/?page=docs&section=datatable\n".
            "The DCMS JS wrapper around Metronics' KTDatatables.\n\n".

            "";
            $console->info($helpInfo);
        }
        if ($part == 'dcmsdatatable'){
            $print = 
'<head>
    <link href="assets/css/style.bundle.css" rel="stylesheet" type="text/css" />
</head>

<div class="card card-custom mt-5">
    <div class="card-header flex-wrap border-0 pt-6 pb-0">
        <div class="card-title">
            <h3 class="card-label">{{ __(\'Alles In Een Pakketten\') }}</h3>
        </div>
    </div>
    <div class=\'card-body\'>
        <div class=\'mb-7\'>
            <div class=\'row align-items-center\'>
                <div class=\'col-lg-9 col-xl-8\'>
                    <div class=\'row align-items-center\'>
                        <div class=\'col-md-4 my-2 my-md-0\'>
                            <div class=\'input-icon\'>
                                <input type=\'text\' class=\'form-control\' placeholder={{ __(\'Zoeken\') }}
                                    id=\'kt_datatable_search_query\' />
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
                <div class="kt_datatable_rowcontrols" style="display:none">
                    <div class=\'input-group\'>
                        <div class=\'input-group-prepend\'>
                            <span class=\'input-group-text\'>{{ __(\'ID:\') }}</span>
                        </div>
                        <input type=\'text\' class=\'form-control\' id=\'kt_datatable_check_input\' value=\'1\' />
                        <div class=\'input-group-append\'>
                            <button class=\'btn btn-secondary font-weight-bold\' type=\'button\'
                                id=\'kt_datatable_check\'>{{ __(\'Selecteer rij\') }}</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class=\'col-lg-10\'>
                <button class=\'btn btn-light font-weight-bold\' type=\'button\'
                    id=\'kt_datatable_reload\'>{{ __(\'Vernieuwen\') }}</button>
                <div class="kt_datatable_rowcontrols" style="display:none">
                    <button class=\'btn btn-light font-weight-bold\' type=\'button\'
                        id=\'kt_datatable_check_all\'>{{ __(\'Selecteer alle rijen\') }}</button>
                    <button class=\'btn btn-light font-weight-bold\' type=\'button\'
                        id=\'kt_datatable_uncheck_all\'>{{ __(\'Deselecteer alle rijen\') }}</button>
                    <button class=\'btn btn-light font-weight-bold\' type=\'button\'
                        id=\'kt_datatable_remove_row\'>{{ __(\'Verwijder geselecteerde rijen\') }}</button>
                </div>
            </div>
        </div>
        <div class=\'datatable datatable-bordered datatable-head-custom\' id=\'pkgaioDT\' data-route=\'/pkgaio\'
            data-edit-route={{ route(\'pkgaio.edit\',\'__id__\') }}
            data-destroy-route={{ route(\'pkgaio.destroy\',\'__id__\') }} data-page-size=20 data-pagination=true
            data-scrolling=false data-include-actions=true data-include-selector=true
            data-delete-rows-confirm-title=\'{{ __(\'Verwijder pakketten\') }}\'
            data-delete-rows-confirm-message=\'{{ __(\'Weet u zeker dat u deze pakketten wil verwijderen?\') }}\'
            data-delete-rows-complete-title=\'{{ __(\'Pakketten verwijderd\') }}\'
            data-delete-rows-complete-message=\'{{ __(\'De pakketten zijn verwijderd.\') }}\'
            data-delete-rows-failed-title=\'{{ __(\'Verwijderen mislukt\') }}\'
            data-delete-rows-failed-message=\'{{ __(\'De pakketten konden niet verwijderd worden.\') }}\'
            data-delete-single-confirm-title="{{ __(\'Verwijder pakket\') }}"
            data-delete-single-confirm-message="{{ __(\'Weet u zeker dat u dit pakket wil verwijderen?\') }}"
            data-delete-single-complete-title="{{ __(\'Pakket verwijderd\') }}"
            data-delete-single-complete-message="{{ __(\'Het pakket is verwijderd.\') }}"
            data-delete-single-failed-title="{{ __(\'Verwijderen mislukt\') }}"
            data-delete-single-failed-message="{{ __(\'Het pakket kan niet verwijderd worden.\') }}">
            <div id=\'tableColumns\'>
                <div data-title="{{ __(\'Name\') }}" data-column=\'name\' data-href="__website__" data-width=\'150\'></div>
                <div data-title="{{ __(\'User\') }}" data-column=\'name\' data-width=\'200\' data-type=\'card\'
                    data-card-color=\'primary\' data-card-title="name" data-card-info="created_at" data-card-image="logo">
                </div>
                <div data-title="{{ __(\'E-mail\') }}" data-column=\'email\' data-width=\'150\'></div>
                <div data-title="{{ __(\'Logo\') }}" data-column=\'logo\' data-type="image" data-width=\'150\'></div>
                <div data-title="{{ __(\'Price\') }}" data-column=\'price\' data-type="price" data-currency="€"
                    data-width=\'150\'></div>
                <div data-title="{{ __(\'Verified\') }}" data-column=\'created_at\' data-type="boolean"
                    data-text-color="success" data-width=\'150\'></div>
                <div data-title="{{ __(\'Phone number\') }}" data-column=\'phone_nr\' data-type=\'icon\'
                    data-icon-class=\'fas fa-phone mr-2 icon-sm\' data-width=\'150\'></div>
                <div data-title="{{ __(\'Website\') }}" data-column=\'website\' data-type=\'icon\'
                    data-icon-class=\'fab fa-internet-explorer mr-2 icon-sm\' data-width=\'150\'></div>
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
<script>
    DCMSDatatable({
        table: $(".datatable"),
        //customColumns: [{
        //    field: Lang("Totaal"),
        //    title: Lang("Totaal"),
        //    order: 5,
        //    sortable: false,
        //    width: 200,
        //    autoHide: false,
        //    textAlign: "center",
        //    template: function (row) {
        //        let pricePerMonth = row.price;
        //        let oneOffCosts = row.one_off_costs;
        //        let discount = row.discount;
        //
        //        let totalCosts = pricePerMonth * 12 - discount;
        //
        //        let priceDiv = (row.price) ? `<div><p class="pricePerMonth">`+Lang("Prijs per maand:")+` €`+pricePerMonth+`,-`+`</p></div>` : ";
        //        let oneOffCostsDiv = (row.one_off_costs) ? `<div><p class="oneOffCosts">`+Lang("Eenmalige kosten:")+` €`+oneOffCosts+`,-`+`</p></div>` : ";
        //        let discountDiv = (row.discount) ? `<div><p class="discount">`+Lang("Korting:")+discount+`</p></div>` : ";
        //        let totalDiv = `<br><div><p class="totalCosts">`+Lang("Totaal:")+` €`+totalCosts+`,-`+`</p></div>`;
        //
        //        return priceDiv + oneOffCostsDiv + discountDiv + totalDiv;
        //    }
        //}]
    });
</script>';
            $console->info($print);
        }

        if ($part == 'dcmscontroller'){
            $print = "
function DCMS()
{
    return [
        'routePrefix' => 'post',
        'class' => 'Post',
        'indexQuery' => Post::all(),
        'created' => [
            'title' => __('Post aangemaakt'),
            'message' => __('The post has been created.'),
            'url' => '/post'
        ],
        'updated' => [
            'title' => __('__name__ updated'),
            'message' => __('The post has been updated.'),
            'url' => '/post'
        ],
        'deleted' => [
            'url' => '/post'
        ],
        'request' => 'PostRequest',
        'views' => [
            'index' => 'index',
            'show' => 'crud',
            'edit' => 'crud',
            'create' => 'crud'
        ]
    ];
}";
            $console->info($print);
        }
    }
}
