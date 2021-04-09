<?php
/**
 * Simple Vocab Plus
 * 
 * @copyright Copyright 2014 UCSC Library Digital Initiatives
 * @copyright Copyright 2021 Daniele Binaghi
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
 */

/**
 * The Simple Vocab Plus Endpoint controller.
 * 
 * @package SvPlus
 */
class SimpleVocabPlus_EndpointController extends Omeka_Controller_AbstractActionController
{
	
	/**
	 * Outputs the suggest endpoint URL of the specified element or NULL if 
	 * there is none.
	 */
	public function vocabAction()
	{
		$this->_helper->viewRenderer->setNoRender();
		$elementId = $this->getRequest()->getParam('element_id');
		$svpAssign = $this->_helper->db->getTable('SvpAssign')->findByElementId($elementId);
		$vocab = $this->_helper->db->getTable('SvpAssign')->find($svpAssign->vocab_id);
		echo $vocab ? $vocab->url : null;
	}

	/**
	 * Proxy for the SvpSuggest suggest endpoints, used by the 
	 * autosuggest feature.
	 */
	public function suggestProxyAction()
	{
		// Get the term
		$term = $this->getRequest()->getParam('term');

		// Get the suggest record
		$elementId = $this->getRequest()->getParam('element-id');
		$svpAssigns = $this->_helper->db->getTable('SvpAssign')->findByElementId($elementId);
		$return = array();
		$termTable = $this->_helper->db->getTable('SvpTerm');
		$elementTextTable = $this->_helper->db->getTable('ElementText');
		foreach($svpAssigns as $svpAssign) {
			if ($svpAssign['type'] == 'self') {
				// case of values retrieved from repository's data
				$select = $elementTextTable->getSelect();
				$select->from(array(),'text')
						->where('record_type = ?', 'Item')
						->where('element_id = ?', $elementId)
						->where('text like ?', $term . '%')
						->group('text')
						->order('text ASC');
				$return = $elementTextTable->fetchCol($select);
			} elseif ($svpAssign['type'] == 'multi') {
				// case of values retrieved from multiple elements in repository's data
				$select = $elementTextTable->getSelect();
				$select->from(array(),'text')
						->where('record_type = ?', 'Item')
						->where('element_id = (?)', $svpAssign->sources_id)
						->where('text like ?', $term . '%')
						->group('text')
						->order('text ASC');
				$return = $elementTextTable->fetchCol($select);
			} else {
				// case of values retrieved from vocabulary (local or remote)
				$results = $termTable->findBySql('vocab_id = ? and term like ?', array($svpAssign->vocab_id, $term . '%'));
				foreach($results as $result) {
					$return[] = $result->term;
				}
			}
		}
		$this->_helper->json($return);
	}
}
