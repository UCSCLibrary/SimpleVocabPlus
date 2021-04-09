<?php
/**
 * Simple Vocabulary Plus
 * 
 * @copyright Copyright 2007-2012 UCSC Library Digital Initiatives
 * @copyright Copyright 2020-2021 Daniele Binaghi
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
 */

// Define Constants
$hcolor = get_option('simple_vocab_plus_fields_highlight');
define('SIMPLE_VOCAB_PLUS_HIGHLIGHT_COLOR', (preg_match('/#([a-f0-9]{3}){1,2}\b/i', $hcolor) ? $hcolor : ''));

/**
 * Simple Vocabulary Plus
 * 
 * @package SimpleVocabPlus
 */
class SimpleVocabPlusPlugin extends Omeka_Plugin_AbstractPlugin
{
	/**
	 * @var array Hooks for the plugin.
	 */
	protected $_hooks = array(
		'install',
		'upgrade',
		'uninstall', 
		'initialize',
		'config',
		'config_form',
		'define_acl', 
		'admin_head'
	);

	/**
	 * @var array Filters for the plugin.
	 */
	protected $_filters = array(
		'admin_navigation_main'
	);
	
	/**
	 * Install the plugin.
	 */
	public function hookInstall()
	{
		$sql1 = "CREATE TABLE `{$this->_db->SvpAssign}` (
					`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
					`element_id` int(10) unsigned NOT NULL,
					`type` varchar(10) NOT NULL,
					`enforced` boolean,
					`vocab_id` int(10) unsigned NOT NULL,
					`sources_id` varchar(100),
					PRIMARY KEY (`id`),
					UNIQUE KEY `element_id` (`element_id`)
				) ENGINE=MyISAM  DEFAULT CHARSET=utf8";
		$this->_db->query($sql1); 
		$sql2 = "CREATE TABLE `{$this->_db->SvpTerm}` (
					`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
					`vocab_id` int(10) unsigned NOT NULL,
					`term` text NOT NULL,
					PRIMARY KEY (`id`)
				) ENGINE=MyISAM  DEFAULT CHARSET=utf8";
		$this->_db->query($sql2);
		$sql3 = "CREATE TABLE `{$this->_db->SvpVocab}` (
					`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
					`name` text NOT NULL,
					`url` text NOT NULL,
					PRIMARY KEY (`id`)
				) ENGINE=MyISAM  DEFAULT CHARSET=utf8";
		$this->_db->query($sql3);

		set_option('simple_vocab_plus_files', '0');
		set_option('simple_vocab_plus_collections', '0');
		set_option('simple_vocab_plus_exhibits', '0');
		set_option('simple_vocab_plus_fields_highlight', '');
		set_option('simple_vocab_plus_fields_description', '1');
		set_option('simple_vocab_plus_values_compare', '0');
	}
	
    /**
     * Upgrade the plugin.
     */
    public function hookUpgrade($args)
    {
        $oldVersion = $args['old_version'];
        $newVersion = $args['new_version'];
        $db = $this->_db;

        if (version_compare($oldVersion, '3.0', '<')) {
			$sql1 = "ALTER TABLE `{$this->_db->SvpAssign}`
					ADD COLUMN `type` varchar(10) NOT NULL AFTER `element_id`, 
					ADD COLUMN `enforced` boolean AFTER `type`";
			$this->_db->query($sql1);
			$sql2 = "UPDATE `{$this->_db->SvpAssign}` 
					RIGHT JOIN `{$this->_db->SvpVocab}` ON `{$this->_db->SvpAssign}`.vocab_id = `{$this->_db->SvpVocab}`.id
					SET `{$this->_db->SvpAssign}`.type = 'local', `{$this->_db->SvpAssign}`.enforced = false,
					WHERE `{$this->_db->SvpVocab}`.url = 'local'";
			$this->_db->query($sql2);
			$sql3 = "UPDATE `{$this->_db->SvpAssign}`
					SET type = 'remote', enforced = false
					WHERE type <> 'local'";
			$this->_db->query($sql3);
			
			set_option('simple_vocab_plus_files', '0');
			set_option('simple_vocab_plus_collections', '0');
			set_option('simple_vocab_plus_exhibits', '0');
			set_option('simple_vocab_plus_fields_highlight', '');
			set_option('simple_vocab_plus_fields_description', '1');
			set_option('simple_vocab_plus_values_compare', '0');

            $message = __('Database has been updated correctly.');
            throw new Omeka_Plugin_Exception($message);
        } elseif (version_compare($oldVersion, '3.1', '<')) {
			$sql1 = "ALTER TABLE `{$this->_db->SvpAssign}`
					ADD COLUMN `sources_id` varchar(100) NOT NULL AFTER `vocab_id`"; 
			$this->_db->query($sql1);

            $message = __('Database has been updated correctly.');
            throw new Omeka_Plugin_Exception($message);
		}
    }

	/**
	 * Uninstall the plugin.
	 */
	public function hookUninstall()
	{
		$sql1 = "DROP TABLE IF EXISTS `{$this->_db->SvpAssign}`";
		$this->_db->query($sql1);
		$sql2 = "DROP TABLE IF EXISTS `{$this->_db->SvpVocab}`";
		$this->_db->query($sql2);
		$sql3 = "DROP TABLE IF EXISTS `{$this->_db->SvpTerm}`";
		$this->_db->query($sql3);
	  
		delete_option('simple_vocab_plus_files');
		delete_option('simple_vocab_plus_collections');
		delete_option('simple_vocab_plus_exhibits');
		delete_option('simple_vocab_plus_fields_highlight');
		delete_option('simple_vocab_plus_fields_description');
		delete_option('simple_vocab_plus_values_compare');
	}

	/**
	 * Queue the javascript and css files to help the form work.
	 *
	 * This function runs before the admin section of the sit loads.
	 * It queues the javascript and css files which help the form work,
	 * so that they are loaded before any html output.
	 *
	 * @return void
	 */
	public function hookAdminHead() {
		queue_js_file('SimpleVocabPlus');
		queue_css_string('.ui-tabs-active.ui-state-active {background: none repeat scroll 0 0 #f9f9f9;}');
		
		$filesApplyToo = get_option('simple_vocab_plus_files');
		$collectionsApplyToo = get_option('simple_vocab_plus_files');
		$exhibitsApplyToo = (get_option('simple_vocab_plus_files') && plugin_is_active('ExhibitBuilder'));
		if (get_option('simple_vocab_plus_fields_description')) {
			$suggests = get_db()->getTable('SvpAssign')->findAll();
			foreach($suggests as $suggest) {
				$element = get_db()->getTable('Element')->find($suggest->element_id);
				add_filter(array('ElementForm', 'Item', $element->getElementSet()->name, $element->name), array($this, 'markSuggestField'));
				if ($filesApplyToo) {
					add_filter(array('ElementForm', 'File', $element->getElementSet()->name, $element->name), array($this, 'markSuggestField'));
				}
				if ($collectionsApplyToo) {
					add_filter(array('ElementForm', 'Collection', $element->getElementSet()->name, $element->name), array($this, 'markSuggestField'));
				}
				if ($exhibitsApplyToo) {
					add_filter(array('ElementForm', 'Exhibit', $element->getElementSet()->name, $element->name), array($this, 'markSuggestField'));
				}
			}
		}
	}
	
	/**
	 * Initialize the plugin. Register Autosuggest controller plugin.
	 *
	 * @return void
	 */
	public function hookInitialize()
	{
		// Register the SelectFilter controller plugin.
		$front = Zend_Controller_Front::getInstance();
		$front->registerPlugin(new SimpleVocabPlus_Controller_Plugin_Autosuggest);

		// Add translation.
		add_translation_source(dirname(__FILE__) . '/languages');
	}

    public function hookConfig($args)
    {
		$post = $args['post'];
		set_option('simple_vocab_plus_files', $post['simple_vocab_plus_files']);
		set_option('simple_vocab_plus_collections', $post['simple_vocab_plus_collections']);
		set_option('simple_vocab_plus_exhibits', $post['simple_vocab_plus_exhibits']);
		set_option('simple_vocab_plus_fields_highlight', $post['simple_vocab_plus_fields_highlight']);
		set_option('simple_vocab_plus_fields_description', $post['simple_vocab_plus_fields_description']);
		set_option('simple_vocab_plus_values_compare', $post['simple_vocab_plus_values_compare']);
    }
    
    public function hookConfigForm()
    {
        include 'config_form.php';
    }
	
	/**
	 * Define the plugin's access control list.
	 *
	 * @return void
	 */
	public function hookDefineAcl($args)
	{
		$args['acl']->addResource('SimpleVocabPlus_Index');
	}
	
	/**
	 * Add the Simple Vocab Plus page to the admin navigation.
	 *
	 *@param array $nav The admin nav array to be filtered
	 *
	 *@return array $nav The filtered nav array
	 */
	public function filterAdminNavigationMain($nav)
	{
		$nav[] = array(
			'label' => __('Simple Vocab Plus'), 
			'uri' => url('simple-vocab-plus'), 
			'resource' => 'SimpleVocabPlus_Index', 
			'privilege' => 'index', 
		);
		return $nav;
	}

	/**
	 * Add a note to element's description.
	 *
	 *@param array $components The elements having a vocabulary associated
	 *
	 *@return array $components The amended component array
	 */
	public function markSuggestField($components, $args) {
		$components['description'] = $components['description'] . '<i>' . __('(Please note: autosuggest feature active - Simple Vocab Plus plugin)') . '</i><br>';
		return($components);
	}
}
