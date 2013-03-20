
<?php JHTML::_('behavior.formvalidation') ?>
<?php $form = $this->get('Form') ?>

<form action="<?php echo JRoute::_(JURI::base()."index.php?option=com_jsolrsearch&task=search"); ?>" method="post" name="adminForm" class="form-validate jsolr-search-result-form">
  <input type="hidden" name="plugin" value="<?php echo $this->get('CurrentPlugin') ?>" />
  <ul class="main-search">
    <?php foreach($form->getFieldsets() as $fieldset ) : ?>
      <?php if ($fieldset->name == 'main'): ?>
        <?php foreach ($this->get('Form')->getFieldset($fieldset->name) as $field): ?>
        <li class="pull-left">
          <label><?php echo $field->label ?></label>
          <span><?php echo $field->getInput() ?></span>
        </li>
        <?php endforeach;?>
      <?php endif ?>
    <?php endforeach;?>
  </ul>

  <div class="jsolr-clear"></div>

<?php $plugin = $this->get('CurrentPlugin') ?>
<?php if (!is_null($this->items) && !empty($plugin)): ?>

  <div class="btn-group">
    <?php echo JHTML::link(JURI::current(), JText::_('Everything'), array('class' => 'btn jsolr-every pull-left')) ?>

    <?php foreach ($this->get('ComponentsList') as $component): ?>
      <?php echo JHTML::link(JURI::current() . '?plugin=' . $component['plugin'], $component['name'], array('class' => 'btn pull-left')) ?>
    <?php endforeach ?>

    <?php echo JHTML::link('#', JText::_("Search Tools"), array('id' => 'jsolr-search-tools', 'class' => 'btn pull-left')) ?>
  </div>

  <div class="jsolr-clear"></div>

  <?php if ($form->getType() != JSolrForm::TYPE_SEARCHTOOLS): ?>
      <?php foreach($form->getFieldsets() as $fieldset ) : ?>
      <?php if ($fieldset->name != 'main'): ?>
          <fieldset>
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

  <div id="jsolr-search-tools-list" class="navbar navbar-static<?php if (!$this->showSearchToolsOnStart()): ?> jsolr-hidden<?php endif ?>">
    <div class="navbar-inner">
      <div class="container">
        <ul class="nav">
          <?php foreach($form->getFieldsets() as $fieldset ) : ?>
            <?php if ($fieldset->name != 'main'): ?>
                <?php foreach ($this->get('Form')->getFieldset($fieldset->name) as $field): ?>
                <li>
                    <a href="#"><?php echo $field->getValueText() ?><strong class="caret"></strong></a>
                      <?php echo $field->getInput() ?>
                </li>
                <?php endforeach;?>
            <?php endif ?>
          <?php endforeach;?>
        </ul>
      </div>
    </div>
  </div>

  <?php endif ?>
<?php endif ?>

  <?php echo JHTML::_('form.token'); ?>
</form>