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
});

var jsolrsearch = {
	results: null,
	pagination: null,
	form: null,
	baseUrl: '/index.php/component/jsolrsearch/basic',

	init: function() {
		this.results = jQuery('.jsolr-results');
		this.pagination = jQuery('.jsolr-pagination');
		this.form = jQuery('.jsolr-search-result-form');
	},

	update: function() {
		var url = this.createUrl();
		this.sendRequest(url);

		history.pushState({'url': url}, document.title, url);
	},

	createUrl: function() {
		var attrs = [];

		$.each(this.form.find('input'), function(key, elem){
			elem = $(elem);

			if (elem.attr('name') != 'undefined' && elem.val() != '') {
				attrs.push(elem.attr('name') + '=' + elem.val());
			}
		});

		return this.baseUrl + '?' + attrs.join('&');
	},

    sendRequest: function(url)
    {
    	this.results.fadeOut();

    	jQuery.getJSON(url + '&ajax=1', function(response){
    		jsolrsearch.updateTemplate(response.results, response.pagination);
    	});
    },

    updateTemplate: function(results, pagination)
    {
    	this.results.html(results);
    	this.pagination.html(pagination);
    	this.results.fadeIn();
    }
}