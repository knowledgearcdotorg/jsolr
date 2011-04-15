<?php
defined('_JEXEC') or die('Restricted access');
?>
<form action="<?php echo JRoute::_( 'index.php?option=com_jsolrsearch' ); ?>" method="post" id="josForm" name="josForm" class="form-validate jsolr-advanced-search">

	<?php if ( $this->params->def( 'show_page_title', 1 ) ) : ?>
	<div class="componentheading<?php echo $this->escape($this->params->get('pageclass_sfx')); ?>"><?php echo JText::_($this->get("Title")); ?></div>
	<?php endif; ?>

	<div id="query"></div>

	<fieldset>

		<div class="field">
			<div class="label">
				<label id="aqLbl" for="aq">
					<?php echo JText::_("COM_JSOLRSEARCH_LABEL_AQ"); ?>:
				</label>
			</div>
		  	<div class="control">
		  		<input type="text" name="aq" id="aq" size="40" value="<?php echo $this->get("AndQuery"); ?>" class="inputbox jsolrquery" maxlength="50" />
		  	</div>
		</div>

		<div class="field">
			<div class="label">
				<label id="aqLbl" for="aq">
					<?php echo JText::_("COM_JSOLRSEARCH_LABEL_EQ"); ?>:
				</label>
			</div>
		  	<div class="control">
		  		<input type="text" id="eq" name="eq" size="40" value="<?php echo $this->get("ExactQuery"); ?>" class="inputbox jsolrquery" maxlength="100"/>
		  	</div>
		</div>

		<div class="field">
			<div class="label">
				<label id="aqLbl" for="aq">
					<?php echo JText::_("COM_JSOLRSEARCH_LABEL_OQ"); ?>:
				</label>
			</div>
		  	<div class="control">
				<input class="inputbox jsolrquery" type="text" id="oq0" name="oq0" size="10" value=""/>
	  			<span><?php echo JText::_("COM_JSOLRSEARCH_OR"); ?></span>
	  			<input class="inputbox jsolrquery" type="text" id="oq1" name="oq1" size="10" value=""/>
				<span><?php echo JText::_("COM_JSOLRSEARCH_OR"); ?></span>
				<input class="inputbox jsolrquery" type="text" id="oq2" name="oq2" size="10" value=""/>
		  	</div>
		</div>

		<div class="field">
			<div class="label">
				<label id="aqLbl" for="aq">
					<?php echo JText::_("COM_JSOLRSEARCH_LABEL_NQ"); ?>:
				</label>
			</div>
		  	<div class="control">
				<input class="inputbox jsolrquery" type="text" id="nq" name="nq" size="40" value="<?php echo $this->get("NotQuery"); ?>"/>
		  	</div>
		</div>

		<?php 
		foreach ($this->get("Form")->renderToArray() as $key=>$value) {
		?>
		<div class="field">
			<div class="label">
				<?php echo JText::_(JArrayHelper::getValue($value, 0)); ?>:
			</div>
			<div class="control">
				<?php echo JArrayHelper::getValue($value, 1); ?>
			</div>
		</div>
		<?php
		}
		?>
	
		<div class="field">
			<button class="button validate" type="submit"><?php echo JText::_("COM_JSOLRSEARCH_BUTTON_ADVANCED_SEARCH_SUBMIT"); ?></button>
		</div>
		
		<input type="hidden" name="task" value="advanced" />
		<input type="hidden" name="o" value="<?php echo JRequest::getWord("o"); ?>" />
		<?php echo JHTML::_( 'form.token' ); ?>
	</fieldset>
</form>