<template>
    <select ref="inputElement" class="form-control" data-type="slimselect"
        :name="name">
        <slot></slot>
    </select>
</template>
<script>
export default {
    props: ['autoclose'],
    name: "slimselect",

    data() {
        return Object.assign({
            inputElement: {},
            noresultstext: Lang("No results found."),
            placeholder: " ",
        },this.$attrs);
    },

    mounted() {
        if (typeof SlimSelect == 'undefined' && (window.DCMS.config.plugins.slimselect && window.DCMS.config.plugins.slimselect.enable !== false)) {
            window.DCMS.loadCSS(window.DCMS.config.plugins.slimselect);
            window.DCMS.loadJS(window.DCMS.config.plugins.slimselect);
        }

        this.makeSelect();
    },

    methods: {
        makeSelect() {
            this.inputElement = this.$refs.inputElement;
            let self = this;

            window.DCMS.hasLoaded('SlimSelect', function () {
                self.inputElement.style.visibility = 'inherit';
                self.inputElement.style.display = 'inherit';
                let Slim = new SlimSelect({
                    select: self.inputElement,
                    closeOnSelect: self.autoclose == 'false' ? false : true,
                    searchPlaceholder: self.placeholder,
                    searchText: self.noresultstext,
                    placeholder: (self.placeholder) ? self.placeholder : ' ',
                });
                window.DCMS.slimSelects[self.inputElement.name] = {
                    element: self.inputElement,
                    slim: Slim
                };
            });
        }
    }
}
</script>
<style lang="">

</style>
