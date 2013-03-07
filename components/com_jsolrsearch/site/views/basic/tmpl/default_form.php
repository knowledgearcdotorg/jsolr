
<?php JHTML::_('behavior.formvalidation') ?>
<form action="<?php echo JRoute::_(JURI::base()."index.php?option=com_jsolrsearch&task=search"); ?>" method="post" name="adminForm" class="form-validate jsolr-search-result-form">

  <?php foreach($this->get('Form')->getFieldsets() as $fieldset ) : ?>
    <fieldset>
      <legend><?php echo JText::_($fieldset->label); ?></legend>
      <?php foreach ($this->get('Form')->getFieldset($fieldset->name) as $field): ?>
        <label><?php echo $field->label ?></label>
        <span><?php echo $field->getInput() ?></span>
      <?php endforeach ?>
    </fieldset>

    <fieldset>
        <label class="pull-right"><input type="submit" value="<?php echo JText::_("Search"); ?>" class="btn btn-primary" /></label>
    </fieldset>
  <?php endforeach;?> 

    <div class="jsolr-advanced-link">
        <a href="<?php echo $this->get("AdvancedSearchURL"); ?>"><?php echo JText::_("Advanced search"); ?></a>   
    </div>  

    <?php echo JHTML::_('form.token'); ?>
</form>