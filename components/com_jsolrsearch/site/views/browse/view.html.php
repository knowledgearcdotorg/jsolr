<?php
/**
 * @package		JSolr
 * @subpackage	Search
 * @copyright	Copyright (C) 2012 Wijiti Pty Ltd. All rights reserved.
 * @license     This file is part of the JSolrSearch Component for Joomla!.
 *
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
 
class JSolrSearchViewBrowse extends JView
{	
	protected $state;
	protected $items;
	
	public function display($tpl = null)
    {
    	$this->state = $this->get("State");
    	$this->items = $this->get("Items");
    	
    	$document = JFactory::getDocument();

    	$document->addStyleSheet(JURI::base()."media/".$this->getModel()->get('option')."/css/jsolrsearch.css");

		$templates = JArrayHelper::getValue($this->get('_path'), 'template');
    	
    	JPluginHelper::importPlugin("jsolrsearch");
		$dispatcher =& JDispatcher::getInstance();

		foreach ($dispatcher->trigger("onJSolrSearchExtensionGet") as $result) {
			$extension = str_replace("com_", "", JArrayHelper::getValue(array_keys($result), 0));
			$pluginOverridePath = JPATH_PLUGINS.DS."jsolrsearch".DS.$extension.DS.'views';
	    	
	    	if (array_search($pluginOverridePath, $templates) == false && 
	    		is_dir($pluginOverridePath)) {
		    	$this->addTemplatePath($pluginOverridePath);
		    }    	
		}
    	
    	$themeOverridePath = JPATH_THEMES.DS.JFactory::getApplication()->getTemplate().DS.
    		'html'.DS.'com_jsolrsearch'.DS.'plugins';

		if (array_search($themeOverridePath, $templates) == false && 
    		is_dir($themeOverridePath)) {
	    	$this->addTemplatePath($themeOverridePath);
	    }
    	
        parent::display($tpl);
    }

	public function loadResultsTemplate()
	{
    	// make item available to templates.
    	$this->assignRef("item", $item);

    	$extension = str_replace("com_", "", JRequest::getCmd('o'));
    	
    	$templates = JArrayHelper::getValue($this->get('_path'), 'template');
    	
    	$pluginOverridePath = JPATH_PLUGINS.DS."jsolrsearch".DS.$extension.DS.'views';
    	$themeOverridePath = JPATH_THEMES.DS.JFactory::getApplication()->getTemplate().DS.
    		'html'.DS.'com_jsolrsearch'.DS.'plugins';
    	
	    if (JPath::find($pluginOverridePath, $extension."_facets.php") ||
	    	JPath::find($themeOverridePath, $extension."_facets.php")) {
	    	$this->setLayout($extension);
	    	return $this->loadTemplate('facets');
	    } else {
	    	$this->setLayout('default');
	    	return $this->loadTemplate('facets');
	    }
	}
}