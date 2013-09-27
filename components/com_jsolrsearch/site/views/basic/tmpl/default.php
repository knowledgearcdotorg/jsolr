<?php
/**
 * @package		JSolr
 * @subpackage	Search
 * @copyright	Copyright (C) 2012 Wijiti Pty Ltd. All rights reserved.
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
 * Hayden Young					<haydenyoung@wijiti.com>
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

JHTML::_('behavior.formvalidation');
$form = $this->get('Form');
defined( '_JEXEC' ) or die( 'Restricted access' );

$document = JFactory::getDocument();
$document->addScript(JURI::base().'/media/com_jsolrsearch/js/jquery/jquery.js');
$document->addScript(JURI::base().'/media/com_jsolrsearch/js/jsolrsearch.js');

$document->addStyleSheet(JURI::base().'/media/com_jsolrsearch/css/jsolrsearch.css');

$document->addScriptDeclaration('
jQuery(document).ready(function() {
	var jsolrsearch_autocomplete_url = "'.JRoute::_('index.php?option=jsolrsearch&view=basic').'";
	var jsolrsearch_search_url = "'.JRoute::_('index.php?option=jsolrsearch&view=basic').'";
});
');
?>
<div class="jsolr-content jsolr-main"> 
	<form action="<?php echo JRoute::_("index.php"); ?>" method="get" name="adminForm" class="form-validate jsolr-search-result-form" id="jsolr-search-result-form">
		<input type="hidden" name="option" value="com_jsolrsearch"/>
		<input type="hidden" name="task" value="search"/>
	  <fieldset class="word">
	    <?php foreach($form->getFieldsets() as $fieldset ) : ?>
	      <?php if ($fieldset->name == 'search'): ?>
	        <?php foreach ($this->get('Form')->getFieldset($fieldset->name) as $field): ?>
	          <span><?php echo $form->getInput($field->fieldname); ?></span>
	        <?php endforeach;?>
	      <?php endif ?>
	    <?php endforeach;?>
	        <input type="submit" value="<?php echo JText::_("COM_JSOLRSEARCH_BUTTON_SUBMIT"); ?>" class="btn btn-primary" />
	  </fieldset>
	
	  <div class="jsolr-clear"></div>
	</form>
</div>