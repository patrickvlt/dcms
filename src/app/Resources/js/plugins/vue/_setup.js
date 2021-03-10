if (typeof Vue == 'undefined' && (window.DCMS.config.plugins.vue && window.DCMS.config.plugins.vue.enable !== false)) {
    window.DCMS.loadJS(window.DCMS.config.plugins.vue);
}

window.DCMS.hasLoaded('Vue',function(){
    require('./_components.js');
})

/*
* FilePond
*/

window.DCMS.filePonds = [];
window.DCMS.filePond = {};
window.DCMS.filePond.maxSize = (typeof dcmsMaxSizeServer !== 'undefined') ? dcmsMaxSizeServer + "KB" : "2000KB";
window.DCMS.filePond.allowRevert = true;
window.DCMS.filePond.instantUpload = true;

/*
* TinyMCE
*/

window.DCMS.tinyMCE = {};
window.DCMS.tinyMCE.langFiles = '/js/dcms/tinymce_lang/' + window.DCMS.language + '.js';
window.DCMS.tinyMCE.plugins = 'print preview fullpage searchreplace autolink directionality visualblocks visualchars fullscreen image link media template codesample table charmap hr pagebreak nonbreaking anchor toc insertdatetime advlist lists textcolor wordcount imagetools  contextmenu colorpicker textpattern help';
window.DCMS.tinyMCE.toolbar = 'formatselect | bold italic strikethrough forecolor backcolor | link | alignleft aligncenter alignright alignjustify  | numlist bullist outdent indent  | removeformat';


/*
* DateTimePicker
*/

window.DCMS.dateTimePickers = [];

/*
* SlimSelect
*/

window.DCMS.slimSelects = [];


/*
* jExcel
*/

window.DCMS.jExcel = {};
window.DCMS.jExcel.tables = [];
window.DCMS.jExcel.translations = require('./../translations/_jexcel.js');
