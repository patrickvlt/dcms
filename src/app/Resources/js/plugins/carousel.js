"use strict";

var dcars = 0;

document.querySelectorAll('[data-type=dcarousel]').forEach(function (element, x){
    var carousel, imgSource, imgElement, dcarPrefix, dcarColumn, defaultImgString;

    imgSource = element.dataset.dcarSrc;
    dcarPrefix = element.dataset.dcarPrefix;
    dcarColumn = element.dataset.dcarColumn;
    imgElement = '';
    dcars =+ 1;

    function defaultImgString(img,dcars,dcarPrefix,dcarColumn){
        return `<div class="dCar-div">
            <div class="dCar-controls">
                <a class="dcarBtn" data-dcar="`+dcars+`" data-dcar-action="copy" data-dcar-file="`+img+`"><span class="btn btn-xs btn-icon btn-circle btn-white btn-hover-text-primary ">
                    <i class="fas fa-copy"></i>
                </span></a>
                <a class="dcarBtn" data-dcar="`+dcars+`" data-dcar-action="destroy" data-dcar-prefix="`+dcarPrefix+`" data-dcar-column="`+dcarColumn+`" data-dcar-file="`+img+`"><span class="btn btn-xs btn-icon btn-circle btn-white btn-hover-text-primary ">
                    <i class="ki ki-bold-close icon-xs"></i>
                </span></a>
            </div>
            <img class="dCar-item" src="`+img+`">
        </div>`;
    }
    
    if (typeof imgSource === 'string' || imgSource instanceof String){
        if (imgSource.match(/\[/g) && imgSource.match(/\]/g)){
            imgSource = JSON.parse(imgSource);
            // loop through array and make image elements
            Array.from(imgSource).forEach(function (img,y){
                imgElement = imgElement + defaultImgString(img);
            })
        } else {
            if (imgSource.match(/youtube/g)){
                element.style.marginTop = '0px';
                imgElement = `<div class="dCar-div w-100">
                <iframe class="dCar-iframe" src="`+imgSource+`" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                </div>`;
            } else {
                imgElement = defaultImgString(imgSource);
            }
        }
    }
    

    element.innerHTML = element.innerHTML + (`
    <div id="dCar-wrapper">
        <div id="dCar-carousel">
            <div id="dCar-content">
            </div>
        </div>
        <button type="button" id="dCar-prev">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                <path fill="none" d="M0 0h24v24H0V0z"></path>
                <path d="M15.61 7.41L14.2 6l-6 6 6 6 1.41-1.41L11.03 12l4.58-4.59z"></path>
            </svg>
        </button>
        <button type="button" id="dCar-next">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                <path fill="none" d="M0 0h24v24H0V0z"></path>
                <path d="M10.02 6L8.61 7.41 13.19 12l-4.58 4.59L10.02 18l6-6-6-6z"></path>
            </svg>
        </button>
    </div>
    `)

    carousel = element.querySelector('#dCar-content');

    carousel.innerHTML = carousel.innerHTML + imgElement;
})

if (document.querySelectorAll('[data-type=dcarousel]').length > 0){
    const gap = 300;

    const carousel = document.getElementById("dCar-carousel"),
      content = document.getElementById("dCar-content"),
      next = document.getElementById("dCar-next"),
      prev = document.getElementById("dCar-prev");

    next.addEventListener("click", e => {
        carousel.scrollBy(width + gap, 0);
        if (carousel.scrollWidth !== 0) {
        prev.style.display = "flex";
        }
        if (content.scrollWidth - width - gap <= carousel.scrollLeft + width) {
        next.style.display = "none";
        }
    });

    prev.addEventListener("click", e => {
        carousel.scrollBy(-(width + gap), 0);
        if (carousel.scrollLeft - width - gap <= 0) {
        prev.style.display = "none";
        }
        if (!content.scrollWidth - width - gap <= carousel.scrollLeft + width) {
        next.style.display = "flex";
        }
    });


    let width = carousel.offsetWidth;
    window.addEventListener("resize", e => (width = carousel.offsetWidth));
}

$(document).on('click','[data-dCar-action="destroy"]',function(element){
    var element, dCarPrefix, dCarColumn, dCarFile, dCarRevertKey, parentDiv, parentCar;
    element = element.currentTarget;

    parentCar = element.parentNode.parentNode.parentNode.parentNode.parentNode.parentNode;
    dCarPrefix = parentCar.dataset.dcarPrefix;
    dCarColumn = parentCar.dataset.dcarColumn;
    dCarRevertKey = (parentCar.dataset.dcarRevertKey) ? '/'+parentCar.dataset.dcarRevertKey : '/'+dCarColumn;
    dCarFile = element.dataset.dcarFile;

    parentDiv = element.parentNode.parentNode;
    
    Alert('warning', Lang('Deleting object'), Lang('Are you sure you want to delete this object?'), {
        confirm: {
            text: Lang('Ok'),
            btnClass: 'btn-warning',
            action: function() {
                $.ajax({
                    type: "DELETE",
                    url: "/"+dCarPrefix+"/file/revert/image/"+dCarColumn+dCarRevertKey,
                    data: dCarFile,
                    dataType: "dataType",
                    headers: {
                        'X-CSRF-TOKEN': window.csrf
                    },
                    complete: function (response) {
                        if (parentCar.querySelectorAll('.dCar-div').length == 1){
                            $(parentCar).remove();
                        } else {
                            $(parentDiv).remove();
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

$(document).on('click','[data-dCar-action="copy"]',function(element){
    var img;
    img = $(this).data('dcar-file');
    textToClipBoard(img);
    toastr.info(Lang('Image copied to clipboard.'))
})