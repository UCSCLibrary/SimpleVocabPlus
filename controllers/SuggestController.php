<?php
/**
 * Simple Vocab Plus
 * 
 * @copyright Copyright 2014 UCSC Library Digital Initiatives
 * @copyright Copyright 2021 Daniele Binaghi
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
		$svpAssign = $this->_helper->db->getTable('SvpAssign')->find($id);
		$svpAssign->delete();
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

		$element_id = $this->getRequest()->getParam('av_element-id');
		$enforced = $this->getRequest()->getParam('av_enforced');
		$sources_id = implode(',', $this->getRequest()->getParam('av_multi-id'));
		// Do not process too many sources.
		if (strlen($sources_id) > 100) {
			$this->_helper->flashMessenger(__('Please select less source elements.'), 'error');
			$this->_helper->redirector('index','index');
		}

		switch ($this->getRequest()->getParam('av_source')) {
			case "self":
				$vocab_id = 0;
				$type = __('self');
				break;
			case "multi":
				$vocab_id = 0;
				$type = __('multi');
				break;
			default:
				$vocab_id = $this->getRequest()->getParam('av_vocab-id');
				$type = ($this->_vocabIsLocal($vocab_id) ? __('local') : __('remote'));
		}

		// Do not process empty select options.
		if ($element_id == '') {
			$this->_helper->flashMessenger(__('Please select an element to assign.'), 'error');
			$this->_helper->redirector('index','index');
		}

		// Do not process an invalid suggest endpoint.
		if ($type != 'self' && (($type == 'multi' && !$sources_id) || ($type == 'vocab' && !$this->_vocabExists($vocab_id)))) {
			$this->_helper->flashMessenger(__('Invalid suggest endpoint. No changes have been made.'), 'error');
			$this->_helper->redirector('index','index');
		}
		
		$svpAssign = new SvpAssign;
		$svpAssign->element_id = $element_id;
		$svpAssign->type = $type;
		$svpAssign->enforced = $enforced;
		$svpAssign->vocab_id = $vocab_id;
		$svpAssign->sources_id = $sources_id;
		$svpAssign->save();
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
