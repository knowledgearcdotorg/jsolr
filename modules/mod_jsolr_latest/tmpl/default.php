<?php
/**
 * @copyright  Copyright (C) 2014 KnowledgeARC Ltd. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php foreach ($items as $item): ?>

    <h3><a href="<?php echo JRoute::_($item->link); ?>"><?php echo $item->title; ?></a></h3>

    <div><?php echo $item->author; ?></div>

<?php endforeach; ?>
