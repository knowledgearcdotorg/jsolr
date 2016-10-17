<?php
/**
 * @copyright  Copyright (C) 2014-2016 KnowledgeArc Ltd. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php foreach ($items as $item): ?>
    <h3><a href="<?php echo JRoute::_($item->link); ?>"><?php echo $item->{\JSolr\Helper::localize('title_txt_*')}; ?></a></h3>
<?php endforeach; ?>
