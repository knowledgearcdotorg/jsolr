<?php
defined('_JEXEC') or die('Restricted access');

$params = $this->state->get('params');
?>
<div class="edit item-page<?php echo $params->get('pageclass_sfx'); ?>">
	<?php if ($params->get('show_page_heading', 1)) : ?>
	<h1><?php echo $this->escape($params->get('page_heading')); ?></h1>
	<?php endif; ?>

	<form action="<?php echo JRoute::_('index.php?option=com_jsolrsearch&task=search'); ?>" method="post" id="josForm" name="josForm" class="form-validate jsolr-advanced-search">	
		<fieldset>
			<div class="formelm">
				<?php echo $this->form->getLabel('aq'); ?>
				<?php echo $this->form->getInput('aq'); ?>
			</div>

			<div class="formelm">
				<?php echo $this->form->getLabel('eq'); ?>
				<?php echo $this->form->getInput('eq'); ?>
			</div>
			
			<div class="formelm">
				<?php echo $this->form->getLabel('oq'); ?>
				<?php echo $this->form->getInput('oq'); ?>
			</div>

			<div class="formelm">
				<?php echo $this->form->getLabel('nq'); ?>
				<?php echo $this->form->getInput('nq'); ?>
			</div>
			
			<div class="formelm">
				<?php echo $this->form->getLabel('lr'); ?>
				<?php echo $this->form->getInput('lr'); ?>
			</div>
			
			<div class="formelm">
				<?php echo $this->form->getLabel('qdr'); ?>
				<?php echo $this->form->getInput('qdr'); ?>
			</div>

			<?php 
			if (JRequest::getWord("o")) :

				foreach ($this->form->getFieldsets(JRequest::getWord("o")) as $fieldset) :
                	if ($fieldset->label) :
                	?>
                	</fieldset>
                	
                	<fieldset>
                		<legend><?php echo JText::_($fieldset->label); ?></legend>
                	<?php
                	endif;

					foreach ($this->form->getFieldset($fieldset->name) as $field) :
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
				<a id="jsolr-submit-advanced" class="button validate" style="float:right;background:#DDDDDD;border:1px solid #D1D1D1;padding:5px 10px;cursor:pointer;"><?php echo JText::_("COM_JSOLRSEARCH_ADVANCED_SEARCH_BUTTON_SUBMIT"); ?></a>
			</div>
			
			<input type="hidden" name="task" value="advanced" />
			<input type="hidden" name="o" value="<?php echo JRequest::getWord("o"); ?>" />
			<?php echo JHTML::_( 'form.token' ); ?>
		</fieldset>
	</form>
</div>