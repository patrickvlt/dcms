require('./assets/splide.min.js');

if (document.querySelectorAll('[data-type=splide]').length > 0) {
    // window.addEventListener('DOMContentLoaded', (event) => {
        $.each($('[data-type="splide"]'), function (x, element) {
            var glidePerPage, glideHeight, glideBreakpoints, glidePrefix, glideColumn;
            glidePerPage = ($(element).data('splide-per-page')) ? $(element).data('splide-per-page') : 2;
            glideHeight = ($(element).data('splide-height')) ? $(element).data('splide-height') : '15rem';
            glidePrefix = ($(element).data('splide-prefix')) ? $(element).data('splide-prefix') : '';
            glideColumn = ($(element).data('splide-column')) ? $(element).data('splide-column') : '';
            glideBreakpoints = ($(element).data('splide-breakpoints')) ? $(element).data('splide-breakpoints') : '6rem';
            $(element).addClass('splide');
            $(element).append(`<div class="splide__track">
                <ul class="splide__list">
                </ul>
            </div>`);
            let splideTrack = $(element).find('.splide__list');
            let imagesSource = $(element).data('splide-source');
            let images = '';
            if ($(element).data('splide-source')) {
                // if one single URL, most of the time a string
                if (typeof imagesSource === 'string' || imagesSource instanceof String){
                    images = `<li class="splide__slide" data-splide="`+x+`"><div class="spotlight glideJSimg" data-src="` + imagesSource + `" style="background-image:url('` + imagesSource + `')"></div></li><a class="splideBtn" data-splide="`+x+`" data-splide-action="destroy" data-splide-prefix="`+glidePrefix+`" data-splide-column="`+glideColumn+`" data-splide-file="`+imagesSource+`"><span class="btn btn-xs btn-icon btn-circle btn-white btn-hover-text-primary " data-splide-toggle="tooltip" data-splide-original-title="` + Lang('Remove') + `">
                        <i class="ki ki-bold-close icon-xs text-muted"></i>
                    </span></a>`
                // if source is an array
                } else {
                    $.each(imagesSource, function (x, image) {
                        images = images + `<li class="splide__slide" data-splide="`+x+`"><div class="spotlight glideJSimg" data-src="` + image + `" style="background-image:url('` + image + `')"></div></li>
                        <a class="splideBtn" data-splide="`+x+`" data-splide-action="copy" data-splide-file="`+image+`"><span class="btn btn-xs btn-icon btn-circle btn-white btn-hover-text-primary ">
                            <i class="fas fa-copy"></i>
                        </span></a>
                        <a class="splideBtn" data-splide="`+x+`" data-splide-action="destroy" data-splide-prefix="`+glidePrefix+`" data-splide-column="`+glideColumn+`" data-splide-file="`+image+`"><span class="btn btn-xs btn-icon btn-circle btn-white btn-hover-text-primary " data-splide-toggle="tooltip" data-splide-original-title="` + Lang('Remove') + `">
                            <i class="ki ki-bold-close icon-xs"></i>
                        </span></a>`
                    });
                }
            }
            $(splideTrack).append(images);
            new Splide(element, {
                perPage: glidePerPage,
                height: glideHeight,
                cover: true,
                breakpoints: {
                    height: glideBreakpoints,
                }
            }).mount();
            if ($(element).find('.glideJSimg').length <= 1){
                $(element).find('.splide__arrows').hide();
                $(element).find('.splide__pagination').hide();
            }
        });
    // }); 
}

$(document).on('click','[data-splide-action="destroy"]',function(element){
    var element, splidePrefix, splideColumn, splideFile, parentSplide;
    element = element.currentTarget;

    splidePrefix = element.dataset.splidePrefix;
    splideColumn = element.dataset.splideColumn;
    splideRevertKey = (element.dataset.splideRevertKey) ? '/'+element.dataset.splideRevertKey : '/'+splideColumn;
    splideFile = element.dataset.splideFile;

    parentDiv = element.parentNode.parentNode.parentNode;
    let els = document.querySelectorAll('#'+parentDiv.id+' [data-splide="'+element.dataset.splide+'"]');
    
    Alert('warning', Lang('Deleting object'), Lang('Are you sure you want to delete this object?'), {
        confirm: {
            text: Lang('Ok'),
            btnClass: 'btn-warning',
            action: function() {
                $.ajax({
                    type: "DELETE",
                    url: "/"+splidePrefix+"/file/revert/image/"+splideColumn+splideRevertKey,
                    data: splideFile,
                    dataType: "dataType",
                    headers: {
                        'X-CSRF-TOKEN': window.csrf
                    },
                    complete: function (response) {
                        $(els).remove();
                        if ($(parentDiv).find('.splide__slide').length == 0){
                            $(parentDiv).find('.splide__arrows').remove();
                            $(parentDiv).find('.splide__pagination').remove();
                            parentDiv.remove();
                        }
                    }
                });
            }
        },
        cancel: {
            text: Lang('Cancel'),
            btnClass: 'btn-dark'
        }
    });
})

$(document).on('click','[data-splide-action="copy"]',function(element){
    img = $(this).data('splide-file');
    textToClipBoard(img);
    toastr.info(Lang('Image copied to clipboard.'))
})