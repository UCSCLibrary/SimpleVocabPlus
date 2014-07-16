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
      $this->view->form_suggest_options = $this->_getFormSuggestOptions();
      $this->view->assignments = $this->_getAssignments();
      //echo 'updating:<br>';
      foreach(get_db()->getTable('SvpVocab')->findAll() as $vocab) {
      	$vocab->updateNow();
      }
      //die();
    }
    
    public function editElementSuggestAction()
    {
        $elementId = $this->getRequest()->getParam('element_id');
        $vocab = $this->getRequest()->getParam('vocab');
        
        // Don't process empty select options.
        if ('' == $elementId) {
            $this->_helper->redirector('index');
        }
        
        $svAssign = $this->_helper->db->getTable('SvpAssign')->findByElementId($elementId);
        
        // Handle an existing suggest record.
        if ($svAssign) {
            
            // Delete suggest record if there is no endpoint.
            if ('' == $vocab) {
                $svAssign->delete();
                $this->_helper->flashMessenger(__('Successfully disabled the element\'s suggest feature.'), 'success');
                $this->_helper->redirector('index');
            }
            
            // Don't process an invalid suggest endpoint.
            if (!$this->_vocabExists($vocab)) {
                $this->_helper->flashMessenger(__('Invalid vocabulary. No changes have been made.'), 'error');
                $this->_helper->redirector('index');
            }
            
            $svAssign->vocab_id = $vocab;
            $this->_helper->flashMessenger(__('Successfully edited the element\'s suggest feature.'), 'success');
        
        // Handle a new suggest record.
        } else {
            
            // Don't process an invalid suggest endpoint.
            if (!$this->_vocabExists($vocab)) {
                $this->_helper->flashMessenger(__('Invalid suggest endpoint. No changes have been made.'), 'error');
                $this->_helper->redirector('index');
            }
            
            $svAssign = new SvpAssign;
            $svAssign->element_id = $elementId;
            $svAssign->vocab_id = $vocab;
            $this->_helper->flashMessenger(__('Successfully enabled the element\'s suggest feature.'), 'success');
        }
        
        $svAssign->save();
        $this->_helper->redirector('index');
    }
    
    /**
     * Outputs the suggest endpoint URL of the specified element or NULL if 
     * there is none.
     */
    public function vocabEndpointAction()
    {
        $this->_helper->viewRenderer->setNoRender();
        $elementId = $this->getRequest()->getParam('element_id');
        $svAssign = $this->_helper->db->getTable('SvpAssign')->findByElementId($elementId);
        $vocab = $this->_helper->db->getTable('SvpAssign')->find($svAssign->vocab_id);
	echo  $vocab->url;
    }
    
    /**
     * Proxy for the Sv Suggest suggest endpoints, used by the 
     * autosuggest feature.
     */
    public function suggestEndpointProxyAction()
    {
      //get the term
      $term = $this->getRequest()->getParam('term');

        // Get the suggest record.
      $elementId = $this->getRequest()->getParam('element-id');
      $svAssign = $this->_helper->db->getTable('SvpAssign')->findByElementId($elementId);
      $results = $this->_helper->db->getTable('SvpTerm')->findBySql('vocab_id = ? and term like ?',array($svAssign->vocab_id,$term.'%'));

	$return = array();
	foreach($results as $result) {
	  $return[] = $result->term;
	}
	
        $this->_helper->json($return);
    }

    public function getVocabAction()
    {
        $this->_helper->viewRenderer->setNoRender();
	$vocabId = $this->getParam('vocab');
	$vocab = $this->_helper->db->getTable('SvpVocab')->find($vocabId);
	$termTable = $this->_helper->db->getTable('SvpTerm');
	$select = $termTable->getSelect()->where('vocab_id = ?',$vocabId);
	$terms = $termTable->fetchObjects($select);
	//$terms = $termTable->fetchObjects('select * from omeka_svp_terms where vocab_id = '.$vocabId);
	$return = array('url'=>$vocab->url,'terms'=>array());
	foreach($terms as $term) {
	  $return['terms'][]=$term->term;
	}
	echo(json_encode($return));
    }

    

    public function editVocabularyAction() {
      
      if(isset($_REQUEST['vocab']) && $_REQUEST['vocab'] !='')
	$vocab_id=$_REQUEST['vocab'];
      else
	throw new Exception('no vocab chosen');

      if(isset($_REQUEST['ev-url']) && $_REQUEST['ev-url'] !='local') {

	$vocab = $this->_helper->db->getTable('SvpVocab')->find($vocab_id);
	$vocab->url = $url;
	$vocab->save();

      } else if(isset($_REQUEST['ev-edittext'])) {

	$text = $_REQUEST['ev-edittext'];

	$possibleUpdates = $this->_helper->db->getTable('SvpTerm')->updateFromText($vocab_id,$text);

	if($possibleUpdates) {
	  //prompt about updates
	  $updates = $this->_promptUpdates($possibleUpdates);
	  //if there are updates
	  //update terms for all assigned records
	  $this->_updateRecords($vocab_id,$updates);
	}
      }
      $this->_helper->redirector('index');
    }

    private function _promptUpdates($possibleUpdates) {
      //this is only a test function. 
      //it'll work ok as long as there
      //are the same number of deleted and added
      //terms and they're all intended as 
      //updates. This code will have to be
      //reorganized, since we will probably need an interactive 
      //dialog here

      $length = (count($possibleUpdates['add']) > count($possibleUpdates['delete'])) ? count($possibleUpdates['delete']) : count($possibleUpdates['add']);

      for($i=0;$i<$length;$i++) {
	    $updates[$possibleUpdates['delete'][$i]] = $possibleUpdates['add'][$i];
	  }
      return($updates);
    }

    private function _updateRecords($vocab_id,$updates) {
      //find all assignments for this vocab
      //run a sql query to update the element texts table
      //for these elements when the old term is matched
      //should be able to get it in a single query.
      foreach($updates as $old=>$new) {
	$sql = 'update omeka_element_texts as et left join omeka_svp_assigns as sa on (et.element_id = sa.element_id) set et.text=REPLACE(et.text,"'.$old.'","'.$new.'") where sa.vocab_id='.$vocab_id;
	get_db()->query($sql);
      }
      
    }

    public function newVocabularyAction() {
      //todo: form validation
	$name = $_REQUEST['nv-name'];
      if($_REQUEST['nv-local']=='local') {
	$text = $_REQUEST['nv-definetext'];
	$vocab_id = $this->_helper->db->getTable('SvpVocab')->createVocab($name,'local');
	$success = $this->_helper->db->getTable('SvpTerm')->addFromText($vocab_id,$text);

      } else if($_REQUEST['nv-local']=='remote') {
	$url = $_REQUEST['nv-url'];
	$vocab = $this->_helper->db->getTable('SvpVocab')->createVocab($name,$url);
	$vocab->updateNow();
      }
      $this->_helper->redirector('index');
	
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
        it.name AS item_type_name, ls.id AS lc_suggest_id 
        FROM {$db->ElementSet} es 
        JOIN {$db->Element} e ON es.id = e.element_set_id 
        LEFT JOIN {$db->ItemTypesElements} ite ON e.id = ite.element_id 
        LEFT JOIN {$db->ItemType} it ON ite.item_type_id = it.id 
        LEFT JOIN {$db->SvpAssign} ls ON e.id = ls.element_id 
        WHERE es.record_type IS NULL OR es.record_type = 'Item' 
        ORDER BY es.name, it.name, e.name";
        $elements = $db->fetchAll($sql);
        $options = array('' => __('Select Below'));
        foreach ($elements as $element) {
            $optGroup = $element['item_type_name'] 
                      ? __('Item Type') . ': ' . __($element['item_type_name']) 
                      : __($element['element_set_name']);
            $value = __($element['element_name']);
            if ($element['lc_suggest_id']) {
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
        
        $assignments = array();
        foreach ($svSuggestTable->findAll() as $svSuggest) {
            $element = $elementTable->find($svSuggest->element_id);
            $elementSet = $elementSetTable->find($element->element_set_id);
            $authorityVocabulary = $svpVocabTable->find($svSuggest->vocab_id)['name'];
            $assignments[] = array('element_set_name' => __($elementSet->name), 
                                   'element_name' => __($element->name), 
                                   'authority_vocabulary' => __($authorityVocabulary));
        }
        return $assignments;
    }

}
