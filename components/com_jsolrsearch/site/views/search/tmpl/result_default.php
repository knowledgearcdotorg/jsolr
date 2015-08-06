<?php
/**
 * Provides a template for an individual search result.
 *
 * To override this generic search result, create a results_result.php file in
 * the template jsolrsearch/html/search directory.
 *
 * To override an individual search result by component (E.g. com_content,
 * com_jspace), create a <component>_result in jsolrsearch/html/plugins
 * directory.
 *
 * @package     JSolr.Search
 * @subpackage  View
 * @copyright   Copyright (C) 2012-2015 KnowledgeArc Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

?>
<article class="jsolrsearch-result">
    <header>
        <h4>
            <a href="<?php echo  $this->item->link; ?>"><?php echo \JSolr\Helper::highlight($this->item->key, 'title', $this->item->title); ?></a>
        </h4>
    </header>
    <p><?php echo \JSolr\Helper::highlight($this->item->key, \JSolr\Helper::localize('body_*')); ?></p>
    <footer>
        <dl>
            <?php if ($this->item->created) : ?>
            <dt><?php echo JText::_("COM_JSOLRSEARCH_RESULT_CREATED_LABEL"); ?></dt>
            <dd>
                <time datetime="<?php echo JFactory::getDate($this->item->created)->toISO8601(); ?>"><?php echo JFactory::getDate($this->item->created)->format(JText::_('DATE_FORMAT_LC2')); ?></time>
            </dd>
            <?php endif; ?>

            <?php if ($this->item->modified) : ?>
            <dt><?php echo JText::_("COM_JSOLRSEARCH_RESULT_MODIFIED_LABEL"); ?></dt>
            <dd>
                <time datetime="<?php echo JFactory::getDate($this->item->modified)->toISO8601(); ?>"><?php echo JFactory::getDate($this->item->modified)->format(JText::_('DATE_FORMAT_LC2')); ?></time>
            </dd>
            <?php endif; ?>

            <?php if (isset($this->item->link)) : ?>
            <dt><?php echo JText::_("COM_JSOLRSEARCH_RESULT_LINK_LABEL"); ?></dt>
            <dd>
                <a href="<?php echo $this->item->link; ?>"><?php echo $this->item->link; ?></a>
            </dd>
            <?php endif; ?>

            <?php if (isset($this->item->category)) : ?>
            <dt><?php echo JText::_("COM_JSOLRSEARCH_RESULT_CATEGORY_LABEL"); ?></dt>
            <dd>
                <a href="<?php echo $this->item->link; ?>"><?php echo $this->item->category; ?></a>
            </dd>
            <?php endif; ?>
        </dl>
    </footer>
</article>
