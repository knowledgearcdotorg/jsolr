<?php $form = JSolrSearchModelSearch::getFacetFilterForm() ?>
<?php if (!is_null($form)): ?>
<ul>
	<?php foreach ($form->getAppliedFacetFilters() as $field): ?>
	<?php if ($field['value'] == 'null' || empty($field['value'])) continue ?>
	<li>
		<span class="jsolr-label"><?php echo $field['label'] ?></span>
		<span class="jsolr-value"><?php echo $field['value'] ?></span>

		<?php echo JHTML::link($this->updateUri(array(), array($field['name'], 'ajax', @$_GET['plugin'])), '<img src="'. JURI::base().'/media/com_jsolrsearch/images/close.png" />', array('class'=>'jsolr-del' ,'date-name' => $field['name'])) ?>
	</li>
	<?php endforeach ?>
</ul>
<?php endif ?>
<div class="jsolr-clear"></div>