<?php
/**
 * Provides a default results template.
 *
 * Includes total number of records, spelling suggestions and the list of
 * search results.
 *
 *  Override this template to customize the results display (does not affect
 *  the display of an individual result (use results_result or
 *  results_<plugin>).
 *
 * @package     JSolr.Search
 * @subpackage  View
 * @copyright   Copyright (C) 2012-2016 KnowledgeArc Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

$featuredItems = $this->get('FeaturedItems');
?>

<div id="jsolr-total">

    <?php
    if ($this->get("Pagination")->get('pages.current') > 1) :
        echo JText::sprintf('COM_JSOLRSEARCH_TOTAL_RESULTS_CURRENTPAGE', $this->get("Pagination")->get('pages.current'), $this->items->get('numFound'), $this->items->get('qTimeFormatted'));
    else :
        echo JText::sprintf('COM_JSOLRSEARCH_TOTAL_RESULTS', $this->items->get('numFound'), $this->items->get('qTimeFormatted'));
    endif;
    ?>

</div>

<?php
if ($this->items->getSuggestions()) :
    foreach ($this->get("SuggestionQueryURIs") as $item) :
?>
<div>Did you mean <a href="<?php echo JArrayHelper::getValue($item, 'uri'); ?>"><?php echo JArrayHelper::getValue($item, 'title'); ?></a></div>
<?php
    endforeach;
endif;
?>

<?php if (!count($this->items)) : ?>

<span><?php JText::_("COM_JSOLRSEARCH_NO_RESULTS"); ?></span>

<?php endif; ?>

<?php if ($this->get("Pagination")->get('pages.current') == 1 && $featuredItems->get("numFound")) : ?>
    <?php echo $this->loadResultTemplate($featuredItems->getIterator()->current(), $featuredItems->getHighlighting()->{$featuredItems->getIterator()->current()->key}); ?>
<?php endif; ?>

<ol>

    <?php foreach ($this->items as $item) : ?>

    <li>
        <?php echo $this->loadResultTemplate($item, $this->items->getHighlighting()->{$item->key}); ?>
    </li>

    <?php endforeach; ?>

</ol>
