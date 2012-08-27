<?php 
/**
 * A form view for adding/editing JSolrIndex configuration.
 * 
 * @package		JSolr
 * @subpackage	Index
 * @copyright	Copyright (C) 2012 Wijiti Pty Ltd. All rights reserved.
 * @license     This file is part of the JSolrIndex component for Joomla!.

   The JSolrIndex component for Joomla! is free software: you can redistribute it 
   and/or modify it under the terms of the GNU General Public License as 
   published by the Free Software Foundation, either version 3 of the License, 
   or (at your option) any later version.

   The JSolrIndex component for Joomla! is distributed in the hope that it will be 
   useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   GNU General Public License for more details.

   You should have received a copy of the GNU General Public License
   along with the JSolrIndex component for Joomla!.  If not, see 
   <http://www.gnu.org/licenses/>.

 * Contributors
 * Please feel free to add your name and email (optional) here if you have 
 * contributed any source code changes.
 * Name							Email
 * Hayden Young					<haydenyoung@wijiti.com> 
 * 
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

$application = JFactory::getApplication("administrator");

$document = JFactory::getDocument();

$document->addScriptDeclaration(
'
var jsolrindex = new Object({
	options : {
		jsolrIndexTest : {
			url : "'.JURI::root().'administrator/index.php?option=com_jsolrindex&task=test&format=raw",
		},
		jsolrStartIndex : {
			url : "'.JURI::root().'administrator/index.php?option=com_jsolrindex&task=index&format=raw",
		},
		jsolrPurgeIndex : {
			url : "'.JURI::root().'administrator/index.php?option=com_jsolrindex&task=purge&format=raw",
		},
		jsolrIndexTestTika : {
			url : "'.JURI::root().'administrator/index.php?option=com_jsolrindex&task=testTika&format=raw"
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
			<legend>Index Management</legend>
			<div id="jsolrIndexManagementMessage">&nbsp;</div>
			
			<div id="jSolrIndexManagementButtons">
				<button id="jsolrIndexTest"><?php echo JText::_("Test connection"); ?></button>
				<button id="jsolrStartIndex"><?php echo JText::_("Start indexing"); ?></button>
				<button id="jsolrPurgeIndex"><?php echo JText::_("Purge index"); ?></button>
			</div>
		</fieldset>
		
		<fieldset>
			<legend>Attachment Indexing</legend>
			<div id="jsolrIndexAttachmentIndexingMessage">&nbsp;</div>
			
			<div id="jSolrAttachmentIndexingButtons">
				<button id="jsolrIndexTestTika"><?php echo JText::_("Test Connection"); ?></button>				
			</div>
		</fieldset>

		<div class="clr"></div>
	</div>
	<noscript>Warning! JavaScript must be enabled for proper operation of the Administrator backend.</noscript>
</div>