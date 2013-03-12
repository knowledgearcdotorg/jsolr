jQuery(document).ready(function($) {
	$('.jrange-custom a').click(function(){
		$(this).next().toggle();
		return false;
	});

	$('a.jrange-option').click(function(){
		var elem = $(this);

		$('#' + elem.attr('data-name') + '_value').val(elem.attr('data-value'));
		$('#' + elem.attr('data-name') + '_from').val('');
		$('#' + elem.attr('data-name') + '_to').val('');

		$('.jsolr-search-result-form').submit();

		return false;
	});
});