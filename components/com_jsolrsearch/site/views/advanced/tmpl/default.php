<?php
defined('_JEXEC') or die('Restricted access');
?>
<form action="<?php echo JRoute::_( 'index.php?option=com_jsolrsearch' ); ?>" method="post" id="josForm" name="josForm" class="form-validate">

	<?php if ( $this->params->def( 'show_page_title', 1 ) ) : ?>
	<div class="componentheading<?php echo $this->escape($this->params->get('pageclass_sfx')); ?>"><?php echo $this->escape($this->params->get('page_title')); ?></div>
	<?php endif; ?>

	<table cellpadding="0" cellspacing="0" border="0" width="100%" class="contentpane">
		<tr>
			<td width="30%" height="40">
				<label id="aqLbl" for="aq">
					<?php echo JText::_("COM_JSOLRSEARCH_LABEL_AQ"); ?>:
				</label>
			</td>
		  	<td>
		  		<input type="text" name="aq" id="aq" size="40" value="<?php echo $this->get("AndQuery"); ?>" class="inputbox required" maxlength="50" />
		  	</td>
		</tr>
		<tr>
			<td height="40">
				<label id="eqLbl" for="eq">
					<?php echo JText::_("COM_JSOLRSEARCH_LABEL_EQ"); ?>:
				</label>
			</td>
			<td>
				<input type="text" id="eq" name="eq" size="40" value="<?php echo $this->get("ExactQuery"); ?>" class="inputbox required validate-email" maxlength="100"/>
			</td>
		</tr>
		<tr>
			<td height="40">
				<label id="oqLbl" for="oq">
					<?php echo JText::_("COM_JSOLRSEARCH_LABEL_OQ"); ?>:
				</label>
			</td>
		  	<td>
		  		<div>
					<input class="inputbox" type="text" id="oq0" name="oq0" size="10" value=""/>
		  			<span><?php echo JText::_("COM_JSOLRSEARCH_OR"); ?></span>
		  			<input class="inputbox" type="text" id="oq1" name="oq1" size="10" value=""/>
					<span><?php echo JText::_("COM_JSOLRSEARCH_OR"); ?></span>
					<input class="inputbox" type="text" id="oq2" name="oq2" size="10" value=""/>
				</div>
		  	</td>
		</tr>
		<tr>
			<td height="40">
				<label id="nq" for="nq">
					<?php echo JText::_("COM_JSOLRSEARCH_LABEL_NQ"); ?>:
				</label>
			</td>
			<td>
				<input class="inputbox" type="text" id="nq" name="nq" size="40" value="<?php echo $this->get("NotQuery"); ?>"/>
			</td>
		</tr>
		<tr>
			<td height="40">
				<label id="oLbl" for="o">
					<?php echo JText::_("COM_JSOLRSEARCH_LABEL_O"); ?>:
				</label>
			</td>
			<td>
				<?php
				echo JHTML::_("select.genericlist", $this->get("FilterOptions"), "o", "class=\"inputbox\"", "value", "text", $this->get("FilterOption"), "o");
				?>
			</td>
		</tr>
		<tr>
			<td height="40">
				<label id="lrLbl">
					<?php echo JText::_("COM_JSOLRSEARCH_LABEL_LR"); ?>:
				</label>
			</td>
			<td>
			<?php
			echo JHTML::_("select.genericlist", $this->get("Languages"), "lr", "class=\"inputbox\"", "value", "text", $this->get("Language"), "lr");
			?>
			</td>
		</tr>
		<tr>
			<td height="40">
				<label id="qdrLbl" for="qdr">				
					<?php echo JText::_("COM_JSOLRSEARCH_LABEL_QDR"); ?>:
				</label>
			</td>
			<td>
				<?php 
				echo JHTML::_("select.genericlist", $this->get("DateRanges"), "qdr", "class=\"inputbox\"", "value", "text", $this->get("DateRange"), "qdr");
				?>
			</td>
		</tr>
	</table>

	<button class="button validate" type="submit"><?php echo JText::_("COM_JSOLRSEARCH_BUTTON_ADVANCED_SEARCH_SUBMIT"); ?></button>
	<input type="hidden" name="task" value="advanced" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>