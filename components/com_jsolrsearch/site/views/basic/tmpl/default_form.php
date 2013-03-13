
<?php JHTML::_('behavior.formvalidation') ?>
<form action="<?php echo JRoute::_(JURI::base()."index.php?option=com_jsolrsearch&task=search"); ?>" method="post" name="adminForm" class="form-validate jsolr-search-result-form">

  <div id="jSolrOptions">
     Select component:
     <select name="plugin">
        <?php foreach ($this->plugins as $plugin): ?>
          <option value="">ALL</option>
           <option value="<?php echo $$plugin['plugin'] ?>"><?php echo $plugin['name'] ?></option>
        <?php endforeach ?>
     </select>
  </div>

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

    <?php echo JHTML::_('form.token'); ?>
</form>