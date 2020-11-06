hasLoaded('tinymce',function(){
    if (document.querySelectorAll('[data-type=tinymce]').length > 0) {
        tinymce.init({
            selector: '[data-type=tinymce]',
            language_url: window.langFiles,
            language: window.locale,
            plugins: window.tinyMCEplugins,
            toolbar1: window.tinyMCEtoolbar,
        });   
    }
});