<?php echo head(array('title' => 'Simple Vocabulary Plus')); ?>
<script type="text/javascript" charset="utf-8">
	//<![CDATA[
	jQuery(document).ready(function() {
		jQuery('#av_element-id').change(function() {
			jQuery.post(
				<?php echo js_escape(url('simple-vocab-plus/endpoint/vocab')); ?>,
				{element_id: jQuery('#av_element-id').val()},
				function(data) {
					jQuery('#suggest-endpoint').val(data);
				}
			);
		});
		
		jQuery('#ex_element-id').change(function() {
			jQuery.ajax({
				url: <?php echo js_escape(url(array('action' => 'element-texts', 'format' => 'html'))); ?>,
				data: {element_id: jQuery('#ex_element-id').val()},
				success: function(data) {
					jQuery('#texts').html(data);
				}
			});
		});
		
		jQuery('#tabs').tabs({
			create: function() {
				var widget = jQuery(this).data('ui-tabs');
				jQuery(window).on('hashchange', function() {
					widget.option('active', widget._getIndex(location.hash));
				});
			}
		});
	
		jQuery('#nv_definetext').on({
			'dragenter dragover': function(event) {
				event.preventDefault();
				event.stopPropagation();
				jQuery(this).css({'background-color': '#eeeeee', 'border': 'inset'});
				event.originalEvent.dataTransfer.dropEffect = 'copy';
				return false;
			},
			'dragleave': function(event) {
				event.preventDefault();
				event.stopPropagation();
				jQuery(this).css({'background-color': '', 'border': ''});
				return false;
			},
			'drop': function(event) {
				event.preventDefault();
				event.stopPropagation();
				jQuery(this).css({'background-color': '', 'border': ''});
				
				var file = event.originalEvent.dataTransfer.files[0];
				if (file.type == 'text/plain' || file.type == 'text/html') {
					var reader = new FileReader();
					reader.onload = function(event) {
						jQuery('#nv_definetext').val(event.target.result.trim());
					};
					reader.readAsText(file, 'UTF-8');
				} else {
					alert('<?php echo __('File format not supported.') ?>');
				}
				return false;
			}
		});
	});
	//]]>
</script>
<?php echo flash(); ?>
<div id="tabs">
	<ul>
		<li><a href="#tab1"><?php echo __('Assign Vocabulary'); ?></a></li>
		<li><a href="#tab2"><?php echo __('Create Vocabulary'); ?></a></li>
		<li><a href="#tab3"><?php echo __('Edit Vocabulary'); ?></a></li>
		<li><a href="#tab4"><?php echo __('Examine Element'); ?></a></li>
	</ul>
	
	<div id="tab1" style="height:1%; overflow:hidden">
		<form method="post" action="<?php echo url('simple-vocab-plus/suggest/add'); ?>">
			<section class="nine columns alpha">
				<h2><?php echo __('Assign Vocabulary to Metadata Element'); ?></h2>
				<div class="field">
					<div id="element-id-label" class="two columns alpha">
						<label for="element-id"><?php echo __('Element'); ?></label>
					</div>
					<div class="inputs five columns omega">
						<p class="explanation">
							<?php echo __('Select an element to assign it a vocabulary. Elements already assigned a vocabulary are marked with an asterisk (*).'); ?>
						</p>
						<?php echo $this->formSelect('av_element-id', null, array('id' => 'av_element-id'), $this->form_element_options) ?>
					</div>
				</div>
				<div class="field">
					<div id="nv_local-label" class="two columns alpha">
						<label for="nv_local"><?php echo __('Source'); ?></label>
					</div>
					<div class="inputs five columns omega">
						<p class="explanation"><?php
							echo __("Choose the source of terms (note: if uncertain of values' consistency, you might want to <a href=\"#tab4\"><strong>examine them first</strong></a>):");
						?></p>
						<input type="radio" name="av_source" value="self" id="av_self-radio">
						<?php echo __('Self assign: values are retrieved from the element\'s recorded ones'); ?>
						<br>
						<input type="radio" name="av_source" value="multi" id="av_multi-radio">
						<?php echo __('Multiple assign: values are retrieved from multiple elements of the repository'); ?>
						<br>
						<input type="radio" name="av_source" value="vocab" id="av_vocab-radio">
						<?php echo __('Vocabulary: values are retrieved from a local\remote custom vocabulary'); ?>
					</div>
				</div>
				<div class="field" id="av_multi-field">
					<div id="multi-id-label" class="two columns alpha">
						<label for="multi-id"><?php echo __('Source element'); ?></label>
					</div>
					<div class="inputs five columns omega">
						<p class="explanation">
							<?php echo __('Select one or more elements to use as source.'); ?>
						</p>
						<?php echo $this->formSelect('av_multi-id', null, array('id' => 'av_multi-id'), $this->form_element_options) ?>
					</div>
				</div>
				<div class="field" id="av_vocab-field">
					<div id="suggest-endpoint-label" class="two columns alpha">
						<label for="suggest-endpoint"><?php echo __('Source vocabulary'); ?></label>
					</div>
					<div class="inputs five columns omega">
						<p class="explanation">
							<?php echo __('Select a vocabulary to enable the autosuggest feature for the above element.', '</a>'); ?>
						</p>
						<?php echo $this->formSelect('av_vocab-id', null, array('id' => 'av_vocab-id'), $this->form_vocab_options); ?>
					</div>
				</div>
				<div class="field" id="av_enforce-field">
					<div id="enforced-label" class="two columns alpha">
						<label for="enforced"><?php echo __('Enforce values'); ?></label>
					</div>
					<div class="inputs five columns omega">
						<p class="explanation">
							<?php echo __('If selected, user will see a dropdown box and will have to choose one of the suggested values. Works only with local/remote vocabularies. Deselect the option to disable the feature.'); ?>
						</p>
						<?php echo $this->formCheckbox('av_enforced', null, '0', array('1', '0')) ?>
					</div>
				</div>
				<div id="assign" class="field">
					<?php echo $this->formSubmit('av_add-button', __('Assign Vocabulary'), array('class' => 'submit green button', 'id'=>'av_add-button')); ?>
				</div>
			</section>
			<?php echo $this->csrf; ?>
			<section class="nine columns alpha">
			<fieldset id="fieldset-svpAssignmentsSet" class="svpFieldset" style="border: 1px solid #cccccc; padding: 15px; margin: 7px">
				<legend style="font-weight: bold; padding: 5px"><?php echo __('Current Assignments'); ?></legend>
				<div>
				<?php if ($this->assignments): ?>
					<table>
						<thead>
							<tr>
								<th><?php echo __('Element Set'); ?></th>
								<th><?php echo __('Element'); ?></th>
								<th><?php echo __('Type'); ?></th>
								<th><?php echo __('Enforced'); ?></th>
								<th><?php echo __('Vocabulary'); ?></th>
								<th style="width:22%;"></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($this->assignments as $assignment): ?>
							<tr>
								<td class="element_set_name" data-svp-element-set-id="<?php echo $assignment['element_set_id']; ?>"><?php echo __($assignment['element_set_name']); ?></td>
								<td class="element_name" data-svp-element-id="<?php echo $assignment['element_id']; ?>"><?php echo __($assignment['element_name']); ?></td>
								<td class="element_name" data-svp-type="<?php echo $assignment['type']; ?>"><?php echo __($assignment['type']); ?></td>
								<td class="element_name" data-svp-enforced="<?php echo $assignment['enforced']; ?>"><?php echo ($assignment['enforced'] ? __('true') : __('false')); ?></td>
								<td class="authority_vocabulary" data-svp-vocab-id="<?php echo $assignment['authority_vocabulary_id']; ?>">
									<?php echo $assignment['authority_vocabulary']; ?>
								</td>
								<td>
									<a class="delete-confirm right" href="<?php echo url('simple-vocab-plus/suggest/delete-confirm/id/' . $assignment['suggest_id']); ?>"><button class="red button" style="margin:0" type="button"><?php echo __('Delete') ?></button></a>
								</td>
							</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				<?php else: ?>
					<p><?php echo __('There are no suggest assignments.'); ?></p>
				<?php endif; ?>
				</div>
			</fieldset>
			</section>
		</form>
	</div>
	
	<div id="tab2" style="height:1%; overflow:hidden">
		<section class="ten columns alpha" id="tab2" style="height:1%; overflow:hidden">
			<h2><?php echo __('Create new Vocabulary'); ?></h2>
			<form method="post" action="<?php echo url('simple-vocab-plus/vocabulary/add'); ?>">
				<div class="field" id="nv_name-field">
					<div id="nv_name-label" class="two columns alpha">
						<label for="nv_name"><?php echo __('Name'); ?></label>
					</div>
					<div class="inputs five columns omega">
						<p class="explanation"><?php echo __('Provide a name for your new vocabulary.'); ?></p>
						<input type="text" name="nv_name">
					</div>
				</div>
				<div class="field">
					<div id="nv_local-label" class="two columns alpha">
						<label for="nv_local"><?php echo __('Location'); ?></label>
					</div>
					<div class="inputs five columns omega">
						<p class="explanation"><?php
							echo __("Choose whether you would like the repository to store your new vocabulary's terms, or rather to store the vocabulary in the cloud (using Google Drive or GitPages or other similar services).");
						?></p>
						<input type="radio" name="nv_local" value="local" id="nv_local-radio">
						<?php echo __('Define vocabulary and manually edit it here'); ?>
						<br>
						<input type="radio" name="nv_local" value="remote" id="nv_cloud-radio">
						<?php echo __('Import vocabulary from cloud'); ?>
					</div>
				</div>
				<div class="field" id="nv_url-field">
					<div id="nv_url-label" class="two columns alpha">
						<label for="nv_url"><?php echo __('URL'); ?></label>
					</div>
					<div class="inputs five columns omega">
						<p class="explanation">
							<?php echo __('If the vocabulary is stored in the cloud, this field shows the file URL for the vocabulary; any web server or cloud service can host this file. A plain text file is expected with one term per line; a line consisting of a sequence of 3 hyphens will be displayed as an empty line and can be used as separator for subgroups of terms; a line starting with 3 asterisks will be displayed as an unselectable, bolded subgroup title.'); ?>
						</p>
					<input type="text" name="nv_url">
					</div>
				</div>
				<div class="field" id="nv_definetext-field">
					<div id="nv_definetext-label" class="two columns alpha">
						<label for="nv_definetext"><?php echo __('Terms list'); ?></label>
					</div>
					<div class="inputs five columns omega">
						<p class="explanation">
							<?php echo __('Input all terms below, one term per line; a line consisting of a sequence of 3 hyphens will be displayed as an empty line and can be used as separator for subgroups of terms; a line starting with 3 asterisks will be displayed as an unselectable, bolded subgroup title. You can also drag and drop a text file here, to automatically paste its content.'); ?>
						</p>
						<textarea rows="15" columns="30" name="nv_definetext" id="nv_definetext" placeholder="<?php echo __('Input all terms or drop a text file...') ?>" style="white-space: pre-wrap"></textarea>
					</div>
				</div>
				<div id="create" class="field">
					<?php echo $this->formSubmit('nv_add-button', __('Create Vocabulary'), array('class' => 'submit green button', 'id'=>'nv_add-button')); ?>
				</div>
				<?php echo $this->csrf;?>
			</form>
		</section>
	</div>
	
	<div id="tab3" style="height:1%; overflow:hidden">
		<section class="ten columns alpha">
			<h2><?php echo __('Edit existing Vocabulary'); ?></h2>
			<form method="post" action="<?php echo url('simple-vocab-plus/vocabulary/edit'); ?>">
				<div class="field">
					<div id="ev_name-label" class="two columns alpha">
						<label for="ev_name"><?php echo __('Name'); ?></label>
					</div>
					<div class="inputs five columns omega">
						<p class="explanation"><?php echo __('Select a Vocabulary to edit.'); ?></p>
						<?php echo $this->formSelect('ev_name', null, array('id' => 'ev_name'), $this->form_vocab_options); ?>
					</div>
				</div>
				<div class="field" id="ev_url-field">
					<div id="ev_url-label" class="two columns alpha">
						<label for="ev_url"><?php echo __('URL'); ?></label>
					</div>
					<div class="inputs five columns omega">
						<p class="explanation">
							<?php echo __('If the vocabulary is stored in the cloud, this field shows the file URL for the vocabulary. Any web server or cloud service can host this file (we recommend GitPages).'); ?>
						</p>
						<input type="text" name="ev_url" id="ev_url">
					</div>
				</div>
				<div class="field" id="ev_edittext-field">
					<div id="ev_edittext-label" class="two columns alpha">
						<label for="ev_edittext"><?php echo __('Terms list'); ?></label>
					</div>
					<div class="inputs five columns omega">
						<p class="explanation">
							<?php echo __('The vocabulary\'s terms are displayed here, one term per line; a line consisting of a sequence of 3 hyphens will be displayed as an empty line and can be used as separator for subgroups of terms; a line starting with 3 asterisks will be displayed as an unselectable, bolded subgroup title.'); ?>
						</p>
						<textarea rows="15" columns="30" name="ev_edittext" id="ev_edittext"></textarea>
					</div>
				</div>
				<div id="editfield" class="field">
					<?php echo $this->formSubmit('edit-vocabulary', __('Save Changes'), array('class' => 'submit green button', 'id'=>'ev_edit-button')); ?>
					<a class="delete-confirm" id="ev_delete-link" href="<?php echo url('simple-vocab-plus/vocabulary/delete-confirm/id/'); ?>"><button id="ev_delete-button" class="red button" style="margin:0" type="button"><?php echo __('Delete Vocabulary') ?></button></a>
				</div>
				<?php echo $this->csrf;?>
			</form>
		</section>
	</div>
	
	<div id="tab4" style="height:1%; overflow:hidden">
		<section class="ten columns alpha">
			<h2><?php echo __('Examine Element\'s values'); ?></h2>
			<div class="field">
				<div class="inputs seven columns alpha">
					<p class="explanation">
						<?php echo __('Before creating a vocabulary with values retrieved from the ones stored in the repository for a particular element, you might want to examine their consistency. Consider in fact the following caveats:'); ?>
					</p>
					<ul>
						<li><?php echo __('Vocabulary terms must not contain newlines (line breaks).'); ?></li>
						<li><?php echo __('Vocabulary terms are typically short and concise. If your existing texts are otherwise, avoid using a controlled vocabulary for this element.'); ?></li>
						<li><?php echo __('Existing texts that are not in the vocabulary will be preserved - however, they cannot be selected in the item edit page, and will be deleted once you save the item.'); ?></li>
					</ul>
				</div>
			</div>
			<div class="field">
				<div id="ex-element-id-label" class="two columns alpha">
					<label for="ex-element-id"><?php echo __('Element'); ?></label>
				</div>
				<div class="inputs five columns omega">
					<p class="explanation">
						<?php echo __('Select an element to examine.'); ?>
					</p>
					<?php echo $this->formSelect('ex_element-id', null, array('id' => 'ex_element-id'), $this->form_element_options) ?>
				</div>
			</div>
		</section>
		<section id="texts" class="nine columns alpha"></section>
	</div>

</div>
<?php
echo foot();
