import * as tinymce from './assets/tinymce.js';

if (document.querySelectorAll('[data-type=tinymce]').length > 0) {
    window.addEventListener('DOMContentLoaded', (event) => {
        tinymce.init({
          selector: '[data-type=tinymce]',
          language_url: '/js/dcms/tinymce_lang/nl.js',
          language: 'nl',
          plugins: "link",
          menubar: "insert",
        });
    });
}