if (document.querySelectorAll('[data-type=filepond]').length > 0) {

    hasLoaded(['FilePond','FilePondPluginImagePreview','FilePondPluginFileValidateSize'],function(){
        window.ponds = [];
        /**
         *
         *  Register any plugins
         *
         */

        FilePond.registerPlugin(FilePondPluginImagePreview);
        FilePond.registerPlugin(FilePondPluginFileValidateSize);

        FilePond.setOptions({
            labelIdle: Lang('Drag & drop your files or ') + '<span class=\'filepond--label-action\'>' + Lang('browse') + '</span>',
            labelInvalidField: Lang('Field contains invalid files'),
            labelFileWaitingForSize: Lang('Waiting for size'),
            labelFileSizeNotAvailable: Lang('Size not available'),
            labelFileLoading: Lang('Loading'),
            labelFileLoadError: Lang('Error during load'),
            labelFileProcessing: Lang('Uploading'),
            labelFileProcessingComplete: Lang('Upload complete'),
            labelFileProcessingAborted: Lang('Upload cancelled'),
            labelFileProcessingError: Lang('Error during upload'),
            labelFileProcessingRevertError: Lang('Error during revert'),
            labelFileRemoveError: Lang('Error during remove'),
            labelTapToCancel: Lang('tap to cancel'),
            labelTapToRetry: Lang('tap to retry'),
            labelTapToUndo: Lang('tap to undo'),
            labelButtonRemoveItem: Lang('Remove'),
            labelButtonAbortItemLoad: Lang('Abort'),
            labelButtonRetryItemLoad: Lang('Retry'),
            labelButtonAbortItemProcessing: Lang('Cancel'),
            labelButtonUndoItemProcessing: Lang('Undo'),
            labelButtonRetryItemProcessing: Lang('Retry'),
            labelButtonProcessItem: Lang('Upload'),
            labelMaxFileSizeExceeded: Lang('File is too large'),
            labelMaxFileSize: Lang('Maximum file size is {filesize}'),
            labelMaxTotalFileSizeExceeded: Lang('Maximum total size exceeded'),
            labelMaxTotalFileSize: Lang('Maximum total file size is {filesize}'),
            labelFileTypeNotAllowed: Lang('File of invalid type'),
            fileValidateTypeLabelExpectedTypes: Lang('Expects {allButLastType} or {lastType}'),
            imageValidateSizeLabelFormatError: Lang('Image type not supported'),
            imageValidateSizeLabelImageSizeTooSmall: Lang('Image is too small'),
            imageValidateSizeLabelImageSizeTooBig: Lang('Image is too big'),
            imageValidateSizeLabelExpectedMinSize: Lang('Minimum size is {minWidth} × {minHeight}'),
            imageValidateSizeLabelExpectedMaxSize: Lang('Maximum size is {maxWidth} × {maxHeight}'),
            imageValidateSizeLabelImageResolutionTooLow: Lang('Resolution is too low'),
            imageValidateSizeLabelImageResolutionTooHigh: Lang('Resolution is too high'),
            imageValidateSizeLabelExpectedMinResolution: Lang('Minimum resolution is {minResolution}'),
            imageValidateSizeLabelExpectedMaxResolution: Lang('Maximum resolution is {maxResolution}'),
            onprocessfile: () => {
                EnableSubmit();
            }
        });

        window.pondArray = [];
        window.fileArray = [];

        function MakePond(inputElement, method = 'POST') {
            var revertKey, parseData;
            const pond = FilePond.create(inputElement);
            revertKey = (inputElement.dataset.filepondRevertKey) ? '/' + inputElement.dataset.filepondRevertKey : '';
            if (!inputElement.dataset.filepondPrefix) {
                console.log('No prefix found. Add a data-filepond-prefix to the input element (the prefix of the current model). e.g. (data-filepond-prefix="user")')
                return false;
            }
            if (!inputElement.dataset.filepondMime) {
                console.log('No mime found. Add a data-filepond-mime to the input element. e.g. (data-filepond-mime="image")')
                return false;
            }
            if (!inputElement.dataset.filepondColumn) {
                console.log('No column attribute assigned to FilePond. Add a data-filepond-column to the input element. e.g. (data-filepond-column="logo")')
                return false;
            }
            pond.allowMultiple = (inputElement.dataset.filepondMaxFiles > 1) ? true : false;
            pond.maxFiles = inputElement.dataset.filepondMaxFiles ?? 1;
            pond.maxSize = inputElement.dataset.filepondMaxFileSize ? inputElement.dataset.filepondMaxFileSize : window.FilePondMaxFileSize;
            pond.name = inputElement.dataset.filepondColumn + "[]";
            pond.instantUpload = (inputElement.dataset.filepondInstantUpload) ? inputElement.dataset.filepondInstantUpload : window.FilePondInstantUpload;
            pond.allowRevert = (inputElement.dataset.filepondAllowRevert) ? inputElement.dataset.filepondAllowRevert : window.FilePondAllowRevert;
            pond.onerror = (res) => {
                HaltSubmit();
            }
            pond.onprocessfile = (error, file) => {
                HaltSubmit();
                if (!error) {
                    window.fileArray.push({
                        "input": pond.name,
                        "file": file.serverId
                    })
                    if (document.querySelectorAll('[data-type=jexcel]').length > 0) {
                        document.querySelectorAll('[data-type=jexcel]').forEach(function (table) {
                            if (document.querySelector(inputElement.dataset.filepondTableSelector)){
                                $.ajax({
                                    type: "GET",
                                    url: file.serverId,
                                    async: false,
                                    dataType: "text",
                                    success: function (file) {
                                        parseData = Papa.parse(file);
                                        window.parseData = parseData.data;
                                        $(table).jexcel('setData', window.parseData, false);
                                    }
                                });
                            }
                        });
                    }
                    EnableSubmit();
                }
            }
            pond.server = {
                method: method,
                headers: {
                    'X-CSRF-TOKEN': window.csrf,
                    'accept': 'application/json'
                },
                process: {
                    url: (inputElement.dataset.filepondProcessUrl) ? inputElement.dataset.filepondProcessUrl : '/dcms/file/process/' + inputElement.dataset.filepondPrefix + '/' + inputElement.dataset.filepondMime + '/' + inputElement.dataset.filepondColumn,
                    onerror: (res) => {
                        let response, errors = '';
                        try {
                            response = JSON.parse(res);
                        } catch (error) {
                            Swal.fire(Lang('Upload failed'), '', 'error')
                            return;
                        };
                        if (!response.errors) {
                            Swal.fire({
                                title: Lang('Upload failed'),
                                html: Lang('An unknown error has occurred.') + "<br>" + Lang('Contact support if this problem persists.'),
                                icon: "error"
                            })
                        }
                        if (response.errors) {
                            $.each(response.errors, function (x, objWithErrors) {
                                $.each(objWithErrors, function (x, error) {
                                    errors += error + '<br>'
                                });
                            });
                            Swal.fire({
                                title: Lang('Upload failed'),
                                html: errors,
                                icon: "error"
                            })
                        }
                    }
                },
                revert: {
                    headers: {
                        'X-CSRF-TOKEN': window.csrf,
                        "Content-Type": "application/json",
                    },
                    url: (inputElement.dataset.filepondRevertUrl) ? inputElement.dataset.filepondRevertUrl : '/dcms/file/revert/' + inputElement.dataset.filepondPrefix + '/' + inputElement.dataset.filepondMime + '/' + inputElement.dataset.filepondColumn,
                    method: 'DELETE',
                }
            }
            ponds.push(pond);
            $('[data-type=filepond]').show();
        }
        const inputElement = document.querySelector('input[data-type=filepond]');
        document.querySelectorAll('input[data-type=filepond]').forEach(function (element) {
            MakePond(element);
        });
        document.querySelectorAll('.filepond--drop-label').forEach(element => element.classList.add('input-group-text'));
    })
}
