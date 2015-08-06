<?php
/**
 * Installation scripts.
 *
 * @package    JSolr.Index
 * @copyright  Copyright (C) 2012-2015 KnowledgeArc Ltd. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

jimport('joomla.installer.helper');
jimport('joomla.filesystem.folder');

class Com_JSolrIndexInstallerScript
{
    private $dependencies = array(
        'libraries'=>array(
            'jsolr'=>array(
                'published'=>true
            )
        ),
        'modules'=>array(
            'mod_jsolrconnectionmonitor'=>array(
                'published'=>true,
                'client_id'=>'1',
                'position'=>'jsolrindex',
                'title'=>'MOD_JSOLRCONNECTIONMONITOR_DEFAULT_INDEX_TITLE',
                'params'=>'{"service":"index"}',
                'installed_position'=>'jsolrindex' // use for replacing modules by position.
            )
        ),
        'plugins'=>array(
        //    'plg_system_mysystemplugin'=>array(
        //        'published'=>true
        //    )
        )
    );

    public function install($parent)
    {

    }

    public function update(JAdapterInstance $adapter)
    {
        $src = $adapter->getParent()->getPath('source');

        $site = $adapter->getParent()->getPath('extension_site');

        $admin = $adapter->getParent()->getPath('extension_administrator');

        $attributes = $adapter->getParent()->get('manifest')->media->attributes();

        $attributes = reset($attributes);

        $extension = \Joomla\Utilities\ArrayHelper::getValue($attributes, 'destination');

        $exclude = array();

        $exclude[] = $adapter->get('manifest_script');

        $exclude[] = JFile::getName($adapter->getParent()->getPath('manifest'));

        $this->removeRedundantFiles($site, $src.'/site');

        $this->removeRedundantFiles($admin, $src.'/admin', $exclude);

        if ($extension) {
            $media = JPATH_ROOT.'/media/'.$extension;

            $this->removeRedundantFiles($media, $src.'/media', $exclude);
        }
    }

    public function uninstall($parent)
    {
        $src = JPATH_ROOT."/cli/jsolr_crawler.php";

        if (JFile::exists($src)) {
            if (JFile::delete($src)) {
                echo "<p>Crawler uninstalled from ".$src." successfully.</p>";
            } else {
                echo "<p>Could not uninstall crawler from ".$src.". You will need to manually remove it.</p>";
            }
        }
    }

    public function postflight($type, $parent)
    {
        $crawler = $this->installCrawler($parent);

        $dependencies = $this->installDependencies($parent);
        ?>

        <table class="adminlist table table-striped" style="width: 100%;">
            <thead>
                <tr>
                    <th class="title">Extension</th>
                    <th width="30%">Status</th>
                </tr>
            </thead>

            <tbody>
                <tr>
                    <td>JSolr Crawler</td>
                    <td>
                        <?php if ($crawler) : ?>
                        <strong style="color: green">Installed</strong>
                        <?php else : ?>
                        <strong style="color: red">Not Installed</strong>
                        <?php endif; ?>
                    </td>
                </tr>

                <?php foreach ($dependencies as $key=>$value) : ?>

                <tr>
                    <td><?php echo $key; ?></td>
                    <td>
                        <?php if (JArrayHelper::getValue($value, 'status') == 1) : ?>
                        <strong style="color: green">Installed</strong>
                        <?php elseif (JArrayHelper::getValue($value, 'status') == 2) : ?>
                        <strong>Up-to-date</strong>
                        <?php else : ?>
                        <strong style="color: red">Not Installed</strong>
                        <?php endif; ?>
                    </td>
                </tr>

                <?php endforeach; ?>
            </tbody>
        </table>
        <?php
    }

    private function installCrawler($parent)
    {
        $success = false;

        $src = $parent->getParent()->getPath('extension_administrator').
            '/cli/jsolr_crawler.php';

        $cli = JPATH_ROOT.'/cli/jsolr_crawler.php';

        if (JFile::exists($src)) {
            if ($success = JFile::move($src, $cli)) {
                JFolder::delete($parent->getParent()->getPath('extension_administrator').'/cli');
            }
        }

        return $success;
    }

    /**
     * Installs all dependencies.
     *
     * @param JAdapterInstance $parent
     */
    private function installDependencies($parent)
    {
        $installed = array();

        $installer = new JInstaller();

        $installer->setOverwrite(true);

        $src = $parent->getParent()->getPath('source');

        foreach ($this->dependencies as $type=>$extension) {
            foreach ($extension as $name=>$params) {
                $packageZip = $src.'/'.$type.'/'.$name.'.zip';

                if (JFile::exists($packageZip)) {
                    if ($package = JInstallerHelper::unpack($packageZip)) {
                        $doInstall = true;

                        $path = $this->getInstalledManifest($type, $name, $params);

                        $oldManifest = null;

                        if (JFile::exists($path)) {
                            $oldManifest = $installer->parseXMLInstallFile($path);
                        }

                        $dir = JArrayHelper::getValue($package, 'dir').'/';

                        $path = $this->getExtractedManifest($dir, $type, $name);

                        $newManifest = $installer->parseXMLInstallFile($path);

                        if ($oldManifest) {
                            $oldVersion = JArrayHelper::getValue($oldManifest, 'version');

                            $newVersion = JArrayHelper::getValue($newManifest, 'version');

                            if (version_compare($oldVersion, $newVersion, 'ge')) {
                                $doInstall = false;
                            }
                        }

                        $success = true;

                        if ($doInstall) {
                            if ($success = $installer->install($dir)) {
                                $installed[$name] = array('status'=>1);
                            } else {
                                $installed[$name] = array('status'=>0);
                            }
                        } else {
                            $installed[$name] = array('status'=>2);
                        }

                        if ($success) {
                            // post installation configuration.
                            if ($type == 'modules') {
                                $this->configureModule($name, $params);
                            }
                        }

                        JInstallerHelper::cleanupInstall($packageZip, $dir);
                    }
                }
            }
        }

        return $installed;
    }

    private function getInstalledManifest($type, $name, $params)
    {
        $path = JPATH_ROOT;

        switch ($type) {
            case 'libraries':
                $path.="/administrator/manifests/libraries/$name";

                break;

            case 'modules':
                if (JArrayHelper::getValue($params, 'client_id') == 1) {
                    $path.="/administrator";
                }

                $path.="/modules/$name/$name";

                break;

            case 'plugins':
                if (count($parts = explode('_', $name, 2)) == 3) {
                    $path.='/plugins/'.JArrayHelper::getValue($parts, 1).'/'.JArrayHelper::getValue($parts, 2);
                }
        }

        return $path.'.xml';
    }

    private function getExtractedManifest($path, $type, $name)
    {
        switch ($type) {
            case 'libraries':
                $path.="/$name";

                break;

            case 'modules':
                $path.="/$name";

                break;

            case 'plugins':
                if (count($parts = explode('_', $name, 2)) == 3) {
                    $path.='/'.JArrayHelper::getValue($parts, 2);
                }
        }

        return $path.'.xml';
    }

    /**
     * Configure the module using the module's params.
     *
     * @param string $name The name of the extension; E.g. mod_mymodule.
     * @param string $params An array of parameters that should be used to
     * configure the module.
     */
    private function configureModule($name, $params)
    {
        $db = JFactory::getDbo();

        $query = $db->getQuery(true)
            ->select('COUNT(*)')
            ->from('#__modules')
            ->where("module = '$name'");

        if ($clientId = JArrayHelper::getValue($params, 'client_id')) {
            $query->where("client_id = $clientId");
        }

        if ($installedPosition = JArrayHelper::getValue($params, 'installed_position')) {
            $query->where("position = '$installedPosition'");
        }

        $count = $db->setQuery($query)->loadResult();

        if (!$count) {
            $language = JFactory::getLanguage();

            $language->load($name, JPATH_ADMINISTRATOR, null, true);

            // Set up module per config preferences.
            if (count($params)) {
                $query = $db->getQuery(true)
                    ->update($db->qn('#__modules'))
                    ->where($db->qn('module').' = '.$db->q($name));

                if ($title = JArrayHelper::getValue($params, 'title')) {
                    $query->set($db->qn('title').' = '.$db->q(JText::_($title)));
                }

                if ($position = JArrayHelper::getValue($params, 'position')) {
                    $query->set($db->qn('position').' = '.$db->q($position));
                }

                if(JArrayHelper::getValue($params, 'published')) {
                    $query->set($db->qn('published').' = '.$db->q('1'));
                }

                $db->setQuery($query);

                $db->execute();
            }

            // Make accessible on every page.
            $query = $db->getQuery(true)
                ->select('id')
                ->from($db->qn('#__modules'))
                ->where($db->qn('module').' = '.$db->q($name));

            $db->setQuery($query);

            $moduleId = $db->loadResult();

            $query = $db->getQuery(true)
                ->select('*')
                ->from($db->qn('#__modules_menu'))
                ->where($db->qn('moduleid').' = '.$db->q($moduleId));

            $db->setQuery($query);

            $assignments = $db->loadObjectList();

            if(!$assignments) {
                $object = (object)array('moduleid'=>$moduleId, 'menuid'=>0);

                $db->insertObject('#__modules_menu', $object);
            }
        }
    }

    /**
     * Removes redundant files and directories from previous versions that no
     * longer apply to the new version.
     */
    private function removeRedundantFiles($oldPath, $newPath, $exclude = array())
    {
        foreach (JFolder::files($oldPath, '.', true, true, $exclude) as $file) {
            if (JFile::exists($file)) {
                if (!JFile::exists(str_replace($oldPath, $newPath, $file))) {
                    Jfile::delete($file);
                }
            }
        }

        foreach (JFolder::folders($oldPath, '.', true, true, $exclude) as $folder) {
            if (JFolder::exists($folder)) {
                if (!JFolder::exists(str_replace($oldPath, $newPath, $folder))) {
                    JFolder::delete($folder);
                }
            }
        }
    }
}
