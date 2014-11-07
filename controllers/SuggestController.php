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

    public function deleteAction() 
    {
        $suggestId = $this->getRequest()->getParam('suggest_id');
        $svAssign = $this->_helper->db->getTable('SvpAssign')->find($suggestId);
        $svAssign->delete();
        $this->_helper->flashMessenger(__('Successfully disabled the element\'s suggest feature.'), 'success');
        $this->_helper->redirector('index','index');
    }
    
    public function editAction()
    {
        $suggestId = $this->getRequest()->getParam('suggest_id');
        $elementId = $this->getRequest()->getParam('element_id');
        $vocab = $this->getRequest()->getParam('vocab_id');
        // Don't process empty select options.
        if ('' == $elementId) {
            $this->_helper->redirector('index','index');
        }

        // Don't process an invalid suggest endpoint.
        if (!$this->_vocabExists($vocab)) {
            $this->_helper->flashMessenger(__('Invalid suggest endpoint. No changes have been made.'), 'error');
            $this->_helper->redirector('index','index');
        }
            
        $svAssign = $this->_helper->db->getTable('SvpAssign')->find($suggestId);
            
        $svAssign->element_id = $vocab;
        $svAssign->vocab_id = $vocab;
        $svAssign->save();
        $this->_helper->flashMessenger(__('Successfully edited the element\'s suggest feature.'), 'success');
        
    } 


    /**
     * Adds a connection between an element and a vocabulary
     *
     * Overwrites existing connection for that element, if one exists
     *
     * @return void
     */
    public function addAction() {
            
        $elementId = $this->getRequest()->getParam('element_id');
        $vocab = $this->getRequest()->getParam('vocab_id');

        // Don't process empty select options.
        if ('' == $elementId) {
                $this->_helper->flashMessenger(__('Please select an element to assign'), 'success');
            $this->_helper->redirector('index','index');
        }

        // Don't process an invalid suggest endpoint.
        if (!$this->_vocabExists($vocab)) {
            $this->_helper->flashMessenger(__('Invalid suggest endpoint. No changes have been made.'), 'error');
            $this->_helper->redirector('index','index');
        }
            
        $svAssign = new SvpAssign;
        $svAssign->element_id = $elementId;
        $svAssign->vocab_id = $vocab;
        $svAssign->save();
        $this->_helper->flashMessenger(__('Successfully enabled the element\'s suggest feature.'), 'success');
        $this->_helper->redirector('index','index');
    }
        
    
    
    /**
     * Check if the specified vocabulary exists.
     * 
     * @param string $vocab
     * @return bool
     */
    private function _vocabExists($vocab)
    {
      $vocab = $this->_helper->db->getTable('SvpVocab')->find($vocab);
      
      if ($vocab) {
	return true;
      }
      return false;
    }
    

}