<?php 
$form = JSolrSearchModelSearch::getFacetFilterForm(); 
?>

<?php if (!is_null($form)): ?>
<ul>
	<?php foreach ($form->getAppliedFacetFilters() as $field): ?>
	<?php if ($field['value'] == 'null' || empty($field['value'])) continue; ?>
	
	<?php
	$uri = clone $form->getFacetedURI();
	$uri->delVar($field['filter']);
	?>
	<li>
		<span class="jsolr-label"><?php echo $field['label'] ?></span>
		<span class="jsolr-value"><?php echo $field['value'] ?></span>

		<?php echo JHTML::link((string)$uri, '<img src="'. JURI::base().'/media/com_jsolrsearch/images/close.png" />'); ?>
	</li>
	<?php endforeach ?>
</ul>
<?php endif ?>
<div class="jsolr-clear"></div>