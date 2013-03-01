<?php

defined('_JEXEC') or die('Restricted access');

jimport('joomla.form.fields.list');
JFormHelper::loadFieldClass('list');

class JSolrFormFieldFacet extends JFormField
{
	protected function getDefaultOptions()
    {
      	return array(COM_JSOLRSEARCH_DATERANGE_ANYTIME => '',COM_JSOLRSEARCH_DATERANGE_24_HOURS => 'd', COM_JSOLRSEARCH_DATERANGE_PREV_WEEK => 'w', COM_JSOLRSEARCH_DATERANGE_PREV_MONTH => 'm', COM_JSOLRSEARCH_DATERANGE_PREV_YEAR => 'y');
    }

    protected function getInput()
    {
    }
}