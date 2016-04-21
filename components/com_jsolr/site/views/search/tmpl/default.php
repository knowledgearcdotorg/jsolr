<?php
/**
 * Default search page.
 *
 * Override to edit the JSolrSearch home page.
 *
 * @package     JSolr.Search
 * @subpackage  View
 * @copyright   Copyright (C) 2012-2016 KnowledgeArc Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

JHTML::_('behavior.formvalidation');

$form = $this->get('Form');

defined('_JEXEC') or die('Restricted access');

$document = JFactory::getDocument();

$document->addStyleSheet(JURI::base().'/media/com_jsolrsearch/css/jsolrsearch.css');
?>
<section id="jsolrSearch">
    <form action="<?php echo JRoute::_("index.php"); ?>" method="get" name="adminForm" class="form-validate jsolr-search-result-form" id="jsolr-search-result-form">
        <fieldset class="word">
            <?php foreach ($this->get('Form')->getFieldset('query') as $field): ?>
            <span><?php echo $form->getInput($field->fieldname); ?></span>
            <?php endforeach;?>

            <input type="hidden" name="option" value="com_jsolrsearch"/>
            <input type="hidden" name="task" value="search"/>

            <button type="submit" class="button"><?php echo JText::_("COM_JSOLRSEARCH_BUTTON_SUBMIT"); ?></button>
        </fieldset>

        <div class="jsolr-clear"></div>
    </form>
</section>
