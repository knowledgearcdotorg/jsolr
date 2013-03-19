<?php 
/**
 * A form view for adding/editing JSolrSearch configuration.
 * 
 * @package		JSolr
 * @subpackage	Search
 * @copyright	Copyright (C) 2012 Wijiti Pty Ltd. All rights reserved.
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
 * 
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

$testUrl = new JURI(JURI::root().'administrator/index.php');
$testUrl->setVar('option', 'com_jsolrsearch');
$testUrl->setVar('task', 'test');
$testUrl->setVar('format', 'raw');

$application = JFactory::getApplication("administrator");

$document = JFactory::getDocument();

$document->addScriptDeclaration(
'
var jsolrsearch = new Object({
	options : {
		jsolrSearchTest : {
			url : "'.(string)$testUrl.'"
		}
	},	
	language : {
		pleaseWait : "'.JText::_("Please wait...").'",
		failed : "'.JText::_("Connection failed.").'",
		cancelling : "'.JText::_("Cancelling...").'",
		cancelled : "'.JText::_("Cancelled.").'"
	}
});
');
?>

<div id="element-box">
	<div class="m">
		<fieldset>
			<legend>Search Management</legend>

			<div id="jsolrSearchManagementMessage">&nbsp;</div>
			
			<div id="jSolrSearchManagementButtons">
				<button id="jsolrSearchTest"><?php echo JText::_("Test connection"); ?></button>
			</div>
		</fieldset>

		<div class="clr"></div>
	</div>
	<noscript>Warning! JavaScript must be enabled for proper operation of the Administrator backend.</noscript>
</div>