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
 * @package SvPlus
 */
class SimpleVocabPlus_VocabularyController extends Omeka_Controller_AbstractActionController
{

    public function addAction() {

      $this->_validatePost();

	$name = $_REQUEST['nv-name'];
      if($_REQUEST['nv-local']=='local') {
	$text = $_REQUEST['nv-definetext'];
	$vocab = $this->_helper->db->getTable('SvpVocab')->createVocab($name,'local');
	
	$success = $this->_helper->db->getTable('SvpTerm')->addFromText($vocab->id,$text);

      } else if($_REQUEST['nv-local']=='remote') {
	$url = $_REQUEST['nv-url'];
	$vocab = $this->_helper->db->getTable('SvpVocab')->createVocab($name,$url);
	$vocab->updateNow();
      }
      $flash = $this->_helper->FlashMessenger;
      $flash->addMessage('Your vocabulary has been created successfully. You may now assign it to metadata elements.','success');

      $this->_helper->redirector('index','index');

	
    }


    public function editAction() {

      $this->validatePost();
      
      if(isset($_REQUEST['vocab']) && $_REQUEST['vocab'] !='')
          $vocab_id=$_REQUEST['vocab'];
      else
          $this->_helper->flashMessenger(__('There was a problem editing your vocabulary.'), 'error');
          //throw new Exception('no vocab chosen');

      if(isset($_REQUEST['ev-url']) && ($url=$_REQUEST['ev-url']) !='local') {

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
      $this->_helper->flashMessenger(__('Vocabulary edited successfully'), 'success');
      $this->_helper->redirector('index','index');
    }

    public function getAction()
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

    private function validatePost(){
      $csrf = new Omeka_Form_SessionCsrf;
      if(!$csrf->isValid($_POST)){
	$flash->addMessage('There was an error processing your request.','error');
	$this->_helper->redirector('index','index');
      }
    }
    

}