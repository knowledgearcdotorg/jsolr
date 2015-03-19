<?php
/**
 * @package		JSolr.Search
 * @subpackage	View
 * @copyright   Copyright (C) 2012-2015 KnowledgeArc Ltd. All rights reserved.
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
 * Hayden Young					<hayden@knowledgearc.com>
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

        parent::display($this->_getDefaultTemplate());
    }

    /**
     * Gets the default template, searching for it in the
     * html/com_jsolrsearch/browse/ first, then loading the default.php
     * template from the extension's views/browse/tmpl folder.
     *
     * To override the default browse page, place a file called
     * <override>_<extension>.php in the html/com_jsolrsearch/browse/ directory,
     * where <override> is the name of the base layout you are overriding (in
     * most cases this will be "default"), and <extension> is the name of the
     * component whose data you are trying to browse.
     *
     * E.g.
     *
     * default_content.php
     */
    private function _getDefaultTemplate()
    {
    	$o = JFactory::getApplication()->input->get('o');
    	$extension = str_replace("com_", "", $o);

    	$override = $this->getLayout().'_'.$extension.'.php';

    	$themeOverridePath = JPATH_THEMES.'/'.JFactory::getApplication()->getTemplate().
    	'/html/com_jsolrsearch/browse';

    	if (JPath::find($themeOverridePath, $override)) {
    		return $extension;
    	} else {
    		return null;
    	}
    }
}