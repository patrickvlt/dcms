import * as tinymce from './assets/tinymce.js';

if (document.querySelectorAll('[data-type=tinymce]').length > 0) {
    window.addEventListener('DOMContentLoaded', (event) => {
        tinymce.init({
          selector: '[data-type=tinymce]',
          language_url: window.langFiles,
          language: window.locale,
          plugins: window.tinyMCEplugins,
          menubar: window.tinyMCEtoolbar,
        });
    });
}