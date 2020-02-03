<?php if (!$element_texts): ?>
<p class="error"><?php echo __('No texts for the selected element was found in the repository.'); ?></p>
<?php else: ?>
<table>
    <tr>
        <th style="text-align:center"><?php echo __('Count'); ?></th>
        <th style="text-align:center"><?php echo __('Warnings'); ?></th>
        <th><?php echo __('Text'); ?></th>
    </tr>
    <?php foreach ($element_texts as $element_text): ?>
    <tr>
        <td style="text-align:center"><?php echo $element_text['count']; ?></td>
        <td class='error' style="text-align:center"><?php echo implode('<br />', $element_text['warnings']); ?></td>
        <td>
			<?php 
				$clean_snippet = snippet(nl2br($element_text['text']), 0, 600);
				$queryURL = url('items/browse?advanced[0][element_id]=' . $element_text['element_id'] . '&advanced[0][type]=contains&advanced[0][terms]=' . $clean_snippet);
				echo '<a class="browse-link" href="' . $queryURL . '" title="' . __('click to search for') . ' ' . ($element_text['count'] == 1 ? __('this page') : __('these pages')) . '">' . $clean_snippet . '</a>'; 
			?>
		</td>
    </tr>
    <?php endforeach; ?>
</table>
<?php endif; ?>