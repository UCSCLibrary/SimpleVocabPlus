<?php
$simple_vocab_plus_files				= get_option('simple_vocab_plus_files');
$simple_vocab_plus_fields_highlight		= get_option('simple_vocab_plus_fields_highlight');
$simple_vocab_plus_fields_description	= get_option('simple_vocab_plus_fields_description');
$view = get_view();
?>

<h2><?php echo __('Application scope'); ?></h2>

<div class="field">
	<div class="two columns alpha">
		<?php echo $view->formLabel('simple_vocab_plus_files', __('Apply to Files')); ?>
	</div>
	<div class="inputs five columns omega">
		<p class="explanation">
			<?php echo __('If checked, the plugin will be applied to Files metadata too (by default, this plugin only applies to Items metadata).'); ?>
		</p>
		<?php echo $view->formCheckbox('simple_vocab_plus_files', $simple_vocab_plus_files, null, array('1', '0')); ?>
	</div>
</div>

<h2><?php echo __('Warnings'); ?></h2>

<div class="field">
	<div class="two columns alpha">
		<?php echo $view->formLabel('simple_vocab_plus_fields_highlight', __('Highlight autosuggest fields')); ?>
	</div>
	<div class="inputs five columns omega">
		<p class="explanation">
			<?php echo __('Color hex code (e.g.: #ff0000) to highlight the fields that have autosuggest feature applied (blank means no highlight).'); ?>
		</p>
		<?php echo $view->formText('simple_vocab_plus_fields_highlight', $simple_vocab_plus_fields_highlight, null, ''); ?>
	</div>
</div>

<div class="field">
	<div class="two columns alpha">
		<?php echo $view->formLabel('simple_vocab_plus_fields_description', __('Add autosuggest reminder')); ?>
	</div>
	<div class="inputs five columns omega">
		<p class="explanation">
			<?php echo __('Adds a notice to the description of fields that have autosuggest feature applied.'); ?>
		</p>
		<?php echo $view->formCheckbox('simple_vocab_plus_fields_description', $simple_vocab_plus_fields_description, null, array('1', '0')); ?>
	</div>
</div>