<template>
    <select ref="inputElement" class="form-control" data-type="slimselect" 
        :id="id"
        :name="name"
        :aria-describedby="name"
        :autoclose="autoclose"
        :placeholder="placeholder"
        :data="data"
        :optiontextattr="optiontextattr"
        :optionvalueattr="optionvalueattr"
        :value="value"
        >
        <option disabled></option>
    </select>
</template>
<script>
export default {
    props: ['autoClose','placeholder', 'data', 'value'],

    data() {
        return Object.assign({
            inputElement: {},
        },this.$attrs);
    },

    mounted() {
        this.makeSelect();
    },

    methods: {
        makeSelect() {
            this.inputElement = this.$refs.inputElement;
            let self = this;

            window.DCMS.hasLoaded('SlimSelect', function () {
                if(self.data){
                    let thisData = JSON.parse(self.data);
                    
                    for (const d in thisData) {
                        let thisOption = document.createElement('option');
                        thisOption.text = thisData[d][self.optiontextattr];
                        thisOption.value = thisData[d][self.optionvalueattr];
                        thisOption.selected = (thisData[d][self.optionvalueattr] == self.value) ? 'selected' : '';
                        self.inputElement.add(thisOption);
                    }
                }
                let Slim = new SlimSelect({
                    select: self.inputElement,
                    closeOnSelect: self.autoClose == 'false' ? false : true,
                    searchPlaceholder: " ",
                    searchText: Lang("No results found."),
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