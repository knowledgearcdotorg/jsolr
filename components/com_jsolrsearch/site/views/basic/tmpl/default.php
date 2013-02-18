<?php
/**
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
?>
<form action="<?php echo JRoute::_(JURI::base()."index.php?option=com_jsolrsearch&task=search"); ?>" method="post" name="adminForm" class="jsolr-search-form">
	<div class="jsolr-query">
		<input type="text" name="q" id="q" class="inputbox"/><button type="submit" class="jsolr-search-button">Search</button>
	</div>
	<div class="jsolr-advanced-link">
		<a href="<?php echo $this->get("AdvancedSearchURL"); ?>"><?php echo JText::_("Advanced search"); ?></a>	
	</div>
	<?php echo JHTML::_('form.token'); ?>
</form>