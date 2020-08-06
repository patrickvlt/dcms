require('./assets/slimselect.min.js');

import SlimSelect from './assets/slimselect.min.js';

if (document.querySelectorAll('[data-type=slimselect]').length > 0) {
    window.addEventListener('DOMContentLoaded', (event) => {
        document.querySelectorAll('[data-type=slimselect]').forEach(function (element){
            let Slim = new SlimSelect({
                select: element,
                addable: (element.dataset.addMethod !== null && typeof element.dataset.addMethod !== 'undefined' && element.dataset.addAction !== null && typeof element.dataset.addAction !== 'undefined') ? function(value){
                    $.ajax({
                        type: Slim.select.element.dataset.addMethod,
                        url: Slim.select.element.dataset.addAction,
                        headers: {
                            'X-CSRF-TOKEN': window.csrf
                        },
                        data: {
                            status: value
                        },
                        success: function (response) {
                            $("#"+Slim.select.element.id).load(location.href + " #"+Slim.select.element.id+">*", "", );
                            Alert('success', Lang('Optie toegevoegd'), Lang('De nieuwe optie is nu zichtbaar.'), {
                                confirm: {
                                    text: Lang('Ok'),
                                    btnClass: 'btn-success',
                                }
                            });
                        },
                        error: function(response){
                            let errors = response.responseJSON.errors;
                            let alertErrors = '';
                            $.each(errors, function (indexInArray, error) { 
                                 alertErrors += error[0] + ' <br>';
                            });
                            Alert('error', Lang('Toevoegen mislukt'), Lang(alertErrors), {
                                confirm: {
                                    text: Lang('Ok'),
                                    btnClass: 'btn-danger',
                                }
                            });
                        }
                    });
                } : null
            });
        });
    })
}

