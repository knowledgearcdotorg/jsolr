
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
<?php if (!is_null($this->items) || !empty($plugin)): ?>

  <div class="btn-group">
    <?php echo JHTML::link(JURI::current(), JText::_('Everything'), array('class' => 'btn jsolr-every pull-left')) ?>

    <?php $i = 0; $max = $this->getComponentsLimit(); $components = $this->get('ComponentsList') ?>
    <?php $count = count($components) ?>

    <?php for ($i = 0; $i < $count; ++$i): ?>
      <?php if ($i == $max): ?>
        <ul class="nav nav-more pull-left">
          <li>
            <a href="#"><?php echo JText::_(COM_JSOLRSEARCH_COMPONENTS_MORE) ?><span class="more"></span></a>

            <ul class="more-list">
              <?php for(; $i < $count; ++$i): ?>
                <li><?php echo JHTML::link($this->updateUri(array('plugin' => $components[$i]['plugin'])), $components[$i]['name'], array('class' => 'btn pull-left')) ?></li>
              <?php endfor ?>
            </ul>
          </li>
        </ul>

        <?php break?>
      <?php endif ?>
      <?php echo JHTML::link($this->updateUri(array('plugin' => $components[$i]['plugin'])), $components[$i]['name'], array('class' => 'btn pull-left')) ?>
    <?php endfor ?>

    <?php echo JHTML::link('#', JText::_("Search Tools"), array('id' => 'jsolr-search-tools', 'class' => 'btn pull-left')) ?>
  </div>

  <div class="jsolr-clear"></div>

  <?php if ($form->getType() != JSolrForm::TYPE_SEARCHTOOLS): ?>

  <?php else: ?>

  <div id="jsolr-search-tools-list" class="navbar navbar-static<?php if (!$this->showSearchToolsOnStart()): ?> jsolr-hidden<?php endif ?>">
    <div class="navbar-inner">
      <div class="container">
        <ul class="nav">
          <?php foreach($form->getFieldsets() as $fieldset ) : ?>
            <?php if ($fieldset->name != 'main'): ?>
                <?php foreach ($this->get('Form')->getFieldset($fieldset->name) as $field): ?>
                <li>
                    <a href="#"><?php echo $field->getLabel() ?><strong class="caret"></strong></a>
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