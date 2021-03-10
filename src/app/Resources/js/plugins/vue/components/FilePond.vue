<template>
  <div ref="filePondWrapper">
      <input
          ref="inputElement"
          type="file"
          data-type="filepond"
          :name="name"
          :column="column"
          :model="model"
          :mime="mime"
          :maxfiles="maxfiles"
          :maxfilesize="maxfilesize"
          :allowpreview="allowpreview"
          :instantupload="instantupload"
          :processroute="processroute"
          :allowrevert="allowrevert"
          :allowcopy="allowcopy"
          :allowpaste="allowpaste"
          :revertroute="revertroute"
          :passtotable="passtotable"
          :aria-describedby="column"
          />
  </div>
</template>
<script>
export default {
    props: [
        "column",
        "model",
        "mime",
        "maxfiles",
        "maxfilesize",
        "allowpreview",
        "instantupload",
        "processroute",
        "allowrevert",
        "allowcopy",
        "allowpaste",
        "revertroute",
        "passtotable",
    ],

    data() {
        return Object.assign(
            {
                inputElement: {},
                filePondWrapper: {},
            },
            this.$attrs
        );
    },

    mounted() {
        var self = this;
        if (typeof FilePond == 'undefined' && document.querySelectorAll('[data-type="filepond"]').length > 0 && (window.DCMS.config.plugins.filepond && window.DCMS.config.plugins.filepond.enable !== false)) {
            window.DCMS.loadCSS(window.DCMS.config.plugins.filepond);
            window.DCMS.loadJS(window.DCMS.config.plugins.filepond);
            window.DCMS.loadCSS(window.DCMS.config.plugins.filepondImagePreview);
            window.DCMS.loadJS(window.DCMS.config.plugins.filepondImagePreview);
            window.DCMS.loadJS(window.DCMS.config.plugins.filepondValidateSize);
        }

        window.DCMS.hasLoaded(['FilePond', 'FilePondPluginImagePreview', 'FilePondPluginFileValidateSize'], function () {
            FilePond.registerPlugin(FilePondPluginImagePreview);
            FilePond.registerPlugin(FilePondPluginFileValidateSize);

            require('./../../translations/_filepond.js');
            FilePond.setOptions({
                onprocessfile: () => {
                    window.DCMS.enableSubmit();
                }
            });

            window.DCMS.pondArray = [];
            window.DCMS.fileArray = [];

            self.makePond();
        });
    },

    methods: {
        makePond() {
            this.inputElement = this.$refs.inputElement;
            this.filePondWrapper = this.$refs.filePondWrapper;
            let self = this;

            window.DCMS.hasLoaded(["FilePond", "FilePondPluginImagePreview", "FilePondPluginFileValidateSize",], function () {
                    let pond = FilePond.create(self.inputElement);
                    if (!self.model) {
                        console.log('No model found for FilePond instance. Pass this property with the model attribute, for example: (model="user")');
                    }
                    if (!self.mime) {
                        console.log('No mime found for FilePond instance. Pass this property with the mime attribute, for example: (mime="image")');
                    }
                    if (!self.column) {
                        console.log('No column found for FilePond instance. Pass this property with the column attribute, for example: (column="title")');
                        return false;
                    }
                    pond.name = self.column.slice(-2) !== "[]" ? self.column + "[]" : self.column;
                    pond.maxFiles = typeof self.maxfiles !== "undefined" ? self.maxfiles : 1;
                    pond.allowMultiple = typeof self.maxfiles !== "undefined" && self.maxfiles > 1 ? true : false;
                    pond.maxFileSize = typeof self.maxfilesize !== "undefined"? self.maxfilesize : window.DCMS.filePond.maxSize;
                    pond.allowFileSizeValidation = true;
                    pond.allowImagePreview = typeof self.allowpreview !== "undefined" ? self.allowpreview : window.DCMS.filePond.allowPreview;
                    pond.instantUpload = typeof self.instantupload !== "undefined" ? self.instantupload : window.DCMS.filePond.instantUpload;
                    pond.allowRevert = typeof self.allowrevert !== "undefined" ? self.allowrevert : window.DCMS.filePond.allowRevert;
                    pond.allowPaste = typeof self.allowpaste !== "undefined" ? self.allowpaste : false;
                    pond.onerror = () => {
                        window.DCMS.haltSubmit();
                    };
                    pond.onprocessfile = (error, file) => {
                        window.DCMS.haltSubmit();
                        if (!error) {
                            window.DCMS.fileArray.push({
                                input: pond.name,
                                file: file.serverId,
                            });

                            // Insert copy button if file has been uploaded
                            if(self.allowcopy == 'true'){
                                let buttonToInsert = `<button class="filepond--file-action-button filepond--action-copy-item-processing" type="button" data-filepond-copy-button="${pond.name.replace("[]", "")}-copy" data-dcms-action="copy" data-dcms-file="${file.serverId}" data-align="right" style="transform: translate3d(0px, 0px, 0px) scale3d(1, 1, 1); opacity: 1;top: 2.35em"><i class="fas fa-copy" style="color: white;font-size: 10px;margin-bottom: 4px;"></i></button>`;
                                let insertAfter = ".filepond--root.filepond--hopper";
                                insertAfter = self.filePondWrapper.querySelector(insertAfter);
                                if (insertAfter) {
                                    insertAfter = insertAfter.querySelector(
                                        ".filepond--action-revert-item-processing"
                                    );
                                    insertAfter.insertAdjacentHTML("afterend", buttonToInsert);
                                }
                                window.DCMS.copyControls();
                            }

                            if (document.querySelectorAll("[data-type=jexcel]").length > 0) {
                                document.querySelectorAll("[data-type=jexcel]").forEach(function (table) {
                                    if (document.querySelector(self.passtotable)) {
                                        window.axios({
                                            method: "GET",
                                            url: file.serverId,
                                            responseType: "text",
                                            headers: {
                                                "X-CSRF-TOKEN": window.DCMS.csrf,
                                                "Content-type": "application/x-www-form-urlencoded",
                                                "X-Requested-With": "XMLHttpRequest",
                                            },
                                        })
                                        .then(function (response) {
                                            let parseData = window.Papa.parse(response.data);
                                            for (const t in window.DCMS.jExcel.tables) {
                                                let jExcelTable = window.DCMS.jExcel.tables[t];
                                                if (jExcelTable.el == table) {
                                                    jExcelTable.setData(parseData.data, false);
                                                }
                                            }
                                        });
                                    }
                                });
                                window.DCMS.copyControls();
                            }

                            window.DCMS.enableSubmit();
                        }
                    };
                    pond.server = {
                        method: "POST",
                        headers: {
                            "X-CSRF-TOKEN": window.DCMS.csrf,
                            accept: "application/json",
                        },
                        process: {
                            url: self.processroute ? self.processroute : "/dcms/file/process/" + self.model + "/" + self.mime + "/" + self.column,
                            onerror: (res) => {
                                let response;
                                try {
                                    response = JSON.parse(res);
                                } catch (error) {
                                    //
                                }
                                if (!response.errors) {
                                    Swal.fire({
                                        title: Lang("Upload failed"),
                                        html: Lang("An unknown error has occurred.") + "<br>" + Lang("Contact support if this problem persists."),
                                        icon: "error",
                                        confirmButtonText: typeof (window.DCMS.sweetAlert.confirmButtonText !== 'undefined') ? window.DCMS.sweetAlert.confirmButtonText : Lang("OK"),
                                    });
                                }
                                if (response.errors) {
                                    for (const x in response.errors) {
                                        for (const y in response.errors[x]) {
                                            toastr.error(response.errors[x][y]);
                                        }
                                    }
                                }
                            },
                        },
                        revert: {
                            headers: {
                                "X-CSRF-TOKEN": window.DCMS.csrf,
                                "Content-Type": "application/json",
                            },
                            url: self.revertroute ? self.revertroute : "/dcms/file/revert/" + self.model + "/" + self.mime + "/" + self.column,
                            method: "DELETE",
                        },
                    };
                    window.DCMS.filePonds.push(pond);

                    self.inputElement.style.visibility = "visible";
                    self.inputElement.style.display = "inherit";
                }
            );
        },
    },
};
</script>
<style lang="">
</style>
