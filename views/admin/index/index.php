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

    jQuery( "#tabs" ).tabs();

});
//]]>
</script>

<?php echo flash(); ?>

<form method="post" action="<?php echo url('simple-vocab-plus/suggest/add'); ?>">

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
            <?php echo $this->formSelect('vocab_id', null, array('id' => 'assign-vocab'), $this->form_vocab_options); ?>
        </div>
    </div>

</section>
<section class="two columns omega">
    <div id="edit" class="panel">
        <?php echo $this->formSubmit('edit-element-suggest', __('Assign Vocabulary'), array('class' => 'submit big green button')); ?>
    </div>
</section>
   <?php echo $this->csrf;?>
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
            <th style="width:22%;"></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($this->assignments as $assignment): ?>
        <tr>
            <td class="element_set_name" data-svp-element-set-id="<?php echo $assignment['element_set_id']; ?>"><?php echo $assignment['element_set_name']; ?></td>
            <td class="element_name" data-svp-element-id="<?php echo $assignment['element_id']; ?>"><?php echo $assignment['element_name']; ?></td>
            <td class="authority_vocabulary" data-svp-vocab-id="<?php echo $assignment['authority_vocabulary_id']; ?>"><?php echo $assignment['authority_vocabulary']; ?></td>
            <td><button id="<?php echo $assignment['suggest_id'];?>" class="svp-edit-suggest-button" style="margin:0px 5px 0px 0px;">Edit</button><a href="<?php echo url('simple-vocab-plus/suggest/delete/suggest_id/'.$assignment['suggest_id']); ?>"><button style="margin:0px" type="button">Delete</button></a></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?>
    <p><?php echo __('There are no suggest assignments.'); ?></p>
    <?php endif; ?>
    </div>
</section>


</div>
<div  id="tab2" style="height:1%; overflow:hidden" >
<section class="ten columns alpha" id="tab2" style="height:1%; overflow:hidden">
    <h2><?php echo __('Create New Vocabulary'); ?></h2>
    
    <form method="post" action="<?php echo url('simple-vocab-plus/vocabulary/add'); ?>">

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
            <p class="explanation"><?php echo __('Choose whether you would like '
            . 'Omeka to store the terms for your new vocabulary, or whether you '
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
            <p class="explanation"><?php echo __('Input all terms below, one per line. '
            . 'You can also drag and drop text files here to paste their contents automatically.'
            );
         ?></p>
          <textarea rows="15" name="nv-definetext"></textarea>
        </div>
    </div>


   <div id="create" class="field">
   <?php echo $this->formSubmit('new-vocabulary', __('Create Vocabulary'), array('class' => 'submit green button', 'id'=>'createbutton')); ?>
    </div>

   <?php echo $this->csrf;?>
   
   </form>

</section>

</div>

<div  id="tab3" style="height:1%; overflow:hidden">
<section class="ten columns alpha">
    <h2><?php echo __('Edit Existing Vocabulary'); ?></h2>
   
    <form method="post" action="<?php echo url('simple-vocab-plus/vocabulary/edit'); ?>">
    <div class="field">
        <div id="vocab-edit-label" class="two columns alpha">
            <label for="vocab-edit"><?php echo __('Choose Vocabulary'); ?></label>
        </div>
        <div class="inputs five columns omega">
            <p class="explanation"><?php echo __('Choose a Vocabulary'); ?></p>
            <?php echo $this->formSelect('vocab', null, array('id' => 'edit-vocab'), $this->form_vocab_options); ?>
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

   <?php echo $this->csrf;?>

</form>

</section>
</div>
<script>
jQuery(document).ready(function() {
    var svpflag = false;
    jQuery('.svp-edit-suggest-button').click(function(e) {
        e.preventDefault();
        if (svpflag) {
            if (jQuery(this).attr("id") == svpflag) {
                var element_id = jQuery('#edit-element-id').val();
                var vocab_id = jQuery('#edit-vocab-id').val();
                var form = jQuery("<form action='<?php echo url('simple-vocab-plus/suggest/edit/suggest_id/'); ?>" + svpflag + "'></form>");
                form.append('<input type="hidden" name="element_id" value="' + element_id + '" />');
                form.append('<input type="hidden" name="vocab_id" value="' + vocab_id + '" />');
                form.append('<?php echo trim($this->csrf);?>');
                form.appendTo(jQuery('body'));
                form.submit();
            } else {
                alert('Please edit one suggest assignment at a time');
            }
            //prepare and submit form with params from boxes created below
        } else {
            var form_element_options = <?php echo json_encode($this->formSelect('element_id', null, array('id' => 'edit-element-id'), $this->form_element_options)); ?>;
            var suggest_options = <?php echo json_encode($this->formSelect('element_id', null, array('id' => 'edit-vocab-id'), $this->form_vocab_options)); ?>;
            var element_row = jQuery(this).parent().parent();
            var element_id = element_row.find('.element_name').attr('data-svp-element-id');
            var vocab_id = element_row.find('.authority_vocabulary').attr('data-svp-vocab-id');
            element_row.children('.element_set_name').html(form_element_options);
            element_row.find('.element_set_name select').val(element_id);
            element_row.children('.element_name').html('');
            element_row.children('.authority_vocabulary').html(suggest_options);
            element_row.find('.authority_vocabulary select').val(vocab_id);
            jQuery(this).html("Save");
            jQuery("#edit-element-id").css('max-width', '250px');
            svpflag = jQuery(this).attr('id');
        }
    });
});
</script>
</div>
<?php
echo foot();
