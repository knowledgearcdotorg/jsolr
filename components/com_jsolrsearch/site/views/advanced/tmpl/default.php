<?php
defined('_JEXEC') or die('Restricted access');
//test
$params = $this->state->get('params');
?>
<div class="edit item-page<?php echo $params->get('pageclass_sfx'); ?>">
	<?php if ($params->get('show_page_heading', 1)) : ?>
	<h1><?php echo $this->escape($params->get('page_heading')); ?></h1>
	<?php endif; ?>

	<form action="<?php echo JRoute::_(JURI::base().'index.php?option=com_jsolrsearch&task=advanced'); ?>" method="post" id="josForm" name="josForm" class="form-validate jsolr-advanced-search">	
		<fieldset>
			
			<?php 
				/**
				 * Display all defined form fields
				 */
			?>
			<?php foreach($this->get('Form')->getFieldset() as $field ) : ?>
				<div class="formelm">
					<?php echo $this->get('Form')->getLabel($field->fieldname); ?>
					<?php echo $this->get('Form')->getInput($field->fieldname); ?>
				</div>
			<?php endforeach;?>
			

			<?php 
			if (JRequest::getWord("o")) :

				foreach ($this->get('Form')->getFieldsets(JRequest::getWord("o")) as $fieldset) :
                	if ($fieldset->label) :
                	?>
                	</fieldset>
                	
                	<fieldset>
                		<legend><?php echo JText::_($fieldset->label); ?></legend>
                	<?php
                	endif;

					foreach ($this->get('Form')->getFieldset($fieldset->name) as $field) :
					?>
					<div class="formelm">
						<?php echo $field->label; ?>
						<?php echo $field->input; ?>
					</div>
					<?php
					endforeach;
				endforeach;
			endif;
			?>
		
			<div class="field">
			  	<input id="jsolr-submit-advanced" class="button validate" type="submit" value="<?php echo JText::_("COM_JSOLRSEARCH_ADVANCED_SEARCH_BUTTON_SUBMIT"); ?>" />
			</div>
			
			<input type="hidden" name="task" value="advanced" />
			<input type="hidden" name="o" value="<?php echo JRequest::getWord("o"); ?>" />
			<?php echo JHTML::_( 'form.token' ); ?>
		</fieldset>
	</form>
</div>