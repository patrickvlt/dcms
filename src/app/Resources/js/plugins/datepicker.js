if (document.querySelectorAll('[data-type=datepicker]').length > 0) {
    window.addEventListener('DOMContentLoaded', (event) => {
        $.each($("[data-type=datepicker]"), function (x, element) { 
            var autoClose, format, weekStart;
            autoClose = ($(element).data('datepicker-auto-close')) ? $(element).data('datepicker-auto-close') : true;
            format = ($(element).data('datepicker-format')) ? $(element).data('datepicker-format') : window.AppDateFormat;
            weekstart = ($(element).data('datepicker-week-start')) ? $(element).data('datepicker-week-start') : 1;
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