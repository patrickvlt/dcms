if (typeof MaterialDatetimePicker == 'undefined' && document.querySelectorAll('[data-type*=picker]').length > 0 && (window.DCMS.config.plugins.datetimepicker && window.DCMS.config.plugins.datetimepicker.enable !== false)) {
    window.DCMS.loadCSS(window.DCMS.config.plugins.datetimepicker, 'local');
    window.DCMS.loadJS(window.DCMS.config.plugins.datetimepicker, 'local');
}

window.DCMS.datetimePickers = [];
window.DCMS.datetimePicker = function () {
    if (document.querySelectorAll('[data-type*=picker]').length > 0) {
        window.DCMS.hasLoaded('MaterialDatetimePicker', function () {
            let datepickers = document.querySelectorAll('[data-type*=picker]');
            if (datepickers){
                Array.from(datepickers).forEach((datepicker) => {
                    const input = datepicker;
                    const picker = new MaterialDatetimePicker({
                        timePickerDefault: input.dataset.type == 'timepicker' ? true : false,
                        timePickerOnly: input.dataset.type == 'timepicker' ? true : false,
                        // dateValidator: function (d) {
                        //     // dont allow saturday example
                        //     return moment(d).day() !== 6;
                        // },
                    }).on('open', () => {
                        if (input.dataset.type == 'timepicker'){
                            document.querySelector('.c-datepicker__header-date__month').style.display = 'none';
                            document.querySelector('.c-datepicker__header-date__day').style.display = 'none';
                            document.querySelector('.c-datepicker__header-day').style.display = 'none';
                            document.querySelector('.c-datepicker__header-date').classList.add('c-datepicker__header-date-tighten');
                            document.querySelector('.c-datepicker__header-date').classList.remove('c-datepicker__header-date-expand');
                            document.querySelector('.c-datepicker--show-time').click();
                            document.querySelectorAll('.c-datepicker__toggle').forEach((toggle) => {
                                toggle.style.display = 'none';
                            });
                        } else {
                            document.querySelector('.c-datepicker__header-date__month').style.display = 'inherit';
                            document.querySelector('.c-datepicker__header-date__day').style.display = 'inherit';
                            document.querySelector('.c-datepicker__header-day').style.display = 'inherit';
                            document.querySelector('.c-datepicker__header-date').classList.remove('c-datepicker__header-date-tighten');
                            document.querySelector('.c-datepicker__header-date').classList.add('c-datepicker__header-date-expand');
                            document.querySelector('.c-datepicker--show-calendar').click();
                            document.querySelectorAll('.c-datepicker__toggle').forEach((toggle) => {
                                toggle.style.display = 'inherit';
                            });
                        }
                    }).on('submit', (val) => {
                        input.value = val.format(input.dataset.datetimepickerFormat);
                    });

                    input.addEventListener('focus', () => picker.open());      
                    window.DCMS.datetimePickers[input.name] = picker;
                });
            }
        });
    }
};
window.DCMS.datetimePicker();

