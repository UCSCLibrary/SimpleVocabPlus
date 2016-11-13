jQuery(document).ready(function() {

    jQuery('#definetext-field').hide();
    jQuery('#nv-urlfield').hide();
    jQuery('#createbutton').attr('disabled','disabled');

    jQuery('#localradio').click(function() {
    jQuery('#definetext-field').show(300);
    jQuery('#nv-urlfield').hide(300);
    jQuery('#createbutton').prop('disabled',false);
    });

    jQuery('#cloudradio').click(function() {
    jQuery('#definetext-field').hide(300);
    jQuery('#nv-urlfield').show(300);
    jQuery('#createbutton').prop('disabled',false);
    });

    var url = 'simple-vocab-plus/vocabulary/get/vocab/';

    jQuery('#edit-vocab').change(function() {
        jQuery.ajax({
            dataType: "json",
            url: url+jQuery(this).val(),
            success: function(data){
            jQuery('#ev-edittext').html('');
            jQuery.each(data['terms'],function(i,term) {
                jQuery('#ev-edittext').append(term+"&#13;&#10;");
            });

            jQuery('#ev-url').val(data['url']);
            if(data['url']=='local') {
                jQuery('#ev-url').prop('disabled','disabled');
                jQuery('#ev-edittext').prop('disabled',false);
            } else {
                jQuery('#ev-url').prop('disabled',false);
                jQuery('#ev-edittext').prop('disabled','disabled');
            }
            }
        });
    });
});
