<template>
    <textarea ref="inputElement" data-type="tinymce"
        class="form-control" 
        :name="name"
        :id="id"
        :aria-describedby="name"
        :height="height">
        {{ content }}
    </textarea>
</template>
<script>
export default {
    props: ['height','content'],

    data() {
        return Object.assign({
            inputElement: {},
        },this.$attrs);
    },

    mounted() {
        this.makeTinyMCE();
    },

    methods: {
        makeTinyMCE() {
            this.inputElement = this.$refs.inputElement;
            let self = this;
            window.DCMS.hasLoaded('tinymce', function () {
                tinymce.init({
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
                });
            });
        }
    }
}
</script>
<style lang="">
    
</style>