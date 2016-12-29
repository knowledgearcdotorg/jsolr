<?php
class JSolrFormFieldsSortTest extends \PHPUnit_Framework_TestCase
{
    protected $form;

    protected function setUp()
    {
        parent::setUp();

        $path = dirname(__FILE__).'/../../../../../../../components/com_jsolr/admin/models/fields/';

        $this->form = new JForm('form1');
        $this->form->addFieldPath($path);
        $this->form->load('<form><field name="sort" type="jsolr.sort" label="sort" default=""><option value="">relevance</option>
           <option value="date" sort="date_tdt" direction="desc">date</option></field></form>');
    }

    public function testDefaultSelected()
    {
        $field = $this->form->getField('sort');

        $this->assertEquals(array(), $field->getSort());
    }

    public function testDateSelected()
    {
        $field = $this->form->getField('sort');
        $field->value = 'date';

        $this->assertEquals(array("date_tdt"=>"desc"), $field->getSort());
    }
}
