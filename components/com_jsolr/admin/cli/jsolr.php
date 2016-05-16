#!/usr/bin/php
<?php
/**
 * @package     JSolr
 * @subpackage  Cli
 * @copyright   Copyright (C) 2012-2016 KnowledgeArc Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// Make sure we're being called from the command line, not a web interface
if (array_key_exists('REQUEST_METHOD', $_SERVER)) die();

/**
 * This is a CRON script which should be called from the command-line, not the
 * web. For example something like:
 * /usr/bin/php /path/to/site/cli/jcrawl.php
 */

// Set flag that this is a parent file.
define('_JEXEC', 1);

// Load system defines
if (file_exists(dirname(dirname(__FILE__)) . '/defines.php')) {
        require_once dirname(dirname(__FILE__)) . '/defines.php';
}

if (!defined('_JDEFINES')) {
    define('JPATH_BASE', dirname(dirname(__FILE__)));
    require_once JPATH_BASE . '/includes/defines.php';
}

// Get the framework.
if (file_exists(JPATH_LIBRARIES . '/import.legacy.php'))
    require_once JPATH_LIBRARIES . '/import.legacy.php';
else
    require_once JPATH_LIBRARIES . '/import.php';

// Bootstrap the CMS libraries.
require_once JPATH_LIBRARIES . '/cms.php';

// Load the configuration
require_once JPATH_CONFIGURATION . '/configuration.php';

// System configuration.
$config = new JConfig;

// Configure error reporting to maximum for CLI output.
error_reporting(E_ALL & ~E_STRICT);
ini_set('display_errors', 1);

// Load Library language
$lang = JFactory::getLanguage();

// Try the finder_cli file in the current language (without allowing the loading of the file in the default language)
$lang->load('jsolr_cli', JPATH_SITE, null, false, false)
// Fallback to the finder_cli file in the default language
|| $lang->load('jsolr_cli', JPATH_SITE, null, true);

\JLoader::registerNamespace('JSolr', JPATH_PLATFORM);

$path = JPATH_ADMINISTRATOR.'/components/com_jsolr/helpers/jsolr.php';
\JLoader::register('JSolrHelper', $path);

\JLoader::import('joomla.application.component.helper');

use \Joomla\Utilities\ArrayHelper;
use \Joomla\Input\Input;
use \Joomla\Registry\Registry;
use Solarium\QueryType\Luke\Query as LukeQuery;

/**
 * Simple command line interface application class.
 *
 * @package     JSolr
 * @subpackage  Cli
 */
class JSolrCli extends JApplicationCli
{
    public function __construct(Input $input = null, Registry $config = null)
    {
        parent::__construct($input, $config);

        $GLOBALS['application'] = $this;

        // fool the system into thinking we are running as JSite with JSolr as the active component
        $_SERVER['HTTP_HOST'] = 'domain.com';
        JFactory::getApplication('site');

        // Disable caching.
        $config = JFactory::getConfig();
        $config->set('caching', 0);
        $config->set('cache_handler', 'file');
    }

    public function doExecute()
    {
        $command = ArrayHelper::getValue($this->input->args, 0, null, 'word');

        try {
            if ($command) {
                $this->$command();
            } else {
                $this->help();
            }
        } catch (Exception $e) {
            JSolrHelper::log($e->getMessage(), \JLog::ERROR);
            JSolrHelper::log($e->getTraceAsString(), \JLog::DEBUG);
        }
    }

    /**
     * The "clean" task target.
     *
     * Fires the onClean event.
     */
    protected function clean()
    {
        // throw error right away if correct number of args have not been specified.
        if (count($this->input->args) !== 2) {
            throw new Exception('Usage: jsolr clean [<plugin>] [<options>]');
        }

        $plugin = ArrayHelper::getValue($this->input->args, 1, null, 'word');

        $this->fireEvent('onJSolrClean', array(), $plugin);
    }

    protected function index()
    {
        // throw error right away if correct number of args have not been specified.
        if (count($this->input->args) < 1 && count($this->input->args) > 3) {
            throw new Exception('Usage: jsolr index [<sub-command>] [<last-index-date>] [<options>]');
        }

        $subCommand = ArrayHelper::getValue($this->input->args, 1, null, 'word');

        $lastModified = null;

        if ($subCommand !== null) {
            switch ($subCommand) {
                case "update":
                    $format = "Y-m-d\TH:i:sP";
                    $lastModified = ArrayHelper::getValue($this->input->args, 2, null, 'string');
                    $tz = new DateTimeZone(JFactory::getConfig()->get('offset'));

                    if ($lastModified) {
                        $lastModified = JDate::createFromFormat($format, $lastModified, $tz);

                        if ($lastModified === false) {
                            throw new Exception("Invalid last modified date.");
                        }
                    } else { // use lastmodified from Solr index.
                        $client = \JSolr\Index\Factory::getClient();

                        $client->registerQueryType(LukeQuery::QUERY_LUKE, 'Solarium\\QueryType\\Luke\\Query');
                        $luke = $client->createQuery(LukeQuery::QUERY_LUKE);
                        $response = $client->execute($luke);

                        $lastModified = JFactory::getDate($response->getLastModified(), $tz);
                    }

                    $lastModified = $lastModified->format($format);

                    break;

                default:
                    throw new Exception('Sub command not found. Available sub commands; update');
                    break;
            }
        }

        $start = new JDate('now');

        JSolrHelper::log("crawl start ".$start->format("c"), JLog::DEBUG);

        $this->fireEvent('onJSolrIndex', array($lastModified));

        $end = new JDate('now');

        JSolrHelper::log("crawl end ".$end->format("c"), JLog::DEBUG);

        $time = $start->diff($end);

        JSolrHelper::log("execution time: ".$time->format("%H:%I:%S"), JLog::DEBUG);
    }

    protected function optimize()
    {
        JSolrHelper::log('optimizing...', JLog::DEBUG);

        $client = \JSolr\Index\Factory::getClient();

        $update = $client->createUpdate();
        $update->addOptimize(); // TODO: using solr defaults. Need to research further.
        $result = $client->update($update);

        JSolrHelper::log("optimization finished: ".$result->getStatus(), JLog::DEBUG);
    }

    protected function purge()
    {
        // throw error right away if correct number of args have not been specified.
        if (count($this->input->args) !== 2) {
            throw new Exception('Usage: jsolr purge <plugin>');
        }

        $plugin = ArrayHelper::getValue($this->input->args, 1, null, 'string');

        if ($plugin) {
            JSolrHelper::log('purging '.$plugin.' items...', \JLog::DEBUG);

            $this->fireEvent('onPurge', array(), $plugin);

            JSolrHelper::log('purging '.$plugin.' items completed', \JLog::DEBUG);
        } else {
            $client = \JSolr\Index\Factory::getClient();

            JSolrHelper::log('purging all items from index...', JLog::DEBUG);

            // more efficient than calling each plugin's onPurge.
            $update = $client->createUpdate();

            $update->addDeleteQuery('*:*');
            $update->addCommit();

            $result = $client->update($update);

            JSolrHelper::log('purging index completed: '.$result->getStatus(), JLog::DEBUG);
        }
    }

    protected function rebuild()
    {
        $this->purge();
        $this->index();
    }

    protected function config()
    {
        $config = \JSolr\Index\Factory::getConfig();

        echo <<<EOT

Copyright (C) 2012-2016 KnowledgeArc Ltd. All rights reserved.
GNU General Public License version 2 or later; see LICENSE.txt


EOT;

        foreach ($config->flatten() as $key=>$value) {
            echo <<<EOT
{$key}={$value}

EOT;
        }

        echo <<<EOT


EOT;
    }

    /**
     * Method to build and print the help screen text to stdout.
     *
     * @return void
     * @since 1.0
     */
    protected function help()
    {
        echo <<<EOT
Usage: jsolr [command] [<sub-command>] [<args>] [<options>]

Provides tools for managing Solr from Joomla.

COMMAND
  clean         Clean out deleted items from the index.
  help          Display this help and exit.
  config        Display configuration information.
  optimize      Run an optimization on the index.
  purge         Purge the contents of the index.
  rebuild       Rebuild the index, deleting then re-creating all
                documents.
  update        Index only those items which have been created or
                modified since the specified ISO8601-compatible date
                or the last index date if no date is specified.

OPTIONS
  -q, --quiet         Suppress all output including errors. Overrides
                      --verbose if both options are specified.
  -v, --verbose       Display verbose information about the current action.

EOT;
    }

    private function helpPurge()
    {
        echo <<<EOT
Usage: jsolr purge [<plugin>] [<options>]

Purge items from the index. If <plugin> is specified, the plugin's onPurge
event will be fired. If no <plugin> is specified, all items are deleted
from the index.
EOT;
    }

    private function fireEvent($name, $args = array(), $plugin = null)
    {
        if ($plugin) {
            if (!is_a(JPluginHelper::getPlugin('jsolr', $plugin), 'stdClass')) {
                throw new Exception('The specified plugin does not exist or is not enabled.');
            }
        }

        $dispatcher = JEventDispatcher::getInstance();

        JPluginHelper::importPlugin("jsolr", $plugin, true, $dispatcher);

        return $dispatcher->trigger($name, $args);
    }
}

JApplicationCli::getInstance('JSolrCli')->execute();
