<?php
/**
 * Installation scripts.
 * 
 * @author		$LastChangedBy$
 * @package	Wijiti
 * @subpackage	JSolr
 * @copyright	Copyright (C) 2010 Wijiti Pty Ltd. All rights reserved.
 * @license     This file is part of the JSolr component for Joomla!.

   The JSolr component for Joomla! is free software: you can redistribute it 
   and/or modify it under the terms of the GNU General Public License as 
   published by the Free Software Foundation, either version 3 of the License, 
   or (at your option) any later version.

   The JSolr component for Joomla! is distributed in the hope that it will be 
   useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   GNU General Public License for more details.

   You should have received a copy of the GNU General Public License
   along with the JSolr component for Joomla!.  If not, see 
   <http://www.gnu.org/licenses/>.

 * Contributors
 * Please feel free to add your name and email (optional) here if you have 
 * contributed any source code changes.
 * Name							Email
 * Hayden Young					<haydenyoung@wijiti.com> 
 * 
 */

defined('_JEXEC') or die('Restricted access');

function com_install()
{
	$installer = new JSolrInstaller();
	$installer->install();
}

class JSolrInstaller
{
	public function __construct()
	{
	
	}
	
	public function install()
	{
		$src = "administrator".DS."components".DS."com_jsolr".DS."crawler.php";
		$dest = "crawler.php";
		
		if (JFile::move($src, $dest, JPATH_ROOT)) {
			echo "<p>Crawler installed in ".JPATH_ROOT." successfully. Use the crawler file to run an indexing cron job across your Joomla! site.</p>";
		} else {
			echo "<p>Crawler failed to install in ".JPATH_ROOT.". You will need to copy it manually from ".JPATH_COMPONENT_ADMINISTRATOR.".</p>";
		}
	}
}