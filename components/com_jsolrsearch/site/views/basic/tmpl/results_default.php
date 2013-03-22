<?php
if (!count($this->items)) {
   echo '<span>' . JText::_(COM_JSOLRSEARCH_NO_RESULTS) . '</span>';
}
foreach ($this->items as $item) :
       echo $this->loadResultTemplate($item);
endforeach;
?>