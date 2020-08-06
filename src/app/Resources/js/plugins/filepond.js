import * as FilePond from './assets/filepond.min.js';
import FilePondPluginImagePreview from './assets/filepond.preview.min.js';
import FilePondPluginFileValidateSize from './assets/filepond.maxsize.min.js';

if (document.querySelectorAll('[data-type=filepond]').length > 0) {

    /**
    *
    *  Register any plugins
    *
    */

    FilePond.registerPlugin(FilePondPluginImagePreview);
    FilePond.registerPlugin(FilePondPluginFileValidateSize);

    FilePond.setOptions({
        labelIdle: Lang('Drag & drop your files or ') + '<span class=\'filepond--label-action\'>' + Lang('Browse') + '</span>',
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
        const pond = FilePond.create(inputElement);
        pond.allowMultiple = (inputElement.dataset.maxFiles > 1) ? true : false;
        pond.maxFiles = inputElement.dataset.maxFiles;
        pond.maxSize = maxSizeServer;
        pond.name = inputElement.dataset.name+"[]";
        pond.instantUpload = true;
        // pond.allowProcess = false;
        pond.allowRevert = true,
        pond.onerror = (res) => {
                HaltSubmit();
        }
        pond.onprocessfile = (error, file) => {
            HaltSubmit();
            if (!error){
                window.fileArray.push({
                    "input": pond.name,
                    "file": file.serverId
                })
                EnableSubmit();
            }
        }
        pond.server = {
            method: method,
            headers: {
                'X-CSRF-TOKEN': window.csrf
            },
            process: {
                url: '/file/process/'+inputElement.dataset.mime,
                onerror: (res) => {
                    let fileResponse;
                    try {
                        fileResponse = JSON.parse(res);
                    } catch (error) {
                        fileResponse = Lang('Something went wrong. File is invalid or too big (max ')+maxSizeServer+' MB)';
                    }
                    if (FilePondJQAlerts == true){
                        Alert('error', Lang('Upload failed'), Lang(fileResponse), {
                            confirm: {
                                text: Lang('Ok'),
                                btnClass: 'btn-danger',
                            },
                        });
                    } else {
                        alert(JSON.parse(res)['message']);
                    }
                }
            },
            revert: {
                headers: {
                    'X-CSRF-TOKEN': window.csrf,
                    "Content-Type": "application/json",
                },
                url: '/file/delete/'+inputElement.dataset.mime,
                method: 'DELETE',
            }
        }
    }
    
    window.addEventListener('DOMContentLoaded', (event) => {
        const inputElement = document.querySelector('input[data-type=filepond]');
        document.querySelectorAll('input[data-type=filepond]').forEach(function (element){
            MakePond(element);
        });
        document.querySelectorAll('.filepond--drop-label').forEach(element => element.classList.add('input-group-text'));
    });
}
