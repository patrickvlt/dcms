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
        if(!inputElement.dataset.prefix){
            console.log('No prefix found. Add a data-prefix to the input element. e.g. (data-prefix="user")')
        }
        pond.allowMultiple = (inputElement.dataset.maxFiles > 1) ? true : false;
        pond.maxFiles = inputElement.dataset.maxFiles;
        pond.maxSize = window.FilePondMaxFileSize;
        pond.name = inputElement.dataset.name+"[]";
        pond.instantUpload = (inputElement.dataset.instantUpload) ? inputElement.dataset.instantUpload : window.FilePondInstantUpload;
        // pond.allowProcess = false;
        pond.allowRevert = (inputElement.dataset.allowRevert) ? inputElement.dataset.allowRevert : window.FilePondAllowRevert;
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
                'X-CSRF-TOKEN': window.csrf,
                'accept': 'application/json'
            },
            process: {
                url: '/'+inputElement.dataset.prefix+'/file/process/'+inputElement.dataset.mime+'/'+inputElement.name,
                onerror: (res) => {
                    let fileResponse, errors = [];
                    fileResponse = JSON.parse(res);
                    $.each(fileResponse, function (x, error) { 
                        errors.push(error[0]);
                    });
                    Alert('error', Lang('Upload failed'), errors, {
                        confirm: {
                            text: Lang('Ok'),
                            btnClass: 'btn-danger',
                        },
                    });
                }
            },
            revert: {
                headers: {
                    'X-CSRF-TOKEN': window.csrf,
                    "Content-Type": "application/json",
                },
                url: '/'+inputElement.dataset.prefix+'/file/revert/'+inputElement.dataset.mime+'/'+inputElement.name,
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
