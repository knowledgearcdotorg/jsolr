<?php
/**
 * A controller for managing content sharing.
 *
 * @package     JSolr.Search
 * @subpackage  Controller
 * @copyright   Copyright (C) 2012-2017 KnowledgeArc Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use JSolr\Search\Factory;

class JSolrController extends JControllerLegacy
{
    public function advanced()
    {
        $model = $this->getModel("advanced");

        $this->setRedirect(JRoute::_((string)$model->getUri(), false));
    }

    public function search()
    {
        $this->setRedirect(JRoute::_(\JSolr\Search\Factory::getSearchRoute(), false));
    }

    public function display($cachable = false, $urlparams = false)
    {
        $default = "search";

        $viewName = JFactory::getApplication()->input->get("view", $default, 'cmd');

        $model = $this->getModel($viewName);

        // Add more views for custom layouts; xlsx, xml, etc.
        $this->addViewPath(JPATH_THEMES.'/'.
            JFactory::getApplication()->getTemplate().'/html/com_jsolr');

        $view = $this->getView(
            $viewName,
            JFactory::getApplication()->input->get("format", "html", 'cmd'));

        $view->setModel($model, true);

        $query = JFactory::getApplication()->input->get("q", null, 'html');

        if (($viewName == "" || $viewName == $default) &&
            (trim($query) ||
            $model->getAppliedFacetFilters())) {
            $view->setLayout("results");
        }

        return parent::display($cachable, $urlparams);
    }
}
