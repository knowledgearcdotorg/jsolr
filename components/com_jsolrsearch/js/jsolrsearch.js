jQuery(document).ready(function($) {
	$('.jrange-custom a').click(function(){
		$(this).next().toggle();
		return false;
	});

	$('.jsolr-search-result-form a.jrange-option').click(function(){
		var elem = $(this);

		if (elem.attr('data-value') == '') {
			$('#' + elem.attr('data-name') + '_value').val('');
		} else {
			$('#' + elem.attr('data-name') + '_value').val(elem.attr('data-value'));
		}
		
		$('#' + elem.attr('data-name') + '_from').val('');
		$('#' + elem.attr('data-name') + '_to').val('');
		elem.parent().parent().parent().find('span.jsolr-current').html(elem.html());

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

	if ($('.datepicker').datepicker != undefined) {
		$('.datepicker').datepicker({
			autoclose: true
		});
	}

	if ($('.dropdown-toggle').dropdown != undefined) {
		$('.dropdown-toggle').dropdown();
	}

	$('.jsolr-search-result-form .dropdown input, .jsolr-search-result-form .dropdown label, .jsolr-search-result-form ul span').click(function(e) {
	  e.stopPropagation();
	});

	$('#jsolr-search-tools').click(function(){
		$('#jsolr-search-tools-list').toggle();
		return false;
	});

	$('#jsolr-search-submit').click(function(){
		$('.jsolr-search-result-form').submit();
		return false;
	});

	$('.jsolr-search-result-form a[href=#]').click(function(){
		return false;
	});

	jsolrsearch.init();

	$('.jsolr-search-result-form').submit(function(){
		if (jsolrsearch.results.length) {
			jsolrsearch.update();
			return false;
		}
	});

	$('.jsolr-module-filter a.jrange-option').click(function(e){
		var elem = $(e.currentTarget);
		var name = elem.attr('data-name');

		if (elem.attr('data-value') == '') {
			$('#' + name + '_value').val('');
		} else {
			$('#' + name + '_value').val(elem.attr('data-value'));
		}

		$('[data-name="' + name + '"]').removeClass('jrange-option-selected');
		elem.addClass('jrange-option-selected');
		
		$('#' + name + '_from').val('');
		$('#' + name + '_to').val('');

		jsolrsearch.update();
		return false;
	});

	$('.jsolr-module-filter [type=checkbox], .jsolr-module-filter select, .jsolr-module-filter [type=radio]').change(function(){
		jsolrsearch.update();
		return false;
	});
});

var jsolrsearch = {
	results: null,
	pagination: null,
	facetsSelected: null,
	form: null,

	init: function() {
		this.results = jQuery('.jsolr-results');
		this.pagination = jQuery('.jsolr-pagination');
		this.facetsSelected = jQuery('#jsolr-facet-filters-selected');
		this.form = jQuery('.jsolr-search-result-form, .jsolr-module-filter');

    	this.updateFacetFiltersEvents();
	},

	update: function(params) {
		var url = this.createUrl(params);
		this.sendRequest(url);

		if (typeof history.pushState === 'undefined') { // if broswer does not support history.pushState for example IE9-
			window.location = url;
		} else {
			history.pushState({'url': url}, document.title, url);
		}
	},

	createUrl: function() {
		var attrs = [];

		$.each(this.form.find('input, [type=checkbox], select'), function(key, elem){
			elem = $(elem);

			if ((elem.attr('type') == 'checkbox' || elem.attr('type') == 'radio' ) && !elem.is(':checked')) {
				return;
			}

			var name = elem.attr('name');

			if (name != undefined) {
				if (name.substr(0, 4) == 'com_') {
					var start	= name.indexOf("[");
					var end		= name.indexOf(']');

					name = name.substr(start + 1, end - start - 1) + name.substr(end + 1);
				}

				var val = elem.val();

				if (elem.is('select') && $.isArray(key)) {
					$.each(val, function(key, s){
						attrs.push(name + '=' + s);
					});
				} else {
					attrs.push(name + '=' + val);
				}
			}
		});

		return jsolrsearch_search_url + '?' + attrs.join('&');
	},

    sendRequest: function(url)
    {
    	this.results.fadeOut();

    	jQuery.getJSON(url + '&ajax=1', function(response){
    		jsolrsearch.updateTemplate(response);
    	});
    },

    updateTemplate: function(response)
    {
    	this.results.html(response.results);
    	this.pagination.html(response.pagination.replace(/\&amp;ajax\=1/ig, ''));
    	this.facetsSelected.html(response.facets_selected);
    	this.results.fadeIn();
    	this.updateFacetFiltersEvents();
    },

    updateFacetFiltersEvents: function()
    {
		this.facetsSelected.find('a').click(function(e){
			$(e.currentTarget).parent().addClass('to-delete');
			var name = $(e.currentTarget).attr('date-name').replace('[', '\\[').replace(']', '\\]');

			var el = jQuery('[name="' + name + '"]');

			if (el.size() > 0) {
				el.attr('selected', false);
				el.attr('checked', false);
				console.log(el);
				console.log(el[0]);
				jsolrsearch.update();
			} else { // link
				var selector = '[data-selector="' + name + '"]';

				elem = jQuery(selector);
				elem[0].click();
			}

    		return false;
    	});
    }
}
