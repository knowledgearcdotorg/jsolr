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
				<label id="andPhraseLbl" for="andPhrase">
					<?php echo JText::_( 'All of these words' ); ?>:
				</label>
			</td>
		  	<td>
		  		<input type="text" name="andPhrase" id="andPhrase" size="40" value="" class="inputbox required" maxlength="50" />
		  	</td>
		</tr>
		<tr>
			<td height="40">
				<label id="exactPhraseLbl" for="exactPhrase">
					<?php echo JText::_( 'This exact phrase' ); ?>:
				</label>
			</td>
			<td>
				<input type="text" id="exactPhrase" name="exactPhrase" size="40" value="" class="inputbox required validate-email" maxlength="100" />
			</td>
		</tr>
		<tr>
			<td height="40">
				<label id="orPhraseLbl" for="orPhrase">
					<?php echo JText::_( 'One or more of these words' ); ?>:
				</label>
			</td>
		  	<td>
		  		<div><input class="inputbox" type="text" id="orPhrase" name="orPhrase" size="10" value="" />
		  		or <input class="inputbox" type="text" id="orPhrase" name="orPhrase" size="10" value="" />
		  		or <input class="inputbox" type="text" id="orPhrase" name="orPhrase" size="10" value="" /></div>
		  	</td>
		</tr>
		<tr>
			<td height="40">
				<label id="notPhrase" for="notPhrase">
					<?php echo JText::_( "Don't show results containing" ); ?>:
				</label>
			</td>
			<td>
				<input class="inputbox" type="text" id="notPhrase" name="notPhrase" size="40" value="" />
			</td>
		</tr>
		<tr>
			<td height="40">
				<label id="optionsLbl">
					<?php echo JText::_( "Search in" ); ?>:
				</label>
			</td>
			<td>
				<div><label><input class="inputbox" type="checkbox" id="optionsEverything" name="options[]" size="40" value="" /><?php echo JText::_("Everything"); ?></label></div>
				<div><label><input class="inputbox" type="checkbox" id="optionsContent" name="options[]" size="40" value="" /><?php echo JText::_("Articles"); ?></label></div>
				<div><label><input class="inputbox" type="checkbox" id="optionsWebLink" name="options[]" size="40" value="" /><?php echo JText::_("Web Links"); ?></label></div>
			</td>
		</tr>
		<tr>
			<td height="40">
				<label id="langLbl">
					<?php echo JText::_( "Language" ); ?>:
				</label>
			</td>
			<td>
				<select id="lang" name="lang">
					<option value="en-GB">English - GB</option>
				</select>
			</td>
		</tr>
		<tr>
			<td height="40">
				<label id="dateLbl">
					<?php echo JText::_( "Date" ); ?>:
				</label>
			</td>
			<td>
				<select id="date" name="date">
					<option value="0">Anytime</option>
					<option value="1">Last 24 hours</option>
					<option value="2">Last week</option>
					<option value="3">Last month</option>
					<option value="4">Last year</option>
				</select>
			</td>
		</tr>
	</table>

	<button class="button validate" type="submit"><?php echo JText::_('Advanced Search'); ?></button>
	<input type="hidden" name="task" value="advanced" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>