<?php
/**
 * @version		$LastChangedBy$
 * @package		Wijiti
 * @subpackage	JSolrSearch
 * @copyright	Copyright (C) 2010 Wijiti Pty Ltd. All rights reserved.
 * @license     This file is part of the JSolrSearch Component for Joomla!.

   The JSolrSearch Component for Joomla! is free software: you can redistribute it 
   and/or modify it under the terms of the GNU General Public License as 
   published by the Free Software Foundation, either version 3 of the License, 
   or (at your option) any later version.

   The JSolrSearch Component for Joomla! is distributed in the hope that it will be 
   useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   GNU General Public License for more details.

   You should have received a copy of the GNU General Public License
   along with the JSolrSearch Component for Joomla!.  If not, see 
   <http://www.gnu.org/licenses/>.

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
    function display($tpl = null)
    {
    	$document = JFactory::getDocument();

    	$document->addStyleSheet(JURI::base()."media/com_jsolrsearch/css/jsolrsearch.css");

        parent::display($tpl);
    }
    
    public function loadResultTemplate($item)
    {
    	// make item available to templates.
    	$this->assignRef("item", $item);
    	
    	$pluginsPath = JPATH_PLUGINS.DS."jsolrsearch".DS;
    	$path = false;

    	JPluginHelper::importPlugin("jsolrsearch");
		$dispatcher =& JDispatcher::getInstance();

		$results = $dispatcher->trigger("onFindResultTemplatePath", array($item->option));

		$i = 0;
		while (!$path && $i < count($results)) {
			if (JArrayHelper::getValue($results, $i)) {
				$path = JArrayHelper::getValue($results, $i);
			}
			
			$i++;
		}
    	
    	if (is_dir($pluginsPath)) {
			if ($handle = opendir($pluginsPath)) {
				while (!$path && $plugin = readdir($handle)) {
					if ($plugin != "." && $plugin != "..") {
						$path = JPath::find($pluginsPath.$plugin.DS."views".DS."result", $item->option.".php");
							
				    	// Next look for default layout in plugin.
				    	if (!$path) {
				    		$path = JPath::find($pluginsPath.$plugin.DS."views".DS."result", "default.php");
				    	}
					}
				}
			}
    	}
    	
    	// if a custom layout path is found, output it, otherwise fall back to component default.
    	if ($path) {
			ob_start();
			include $path;
			$output = ob_get_contents();
			ob_end_clean();
			
			return $output;
    	} else {
			return $this->loadTemplate("result");
    	}
    }

	public function loadResultsTemplate($option)
	{
    	$path = false;

		JPluginHelper::importPlugin("jsolrsearch");
		$dispatcher =& JDispatcher::getInstance();

		$results = $dispatcher->trigger("onFindResultsTemplatePath", array($option));

		$i = 0;
		while (!$path && $i < count($results)) {
			if (JArrayHelper::getValue($results, $i)) {
				$path = JArrayHelper::getValue($results, $i);
			}
			
			$i++;
		}

    	// if a custom layout path is found, output it, otherwise fall back to component default.
    	if ($path) {
			ob_start();
			include $path;
			$output = ob_get_contents();
			ob_end_clean();
			
			return $output;
    	} else {
			return $this->loadTemplate("default");
    	}
	}
}