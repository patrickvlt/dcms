if (typeof tinymce == 'undefined' && document.querySelectorAll('[data-type=tinymce]').length && window.DCMS.config.plugins.tinymce && window.DCMS.config.plugins.tinymce.enable !== false) {
    window.DCMS.loadJS(window.DCMS.config.plugins.tinymce, 'local');
}

window.DCMS.tinyMCE = {};
window.DCMS.tinyMCE.langFiles = '/js/dcms/tinymce_lang/' + window.DCMS.language + '.js';
window.DCMS.tinyMCE.plugins = 'print preview fullpage searchreplace autolink directionality visualblocks visualchars fullscreen image link media template codesample table charmap hr pagebreak nonbreaking anchor toc insertdatetime advlist lists textcolor wordcount imagetools  contextmenu colorpicker textpattern help';
window.DCMS.tinyMCE.toolbar = 'formatselect | bold italic strikethrough forecolor backcolor | link | alignleft aligncenter alignright alignjustify  | numlist bullist outdent indent  | removeformat';

window.DCMS.tinyMCE = function () {
    if (document.querySelectorAll('[data-type=tinymce]').length > 0) {
        window.DCMS.hasLoaded('tinymce', function () {
            tinymce.init({
                selector: '[data-type=tinymce]',
                language_url: window.DCMS.tinyMCE.langFiles,
                language: window.DCMS.language,
                plugins: window.DCMS.tinyMCE.plugins,
                toolbar1: window.DCMS.tinyMCE.toolbar,
                relative_urls: false,
                remove_script_host: false,
                convert_urls: true,
                end_container_on_empty_block: true,
                height: (typeof document.querySelector('[data-type=tinymce]').dataset.tinymceHeight !== 'undefined') ? document.querySelector('[data-type=tinymce]').dataset.tinymceHeight : '',
                init_instance_callback: function (editor) {
                    editor.getContainer().querySelector('button.tox-statusbar__wordcount').click();
                }
            });
        });
    }
};
window.DCMS.tinyMCE();
