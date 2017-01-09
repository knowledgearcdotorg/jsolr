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
class Facet extends \JFormFieldList implements Filterable, Facetable
{
    const FACET_DELIMITER = '|';

    protected $type = 'JSolr.Facet';

    /**
     * (non-PHPdoc)
     * @see Facetable::getFacetQuery()
     */
    public function getFacetQuery()
    {
        $facet = new \Solarium\QueryType\Select\Query\Component\Facet\Field();
        $facet->setKey($this->fieldname);
        $facet->setField($this->facet);

        if ($this->limit) {
            $facet->setLimit($this->limit);
        }

        if (array_search(strtolower($this->sort), array('index', 'count')) !== false) {
            $facet->setSort($this->sort);
        }

        if ($this->mincount) {
            $facet->setMinCount($this->mincount);
        }

        return $facet;
    }

    /**
     * Gets an array of facets from the current search results (provided via the
     * user's session).
     *
     * @return array An array of facets from the current search results.
     */
    protected function getFacet()
    {
        $facet = array();

        $results = JFactory::getApplication()->getUserState('com_jsolr.search.results');

        $facetSet = $results->getFacetSet();

        $facet = $facetSet->getFacet($this->fieldname);

        return $facet;
    }

    /**
     * Method to get the field input markup for a generic list.
     * Use the multiple attribute to enable multiselect.
     *
     * @return  string  The field input markup.
     *
     * @since   11.1
     */
    protected function getInput()
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

        $facets = $this->getFacet();

        foreach ($facets as $key=>$value) {
            $html = array("<li>", "%s", "</li>");

            $key = \JSolr\Helper::getOriginalFacet($key);

            if ($this->isSelected($key)) {
                $html = array("<li class=\"active\">", "%s", "</li>");
            }

            $count = '';

            if ($this->showcount === 'true') {
                $count = '<span>('.$value.')</span>';
            }

            $facet = '<a href="'.$this->buildFilterUri($key).'">'.$key.'</a>'.$count;

            $options[] = JText::sprintf(implode($html), $facet);
        }

        reset($options);

        return $options;
    }

    /**
     * (non-PHPdoc)
     * @see Filterable::getFilter()
     */
    public function getFilter()
    {
        $value = JString::trim($this->value);

        if ($value) {
            $filter = new \Solarium\QueryType\Select\Query\FilterQuery();
            $helper = new \Solarium\Core\Query\Helper;

            $array = array();

            foreach (explode(self::FACET_DELIMITER, $value) as $item) {
                if ($this->exactmatch) {
                    $item = $helper->escapePhrase($item);
                } else {
                    $item = $helper->escapeTerm($item);
                }

                $array[] = $item;
            }

            if (count($array) > 0) {
                $separator = " ".JString::strToUpper($this->condition)." ";

                $filter->setKey($this->name.".".$this->filter);
                $filter->setQuery($this->filter.":".implode($separator, $array));

                return $filter;
            }
        }

        return null;
    }

    /**
     * Evaluates whether the current facet is selected or not.
     *
     * @param string $facet The facet value to evaluate.
     * @return bool True if the current facet is selected, false otherwise.
     */
    protected function isSelected($facet)
    {
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
     * Builds the filter uri for the current facet.
     *
     * @param string $facet The facet value to build into the filter uri.
     * @return string The filter uri for the current facet.
     */
    protected function buildFilterUri($facet)
    {
        $url = clone \JSolr\Search\Factory::getSearchRoute();

        foreach ($url->getQuery(true) as $key=>$value) {
            $url->setVar($key, $value);
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

                $url->setVar($this->name, implode(self::FACET_DELIMITER, $filters));
            } else {
                $url->delVar($this->name);
            }
        } else {
            $filters[] = $facet;
            $url->setVar($this->name, implode(self::FACET_DELIMITER, $filters));
        }

        return (string)$url;
    }

    public function __get($name)
    {
        switch ($name) {
            case 'filter':
            case 'facet':
            case 'limit':
            case 'showcount':
            case 'sort':
            case 'mincount':
                return $this->getAttribute($name, null);

                break;

            case 'exactmatch':
                if ($this->getAttribute($name, null) === 'false') {
                    return false;
                } else {
                    return true;
                }

                break;

            case "condition":
                return $this->getAttribute($name, "or");

                break;

            default:
                return parent::__get($name);
        }
    }
}
