<?php
/**
 * @copyright	Copyright (C) 2012-2013 KnowledgeARC Ltd. All rights reserved.
 * @license     This file is part of the JSolrSearch Component for Joomla!.

   The JSolrSearch Component for Joomla! is free software: you can redistribute it 
   and/or modify it under the terms of the GNU General Public License as 
   published by the Free Software Foundation, either version 3 of the License, 
   or (at your option) any later version.

   The JSolrSearch Component for Joomla! is distributed in the hope that it will be 
   useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   GNU General Public License for more details.

   You should have received a copy of the GNU General Public License
   along with the JSolrSearch Component for Joomla!.  If not, see 
   <http://www.gnu.org/licenses/>.

 * Contributors
 * Please feel free to add your name and email (optional) here if you have 
 * contributed any source code changes.
 * Name							Email
 * Hayden Young					<hayden@knowledgearc.com>
 */

defined('_JEXEC') or die('Restricted access');
//test
$params = $this->state->get('params');
?>
<div class="edit item-page<?php echo $params->get('pageclass_sfx'); ?>">
	<?php if ($params->get('show_page_heading', 1)) : ?>
	<h1><?php echo $this->escape($params->get('page_heading')); ?></h1>
	<?php endif; ?>

	<form action="<?php echo JRoute::_(JURI::base().'index.php'); ?>" method="get" id="josForm" name="josForm" class="form-validate jsolr-advanced-search">	
		<fieldset>
			<?php foreach ($this->get('Form')->getFieldset('query') as $field) : ?>
			<div class="formelm">
				<?php echo $this->get('Form')->getLabel($field->fieldname); ?>
				<?php echo $this->get('Form')->getInput($field->fieldname); ?>
			</div>
			<?php endforeach;?>
		</fieldset>
                	
		<fieldset>
			<?php foreach ($this->get('Form')->getGroup('as') as $field) : ?>
			<div class="formelm">
				<?php echo $this->get('Form')->getLabel($field->fieldname, 'as'); ?>
				<?php echo $this->get('Form')->getInput($field->fieldname, 'as'); ?>
			</div>
			<?php endforeach;?>
			
			<div class="field">
			  	<input id="jsolr-submit-advanced" class="button validate" type="submit" value="<?php echo JText::_("COM_JSOLRSEARCH_ADVANCED_SEARCH_BUTTON_SUBMIT"); ?>" />
			</div>
			
			<input type="hidden" name="option" value="com_jsolrsearch"/>
			<input type="hidden" name="task" value="advanced" />
			<input type="hidden" name="o" value="<?php echo JFactory::getApplication()->input->get("o", null, 'cmd'); ?>" />
			<input type="hidden" name="Itemid" value="<?php echo JFactory::getApplication()->input->get("Itemid", 0, 'int'); ?>" />
			<?php echo JHTML::_( 'form.token' ); ?>
		</fieldset>
	</form>
</div>