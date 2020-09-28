<head>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <link href="/css/dcms/dcms.css" rel="stylesheet" type="text/css" />
</head>
<body>
<div id="app">
    @{{ message }}
    <file-pond
        name="test"
        ref="pond"
        class-name="my-pond"
        label-idle="Drop files here..."
        allow-multiple="true"
        accepted-file-types="image/jpeg, image/png"
        v-bind:files="myFiles"
        v-on:init="handleFilePondInit"/>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

<script src="/js/dcms/dcms.js"></script>
<script>
    DCMSDatatable({
        table: $('.datatable')
    });
    onReady(function(){
        export default {
            name: 'app',
            data: function() {
                return { myFiles: ['index.html'] };
            },
            methods: {
                handleFilePondInit: function() {
                    console.log('FilePond has initialized');

                    // example of instance method call on pond reference
                    this.$refs.pond.getFiles();
                }
            },
            components: {
                FilePond
            }
        };

        var app = new Vue({
            el: '#app',
            data: {
                message: 'Hello Vue!'
            }
        })
    })
</script>
</body>
