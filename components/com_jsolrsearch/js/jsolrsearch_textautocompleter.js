jQuery(document).ready(function(){
	var jsolrsearch_autocomplete_url = '/index.php/component/jsolrsearch/autocomplete';

	jQuery.each(jQuery('.jsolr-autocompleter'), function(index, elem) {
		elem = jQuery(elem);

		var completer = new Autocompleter.Request.JSON(elem.attr('id'), jsolrsearch_autocomplete_url + '?fields=' + elem.attr('data-fields') + '&showFacet=' + elem.attr('data-showFacet'), {'postVar': 'q'});
	});
});