<?php
/**
 * Default display for browse view.
 *
 * @package     JSolr.Search
 * @subpackage  View
 * @copyright   Copyright (C) 2012-2017 KnowledgeArc Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

use \JSolr\Helper;

JFactory::getDocument()->addStyleSheet(JURI::base()."media/".$this->getModel()->get('option')."/css/jsolr.css");
?>
<div class="item-page<?php echo $this->params->get('pageclass_sfx'); ?>">
    <?php if ($this->params->get('show_page_heading')) : ?>
    <div class="page-header">
        <h1> <?php echo $this->escape($this->params->get('page_heading')); ?> </h1>
    </div>
    <?php endif; ?>

    <ul>
    <?php foreach ($this->items->getFacets() as $key=>$facet) : ?>
        <?php foreach($facet as $value=>$count) : ?>
            <?php
            $vars = array(JFactory::getApplication()->input->get('name')=>Helper::getOriginalFacet($value));

            if ($this->params->get('show_count')) {
                $facet = JText::sprintf('%s [%s]', Helper::getOriginalFacet($value), $count);
            } else {
                $facet = JText::sprintf('%s', Helper::getOriginalFacet($value));
            }
            ?>

            <li><?php echo JHTML::_('link', JRoute::_(\JSolr\Search\Factory::getSearchRoute($vars)), $facet); ?></li>
        <?php endforeach; ?>
    <?php endforeach; ?>
    </ul>
</div>
