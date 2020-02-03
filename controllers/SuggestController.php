<?php
/**
 * Simple Vocab Plus
 * 
 * @copyright Copyright 2014 UCSC Library Digital Initiatives
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
 */

/**
 * The Simple Vocab Plus Suggest Assignment controller.
 * 
 * @package SvPlus
 */
class SimpleVocabPlus_SuggestController extends Omeka_Controller_AbstractActionController
{

    public function init()
    {
        // Set the model class so this controller can perform some functions, 
        // such as $this->findById()
        $this->_helper->db->setDefaultModelName('SvpAssign');
    }

	public function deleteAction() 
	{
		$id = $this->getRequest()->getParam('id');
		$svAssign = $this->_helper->db->getTable('SvpAssign')->find($id);
		$svAssign->delete();
		$this->_helper->flashMessenger(__('The element\'s suggest feature was successfully deleted.'), 'success');
		$this->_helper->redirector('index','index');
	}

	protected function _getDeleteConfirmMessage($record)
    {
        return __('This will delete this Assignment, but not the assigned Vocabulary.');
    }

	/**
	 * Adds a connection between an element and a vocabulary
	 *
	 * Overwrites existing connection for that element, if one exists
	 *
	 * @return void
	 */
	public function addAction() {

		$element_id = $this->getRequest()->getParam('element_id');
		$enforced = $this->getRequest()->getParam('enforced');
		if ($this->getRequest()->getParam('self-assign')) {
			$vocab_id = 0;
			$type = __('self');
			$enforced = false;
		} else {
			$vocab_id = $this->getRequest()->getParam('vocab_id');
			$type = ($this->_vocabIsLocal($vocab_id) ? __('local') : __('remote'));
		}

		// Do not process empty select options.
		if ($element_id == '') {
			$this->_helper->flashMessenger(__('Please select an element to assign.'), 'success');
			$this->_helper->redirector('index','index');
		}

		// Do not process an invalid suggest endpoint.
		if (!$this->_vocabExists($vocab_id) && $type != 'self') {
			$this->_helper->flashMessenger(__('Invalid suggest endpoint. No changes have been made.'), 'error');
			$this->_helper->redirector('index','index');
		}
		
		$svAssign = new SvpAssign;
		$svAssign->element_id = $element_id;
		$svAssign->type = $type;
		$svAssign->enforced = $enforced;
		$svAssign->vocab_id = $vocab_id;
		$svAssign->save();
		$this->_helper->flashMessenger(__('The element\'s suggest feature was successfully added.'), 'success');
		$this->_helper->redirector('index','index');
	}

	/**
	 * Check if the specified vocabulary exists.
	 *
	 * @param integer $vocab_id
	 * @return bool
	 */
	private function _vocabExists($vocab_id)
	{
		$vocab = $this->_helper->db->getTable('SvpVocab')->find($vocab_id);
		return !empty($vocab);
	}
	
	/**
	 * Check if the specified vocabulary is local or remote.
	 *
	 * @param integer $vocab_id
	 * @return bool
	 */
	private function _vocabIsLocal($vocab_id)
	{
		$vocab = $this->_helper->db->getTable('SvpVocab')->find($vocab_id);
		return ($vocab['url'] == 'local');
	}
}