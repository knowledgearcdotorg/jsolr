
<?php JHTML::_('behavior.formvalidation') ?>
<?php $form = $this->get('Form') ?>

<form action="<?php echo JRoute::_("index.php?option=com_jsolrsearch&task=search"); ?>" method="post" name="adminForm" class="form-validate jsolr-search-result-form" id="jsolr-search-result-form" data-baseurl="<?php echo JRoute::_("index.php?option=com_jsolrsearch&view=basic"); ?>">
  <input type="hidden" name="o" value="<?php echo $this->get('CurrentPlugin') ?>" />
  <fieldset class="word">
    <?php foreach($form->getFieldsets() as $fieldset ) : ?>
      <?php if ($fieldset->name == 'main'): ?>
        <?php foreach ($this->get('Form')->getFieldset($fieldset->name) as $field): ?>
          <span><?php echo $field->getInput() ?></span>
        <?php endforeach;?>
      <?php endif ?>
    <?php endforeach;?>
  </fieldset>

  <div class="jsolr-clear"></div>

<?php $plugin = $this->get('CurrentPlugin') ?>
<?php if (true): ?>

  <div class="btn-group jsolr-plugins-list <?php if (is_null($this->items) && empty($plugin)): ?> jsolr-hidden<?php endif ?>">
    <?php $i = 0; $max = $this->getComponentsLimit(); $components = $this->get('ComponentsList') ?>
    <?php $components = array_merge(array(array('plugin' => '', 'name' => JText::_('Everything'))), $components) ?>
    <?php $count = count($components) ?>

    <?php for ($i = 0; $i < $count; ++$i): ?>
      <?php if ($i == $max + 1): ?>
        <ul class="nav nav-more pull-left">
          <li>
            <a href="#"><?php echo JText::_("COM_JSOLRSEARCH_COMPONENTS_MORE") ?><span class="more"></span></a>

            <ul class="more-list">
              <?php for(; $i < $count; ++$i): ?>
                <li><?php echo JHTML::link($this->updateUri(array('o' => $components[$i]['plugin'])), $components[$i]['name'], array('data-category' => $components[$i]['plugin'], 'class' => 'btn pull-left' . ($components[$i]['plugin'] == $this->get('CurrentPlugin') ? ' jsolr-plugins-selected' : ' jsolr-plugins'))) ?></li>
              <?php endfor ?>
            </ul>
          </li>
        </ul>

        <?php break?>
      <?php endif ?>
      <?php echo JHTML::link($this->updateUri(array('o' => $components[$i]['plugin'])), $components[$i]['name'], array('data-category' => $components[$i]['plugin'], 'class' => 'btn pull-left' . ($components[$i]['plugin'] == $this->get('CurrentPlugin') ? ' jsolr-plugins-selected' : ' jsolr-plugins'))) ?>
    <?php endfor ?>

    <?php if ($this->showSearchToolsButton()): ?>
      <?php echo JHTML::link('#', JText::_("Search Tools"), array('id' => 'jsolr-search-tools', 'class' => 'btn pull-left')) ?>
    <?php endif ?>
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
                    <a href="#"><span class="jsolr-current" data-all="<?php echo htmlspecialchars($field->getLabel()) ?>"><?php echo $field->getValueText() ?></span><strong class="caret"></strong></a>
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