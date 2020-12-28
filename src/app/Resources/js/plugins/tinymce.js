window.hasLoaded('tinymce',function(){
    if (document.querySelectorAll('[data-type=tinymce]').length > 0) {
        tinymce.init({
            selector: '[data-type=tinymce]',
            language_url: window.langFiles,
            language: window.locale,
            plugins: window.tinyMCEplugins,
            toolbar1: window.tinyMCEtoolbar,
            relative_urls : false,
            remove_script_host : false,
            convert_urls : true,
            end_container_on_empty_block: true,
            init_instance_callback: function (editor) {
                $(editor.getContainer()).find('button.tox-statusbar__wordcount').click();  // if you use jQuery
             }
        });
    }
});
