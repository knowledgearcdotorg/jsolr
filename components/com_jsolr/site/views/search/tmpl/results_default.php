<?php
/**
 * Provides a default results template.
 *
 * Includes total number of records, spelling suggestions and the list of
 * search results.
 *
 * Override this template to customize the results display (does not affect
 * the display of an individual result (use results_result or
 * results_[dimension]).
 *
 * @package     JSolr.Search
 * @subpackage  View
 * @copyright   Copyright (C) 2012-2017 KnowledgeArc Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
?>
<div id="jsolr-total">
    <?php
    if ($this->get("Pagination")->get('pages.current') > 1) :
        echo JText::sprintf('COM_JSOLR_TOTAL_RESULTS_CURRENTPAGE', $this->get("Pagination")->get('pages.current'), $this->get('Total'));
    else :
        echo JText::plural('COM_JSOLR_TOTAL_N_RESULTS', $this->get('Total'));
    endif;
    ?>

</div>

<?php
if ($didYouMean = $this->get('DidYouMean')) :
?>
<div><b><?php echo JText::_("COM_JSOLR_SEARCH_DIDYOUMEAN"); ?></b> <a href="<?php echo $didYouMean->url; ?>"><?php echo $didYouMean->value; ?></a></div>
<?php
endif;
?>

<?php if (!count($this->items)) : ?>
<span><?php JText::_("COM_JSOLR_NO_RESULTS"); ?></span>
<?php endif; ?>

<ol>
    <?php foreach ($this->items as $item) : ?>

    <li>
        <?php
        $hl = $this->get('Highlighting')->getResult($item->id);
        ?>
        <article class="jsolr-result">
            <header>
                <h4>
                    <a href="<?php echo $item->link; ?>"><?php echo \JSolr\Helper::highlight($hl, \JSolr\Helper::localize('title_txt_*'), $item->{\JSolr\Helper::localize('title_txt_*')}); ?></a>
                </h4>
            </header>
            <p><?php echo \JSolr\Helper::highlight($hl, \JSolr\Helper::localize('content_txt_*'), $item->{\JSolr\Helper::localize('description_txt_*')}); ?></p>
            <footer>
                <dl>
                    <?php if ($item->created_tdt) : ?>
                    <dt><?php echo JText::_("COM_JSOLR_RESULT_CREATED_LABEL"); ?></dt>
                    <dd>
                        <time datetime="<?php echo JFactory::getDate($item->created_tdt)->toISO8601(); ?>"><?php echo JFactory::getDate($item->created_tdt)->format(JText::_('DATE_FORMAT_LC2')); ?></time>
                    </dd>
                    <?php endif; ?>

                    <?php if ($item->modified_tdt) : ?>
                    <dt><?php echo JText::_("COM_JSOLR_RESULT_MODIFIED_LABEL"); ?></dt>
                    <dd>
                        <time datetime="<?php echo JFactory::getDate($item->modified_tdt)->toISO8601(); ?>"><?php echo JFactory::getDate($item->modified_tdt)->format(JText::_('DATE_FORMAT_LC2')); ?></time>
                    </dd>
                    <?php endif; ?>

                    <?php if (isset($item->category_s)) : ?>
                    <dt><?php echo JText::_("COM_JSOLR_RESULT_CATEGORY_LABEL"); ?></dt>
                    <dd>
                        <a href="<?php echo JRoute::_($item->link); ?>"><?php echo $item->category_s; ?></a>
                    </dd>
                    <?php endif; ?>
                </dl>
            </footer>
        </article>
    </li>

    <?php endforeach; ?>

</ol>
