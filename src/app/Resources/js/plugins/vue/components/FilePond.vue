<template>
  <div ref="filePondWrapper">
      <input
          ref="inputElement"
          type="file"
          data-type="filepond"
          :id="id"
          :name="name"
          :column="column"
          :model="model"
          :mime="mime"
          :maxfiles="maxfiles"
          :instantupload="instantupload"
          :processroute="processroute"
          :allowcopy="allowcopy"
          :allowrevert="allowrevert"
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
        "instantupload",
        "processroute",
        "allowrevert",
        "revertroute",
        "passtotable",
        "allowcopy"
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
        this.makePond();
    },

    methods: {
        makePond() {
            this.inputElement = this.$refs.inputElement;
            this.filePondWrapper = this.$refs.filePondWrapper;
            let self = this;

            window.DCMS.hasLoaded(
                [
                    "FilePond",
                    "FilePondPluginImagePreview",
                    "FilePondPluginFileValidateSize",
                ],
                function () {
                    let pond = FilePond.create(self.inputElement);
                    if (!self.model) {
                        console.log(
                            'No model found for FilePond instance. Pass this property with the model attribute, for example: (model="user")'
                        );
                        return false;
                    }
                    if (!self.mime) {
                        console.log(
                            'No mime found for FilePond instance. Pass this property with the mime attribute, for example: (mime="image")'
                        );
                        return false;
                    }
                    if (!self.column) {
                        console.log(
                            'No column found for FilePond instance. Pass this property with the column attribute, for example: (column="title")'
                        );
                        return false;
                    }
                    pond.allowMultiple = self.maxfiles > 1 ? true : false;
                    pond.allowFileSizeValidation = true;
                    pond.maxfiles = typeof self.maxfiles !== "undefined" ? self.maxfiles : 1;
                    pond.maxFileSize = self.maxfilesize
                        ? self.maxfilesize
                        : window.DCMS.filePond.maxSize;
                    pond.name =
                        self.column.slice(-2) !== "[]" ? self.column + "[]" : self.column;
                    pond.instantupload = self.instantupload
                        ? self.instantupload
                        : window.DCMS.filePond.instantupload;
                    pond.allowrevert = self.allowrevert
                        ? self.allowrevert
                        : window.DCMS.filePond.allowrevert;
                    pond.allowPaste = false;
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

                            if (self.allowcopy){
                                // Insert copy button if file has been uploaded
                                let buttonToInsert = `<button class="filepond--file-action-button filepond--action-copy-item-processing" type="button" data-filepond-copy-button="${pond.name.replace(
                                    "[]",
                                    ""
                                )}-copy" data-dcms-action="copy" data-dcms-file="${file.serverId
                                    }" data-align="right" style="transform: translate3d(0px, 0px, 0px) scale3d(1, 1, 1); opacity: 1;top: 2.35em">
                            <i class="fas fa-copy" style="color: white;font-size: 10px;margin-bottom: 4px;"></i>
                            </button>`;
                                let insertAfter = ".filepond--root.filepond--hopper";
                                insertAfter = self.filePondWrapper.querySelector(insertAfter);
                                if (insertAfter) {
                                    insertAfter = insertAfter.querySelector(
                                        ".filepond--action-revert-item-processing"
                                    );
                                    insertAfter.insertAdjacentHTML("afterend", buttonToInsert);
                                }
                            }

                            if (document.querySelectorAll("[data-type=jexcel]").length > 0) {
                                document
                                    .querySelectorAll("[data-type=jexcel]")
                                    .forEach(function (table) {
                                        if (document.querySelector(self.passtotable)) {
                                            window
                                                .axios({
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
                            }

                            window.DCMS.copyControls();
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
                            url: self.processroute
                                ? self.processroute
                                : "/dcms/file/process/" +
                                self.model +
                                "/" +
                                self.mime +
                                "/" +
                                self.column,
                            onerror: (res) => {
                                let response,
                                    errors = "";
                                try {
                                    response = JSON.parse(res);
                                } catch (error) {
                                    Swal.fire(Lang("Upload failed"), "", "error");
                                    return;
                                }
                                if (!response.errors) {
                                    Swal.fire({
                                        title: Lang("Upload failed"),
                                        html:
                                            Lang("An unknown error has occurred.") +
                                            "<br>" +
                                            Lang("Contact support if this problem persists."),
                                        icon: "error",
                                    });
                                }
                                if (response.errors) {
                                    for (const x in response.errors) {
                                        for (const y in response.errors[x]) {
                                            errors += response.errors[x][y] + "<br>";
                                        }
                                    }
                                    Swal.fire({
                                        title: Lang("Upload failed"),
                                        html: errors,
                                        icon: "error",
                                    });
                                }
                            },
                        },
                        revert: {
                            headers: {
                                "X-CSRF-TOKEN": window.DCMS.csrf,
                                "Content-Type": "application/json",
                            },
                            url: self.revertroute
                                ? self.revertroute
                                : "/dcms/file/revert/" +
                                self.model +
                                "/" +
                                self.mime +
                                "/" +
                                self.column,
                            method: "DELETE",
                        },
                    };
                    window.DCMS.filePonds.push(pond);

                    self.filePondWrapper.querySelector(".filepond--drop-label").classList.add("input-group-text");
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
