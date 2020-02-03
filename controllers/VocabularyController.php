<?php
/**
 * Simple Vocab Plus
 *
 * @copyright Copyright 2014 UCSC Library Digital Initiatives
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
 */

/**
 * The Simple Vocab Plus Vocabulary controller.
 *
 * @package SimpleVocabPlus
 */
class SimpleVocabPlus_VocabularyController extends Omeka_Controller_AbstractActionController
{
	public function addAction()
	{
		$this->_validatePost();

		$name = trim($_REQUEST['nv-name']);
		// Checks that an unambiguous name has been provided for the vocabulary 
		if ($name == '') {
			$this->_helper->flashMessenger(__('You did not provide a name for your new vocabulary.'), 'error');
			$this->_helper->redirector('index', 'index');
		} elseif ($this->_vocabExists($name)) {
			$this->_helper->flashMessenger(__('The name you provided for your new vocabulary is already in use.'), 'error');
			$this->_helper->redirector('index', 'index');
		}
		
		if ($_REQUEST['nv-local'] == 'local') {
			// Local vocabulary
			$text = $_REQUEST['nv-definetext'];
			$vocab = $this->_helper->db->getTable('SvpVocab')->createVocab($name, 'local');
			$success = $this->_helper->db->getTable('SvpTerm')->addFromText($vocab->id, $text);
		} elseif ($_REQUEST['nv-local'] == 'remote') {
			// Remote vocabulary
			$url = $_REQUEST['nv-url'];
			$vocab = $this->_helper->db->getTable('SvpVocab')->createVocab($name, $url);
			$vocab->updateNow();
		}
		$flash = $this->_helper->FlashMessenger;
		$flash->addMessage(__('Your vocabulary has been created successfully. You may now assign it to metadata elements.'), 'success');
		$this->_helper->redirector('index', 'index');
	}

	public function editAction()
	{
		$this->_validatePost();
		
		if (isset($_REQUEST['ev-name'])) {
			if ($_REQUEST['ev-name'] != '') {
				$vocab_id = $_REQUEST['ev-name'];
			} else {
				$this->_helper->flashMessenger(__('No vocabulary was chosen for editing.'), 'error');
				$this->_helper->redirector('index', 'index');
			}
		} else {
			$this->_helper->flashMessenger(__('There was a problem editing your vocabulary.'), 'error');
			$this->_helper->redirector('index', 'index');
		}
		
		$vocab_url = trim('' . $_REQUEST['ev-url']);
		$vocab_text = trim('' . $_REQUEST['ev-edittext']);
		
		if ($vocab_url == '' || ($vocab_url == 'local' && $vocab_text == '')) {
			// delete vocabulary
			if ($vocab = $this->_helper->db->getTable('SvpAssign')->find(array('vocab_id'=>$vocab_id))) {
				$this->_helper->flashMessenger(__('At least one element is still assigned to this vocabulary. Delete the assignments before trying to delete the vocabulary.'), 'error');
				$this->_helper->redirector('index', 'index');
			} else {
				$this->_helper->db->getTable('SvpVocab')->deleteVocab($vocab_id);
				$this->_helper->flashMessenger(__('Vocabulary deleted successfully.'), 'success');
				$this->_helper->redirector('index', 'index');
			}
		} elseif ($vocab_url != '' && $vocab_url != 'local') {
			// edit remote vocabulary
			$vocab = $this->_helper->db->getTable('SvpVocab')->find($vocab_id);
			$vocab->url = $vocab_url;
			$vocab->save();
			$this->_helper->flashMessenger(__('Remote vocabulary edited successfully.'), 'success');
			$this->_helper->redirector('index', 'index');
		} else {
			// edit local vocabulary
			$possibleUpdates = $this->_helper->db->getTable('SvpTerm')->updateFromText($vocab_id, $vocab_text);
			if ($possibleUpdates) {
				//prompt about updates
				$updates = $this->_promptUpdates($possibleUpdates);
				//if there are updates
				//update terms for all assigned records
				$this->_updateRecords($vocab_id, $updates);
			}
			$this->_helper->flashMessenger(__('Local vocabulary edited successfully.'), 'success');
			$this->_helper->redirector('index', 'index');
		}
	}

	public function getAction()
	{
		$db = $this->_helper->db;
		$vocabId = $this->getParam('vocab');
		$vocab = $db->getTable('SvpVocab')->find($vocabId);
		$terms = $db->getTable('SvpTerm')->findBy(array('vocab_id' => $vocabId, 'sort_field' => 'id', 'sort_dir' => 'a'));
		$return = array('url' => $vocab->url, 'terms' => array());
		foreach ($terms as $term) {
			$return['terms'][] = $term->term;
		}
		$this->_helper->viewRenderer->setNoRender();
		echo json_encode($return);
	}

	private function _promptUpdates($possibleUpdates)
	{
		// this is only a test function.
		// it'll work ok as long as there
		// are the same number of deleted and added
		// terms and they're all intended as
		// updates. This code will have to be
		// reorganized, since we will probably need an interactive
		// dialog here

		$length = count($possibleUpdates['add']) > count($possibleUpdates['delete'])
			? count($possibleUpdates['delete'])
			: count($possibleUpdates['add']);

		for ($i=0; $i < $length; $i++) {
			$updates[$possibleUpdates['delete'][$i]] = $possibleUpdates['add'][$i];
		}
		return $updates;
	}

	private function _updateRecords($vocab_id, $updates)
	{
		// find all assignments for this vocab
		// run a sql query to update the element texts table
		// for these elements when the old term is matched
		// should be able to get it in a single query.
		$db = $this->_helper->db;
		foreach ($updates as $old => $new) {
			$sql = "UPDATE `{$db->ElementText}` AS et
				LEFT JOIN `{$db->SvpAssign}` AS sa
					ON et.element_id = sa.element_id
				SET et.text = REPLACE(et.text, ?, ?)
				WHERE sa.vocab_id = ?
			";
			$bind = array($old, $new, (integer) $vocab_id);
			$db->query($sql, $bind);
		}
	}

	/**
	 * Check if the specified vocabulary exists.
	 *
	 * @param string $vocab_name
	 * @return bool
	 */
	private function _vocabExists($vocab_name)
	{
		$vocab = $this->_helper->db->getTable('SvpVocab')->findBy(array('name'=>$vocab_name));
		return !empty($vocab);
	}

	private function _validatePost()
	{
		$csrf = new Omeka_Form_SessionCsrf;
		if (!$csrf->isValid($_POST)) {
			$flash->addMessage(__('There was an error processing your request.'), 'error');
			$this->_helper->redirector('index', 'index');
		}
	}
}