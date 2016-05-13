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

/**
 * Simple command line interface application class.
 *
 * @package     JSolr
 * @subpackage  Cli
 */
class JSolrCli extends JApplicationCli
{
    public function __construct(\Joomla\Input\Input $input = null, \Joomla\Registry\Registry $config = null)
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
        if ($this->input->get('h') || $this->input->get('help')) {
            $this->help();
            return;
        }

        try {
            if ($this->input->get('p') || $this->input->get('purge')) {
                $this->purge();
                return;
            }

            if ($this->input->get('o') || $this->input->get('optimize')) {
                $this->optimize();
                return;
            }

            if ($this->input->get('c') || $this->input->get('clean')) {
                $this->clean();
                return;
            }

            if ($this->input->get('r') || $this->input->get('rebuild')) {
                $this->rebuild();
                return;
            }

            if ($this->input->get('d') || $this->input->get('delete')) {
                $this->delete();
                return;
            }

            if ($this->input->get('a') || $this->input->get('add')) {
                $this->add();
                return;
            }

            if ($this->input->get('i') || $this->input->get('information')) {
                $this->information();
                return;
            }

            $this->index();

        } catch (Exception $e) {
            JSolrHelper::log($e->getMessage(), JLog::ERROR);
            JSolrHelper::log($e->getTraceAsString(), JLog::DEBUG);
        }
    }

    protected function add()
    {
        // throw error right away if no id has been specified.
        if (!count($this->input->args)) {
            throw new Exception('No id specified.');
        }

        $plugin = $this->input->getString('a', $this->input->getString('add', null));

        if ($plugin == 1) {
            throw new Exception('No plugin specified.');
        }

        $id = JArrayHelper::getValue($this->input->args, 0);

        $this->fireEvent('onItemAdd', array($id), $this->isVerbose(), $plugin);
    }

    /**
     * The "clean" task target.
     *
     * Fires the onClean event.
     */
    protected function clean()
    {
        $this->fireEvent('onClean', array(get_class($this), $this->isVerbose()), $this->getPlugin());
    }

    protected function delete()
    {
        // throw error right away if no id has been specified.
        if (!count($this->input->args)) {
            throw new Exception('No id specified.');
        }

        $plugin = $this->input->getString('d', $this->input->getString('delete', null));

        if ($plugin == 1) {
            throw new Exception('No plugin specified.');
        }

        $id = JArrayHelper::getValue($this->input->args, 0);

        $this->fireEvent('onItemDelete', array($id), $plugin);
    }

    protected function index()
    {
        $indexingParams = array();

        if ($this->input->getString('u') || $this->input->getString('update')) {
            $lastModified = $this->input->getString('u', $this->input->getString('update'));

            $d = JDate::createFromFormat("Y-m-d\TH:i:sP", $lastModified, new DateTimeZone(JFactory::getConfig()->get('offset')));

            $valid = false;

            if ($d) {
                if ($d->getTimezone()) {
                    $format = "Y-m-d\TH:i:s".(($d->getTimezone()->getName() == 'Z') ? '\Z' : 'P');

                    if ($d->format($format) == $lastModified) {
                        $valid = true;
                    }
                }
            }

            if ($valid) {
                $indexingParams['lastModified'] = $lastModified;
            } else {
                $client = \JSolr\Index\Factory::getClient();

                if ($client->ping()) {
                    $response = $client->luke();

                    $indexingParams['lastModified'] = $response->index->lastModified;
                }
            }
        }

        $start = new JDate('now');

        JSolrHelper::log("crawl start ".$start->format("c"), JLog::DEBUG);

        $this->fireEvent('onIndex', array(get_class($this), $this->isVerbose(), $indexingParams), $this->getPlugin());

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

        JSolrHelper::log("Optimization finished: ".$result->getStatus(), JLog::DEBUG);
    }

    protected function purge()
    {
        $plugin = $this->getPlugin();

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

    protected function information()
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
Usage: jsolr [OPTIONS] [task]
   jsolr [OPTIONS] [u|update] <last-index-date>
   jsolr [q|v] [a|add] <plugin> <id>
   jsolr [q|v] [d|delete] <plugin> <id>

Provides tools for managing Solr from Joomla.

[OPTIONS]
  -q, --quiet         Suppress all output including errors. Overrides
                      --verbose if both options are specified.
  -v, --verbose       Display verbose information about the current action.
  -P, --plugin=name   Specify an optional plugin name (E.g. content) to run
                      tasks against a particular plugin.
[task]
  -a, --add           Add/edit a single item to/in the index, using the
                      plugin and id to determine which crawler should perform
                      the add action.
  -c, --clean         Clean out deleted items from the index.
  -d, --delete        Delete a single item from the index, using the plugin
                      and id to determine which crawler should perform the
                      delete action.
  -h, --help          Display this help and exit.
  -i, --information   Display configuration information.
  -o, --optimize      Run an optimization on the index.
  -p, --purge         Purge the contents of the index.
  -r, --rebuild       Rebuild the index, deleting then re-creating all
                      documents.
  -u, --update        Index only those items which have been created or
                      modified since the specified ISO8601-compatible date
                      or the last index date if no date is specified.

EOT;
    }

    private function fireEvent($name, $args = array(), $plugin = null)
    {
        if ($plugin) {
            if (!is_a(JPluginHelper::getPlugin('jsolrcrawler', $plugin), 'stdClass')) {
                throw new Exception('The specified plugin does not exist or is not enabled.');
            }
        }

        $dispatcher = JEventDispatcher::getInstance();

        JPluginHelper::importPlugin("jsolrcrawler", $plugin, true, $dispatcher);

        return $dispatcher->trigger($name, $args);
    }

    private function getPlugin()
    {
        return $this->input->getString('plugin', $this->input->getString('P', null));
    }
}

JApplicationCli::getInstance('JSolrCli')->execute();
