#!/usr/bin/php
<?php
/**
 * @package     JSolr
 * @subpackage  Index
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
$lang->load('jsolrcrawler_cli', JPATH_SITE, null, false, false)
// Fallback to the finder_cli file in the default language
|| $lang->load('jsolrcrawler_cli', JPATH_SITE, null, true);

JLoader::registerNamespace('JSolr', JPATH_PLATFORM);

JLoader::import('joomla.application.component.helper');

/**
 * Simple command line interface application class.
 *
 * @package JSolr.Index.CLI
 */
class JSolrCrawlerCli extends JApplicationCli
{
    public function doExecute()
    {
        if ($this->input->get('h') || $this->input->get('help')) {
            $this->help();
            return;
        }

        // fool the system into thinking we are running as JSite with JSolr as the active component
        $_SERVER['HTTP_HOST'] = 'domain.com';
        JFactory::getApplication('site');

        // Disable caching.
        $config = JFactory::getConfig();
        $config->set('caching', 0);
        $config->set('cache_handler', 'file');

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
            $this->out($e->getMessage());

            if ($this->isVerbose()) {
                $this->out($e->getTraceAsString());
            }
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
                $client = \JSolr\Index\Factory::getService();

                if ($client->ping()) {
                    $response = $client->luke();

                    $indexingParams['lastModified'] = $response->index->lastModified;
                }
            }
        }

        $start = new JDate('now');

        $this->out("crawl start ".$start->format("c"));

        $this->fireEvent('onIndex', array(get_class($this), $this->isVerbose(), $indexingParams), $this->getPlugin());

        $end = new JDate('now');

        $this->out("crawl end ".$end->format("c"));

        $time = $start->diff($end);

        $this->out("execution time: ".$time->format("%H:%I:%S"));
    }

    protected function optimize()
    {
        $client = \JSolr\Index\Factory::getService();

        if ($client->ping()) {
            $client->optimize();
        }
    }

    protected function purge()
    {
        $plugin = $this->getPlugin();

        if ($plugin) {
            $this->fireEvent('onPurge', array(get_class($this), $this->isVerbose()), $plugin);
        } else {
            $solr = \JSolr\Index\Factory::getService();

            if ($solr->ping()) {
                $this->out('purging all items from index...');

                // more efficient than calling each plugin's onPurge.
                $solr->deleteByQuery("*:*");
                $solr->commit();

                $this->out('purging index completed.');
            }
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
Usage: jsolrcrawler [OPTIONS] [task]
   jsolrcrawler [OPTIONS] [u|update] <last-index-date>
   jsolrcrawler [q|v] [a|add] <plugin> <id>
   jsolrcrawler [q|v] [d|delete] <plugin> <id>

Provides tools for managing a Joomla-centric Solr index.

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

    public function out($text = '', $nl = true)
    {
        if (!($this->input->get('q', false) || $this->input->get('quiet', false))) {
            parent::out($text, $nl);
        }

        return $this;
    }

    private function fireEvent($name, $args = array(), $plugin = null)
    {
        if ($plugin) {
            if (!is_a(JPluginHelper::getPlugin('jsolrcrawler', $plugin), 'stdClass')) {
                throw new Exception('The specified plugin does not exist or is not enabled.');
            }
        }

        $dispatcher = JEventDispatcher::getInstance();

        JPluginHelper::importPlugin("jsolr", $plugin, true, $dispatcher);

        return $dispatcher->trigger($name, $args);
    }

    private function isVerbose()
    {
        // Verbose can only be set if quiet is not set.
        if (!($this->input->get('q', false) || $this->input->get('quiet', false))) {
            if ($this->input->get('v') || $this->input->get('verbose')) {
                return true;
            }
        }

        return false;
    }

    private function getPlugin()
    {
        return $this->input->getString('plugin', $this->input->getString('P', null));
    }
}

JApplicationCli::getInstance('JSolrCrawlerCli')->execute();
