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
 
class JSolrSearchViewBrowse extends JViewLegacy
{	
	protected $state;
	protected $items;
	protected $params;
	
	public function display($tpl = null)
    {
    	$this->state = $this->get("State");
    	$this->items = $this->get("Items");
    	$this->params = $this->state->get('params');
    	
    	$document = JFactory::getDocument();

    	$document->addStyleSheet(JURI::base()."media/".$this->getModel()->get('option')."/css/jsolrsearch.css");

        parent::display($tpl);
    }

	public function loadFacetsTemplate()
	{
    	$extension = str_replace("com_", "", JRequest::getCmd('o'));
  	
    	$template = null;
    	
    	if ($extension) {
    		$template = $extension."_facets.php";
    	}
    	
    	$pluginOverridePath = JPATH_PLUGINS."/jsolrsearch/".$extension.'/views';
    	$themeOverridePath = JPATH_THEMES.'/'.JFactory::getApplication()->getTemplate().
    		'/html/com_jsolrsearch/plugins';
    	
    	if ($template) {
		    if (JPath::find($pluginOverridePath, $extension."_facets.php") ||
		    	JPath::find($themeOverridePath, $extension."_facets.php")) {		    	
		    	$this->addTemplatePath(dirname(JPath::find($pluginOverridePath, $extension."_facets.php")));
		    	$this->addTemplatePath(dirname(JPath::find($themeOverridePath, $extension."_facets.php")));
		    	
		    	$this->setLayout($extension);
		    	return $this->loadTemplate('facets');
		    }
    	}
	    
	    $this->setLayout('default');
	    return $this->loadTemplate('facets');
	}
}