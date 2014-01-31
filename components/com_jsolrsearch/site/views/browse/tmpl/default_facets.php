<?php
/**
 * Default display for browse view.
 * 
 * @copyright	Copyright (C) 2012-2013 KnowledgeARC Ltd. All rights reserved.
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
 * Hayden Young					<hayden@knowledgearc.com>
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('jsolr.helper');
?>
<ul>
<?php foreach ($this->items as $keyi=>$valuei) : ?>
	<?php $field = JArrayHelper::getValue($this->state->get('facet.operators'), $keyi); ?>
	<?php foreach ($valuei as $keyj=>$valuej) : ?>

		<?php 
		$vars = array(
				JFactory::getApplication()->input->get('name')=>JSolrHelper::getOriginalFacet($keyj),
                'o'=>JFactory::getApplication()->input->get('o'));
		
		if ($this->params->get('show_count')) {
			$facet = JText::sprintf('%s [%s]', JSolrHelper::getOriginalFacet($keyj), $valuej);
		} else {
			$facet = JText::sprintf('%s', JSolrHelper::getOriginalFacet($keyj));
		}
		?>
            
		<li><?php echo JHTML::_('link', JRoute::_(JSolrSearchFactory::getSearchRoute($vars)), $facet); ?></li>
	<?php endforeach; ?>
<?php endforeach; ?>
</ul>