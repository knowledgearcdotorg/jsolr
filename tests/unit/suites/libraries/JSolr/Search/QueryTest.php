<?php
use \JSolr\Apache\Solr\Service;
use \JSolr\Search\Query;

class JSolrSearchQueryTest extends \PHPUnit_Framework_TestCase
{
    public function testFacet()
    {
        $app = JFactory::getApplication();

        $properties = $app->get('build.properties');

        $host = JArrayHelper::getValue($properties, 'solr.host');
        $port = JArrayHelper::getValue($properties, 'solr.port');
        $path = JArrayHelper::getValue($properties, 'solr.path');

        $service = new Service($host, $port, $path);

        $query = new Query('*:*', $service);

        $query->useQueryParser('edismax')
            ->facet(0, 'index', -1)
            ->rows(0)
            ->facetFields(array('dc.contributor.author_fc'));

        $results = $query->search();

        $facets = $results->getFacets()->{"dc.contributor.author_fc"};

        $this->assertEquals(array_keys((array)$facets), array('Young, Hayden'));
        $this->assertEquals(count((array)$facets), 1);
    }

    public function testDateRange()
    {
        $app = JFactory::getApplication();

        $properties = $app->get('build.properties');

        $host = JArrayHelper::getValue($properties, 'solr.host');
        $port = JArrayHelper::getValue($properties, 'solr.port');
        $path = JArrayHelper::getValue($properties, 'solr.path');

        $range = "dc.date.issued_tdt";
        $start = "NOW/YEAR-5YEAR";
        $end = "NOW";
        $gap = "+1YEAR";

        $service = new Service($host, $port, $path);

        $query = new Query('*:*', $service);

        $query->useQueryParser('edismax')
            ->facet(0, 'index', -1)
            ->rows(0)
            ->facetRange($range, $start, $end, $gap);

        $results = $query->search();

        $this->assertEquals(count((array)$results->getFacetRanges()->{$range}->counts), 6);
    }

    public function testDateRangeWithMinCount()
    {
        $app = JFactory::getApplication();

        $properties = $app->get('build.properties');

        $host = JArrayHelper::getValue($properties, 'solr.host');
        $port = JArrayHelper::getValue($properties, 'solr.port');
        $path = JArrayHelper::getValue($properties, 'solr.path');

        $field = "dc.date.issued_tdt";
        $start = "NOW/YEAR-5YEAR";
        $end = "NOW";
        $gap = "+1YEAR";

        $service = new Service($host, $port, $path);

        $query = new Query('*:*', $service);

        $query->useQueryParser('edismax')
            ->facet(1, 'index', -1)
            ->rows(0)
            ->facetRange($field, $start, $end, $gap);

        $results = $query->search();

        $this->assertEquals(count((array)$results->getFacetRanges()->{"dc.date.issued_tdt"}->counts), 1);
    }
}