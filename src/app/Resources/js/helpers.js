/**
 *
 *  When document is completely finished loading
 *
 */

window.DCMS.onComplete = function (yourMethod) {
    var readyStateCheckInterval = setInterval(function () {
        if (document && (document.readyState == 'complete')) {
            clearInterval(readyStateCheckInterval);
            yourMethod();
        }
    }, 200);
};

/**
 *
 *  When document is interactive
 *
 */

window.DCMS.onReady = function (yourMethod) {
    var readyStateCheckInterval = setInterval(function () {
        if (document && (document.readyState == 'interactive' || document.readyState == 'complete')) {
            clearInterval(readyStateCheckInterval);
            yourMethod();
        }
    }, 200);
};

/**
 *
 *  Run callback function when a certain method/function has been loaded
 *
 */

window.DCMS.hasLoaded = function (plugins, yourMethod) {
    plugins = (typeof plugins == 'string') ? [plugins] : plugins;
    var success = 0;
    var readyStateCheckInterval = setInterval(function () {
        plugins.forEach(function (plugin) {
            if (typeof window[plugin] !== 'undefined' || typeof $.fn[plugin] !== 'undefined') {
                clearInterval(readyStateCheckInterval);
                success++;
                if (success == plugins.length) {
                    yourMethod();
                }
            }
        });
    }, 200);
};

window.DCMS.scrollIntoView = function (selector, offset = 0) {
    window.scroll(0, document.querySelector(selector).offsetTop - offset);
};


/**
 *
 *  Spinner on buttons when submitting a form, or uploading files
 *
 */

var spinner = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';

window.DCMS.haltSubmit = function (selector = null) {
    if (!selector) {
        selector = 'button[type=submit]';
    }
    document.querySelectorAll(selector).forEach(element => element.disabled = true);
};
window.DCMS.disableSubmit = function (selector = null) {
    if (!selector) {
        selector = 'button[type=submit]';
    }
    document.querySelectorAll(selector).forEach(function (element) {
        element.innerHTML = spinner + " " + element.innerHTML;
        element.disabled = true;
    });
};
window.DCMS.enableSubmit = function (selector = null) {
    if (!selector) {
        selector = 'button[type=submit]';
    }
    document.querySelectorAll(selector).forEach(function (element) {
        element.innerHTML = element.innerHTML.replace(spinner, '');
        element.disabled = false;
    });
};

/**
 *
 *  Load links in modal
 *
 */

window.DCMS.loadInModal = function (url) {
    var modalEl;
    $.get(url, function (data) {
        modalEl = $('#global_modal');
        modalEl.find('.modal-content').html(data);
        modalEl.modal('show');
        if (modalEl.find('[data-modal-init').length == 1) {
            let callback = modalEl.find('[data-modal-init]').data('modal-init');
            var fn = window[callback];
            if (typeof fn === 'function') {
                fn(modalEl);
            }
        }
    });
};

/**
 *
 *  Copy file from FilePond or Carousel
 */


window.DCMS.copyControls = function () {
    let copyControls = document.querySelectorAll('[data-dcms-action="copy"]');
    if (copyControls) {
        Array.from(copyControls).forEach((copyControl) => {
            try {
                copyControl.removeEventListener('click');
            } catch (error) {
                //
            }
            copyControl.addEventListener('click', function (e) {
                let img = e.currentTarget.dataset.dcmsFile;
                window.DCMS.textToClipBoard(img);
                window.toastr.success(Lang('Image copied to clipboard.'));
            });
        });
    }
};

/**
 *
 *  Copy text to clipboard
 */

window.DCMS.textToClipBoard = function (text) {
    var dummy = document.createElement("textarea");
    document.body.appendChild(dummy);
    dummy.value = text;
    dummy.select();
    document.execCommand("copy");
    document.body.removeChild(dummy);
};