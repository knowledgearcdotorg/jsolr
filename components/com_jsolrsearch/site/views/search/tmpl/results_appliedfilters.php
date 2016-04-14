<?php
/**
 * Provides a list of facet filters applied to the current search results.
 *
 * @package     JSolr.Search
 * @subpackage  View
 * @copyright   Copyright (C) 2012-2016 KnowledgeArc Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

$form = $this->get('Form');
?>

<?php if (!is_null($form)): ?>
<ul>

    <?php
    foreach ($this->get('AppliedFacetFilters') as $field) :
        $uri = clone JURI::getInstance();
        $uri->delVar($field->name);
    ?>

    <li>
        <span class="jsolr-label"><?php echo $field->label; ?></span>
        <span class="jsolr-value"><?php echo str_replace('|', ' | ', $field->value); ?></span>

        <?php echo JHTML::link((string)htmlentities($uri), '(clear)'); ?>
    </li>

    <?php
    endforeach;
    ?>

    <?php
    foreach ($this->get('AppliedAdvancedFilters') as $field) :
        $uri = clone JURI::getInstance();
        $uri->delVar($field->name);
    ?>

    <li>
        <span class="jsolr-label"><?php echo $field->label; ?></span>

        <?php echo JHTML::link((string)htmlentities($uri), '(clear)'); ?>
    </li>

    <?php
    endforeach;
    ?>

</ul>

<?php endif ?>

<div class="clr"></div>
