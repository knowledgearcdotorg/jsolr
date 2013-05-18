<?php
if (!count($this->items)) {
   echo '<span>' . JText::_("COM_JSOLRSEARCH_NO_RESULTS") . '</span>';
}
?>

<div id="jsolr_total">
<?php echo JText::sprintf('COM_JSOLRSEARCH_TOTAL_RESULTS', $this->state->get('total'), $this->state->get('qTime')); ?>
</div>

<?php
foreach ($this->items as $item) :
       echo $this->loadResultTemplate($item);
endforeach;
?>