<?php
/**
 * @package     JSolr.Search
 * @subpackage  View
 * @copyright   Copyright (C) 2012-2016 KnowledgeArc Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.view');
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
     * html/com_jsolr/browse/ first, then loading the default.php
     * template from the extension's views/browse/tmpl folder.
     *
     * To override the default browse page, place a file called
     * <override>_<extension>.php in the html/com_jsolr/browse/ directory,
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

        $themeOverridePath =
            JPATH_THEMES.'/'.JFactory::getApplication()->getTemplate().
            '/html/com_jsolrsearch/browse';

        if (JPath::find($themeOverridePath, $override)) {
            return $extension;
        } else {
            return null;
        }
    }
}
