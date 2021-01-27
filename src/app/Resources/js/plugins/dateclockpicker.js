window.DCMS.dateClockPicker = function(){
    if (document.querySelectorAll('[data-type=datepicker]').length > 0) {
        window.hasLoaded('datepicker',function () {
            $.each($("[data-type=datepicker]"), function (x, element) {
                var autoClose, format, weekStart;
                autoClose = ($(element).data('datepicker-auto-close') !== 'false') ? 'true' : 'false';
                format = ($(element).data('datepicker-format')) ? $(element).data('datepicker-format') : window.AppDateFormat;
                weekStart = ($(element).data('datepicker-week-start')) ? $(element).data('datepicker-week-start') : 1;
                $(element).datepicker({
                    autoclose: autoClose,
                    format: format,
                    weekStart: weekStart,
                    todayBtn: 'linked',
                    todayHighlight: true,
                });
            });
        });
    }
    if (document.querySelectorAll('[data-type=clockpicker]').length > 0) {
        window.hasLoaded('clockpicker',function () {
            $.each($("[data-type=clockpicker]"), function (x, element) {
                var autoClose;
                autoClose = ($(element).data('clockpicker-auto-close') !== 'false') ? true : false;
                $(element).clockpicker({
                    autoclose: false,
                    donetext: 'OK',
                    placement: 'bottom',
                    today: 'today',
                });
            });
        });
    }
};
window.DCMS.dateClockPicker();
