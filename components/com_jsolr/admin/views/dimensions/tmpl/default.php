<?php
/**
 * List dimensions.
 *
 * @package    JSolr
 * @copyright  Copyright (C) 2012-2017 KnowledgeArc Ltd. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', 'select');
JHtml::_('searchtools.form');

$user      = JFactory::getUser();
$userId    = $user->get('id');
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$saveOrder = $listOrder == 'a.ordering';

if ($saveOrder) {
    $saveOrderingUrl = 'index.php?option=com_jsolr&task=dimensions.saveOrderAjax&tmpl=component';
    JHtml::_('sortablelist.sortable', 'dimensions', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
}
?>
<form action="<?php echo JRoute::_('index.php?option=com_jsolr&view=dimensions'); ?>" method="post" name="adminForm" id="adminForm">
    <?php if (!empty($this->sidebar)) : ?>
    <div id="j-sidebar-container" class="span2">
        <?php echo $this->sidebar; ?>
    </div>
    <div id="j-main-container" class="span10">
    <?php else : ?>
    <div id="j-main-container">
    <?php endif;?>

        <div class="js-stools clearfix">
            <div class="clearfix">
                <div class="js-stools-container-list hidden-phone hidden-tablet shown">
                    <?php echo JLayoutHelper::render('joomla.searchtools.default.filters', array('view'=>$this)); ?>
                </div>
            </div>
        </div>

        <?php if (empty($this->items)) : ?>
        <div class="alert alert-no-items">
            <?php echo JText::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
        </div>
        <?php else : ?>
            <table class="table table-striped" id="dimensions">
                <thead>
                    <tr>
                        <th width="1%" class="nowrap center hidden-phone">&nbsp;
                        </th>
                        <th width="1%" class="nowrap center">
                            <?php echo JHtml::_('grid.checkall'); ?>
                        </th>
                        <th width="1%" style="min-width:55px" class="nowrap center">
                            <?php echo JText::_('JSTATUS'); ?>
                        </th>
                        <th class="nowrap">
                            <?php echo JText::_('JGLOBAL_TITLE'); ?>
                        </th>
                        <th width="10%" class="nowrap hidden-phone">
                            <?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_ACCESS', 'access_level', $listDirn, $listOrder); ?>
                        </th>
                        <?php if ($assoc) : ?>
                        <?php endif;?>
                        <th width="1%" class="nowrap hidden-phone">
                            <?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
                        </th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $n = count($this->items);

                foreach ($this->items as $i => $item) :
                    $canCreate  = $user->authorise('core.create');
                    $canEdit    = $user->authorise('core.edit');
                    $canCheckin = $user->authorise('core.manage', 'com_checkin') || $item->checked_out == $userId || $item->checked_out == 0;
                    $canEditOwn = $user->authorise('core.edit.own') && $item->created_by == $userId;
                    $canChange  = $user->authorise('core.edit.state') && $canCheckin;
                    ?>
                    <tr class="row<?php echo $i % 2; ?>">
                        <td class="order nowrap center hidden-phone">
                            <?php
                            $iconClass = '';

                            if (!$canChange) {
                                $iconClass = ' inactive';
                            } elseif (!$saveOrder) {
                                $iconClass = ' inactive tip-top hasTooltip" title="' . JHtml::tooltipText('JORDERINGDISABLED');
                            }
                            ?>
                            <span class="sortable-handler<?php echo $iconClass; ?>">
                                <span class="icon-menu"></span>
                            </span>
                            <?php if ($canChange && $saveOrder) : ?>
                                <input type="text" style="display:none" name="order[]" size="5"
                                    value="<?php echo $item->ordering; ?>" class="width-20 text-area-order " />
                            <?php endif; ?>
                        </td>
                        <td class="center">
                            <?php echo JHtml::_('grid.id', $i, $item->id); ?>
                        </td>
                        <td class="center">
                            <div class="btn-group">
                                <?php echo JHtml::_('jgrid.published', $item->published, $i, 'dimensions.', $canChange, 'cb', $item->publish_up, $item->publish_down); ?>
                                <?php
                                // Create dropdown items and render the dropdown list.
                                if ($canChange) {
                                    JHtml::_('actionsdropdown.' . ((int) $item->published === 2 ? 'un' : '') . 'archive', 'cb' . $i, 'dimensions');
                                    JHtml::_('actionsdropdown.' . ((int) $item->published === -2 ? 'un' : '') . 'trash', 'cb' . $i, 'dimensions');
                                    echo JHtml::_('actionsdropdown.render', $this->escape($item->name));
                                }
                                ?>
                            </div>
                        </td>
                        <td class="nowrap has-context">
                            <div class="pull-left">
                                <?php if ($item->checked_out) : ?>
                                    <?php echo JHtml::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'dimensions.', $canCheckin); ?>
                                <?php endif; ?>
                                <?php if ($canEdit || $canEditOwn) : ?>
                                    <a href="<?php echo JRoute::_('index.php?option=com_jsolr&task=dimension.edit&id=' . (int) $item->id); ?>"><?php echo $this->escape($item->name); ?></a>
                                <?php else : ?>
                                    <?php echo $this->escape($item->name); ?>
                                <?php endif; ?>
                                <span class="small">
                                    <?php echo JText::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($item->alias)); ?>
                                </span>
                            </div>
                        </td>
                        <td class="small hidden-phone">
                            <?php echo $item->access_level; ?>
                        </td>
                        <td class="hidden-phone">
                            <?php echo $item->id; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif;?>
        <input type="hidden" name="task" value="" />
        <input type="hidden" name="boxchecked" value="0" />
        <?php echo JHtml::_('form.token'); ?>
    </div>
</form>