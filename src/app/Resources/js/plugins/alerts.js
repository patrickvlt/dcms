/** 
* 
* 
*  Alerts
* 
*/

require('./assets/jquery-confirm.min.js');

const defaultButtons = function(){
    return {
        confirm: {
            text: Lang('Ok'),
            btnClass: 'btn-primary',
            action: function(){}
        },
        cancel: {
            text: Lang('Cancel'),
            btnClass: 'btn-secondary',
            action: function(){}
        }
    }
}
window.Alert = function (type='success',title,message,sentButtons) {
    alertButtons = (typeof sentButtons !== 'undefined') ? sentButtons : defaultButtons();
    switch (type) {
        case 'success':
            alertColor = 'green';
            alertIcon = '<i class="las la-check-circle text-success"></i>';
            break;
        case 'error':
            alertColor = 'red';
            alertIcon = '<i class="las la-exclamation-triangle text-danger"></i>';
            break;
        case 'warning':
            alertColor = 'orange';
            alertIcon = '<i class="las la-exclamation-triangle text-warning"></i>';
            break;
        case 'info':
            alertColor = 'blue';
            alertIcon = '<i class="las la-info-circle text-info"></i>';
            break;
        case 'question':
            alertColor = 'dark';
            alertIcon = '<i class="las la-question-circle text-question"></i>';
            break;
    }
    $.confirm({
        title: '<span class="jconfirm-icon-c">'+alertIcon+'</span> '+title+'',
        content: message,
        closeIcon: true,
        escapeKey: true,
        draggable: true,
        backgroundDismiss: true,
        type: alertColor,
        typeAnimated: true,
        buttons: alertButtons
    });
 } 