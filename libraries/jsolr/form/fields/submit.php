<?php
/**
 * Class to select lang
 *
 * @author      $LastChangedBy: bartlomiejkielbasa $
 * @package     JSolr
 *
 * @author Bartlomiej Kielbasa <bartlomiejkielbasa@wijiti.com> *
 */

jimport('jsolr.form.fields.abstract');

class JSolrFormFieldSubmit extends JSolrFormFieldAbstract {
    /**
     * @inheritdoc
     */
    public function getFilter()
    {

        return '';
    }
    
    function getValueText()
    {
        return '';
    }

    function getInputFacetFilter()
    {
        return '<input type="submit" value="' . JText::_("COM_JSOLRSEARCH_BUTTON_SUBMIT") . '" class="btn btn-primary" />';
    }

    function getInputSearchTool()
    {
        return '<input type="submit" value="' . JText::_("COM_JSOLRSEARCH_BUTTON_SUBMIT") . '" class="btn btn-primary" />';
    }
}