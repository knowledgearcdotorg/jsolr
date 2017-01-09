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
        $this->form->load('<form><field name="author" facet="author" filter="author" type="jsolr.facet" label="Author" class="inputbox jsolrquery" showcount="true" limit="5" sort="index" mincount="1" condition="and"/></form>');
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

    public function testFacet()
    {
        $field = $this->form->getField('author');

        $result = $this->getMockBuilder('Solarium\QueryType\Select\Result\Result')
            ->disableOriginalConstructor()
            ->setMethods(array('getFacetSet', 'getFacet'))
            ->getMock();

        $result->expects($this->any())
            ->method('getFacetSet')
            ->will($this->returnSelf());

        $result->expects($this->any())
            ->method('getFacet')
            ->with('author')
            ->will($this->returnValue(
                array(
                "Facet 1"=>10,
                "Facet 2"=>5,
                "Facet 3"=>1)
            ));

        JFactory::getApplication()->setUserState('com_jsolr.search.results', $result);

        $xml = <<<XML
<ul class="inputbox jsolrquery"><li><a href="index.php?option=com_jsolr&view=search&Itemid=117&author=Facet 1">Facet 1</a><span>(10)</span></li><li><a href="index.php?option=com_jsolr&view=search&Itemid=117&author=Facet 2">Facet 2</a><span>(5)</span></li><li><a href="index.php?option=com_jsolr&view=search&Itemid=117&author=Facet 3">Facet 3</a><span>(1)</span></li></ul>
XML;

        $this->assertEquals($xml, $this->form->getInput($field->name));
    }
}
