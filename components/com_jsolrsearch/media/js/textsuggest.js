jsolrsearch.autocompleters = new Array();

jQuery(document).ready(function(){
	jQuery.each(jQuery('.jsolr-autocompleter'), function(index, elem) {
		elem = jQuery(elem);
		jsolrsearch.autocompleters[elem.attr('id')] = new Autocompleter.Request.JSON(elem.attr('id'), elem.data('autocompleteurl'), {'postVar': 'q'});
	});
});