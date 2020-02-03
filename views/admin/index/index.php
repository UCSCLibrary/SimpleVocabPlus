<?php echo head(array('title' => 'Simple Vocabulary Plus')); ?>
<script type="text/javascript" charset="utf-8">
//<![CDATA[
jQuery(document).ready(function() {
	jQuery('#element-id').change(function() {
		jQuery.post(
			<?php echo js_escape(url('simple-vocab-plus/endpoint/vocab')); ?>,
			{element_id: jQuery('#element-id').val()},
			function(data) {
				jQuery('#suggest-endpoint').val(data);
			}
		);
	});
	jQuery('#ex-element-id').change(function() {
		jQuery.ajax({
			url: <?php echo js_escape(url(array('action' => 'element-texts', 'format' => 'html'))); ?>,
			data: {element_id: jQuery('#ex-element-id').val()},
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
			<section class="seven columns alpha">
				<h2><?php echo __('Assign Vocabulary to Metadata Element'); ?></h2>
				<div class="field">
					<div id="element-id-label" class="two columns alpha">
						<label for="element-id"><?php echo __('Element'); ?></label>
					</div>
					<div class="inputs five columns omega">
						<p class="explanation">
							<?php echo __('Select an element to assign it a Simple Vocabulary. Elements already assigned an authority/vocabulary are marked with an asterisk (*).'); ?>
						</p>
						<?php echo $this->formSelect('element_id', null, array('id' => 'element-id'), $this->form_element_options) ?>
					</div>
				</div>
				<div class="field">
					<div id="enforced-label" class="two columns alpha">
						<label for="enforced"><?php echo __('Enforce values'); ?></label>
					</div>
					<div class="inputs five columns omega">
						<p class="explanation">
							<?php echo __('If selected, user will see a dropdown box and will have to choose one of the suggested values. Works only with local/remote vocabularies. Deselect the option to disable the feature.'); ?>
						</p>
						<?php echo $this->formCheckbox('enforced', null, '0', array('1', '0')) ?>
					</div>
				</div>
				<div class="field">
					<div id="self-assign-label" class="two columns alpha">
						<label for="self-assign"><?php echo __('Self assign'); ?></label>
					</div>
					<div class="inputs five columns omega">
						<p class="explanation">
							<?php echo __('If selected, values are retrieved from repository instead of vocabulary; if uncertain of values\' consistency, you might want to <a href="#tab4"><strong>examine them first</strong></a>. Deselect the option to disable the feature.'); ?>
						</p>
						<?php echo $this->formCheckbox('self-assign', null, '0', array('1', '0')) ?>
					</div>
				</div>
				<div class="field">
					<div id="suggest-endpoint-label" class="two columns alpha">
						<label for="suggest-endpoint"><?php echo __('Vocabulary'); ?></label>
					</div>
					<div class="inputs five columns omega">
						<p class="explanation">
							<?php echo __('Select a vocabulary to enable the autosuggest feature for the above element.', '</a>'); ?>
						</p>
						<?php echo $this->formSelect('vocab_id', null, array('id' => 'assign-vocab'), $this->form_vocab_options); ?>
					</div>
				</div>
			</section>
			<section class="two columns omega">
				<div id="edit" class="panel">
					<?php echo $this->formSubmit('edit-element-suggest', __('Assign Vocabulary'), array('class' => 'submit big green button')); ?>
				</div>
			</section>
			<?php echo $this->csrf; ?>
			<section class="nine columns alpha">
				<div>
					<h2><?php echo __('Current Assignments'); ?></h2>
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
			</section>
		</form>
	</div>
	
	<div id="tab2" style="height:1%; overflow:hidden">
		<section class="ten columns alpha" id="tab2" style="height:1%; overflow:hidden">
			<h2><?php echo __('Create new Vocabulary'); ?></h2>
			<form method="post" action="<?php echo url('simple-vocab-plus/vocabulary/add'); ?>">
				<div class="field" id="nv-name-field">
					<div id="nv-name-label" class="two columns alpha">
						<label for="nv-name"><?php echo __('Name'); ?></label>
					</div>
					<div class="inputs five columns omega">
						<p class="explanation"><?php echo __('Provide a name for your new vocabulary.'); ?></p>
						<input type="text" name="nv-name">
					</div>
				</div>
				<div class="field">
					<div id="nv-local-label" class="two columns alpha">
						<label for="nv-local"><?php echo __('Location'); ?></label>
					</div>
					<div class="inputs five columns omega">
						<p class="explanation"><?php
							echo __('Choose whether you would like the repository to store your new vocabulary\'s terms, or rather to store the vocabulary in the cloud (using Google Drive or GitPages or other similar services.)');
						?></p>
						<input type="radio" name="nv-local" value="local" id="nv-localradio">
						<?php echo __('Define vocabulary and manually edit it here'); ?>
						<br>
						<input type="radio" name="nv-local" value="remote" id="nv-cloudradio">
						<?php echo __('Syncronize vocabulary with cloud'); ?>
					</div>
				</div>
				<div class="field" id="nv-url-field">
					<div id="nv-url-label" class="two columns alpha">
						<label for="nv-url"><?php echo __('URL'); ?></label>
					</div>
					<div class="inputs five columns omega">
						<p class="explanation">
							<?php echo __('If the vocabulary is stored in the cloud, this field shows this field shows the file URL for the vocabulary. Any web server or cloud service can host this file (we recommend GitPages). A plain text file is expected with one term per line.'); ?>
						</p>
					<input type="text" name="nv-url">
					</div>
				</div>
				<div class="field" id="nv-definetext-field">
					<div id="nv-definetext-label" class="two columns alpha">
						<label for="nv-definetext"><?php echo __('Terms list'); ?></label>
					</div>
					<div class="inputs five columns omega">
						<p class="explanation">
							<?php echo __('Input all terms below, one term per line. You can also drag and drop text files here, to paste their contents automatically.'); ?>
						</p>
						<textarea rows="15" columns="30" name="nv-definetext"></textarea>
					</div>
				</div>
				<div id="create" class="field">
					<?php echo $this->formSubmit('new-vocabulary', __('Create Vocabulary'), array('class' => 'submit green button', 'id'=>'nv-createbutton')); ?>
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
					<div id="ev-name-label" class="two columns alpha">
						<label for="ev-name"><?php echo __('Name'); ?></label>
					</div>
					<div class="inputs five columns omega">
						<p class="explanation"><?php echo __('Select a Vocabulary to edit.'); ?></p>
						<?php echo $this->formSelect('ev-name', null, array('id' => 'ev-name'), $this->form_vocab_options); ?>
					</div>
				</div>
				<div class="field" id="ev-url-field">
					<div id="ev-url-label" class="two columns alpha">
						<label for="ev-url"><?php echo __('URL'); ?></label>
					</div>
					<div class="inputs five columns omega">
						<p class="explanation">
							<?php echo __('If the vocabulary is stored in the cloud, this field shows this field shows the file URL for the vocabulary. Any web server or cloud service can host this file (we recommend GitPages). A plain text file is expected, with one term per line. To delete this vocabulary, empty the URL field and save.'); ?>
						</p>
						<input type="text" name="ev-url" id="ev-url">
					</div>
				</div>
				<div class="field" id="ev-edittext-field">
					<div id="ev-edittext-label" class="two columns alpha">
						<label for="ev-edittext"><?php echo __('Terms list'); ?></label>
					</div>
					<div class="inputs five columns omega">
						<p class="explanation">
							<?php echo __('The vocabulary\'s terms are displayed here, one term per line. To delete this vocabulary, empty terms list and save.' ); ?>
						</p>
						<textarea rows="15" columns="30" name="ev-edittext" id="ev-edittext"></textarea>
					</div>
				</div>
				<div id="editfield" class="field">
					<?php echo $this->formSubmit('edit-vocabulary', __('Save Changes'), array('class' => 'submit green button', 'id'=>'ev-edit-button')); ?>
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
					<?php echo $this->formSelect('ex-element_id', null, array('id' => 'ex-element-id'), $this->form_element_options) ?>
				</div>
			</div>
		</section>
		<section id="texts" class="ten columns alpha"></section>
	</div>

</div>
<?php
echo foot();