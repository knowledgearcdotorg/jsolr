<?php
/**
 * Supports a date picker.
 * 
 * @author		$LastChangedBy: bartlomiejkielbasa $
 * @package		JSolr
 * 
 * Contributors
 * Please feel free to add your name and email (optional) here if you have 
 * contributed any source code changes.
 * @author Michał Kocztorz <michalkocztorz@wijiti.com> 
 * @author Bartłomiej Kiełbasa <bartlomiejkielbasa@wijiti.com> 
 * 
 */

defined('JPATH_BASE') or die;

jimport('jsolr.form.fields.daterange');
jimport('jsolr.helper.jsolr');

class JSolrFormFieldNumberRange extends JSolrFormFieldDateRange
{
	protected $type = 'JSolr.NumberRange';

	/**
	 * @inheritdoc
	 */
	public function getInputFacetFilter()
	{
		$id = JArrayHelper::getValue($this->element, 'name'); ;
		$html = '';
		$name = (string)JArrayHelper::getValue($this->element, 'name');
		$value = explode('|', JArrayHelper::getValue($this->value, 'value'));

		if ($value[0] == '') {
			unset($value[0]);
		}

		$html .= '<input type="hidden" id="' .$id. '_value" name="' . $this->name .'[value]" value="' . implode('|', $value) .'" />';

		$html .= '<ul data-type="jnumberrange">';

		$first = true;

		foreach ($this->getFinalOptions() as $v => $label) {
			$selector = $first? $this->name : '';
			
			if (!(in_array($v, $value))) {
				if ($this->isMultiple()) {
					if ($v != '') {
						$v = array_merge($value, array($v));
					} else {
						$v = array();
					}

					$html .= '<li>' . JHTML::_('link', '#', $label, array('data-selector' => $selector, 'class' => 'jrange jnumberrange-option jrange-option', 'data-value' => implode('|', $v), 'data-name' => $id, 'id' => 'daterange_option_' . $id . '_' . implode('_', $v))) . '</li>';
				} else {
					$html .= '<li>' . JHTML::_('link', '#', $label, array('data-selector' => $selector, 'class' => 'jrange jnumberrange-option jrange-option', 'data-value' => $v, 'data-name' => $id, 'id' => 'numerrange_option_' . $id. '_' . $v)) . '</li>';
				}
			} else {
				if ($this->isMultiple()) {
					$html .= '<li><span class="jsolr-option-current">' . $label . JHTML::link('#', JHTML::image(JURI::base(false) . 'media/com_jsolrsearch/images/close.png'), array('data-selector' => $selector, 'data-value' => $v, 'class' => 'jrange-remove', 'data-name' => $id)) . ' </span></li>';
				} else {
					$html .= '<li>' . JHTML::_('link', '#', $label, array('data-selector' => $selector, 'class' => 'jrange jnumberrange-option jrange-option jrange-option-selected', 'data-value' => $v, 'data-name' => $id, 'id' => 'numerrange_option_' . $id. '_' . $v)) . '</li>';
				}
			}

			$first = false;
		}

		if ($this->useCustomRange()) {
			$html .= '<li class="jdaterange-custom jrange-custom">' . JHTML::_('link', '#', JText::_("COM_JSOLRSEARCH_DATERANGE_CUSTOM"));
			$name = $this->name;
			$from 	= JArrayHelper::getValue($this->value, 'from');
			$to 	= JArrayHelper::getValue($this->value, 'to');
			
			$html .= '<span class="jsolr-hidden">';

			$html .= '<label>' . JText::_('COM_JSOLRSEARCH_FROM') .'<input type="text" name="' . $name .'[from]" id="' . $id . '_from" value="' . $from .'" /></label>';
			$html .= '<label>' . JText::_('COM_JSOLRSEARCH_TO') .'<input type="text" name="' . $name .'[to]" id="' . $id . '_to" value="' . $to .'" /></label>';

			$html .= '<br /><input type="submit" value="' . JText::_('COM_JSOLRSEARCH_BUTTON_SUBMIT') . '" class="btn jsolr-submit" />';

			$html .= '</span>';
		
			$html .= '</li>';
		}

		$html .= '</ul>';
		
		return $html;
	}
	
	/**
	 * @inheritdoc
	 */
	public function getInputSearchTool()
	{
		$id 	= JArrayHelper::getValue($this->element, 'name');
		$html 	= '<ul>';
		$name 	= (string)JArrayHelper::getValue($this->element, 'name');

		$from 	= JArrayHelper::getValue($this->value, 'from');
		$to 	= JArrayHelper::getValue($this->value, 'to');
		$value 	= JArrayHelper::getValue($this->value, 'value');

		$html .= '<input type="hidden" id="' .$id. '_value" name="' . $this->name .'[value]" value="' . $value .'" />';

		foreach ($this->getFinalOptions() as $value => $label) {
			$html .= '<li>' . JHTML::_('link', '#', $label, array('class' => 'jrange-option jnumberrange-option', 'data-value' => $value, 'data-name' => $id, 'id' => 'numberrange_option_' . $id)) . '</li>';
		}

		if ($this->useCustomRange()) {
			$html .= '<li class="jnumberrange-custom jrange-custom">' . JHTML::_('link', '#', JText::_("COM_JSOLRSEARCH_NUMBERRANGE_CUSTOM"));
			$name = $this->name;
			
			$html .= '<span>';

			$html .= '<label>' . JText::_("COM_JSOLRSEARCH_FROM") .'<input type="text" name="' . $name .'[from]" value="' . $from .'" id="' . $id . '_from" /></label>';
			$html .= '<label>' . JText::_("COM_JSOLRSEARCH_TO") .'<input type="text" name="' . $name .'[to]" value="' . $to .'" id="' . $id . '_to" /></label>';

			$html .= '<br /><input type="submit" value="' . JText::_('COM_JSOLRSEARCH_BUTTON_SUBMIT') . '" class="btn jsolr-submit" />';

			$html .= '</span>';
		
			$html .= '</li>';
		}

		$html .= '</ul>';
		
		return $html;
	}
	
	/**
	 * @inheritdoc
	 */
	public function getFilter()
	{
		$facet = JArrayHelper::getValue($this->element, 'facet');

		$filter = '';

		if (is_array($this->value)) {
			$from 	= JArrayHelper::getValue($this->value, 'from');
			$to 	= JArrayHelper::getValue($this->value, 'to');
			$value 	= JArrayHelper::getValue($this->value, 'value');

			if (is_numeric($from) || is_numeric($to)) {
				if (!is_numeric($from)) {
					$from = '*';
				} elseif (!is_numeric($to)) {
					$to = '*';
				}

				$filter = $facet . ':[' . $from . ' TO ' . $to . ']';
			} elseif (!empty($value)) {
				$filters = array();

				foreach (explode('|', $value) as $val) {
					$val = explode('_', $val);

					$filters[] = '[' . $this->escape($val[0]) . ' TO ' . $this->escape($val[1]) . ']';
				}

				if (count($filters)) {
					$filter = $facet . ':' . implode(' OR ', $filters);
				}
			}
		}

		return $filter;
	}

	function getDefaultOptions()
	{
		$step 	= $this->getStep();
		$start 	= $this->getStart();
		$end 	= $this->getEnd();

		$options = array('' => JText::_("COM_JSOLRSEARCH_NUMBERRANGE_ALL"));

		while($start < $end) {
			if ($start + $step <= $end) {
				$options[$start . '_' . ($start + $step)] = sprintf(JText::_("COM_JSOLRSEARCH_NUMBERRANGE_FROM_TO"), $start, $start + $step); 
			} else {
				$options[$start . '_' . $end] = sprintf(JText::_("COM_JSOLRSEARCH_NUMBERRANGE_FROM_TO"), $start, $end);
			}

			$start += $step;
		}

		return $options;
	}

	function getStep()
	{
		return isset($this->element['step']) ? (int)$this->element['step'] : 10;
	}

	function getStart()
	{
		return isset($this->element['start']) ? (int)$this->element['start'] : 0;
	}

	function getEnd()
	{
		return isset($this->element['end']) ? (int)$this->element['end'] : 100;
	}
}