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

jimport('jsolr.form.fields.rangeabstract');
jimport('jsolr.helper.jsolr');
jimport('jsolr.helper.jhtml');

//JSorl prefix!
class JSolrFormFieldDateRange extends JSolrFormFieldRangeAbstract
{
	protected $type = 'JSolr.DateRange'; //JSorl prefix
	
	/**
	 * @inheritdoc
	 */
	public function getInputFacetFilter()
	{
		$id = $this->element['name'];
		$html = '<ul>';
		$name = (string)$this->element['name'];
		$value = explode('|', $this->value['value']);

		if ($value[0] == '') {
			unset($value[0]);
		}

		$html .= '<input type="hidden" id="' .$id. '_value" name="' . $this->name .'[value]" value="' . implode('|', $value) .'" />';

		$html .= '';

		foreach ($this->getFinalOptions() as $v => $label) {
			if (!(in_array($v, $value))) {
				if ($this->isMultiple()) {
					if ($v != '') {
						$v = array_merge($value, array($v));
					} else {
						$v = array();
					}

					$html .= '<li>' . JHTML::_('link', '#', $label, array('class' => 'jrange jdaterange-option jrange-option', 'data-value' => implode('|', $v), 'data-name' => $id, 'id' => 'daterange_option_' . $id)) . '</li>';
				} else {
					$html .= '<li>' . JHTML::_('link', '#', $label, array('class' => 'jrange jdaterange-option jrange-option', 'data-value' => $v, 'data-name' => $id, 'id' => 'daterange_option_' . $id)) . '</li>';
				}

				
			} else {
				if ($this->isMultiple()) {
					$html .= '<li><span class="jsolr-option-current">' . $label . JHTML::link('#', JHTML::image(JURI::base(false) . 'media/com_jsolrsearch/images/close.png'), array('data-value' => $v, 'class' => 'jrange-remove', 'data-name' => $id)) . ' </span></li>';
				} else {
					$html .= '<li><span class="jsolr-option-current">' . $label . '</span></li>';
				}
			}
		}

		if ($this->useCustomRange()) {
			$html .= '<li class="jdaterange-custom jrange-custom .jsolr-hidden">' . JHTML::_('link', '#', JText::_(COM_JSOLRSEARCH_DATERANGE_CUSTOM));
			$name = $this->name;
			
			$html .= '<span>';

			$html .= JSolrHtML::calendar($this->value['from'], $name . '[from]', "{$id}_from");
			$html .= JSolrHtML::calendar($this->value['to'], $name . '[to]', "{$id}_to");

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
		$id = $this->element['name'];
		$html = '<ul>';
		$name = (string)$this->element['name'];
		$value = explode('|', $this->value['value']);

		if ($value[0] == '') {
			unset($value[0]);
		}

		$html .= '<input type="hidden" id="' .$id. '_value" name="' . $this->name .'[value]" value="' . implode('|', $value) .'" />';

		$html .= '';

		foreach ($this->getFinalOptions() as $v => $label) {
			if (!(in_array($v, $value))) {
				if ($this->isMultiple()) {
					if ($v != '') {
						$v = array_merge($value, array($v));
					} else {
						$v = array();
					}

					$html .= '<li>' . JHTML::_('link', '#', $label, array('class' => 'jrange jdaterange-option jrange-option', 'data-value' => implode('|', $v), 'data-name' => $id, 'id' => 'daterange_option_' . $id)) . '</li>';
				} else {
					$html .= '<li>' . JHTML::_('link', '#', $label, array('class' => 'jrange jdaterange-option jrange-option', 'data-value' => $v, 'data-name' => $id, 'id' => 'daterange_option_' . $id)) . '</li>';
				}

				
			} else {
				if ($this->isMultiple()) {
					$html .= '<li><span class="jsolr-option-current">' . $label . JHTML::link('#', JHTML::image(JURI::base(false) . 'media/com_jsolrsearch/images/close.png'), array('data-value' => $v, 'class' => 'jrange-remove', 'data-name' => $id)) . ' </span></li>';
				} else {
					$html .= '<li><span class="jsolr-option-current">' . $label . '</span></li>';
				}
			}
		}

		if ($this->useCustomRange()) {
			$html .= '<li class="jdaterange-custom jrange-custom">' . JHTML::_('link', '#', JText::_(COM_JSOLRSEARCH_DATERANGE_CUSTOM));
			$name = $this->name;
			
			$html .= '<span>';

			$html .= JSolrHtML::calendar($this->value['from'], $name . '[from]', "{$id}_from");
			$html .= JSolrHtML::calendar($this->value['to'], $name . '[to]', "{$id}_to");

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
		$facet = (string)$this->element['facet'];

		$filter = '';

		if (is_array($this->value)) {
			$from 	= $this->value['from'];
			$to 	= $this->value['to'];
			$value  = $this->value['value'];

			if (!empty($from) && !empty($to)) {
				$from 	= JSolrHelper::getSolrDate($from);
				$to 	= JSolrHelper::getSolrDate($to);

				$filter = $facet . ':[' . $from . ' TO ' . $to . ']';
			} elseif (!empty($value)){
				$filters = array();

				foreach (explode('|', $value) as $val) {
					switch ($value) {
						case 'd':
							$filters[] = '[NOW-1DAY TO NOW]';
							break;

						case 'w':
							$filters[] = '[NOW-7DAY TO NOW]';
							break;

						case 'm':
							$filters[] = '[NOW-1MONTH TO NOW]';
							break;

						case 'y':
							$filters[] = '[NOW-1YEAR TO NOW]';
							break;
					}
				}

				if (count($filters)) {
					$filter = $facet . ':' . implode(' OR ', $filters);
				}
			}
		}

		return $filter;
	}
	
	/**
	 * @inheritdoc
	 */
	protected function getDefaultOptions()
	{
		return array('' => JText::_(COM_JSOLRSEARCH_DATERANGE_ANYTIME),'d' => JText::_(COM_JSOLRSEARCH_DATERANGE_LASTDAY),'w' => JText::_(COM_JSOLRSEARCH_DATERANGE_LASTWEEK), 'm' => JText::_(COM_JSOLRSEARCH_DATERANGE_LASTMONTH), 'y' => JText::_(COM_JSOLRSEARCH_DATERANGE_LASTYEAR));
	}

	function getValueText()
	{
		if (is_array($this->value)) {
			$from 	= $this->value['from'];
			$to 	= $this->value['to'];
			$value  = $this->value['value'];

			if (!empty($from) && !empty($to)) {
				return JText::_(COM_JSOLRSEARCH_DATERANGE_FROM) . ' ' . $from . ' ' . JText::_(COM_JSOLRSEARCH_DATERANGE_TO) . ' ' . $to;
			}elseif (!empty($value)){
				$options = $this->getFinalOptions();
				return $options[$value];
			}
		}

		$options = $this->getFinalOptions();

		return $options[''];
	}

	function getLabelSearchTool()
	{
		return $this->getValueText() . '<span class="more"></span>';
	}
}

