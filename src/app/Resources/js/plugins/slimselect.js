if (document.querySelectorAll('[data-type=slimselect]').length > 0) {
    window.hasLoaded('SlimSelect',function(){
        document.querySelectorAll('[data-type=slimselect]').forEach(function (element){
            let Slim = new SlimSelect({
                select: element,
                closeOnSelect: element.dataset.slimselectAutoClose == 'false' ? false : true,
                searchPlaceholder: " ",
                searchText: Lang("No results found."),
                placeholder: (element.dataset.slimselectPlaceholder) ? element.dataset.slimselectPlaceholder : ' ',
                addable: (element.dataset.slimselectAddable == 'true') ? function(value){
                    if (element.dataset.slimselectAddRoute !== null && typeof element.dataset.slimselectAddRoute !== 'undefined'){
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
                            success: function () {
                                $("#"+Slim.select.element.id).load(location.href + " #"+Slim.select.element.id+">*", "", );
                                Swal.fire(Lang('Added new option'),Lang('The new option is now available.'),'success');
                            },
                            error: function(response){
                                let errors = response.responseJSON.errors;
                                let alertErrors = '';
                                if (typeof errors !== 'undefined' && errors !== null){
                                    $.each(errors, function (indexInArray, error) {
                                        alertErrors += error[0] + ' <br>';
                                    });
                                } else {
                                    alertErrors = Lang('An unknown error has occurred.') + "<br>" + Lang("Contact support if this problem persists.");
                                }
                                Swal.fire({
                                    title: Lang('Adding option failed'),
                                    html: alertErrors,
                                    icon: "error"
                                });
                            }
                        });
                    } else {
                        return {
                            text: value,
                            value: value.toLowerCase()
                        };
                    }
                } : null
            });
        });
    });
}

