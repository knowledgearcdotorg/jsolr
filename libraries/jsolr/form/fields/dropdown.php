<?php
/**
 * Supports a collection picker.
 * 
 * @author		$LastChangedBy: spauldingsmails $
 * @package		JSpace
 * @copyright	Copyright (C) 2011 Wijiti Pty Ltd. All rights reserved.
 * @license     This file is part of the JSpace component for Joomla!.

   The JSpace component for Joomla! is free software: you can redistribute it 
   and/or modify it under the terms of the GNU General Public License as 
   published by the Free Software Foundation, either version 3 of the License, 
   or (at your option) any later version.

   The JSpace component for Joomla! is distributed in the hope that it will be 
   useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   GNU General Public License for more details.

   You should have received a copy of the GNU General Public License
   along with the JSpace component for Joomla!.  If not, see 
   <http://www.gnu.org/licenses/>.

 * Contributors
 * Please feel free to add your name and email (optional) here if you have 
 * contributed any source code changes.
 * Name							Email
 * Michał Kocztorz				<michalkocztorz@wijiti.com> 
 * 
 */

defined('JPATH_BASE') or die;

jimport('joomla.form.formfield');
jimport('joomla.form.helper');
jimport('jsolr.form.fields.filterable');

JFormHelper::loadFieldClass('list');

class JSolrFormFieldDropdown extends JFormFieldList implements JSolrFilterable
{
	/**
	 * The form field type.
	 *
	 * @var         string
	 * @since       1.6
	 */
	protected $type = 'JSolr.dropdown';
	
	protected static $_headLoaded = false;

	protected function getInput()
	{
		$this->_head();
		$ul  = '<div class="jsolr-dropdown">';
		$ul .= '<input class="jsolr-dropdown-input" type="hidden" name="' . $this->name . '" id="' . $this->id . '"' . ' value="' . htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') . '" />';
		$ul .= '<div class="jsolr-dropdown-label">' . $this->getValueLabel() . '</div>';
		$ul .= '<ul class="jsolr-dropdown-list">' . implode('', $this->getOptions()) . '</ul>';
		$ul .= '</div>';
		return $ul;
	}
	
	protected function getValueLabel() {
		$ret = "";
		foreach ($this->element->children() as $option)
		{
			// Only add <option /> elements.
			if ($option->getName() != 'option')
			{
				continue;
			}
				
			$selected = ((string) $option['value']) == $this->value;
			if( $selected ) {
				return trim((string) $option);
			}
		
		}
		
		return $ret;
	}
	
	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   11.1
	 */
	protected function getOptions()
	{
		// Initialize variables.
		$options = array();
	
		foreach ($this->element->children() as $option)
		{
	
			// Only add <option /> elements.
			if ($option->getName() != 'option')
			{
				continue;
			}
			
			$selected = ((string) $option['value']) == $this->value;
	
			// Create a new option object based on the <option /> element.
			$tmp = '<li class="jsolr-dropdown-option' . ( $selected ? ' jsolr-dropdown-option-selected' : '' ) . '" data-value="' . ((string) $option['value']) . '">' . $this->getOption($option) . '</li>';
	
	
			// Add the option object to the result set.
			$options[] = $tmp;
		}
	
		reset($options);
	
		return $options;
	}
	
	/**
	 * Render contents of <li>
	 * @param unknown_type $element
	 * @return string
	 */
	protected function getOption( $option ) {
		return trim((string) $option);
	}
	
	protected function _head() {
		if( self::$_headLoaded ) {
			return;
		}
		$doc = JFactory::getDocument();
		$doc->addScript( "http://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js" ); //loaded from google
		$doc->addCustomTag( '<script type="text/javascript">jQuery.noConflict();</script>' );
		
		$content = <<< CSS
.jsolr-dropdown{
	display:inline-block;
	height: 2em;
	width:200px;
	position:relative;
	background-color: #f9f9f9;
}
.jsolr-dropdown-label{
	margin:0px;
	border:0px;
	background-color: transparent;
	width:100%;
	height:100%;
	text-align: center;
	display:block;
	cursor: pointer;
	line-height: 2em;
	color: #777;
	font-weight: 700;
}
.jsolr-dropdown-label:hover{
	color: #222;
}
.jsolr-dropdown-list{
	position: absolute;
	z-index: 1000;
	height:1.5em;
	min-width: 100%;
	top: 0px;
	display:none;
}
.jsolr-dropdown li.jsolr-dropdown-option{
	display:none;
	height: 1.5em;
	line-height:1.5em;
	cursor: pointer;
	padding: 0px 5px 0px 15px;
}
.jsolr-dropdown-active ul.jsolr-dropdown-list{
    background: none repeat scroll 0 0 #FFFFFF;
    border: 1px solid #D6D6D6;
    box-shadow: 0 2px 4px #D6D6D6;
    color: #333333;
    padding-bottom: 5px;
    padding-top: 5px;
	height:auto;
	top: 90%;
	display:block;
}
.jsolr-dropdown-active li.jsolr-dropdown-option{
	padding: 6px 30px 6px 30px;
}
.jsolr-dropdown-active li.jsolr-dropdown-option-selected{
	font-weight: 700;
	color:#000;
}
.jsolr-dropdown-active li.jsolr-dropdown-option{
	display:block;
}
.jsolr-dropdown-active li:hover.jsolr-dropdown-option{
	background-color: #f4f4f4;		
}
CSS;
		$doc->addStyleDeclaration($content);
		
		$js = <<< JS
(function($){
	$(document).ready( function() {
		$('.jsolr-dropdown').on('click', function(e){
			e.stopPropagation();
			$(this).addClass('jsolr-dropdown-active');
		});
		$('.jsolr-dropdown').on('click', '.jsolr-dropdown-option', function(e){
			var option = $(this);
			if( option.parents('.jsolr-dropdown-active').length > 0 ) {
				e.stopPropagation();
				var dropdown = option.parents('.jsolr-dropdown');
				dropdown.find('.jsolr-dropdown-option').removeClass('jsolr-dropdown-option-selected');
				$('.jsolr-dropdown').removeClass('jsolr-dropdown-active');
				dropdown.find('.jsolr-dropdown-input').val(option.data('value'));
				dropdown.find('.jsolr-dropdown-label').html(option.html());
				option.addClass('jsolr-dropdown-option-selected');
			}
		});
		$('body').on('click', function(){
			$('.jsolr-dropdown').removeClass('jsolr-dropdown-active');
		});
	});
})(jQuery);
JS;
		$doc->addScriptDeclaration($js);
		self::$_headLoaded = true;
	}
	
	public function getFilter()
	{
		return null;
	}
}