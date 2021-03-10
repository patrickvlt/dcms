if (typeof FilePond == 'undefined' && document.querySelectorAll('[data-type=filepond]').length > 0 && (window.DCMS.config.plugins.filepond && window.DCMS.config.plugins.filepond.enable !== false)) {
    window.DCMS.loadCSS(window.DCMS.config.plugins.filepond);
    window.DCMS.loadJS(window.DCMS.config.plugins.filepond);
    window.DCMS.loadCSS(window.DCMS.config.plugins.filepondImagePreview);
    window.DCMS.loadJS(window.DCMS.config.plugins.filepondImagePreview);
    window.DCMS.loadJS(window.DCMS.config.plugins.filepondValidateSize);
}

// Filepond
window.DCMS.maxSizeServer = (typeof dcmsMaxSizeServer !== 'undefined') ? dcmsMaxSizeServer + "KB" : "2000KB";
window.DCMS.FilePondAllowRevert = true;
window.DCMS.FilePondInstantUpload = true;

function copyControlsEvents() {
    let copyControls = document.querySelectorAll('[data-dcms-action="copy"]');
    if (copyControls) {
        Array.from(copyControls).forEach((copyControl) => {
            copyControl.addEventListener('click', function (e) {
                let img = e.target.dataset.dcmsFile;
                window.DCMS.textToClipBoard(img);
                window.toastr.success(Lang('Image copied to clipboard.'));
            });
        });
    }
}

window.DCMS.filePond = function () {
    if (document.querySelectorAll('[data-type=filepond]').length > 0) {
        window.DCMS.hasLoaded(['FilePond', 'FilePondPluginImagePreview', 'FilePondPluginFileValidateSize'], function () {
            window.DCMS.filePonds = [];

            FilePond.registerPlugin(FilePondPluginImagePreview);
            FilePond.registerPlugin(FilePondPluginFileValidateSize);

            require('./../translations/_filepond.js');
            FilePond.setOptions({
                onprocessfile: () => {
                    window.DCMS.enableSubmit();
                }
            });
            window.DCMS.pondArray = [];
            window.DCMS.fileArray = [];

            function MakePond(inputElement, method = 'POST') {
                let parseData;
                let pond = FilePond.create(inputElement);
                if (!inputElement.dataset.filepondPrefix) {
                    console.log('No prefix found. Add a data-filepond-prefix to the input element (the prefix of the current model). e.g. (data-filepond-prefix="user")');
                }
                if (!inputElement.dataset.filepondMime) {
                    console.log('No mime found. Add a data-filepond-mime to the input element. e.g. (data-filepond-mime="image")');
                }
                if (!inputElement.dataset.filepondColumn) {
                    console.log('No column attribute assigned to FilePond. Add a data-filepond-column to the input element. e.g. (data-filepond-column="logo")');
                    return false;
                }
                pond.allowMultiple = (inputElement.dataset.filepondMaxFiles > 1) ? true : false;
                pond.allowFileSizeValidation = true;
                pond.maxFiles = (typeof inputElement.dataset.filepondMaxFiles !== 'undefined') ? inputElement.dataset.filepondMaxFiles : 1;
                pond.maxFileSize = inputElement.dataset.filepondMaxFileSize ? inputElement.dataset.filepondMaxFileSize : window.DCMS.maxSizeServer;
                pond.name = inputElement.dataset.filepondColumn + "[]";
                pond.instantUpload = (inputElement.dataset.filepondInstantUpload) ? inputElement.dataset.filepondInstantUpload : window.DCMS.filePond.instantUpload;
                pond.allowRevert = (inputElement.dataset.filepondAllowRevert) ? inputElement.dataset.filepondAllowRevert : window.DCMS.filePond.allowRevert;
                pond.allowPaste = false;
                pond.onerror = () => {
                    window.DCMS.haltSubmit();
                };
                pond.onprocessfile = (error, file) => {
                    window.DCMS.haltSubmit();
                    if (!error) {
                        window.DCMS.fileArray.push({
                            "input": pond.name,
                            "file": file.serverId
                        });

                        // Insert copy button if file has been uploaded
                        let buttonToInsert = `<button class="filepond--file-action-button filepond--action-copy-item-processing" type="button" data-filepond-copy-button="${pond.name.replace('[]', '')}-copy" data-dcms-action="copy" data-dcms-file="${file.serverId}" data-align="right" style="transform: translate3d(0px, 0px, 0px) scale3d(1, 1, 1); opacity: 1;top: 2.35em">
                        <i class="fas fa-copy" style="color: white;font-size: 10px;margin-bottom: 4px;"></i>
                        </button>`;
                        let insertAfter = "#" + pond.name.replace('[]', '') + ".filepond--root.filepond--hopper";
                        insertAfter = document.querySelector(insertAfter);
                        if (insertAfter) {
                            insertAfter = insertAfter.querySelector('.filepond--action-revert-item-processing');
                            insertAfter.insertAdjacentHTML('afterend', buttonToInsert);
                        }

                        if (document.querySelectorAll('[data-type=jexcel]').length > 0) {
                            document.querySelectorAll('[data-type=jexcel]').forEach(function (table) {
                                if (document.querySelector(inputElement.dataset.filepondTableSelector)) {
                                    window.axios({
                                        method: 'GET',
                                        url: file.serverId,
                                        responseType: 'text',
                                        headers: {
                                            'X-CSRF-TOKEN': window.DCMS.csrf,
                                            "Content-type": "application/x-www-form-urlencoded",
                                            'X-Requested-With': 'XMLHttpRequest',
                                        }
                                    }).then(function (response) {
                                        parseData = window.Papa.parse(response.data);
                                        for (const t in window.DCMS.jExcelTables) {
                                            let jExcelTable = window.DCMS.jExcelTables[t];
                                            if (jExcelTable.el == table) {
                                                jExcelTable.setData(parseData.data, false);
                                            }
                                        }
                                    });
                                }
                            });
                        }

                        copyControlsEvents();
                        window.DCMS.enableSubmit();
                    }
                };
                pond.server = {
                    method: method,
                    headers: {
                        'X-CSRF-TOKEN': window.DCMS.csrf,
                        'accept': 'application/json'
                    },
                    process: {
                        url: (inputElement.dataset.filepondProcessUrl) ? inputElement.dataset.filepondProcessUrl : '/dcms/file/process/' + inputElement.dataset.filepondPrefix + '/' + inputElement.dataset.filepondMime + '/' + inputElement.dataset.filepondColumn,
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
                            'X-CSRF-TOKEN': window.DCMS.csrf,
                            "Content-Type": "application/json",
                        },
                        url: (inputElement.dataset.filepondRevertUrl) ? inputElement.dataset.filepondRevertUrl : '/dcms/file/revert/' + inputElement.dataset.filepondPrefix + '/' + inputElement.dataset.filepondMime + '/' + inputElement.dataset.filepondColumn,
                        method: 'DELETE',
                    }
                };
                window.DCMS.filePonds.push(pond);

                let filePonds = document.querySelectorAll('[data-type=filepond]');
                if (filePonds) {
                    for (const f in filePonds) {
                        try {
                            filePonds[f].style.visibility = 'visible';
                            filePonds[f].style.display = 'inherit';
                        } catch (error) {

                        }
                    }
                }
            }

            document.querySelectorAll('input[data-type=filepond]').forEach(function (element) {
                MakePond(element);
            });
            document.querySelectorAll('.filepond--drop-label').forEach(element => element.classList.add('input-group-text'));
        });

        copyControlsEvents();
    }
};
window.DCMS.filePond();
