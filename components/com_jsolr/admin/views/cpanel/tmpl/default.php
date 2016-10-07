<?php
/**
 * JSolr configuration panel.
 *
 * @copyright  Copyright (C) 2012-2016 KnowledgeArc Ltd. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined( '_JEXEC' ) or die( 'Restricted access' );

$noValue = JText::_('COM_JSOLR_CPANEL_NOVALUE');

$js = <<<JS
(function ($) {
    $(document).ready(function() {
        poll = function() {
            var request = {
                'option' : 'com_jsolr',
                'view' : 'cpanel',
                'format' : 'json'
            };

            setTimeout(function() {
                $.ajax({
                    type : 'POST',
                    data : request,
                    success: function (response) {
                        if (response.data) {
                            var index = response.data;

                            if ('statusText' in index) {
                                $('#jsolrStatus').html(index['statusText']);
                            }

                            if (index['status']) {
                                if ('statistics' in index) {
                                    var statistics = index['statistics'];

                                    if ('numDocs' in statistics) {
                                        $('#jsolrNumDocs').html(statistics['numDocs']);
                                    }

                                    if ('lastModifiedFormatted' in statistics) {
                                        $('#jsolrLastModified').html(statistics['lastModifiedFormatted']);
                                    }
                                }
                            } else {
                                $('#jsolrNumDocs').html('{$noValue}');
                                $('#jsolrLastModified').html('{$noValue}');
                            }
                        }
                    }, dataType: "json", complete: poll});
            }, 30000);

            return false;
        };

        poll();
    });
})(jQuery)
JS;

JFactory::getDocument()->addScriptDeclaration($js);
?>
<?php if (!empty($this->sidebar)) : ?>
<div id="j-sidebar-container" class="span2">
    <?php echo $this->sidebar; ?>
</div>
<div id="j-main-container" class="span10">
<?php else : ?>
<div id="j-main-container">
<?php endif;?>

    <div class="row-striped">

        <div class="row-fluid">

            <div class="span6">

                <strong class="row-title"><?php echo JText::_('COM_JSOLR_CPANEL_STATUS');?></strong>

            </div>


            <div id="jsolrStatus" class="span6">
            <?php
            if (($status = $this->item->get('status')) == "OK") :
                echo JText::_("COM_JSOLR_CPANEL_CONNECTED");
            else :
                echo JText::_("COM_JSOLR_CPANEL_".strtoupper($status));
            endif;
            ?>

            </div>

        </div>

        <div class="row-fluid">
            <div class="span6">
                <strong class="row-title"><?php echo JText::_('COM_JSOLR_CPANEL_URL');?></strong>
            </div>

            <div class="span6">
                <?php echo $this->item->get('settings.url'); ?>
            </div>
        </div>

        <?php if ((bool)$this->item->get('settings.connection2')) : ?>
            <?php if ($this->item->get('settings.url2')) : ?>
            <div class="row-fluid">
                <div class="span6">
                    <strong class="row-title"><?php echo JText::_('COM_JSOLR_CPANEL_URL2');?></strong>
                </div>

                <div class="span6">
                    <?php echo $this->item->get('settings.url2'); ?>
                </div>
            </div>
            <?php endif; ?>
        <?php endif; ?>

        <div class="row-fluid">
            <div class="span6">
                <strong class="row-title"><?php echo JText::_('COM_JSOLR_CPANEL_NUMDOCS'); ?></strong>
            </div>

            <div id="jsolrNumDocs" class="span6">
                <?php echo $this->item->get('statistics.index.numDocs'); ?>
            </div>
        </div>

        <div class="row-fluid">
            <div class="span6">
                <strong class="row-title"><?php echo JText::_('COM_JSOLR_CPANEL_LASTINDEXED'); ?></strong>
            </div>

            <div class="span6" id="jsolrLastModified">
                <?php
                if ($this->item->get('statistics.index.lastModified')) :
                    echo JHtml::_('date', $this->item->get('statistics.index.lastModified'), JText::_('DATE_FORMAT_LC2'));
                else :
                    echo JText::_('COM_JSOLR_CPANEL_NOVALUE');
                endif;
                ?>
            </div>
        </div>

        <?php if (!$this->item->get('libraries.curl')) : ?>
        <div class="row-fluid">
            <div class="span6">
                <strong class="row-title"><?php echo JText::_('COM_JSOLR_CPANEL_CURL_NOT_INSTALLED'); ?></strong>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($this->item->get('settings.indexAttachments')) : ?>
            <div class="row-fluid">
                <div class="span6">
                    <strong class="row-title"><?php echo JText::_('COM_JSOLR_CPANEL_JSOLR_EXTRACTOR'); ?></strong>
                </div>

                <div class="span6">
                    <?php echo $this->item->get('settings.extractor'); ?>
                </div>
            </div>

            <?php if ($this->item->get('settings.extractor') == 'tika_app') : ?>
            <div class="row-fluid">
                <div class="span6">
                    <strong class="row-title"><?php echo JText::_('COM_JSOLR_CPANEL_JSOLR_EXTRACTOR_TIKA_APP'); ?></strong>
                </div>

                <div class="span6">
                    <?php echo $this->item->get('settings.tikaApp'); ?>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($this->item->get('settings.extractor') == 'tika_server') : ?>
            <div class="row-fluid">
                <div class="span6">
                    <strong class="row-title"><?php echo JText::_('COM_JSOLR_CPANEL_JSOLR_EXTRACTOR_TIKA_SERVER'); ?></strong>
                </div>

                <div class="span6">
                    <?php echo $this->item->get('settings.tikaServer'); ?>
                </div>
            </div>
            <?php endif; ?>
        <?php endif; ?>

    </div>
</div>
