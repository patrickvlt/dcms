/**
*
* To easily define custom settings for a datetimepicker on your page, define a datetimePickers array.
* You can change the name of this variable if you wish, if you do, don't forget to change it in this code aswell.
* You can define this in your separate JavaScript before this code runs, example below:

var datetimePickers = [];
datetimePickers['yourInputElementName'] = {
    format: 'DD-MM-YYYY',
    // Disable saturday
    dateValidator: function (d) {
        return moment(d).day() !== 6;
    },
}

*
* Your settings will be applied when DCMS creates the datetimepicker elements.
*/

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

window.DCMS.hasLoaded('rome', function () {
    if (typeof MaterialDatetimePicker == 'undefined' && document.querySelectorAll('[data-type*=picker]').length > 0 && (window.DCMS.config.plugins.datetimepicker && window.DCMS.config.plugins.datetimepicker.enable !== false)) {
        window.DCMS.loadCSS(window.DCMS.config.plugins.datetimepicker, 'local');
        window.DCMS.loadJS(window.DCMS.config.plugins.datetimepicker, 'local');
    }

    window.DCMS.datetimePickers = [];
    window.DCMS.datetimePicker = function () {
        if (document.querySelectorAll('[data-type*=picker]').length > 0) {
            window.DCMS.hasLoaded(['MaterialDatetimePicker', 'moment', 'rome'], function () {
                let datepickers = document.querySelectorAll('[data-type*=picker]');
                if (datepickers) {
                    Array.from(datepickers).forEach((datepicker) => {
                        const input = datepicker;
                        const picker = new MaterialDatetimePicker(typeof datetimePickers !== 'undefined' && datetimePickers[input.name] ? datetimePickers[input.name] : {}).on('open', () => {
                            document.querySelector('.rd-month-label').innerHTML = window.DCMS.moment(document.querySelector('.rd-month-label').innerHTML).locale(window.DCMS.language).format('MMMM YYYY').toString();
                            document.querySelectorAll('.c-datepicker__day-head').forEach((day) => {
                                day.innerHTML = Lang(day.innerHTML);
                            });
                            if (input.dataset.type == 'timepicker') {
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
                            let thisFormat;
                            if (typeof datetimePickers !== 'undefined' && datetimePickers[input.name].format){
                                thisFormat = datetimePickers[input.name].format;
                            } else if (input.dataset.datetimepickerFormat){
                                thisFormat = input.dataset.datetimepickerFormat;
                            }
                            input.value = val.format(thisFormat);
                        });

                        input.addEventListener('focus', () => picker.open());
                        window.DCMS.datetimePickers[input.name] = picker;
                    });
                }
            });
        }
    };
    window.DCMS.datetimePicker();
});