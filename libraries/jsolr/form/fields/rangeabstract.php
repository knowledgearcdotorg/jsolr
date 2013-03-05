<?php
/**
 * Abstract class for all rangalbe JSolr form fields
 *
 * @author		$LastChangedBy: bartlomiejkielbasa $
 * @package		JSolr
 *
 * @author Bartlomiej Kielbasa <bartlomiejkielbasa@wijiti.com> *
 */

jimport('jsolr.form.fields.selectabstract');

abstract class JSolrFormFieldRangeAbstract extends JSolrFormSelectAbstract
{
	/**
	 * Method to get if using custom range is needed
	 */
	protected function useCustomRange()
	{
		return isset($this->element['cusotmRange']) ? (boolean)$this->element['cusotmRange'] : true;
	}
	
	/**
	 * Method to get range start point
	 * @return int
	 */
	protected function getRangeStart()
	{
		return isset($this->element['rangeStart']) ? (int)$this->element['rangeStart'] : 0;
	}
	
	/**
	 * Method to get range end point
	 * @return int
	 */
	protected function getRangeEnd()
	{
		return isset($this->element['rangeEnd']) ? (int)$this->element['rangeEnd'] : 100;
	}
	
	/**
	 * Method to get range step
	 * @return int
	 */
	protected function getRangeStep()
	{
		return isset($this->element['rangeStep']) ? (int)$this->element['rangeStep'] : 10;
	}
}