<?php
if (!count($this->items)) {
   echo '<span>' . JText::_("COM_JSOLRSEARCH_NO_RESULTS") . '</span>';
}
?>

<div id="jsolr_total">
<?php echo JText::sprintf('COM_JSOLRSEARCH_TOTAL_RESULTS', $this->state->get('total'), $this->state->get('qTime')); ?>
</div>

<?php 
if (JFactory::getApplication()->getUserState('com_jsolrsearch.suggestions')) :
foreach ($this->get("SuggestionQueryURIs") as $item) :
?>
<div>Did you mean <a href="<?php echo JArrayHelper::getValue($item, 'uri'); ?>"><?php echo JArrayHelper::getValue($item, 'title'); ?></a></div>
<?php
endforeach; 
endif;
?>

<?php
foreach ($this->items as $item) :
       echo $this->loadResultTemplate($item);
endforeach;
?>