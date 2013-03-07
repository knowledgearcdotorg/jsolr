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
 
jimport( 'joomla.application.component.view');
jimport('joomla.filesystem.path');
jimport('joomla.utilities.arrayhelper');
 
class JSolrSearchViewBasic extends JView
{	
	protected $items;
	protected $state;
    protected $form;
	
	public function display($tpl = null)
    {
        $this->form = $this->get('Form');
        $this->items = $this->get('Items');
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
     * @return string The output of the template override or the default 
	 * template if no override is found.
     */
    public function loadResultTemplate($item)
    {
    	// make item available to templates.
    	$this->assignRef("item", $item);

    	$extension = str_replace("com_", "", $item->extension);

    	@$templates = JArrayHelper::getValue($this->get('_path'), 'template');

    	$pluginOverridePath = JPATH_PLUGINS.DS."jsolrsearch".DS.$extension.DS.'views';
    	$themeOverridePath = JPATH_THEMES.DS.JFactory::getApplication()->getTemplate().DS.
    		'html'.DS.'com_jsolrsearch'.DS.'plugins';
    	
	    if (JPath::find($pluginOverridePath, $extension."_result.php") ||
	    	JPath::find($themeOverridePath, $extension."_result.php")) {
	    	$this->setLayout($extension);
	    } else {
	    	$this->setLayout('results');
	    }
	    
	    return $this->loadTemplate('result');
    }

	public function loadResultsTemplate()
	{
    	$extension = str_replace("com_", "", JRequest::getCmd('o'));
    	
    	$templates = JArrayHelper::getValue($this->get('_path'), 'template');
    	
    	$pluginOverridePath = JPATH_PLUGINS.DS."jsolrsearch".DS.$extension.DS.'views';
    	$themeOverridePath = JPATH_THEMES.DS.JFactory::getApplication()->getTemplate().DS.
    		'html'.DS.'com_jsolrsearch'.DS.'plugins';
    	
	    if (JPath::find($pluginOverridePath, $extension."_results.php") ||
	    	JPath::find($themeOverridePath, $extension."_results.php")) {
	    	$this->setLayout($extension);
	    	return $this->loadTemplate('results');
	    } else {
	    	$this->setLayout('results');
	    	return $this->loadTemplate('default');
	    }
	}

    public function loadFormTemplate()
    {
        return $this->loadTemplate('form');
    }
}