<?php
/**
 * @package    JSolr.Module
 * @copyright  Copyright (C) 2012-2017 KnowledgeArc Ltd. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JLoader::registerNamespace('JSolr', JPATH_PLATFORM);

require_once dirname(__FILE__).'/helper.php';

$moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx'));

// Don't show the filter module contents unless the user has specified
// something to search for.
if (ModJSolrFilterHelper::showFilter()) {
	$form = ModJSolrFilterHelper::getForm();
	require JModuleHelper::getLayoutPath('mod_jsolr_filter', $params->get('layout', 'default'));
}
