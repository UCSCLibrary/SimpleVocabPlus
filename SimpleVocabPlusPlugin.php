<?php
/**
 * Simple Vocabulary Plus
 * 
 * @copyright Copyright 2007-2012 UCSC Library Digital Initiatives
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
 */

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
			     'uninstall', 
			    'initialize', 
			    'define_acl', 
			    'admin_head'
			      );

  /**
   * @var array Filters for the plugin.
   */
    protected $_filters = array(
        'admin_navigation_main', 
    );
    
    /**
     * Install the plugin.
     */
    public function hookInstall()
    {
        $sql1 = "
        CREATE TABLE `{$this->_db->SvpAssign}` (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `element_id` int(10) unsigned NOT NULL,
            `vocab_id` int(10) unsigned NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `element_id` (`element_id`)
        ) ENGINE=MyISAM  DEFAULT CHARSET=utf8";
        $this->_db->query($sql1); 
	$sql2 = "
        CREATE TABLE `{$this->_db->SvpTerm}` (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `vocab_id` int(10) unsigned NOT NULL,
            `term` text NOT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=MyISAM  DEFAULT CHARSET=utf8";
	$this->_db->query($sql2);
	$sql3 = "
        CREATE TABLE `{$this->_db->SvpVocab}` (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `name` text NOT NULL,
            `url` text NOT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=MyISAM  DEFAULT CHARSET=utf8";
        $this->_db->query($sql3);
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
      
    }

    public function markSuggestField($components, $args) {
        $components['description'] = $components['description']." (This element has autosuggest activated using the Simple Vocab Plus plugin)";
        return($components);
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

        $suggests = get_db()->getTable('SvpAssign')->findAll();
        foreach($suggests as $suggest) {
            $element = get_db()->getTable('Element')->find($suggest->element_id);
            add_filter(array('ElementForm', 'Item', $element->getElementSet()->name, $element->name),array($this,'markSuggestField'));
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
        //add_translation_source(dirname(__FILE__) . '/languages');
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
}
