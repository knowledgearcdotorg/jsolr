<?php
/**
 * JSolrSearch configuration panel.
 *
 * @copyright  Copyright (C) 2012-2016 KnowledgeArc Ltd. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined( '_JEXEC' ) or die( 'Restricted access' );
?>
<div class="row-fluid">
<?php
foreach ($this->modules as $module) {
    // Get module parameters
    $params = new JRegistry;

    $params->loadString($module->params);

    echo JModuleHelper::renderModule($module, array('style' => 'well'));
}
?>
</div>
