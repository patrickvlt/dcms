"use strict";

window.DCMS.dCarousel = function () {
    var dcars = 0;
    if (document.querySelectorAll('[data-type=dcarousel]').length > 0) {
        document.querySelectorAll('[data-type=dcarousel]').forEach(function (element) {
            var carousel, dcarSrc, imgElement, dcarHeight, dcarPrefix, dcarColumn;

            try {
                dcarSrc = element.dataset.dcarSrc.split(' ');
            } catch (error) {
                dcarSrc = element.dataset.dcarSrc;
            }

            dcarPrefix = typeof (element.dataset.dcarPrefix !== 'undefined') ? element.dataset.dcarPrefix : null;
            dcarColumn = typeof (element.dataset.dcarColumn !== 'undefined') ? element.dataset.dcarColumn : null;
            dcarHeight = typeof (element.dataset.dcarHeight !== 'undefined') ? element.dataset.dcarHeight : null;

            if (dcarHeight) {
                dcarHeight = `style="height: ` + dcarHeight + `"`;
            }

            imgElement = '';
            dcars = + 1;

            function defaultImgString(img, dcars) {
                return `<div class="dCar-div">
                            <div class="dCar-controls">
                                <a class="dCar-btn spotlight" href="`+ img + `" data-dcar="` + dcars + `" data-dcms-action="copy" data-dcms-file="` + img + `"><span class="dCar-btn dCar-btn-xs dCar-btn-icon dCar-btn-circle dCar-btn-white dCar-btn-hover-text-primary ">
                                    <i class="fas fa-eye"></i>
                                </span></a>
                                <a class="dCar-btn" data-dcar="`+ dcars + `" data-dcms-action="copy" data-dcms-file="` + img + `"><span class="dCar-btn dCar-btn-xs dCar-btn-icon dCar-btn-circle dCar-btn-white dCar-btn-hover-text-primary ">
                                    <i class="fas fa-copy"></i>
                                </span></a>
                                <a class="dCar-btn" data-dcar="`+ dcars + `" data-dCar-action="destroy" data-dcar-prefix="` + dcarPrefix + `" data-dcar-column="` + dcarColumn + `" data-dcms-file="` + img + `"><span class="dCar-btn dCar-btn-xs dCar-btn-icon dCar-btn-circle dCar-btn-white dCar-btn-hover-text-primary ">
                                    <i class="fas fa-trash"></i>
                                </span></a>
                            </div>
                            <img class="dCar-item" src="`+ img + `">
                            <input style="display:none" name="${dcarColumn.match('/[]/') ? dcarColumn : dcarColumn + "[]"}" value=${img}>
                        </div>`;
            }

            function TypeOfCard(entry, element) {
                if (entry.match(/youtube/g)) {
                    element.style.marginTop = '0px';
                    entry = entry.replace('watch?v=', '/embed/');
                    imgElement = `<div class="dCar-div w-100" ` + dcarHeight + `>
                    <iframe class="dCar-iframe" src="`+ entry + `" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                    </div>`;
                } else {
                    imgElement = defaultImgString(entry);
                }
                return imgElement;
            }

            if (typeof dcarSrc === 'string' || dcarSrc instanceof String) {
                if (dcarSrc.match(/\[/g) && dcarSrc.match(/\]/g)) {
                    dcarSrc = JSON.parse(dcarSrc);
                    // loop through array and make image elements
                    Array.from(dcarSrc).forEach(function (img, y) {
                        imgElement = imgElement + TypeOfCard(img, element);
                    });
                } else {
                    imgElement = TypeOfCard(imgElement);
                }
            } else if (typeof dcarSrc === 'object') {
                // loop through array and make image elements
                Array.from(dcarSrc).forEach(function (img) {
                    imgElement = imgElement + TypeOfCard(img, element);
                });
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
            `);

            carousel = element.querySelector('#dCar-content');

            carousel.innerHTML = carousel.innerHTML + imgElement;
        });

        if (document.querySelectorAll('[data-type=dcarousel]').length > 0) {
            document.querySelectorAll('[data-type=dcarousel').forEach(function (element) {
                const gap = 500;

                const carousel = element.querySelector("#dCar-carousel"),
                    content = element.querySelector("#dCar-content"),
                    next = element.querySelector("#dCar-next"),
                    prev = element.querySelector("#dCar-prev");

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
            });
        }

        $(document).on('click', '[data-dCar-action="destroy"]', function (element) {
            var dCarPrefix, dCarColumn, dCarFile, dCarRevertKey, parentDiv, parentCar;
            element = element.currentTarget;

            parentCar = element.parentNode.parentNode.parentNode.parentNode.parentNode.parentNode;
            dCarPrefix = parentCar.dataset.dcarPrefix;
            dCarColumn = parentCar.dataset.dcarColumn;
            dCarRevertKey = (parentCar.dataset.dcarRevertKey) ? '/' + parentCar.dataset.dcarRevertKey : '/' + dCarColumn;
            dCarFile = element.dataset.dcmsFile;

            parentDiv = element.parentNode.parentNode;

            Swal.fire({
                title: Lang('Deleting item'),
                text: Lang('Are you sure you want to delete this item?'),
                icon: "warning",
                confirmButtonColor: typeof (window.SwalConfirmButtonColor !== 'undefined') ? window.SwalConfirmButtonColor : "var(--primary)",
                confirmButtonText: typeof (window.SwalConfirmButtonText !== 'undefined') ? window.SwalConfirmButtonText : Lang("OK"),
                cancelButtonColor: typeof (window.SwalCancelButtonColor !== 'undefined') ? window.SwalCancelButtonColor : "var(--dark)",
                cancelButtonText: typeof (window.SwalCancelButtonText !== 'undefined') ? window.SwalCancelButtonText : Lang("Cancel"),
            }).then(function (result) {
                if (result.value) {
                    try {
                        toastr.success(Lang('Removed item.'));
                    } catch (error) {
                        //
                    }
                    if (parentCar.querySelectorAll('.dCar-div').length == 1) {
                        $(parentCar).remove();
                    } else {
                        $(parentDiv).remove();
                    }
                }
            });
        });

        $(document).on('mouseenter', '.dCar-div', function () {
            $(this).find('.dCar-controls').css('visibility', 'visible');
        });

        $(document).on('mouseleave', '.dCar-div', function () {
            $(this).find('.dCar-controls').css('visibility', 'hidden');
        });
    }
};
window.DCMS.dCarousel();
