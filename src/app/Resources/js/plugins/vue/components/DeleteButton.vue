<template>
    <button ref="buttonElement" type="button" data-dcms-action="destroy" class="btn btn-danger"
        :data-dcms-id="objectid"
        :data-dcms-destroy-route="route"
        :data-dcms-destroy-redirect="redirect"
        :data-dcms-delete-confirm-title="confirmtitle"
        :data-dcms-delete-confirm-message="confirmmessage"
        :data-dcms-delete-complete-title="completetitle"
        :data-dcms-delete-complete-message="completemessage"
        :data-dcms-delete-failed-title="failedtitle"
        :data-dcms-delete-failed-message="failedmessage">
        <slot></slot>
    </button>
</template>
<script>
    export default {
        data() {
            return Object.assign({
                buttonElement: {},
            },
                this.$attrs
            );
        },

        // when the page has loaded
        mounted() {
            var self = this;
            this.$refs.buttonElement.addEventListener('click',function(e){
                e.preventDefault();
                let redirect = (self.redirect) ? self.redirect : false;
                let route = self.route.replace('__id__', self.id);
                window.DCMS.deleteModel({
                    id: self.id,
                    route: route,
                    confirmTitle: (self.confirmtitle) ? Lang(self.confirmtitle) : Lang('Delete object'),
                    confirmMsg: (self.confirmmessage) ? Lang(self.confirmmessage) : Lang('Are you sure you want to delete this object?'),
                    completeTitle: (self.completetitle) ? Lang(self.completetitle) : Lang('Deleted object'),
                    completeMsg: (self.completemessage) ? Lang(self.completemessage) : Lang('The object has been succesfully deleted.'),
                    failedTitle: (self.failedtitle) ? Lang(self.failedtitle) : Lang('Deleting failed'),
                    failedMsg: (self.failedmessage) ? Lang(self.failedmessage) : Lang('This object can\'t be deleted. It might still be required somewhere.'),
                    redirect: redirect
                });
            });
        },
    }
</script>
<style lang="">

</style>
