<?php
/**
 * Default display for browse view.
 * 
 * @package		JSolr
 * @subpackage	Search
 * @copyright	Copyright (C) 2012 Wijiti Pty Ltd. All rights reserved.
 * @license     This file is part of the JSolrSearch Component for Joomla!.

   The JSolrSearch Component for Joomla! is free software: you can redistribute it 
   and/or modify it under the terms of the GNU General Public License as 
   published by the Free Software Foundation, either version 3 of the License, 
   or (at your option) any later version.

   The JSolrSearch Component for Joomla! is distributed in the hope that it will be 
   useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   GNU General Public License for more details.

   You should have received a copy of the GNU General Public License
   along with the JSolrSearch Component for Joomla!.  If not, see 
   <http://www.gnu.org/licenses/>.

 * Contributors
 * Please feel free to add your name and email (optional) here if you have 
 * contributed any source code changes.
 * Name							Email
 * Hayden Young					<haydenyoung@wijiti.com>
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

$operators = $this->state->get('facet.operators');
?>
<ul>
<?php foreach (get_object_vars($this->items) as $keyi=>$valuei) : ?>
	<?php $field = JArrayHelper::getValue($operators, $keyi); ?>
	<?php foreach ($valuei as $keyj=>$valuej) : ?>
		<li><?php echo JHTML::_('link', JRoute::_('index.php?option=com_jsolrsearch&view=basic&o=com_jspace&q='.$field.':"'.$keyj.'"'), JText::sprintf('%s (%s)', $keyj, $valuej)); ?></li>
	<?php endforeach; ?>
<?php endforeach; ?>
</ul>