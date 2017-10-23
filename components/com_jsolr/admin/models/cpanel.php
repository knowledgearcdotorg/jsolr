<?php
/**
 * A model that provides configuration options for JSolr.
 *
 * @package     JSolr
 * @subpackage  Model
 * @copyright   Copyright (C) 2012-2017 KnowledgeArc Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

\JLoader::import('joomla.registry.registry');
\JLoader::import('joomla.application.component.model');

use Solarium\QueryType\Luke\Query as LukeQuery;

class JSolrModelCPanel extends JModelLegacy
{
    private $client;

    public function __construct($config = array())
    {
        parent::__construct($config);

        $this->client = \JSolr\Index\Factory::getClient();
    }

    public function getItem()
    {
        $item = new \Joomla\Registry\Registry();
        $temp = new stdClass();

        $config = \JSolr\Index\Factory::getConfig();

        // No connection configured. Just return a basic item.
        if (!$config->get('url')) {
            $item->set('status', 'NO_CONFIGURATION');
        } else {
            $ping = $this->client->createPing();

            try {
                $result = $this->client->ping($ping);
                $temp->statistics = $this->getLuke()->getData();

                $item->loadArray($result->getData());
            } catch (Exception $e) {
                $item->set('status', "PING_FAILED");
            }
        }

        $temp->settings = JComponentHelper::getComponent($this->option)->params->toObject();
        $item->loadObject($temp);

        $item->set('libraries.curl', function_exists("curl_version"));

        return $item;
    }

    private function getLuke()
    {
        if (is_null($this->client)) {
            return null;
        }

        $this->client->registerQueryType(LukeQuery::QUERY_LUKE, 'Solarium\\QueryType\\Luke\\Query');
        $luke = $this->client->createQuery(LukeQuery::QUERY_LUKE);
        return $this->client->execute($luke);
    }
}
