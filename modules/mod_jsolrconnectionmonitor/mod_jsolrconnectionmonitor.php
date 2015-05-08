<?php
/**
 * @package		JSolr
 * @copyright	Copyright (C) 2014 KnowledgeARC Ltd. All rights reserved.
 * @license     This file is part of the JSolr Connection Monitor module for Joomla!.

   The JSolr Connection Monitor module for Joomla! is free software: you can
   redistribute it and/or modify it under the terms of the GNU General Public
   License as published by the Free Software Foundation, either version 3 of
   the License, or (at your option) any later version.

   The JSolr Connection Monitor module for Joomla! is distributed in the hope
   that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
   warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   GNU General Public License for more details.

   You should have received a copy of the GNU General Public License
   along with the JSolr filter module for Joomla!.  If not, see
   <http://www.gnu.org/licenses/>.

 * Contributors
 * Please feel free to add your name and email (optional) here if you have
 * contributed any source code changes.
 * Name							Email
 * Hayden Young					<hayden@knowledgearc.com>
 *
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
			}, 120000);

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