<?php 
JHTML::_('behavior.formvalidation');
$form = $this->get('Form'); 
?>

<form action="<?php echo JRoute::_("index.php"); ?>" method="get" name="adminForm" class="form-validate jsolr-search-result-form" id="jsolr-search-result-form">
	<input type="hidden" name="option" value="com_jsolrsearch"/>
	<input type="hidden" name="task" value="search"/>
	
	<?php if (JFactory::getApplication()->input->get('o', null)) : ?>
	<input type="hidden" name="o" value="<?php echo JFactory::getApplication()->input->get('o'); ?>"/>
	<?php endif; ?>
	
  <fieldset class="word">
    <?php foreach($form->getFieldsets() as $fieldset ) : ?>
      <?php if ($fieldset->name == 'main'): ?>
        <?php foreach ($this->get('Form')->getFieldset($fieldset->name) as $field): ?>
          <span><?php echo $form->getInput($field->fieldname); ?></span>
        <?php endforeach;?>
      <?php endif ?>
    <?php endforeach;?>
        <input type="submit" value="<?php echo JText::_("COM_JSOLRSEARCH_BUTTON_SUBMIT"); ?>" class="btn btn-primary" />
  </fieldset>

  <div class="jsolr-clear"></div>

  <div class="btn-group jsolr-plugins-list <?php if (is_null($this->items) && !($this->getModel()->getState('query.o', null))) : ?> jsolr-hidden<?php endif; ?>">
    <?php $i = 0; $max = $this->getComponentsLimit(); $components = $this->get('Extensions'); ?>

    <?php for ($i = 0; $i < count($components); ++$i): ?>
      <?php if ($i == $max + 1): ?>
        <ul class="nav nav-more pull-left">
          <li>
            <a href="#"><?php echo JText::_("COM_JSOLRSEARCH_COMPONENTS_MORE") ?><span class="more"></span></a>

            <ul class="more-list">
              <?php for(; $i < count($components); ++$i): ?>
                <li><?php echo JHTML::link($components[$i]['uri'], $components[$i]['name'], array('data-category' => $components[$i]['plugin'], 'class' => 'btn pull-left' . ($components[$i]['plugin'] == JFactory::getApplication()->input->get('o')) ? ' jsolr-plugins-selected' : ' jsolr-plugins')); ?></li>
              <?php endfor ?>
            </ul>
          </li>
        </ul>
        <?php break?>
      <?php endif ?>

      <?php
      $class = ($components[$i]['plugin'] == JFactory::getApplication()->input->get('o')) ? ' jsolr-plugins-selected' : ' jsolr-plugins';
      echo JHTML::link($components[$i]['uri'], $components[$i]['name'], array('data-category'=>$components[$i]['plugin'], 'class'=>'btn pull-left'.$class)); 
      ?>
    <?php endfor ?>
  </div>

  <div class="jsolr-clear"></div>

  <?php if ($form->getType() != JSolrForm::TYPE_SEARCHTOOLS): ?>

  <?php else: ?>

		<?php foreach($form->getFieldsets() as $fieldset ) : ?>
			<?php if ($fieldset->name != 'main'): ?>
				<?php foreach ($this->get('Form')->getFieldset($fieldset->name) as $field): ?>
					<?php echo $form->getInput($field->name); ?>
				<?php endforeach;?>
			<?php endif ?>
		<?php endforeach;?>

  <?php endif ?>

  <?php echo JHTML::_('form.token'); ?>
</form>