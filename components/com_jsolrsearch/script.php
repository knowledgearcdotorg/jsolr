<?php
/**
 * Installation scripts.
 *
 * @package		JSolr.Search
 * @subpackage	Installer
 * @copyright   Copyright (C) 2012-2015 KnowledgeArc Ltd. All rights reserved.
 * @license     This file is part of the JSolrSearch component for Joomla!.

   The JSolrSearch component for Joomla! is free software: you can redistribute it
   and/or modify it under the terms of the GNU General Public License as
   published by the Free Software Foundation, either version 3 of the License,
   or (at your option) any later version.

   The JSolrSearch component for Joomla! is distributed in the hope that it will be
   useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   GNU General Public License for more details.

   You should have received a copy of the GNU General Public License
   along with the JSolrSearch component for Joomla!.  If not, see
   <http://www.gnu.org/licenses/>.

 * Contributors
 * Please feel free to add your name and email (optional) here if you have
 * contributed any source code changes.
 * Name							Email
 * Hayden Young					<hayden@knowledgearc.com>
 *
 */

defined('_JEXEC') or die('Restricted access');

jimport('joomla.installer.helper');
jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');

class com_JSolrSearchInstallerScript
{
	public function install(JAdapterInstance $adapter)
	{
/*
 * @todo Implement package installer to install complimentary plugins?
		$installer = new JInstaller();
		$installer->_overwrite = true;

		$pkg_path = JPATH_ADMINISTRATOR.'/components/'.self::COM_JSOLRSEARCH.'/extensions/';

		if ($handle = opendir($pkg_path)) {
			while ($pkg = readdir($handle)) {
				if ($pkg != "." && $pkg != ".." && $pkg != "index.html") {
					$package = JInstallerHelper::unpack($pkg_path.$pkg);

					if ($installer->install($package['dir'])) {
						$msgcolor = "#E0FFE0";
						$msgtext  = "$pkg successfully installed.";
					} else {
						$msgcolor = "#FFD0D0";
						$msgtext  = "ERROR: Could not install $pkg. Please install manually.";
					}
					?>

					<table bgcolor="<?php echo $msgcolor; ?>" width ="100%">
						<tr style="height:30px">
							<td width="50px"><img src="/administrator/images/tick.png" height="20px" width="20px"></td>
							<td><font size="2"><b><?php echo $msgtext; ?></b></font></td>
						</tr>
					</table>

					<?php
					JInstallerHelper::cleanupInstall($pkg_path.$pkg, $package['dir']);
				}
			}
		}
 */
	}

	public function update(JAdapterInstance $adapter)
	{
		$src = $adapter->getParent()->getPath('source');
		$site = $adapter->getParent()->getPath('extension_site');
		$admin = $adapter->getParent()->getPath('extension_administrator');

		$extension = JArrayHelper::getValue($adapter->getParent()->get('manifest')->media, 'destination');
		$extension = JArrayHelper::getValue($extension, 0);

		$media = JPATH_ROOT.'/media/'.$extension;

		$this->_cleanFiles($site, $src.'/site');

		$exclude = array();
		$exclude[] = $adapter->get('manifest_script');
		$exclude[] = JFile::getName($adapter->getParent()->getPath('manifest'));

		$this->_cleanFiles($admin, $src.'/admin', $exclude);

		$this->_cleanFiles($media, $src.'/media', $exclude);
	}

	private function _cleanFiles($path1, $path2, $exclude = array())
	{
		foreach (JFolder::files($path1, '.', true, true, $exclude) as $file) {
			if (JFile::exists($file)) {
				if (!JFile::exists(str_replace($path1, $path2, $file))) {
					Jfile::delete($file);
				}
			}
		}

		foreach (JFolder::folders($path1, '.', true, true, $exclude) as $folder) {
			if (JFolder::exists($folder)) {
				if (!JFolder::exists(str_replace($path1, $path2, $folder))) {
					JFolder::delete($folder);
				}
			}
		}
	}
}