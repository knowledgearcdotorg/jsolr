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

JHtml::_('behavior.keepalive');

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
		pleaseWait : "'.JText::_("COM_JSOLRINDEX_CONFIGURATION_WAITING").'",
		failed : "'.JText::_("COM_JSOLRINDEX_CONFIGURATION_FAILED").'",
		cancelling : "'.JText::_("COM_JSOLRINDEX_CONFIGURATION_CANCELLING").'",
		cancelled : "'.JText::_("COM_JSOLRINDEX_CONFIGURATION_CANCELLED").'"
	}
});
');
?>
<div class="width-60 fltlft">
	<div class="width-100">
		<fieldset class="adminform">
			<legend>Search Server Connection</legend>
			<ul class="adminformlist">
				<li>
					<label 
						title="" 
						class="hasTip required" 
						for="jform_host" 
						id="jform_host-lbl"><?php echo JText::_('COM_JSOLRINDEX_CONNECTION_HOST_LABEL'); ?></label>
					<input 
						type="text" 
						readonly="readonly" 
						class="readonly" 
						size="22" 
						value="<?php echo $this->item->get('host'); ?>" 
						title="<?php echo $this->item->get('host'); ?>">
				</li>
				<li>
					<label 
						title="" 
						class="hasTip required" 
						for="jform_port" 
						id="jform_port-lbl"><?php echo JText::_('COM_JSOLRINDEX_CONNECTION_PORT_LABEL'); ?></label>
					<input 
						type="text" 
						readonly="readonly" 
						class="readonly" 
						size="22" 
						value="<?php echo $this->item->get('port'); ?>" 
						title="<?php echo $this->item->get('port'); ?>">
				</li>
				<li>
					<label 
						title="" 
						class="hasTip required" 
						for="jform_path" 
						id="jform_path-lbl"><?php echo JText::_('COM_JSOLRINDEX_CONNECTION_PATH_LABEL'); ?></label>
					<input 
						type="text" 
						readonly="readonly" 
						class="readonly" 
						size="22" 
						value="<?php echo $this->item->get('path'); ?>" 
						title="<?php echo $this->item->get('path'); ?>">
				</li>
				<li>
					<label 
						title="" 
						class="hasTip required" 
						for="jform_connection" 
						id="jform_connection-lbl"><?php echo JText::_('COM_JSOLRINDEX_CONFIGURATION_STATUS_LABEL'); ?></label>
					<input 
						type="text" 
						readonly="readonly" 
						class="readonly" 
						size="22" 
						value="<?php echo (is_null($this->item->get('index'))) ? JText::_('COM_JSOLRINDEX_CONFIGURATION_STATUS_NOTCONNECTED') : JText::_('COM_JSOLRINDEX_CONFIGURATION_STATUS_CONNECTED'); ?>" 
						title="<?php echo (is_null($this->item->get('index'))) ? JText::_('COM_JSOLRINDEX_CONFIGURATION_STATUS_NOTCONNECTED') : JText::_('COM_JSOLRINDEX_CONFIGURATION_STATUS_CONNECTED'); ?>">
					<?php if (is_null($this->item->get('index'))) : ?>`
					<?php echo JText::_('COM_JSOLRINDEX_CONFIGURATION_NOTCONNECTED_DESCRIPTION'); ?>
					<?php endif; ?>
				</li>
				<?php if (!is_null($this->item->get('index'))) : ?>
				<li>
					<label 
						title="" 
						class="hasTip required" 
						for="jform_numDocs" 
						id="jform_numDocs-lbl"><?php echo JText::_('COM_JSOLRINDEX_CONFIGURATION_NUMDOCS_LABEL'); ?></label>
					<input 
						type="text" 
						readonly="readonly" 
						class="readonly" 
						size="22" 
						value="<?php echo $this->item->get('index')->numDocs; ?>" 
						title="<?php echo $this->item->get('index')->numDocs; ?>">
				</li>
				<li>
					<label 
						title="" 
						class="hasTip required" 
						for="jform_lastModified" 
						id="jform_lastModified-lbl"><?php echo JText::_('COM_JSOLRINDEX_CONFIGURATION_LASTMODIFIED_LABEL'); ?></label>
					<input 
						type="text" 
						readonly="readonly" 
						class="readonly" 
						size="40" 
						value="<?php echo JSolrHelper::datetime($this->item->get('index')->lastModified); ?>" 
						title="<?php echo JSolrHelper::datetime($this->item->get('index')->lastModified); ?>">
				</li>
				<?php endif; ?>
			</ul>
		</fieldset>
	</div>
</div>
		
<div class="width-40 fltrt">
	<div class="width-100">
		<fieldset class="adminform">
			<legend>Index Management</legend>
			
			<div id="jsolrIndexManagementMessage">&nbsp;</div>
			
			<div id="jSolrIndexManagementButtons">
				<button id="jsolrIndexTest"><?php echo JText::_("COM_JSOLRINDEX_CONFIGURATION_TEST"); ?></button>
				<button id="jsolrStartIndex"><?php echo JText::_("COM_JSOLRINDEX_CONFIGURATION_INDEX"); ?></button>
				<button id="jsolrPurgeIndex"><?php echo JText::_("COM_JSOLRINDEX_CONFIGURATION_PURGE"); ?></button>
			</div>
		</fieldset>
	</div>
	
	<div class="width-100">		
		<fieldset class="adminform">
			<legend>Attachment Indexing</legend>
			<div id="jsolrIndexAttachmentIndexingMessage">&nbsp;</div>
			
			<div id="jSolrAttachmentIndexingButtons">
				<button id="jsolrIndexTestTika"><?php echo JText::_("COM_JSOLRINDEX_CONFIGURATION_TEST"); ?></button>				
			</div>
		</fieldset>
	</div>
</div>