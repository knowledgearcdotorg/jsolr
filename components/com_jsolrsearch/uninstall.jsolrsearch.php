<?php
/**
 * @version		$LastChangedBy$
 * @package		Wijiti
 * @subpackage	JSolr
 * @copyright	Copyright (C) 2010 Wijiti Pty Ltd. All rights reserved.
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
 * Hayden Young					<haydenyoung@wijiti.com>
 */

defined('_JEXEC') or die('Restricted access');

function com_uninstall()
{
	$uninstaller = new JSolrSearchUninstaller();
	$uninstaller->uninstall();
}

class JSolrSearchUninstaller
{
	public function __construct()
	{
	
	}
	
	public function uninstall()
	{
		$file = JPATH_ROOT.DS."crawler.php";
		
		if (JFile::delete($file)) {
			echo "<p>Crawler removed from ".JPATH_ROOT." successfully. Remove the crawler from your cron job to complete the uninstallation process.</p>";
		} else {
			echo "<p>Crawler failed to delete from ".JPATH_ROOT.". You will need to remove it manually from ".JPATH_ROOT.".</p>";
		}
	}
}