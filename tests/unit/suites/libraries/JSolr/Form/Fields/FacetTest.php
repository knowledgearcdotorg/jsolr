<?php
class JSolrFormFieldsFacetTest extends \PHPUnit_Framework_TestCase
{
    protected $form;

    protected function setUp()
    {
        parent::setUp();

        $path = dirname(__FILE__).'/../../../../../../../components/com_jsolr/admin/models/fields/';

        $this->form = new JForm('form1');
        $this->form->addFieldPath($path);
        $this->form->load('<form><field name="author" facet="author" filter="author" type="jsolr.facet" label="Author" class="inputbox jsolrquery" showcount="true" limit="5" sort="index" mincount="1"/></form>');
    }

    public function testFacetQuery()
    {
        $field = $this->form->getField('author');

        $expected = new \Solarium\QueryType\Select\Query\Component\Facet\Field();
        $expected->setKey('author');
        $expected->setField('author');
        $expected->setLimit(5);
        $expected->setSort('index');
        $expected->setMinCount('1');

        $this->assertEquals($expected, $field->getFacetQuery());
    }

    public function testFilter()
    {
        $field = $this->form->getField('author');
        $field->value = "Hayden Young";

        $expected = new \Solarium\QueryType\Select\Query\FilterQuery;
        $expected->setKey('author.author');
        $expected->setQuery('author:"Hayden Young"');

        $this->assertEquals($expected, $field->getFilter());
    }

    public function testFilterMultipleValues()
    {
        $field = $this->form->getField('author');
        $field->value = "Hayden Young|Ann-Teresa Young";

        $expected = new \Solarium\QueryType\Select\Query\FilterQuery;
        $expected->setKey('author.author');
        $expected->setQuery('author:"Hayden Young" AND "Ann-Teresa Young"');

        $this->assertEquals($expected, $field->getFilter());
    }

    public function testFilterWithSpecialChars()
    {
        $field = $this->form->getField('author');
        $field->value = "Hayden::Young";

        $expected = new \Solarium\QueryType\Select\Query\FilterQuery;
        $expected->setKey('author.author');
        $expected->setQuery('author:"Hayden::Young"');

        $this->assertEquals($expected, $field->getFilter());
    }

    public function testFilterWithSpecialCharsNotExactMatch()
    {
        $field = $this->form->getField('author');
        $field->value = "Hayden::Young";
        $field->exactmatch = false;

        $expected = new \Solarium\QueryType\Select\Query\FilterQuery;
        $expected->setKey('author.author');
        $expected->setQuery('author:Hayden\:\:Young');

        $this->assertEquals($expected, $field->getFilter());
    }
}
