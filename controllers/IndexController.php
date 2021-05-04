<?php
/**
 * Simple Vocab Plus
 *
 * @copyright Copyright 2014 UCSC Library Digital Initiatives
 * @copyright Copyright 2021 Daniele Binaghi
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
 */

/**
 * The Simple Vocab Plus controller.
 *
 * @package SimpleVocabPlus
 */
class SimpleVocabPlus_IndexController extends Omeka_Controller_AbstractActionController
{
    /**
     * Initialize this controller.
     */
    public function init()
    {
        // Restrict actions to AJAX requests.
        $this->_helper->getHelper('AjaxContext')
                      ->addActionContexts(array('element-texts' => 'html'))
                      ->initContext();
    }

    public function indexAction()
    {
        $this->view->form_element_options_marked = $this->_getFormElementOptions(true);
        $this->view->form_element_options = $this->_getFormElementOptions(false);
        $this->view->form_vocab_options = $this->_getFormSuggestOptions();
        $this->view->assignments = $this->_getAssignments();

        $csrf = new Omeka_Form_SessionCsrf;
        $this->view->csrf = $csrf;

        foreach (get_db()->getTable('SvpVocab')->findAll() as $vocab) {
            $vocab->updateNow();
        }
    }

    /**
     * Get an array to be used in formSelect() containing all elements, 
     * with the ones already assigned marked.
     *
     * @return array
     */
    private function _getFormElementOptions($marked=true)
    {
        $db = $this->_helper->db->getDb();
        $sql = "
			SELECT es.name AS element_set_name,
				e.id AS element_id,
				e.name AS element_name,
				it.name AS item_type_name,
				gv.id AS gv_suggest_id
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
            if ($marked && $element['gv_suggest_id']) {
                $value .= ' *';
            }
            $options[$optGroup][$element['element_id']] = $value;
        }
        return $options;
    }

    /**
     * Get an array to be used in formSelect() containing all suggest endpoints.
     *
     * @return array
     */
    private function _getFormSuggestOptions()
    {
        $vocabs = $this->_helper->db->getTable('SvpVocab')->findAll();

        foreach ($vocabs as $vocab) {
            $options[$vocab['id']] = $vocab['name'];
        }
        
        arsort($options);
        
        $options[''] = __('Select Below');
        
        return array_reverse($options, true);
    }

    /**
     * Get all the authority/vocabulary assignments.
     *
     * @return array
     */
    private function _getAssignments()
    {
        $svpSuggestTable = $this->_helper->db->getTable('SvpAssign');
        $svpVocabTable = $this->_helper->db->getTable('SvpVocab');
        $elementTable = $this->_helper->db->getTable('Element');
        $elementSetTable = $this->_helper->db->getTable('ElementSet');
        $itemTypeTable = $this->_helper->db->getTable('ItemType');
        $itemTypesElementsTable = $this->_helper->db->getTable('ItemTypesElements');

        $assignments = array();
        foreach ($svpSuggestTable->findAll() as $svpSuggest) {
            $element = $elementTable->find($svpSuggest->element_id);
            $elementSet = $elementSetTable->find($element->element_set_id);
            $elementSetName = __($elementSet->name);
            if ($itemTypesElements = $itemTypesElementsTable->findByElement($element->id)) {
                $itemTypesElement = $itemTypesElements[0];
                $itemType = $itemTypeTable->find($itemTypesElement->item_type_id);
                $elementSetName .= ': ' . $itemType->name;
            }
            $authorityVocabulary = $svpVocabTable->find($svpSuggest->vocab_id);
            $authorityVocabularyName = $authorityVocabulary['name'];
			if ($svpSuggest->sources_id != '') {
				$sources_id = split(',', $svpSuggest->sources_id);
				$sources_name = array();
				foreach ($sources_id as $source_id) {
					$sources_name[] = __($elementTable->find($source_id)->name);
				}
				$sources_names = implode(', ', $sources_name);
			} else {
				$sources_names = '';
			}

            $assignments[] = array(
                'suggest_id' => $svpSuggest->id,
                'element_set_name' => $elementSetName,
                'element_set_id' => $elementSet->id,
                'element_name' => $element->name,
                'element_id' => $svpSuggest->element_id,
                'authority_vocabulary' => __($authorityVocabularyName),
                'authority_vocabulary_id' => $authorityVocabulary->id,
                'type' => __($svpSuggest->type),
                'enforced' => __($svpSuggest->enforced),
				'sources_id' => $svpSuggest->sources_id,
				'sources_names' => $sources_names
            );
        }
        return $assignments;
    }

    /**
     * Render the element texts.
     * 
     * Available only via an AJAX request.
     */
    public function elementTextsAction()
    {
        $elementId = $this->getRequest()->getParam('element_id');
        $warningMessages = array(
								'shortText' => __('Short text'),
                                'longText' => __('Long text'),
                                'containsNewlines' => __('Contains newlines'),                                 
                                'containsHTML' => __('Contains HTML code')
                            );
        
        // Get the local vocabulary's terms, if any
        $elementVocabTerms = $this->findElementTerms($elementId);

        // Get the element's element texts, if any
        $elementTexts = array();
        $comparisonEnabled = get_option('simple_vocab_plus_values_compare');
        foreach ($this->findElementTexts($elementId) as $elementText) {
			$text = htmlspecialchars($elementText->text, ENT_QUOTES);
            $warnings = array();
            if (strlen($text) < 3) {
                $warnings[] = $warningMessages['shortText'];
            }
            if (strlen($text) > 100) {
                $warnings[] = $warningMessages['longText'];
            }
            if (strstr($elementText->text, "\n")) {
                $warnings[] = $warningMessages['containsNewlines'];
            }
            if ($this->contains_html($elementText->text)) {
                $warnings[] = $warningMessages['containsHTML'];
            }
            $elementTexts[] = array('element_id' => $elementId, 
                                    'count' => $elementText->count, 
                                    'warnings' => $warnings, 
                                    'text' => ($comparisonEnabled ? (in_array($elementText->text, $elementVocabTerms) ? $text : '***' . $text . '***') : $text));
        }
        
        $this->view->element_texts = $elementTexts;
    }

    /**
     * Checks whether string contains any HTML tag.
     * 
     * @param str $string
     * @return boolean
     */
    public function contains_html($string)
    {
        return preg_match("/<[^<]+>/", $string, $m) != 0;
    }

    /**
     * Find distinct element texts for a specific element.
     * 
     * @param int $elementId
     * @return array
     */
    public function findElementTexts($elementId)
    {
        $db = get_db(); 
        $select = $db->select()
                     ->from($db->ElementText, array('text', 'COUNT(*) AS count'))
                     ->group('text')
                     ->where('element_id = ?', $elementId)
                     ->where('record_type = ?', 'Item')
                     ->order(array('count DESC', 'text ASC'));
        return $db->getTable('ElementText')->fetchObjects($select);
    }

    /**
     * Find distinct local vocabulary's terms for a specific element.
     *
     * @return array
     */
    public function findElementTerms($elementId)
    {
        $db = $this->_helper->db->getDb();
        $sql = "SELECT st.term 
                FROM {$db->SvpTerm} st 
                JOIN {$db->SvpAssign} sa ON st.vocab_id = sa.vocab_id
                WHERE sa.element_id = ?
                AND sa.type = 'local'
                ORDER BY term ASC";
        return $db->fetchCol($sql, $elementId);
    }
}
