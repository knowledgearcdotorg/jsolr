jQuery(document).ready(function(){
	jQuery.each(jQuery('.jsolr-autocompleter'), function(index, elem) {
		elem = jQuery(elem);
		var completer = new Autocompleter.Request.JSON(elem.attr('id'), elem.data('autocompleteurl'), {'postVar': 'q'});
	});
});