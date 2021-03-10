<template>
    <input ref="inputElement" data-type="datetimepicker"
           :name="name"
           :format="format"
           :pickertype="pickertype"
           :value="value">
</template>
<script>
export default {
    /**
    *
    * To easily define validation/date ranges for a datetimepicker on your page, define a dateTimePickers array.
    * You can change the name of this variable if you wish, if you do, don't forget to change it in this code aswell.
    * Define your validation/date ranges on your page, before this component gets mounted to your page:
    *
        var dateTimePickers = [];
        dateTimePickers['yourInputElementName'] = {
            dateValidator: function (d) {
                // Disable saturday
                return moment(d).day() !== 6;
            },
        }
    *
    *
    * Your settings will be applied when DCMS creates the datetimepicker elements.
    */

    props: ['format','pickertype'],

    data() {
        return Object.assign({
            inputElement: {},
            dateTimePickerWrapper: {},
        },this.$attrs);
    },

    mounted() {
        if (typeof MaterialDatetimePicker == 'undefined' && document.querySelectorAll('[data-type="datetimepicker"]').length > 0 && (window.DCMS.config.plugins.datetimepicker && window.DCMS.config.plugins.datetimepicker.enable !== false)) {

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

        this.makePicker();
    },

    methods: {
        makePicker() {
            this.inputElement = this.$refs.inputElement;
            this.dateTimePickerWrapper = this.$refs.dateTimePickerWrapper;
            let self = this;

            window.DCMS.hasLoaded('rome', function () {
                if (typeof MaterialDatetimePicker == 'undefined' && (window.DCMS.config.plugins.datetimepicker && window.DCMS.config.plugins.datetimepicker.enable !== false)) {
                    window.DCMS.loadCSS(window.DCMS.config.plugins.datetimepicker, 'local');
                    window.DCMS.loadJS(window.DCMS.config.plugins.datetimepicker, 'local');
                }


                window.DCMS.hasLoaded(['MaterialDatetimePicker', 'moment', 'rome'], function () {
                    let pickerSettings = typeof dateTimePickers !== 'undefined' && self.name ? dateTimePickers[self.name] : {};
                    let picker = new MaterialDatetimePicker(pickerSettings).on('open', () => {
                        document.querySelector('.rd-month-label').innerHTML = window.DCMS.moment(document.querySelector('.rd-month-label').innerHTML).locale(window.DCMS.language).format('MMMM YYYY').toString();
                        document.querySelectorAll('.c-datepicker__day-head').forEach((day) => {
                            day.innerHTML = Lang(day.innerHTML);
                        });
                        if (self.pickertype == 'time') {
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
                        self.inputElement.value = val.format(self.format);
                    });

                    self.inputElement.addEventListener('focus', () => picker.open());

                    window.DCMS.dateTimePickers[self.inputElement.name] = picker;
                });
            });
        }
    }
}
</script>
<style lang="">

</style>
