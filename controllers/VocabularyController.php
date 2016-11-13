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

        $name = $_REQUEST['nv-name'];
        // Local.
        if ($_REQUEST['nv-local'] == 'local') {
            $text = $_REQUEST['nv-definetext'];
            $vocab = $this->_helper->db->getTable('SvpVocab')->createVocab($name, 'local');
            $success = $this->_helper->db->getTable('SvpTerm')->addFromText($vocab->id, $text);
        }
        // Remote..
        elseif ($_REQUEST['nv-local'] == 'remote') {
            $url = $_REQUEST['nv-url'];
            $vocab = $this->_helper->db->getTable('SvpVocab')->createVocab($name,$url);
            $vocab->updateNow();
        }
        $flash = $this->_helper->FlashMessenger;
        $flash->addMessage(__('Your vocabulary has been created successfully. You may now assign it to metadata elements.'), 'success');
        $this->_helper->redirector('index', 'index');
    }

    public function editAction()
    {
        $this->_validatePost();

        if (isset($_REQUEST['vocab']) && $_REQUEST['vocab'] != '') {
            $vocab_id = $_REQUEST['vocab'];
        }
        else {
            //throw new Exception('no vocab chosen');
            $this->_helper->flashMessenger(__('There was a problem editing your vocabulary.'), 'error');
            $this->_helper->redirector('index', 'index');
        }

        if (isset($_REQUEST['ev-url']) && $_REQUEST['ev-url'] !=' local') {
            $url = $_REQUEST['ev-url'];
            $vocab = $this->_helper->db->getTable('SvpVocab')->find($vocab_id);
            $vocab->url = $url;
            $vocab->save();
        }
        // Edit text.
        elseif (isset($_REQUEST['ev-edittext'])) {
            $text = $_REQUEST['ev-edittext'];
            $possibleUpdates = $this->_helper->db->getTable('SvpTerm')->updateFromText($vocab_id, $text);
            if ($possibleUpdates) {
                //prompt about updates
                $updates = $this->_promptUpdates($possibleUpdates);
                //if there are updates
                //update terms for all assigned records
                $this->_updateRecords($vocab_id, $updates);
            }
        }
        $this->_helper->flashMessenger(__('Vocabulary edited successfully'), 'success');
        $this->_helper->redirector('index', 'index');
    }

    public function getAction()
    {
        $db = $this->_helper->db;
        $vocabId = $this->getParam('vocab');
        $vocab = $db->getTable('SvpVocab')->find($vocabId);
        $terms = $db->getTable('SvpTerm')->findBy(array('vocab_id' => $vocabId));
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
     * @param string $vocab
     * @return bool
     */
    private function _vocabExists($vocab)
    {
        $vocab = $this->_helper->db->getTable('SvpVocab')->find($vocab);
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
