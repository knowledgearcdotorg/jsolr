<?php
/**
 * Supports a postcode field
 * 
 * @author      $LastChangedBy: bartlomiejkielbasa $
 * @package     JSolr
 *
 * @author Bartlomiej Kielbasa <bartlomiejkielbasa@wijiti.com> * * 
 */

defined('JPATH_BASE') or die;

jimport('jsolr.form.fields.text');

class JSolrFormFieldPostcode extends JSolrFormFieldText
{
    protected $type = 'JSolr.Postcode';
}