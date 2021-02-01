window.DCMS.iCheck = function(){
    window.hasLoaded('iCheck',function(){
        $("input[data-type='iCheck']").iCheck({
            checkboxClass: 'icheckbox_flat-blue mr-2',
            radioClass: 'iradio_flat-blue mr-2'
        });
    });
};
window.DCMS.iCheck();