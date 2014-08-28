<?php 
/**
 * JSolrSearch configuration panel.
 * 
 * @copyright	Copyright (C) 2012-2014 KnowledgeARC Ltd. All rights reserved.
 * @license     This file is part of the JSolrIndex component for Joomla!.

   The JSolrIndex component for Joomla! is free software: you can redistribute it 
   and/or modify it under the terms of the GNU General Public License as 
   published by the Free Software Foundation, either version 3 of the License, 
   or (at your option) any later version.

   The JSolrIndex component for Joomla! is distributed in the hope that it will be 
   useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   GNU General Public License for more details.

   You should have received a copy of the GNU General Public License
   along with the JSolrIndex component for Joomla!.  If not, see 
   <http://www.gnu.org/licenses/>.

 * Contributors
 * Please feel free to add your name and email (optional) here if you have 
 * contributed any source code changes.
 * Name							Email
 * Hayden Young					<hayden@knowledgearc.com> 
 * 
 */

defined( '_JEXEC' ) or die( 'Restricted access' );
?>
<div class="row-fluid">
<?php
foreach ($this->modules as $module)
{
    // Get module parameters
    $params = new JRegistry;
    $params->loadString($module->params);

    echo JModuleHelper::renderModule($module, array('style' => 'well'));
}
?>
</div>