<?php
/**
 * A layout override for the searchtool form field.
 */
$encoded = htmlspecialchars($displayData['value'], ENT_COMPAT, 'UTF-8');
?>

<input
    type="hidden"
    name="<?php echo $displayData['name']; ?>"
    id="<?php echo $displayData['id']; ?>"
    value="<?php echo $encoded; ?>"/>

<a
    class="dropdown-toggle"
    id="<?php echo $displayData['fieldname']; ?>"
    data-toggle="dropdown"
    href="#">
    <?php echo JText::_($displayData['selected']['label']); ?>
    <b class="caret"></b>
</a>

<ul
    class="dropdown-menu"
    role="menu"
    aria-labelledby="<?php echo $displayData['name']; ?>">

    <?php foreach ($displayData['options'] as $option) : ?>
    <li
        role="presentation"
        class="<?php echo $option['selected'] ? 'active' : ''; ?>"
        data-value="<?php echo $option['value']; ?>">
        <a
            role="menuitem"
            tabindex="-1"
            href="<?php echo $option['uri']; ?>"><?php echo JText::_(trim($option['label'])); ?>
        </a>
    </li>
    <?php endforeach; ?>
</ul>
