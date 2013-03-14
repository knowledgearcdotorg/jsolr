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

//JSorl prefix!
class JSolrFormFieldDateRange extends JSolrFormFieldRangeAbstract
{
	protected $type = 'JSolr.DateRange'; //JSorl prefix
	
	/**
	 * @inheritdoc
	 */
	protected function preRender()
	{
		$document = JFactory::getDocument();
		$document->addScript('/media/com_jsolrsearch/js/jquery/jquery.js');
		$document->addScript('/media/com_jsolrsearch/js/jquery-ui/jquery-ui-1.10.1.custom.min.js');
		
		$document->addStyleSheet('/media/com_jsolrsearch/css/ui-lightness/jquery-ui-1.10.1.custom.min.css');
	}
	
	/**
	 * @inheritdoc
	 */
	public function getInputFacetFilter()
	{
		$html = '';
		$name = (string)$this->element['name'];

		$html .= '<ul data-type="jdaterange">';

		foreach ($this->getFinalOptions() as $label => $value) {
			$html .= '<li>' . JHTML::_('link', '#', $label, array('class' => 'jrange jdaterange', 'data-value' => $value)) . '</li>';
		}

		if ($this->useCustomRange()) {
			$html .= '<li class="jdaterange-custom jrange-custom">' . JHTML::_('link', '#', COM_JSOLRSEARCH_DATERANGE_CUSTOM);
			$name = $this->name;
			$id = $this->element['name'];
			
			$html .= '<span>';

			$html .= JHTML::calendar($this->value['from'], $name . '[from]', "{$id}_from");
			$html .= JHTML::calendar($this->value['to'], $name . '[to]', "{$id}_to");

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
		$attr = '';
		
		// Initialize some field attributes.
		$attr .= $this->element['class'] ? ' class="'.(string) $this->element['class'].'"' : '';
		
		// To avoid user's confusion, readonly="true" should imply disabled="true".
		if ( (string) $this->element['readonly'] == 'true' || (string) $this->element['disabled'] == 'true') {
			$attr .= ' disabled="disabled"';
		}
		
		$attr .= $this->element['size'] ? ' size="'.(int) $this->element['size'].'"' : '';
		$attr .= $this->multiple ? ' multiple="multiple"' : '';
		
		// Initialize JavaScript field attributes.
		$attr .= $this->element['onchange'] ? ' onchange="'.(string) $this->element['onchange'].'"' : '';
		
		$html = ''; // TODO
		
		return $html;
	}
	
	/**
	 * @inheritdoc
	 */
	public function getFilter()
	{
		$facet = (string)$this->element['facet'];

		if (is_array($this->value)) {
			$from 	= $this->value['from'];
			$to 	= $this->value['to'];

			if (!empty($from) && !empty($to)) {
				$from 	= JSolrHelper::getSolrDate($from);
				$to 	= JSolrHelper::getSolrDate($to);

				return $facet . ':[' . $from . ' TO ' . $to . ']';
			}
		} elseif (!empty($this->value)){
			switch ($this->value) {
				case 'd':
					return $facet . ':[NOW-1DAY TO NOW]';
					break;

				case 'w':
					return $facet . ':[NOW-7DAY TO NOW]';
					break;

				case 'm':
					return $facet . ':[NOW-1MONTH TO NOW]';
					break;

				case 'y':
					return $facet . ':[NOW-1YEAR TO NOW]';
					break;
			}
		}

		return '';
	}
	
	/**
	 * @inheritdoc
	 */
	protected function getDefaultOptions()
	{
		return array(COM_JSOLRSEARCH_DATERANGE_ANYTIME => '',COM_JSOLRSEARCH_DATERANGE_24_HOURS => 'd', COM_JSOLRSEARCH_DATERANGE_PREV_WEEK => 'w', COM_JSOLRSEARCH_DATERANGE_PREV_MONTH => 'm', COM_JSOLRSEARCH_DATERANGE_PREV_YEAR => 'y');
	}

	function fillQuery()
	{
		$filter = $this->getFilter();

		if ($filter) {
			$jSolrQuery = $this->form->getQuery()->mergeFilters($filter);
		}
	}
}
