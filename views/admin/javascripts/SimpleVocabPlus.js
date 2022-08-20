jQuery(document).ready(function() {
	var url = 'simple-vocab-plus/vocabulary/get/vocab/';
	
	jQuery('#av_multi-field').hide();
	jQuery('#av_vocab-field').hide();
	jQuery('#av_add-button').prop('disabled', 'disabled');
	jQuery('#nv_definetext-field').hide();
	jQuery('#nv_url-field').hide();
	jQuery('#nv_add-button').prop('disabled', 'disabled');
	jQuery('#ev_url').prop('readonly', true);
	jQuery('#ev_edittext').prop('readonly', true);
	jQuery('#ev_edit-button').prop('disabled', 'disabled');
	jQuery('#ev_delete-button').prop('disabled', 'disabled');

	jQuery('#av_self-radio').click(function() {
		jQuery('#av_multi-field').hide(300);
		jQuery('#av_vocab-field').hide(300);
		av_add_button_toggle();
	});

	jQuery('#av_multi-radio').click(function() {
		jQuery('#av_multi-field').show(300);
		jQuery('#av_vocab-field').hide(300);
		av_add_button_toggle();
	});

	jQuery('#av_vocab-radio').click(function() {
		jQuery('#av_multi-field').hide(300);
		jQuery('#av_vocab-field').show(300);
		av_add_button_toggle();
	});

	jQuery('#av_element-id').change(function() {
		av_add_button_toggle();
	});

	jQuery('#av_multi-id').change(function() {
		av_add_button_toggle();
	});

	jQuery('#av_vocab-id').change(function() {
		av_add_button_toggle();
	});

	function av_add_button_toggle() {
		var status = 'disabled';
		if (jQuery('#av_element-id').val() != '') {
			if (
				jQuery('#av_self-radio').prop('checked') == true || 
				(jQuery('#av_multi-radio').prop('checked') == true && jQuery('#av_multi-id').val() != '') ||
				(jQuery('#av_vocab-radio').prop('checked') == true && jQuery('#av_vocab-id').val() != '')
			) status = false;
		}
		jQuery('#av_add-button').prop('disabled', status);
	}

	jQuery('#nv_name').change(function() {
		jQuery('#nv_add-button').prop('disabled', !(jQuery('#nv_local-radio').prop('checked') || jQuery('#nv_cloud-radio').prop('checked')));
	});

	jQuery('#nv_local-radio').click(function() {
		jQuery('#nv_definetext-field').show(300);
		jQuery('#nv_url-field').hide(300);
		jQuery('#nv_add-button').prop('disabled', (jQuery('#nv_name').val() == ''));
	});

	jQuery('#nv_cloud-radio').click(function() {
		jQuery('#nv_definetext-field').hide(300);
		jQuery('#nv_url-field').show(300);
		jQuery('#nv_add-button').prop('disabled', (jQuery('#nv_name').val() == ''));
	});

	jQuery('#ev_name').change(function() {
		jQuery.ajax({
			dataType: "json",
			url: url + jQuery(this).val(),
			success: function(data){
				jQuery('#ev_edittext').html('');
				jQuery.each(data['terms'], function(i, term) {
					if (term != '') jQuery('#ev_edittext').append(term + "&#13;&#10;");
				});
				jQuery('#ev_edittext').html(jQuery('#ev_edittext').html().slice(0, -2));

				jQuery('#ev_url').val(data['url']);
				if (data['url'] == 'local') {
					jQuery('#ev_url').prop('readonly', true);
					jQuery('#ev_edittext').prop('readonly', false);
				} else {
					jQuery('#ev_url').prop('readonly', false);
					jQuery('#ev_edittext').prop('readonly', true);
				}
				jQuery('#ev_edit-button').prop('disabled', false);
				jQuery('#ev_delete-link').prop('href', 'simple-vocab-plus/vocabulary/delete-confirm/id/' + jQuery('#ev_name').val());
				jQuery('#ev_delete-button').prop('disabled', false);
			}
		});
	});
});
