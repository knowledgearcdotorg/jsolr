
<?php JHTML::_('behavior.formvalidation') ?>
<?php $form = $this->get('Form') ?>

<form action="<?php echo JRoute::_(JURI::base()."index.php?option=com_jsolrsearch&task=search"); ?>" method="post" name="adminForm" class="form-validate jsolr-search-result-form">

  <ul class="main-search">
    <?php foreach($form->getFieldsets() as $fieldset ) : ?>
      <?php if ($fieldset->name == 'main'): ?>
        <?php foreach ($this->get('Form')->getFieldset($fieldset->name) as $field): ?>
        <li class="dropdown">
          <label><?php echo $field->label ?></label>
          <span><?php echo $field->getInput() ?></span>
        </li>
        <?php endforeach;?>
      <?php endif ?>
    <?php endforeach;?>

    <li><input type="submit" value="<?php echo JText::_("Search"); ?>" class="btn btn-primary" /></li>
  </ul>

  <?php if ($form->getType() == JSolrForm::TYPE_SEARCHTOOLS): ?>
      <?php foreach($form->getFieldsets() as $fieldset ) : ?>
      <?php if ($fieldset->name != 'main'): ?>
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
      <?php endif ?>
    <?php endforeach;?>

  <?php else: ?>

  <div class="navbar navbar-static">
    <div class="navbar-inner">
      <div class="container">
        <ul class="nav">
          <?php foreach($form->getFieldsets() as $fieldset ) : ?>
            <?php if ($fieldset->name != 'main'): ?>
                <?php foreach ($this->get('Form')->getFieldset($fieldset->name) as $field): ?>
                <li class="dropdown">
                    <a class="dropdown-toggle" href="#" data-toggle="dropdown" role="button"><?php echo $field->label ?><strong class="caret"></strong></a>

                      <ul class="dropdown-menu" aria-labelledby="drop" role="menu">
                        <?php echo $field->getInput() ?>
                      </ul>
                </li>
                <?php endforeach;?>
            <?php endif ?>
          <?php endforeach;?>
        </ul>
      </div>
    </div>
  </div>

  <?php endif ?>

    <?php echo JHTML::_('form.token'); ?>
</form>