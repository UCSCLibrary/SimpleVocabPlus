<?php
/**
 * Simple Vocab Plus
 * 
 * @copyright Copyright 2014 UCSC Library Digital Initiatives
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
 */

/**
 * The Simple Vocab Plus controller.
 * 
 * @package SvPlus
 */
class SimpleVocabPlus_IndexController extends Omeka_Controller_AbstractActionController
{
    public function indexAction()
    {
      $this->view->form_element_options = $this->_getFormElementOptions();
      $this->view->form_vocab_options = $this->_getFormSuggestOptions();
      $this->view->assignments = $this->_getAssignments();
      
      $csrf = new Omeka_Form_SessionCsrf;
      $this->view->csrf = $csrf;

      //echo 'updating:<br>';
      foreach(get_db()->getTable('SvpVocab')->findAll() as $vocab) {
      	$vocab->updateNow();
      }
      //die();
    }


    /**
     * Get an array to be used in formSelect() containing all elements.
     * 
     * @return array
     */
    private function _getFormElementOptions()
    {
        $db = $this->_helper->db->getDb();
        $sql = "
        SELECT es.name AS element_set_name, e.id AS element_id, e.name AS element_name, 
        it.name AS item_type_name, gv.id AS gv_suggest_id 
        FROM {$db->ElementSet} es 
        JOIN {$db->Element} e ON es.id = e.element_set_id 
        LEFT JOIN {$db->ItemTypesElements} ite ON e.id = ite.element_id 
        LEFT JOIN {$db->ItemType} it ON ite.item_type_id = it.id 
        LEFT JOIN {$db->SvpAssign} gv ON e.id = gv.element_id 
        WHERE es.record_type IS NULL OR es.record_type = 'Item' 
        ORDER BY es.name, it.name, e.name";
        $elements = $db->fetchAll($sql);
        $options = array('' => __('Select Below'));
        foreach ($elements as $element) {
            $optGroup = $element['item_type_name'] 
                      ? __('Item Type') . ': ' . __($element['item_type_name']) 
                      : __($element['element_set_name']);
            $value = __($element['element_name']);
            if ($element['gv_suggest_id']) {
                $value .= ' *';
            }
            $options[$optGroup][$element['element_id']] = $value;
        }
        return $options;
    }
    
    /**
     * Get an array to be used in formSelect() containing all sugggest endpoints.
     * 
     * @return array
     */
    private function _getFormSuggestOptions()
    {
      //print_r($this->_helper->db->getTable('SimpleVocabPlus'));
      //die();
      $vocabs = $this->_helper->db->getTable('SvpVocab')->findAll();
      
      $options = array('' => __('Select Below'));

        foreach ($vocabs as $vocab) {
	  $options[$vocab['id']] = $vocab['name'];
        }

        return $options;
    }
    
    /**
     * Get all the authority/vocabulary assignments.
     * 
     * @return array
     */
    private function _getAssignments()
    {
        $svSuggestTable = $this->_helper->db->getTable('SvpAssign');
        $svpVocabTable = $this->_helper->db->getTable('SvpVocab');
        $elementTable = $this->_helper->db->getTable('Element');
        $elementSetTable = $this->_helper->db->getTable('ElementSet');
        $itemTypeTable = $this->_helper->db->getTable('ItemType');
        $itemTypesElementsTable = $this->_helper->db->getTable('ItemTypesElements');
        
        $assignments = array();
        foreach ($svSuggestTable->findAll() as $svSuggest) {
            $element = $elementTable->find($svSuggest->element_id);
            $elementSet = $elementSetTable->find($element->element_set_id);
            $elementSetName = $elementSet->name;
            if( $itemTypesElements = $itemTypesElementsTable->findByElement($element->id)) {
                $itemTypesElement = $itemTypesElements[0];
                $itemType = $itemTypeTable->find($itemTypesElement->item_type_id);
                $elementSetName.=': '.$itemType->name;
            }
            $authorityVocabulary = $svpVocabTable->find($svSuggest->vocab_id);
            $authorityVocabularyName = $authorityVocabulary['name'];

            $assignments[] = array(
                'suggest_id' => $svSuggest->id,
                'element_set_name' => $elementSetName, 
                'element_name' => $element->name, 
                'authority_vocabulary' => __($authorityVocabularyName),
                'element_id' => $svSuggest->element_id
            );
        }
        return $assignments;
    }

}
