<?php
/**
 * Edit a search dimension.
 *
 * @package    JSolr
 * @copyright  Copyright (C) 2012-2016 KnowledgeArc Ltd. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

JHtml::_('behavior.formvalidator');
JHtml::_('behavior.keepalive');
JHtml::_('formbehavior.chosen', 'select');

$app = JFactory::getApplication();
$input = $app->input;

JFactory::getDocument()->addScriptDeclaration('
    Joomla.submitbutton = function(task) {
        if (task == "dimension.cancel" || document.formvalidator.isValid(document.getElementById("dimension-form"))) {
            Joomla.submitform(task, document.getElementById("dimension-form"));
        }
    };
');
?>

<form action="<?php echo JRoute::_('index.php?option=com_jsolr&layout=edit&id='.(int)$this->item->id); ?>" method="post" name="adminForm" id="dimension-form" class="form-validate">

    <?php echo JLayoutHelper::render('joomla.edit.title_alias', $this); ?>

    <div class="form-horizontal">
        <div class="row-fluid form-horizontal-desktop">
            <div class="span6">
                <?php echo JLayoutHelper::render('joomla.edit.params', $this); ?>
            </div>
            <div class="span6">
                <?php echo JLayoutHelper::render('joomla.edit.global', $this); ?>
                <?php echo JLayoutHelper::render('joomla.edit.publishingdata', $this); ?>
            </div>
        </div>
    </div>
    <input type="hidden" name="task" value="" />
    <?php echo JHtml::_('form.token'); ?>
</form>