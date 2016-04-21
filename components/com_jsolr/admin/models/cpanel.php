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

\JLoader::import('joomla.registry.registry');
\JLoader::import('joomla.application.component.model');

use Solarium\Client;
use Solarium\QueryType\Luke\Query as LukeQuery;

class JSolrModelCPanel extends JModelLegacy
{
    private $client;

    public function __construct($config = array())
    {
        parent::__construct($config);

        $config = array('endpoint'=>array('localhost'=>array('host'=>'127.0.0.1', 'port'=>8983, 'path'=>'/solr/search/')));

        $this->client = new Solarium\Client($config);
    }

    public function getItem()
    {
        $ping = $this->client->createPing();

        try {
            $result = $this->client->ping($ping);

            $temp = new stdClass();

            $temp->statistics = $this->getLuke()->getData();
            $temp->settings = JComponentHelper::getComponent('com_jsolr')->params->toObject();

            $item = new JRegistry();

            $item->loadArray($result->getData());
            $item->loadObject($temp);

            $item->set('libraries.curl', function_exists("curl_version"));

            return $item;
        } catch (Solarium\Exception $e) {
            $this->setError(JText::_("COM_JSOLRSEARCH_PING_FAILED"));
        }
    }

    private function getLuke()
    {
        $this->client->registerQueryType(LukeQuery::QUERY_LUKE, 'Solarium\\QueryType\\Luke\\Query');
        $luke = $this->client->createQuery(LukeQuery::QUERY_LUKE);
        return $this->client->execute($luke);
    }
}
