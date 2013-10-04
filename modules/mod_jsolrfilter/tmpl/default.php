<?php
/**
 * @author		$LastChangedBy$
 * @package		JSolr
 * @copyright	Copyright (C) 2011 Wijiti Pty Ltd. All rights reserved.
 * @license     This file is part of the JSolr filter module for Joomla!.

   The JSolr filter module for Joomla! is free software: you can 
   redistribute it and/or modify it under the terms of the GNU General Public 
   License as published by the Free Software Foundation, either version 3 of 
   the License, or (at your option) any later version.

   The JSolr filter module for Joomla! is distributed in the hope 
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
 * Hayden Young					<haydenyoung@wijiti.com> 
 * Michał Kocztorz				<michalkocztorz@wijiti.com> 
 * 
 */

defined('_JEXEC') or die('Restricted access');


$document = JFactory::getDocument();

$document->addStyleSheet(JURI::base()."/media/mod_jsolrfilter/css/jsolrfilter.css");
?>

<div class="jsolr-facet-filter">
	<?php foreach($form->getFieldsets() as $fieldset ) : ?>
		<?php if ($fieldset->name == 'facets') : ?>
			<?php foreach ($form->getFieldset($fieldset->name) as $field) : ?>
				<div>
					<?php if ($field->label) : ?>
						<h4><?php echo $form->getLabel($field->name); ?></h4>
					<?php endif; ?>
					<div><?php echo $form->getFacetInput($field->name); ?></div>
				</div>
			<?php endforeach;?>
		<?php endif ?>
	<?php endforeach;?>
</div>