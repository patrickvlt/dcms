if (document.querySelectorAll('[data-type=datepicker]').length > 0) {
    window.addEventListener('DOMContentLoaded', (event) => {
        $.each($("[data-type=datepicker]"), function (x, element) { 
            var autoClose, format, weekStart;
            autoClose = ($(element).data('auto-close')) ? $(element).data('auto-close') : true;
            format = ($(element).data('format')) ? $(element).data('format') : window.AppDateFormat;
            weekstart = ($(element).data('week-start')) ? $(element).data('week-start') : 1;
            $(element).datepicker({
                autoclose: autoClose,
                format: format,
                weekStart: weekstart,
                todayBtn: 'linked',
                todayHighlight: true,
            });
        });
    })
}