<?php
/**
 * A layout override for the searchtool form field.
 */
$encoded = htmlspecialchars($displayData->value, ENT_COMPAT, 'UTF-8');
?>

<input
    type="hidden"
    name="<?php echo $displayData->name; ?>"
    id="<?php echo $displayData->id; ?>"
    value="<?php echo $encoded; ?>"/>

<a
    class="btn dropdown-toggle"
    id="<?php echo $displayData->fieldName; ?>"
    role="button"
    data-toggle="dropdown"
    data-target="#"
    data-original="<?php echo $displayData->value; ?>">
    <?php echo JText::_($displayData->label); ?>
    <b class="caret"></b>

    <ul
        class="dropdown-menu"
        role="menu"
        aria-labelledby="<?php echo $displayData->name; ?>"><?php echo implode($displayData->options); ?></ul>
</a>