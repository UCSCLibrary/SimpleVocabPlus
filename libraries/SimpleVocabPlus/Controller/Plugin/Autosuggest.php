<?php
/**
 * Simple Vocab Plus
 *
 * @copyright Copyright 2014 UCSC Library Digital Initiatives
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
 */

/**
 * The Simple Vocab Plus controller plugin.
 *
 * @package SimpleVocabPlus
 */
class SimpleVocabPlus_Controller_Plugin_Autosuggest extends Zend_Controller_Plugin_Abstract
{
	/**
	 * Include all routes (route + controller + actions) that render an
	 * element form, including actions requested via AJAX.
	 * 
	 * @var array
	 */
	protected $_defaultRoutes = array(
		array(
			'module' => 'default',
			'controller' => 'items',
			'actions' => array('add', 'edit', 'change-type')
		),
		array(
			'module' => 'default',
			'controller' => 'elements',
			'actions' => array('element-form')
		)
	);

	/**
	 * Cached vocab terms.
	 */
	protected $_svpSuggestedTerms;
	protected $_elementText;

    public function __construct()
    {
        $this->backgroundColor = get_option('simple_vocab_plus_fields_highlight');
		$this->backgroundColorString = (preg_match('/#([a-f0-9]{3}){1,2}\b/i', $this->backgroundColor) ? 'background-color: ' . $this->backgroundColor : '');
    }

	/**
	 * Add autosuggest only during defined routes.
	 */
	public function preDispatch(Zend_Controller_Request_Abstract $request)
	{
		$db = get_db();

		// Set NULL modules to default. Some routes do not have a default
		// module, which resolves to NULL.
		$module = $request->getModuleName();
		if (is_null($module)) {
			$module = 'default';
		}
		$controller = $request->getControllerName();
		$action = $request->getActionName();

		$filterFiles = get_option('simple_vocab_plus_files');
		if ($filterFiles) {
			// Add the file add/edit route if configured to.
			$this->_defaultRoutes[] = array(
				'module' => 'default',
				'controller' => 'files',
				'actions' => array('add', 'edit')
			);
		}

		$filterCollections = get_option('simple_vocab_plus_collections');
		if ($filterCollections) {
			// Add the collections add/edit route if configured to.
			$this->_defaultRoutes[] = array(
				'module' => 'default',
				'controller' => 'collections',
				'actions' => array('add', 'edit')
			);
		}

		$filterExhibits = (get_option('simple_vocab_plus_exhibits') && plugin_is_active('ExhibitBuilder'));
		if (filterExhibits) {
			// Add the exhibit add/edit route if configured to.
			$this->_defaultRoutes[] = array(
				'module' => 'default',
				'controller' => 'exhibits',
				'actions' => array('add', 'edit')
			);
		}

		// Allow plugins to add routes that contain form inputs rendered by
		// Omeka_View_Helper_ElementForm::_displayFormInput().
		$routes = apply_filters('svp_suggest_routes', $this->_defaultRoutes);

		// Iterate the defined routes.
		foreach ($routes as $route) {
			// Set the autosuggest if the current action matches a defined route.
			if ($route['module'] === $module
					&& $route['controller'] === $controller
					&& in_array($action, $route['actions'])
				) {
				// Iterate the elements that are assigned to a suggest endpoint.
				$svpAssigns = $db->getTable('SvpAssign')->findAll();
				foreach ($svpAssigns as $svpAssign) {
					$element = $db->getTable('Element')->find($svpAssign->element_id);
					$elementSet = $db->getTable('ElementSet')->find($element->element_set_id);
					$elementTextTable = $db->getTable('ElementText');
					$svpTermTable = $db->getTable('SvpTerm');

					if (!$svpAssign->enforced) {
						// Add the autosuggest JavaScript to the JS queue.
						$view = Zend_Registry::get('view');
						$view->headScript()->captureStart();
?>
// Add autosuggest to <?php echo $elementSet->name . ':' . $element->name; ?>. Used by the Simple Vocab Plus plugin.
jQuery(document).bind('omeka:elementformload', function(event) {
	jQuery('#element-<?php echo $element->id; ?> textarea').autocomplete({
		minLength: 2,
		source: <?php echo json_encode($view->url('simple-vocab-plus/endpoint/suggest-proxy/element-id/' . $element->id)); ?>
	});
});
<?php
						$view->headScript()->captureEnd();
					} else {	
						// Retrieve values to populate select box.
						switch ($svpAssign->type) {
							case 'self':
								$select = $elementTextTable->getSelect();
								$select->from(array(), 'text')
									->where('record_type = ?', 'Item')
									->where('element_id = ?', $element->id)
									->group('text')
									->order('text ASC');
								$this->_svpSuggestedTerms[$element->id] = $elementTextTable->fetchObjects($select);
								break;
							case 'multi':
								$select = $elementTextTable->getSelect();
								$select->from(array(), 'text')
									->where('record_type = ?', 'Item')
									->where('element_id IN (?)', $svpAssign->sources_id)
									->group('text')
									->order('text ASC');
								$this->_svpSuggestedTerms[$element->id] = $elementTextTable->fetchObjects($select);
								break;
							default:
								$select = $svpTermTable->getSelect();
								$select->from(array(), array('text' => 'term'))
									->where('vocab_id = ?', $svpAssign->vocab_id)
									->order('id ASC');
								$this->_svpSuggestedTerms[$element->id] = $svpTermTable->fetchObjects($select);
						}
					}

					add_filter(
						array('ElementInput', 'Item', $elementSet->name, $element->name),
						array($this, 'filterElementInput')
					);
					// Add the file filter if configured to.
					if ($filterFiles) {
						add_filter(
							array('ElementInput', 'File', $elementSet->name, $element->name),
							array($this, 'filterElementInput')
						);
					}
					// Add the collection filter if configured to.
					if ($filterCollections) {
						add_filter(
							array('ElementInput', 'Collection', $elementSet->name, $element->name),
							array($this, 'filterElementInput')
						);
					}
					// Add the exhibit filter if configured to.
					if ($filterExhibits) {
						add_filter(
							array('ElementInput', 'Exhibit', $elementSet->name, $element->name),
							array($this, 'filterElementInput')
						);
					}
				}
			}
		}
	}
	
	/**
	 * Filter the element input.
	 * 
	 * @param array $components
	 * @param array $args
	 * @return array
	 */
	public function filterElementInput($components, $args)
	{
		// Use the cached vocab terms
		if (empty($this->_svpSuggestedTerms[$args['element']->id])) {
			// case autosuggest, values not enforced
			$components['input'] = get_view()->formTextarea(
				$args['input_name_stem'] . '[text]',
				$args['value'],
				array('cols' => 50, 'rows' => 3, 'style' => $this->backgroundColorString)
			);
		} else {
			// case autosuggest, values enforced
			$components['input'] = get_view()->formSelect(
				$args['input_name_stem'] . '[text]', 
				$args['value'], 
				array('style' => $this->backgroundColorString), 
				$this->getTermsList($this->_svpSuggestedTerms[$args['element']->id])
			);
			$components['html_checkbox'] = false;
		}
		
		return $components;
	}
	
	private function getTermsList($terms)
	{
		$selectTerms = array('' => __('Select Below'));
		$blanks_count = 1;
		$terms_count = count($terms);

		for ($i = 0; $i < $terms_count; $i++) {
			$term_text = $terms[$i]['text'];
			if ($term_text == '---') {
				$selectTerms[str_repeat(' ', $blanks_count)] = array();
				$blanks_count++;
			} elseif (substr($term_text, 0, 3) == '***') {
				$stem = substr($term_text, 3);
				$subterms = array();
				$i++;
				while ($i < $terms_count) {
					$term_text = $terms[$i]['text'];
					if ($term_text != '---' && substr($term_text, 0, 3) != '***') {
						$subterms[$term_text] = $term_text;
						$i++;
					} else {
						$i = $i - 1;
						break;
					}
				}
				$selectTerms[$stem] = $subterms;
			} else {
				$selectTerms[$term_text] = $term_text;
			}
		}

		return $selectTerms;
	}
}
