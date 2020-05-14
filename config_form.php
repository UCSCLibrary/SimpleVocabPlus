<?php
$simple_vocab_plus_files				= get_option('simple_vocab_plus_files');
$simple_vocab_plus_collections			= get_option('simple_vocab_plus_collections');
$simple_vocab_plus_exhibits				= get_option('simple_vocab_plus_exhibits');
$simple_vocab_plus_fields_highlight		= get_option('simple_vocab_plus_fields_highlight');
$simple_vocab_plus_fields_description	= get_option('simple_vocab_plus_fields_description');
$simple_vocab_plus_values_compare		= get_option('simple_vocab_plus_values_compare');
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

<div class="field">
	<div class="two columns alpha">
		<?php echo $view->formLabel('simple_vocab_plus_collections', __('Apply to Collections')); ?>
	</div>
	<div class="inputs five columns omega">
		<p class="explanation">
			<?php echo __('If checked, the plugin will be applied to Collections metadata too (by default, this plugin only applies to Items metadata).'); ?>
		</p>
		<?php echo $view->formCheckbox('simple_vocab_plus_collections', $simple_vocab_plus_collections, null, array('1', '0')); ?>
	</div>
</div>

<div class="field">
	<div class="two columns alpha">
		<?php echo $view->formLabel('simple_vocab_plus_exhibits', __('Apply to Exhibits')); ?>
	</div>
	<div class="inputs five columns omega">
<?php if (plugin_is_active('ExhibitBuilder')): ?>
		<p class="explanation">
			<?php echo __('If checked, the plugin will be applied to Exhibits metadata too (by default, this plugin only applies to Items metadata).'); ?>
		</p>
		<?php echo $view->formCheckbox('simple_vocab_plus_exhibits', $simple_vocab_plus_exhibits, null, array('1', '0')); ?>
<?php else: ?>
		<p class="explanation">
			<?php echo __('The Exhibit Builder plugin is not installed or active. Install and activate the plugin in order to be able to configure notifications for new Exhibits.'); ?>
		</p>
<?php endif ?>
	</div>
</div>

<h2><?php echo __('Alerts'); ?></h2>

<div class="field">
	<div class="two columns alpha">
		<?php echo $view->formLabel('simple_vocab_plus_fields_highlight', __('Highlight autosuggest fields')); ?>
	</div>
	<div class="inputs five columns omega">
		<p class="explanation">
			<?php echo __('Color hex code (e.g.: #ff0000) to highlight the fields that have autosuggest feature applied (blank means no highlight).'); ?>
		</p>
		<?php echo $view->formText('simple_vocab_plus_fields_highlight', $simple_vocab_plus_fields_highlight, array('title'=>__('pound sign (#) followed by six hex values'),'pattern'=>'^#[0-9a-f]{6}$')); ?>
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

<h2><?php echo __('Checks'); ?></h2>

<div class="field">
	<div class="two columns alpha">
		<?php echo $view->formLabel('simple_vocab_plus_values_compare', __('Compare element values')); ?>
	</div>
	<div class="inputs five columns omega">
		<p class="explanation">
			<?php echo __('When examining an element, compare the recorded values with the vocabulary\'s terms.'); ?>
		</p>
		<?php echo $view->formCheckbox('simple_vocab_plus_values_compare', $simple_vocab_plus_values_compare, null, array('1', '0')); ?>
	</div>
</div>
