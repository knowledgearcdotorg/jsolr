<?php
/**
 * @author		$LastChangedBy: spauldingsmails $
 * @paackage	Wijiti
 * @subpackage	JSolr
 * @copyright	Copyright (C) 2011 Wijiti Pty Ltd. All rights reserved.
 * @license     This file is part of the JSolrSearch component for Joomla!.

   The JSolrSearch component for Joomla! is free software: you can redistribute it 
   and/or modify it under the terms of the GNU General Public License as 
   published by the Free Software Foundation, either version 3 of the License, 
   or (at your option) any later version.

   The JSolrSearch component for Joomla! is distributed in the hope that it will be 
   useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   GNU General Public License for more details.

   You should have received a copy of the GNU General Public License
   along with the JSolrSearch component for Joomla!.  If not, see 
   <http://www.gnu.org/licenses/>.

 * Contributors
 * Please feel free to add your name and email (optional) here if you have 
 * contributed any source code changes.
 * Name							Email
 * Hayden Young					<haydenyoung@wijiti.com> 
 * 
 */
 
// no direct access
defined('_JEXEC') or die();

jimport('joomla.error.log');
jimport('joomla.language.helper');
jimport('joomla.utilities.arrayhelper');

abstract class JSolrSearchPlugin extends JPlugin 
{
	/**
	 * Constructor
	 *
	 * @param 	object $subject The object to observe
	 * @param 	array  $config  An array that holds the plugin configuration
	 * @param 	string  $name  The plugin name.
	 * @since 1.5
	 */
	public function __construct(&$subject, $config)
	{
		parent::__construct($subject, $config);
		$this->set("option", JArrayHelper::getValue($config, "option"));
		
		$this->loadLanguage(null, JPATH_ADMINISTRATOR);
	}
	
	/**
	 * Returns a list of Solr DISMAX query fields when triggered.
	 * 
	 * Use this event to define boost fields.
	 * 
	 * @return array An array of DISMAX query fields.
	 */
	public abstract function onAddQF();
	
	/**
	 * Returns a list of Solr highlight fields when triggered.
	 * 
	 * @return array An array of highlight fields.
	 */
	public abstract function onAddHL();

	/**
	 * Returns a single filter option returned within an array when triggered.
	 * 
	 * The single item array holds an array key which corresponds with one or 
	 * more option names (stored in Solr's option field) and the value holds 
	 * the translated label for that key.
	 * 
	 * If the plugin's jsolr_show_filter_label parameter is set, the returned 
	 * result takes the form: 
	 * $array[option] = OPTION_TRANSLATED_TO_TEXT
	 *
	 * Otherwise, the result will look like:
	 * $array[option] = null
	 *
	 * The key can be made up of more than one option, with options separated 
	 * by a comma. 
	 * 
	 * @return array A single option returned within an array.
	 */
	public function onFilterOptions()
	{		
		$options = array();

		if ($this->get("params")->get("jsolr_show_filter_label", false)) {
			$options[$this->get("option")] = JText::_("PLG_JSOLRSEARCH_".strtoupper($this->get("_name"))."_FILTER_LABEL");
		} else {
			$options[$this->get("params")->get("option")] = null;
		}
	
		return $options;
	}

	/**
	 * Retrieve the individual result template for this plugin.
	 * 
	 * @param string $option The option used to identify the associated 
	 * template.
	 * 
	 * @return string The path to the individual result template for this 
	 * plugin.
	 */
	public function onFindResultTemplatePath($option)
	{
		$pluginsPath = JPATH_PLUGINS.DS."jsolrsearch".DS;		
		
		$path = false;

		// if the o query string has not been set, exit immediately.
		if (!$option) {
			return $path;	
		}

		$path = JPath::find($pluginsPath.$this->getTemplateDirectoryName().DS."views".DS."results", $option.".php");

		if (!$path) {
			$path = JPath::find($pluginsPath.$this->getTemplateDirectoryName().DS."views".DS."result", "default.php");
		}
		
		return $path;
	}
	
	/**
	 * Retrieve the custom results template for this plugin.
	 * 
	 * @param string $o The currently selected option(s).
	 * 
	 * @return string The path to the custom results template for this plugin. 
	 */
	public function onFindResultsTemplatePath($o)
	{
		$pluginsPath = JPATH_PLUGINS.DS."jsolrsearch".DS;		
		
		$path = false;

		// if the o query string has not been set, exit immediately.
		if (!$o) {
			return $path;	
		}
		
		$options = explode(",", $o);
	
		while (!$path && $option = current($options)) {
			if (array_key_exists($option, $this->onFilterOptions())) {
				$path = JPath::find($pluginsPath.$this->getTemplateDirectoryName().DS."views".DS."results", "default.php");
			}
			
			next($options);
		}
		
		return $path;
	}
	
	/**
	 * Maps a Solr document to a generic result object.
	 * 
	 * @param Apache_Solr_Document $document A Solr document.
	 * @param stdClass $hl Highlighted fields.
	 * @param int $hlFragSize The size of the highlighted fragment.
	 * @param string $lang The language the result should be returned in.
	 * 
	 * @return stdClass A generic result object.
	 */
	public abstract function onFormatResult($document, $hl, $hlFragSize, $lang);
	
	protected function getTemplateDirectoryName()
	{
		return str_ireplace("jsolr", "", $this->get("_name"));
	}
}