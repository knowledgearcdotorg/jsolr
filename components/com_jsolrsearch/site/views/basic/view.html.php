<?php
/**
 * @package		JSolr
 * @subpackage	Search
 * @copyright	Copyright (C) 2012 Wijiti Pty Ltd. All rights reserved.
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
 * Hayden Young					<haydenyoung@wijiti.com>
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
	
	public function display($tpl = null)
    {
        $this->form = $this->get('Form');
        $this->items = $this->get('Items');
        $this->state = $this->get('State');
        $this->plugins = $this->get('ComponentsList');
        $this->params = JComponentHelper::getParams('com_jsolrsearch',true);

        $mod = JModuleHelper::getModule('mod_jsolrfilter');
        $this->moduleEnabled = ($mod->id != 0);
        
        $this->showFilters = !$this->moduleEnabled && JSolrSearchModelSearch::showFilter() && $this->params->get('facet_show',false);

        parent::display($tpl);
    }
    
    /**
     * Loads a single result template, or loads the default template if no 
     * override is found. 
     * 
     * This method will look for the template override in the following directories
     * (and in the following order):
     * 
     * JPATH_THEMES/<template_name>/html/com_jsolrsearch/plugins
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

    	@$templates = JArrayHelper::getValue($this->get('_path'), 'template');

    	$pluginOverridePath = JPATH_PLUGINS."/jsolrsearch/".$extension.'/views';
    	$themeOverridePath = JPATH_THEMES.'/'.JFactory::getApplication()->getTemplate().
    		'/html/com_jsolrsearch/plugins';
    	
	    $this->addTemplatePath(dirname(JPath::find($pluginOverridePath, $extension."_result.php")));

	    if (JPath::find($pluginOverridePath, $extension."_result.php") ||
	    	JPath::find($themeOverridePath, $extension."_result.php")) {
            $this->addTemplatePath(dirname(JPath::find($pluginOverridePath, $extension."_result.php")));
            $this->addTemplatePath(dirname(JPath::find($themeOverridePath, $extension."_result.php")));
	    	$this->setLayout($extension);
	    } else {
	    	$this->setLayout('results');
	    }
	    
	    return $this->loadTemplate('result');
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
    	$extension = str_replace("com_", "", JRequest::getCmd('o'));

        $_path =$this->get('_path');
    	$templates = JArrayHelper::getValue($_path, 'template');
    	
    	$pluginOverridePath = JPATH_PLUGINS."/jsolrsearch/".$extension.'/views';
    	$themeOverridePath = JPATH_THEMES.'/'.JFactory::getApplication()->getTemplate().
    		'/html/com_jsolrsearch/plugins';
    	
	    if (JPath::find($pluginOverridePath, $extension."_results.php") ||
	    	JPath::find($themeOverridePath, $extension."_results.php")) {

	    	$this->setLayout($extension);
            $this->addTemplatePath(dirname(JPath::find($pluginOverridePath, $extension."_result.php")));
            $this->addTemplatePath(dirname(JPath::find($themeOverridePath, $extension."_result.php")));
	    	return $this->loadTemplate('results');
	    } else {
	    	$this->setLayout('results');
	    	return $this->loadTemplate('default');
	    }
	}
	
	/**
	 * Return template with facet filters.
	 * @return string
	 */
	public function loadFacetFiltersTemplate() {
        return $this->loadTemplate('filters');
	}

    public function loadFormTemplate()
    {
        return $this->loadTemplate('form');
    }

    public function loadFacetFiltersSelectedTemplate()
    {
        return $this->loadTemplate('facets_selected');
    }

    /**
     * Method to get information if "Search Tools" button should be rendered
     * @return true if the button should be visiable, otherwise return false
     */
    public function showSearchToolsButton()
    {
        return is_null(JSolrSearchModelSearch::getFacetFilterForm());
    }

    /**
     * Method to get the number of components that will be dispalyed
     * More components than this number will be displayed in dropdown menu with label "More"
     * @return integer
     */
    public function getComponentsLimit()
    {
        return $this->params->get('filter_count',3);
    }
}
