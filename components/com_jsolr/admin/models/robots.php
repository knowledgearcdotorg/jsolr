<?php 
/**
 * A model that provides configuration options for JSolr.
 * 
 * @author		$LastChangedBy: spauldingsmails $
 * @package		Wijiti
 * @subpackage	JSolr
 * @copyright	Copyright (C) 2011 Wijiti Pty Ltd. All rights reserved.
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

jimport('joomla.filesystem.file');

class JSolrModelRobots extends JModel
{
	public function __construct()
	{
		parent::__construct();	
	}
	
	/**
	 * Gets the ignore.txt file path.
	 * 
	 * @return The ignore.txt file path.
	 */
	public function getFilePath()
	{
		return JPATH_ROOT.DS."administrator".DS."components".DS."com_jsolr".DS."ignore.txt";
	}
	
	public function getContents()
	{
		return JFile::read($this->getFilePath());
	}
	
	public function save($array)
	{
		@chmod($this->getFilePath(), 0777);
		$success = JFile::write($this->getFilePath(), JArrayHelper::getValue($array, "robots"));
		@chmod($this->getFilePath(), 0440);
		
		return $success;
	}
}