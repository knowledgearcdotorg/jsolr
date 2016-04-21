<?php

/**

 * Installation scripts.

 *

 * @package     JSolr.Search

 * @subpackage  Installer

 * @copyright   Copyright (C) 2012-2016 KnowledgeArc Ltd. All rights reserved.

 * @license     GNU General Public License version 2 or later; see LICENSE.txt

 */



defined('_JEXEC') or die('Restricted access');



jimport('joomla.installer.helper');

jimport('joomla.filesystem.folder');

jimport('joomla.filesystem.file');



class com_JSolrSearchInstallerScript

{

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