<?php echo head(array('title' => 'Simple Vocabulary Plus')); ?>
<script type="text/javascript" charset="utf-8">
//<![CDATA[
jQuery(document).ready(function() {
    jQuery('#element-id').change(function() {
        jQuery.post(
            <?php echo js_escape(url('simple-vocab-plus/index/suggest-endpoint')); ?>, 
            {element_id: jQuery('#element-id').val()}, 
            function(data) {
                jQuery('#suggest-endpoint').val(data);
            }
        );
    });

    jQuery( "#tabs" ).tabs();

});
//]]>


</script>
<?php echo flash(); ?>
<form method="post" action="<?php echo url('simple-vocab-plus/index/edit-element-suggest'); ?>">

<div id="tabs">
<ul>
<li><a href="#tab1">Assign Vocabularies to Metadata</a></li>
<li><a href="#tab2">Create Vocabularies</a></li>
<li><a href="#tab3">View/Edit Vocabularies</a></li>
</ul>

<div id="tab1" style="height:1%; overflow:hidden">
<section class="seven columns alpha">

    <h2><?php echo __('Assign Vocabulary to Metadata Element'); ?></h2>

    <div class="field">
        <div id="element-id-label" class="two columns alpha">
            <label for="element-id"><?php echo __('Element'); ?></label>
        </div>
        <div class="inputs five columns omega">
            <p class="explanation"><?php echo __('Select an element to assign it ' 
            . 'a Simple Vocabulary. Elements already assigned ' 
            . 'an authority/vocabulary are marked with an asterisk (*).'); ?></p>
            <?php echo $this->formSelect('element_id', null, array('id' => 'element-id'), $this->form_element_options) ?>
        </div>
    </div>
    <div class="field">
        <div id="suggest-endpoint-label" class="two columns alpha">
            <label for="suggest-endpoint"><?php echo __('Vocabulary'); ?></label>
        </div>
        <div class="inputs five columns omega">
            <p class="explanation"><?php echo __('Choose a vocabulary ' 
            . 'to enable the autosuggest feature for the above element. To disable ' 
            . 'the feature just deselect the option.', '</a>'); ?></p>
            <?php echo $this->formSelect('vocab', null, array('id' => 'assign-vocab'), $this->form_suggest_options); ?>
        </div>
    </div>

</section>
<section class="two columns omega">
    <div id="edit" class="panel">
        <?php echo $this->formSubmit('edit-element-suggest', __('Assign Vocabulary'), array('class' => 'submit big green button')); ?>
    </div>
</section>
</form>
<section class="nine columns alpha">

   <div>
    <h2><?php echo __('Current Assignments'); ?></h2>
    <?php if ($this->assignments): ?>
    <table>
        <thead>
        <tr>
            <th><?php echo __('Element Set'); ?></th>
            <th><?php echo __('Element'); ?></th>
            <th><?php echo __('Vocabulary'); ?></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($this->assignments as $assignment): ?>
        <tr>
            <td><?php echo $assignment['element_set_name']; ?></td>
            <td><?php echo $assignment['element_name']; ?></td>
            <td><?php echo $assignment['authority_vocabulary']; ?></td>
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
<div  id="tab2" style="height:1%; overflow:hidden" >
<section class="ten columns alpha" id="tab2" style="height:1%; overflow:hidden">
    <h2><?php echo __('Create New Vocabulary'); ?></h2>
    
    <form method="post" action="<?php echo url('simple-vocab-plus/index/new-vocabulary'); ?>">

    <div class="field" id="nv-namefield">
        <div id="nv-name-label" class="two columns alpha">
            <label for="nv-name"><?php echo __('Name:'); ?></label>
        </div>
        <div class="inputs five columns omega">
            <p class="explanation"><?php echo __('Name your new vocabulary.'); 
         ?></p>
          <input type="text" name="nv-name">
        </div>
    </div>

    <div class="field">
        <div id="nv-local-label" class="two columns alpha">
            <label for="nv-local"><?php echo __('Vocabulary location:'); ?></label>
        </div>
        <div class="inputs five columns omega">
            <p class="explanation"><?php echo __('Choose whether you would like' 
            . 'Omeka to store the terms for your new vocabulary, or whether you' 
            . 'would prefer to store the vocaulary '
	    . 'in the cloud using Google Drive'); 
         ?></p>

          <input type="radio" name="nv-local" value="local" id="localradio">
            Define vocabulary here and edit it manually here<br>
          <input type="radio" name="nv-local" value="remote" id="cloudradio">
            Sync vocabulary with cloud
        </div>
    </div>

    <div class="field" id="nv-urlfield">
        <div id="nv-url-label" class="two columns alpha">
            <label for="nv-url"><?php echo __('Vocabulary URL:'); ?></label>
        </div>
        <div class="inputs five columns omega">
            <p class="explanation"><?php echo __('If the vocabulary is stored in the cloud, this field shows a url to the vocabulary file. Any web server or cloud service can host this file (we recommend GitPages). A plain text file is expected with one term per line.'); 
         ?></p>
          <input type="text" name="nv-url">
        </div>
    </div>

    <div class="field" id="definetext-field">
        <div id="nv-definetext-label" class="two columns alpha">
            <label for="nv-definetext"><?php echo __('Vocabulary Terms:'); ?></label>
        </div>
        <div class="inputs five columns omega">
            <p class="explanation"><?php echo __('Input all terms separated by ' 
            . 'commas. You can also drag and drop text files here to ' 
            . 'paste their contents automatically.'); 
         ?></p>
          <textarea rows="15" name="nv-definetext"></textarea>
        </div>
    </div>


   <div id="create" class="field">
   <?php echo $this->formSubmit('new-vocabulary', __('Create Vocabulary'), array('class' => 'submit green button', 'id'=>'createbutton')); ?>
    </div>
   
   </form>

</section>

</div>

<div  id="tab3" style="height:1%; overflow:hidden">
<section class="ten columns alpha">
    <h2><?php echo __('Edit Existing Vocabulary'); ?></h2>
   
    <form method="post" action="<?php echo url('simple-vocab-plus/index/edit-vocabulary'); ?>">
    <div class="field">
        <div id="vocab-edit-label" class="two columns alpha">
            <label for="vocab-edit"><?php echo __('Choose Vocabulary'); ?></label>
        </div>
        <div class="inputs five columns omega">
            <p class="explanation"><?php echo __('Choose a Vocabulary'); ?></p>
            <?php echo $this->formSelect('vocab', null, array('id' => 'edit-vocab'), $this->form_suggest_options); ?>
        </div>
</div>


    <div class="field" id="ev-urlfield">
        <div id="ev-url-label" class="two columns alpha">
            <label for="ev-url"><?php echo __('Vocabulary Location:'); ?></label>
        </div>
        <div class="inputs five columns omega">
            <p class="explanation"><?php echo __('If the vocabulary is stored in the cloud, this field shows a url to the vocabulary file. Any web server or cloud service can host this file (we recommend GitPages). A plain text file is expected with one term per line.'); 
         ?></p>
          <input type="text" name="ev-url" id="ev-url">
        </div>
    </div>


    <div class="field" id="edittext-field">
        <div id="ev-edittext-label" class="two columns alpha">
            <label for="ev-edittext"><?php echo __('Vocabulary Terms:'); ?></label>
        </div>
        <div class="inputs five columns omega">
            <p class="explanation"><?php echo __('The vocabulary terms are ' 
            . 'displayed here. One term per line.' ); 
         ?></p>
          <textarea rows="20" columns="30" name="ev-edittext" id="ev-edittext"></textarea>
        </div>
    </div>


   <div id="editfield" class="field">
   <?php echo $this->formSubmit('edit-vocabulary', __('Edit Vocabulary'), array('class' => 'submit green button', 'id'=>'edit-button')); ?>
    </div>
   
</form>

</section>
</div>
</div>
<?php 



echo foot(); ?>