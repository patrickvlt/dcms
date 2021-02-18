window.DCMS.tinyMCE = function () {
    if (document.querySelectorAll('[data-type=tinymce]').length > 0) {
        window.hasLoaded('tinymce', function () {
            tinymce.init({
                selector: '[data-type=tinymce]',
                language_url: window.langFiles,
                language: window.locale,
                plugins: window.tinyMCEplugins,
                toolbar1: window.tinyMCEtoolbar,
                relative_urls: false,
                remove_script_host: false,
                convert_urls: true,
                end_container_on_empty_block: true,
                height: (typeof document.querySelector('[data-type=tinymce]').dataset.tinymceHeight !== 'undefined') ? document.querySelector('[data-type=tinymce]').dataset.tinymceHeight : '',
                init_instance_callback: function (editor) {
                    $(editor.getContainer()).find('button.tox-statusbar__wordcount').click();  // if you use jQuery
                }
            });
        });
    }
};
window.DCMS.tinyMCE();
