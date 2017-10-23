<?php
/**
 * @package    JSolr.Module
 * @copyright  Copyright (C) 2012-2017 KnowledgeArc Ltd. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');


$document = JFactory::getDocument();

$document->addStyleSheet(JURI::base()."/media/mod_jsolr_filter/css/jsolrfilter.css");
?>

<div class="jsolr-facet-filter">
	<?php foreach($form->getFieldset('facets') as $field) : ?>
    <div>
        <?php if ($field->label) : ?>
            <h4><?php echo $form->getLabel($field->name); ?></h4>
        <?php endif; ?>
        <div><?php echo $form->getInput($field->name); ?></div>
    </div>
	<?php endforeach;?>
</div>
