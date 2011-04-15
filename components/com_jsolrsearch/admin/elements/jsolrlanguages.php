<?php
// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

/**
 * Renders a languages element
 *
 * @package 	Joomla.Framework
 * @subpackage		Parameter
 * @since		1.5
 */

class JElementJsolrlanguages extends JElement
{
	/**
	* Element name
	*
	* @access	protected
	* @var		string
	*/
	var	$_name = 'Languages';

	function fetchElement($name, $value, &$node, $control_name)
	{
		$user	= & JFactory::getUser();

		/*
		 * @TODO: change to acl_check method
		 */
		if(!($user->get('gid') >= 23) && $node->attributes('client') == 'administrator') {
			return JText::_('No Access');
		}


		$client = $node->attributes('client');

		jimport('joomla.language.helper');
		$languages = JLanguageHelper::createLanguageList($value, constant('JPATH_'.strtoupper($client)), true);
		array_unshift($languages, JHTML::_('select.option', '', '- '.JText::_('Select Language').' -'));

		return JHTML::_('select.genericlist',  $languages, $name, 'class="inputbox"', 'value', 'text', $value, $name );
	}
}
