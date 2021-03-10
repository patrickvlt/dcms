<template>
    <textarea ref="inputElement" data-type="tinymce"
        class="form-control">
        {{ content }}
    </textarea>
</template>
<script>
export default {
    props: ['height','content'],

    data() {
        return Object.assign({
            inputElement: {},
            darkmode: false,
        },this.$attrs);
    },

    mounted() {
        if (typeof tinymce == 'undefined' && document.querySelectorAll('[data-type="tinymce"]').length > 0 && window.DCMS.config.plugins.tinymce && window.DCMS.config.plugins.tinymce.enable !== false) {
            window.DCMS.loadJS(window.DCMS.config.plugins.tinymce, 'local');
        }

        this.makeTinyMCE();
    },

    methods: {
        makeTinyMCE() {
            this.inputElement = this.$refs.inputElement;
            let self = this;
            window.DCMS.hasLoaded('tinymce', function () {
                let defaultOptions = {
                    selector: 'textarea[name="'+self.inputElement.name+'"]',
                    language_url: window.DCMS.tinyMCE.langFiles,
                    language: window.DCMS.language,
                    plugins: window.DCMS.tinyMCE.plugins,
                    toolbar1: window.DCMS.tinyMCE.toolbar,
                    relative_urls: false,
                    remove_script_host: false,
                    convert_urls: true,
                    end_container_on_empty_block: true,
                    height: (typeof self.height !== 'undefined') ? self.height : '',
                    init_instance_callback: function (editor) {
                        editor.getContainer().querySelector('button.tox-statusbar__wordcount').click();
                    }
                };
                let darkSkinOptions = {
                    skin: "oxide-dark",
                    content_css: "dark",
                }
                let definedOptions = (self.darkmode == 'true') ? Object.assign(defaultOptions,darkSkinOptions) : defaultOptions;
                tinymce.init(definedOptions);
            });
        }
    }
}
</script>
<style lang="">

</style>
