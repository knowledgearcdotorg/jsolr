<?php
/**
 * @copyright   Copyright (C) 2013-2016 KnowledgeArc Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace JSolr;

require_once JPATH_ROOT.'/libraries/JSolr/vendor/autoload.php';

use \JFactory as JFactory;
use \JComponentHelper as JComponentHelper;
use \JLanguageHelper as JLanguageHelper;
use Solarium\Client;

/**
 * The JSolr factory class.
 */
abstract class Factory
{
    /**
     * @static
     */
    protected static $component = 'com_jsolr';

    /**
     * @static
     */
    public static $config = null;

    /**
     * Gets a client connection to the Solr Service using settings from the
     * specified component.
     *
     * @return  Solarium\Client  An instance of the Solarium\Client class or
     * null if the connection is not configured.
     */
    public static function getClient()
    {
        $params = self::getConfig();

        $url = new \JUri($params->get('url'));

        $endpoint = \JUri::getInstance()->getHost();

        $client = new \Solarium\Client();

        $client->removeEndpoint('localhost');
        $client->addEndpoint($client->createEndpoint($endpoint, true));

        if ($params->get('url')) {
            $client->getEndpoint($endpoint)->setHost($url->getHost());
            $client->getEndpoint($endpoint)->setPort($url->getPort());
            $client->getEndpoint($endpoint)->setPath($url->getPath());
            $client->getEndpoint($endpoint)->setTimeout(60000);

            if ($params->get('username') && $params->get('password')) {
                $client->getEndpoint($endpoint)->setAuthentication(
                    $params->get('username'),
                    $params->get('password'));
            }
        }

        if ($params->get('connection2')) {
            if ($params->get('url2')) {
                $endpoint = $endpoint."2";

                $client->addEndpoint($client->createEndpoint($endpoint));

                $url = new \JUri($params->get('url2'));

                $client->getEndpoint($endpoint)->setHost($url->getHost());
                $client->getEndpoint($endpoint)->setPort($url->getPort());
                $client->getEndpoint($endpoint)->setPath($url->getPath());

                if ($params->get('username2') && $params->get('password2')) {
                    $client->getEndpoint($endpoint)->setAuthentication(
                        $params->get('username2'),
                        $params->get('password2'));
                }
            }
        }

        return $client;
    }

    /**
     * Gets the Solr component's configuration parameters.
     *
     * @return JRegistry The Solr component's configuration parameters.
     * @throws Exception An exception when the configuration parameters cannot be loaded.
     */
    public static function getConfig()
    {
        if (!self::$config) {
            $lang = JFactory::getLanguage();

            $lang->load('lib_jsolr', JPATH_SITE, JLanguageHelper::detectLanguage(), true);

            if (!JComponentHelper::isEnabled(static::$component, true)) {
                throw new Exception(JText::sprintf('LIB_JSOLR_ERROR_COMPONENT_NOT_FOUND', static::$component), 404);
            }

            if (!self::$config = JComponentHelper::getParams(static::$component, true)) {
                throw new Exception(JText::sprintf('LIB_JSOLR_ERROR_PARAMS_NOT_LOADED', static::$component), 404);
            }
        }

        return self::$config;
    }
}
