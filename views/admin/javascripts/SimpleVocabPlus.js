jQuery(document).ready(function() {

    jQuery('#nv-definetext-field').hide();
    jQuery('#nv-url-field').hide();
    jQuery('#nv-createbutton').attr('disabled','disabled');
	jQuery('#ev-url').attr('disabled','disabled');
	jQuery('#ev-edittext').attr('disabled','disabled');
    jQuery('#ev-edit-button').attr('disabled','disabled');
	
	jQuery('#enforced').change(function() {
		jQuery('#self-assign').prop('disabled',this.checked);
		jQuery('#self-assign').attr('checked',false);
		if (this.checked) {
			jQuery('#assign-vocab').prop('disabled',false);
		}
	});

	jQuery('#self-assign').change(function() {
		jQuery('#assign-vocab').prop('disabled',this.checked);
    });
	
	jQuery('#nv-name').change(function() {
		jQuery('#nv-createbutton').prop('disabled',!(jQuery('#nv-localradio').prop('checked') || jQuery('#nv-cloudradio').prop('checked')));
	});

    jQuery('#nv-localradio').click(function() {
		jQuery('#nv-definetext-field').show(300);
		jQuery('#nv-url-field').hide(300);
		jQuery('#nv-createbutton').prop('disabled',(jQuery('#nv-name').val() == ''));
    });

    jQuery('#nv-cloudradio').click(function() {
		jQuery('#nv-definetext-field').hide(300);
		jQuery('#nv-url-field').show(300);
		jQuery('#nv-createbutton').prop('disabled',(jQuery('#nv-name').val() == ''));
    });

    var url = 'simple-vocab-plus/vocabulary/get/vocab/';

    jQuery('#ev-name').change(function() {
        jQuery.ajax({
            dataType: "json",
            url: url + jQuery(this).val(),
            success: function(data){
				jQuery('#ev-edittext').html('');
				jQuery.each(data['terms'], function(i, term) {
					jQuery('#ev-edittext').append(term + "&#13;&#10;");
				});

				jQuery('#ev-url').val(data['url']);
				if (data['url'] == 'local') {
					jQuery('#ev-url').attr('disabled','disabled');
					jQuery('#ev-edittext').attr('disabled',false);
				} else {
					jQuery('#ev-url').attr('disabled',false);
					jQuery('#ev-edittext').attr('disabled','disabled');
				}
				jQuery('#ev-edit-button').prop('disabled',false);
			}
		});
    });
});