<?php
/**
 * @copyright  Copyright (C) 2012-2015 KnowledgeArc Ltd. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
?>
<article class="jsolrsearch-result">
    <header>
        <h4>
            <a href="<?php echo JRoute::_($this->item->link); ?>"><?php echo JSolrHelper::highlight($this->hl, 'title', $this->item->title); ?></a>
        </h4>
    </header>

    <footer>
        <dl>
            <dt><?php echo JText::_("COM_JSOLRSEARCH_RESULT_AUTHORS_LABEL"); ?></dt>
            <dd>
            <?php echo (is_array($this->item->author)) ? implode('</dd><dd>', $this->item->author) : $this->item->author; ?>
            </dd>

            <?php if ($this->item->{'dc.date.available_dt'}) : ?>
            <dt><?php echo JText::_("COM_JSOLRSEARCH_RESULT_ARCHIVED_LABEL"); ?></dt>
            <dd>
                <time datetime="<?php echo $this->item->{'dc.date.available_dt'}; ?>"><?php echo $this->item->{'dc.date.available_dt'}; ?></time>
            </dd>
            <?php endif; ?>

            <?php if (isset($this->item->link)) : ?>
            <dt><?php echo JText::_("COM_JSOLRSEARCH_RESULT_LINK_LABEL"); ?></dt>
            <dd>
                <a href="<?php echo JRoute::_($this->item->link); ?>"><?php echo JRoute::_($this->item->link); ?></a>
            </dd>
            <?php endif; ?>
        </dl>
    </footer>
</article>
