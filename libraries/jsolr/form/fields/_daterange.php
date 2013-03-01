<?php

defined('_JEXEC') or die('Restricted access');

jimport('solr.form.fields.facet');
JFormHelper::loadFieldClass('facet');

class JFormFieldDateRange extends JSolrFormFieldFacet  {
 
    protected $type = 'daterange';
    protected $selectedName = 'qdr';
    protected $rangeMinName = 'dmin';
    protected $rangeMaxName = 'dmax';
    protected $cssClass = 'daterange';

    public function getInput() {
    	$input = '<ul class="' . $this->cssClass .' ' . $this->id . 'list">';
    	$uri =& JFactory::getURI();

    	$currentVal = $uri->hasVar($this->selectedName) ? $uri->getVar($this->selectedName) : '';
    	$dmin = $uri->hasVar($this->rangeMinName) ? $uri->getVar($this->rangeMinName) : '';
    	$dmax = $uri->hasVar($this->rangeMaxName) ? $uri->getVar($this->rangeMaxName) : '';

    	$uri->setVar($this->rangeMinName, '');
    	$uri->setVar($this->rangeMaxName, '');

    	foreach ($this->getFinalOptions() as $name => $value) {
    		$uri->setVar($this->selectedName, $value);
    		$class = $this->cssClass . '-option' . ($value == $currentVal && empty($dmin) && empty($dmax) ? ' current' : '');
    		$input .= "<li><a href=\"" . $uri->toString() . '" class="' . $class . '" data-value="' . $value . '">' . $this->getOptionText($name) . '</a></li>';
    	}

    	if ($this->useCustomRange()) {
    		$app = JFactory::getApplication();
    		$templateDir = JURI::base() . 'templates/' . $app->getTemplate();

    		$input .= '<li><a href="#" class="daterange-custom">' . COM_JSOLRSEARCH_DATERANGE_CUSTOM . '</a>';
    		$input .= '<div><label>' . COM_JSOLRSEARCH_DATERANGE_FROM .  ':</label>' . $this->getRangeInput($this->rangeMinName, $dmin) . '</div>';
    		$input .= '<div><label>' . COM_JSOLRSEARCH_DATERANGE_TO .  ':</label>' . $this->getRangeInput($this->rangeMaxName, $dmax) . '</div>';
	        $input .= '<div class="daterange-example">' . $this->getExampleText() .'</div>';
	        $input .= '<div class="daterange-submit"><input type="submit" value="Submit" /></div>';
    		$input .= '</li>';
    	}

    	$input .= '</ul>';
        return $input;
    }

    protected function getFinalOptions()
    {
    	$options = $this->getOptions();

    	if (!count($options)) {
    		return $this->getDefaultOptions();
    	}

    	return $options;
    }

    protected function getDefaultOptions()
    {
    	return array(COM_JSOLRSEARCH_DATERANGE_ANYTIME => '',COM_JSOLRSEARCH_DATERANGE_24_HOURS => 'd', COM_JSOLRSEARCH_DATERANGE_PREV_WEEK => 'w', COM_JSOLRSEARCH_DATERANGE_PREV_MONTH => 'm', COM_JSOLRSEARCH_DATERANGE_PREV_YEAR => 'y');
    }

    protected function useCustomRange()
    {
    	return isset($this->element['customRange']) && $this->element['customRange']->data() == 'true';
    }

    protected function getExampleText()
    {
        return isset($this->element['exampleText']) ? $this->element['exampleText'] : COM_JSOLRSEARCH_DATERANGE_EXAMPLE;
    }

    protected function getRangeInput($name, $value)
    {
        return JHTML::calendar($value,$name,$this->rangeMinName . '_img','%Y-%m-%d');
    }

    protected function getOptionText($label)
    {
        return $label;
    }

    protected function getOptions()
    {
        return array(); // TODO
    }
}