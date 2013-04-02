<ul>
	<?php foreach ($this->get('Form')->getAppliedFacetFilters() as $field): ?>
	<li><span class="jsolr-label"><?php echo $field['label'] ?></span></li>
	<?php endforeach ?>
</ul>