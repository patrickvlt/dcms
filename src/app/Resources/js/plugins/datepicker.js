if (document.querySelectorAll('[data-type=datepicker]').length > 0) {
    window.addEventListener('DOMContentLoaded', (event) => {
        $("[data-type=datepicker]").datepicker({
            autoclose: true,
            format: 'dd-mm-yyyy',
            weekStart: 1,
            todayBtn: 'linked',
            todayHighlight: true,
        });
    })
}