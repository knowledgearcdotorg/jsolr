
<?php JHTML::_('behavior.formvalidation') ?>
<form action="<?php echo JRoute::_(JURI::base()."index.php?option=com_jsolrsearch&task=search"); ?>" method="post" name="adminForm" class="form-validate jsolr-search-result-form">

  <?php $form = $this->get('Form') ?>
  <?php if ($form->getType() == JSolrForm::TYPE_SEARCHTOOLS): ?>
    <?php foreach($form->getFieldsets() as $fieldset ) : ?>
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

  <?php else: ?>

  <div class="navbar navbar-static">
    <div class="navbar-inner">
      <div class="container" style="width: auto;">
        <ul class="nav">
          <?php foreach($form->getFieldsets() as $fieldset ) : ?>
            <?php foreach ($this->get('Form')->getFieldset($fieldset->name) as $field): ?>
            <li class="dropdown">
                <a class="dropdown-toggle" href="#" data-toggle="dropdown" role="button"><?php echo $field->label ?><strong class="caret"></strong></a>

                  <ul class="dropdown-menu" aria-labelledby="drop" role="menu">
                    <?php echo $field->getInput() ?>
                  </ul>
            </li>
            <?php endforeach;?>
          <?php endforeach;?>
        </ul>
      </div>
    </div>
  </div>
  
  <input type="submit" value="<?php echo JText::_("Search"); ?>" class="btn btn-primary" />

  <?php endif ?>

    <?php echo JHTML::_('form.token'); ?>
</form>