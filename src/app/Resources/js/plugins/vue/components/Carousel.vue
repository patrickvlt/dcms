<template>
    <div ref="divElement" data-type="dcarousel"
        :id="id"
        :column="column"
        :model="model"
        :height="height"
        :src="src"
        :aria-describedby="column">
    </div>
</template>
<script>
export default {
    props: ['column','model','height','src'],

    data() {
        return Object.assign({
            carousels: 0,
            carousel: {},
            carouselContent: "",
            carouselControls: {},
            carouselDeleteBtns: {},
            gap: 500,
            width: 0,
            nextBtn: {},
            prevBtn: {},
        },this.$attrs);
    },

    mounted() {
        this.makeCarousel();
    },

    methods: {
        makeCarousel() {
            this.divElement = this.$refs.divElement;
            let self = this;

            try {
                this.src = this.src.split(' ');
            } catch (error) {
                //
            }

            if (this.height) {
                this.height = `style="height: ${this.height}"`;
            }

            self.carousels = document.querySelectorAll('[data-type="dcarousel"]') ? document.querySelectorAll('[data-type="dcarousel"]').length : 0;
            self.carousels = + 1;

            function defaultImgString(img) {
                return `<div class="dCar-div">
                            <div class="dCar-controls">
                                <a class="dCar-btn spotlight" href="${img}" data-dcar="${self.carousels}" data-dcms-action="copy" data-dcms-file="${img}"><span class="dCar-btn dCar-btn-xs dCar-btn-icon dCar-btn-circle dCar-btn-white dCar-btn-hover-text-primary ">
                                    <i class="fas fa-eye"></i>
                                </span></a>
                                <a class="dCar-btn" data-dcar="${self.carousels}" data-dcms-action="copy" data-dcms-file="${img}"><span class="dCar-btn dCar-btn-xs dCar-btn-icon dCar-btn-circle dCar-btn-white dCar-btn-hover-text-primary ">
                                    <i class="fas fa-copy"></i>
                                </span></a>
                                <a class="dCar-btn" data-dcar="${self.carousels}" data-dCar-action="destroy" data-dcar-prefix="${self.model}" data-dcar-column="${self.column}" data-dcms-file="${img}"><span class="dCar-btn dCar-btn-xs dCar-btn-icon dCar-btn-circle dCar-btn-white dCar-btn-hover-text-primary ">
                                    <i class="fas fa-trash"></i>
                                </span></a>
                            </div>
                            <img class="dCar-item" src="${img}" alt="item">
                            <input style="display:none" name="${self.column.match('/[]/') ? self.column : self.column + "[]"}" value=${img}>
                        </div>`;
            }

            let imgElement = '';
            function TypeOfCard(entry, element) {
                if (entry.match(/youtube/g)) {
                    self.divElement.style.marginTop = '0px';
                    entry = entry.replace('watch?v=', '/embed/');
                    imgElement = `<div class="dCar-div w-100" ${this.height}>
                    <iframe class="dCar-iframe" src="${entry}" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                    </div>`;
                } else {
                    imgElement = defaultImgString(entry);
                }
                return imgElement;
            }

            // Run Regex match method to determine how to handle the src
            // This component "expects" an array, so incase (by mistake) a single string is passed, this component will convert the src to an array
            if (typeof self.src === 'string' || self.src instanceof String) {
                if (self.src.match(/\[/g) && self.src.match(/\]/g)) {
                    self.src = JSON.parse(self.src);
                    // loop through array and make image elements
                    Array.from(self.src).forEach(function (img, y) {
                        imgElement = imgElement + TypeOfCard(img);
                    });
                } else {
                    imgElement = TypeOfCard(imgElement);
                }
            } else if (typeof self.src === 'object') {
                // loop through array and make image elements
                Array.from(self.src).forEach(function (img) {
                    imgElement = imgElement + TypeOfCard(img);
                });
            }

            self.divElement.innerHTML = self.divElement.innerHTML + (`
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

            self.carousel = self.divElement.querySelector('#dCar-content');
            self.carousel.innerHTML = self.carousel.innerHTML + imgElement;

            self.carousel = self.divElement.querySelector("#dCar-carousel");
            self.carouselContent = self.divElement.querySelector("#dCar-content");
            self.nextBtn = self.divElement.querySelector("#dCar-next");
            self.prevBtn = self.divElement.querySelector("#dCar-prev");

            self.nextBtn.addEventListener("click", e => {
                self.carousel.scrollBy(self.width + self.gap, 0);
                if (self.carousel.scrollWidth !== 0) {
                    self.prevBtn.style.display = "flex";
                }
                if (content.scrollWidth - self.width - self.gap <= self.carousel.scrollLeft + self.width) {
                    self.nextBtn.style.display = "none";
                }
            });

            self.prevBtn.addEventListener("click", e => {
                self.carousel.scrollBy(-(self.width + self.gap), 0);
                if (self.carousel.scrollLeft - self.width - self.gap <= 0) {
                    self.prevBtn.style.display = "none";
                }
                if (!content.scrollWidth - self.width - self.gap <= self.carousel.scrollLeft + self.width) {
                    self.nextBtn.style.display = "flex";
                }
            });


            self.width = self.carousel.offsetWidth;
            window.addEventListener("resize", e => (self.width = self.carousel.offsetWidth));

            self.carouselDeleteBtns = self.divElement.querySelectorAll('[data-dCar-action="destroy"]');
            if (self.carouselDeleteBtns) {
                Array.from(self.carouselDeleteBtns).forEach((button) => {
                    button.addEventListener('click', function (e) {
                        let thisDestroyBtn = e.target.closest('[data-dCar-action="destroy"]');
                        if (thisDestroyBtn) {
                            let parentCar, dCarFile, parentDiv;

                            parentCar = thisDestroyBtn.parentNode.parentNode.parentNode.parentNode.parentNode.parentNode;
                            dCarFile = thisDestroyBtn.dataset.dcmsFile;
                            parentDiv = thisDestroyBtn.parentNode.parentNode;

                            Swal.fire({
                                title: Lang('Deleting item'),
                                text: Lang('Are you sure you want to delete this item?'),
                                icon: "warning",
                                confirmButtonColor: typeof (window.DCMS.sweetAlert.confirmButtonColor !== 'undefined') ? window.DCMS.sweetAlert.confirmButtonColor : "var(--primary)",
                                confirmButtonText: typeof (window.DCMS.sweetAlert.confirmButtonText !== 'undefined') ? window.DCMS.sweetAlert.confirmButtonText : Lang("OK"),
                                cancelButtonColor: typeof (window.DCMS.sweetAlert.cancelButtonColor !== 'undefined') ? window.DCMS.sweetAlert.cancelButtonColor : "var(--dark)",
                                cancelButtonText: typeof (window.DCMS.sweetAlert.cancelButtonText !== 'undefined') ? window.DCMS.sweetAlert.cancelButtonText : Lang("Cancel"),
                            }).then(function (result) {
                                if (result.value) {
                                    if (parentCar.querySelectorAll('.dCar-div').length == 1) {
                                        parentCar.remove();
                                    } else {
                                        parentDiv.remove();
                                    }
                                    window.toastr.warning(Lang('Removed item.'));
                                }
                            });
                        }
                    });
                });
            }

            self.carouselControls = self.divElement.querySelectorAll('.dCar-div');
            if (self.carouselControls) {
                Array.from(self.carouselControls).forEach((dCarControl) => {
                    dCarControl.addEventListener('mouseenter', function (e) {
                        let buttons = dCarControl.querySelector('.dCar-controls');
                        buttons.style.visibility = 'visible';
                    });
                    dCarControl.addEventListener('mouseleave', function (e) {
                        let buttons = dCarControl.querySelector('.dCar-controls');
                        buttons.style.visibility = 'hidden';
                    });
                });
            }
            window.DCMS.copyControls();
        }
    }
}
</script>
<style lang="">

</style>
