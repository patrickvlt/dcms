require('./assets/slimselect.min.js');

import SlimSelect from './assets/slimselect.min.js';

if (document.querySelectorAll('[data-type=slimselect]').length > 0) {
    window.addEventListener('DOMContentLoaded', (event) => {
        document.querySelectorAll('[data-type=slimselect]').forEach(function (element){
            let Slim = new SlimSelect({
                select: element,
                addable: (element.dataset.slimselectAddRoute !== null && typeof element.dataset.slimselectAddRoute !== 'undefined') ? function(value){
                    let column = (element.dataset.slimselectAddColumn !== null && typeof element.dataset.slimselectAddColumn !== 'undefined') ? element.dataset.slimselectAddColumn : '';
                    var data = {};
                    data[column] = value;
                    $.ajax({
                        type: 'POST',
                        url: Slim.select.element.dataset.slimselectAddRoute,
                        headers: {
                            'X-CSRF-TOKEN': window.csrf
                        },
                        data: data,
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

