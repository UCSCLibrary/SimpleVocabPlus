<?php
/**
 * Simple Vocab Plus
 *
 * @copyright Copyright 2014 UCSC Library Digital Initiatives
 * @copyright Copyright 2021 Daniele Binaghi
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
 */

/**
 * The Simple Vocab Plus Vocabulary controller.
 *
 * @package SimpleVocabPlus
 */
class SimpleVocabPlus_VocabularyController extends Omeka_Controller_AbstractActionController
{

    public function init()
    {
        // Set the model class so this controller can perform some functions, 
        // such as $this->findById()
        $this->_helper->db->setDefaultModelName('SvpVocab');
    }

	public function addAction()
	{
		$this->_validatePost();

		$name = trim($_REQUEST['nv_name']);
		// Checks that an unambiguous name has been provided for the vocabulary 
		if ($name == '') {
			$this->_helper->flashMessenger(__('You did not provide a name for your new vocabulary.'), 'error');
			$this->_helper->redirector('index', 'index');
		} elseif ($this->_vocabExists($name)) {
			$this->_helper->flashMessenger(__('The name you provided for your new vocabulary is already in use.'), 'error');
			$this->_helper->redirector('index', 'index');
		}
		
		$db = $this->_helper->db;
		if ($_REQUEST['nv_local'] == 'local') {
			// Local vocabulary
			$text = $_REQUEST['nv_definetext'];
			$vocab = $db->getTable('SvpVocab')->createVocab($name, 'local');
			$success = $db->getTable('SvpTerm')->addFromText($vocab->id, $text);
		} elseif ($_REQUEST['nv_local'] == 'remote') {
			// Remote vocabulary
			$url = $_REQUEST['nv_url'];
			$vocab = $db->getTable('SvpVocab')->createVocab($name, $url);
			$vocab->updateNow();
		}
		$flash = $this->_helper->FlashMessenger;
		$flash->addMessage(__('Your new vocabulary has been created successfully. You may now assign it to metadata elements.'), 'success');
		$this->_helper->redirector('index', 'index');
	}

	public function editAction()
	{
		$this->_validatePost();
		
		if (isset($_REQUEST['ev_name'])) {
			$vocab_id = $_REQUEST['ev_name'];
			if ($vocab_id == '') {
				$this->_helper->flashMessenger(__('No vocabulary was chosen for editing.'), 'error');
				$this->_helper->redirector('index', 'index');
			}
		} else {
			$this->_helper->flashMessenger(__('There was a problem editing your vocabulary.'), 'error');
			$this->_helper->redirector('index', 'index');
		}
		
		$vocab_url = trim('' . $_REQUEST['ev_url']);
		$vocab_text = trim('' . $_REQUEST['ev_edittext']);
		$db = $this->_helper->db;
		
		if ($vocab_url != '' && $vocab_url != 'local') {
			// edit remote vocabulary
			$vocab = $db->getTable('SvpVocab')->find($vocab_id);
			$vocab->url = $vocab_url;
			$vocab->save();
			$this->_helper->flashMessenger(__('Remote vocabulary edited successfully.'), 'success');
			$this->_helper->redirector('index', 'index');
		} else {
			// edit local vocabulary
			$updates = $db->getTable('SvpTerm')->updateFromText($vocab_id, $vocab_text);
			if (empty($updates['add']) && empty($updates['delete'])) {
				$this->_helper->flashMessenger(__('No changes were made to the vocabulary.'), 'alert');
			} else {
				$this->_helper->flashMessenger(__('Local vocabulary edited successfully.'), 'success');
			}
			$this->_helper->redirector('index', 'index');
		}
	}

	public function deleteAction()
	{
		$id = $this->getRequest()->getParam('id');
		// delete vocabulary
		if (!empty($this->_helper->db->getTable('SvpAssign')->findBy(array('vocab_id' => $id)))) {
			$this->_helper->flashMessenger(__('At least one element is still assigned to this vocabulary. Delete the assignments before trying to delete the vocabulary.'), 'error');
			$this->_helper->redirector('index', 'index');
		} else {
			$this->_helper->db->getTable('SvpVocab')->deleteVocab($id);
			$this->_helper->flashMessenger(__('Vocabulary deleted successfully.'), 'success');
			$this->_helper->redirector('index', 'index');
		}
	}
	
	protected function _getDeleteConfirmMessage($record)
    {
        return __('This will delete the Vocabulary "%s", but not the values already stored in the repository.', $record['name']);
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
