<?php
require_once JPATH_ROOT.'/libraries/JSolr/vendor/autoload.php';

class JSolrFormFieldsCalendarTest extends \PHPUnit_Framework_TestCase
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
        type="jsolr.calendar"
        name="date"
        filter="date_dt"
        label="Date"
        class="jsolr-dropdown"
        default="">
        <option value="">Anytime</option>
        <option value="h" filter="[NOW-1HOUR TO NOW]">Past Hour</option>
        <option value="d" filter="[NOW-1DAY TO NOW]">Past Day</option>
        <option value="w" filter="[NOW-7DAY TO NOW]">Past Week</option>
        <option value="m" filter="[NOW-1MONTH TO NOW]">Past Month</option>
        <option value="y" filter="[NOW-1YEAR TO NOW]">Past Year</option>
    </field>
</form>
HTML
);
    }

    public function testFilter()
    {
        $field = $this->form->getField('date');
        $field->value = "d";

        $expected = new \Solarium\QueryType\Select\Query\FilterQuery;
        $expected->setKey('date.date_dt');
        $expected->setQuery('date_dt:[NOW-1DAY TO NOW]');

        $this->assertEquals($expected, $field->getFilter());
    }
}
