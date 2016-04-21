<?php
/**
 * Provides the search form within the search results display so that a user
 * can modify the current search without having to start over.
 *
 * Copy this file to override the layout and style of the search results form.
 *
 * @package     JSolr.Search
 * @subpackage  View
 * @copyright   Copyright (C) 2012-2016 KnowledgeArc Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

JHTML::_('behavior.formvalidation');
?>
<form
    action="<?php echo JRoute::_("index.php"); ?>"
    method="get"
    name="adminForm"
    class="form-validate jsolr-search-result-form"
    id="jsolr-search-result-form">

    <?php if (JFactory::getApplication()->input->get('o', null)) : ?>
    <input type="hidden" name="o" value="<?php echo JFactory::getApplication()->input->get('o'); ?>"/>
    <?php endif; ?>

    <fieldset class="query">

        <!-- Output search fields (in almost all cases will be a single query field). -->
        <?php
        foreach ($this->get('Form')->getFieldset('query') as $field):
            echo $this->form->getInput($field->fieldname);
        endforeach;
        ?>

        <!-- Output the hidden form fields for the various selected facet filters. -->
        <?php
        foreach ($this->get('Form')->getFieldset('facets') as $field):
            if (trim($field->value)) :
                echo $this->form->getInput($field->fieldname);
            endif;
        endforeach;
        ?>

        <button type="submit" class="button"><?php echo JText::_("COM_JSOLRSEARCH_BUTTON_SUBMIT"); ?></button>
    </fieldset>

    <a href="<?php echo JRoute::_(\JSolr\Search\Factory::getAdvancedSearchRoute()); ?>">Advanced search</a>

    <div class="clr"></div>

    <?php $plugins = $this->get('Plugins'); ?>

    <nav>
        <ul>

            <?php for ($i = 0; $i < count($plugins); ++$i): ?>

            <li>
                <?php
                $plugin = JArrayHelper::getValue($plugins, $i);
                $isSelected = (JArrayHelper::getValue($plugin, 'name') == JFactory::getApplication()->input->get('o')) ? true : false;

                echo JHTML::_(
                    'link',
                    $plugins[$i]['uri'],
                    JText::_($plugins[$i]['label']),
                    array(
                        'data-category'=>JArrayHelper::getValue($plugin, 'name'),
                        'class'=> $isSelected ? 'active' : ''));
                ?>
            </li>

            <?php endfor ?>

        </ul>
    </nav>

    <div class="clr"></div>

    <div class="jsolr-searchtools">
        <?php foreach ($this->get('Form')->getFieldset('tools') as $field) : ?>
            <?php echo $this->form->getInput($field->name); ?>
        <?php endforeach;?>
    </div>

    <input type="hidden" name="option" value="com_jsolrsearch"/>
    <input type="hidden" name="task" value="search"/>
    <?php echo JHTML::_('form.token'); ?>
</form>

<div id="custom-dates">
    <form
        id="custom-dates-form"
        action="<?php echo JRoute::_(\JSolr\Search\Factory::getSearchRoute()); ?>"
        method="get">

        <?php echo JHtml::_('calendar', '', "qdr_min", "qdr_min", "%Y-%m-%d"); ?>
        <?php echo JHtml::_('calendar', '', "qdr_max", "qdr_max", "%Y-%m-%d"); ?>

        <button id="custom-dates-submit"><?php echo JText::_('JSUBMIT'); ?></button>
        <a id="custom-dates-cancel" href="#"><?php echo JText::_('JCANCEL'); ?></a>
        
    </form>
</div>
