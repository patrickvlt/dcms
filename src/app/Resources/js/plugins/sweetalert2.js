if (typeof Swal == 'undefined' && (window.DCMS.config.plugins.sweetalert2 && window.DCMS.config.plugins.sweetalert2.enable !== false)) {
    window.DCMS.loadJS(window.DCMS.config.plugins.sweetalert2);
}

window.DCMS.sweetAlert = {};

/**
 *
 *  Colors
 *
 */


window.DCMS.sweetAlert.confirmButtonColor = "var(--primary)";
window.DCMS.sweetAlert.cancelButtonColor = "black";

/**
 *
 *  Messages
 *
 */


window.DCMS.sweetAlert.confirmButtonText = Lang("OK");
window.DCMS.sweetAlert.cancelButtonText = Lang("Cancel");