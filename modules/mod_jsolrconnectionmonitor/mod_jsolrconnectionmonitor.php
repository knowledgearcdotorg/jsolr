<?php
/**
 * @package    JSolr
 * @copyright  Copyright (C) 2014-2016 KnowledgeArc Ltd. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

// Include dependancies.
JLoader::registerNamespace('JSolr', JPATH_PLATFORM);

require_once __DIR__ . '/helper.php';

$document = JFactory::getDocument();

$noValue = JText::_('MOD_JSOLRCONNECTIONMONITOR_NOVALUE');

if (version_compare(JVERSION, "3.0", "ge")) {

$js = <<<JS
(function ($) {
    $(document).ready(function() {
        poll = function() {
            var request = {
                'option' : 'com_ajax',
                'module' : '{$module->name}',
                'method' : 'getIndex',
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

    $document->addScriptDeclaration($js);
}

$index = ModJSolrConnectionMonitorHelper::getIndex($params);

require JModuleHelper::getLayoutPath('mod_'.$module->name, $params->get('layout', 'default'));
