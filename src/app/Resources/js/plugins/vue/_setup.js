if (typeof Vue == 'undefined' && (window.DCMS.config.plugins.vue && window.DCMS.config.plugins.vue.enable !== false)) {
    window.DCMS.loadJS(window.DCMS.config.plugins.vue);
}

window.DCMS.hasLoaded('Vue', function () {
    Vue.component('deletebutton', require('./components/DeleteButton.vue').default);

    /*
    * Carousel
    */

    Vue.component('carousel', require('./components/Carousel.vue').default);

    /*
    * FilePond
    */

    if (typeof FilePond == 'undefined' && document.querySelectorAll('FilePond').length > 0 && (window.DCMS.config.plugins.filepond && window.DCMS.config.plugins.filepond.enable !== false)) {
        window.DCMS.loadCSS(window.DCMS.config.plugins.filepond);
        window.DCMS.loadJS(window.DCMS.config.plugins.filepond);
        window.DCMS.loadCSS(window.DCMS.config.plugins.filepondImagePreview);
        window.DCMS.loadJS(window.DCMS.config.plugins.filepondImagePreview);
        window.DCMS.loadJS(window.DCMS.config.plugins.filepondValidateSize);
    }


    window.DCMS.hasLoaded(['FilePond', 'FilePondPluginImagePreview', 'FilePondPluginFileValidateSize'], function () {
        FilePond.registerPlugin(FilePondPluginImagePreview);
        FilePond.registerPlugin(FilePondPluginFileValidateSize);

        require('./../translations/_filepond.js');
        FilePond.setOptions({
            onprocessfile: () => {
                window.DCMS.enableSubmit();
            }
        });

        window.DCMS.pondArray = [];
        window.DCMS.fileArray = [];
    });

    window.DCMS.filePond = {};
    window.DCMS.filePond.maxSize = (typeof dcmsMaxSizeServer !== 'undefined') ? dcmsMaxSizeServer + "KB" : "2000KB";
    window.DCMS.filePond.allowRevert = true;
    window.DCMS.filePond.instantUpload = true;
    window.DCMS.filePonds = [];
    Vue.component('filepond', require('./components/FilePond.vue').default);

    /*
    * TinyMCE
    */

    if (typeof tinymce == 'undefined' && document.querySelectorAll('TinyMCE').length > 0 && window.DCMS.config.plugins.tinymce && window.DCMS.config.plugins.tinymce.enable !== false) {
        window.DCMS.loadJS(window.DCMS.config.plugins.tinymce, 'local');
    }

    window.DCMS.tinyMCE = {};
    window.DCMS.tinyMCE.langFiles = '/js/dcms/tinymce_lang/' + window.DCMS.language + '.js';
    window.DCMS.tinyMCE.plugins = 'print preview fullpage searchreplace autolink directionality visualblocks visualchars fullscreen image link media template codesample table charmap hr pagebreak nonbreaking anchor toc insertdatetime advlist lists textcolor wordcount imagetools  contextmenu colorpicker textpattern help';
    window.DCMS.tinyMCE.toolbar = 'formatselect | bold italic strikethrough forecolor backcolor | link | alignleft aligncenter alignright alignjustify  | numlist bullist outdent indent  | removeformat';

    Vue.component('tinymce', require('./components/TinyMCE.vue').default);

    /*
    * DateTimePicker
    */

    if (typeof MaterialDatetimePicker == 'undefined' && document.querySelectorAll('DateTimePicker').length > 0 && (window.DCMS.config.plugins.datetimepicker && window.DCMS.config.plugins.datetimepicker.enable !== false)) {

        if (typeof _babelPolyfill == 'undefined') {
            if (typeof process.env.MIX_DCMS_ENV == 'production') {
                window.DCMS.loadJSFile("https://unpkg.com/babel-polyfill@6.2.0/dist/polyfill.js");
            } else {
                window.DCMS.loadJSFile("/js/dcms/assets/polyfill.js");
            }
        }
    
        if (typeof moment == 'undefined') {
            window.DCMS.hasLoaded('_babelPolyfill', function () {
                if (typeof process.env.MIX_DCMS_ENV == 'production') {
                    window.DCMS.loadJSFile("https://github.com/moment/moment/blob/develop/min/moment-with-locales.min.js");
                } else {
                    window.DCMS.loadJSFile("/js/dcms/assets/moment.js");
                }
            });
        }
    
        if (typeof rome == 'undefined') {
            window.DCMS.hasLoaded('moment', function () {
                if (typeof process.env.MIX_DCMS_ENV == 'production') {
                    window.DCMS.loadJSFile("https://cdnjs.cloudflare.com/ajax/libs/rome/2.1.22/rome.standalone.js");
                } else {
                    window.DCMS.loadJSFile("/js/dcms/assets/rome.js");
                }
    
                window.DCMS.moment = moment;
            });
        }    
    }

    window.DCMS.dateTimePickers = [];
    Vue.component('datetimepicker', require('./components/DateTimePicker.vue').default);

    /*
    * SlimSelect
    */

    if (typeof SlimSelect == 'undefined' && document.querySelectorAll('SlimSelect').length > 0 && (window.DCMS.config.plugins.slimselect && window.DCMS.config.plugins.slimselect.enable !== false)) {
        window.DCMS.loadCSS(window.DCMS.config.plugins.slimselect);
        window.DCMS.loadJS(window.DCMS.config.plugins.slimselect);
    }

    window.DCMS.slimSelects = [];
    Vue.component('slimselect', require('./components/SlimSelect.vue').default);

    /*
    * jExcel
    */

    if (typeof jexcel == 'undefined' && document.querySelectorAll('jExcel').length > 0 && (window.DCMS.config.plugins.jexcel && window.DCMS.config.plugins.jexcel.enable !== false)) {
        window.DCMS.loadCSS(window.DCMS.config.plugins.jexcel);
        window.DCMS.loadJS(window.DCMS.config.plugins.jexcel);
    }
    if (typeof jsuites == 'undefined' && document.querySelectorAll('jExcel').length > 0 && (window.DCMS.config.plugins.jsuites && window.DCMS.config.plugins.jsuites.enable !== false)) {
        window.DCMS.loadCSS(window.DCMS.config.plugins.jsuites);
        window.DCMS.loadJS(window.DCMS.config.plugins.jsuites);
    }

    window.DCMS.jExcel = {};
    window.DCMS.jExcel.tables = [];
    require('./../translations/_jexcel.js');

    Vue.component('jexcel', require('./components/jExcel.vue').default);

    /*
    * Vue
    */

    const app = new Vue({
        el: '#vueDCMS',
    });
});