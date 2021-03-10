/**
*
*  Requests with Axios and custom error/success handling
*
*/

require('../requests.js');

/**
*
*  SweetAlert
*
*/

if (typeof Swal == 'undefined' && (window.DCMS.config.plugins.sweetalert2 && window.DCMS.config.plugins.sweetalert2.enable !== false)) {
    window.DCMS.loadJS(window.DCMS.config.plugins.sweetalert2);
}

window.DCMS.sweetAlert = {};

// Messages
window.DCMS.sweetAlert.confirmButtonText = Lang("OK");
window.DCMS.sweetAlert.cancelButtonText = Lang("Cancel");

/**
*
*  Spotlight
*
*/

require('../../../../public/js/dcms/assets/spotlight.js');

/**
*
*  Use Vue and/or vanilla JS for DCMS plugins
*  Pick the desired plugins in the _setup.js files
*
*/

require('./vanilla/_setup.js');
require('./vue/_setup.js');
