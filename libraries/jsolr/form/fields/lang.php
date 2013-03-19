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

class JSolrFormFieldLang extends JSolrFormFieldSelectAbstract {

    /**
     * Method to get default list of countires
     * @return array
     */
    protected function getDefaultOptions()
    {
        return array(
            'en-GB' => 'English (GB)',
            'en-US' => 'English (US)',
            'pl-PL' => 'Polski'
        );
    }

    /**
     * @inheritdoc
     */
    public function getFilter()
    {
        $facet = (string)$this->element['facet'];

        $filter = '';

        if (!empty($this->value)) {
            if (is_string($this->value)) {
                $filter = $facet . ':' . $this->value;
            } elseif (is_array($this->value)) {
                $filter = $facet . ':' . implode(' OR ', $this->value);
            }
        }        

        return $filter;
    }

    function fillQuery()
    {
        $filter = $this->getFilter();

        if ($filter) {
            $this->form->getQuery()->mergeFilters($filter);
            return true;
        }

        return false;
    }

    function getValueText()
    {
        if (!is_array($this->value) || count($this->value) == 0) {
            return JText::_(COM_JSOLRSEARCH_LANGUAGE_ALL);
        }

        $result = array();
        $options = $this->getFinalOptions();

        foreach ($this->value as $v) {
            $result[] = $options[$v];
        }

        return implode(', ', $result);
    }
}