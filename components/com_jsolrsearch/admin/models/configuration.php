<?php
/**
 * A model that provides configuration options for JSolrSearch.
 *
 * @package     JSolr.Search
 * @subpackage  Model
 * @copyright   Copyright (C) 2012-2016 KnowledgeArc Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

jimport('joomla.registry.registry');
jimport('joomla.application.component.model');

use \JSolr\Search\Factory;

class JSolrSearchModelConfiguration extends JModelLegacy
{
    public function test()
    {
        $solr = Factory::getService();

        $response = $solr->ping();

        if ($response === false) {
            $this->setError(JText::_("COM_JSOLRSEARCH_PING_FAILED"));

            return false;
        }

        return true;
    }
}
