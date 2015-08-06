<?php
/**
 * @package    JSolr
 * @copyright  Copyright (C) 2014-2015 KnowledgeARC Ltd. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;
?>

<div class="row-striped">

    <div class="row-fluid">

        <div class="span6">

            <strong class="row-title"><?php echo JText::_('MOD_JSOLRCONNECTIONMONITOR_STATUS');?></strong>

        </div>


        <div id="jsolrStatus" class="span6">
        <?php
        if (JArrayHelper::getValue($index, 'status')) :
            echo JText::_("MOD_JSOLRCONNECTIONMONITOR_CONNECTED");
        else :
            echo JText::_("MOD_JSOLRCONNECTIONMONITOR_NOT_CONNECTED");
        endif;
        ?>

        </div>

    </div>

    <?php foreach (array('host', 'port', 'path') as $setting) : ?>
    <div class="row-fluid">
        <div class="span6">
            <strong class="row-title"><?php echo JText::_('MOD_JSOLRCONNECTIONMONITOR_'.JString::strtoupper($setting));?></strong>
        </div>

        <div class="span6">
            <?php echo JArrayHelper::getValue($index, $setting); ?>
        </div>
    </div>
    <?php endforeach; ?>

    <div class="row-fluid">
        <div class="span6">
            <strong class="row-title"><?php echo JText::_('MOD_JSOLRCONNECTIONMONITOR_NUMDOCS'); ?></strong>
        </div>

        <div id="jsolrNumDocs" class="span6">
            <?php
            if ($statistics = JArrayHelper::getValue($index, 'statistics')) :
                echo $statistics->numDocs;
            else :
                echo JText::_('MOD_JSOLRCONNECTIONMONITOR_NOVALUE');
            endif;
            ?>
        </div>
    </div>

    <div class="row-fluid">
        <div class="span6">
            <strong class="row-title"><?php echo JText::_('MOD_JSOLRCONNECTIONMONITOR_LASTINDEXED'); ?></strong>
        </div>

        <div class="span6" id="jsolrLastModified">
            <?php
            if ($statistics = JArrayHelper::getValue($index, 'statistics')) :
                echo JHtml::_('date', $statistics->lastModified, JText::_('DATE_FORMAT_LC2'));
            else :
                echo JText::_('MOD_JSOLRCONNECTIONMONITOR_NOVALUE');
            endif;
            ?>
        </div>
    </div>

    <?php if ($libs = JArrayHelper::getValue($index, 'libraries')) : ?>
        <?php if (!JArrayHelper::getValue($libs, 'curl')) : ?>
        <div class="row-fluid">
            <div class="span6">
                <strong class="row-title"><?php echo JText::_('MOD_JSOLRCONNECTIONMONITOR_CURL_NOT_INSTALLED'); ?></strong>
            </div>
        </div>
        <?php endif; ?>

        <?php if (!JArrayHelper::getValue($libs, 'jsolr')) : ?>
        <div class="row-fluid">
            <div class="span6">
                <strong class="row-title"><?php echo JText::_('MOD_JSOLRCONNECTIONMONITOR_JSOLR_NOT_INSTALLED'); ?></strong>
            </div>
        </div>
        <?php endif; ?>
    <?php endif; ?>

    <?php if ($extractor = JArrayHelper::getValue($index, 'extractor')) : ?>
        <?php if (JArrayHelper::getValue($extractor, 'type')) : ?>
        <div class="row-fluid">
            <div class="span6">
                <strong class="row-title"><?php echo JText::_('MOD_JSOLRCONNECTIONMONITOR_JSOLR_EXTRACTOR'); ?></strong>
            </div>

            <div class="span6">
                <?php echo JArrayHelper::getValue($extractor, 'type'); ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if (JArrayHelper::getValue($extractor, 'type') == 'local') : ?>
        <div class="row-fluid">
            <div class="span6">
                <strong class="row-title"><?php echo JText::_('MOD_JSOLRCONNECTIONMONITOR_JSOLR_EXTRACTOR_LOCAL'); ?></strong>
            </div>

            <div class="span6">
                <?php echo JArrayHelper::getValue($extractor, 'path'); ?>
            </div>
        </div>
        <?php endif; ?>
    <?php endif; ?>

</div>
