<?php
require_once JPATH_ROOT.'/libraries/JSolr/vendor/autoload.php';

class JSolrFormFieldsFilterListTest extends \PHPUnit_Framework_TestCase
{
    protected $form;

    protected function setUp()
    {
        parent::setUp();

        $path = dirname(__FILE__).'/../../../../../../../components/com_jsolr/admin/models/fields/';

        $this->form = new JForm('form1');
        $this->form->addFieldPath($path);
        $this->form->load(<<<HTML
<form>
    <field
        type="jsolr.filterlist"
        name="type"
        filter="type_s"
        exactmatch="true"
        label="Type"
        class="jsolr-dropdown"
        default="">
        <option value="">Any Type</option>
        <option value="pdf" filter="application/pdf">PDF</option>
        <option value="odt" filter="application/vnd.oasis.opendocument.text">ODT</option>
    </field>
</form>
HTML
);
    }

    public function testFilter()
    {
        $field = $this->form->getField('type');
        $field->value = "pdf";

        $expected = new \Solarium\QueryType\Select\Query\FilterQuery;
        $expected->setKey('type.type_s');
        $expected->setQuery('type_s:"application/pdf"');

        $this->assertEquals($expected, $field->getFilter());
    }
}
