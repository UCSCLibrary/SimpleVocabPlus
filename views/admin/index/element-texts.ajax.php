<?php if (!$element_texts): ?>
<p class="error"><?php echo __('No value for the selected element was found in the repository.'); ?></p>
<?php else: ?>
<p class="info"><?php echo __('These are the values stored in the repository for the selected element; clicking on any of them results in a search for all Items including that particular value. Values not found in a local vocabulary assigned to the element will be marked in red (feature has to be enabled in plugin configuration).'); ?></p>
<table class="centered boldheaders striped">
	<thead>
		<tr>
			<th style="text-align:center"><?php echo __('Count'); ?></th>
			<th><?php echo __('Value'); ?></th>
			<th style="text-align:center"><?php echo __('Warnings'); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($element_texts as $element_text): ?>
		<tr>
			<td style="text-align:center"><?php echo $element_text['count']; ?></td>
			<td>
				<?php
					$tagBefore = '';
					$tagAfter = '';
					$text = $element_text['text'];
					if (substr($text, 0, 3) == '***' && substr($text, -3) == '***') {
						$tagBefore = '<span style="color:red">';
						$tagAfter = '</span>';
						$text = substr($text, 3, -3);
					}	
					$clean_snippet = snippet(nl2br($text), 0, 600);
					$queryURL = url('items/browse?advanced[0][element_id]=' . $element_text['element_id'] . '&advanced[0][type]=contains&advanced[0][terms]=' . $clean_snippet);
					echo '<a class="browse-link" href="' . $queryURL . '" title="' . __('click to search for') . ' ' . ($element_text['count'] == 1 ? __('this page') : __('these pages')) . '">' . $tagBefore . $clean_snippet . $tagAfter . '</a>'; 
				?>
			</td>
			<td class='error' style="text-align:center"><?php echo implode('<br />', $element_text['warnings']); ?></td>
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>
<?php endif; ?>
