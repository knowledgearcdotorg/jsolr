<?php
/**
 * @package		JSolr.Search
 * @subpackage	View
 * @copyright	Copyright (C) 2012-2013 KnowledgeARC Ltd. All rights reserved.
 * @license     This file is part of the JSolrSearch Component for Joomla!.
 * The JSolrSearch Component for Joomla! is free software: you can redistribute it 
 * and/or modify it under the terms of the GNU General Public License as 
 * published by the Free Software Foundation, either version 3 of the License, 
 * or (at your option) any later version.
 *
 * The JSolrSearch Component for Joomla! is distributed in the hope that it will be 
 * useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with the JSolrSearch Component for Joomla!.  If not, see 
 * <http://www.gnu.org/licenses/>.
 *
 * Contributors
 * Please feel free to add your name and email (optional) here if you have 
 * contributed any source code changes.
 * Name							Email
 * Hayden Young					<hayden@knowledgearc.com>
 */
 
defined( '_JEXEC' ) or die( 'Restricted access' );
 
jimport('joomla.filesystem.path');
jimport('joomla.utilities.arrayhelper');
jimport('joomla.application.module');
jimport('joomla.application.component');

jimport('jsolr.helper');
 
class JSolrSearchViewBasic extends JViewLegacy
{	
	protected $items;
	protected $state;
    protected $form;
    protected $params;
	
	public function display($tpl = null)
    {
        $this->form = $this->get('Form');
        $this->items = $this->get('Items');
        $this->state = $this->get('State');
        $this->params = $this->state->get('params');
        $this->plugins = $this->get('ComponentsList');
        
        $this->params->set('o', str_replace('com_', '', JFactory::getApplication()->input->get('o', null, 'cmd')));

        // Load JSolrSearch jquery if not bundled.
        // @deprecated This is deprecated and will be removed in subsequent versions of JSolr.
        JLoader::import('joomla.version');
        $version = new JVersion();
        if (version_compare($version->RELEASE, '3.0', 'lt')) {
        	JFactory::getDocument()->addScript(JURI::base().'/media/com_jsolrsearch/js/jquery/jquery.js');
        } else {
        	JHtml::_('bootstrap.framework');
        }
        
        if ($this->getLayout() == 'default') {
        	$tpl = $this->_getDefaultTemplate();
        }
        echo $this->get('AdvancedURI');
        parent::display($tpl);
    }
    
    private function _getDefaultTemplate()
    {
    	$o = $this->params->get('o');
    	$extension = str_replace("com_", "", $o);
    	$override = 'default_'.$extension.'.php';

    	$pluginOverridePath = JPATH_PLUGINS."/jsolrsearch/".$extension.'/views';
    	$themeOverridePath = JPATH_THEMES.'/'.JFactory::getApplication()->getTemplate().
    	'/html/com_jsolrsearch/basic';
    	
    	if (!JPath::find($themeOverridePath, $override)) {
    		if (JPath::find($pluginOverridePath, $override)) {
    			$this->addTemplatePath(dirname(JPath::find($pluginOverridePath, $override)));
    		} else {
    			$extension = null;
    		}
    	}
    	
    	return $extension;
    }
    
    /**
     * Loads a single result template, or loads the default template if no 
     * override is found. 
     * 
     * This method will look for the template override in the following directories
     * (and in the following order):
     * 
     * JPATH_THEMES/<template_name>/html/com_jsolrsearch/basic
     * JPATH_PLUGINS/jsolrsearch/<extension>/views
     * 
     * where <template_name> is the name of the current template, and 
     * <extension> is the indexed extension parameter in the solr document
     * sans the "com_" prefix.
     * 
     * The file name <extension>_result.php must be used when overriding 
     * the default result layout where <extension is the indexed extension 
     * parameter in the solr document sans the "com_" prefix 
     * (E.g. newsfeeds_result.php).
     * 
     * @param JSolrApacheSolrDocument $item A single solr document.
     * @param stdClass $hl Highlighted values.
     * @return string The output of the template override or the default 
	 * template if no override is found.
     */
    public function loadResultTemplate($item, $hl)
    {
    	// make item available to templates.
    	$this->assignRef("item", $item);
    	$this->assignRef("hl", $hl);
    	
    	$extension = str_replace("com_", "", $item->extension);
		return $this->_loadCustomTemplate($extension, 'result');
    }

    /**
     * Loads a results template for a particular extension, or loads the 
     * default template if no override is found.
     *
     * This method will look for the template override in the following directories
     * (and in the following order):
     *
     * JPATH_THEMES/<template_name>/html/com_jsolrsearch/plugins
     * JPATH_THEMES/<template_name>/html/com_jsolrsearch/basic
     * JPATH_PLUGINS/jsolrsearch/<extension>/views/basic
     *
     * where <template_name> is the name of the current template, and
     * <extension> is the indexed extension parameter in the solr document
     * sans the "com_" prefix.
     *
     * The file name <extension>_results.php must be used when overriding
     * the default results layout where <extension is the indexed extension
     * parameter in the solr document sans the "com_" prefix
     * (E.g. newsfeeds_results.php).
     *
     * @return string The output of the template override or the default
     * template if no override is found.
     */
	public function loadResultsTemplate()
	{
		return $this->_loadCustomTemplate($this->params->get('o'), 'results');
	}
	
	private function _loadCustomTemplate($o, $layout)
	{
		$extension = str_replace("com_", "", $o);
		$override = $layout."_".$extension.".php";
		 
		$pluginOverridePath = JPATH_PLUGINS."/jsolrsearch/".$extension.'/views';
		$themeOverridePath = JPATH_THEMES.'/'.JFactory::getApplication()->getTemplate().
		'/html/com_jsolrsearch/basic';
		 
		$this->setLayout($layout);
		 
		if (JPath::find($themeOverridePath, $override)) {
			return $this->loadTemplate($extension);
		} elseif (JPath::find($pluginOverridePath, $override)) {
			$this->addTemplatePath(dirname(JPath::find($pluginOverridePath, $override)));
			return $this->loadTemplate($extension);
		} else {
			return $this->loadTemplate('default');
		}		
	}
}
