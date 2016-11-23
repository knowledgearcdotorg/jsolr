<?php
/**
 * @copyright   Copyright (C) 2013-2016 KnowledgeArc Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace JSolr\Form\Fields;

require_once JPATH_ROOT.'/libraries/JSolr/vendor/autoload.php';

use \JFactory as JFactory;
use \JArrayHelper as JArrayHelper;
use \JString as JString;
use \JText as JText;

\JLoader::import('joomla.form.helper');
\JFormHelper::loadFieldClass('list');

/**
 * The Facets form field builds a list of facets which a user
 * can then apply to the current search result set to narrow their search
 * further (I.e. filter).
 */
class Facets extends \JFormFieldList implements Filterable, Facetable
{
    const FACET_DELIMITER = '|';

    protected $type = 'JSolr.Facets';

    protected $facetInput;

    /**
     * Get a facet object for building this facet.
     *
     * Maybe as simple as \Solarium\QueryType\Select\Query\Component\Facet\Field
     * or may include complex ranges and queries.
     *
     * @return \Solarium\QueryType\Select\Query\Component\Facet\Field  An instance of the \Solarium\QueryType\Select\Query\Component\Facet\Field class.
     */
    public function getFacet()
    {
        $facet = new \Solarium\QueryType\Select\Query\Component\Facet\Field();
        $facet->setKey($this->fieldname);
        $facet->setField($this->facet);

        return $facet;
    }

    /**
     * Gets an array of facets from the current search results (provided via the
     * user's session).
     *
     * @return array An array of facets from the current search results.
     */
    protected function getFacets()
    {
        $facet = array();

        \JModelLegacy::addIncludePath(JPATH_ROOT.'/components/com_jsolr/models');

        $model = \JModelLegacy::getInstance('Search', 'JSolrModel');

        $facet = $model->getQuery()->getFacetSet()->getFacet($this->fieldname);

        return $facet;
    }

    /**
     * (non-PHPdoc)
     * @see \JFormFieldList::getInput()
     */
    protected function getInput()
    {
        return '<input type="hidden" name="' . $this->name . '" id="' . $this->id . '"' . ' value="'
            . htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') . '"/>';
    }

    /**
     * Method to get the field input markup for a generic list.
     * Use the multiple attribute to enable multiselect.
     *
     * @return  string  The field input markup.
     *
     * @since   11.1
     */
    public function getFacetInput()
    {
        // Initialize variables.
        $html = array();

        if ($class = $this->getAttribute("class", null)) {
            $class = ' class="'.$class.'"';
        }

        $html[] = '<ul'.$class.'>';
        foreach ($this->getOptions() as $option) {
            $html[] = $option;
        }
        $html[] = "</ul>";

        return implode($html);
    }

    /**
     * (non-PHPdoc)
     * @see \JFormFieldList::getOptions()
     */
    protected function getOptions()
    {
        // Initialize variables.
        $options = array();

        $facets = $this->getFacets();

        foreach ($facets as $key=>$value) {
            $html = array("<li>", "%s", "</li>");

            $key = \JSolr\Helper::getOriginalFacet($key);

            if ($this->isSelected($key)) {
                $html = array("<li class=\"active\">", "%s", "</li>");
            }

            $count = '';

            if ($this->getAttribute('count', 'false') === 'true') {
                $count = '<span>('.$value.')</span>';
            }

            $facet = '<a href="'.$this->getFilterURI($key).'">'.$key.'</a>'.$count;

            $options[] = JText::sprintf(implode($html), $facet);
        }

        reset($options);

        return $options;
    }

    /**
     * (non-PHPdoc)
     * @see JSolrFilterable::getFilters()
     */
    public function getFilters()
    {
        $cleaned = JString::trim($this->value);
        $array = explode(self::FACET_DELIMITER, $cleaned);
        $filters = array();

        if ($cleaned) {
            for ($i = 0; $i < count($array); $i++) {
                if ($this->exactmatch) {
                    $array[$i] = '"'.$array[$i].'"';
                }

                $filters[$i] = $this->filter.":".$array[$i];
            }
        }

        return (count($filters)) ? $filters : array();
    }

    /**
     * Evaluates whether the current facet is selected or not.
     *
     * @param string $facet The facet value to evaluate.
     * @return bool True if the current facet is selected, false otherwise.
     */
    protected function isSelected($facet)
    {
        $url = \JSolr\Search\Factory::getSearchRoute();

        $cleaned = JString::trim($this->value);
        $filters = explode(self::FACET_DELIMITER, $cleaned);

        $selected = false;

        while (($filter = current($filters)) && !$selected) {
            if ($filter == $facet) {
                $selected = true;
            }

            next($filters);
        }

        return $selected;
    }

    /**
     * Gets the filter uri for the current facet.
     *
     * @param string $facet The facet value to build into the filter uri.
     * @return string The filter uri for the current facet.
     */
    protected function getFilterURI($facet)
    {
        $url = clone \JSolr\Search\Factory::getSearchRoute();

        foreach ($url->getQuery(true) as $key=>$value) {
            $url->setVar($key, urlencode($value));
        }

        $filters = array();
        if ($cleaned = JString::trim($this->value)) {
            $filters = explode(self::FACET_DELIMITER, $cleaned);
        }

        if ($this->isSelected($facet)) {
            if (count($filters) > 1) {
                $found = false;

                for ($i = 0; ($filter = current($filters)) && !$found; $i++) {
                    if ($filter == $facet) {
                        unset($filters[$i]);
                        $found = true;
                    } else {
                        next($filters);
                    }
                }

                $url->setVar($this->name, urlencode(implode(self::FACET_DELIMITER, $filters)));

            } else {
                $url->delVar($this->name);
            }
        } else {
            $filters[] = $facet;
            $url->setVar($this->name, urlencode(implode(self::FACET_DELIMITER, $filters)));
        }

        return (string)$url;
    }

    public function __get($name)
    {
        switch ($name) {
            case 'filter':
            case 'facet':
                return $this->getAttribute($name, null);
                break;

            case 'exactmatch':
                if ($this->getAttribute($name, null) === 'true')
                    return true;
                else
                    return false;
                break;

            case 'facetInput':
                // If the input hasn't yet been generated, generate it.
                if (empty($this->facetInput)) {
                    $this->facetInput = $this->getFacetInput();
                }

                return $this->facetInput;
                break;

            default:
                return parent::__get($name);
        }
    }
}
