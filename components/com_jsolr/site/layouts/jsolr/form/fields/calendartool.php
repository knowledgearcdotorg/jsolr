<?php
/**
 * @copyright   Copyright (C) 2013-2016 KnowledgeArc Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
?>
<div
    id="customDates"
    class="modal fade"
    tabindex="-1"
    role="dialog"
    aria-labelledby="customDates">
    <?php echo JHtml::_('calendar', '', "qdr_min", "qdr_min", "%Y-%m-%d"); ?>
    <?php echo JHtml::_('calendar', '', "qdr_max", "qdr_max", "%Y-%m-%d"); ?>

    <button id="custom-dates-submit"><?php echo JText::_('JSUBMIT'); ?></button>
    <a id="custom-dates-cancel" href="#"><?php echo JText::_('JCANCEL'); ?></a>
</div>
