jQuery(document).ready(function($) {
	$('.jrange-custom a').click(function(){
		$(this).next().toggle();
		return false;
	});

	$('a.jrange-option').click(function(){
		var elem = $(this);

		if (elem.attr('data-value') == '') {
			$('#' + elem.attr('data-name') + '_value').val('');
		} else {
			$('#' + elem.attr('data-name') + '_value').val(elem.attr('data-value'));
		}
		
		$('#' + elem.attr('data-name') + '_from').val('');
		$('#' + elem.attr('data-name') + '_to').val('');

		$('.jsolr-search-result-form').submit();

		return false;
	});

	$('a.jrange-remove').click(function(){
		var elem = $(this);

		vals = $('#' + elem.attr('data-name') + '_value').val();

		vals = vals.split('|');

		var index = vals.indexOf(elem.attr('data-value'));

		if (index != -1) {
			delete vals[index];
		}

		$('#' + elem.attr('data-name') + '_value').val(vals.join('|'));

		$('.jsolr-search-result-form').submit();

		return false;
	});

	$('#jsolr_form_plugin_select').change(function(){
		window.location.href = '?plugin=' + $(this).val();
		return false;
	});
});
