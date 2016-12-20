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
    id="jsolrSearchResultForm">

    <div class="input-append">
        <?php
        // Output search fields (in almost all cases will be a single query field).
        foreach ($this->get('Form')->getFieldset('query') as $field):
            echo $this->form->getInput($field->fieldname);
        endforeach;
        ?>

        <button type="submit" class="btn"><i class="icon-search"></i></button>
    </div>

    <?php if ((int)$this->state->get('params')->get('advanced_link') == 1) : ?>
    <a href="<?php echo JRoute::_(\JSolr\Search\Factory::getAdvancedSearchRoute()); ?>">Advanced search</a>
    <?php endif; ?>

    <ul id="jsolrDimensions" class="nav nav-tabs">
        <?php
        if (count($this->get("Dimensions")) > 1) :
            foreach ($this->get('Dimensions') as $dimension) :
                echo "<li".($dimension->active ? " class=\"active\"" : "").">".JHTML::link($dimension->url, $dimension->name)."</li>";
            endforeach;
        endif;
        ?>
    </ul>

    <ul class="nav nav-pills">
        <?php foreach ($this->get('Form')->getFieldset('tools') as $field) : ?>
        <li class="dropdown">
            <?php echo $this->form->getInput($field->name); ?>
        </li>
        <?php endforeach;?>
    </ul>

    <div id="jsolrAppliedFilters">
        <?php if (!is_null($this->get('Form'))): ?>
            <?php
            foreach ($this->get('AppliedFacetFilters') as $field) :
                $uri = clone JURI::getInstance();
                $uri->delVar($field->name);
            ?>
            <span class="label">
                <?php echo $field->value; ?>&nbsp;<?php echo JHTML::link((string)htmlentities($uri), '&times;'); ?>
            </span>
            <input type="hidden" name="<?php echo $field->name; ?>" value="<?php echo $field->value; ?>"/>
            <?php
            endforeach;
            ?>
        <?php endif ?>
    </div>

    <input type="hidden" name="option" value="com_jsolr"/>
    <input type="hidden" name="task" value="search"/>
</form>
