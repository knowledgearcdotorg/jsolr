<?php
/**
 * @package     JSolr.Search
 * @subpackage  View
 * @copyright  Copyright (C) 2012-2016 KnowledgeArc Ltd. All rights reserved.
 * @license    This file is part of the JSolr component for Joomla!.
 */
defined('_JEXEC') or die('Restricted access');

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
                  <input id="jsolr-submit-advanced" class="button validate" type="submit" value="<?php echo JText::_("COM_JSOLR_ADVANCED_SEARCH_BUTTON_SUBMIT"); ?>" />
            </div>

            <input type="hidden" name="option" value="com_jsolr"/>
            <input type="hidden" name="task" value="advanced" />
            <input type="hidden" name="o" value="<?php echo JFactory::getApplication()->input->get("o", null, 'cmd'); ?>" />
            <input type="hidden" name="Itemid" value="<?php echo JFactory::getApplication()->input->get("Itemid", 0, 'int'); ?>" />
            <?php echo JHTML::_( 'form.token' ); ?>
        </fieldset>
    </form>
</div>
