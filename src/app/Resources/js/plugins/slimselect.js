if (typeof SlimSelect == 'undefined' && document.querySelectorAll('[data-type=slimselect]').length > 0 && (window.DCMS.config.plugins.slimselect && window.DCMS.config.plugins.slimselect.enable !== false)) {
    window.DCMS.loadCSS(window.DCMS.config.plugins.slimselect);
    window.DCMS.loadJS(window.DCMS.config.plugins.slimselect);
}

window.DCMS.slimSelects = [];
window.DCMS.slimSelect = function () {
    if (document.querySelectorAll('[data-type=slimselect]').length > 0) {
        window.DCMS.hasLoaded('SlimSelect', function () {
            document.querySelectorAll('[data-type=slimselect]:not([data-ssid])').forEach(function (element) {
                let Slim = new SlimSelect({
                    select: element,
                    closeOnSelect: element.dataset.slimselectAutoClose == 'false' ? false : true,
                    searchPlaceholder: " ",
                    searchText: Lang("No results found."),
                    placeholder: (element.dataset.slimselectPlaceholder) ? element.dataset.slimselectPlaceholder : ' ',
                });
                window.DCMS.slimSelects[element.name] = {
                    element: element,
                    slim: Slim
                };
            });
        });
    }
};
window.DCMS.slimSelect();

