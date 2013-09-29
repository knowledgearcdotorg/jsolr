/**
 * Legacy dropdown code for Joomla 2.5.
 * 
 * You will need to include this file in your template override if you require 
 * this functionality in your Joomla! 2.5 site but it is recommended you use 
 * Bootstrap for dropdown menus if you are running JSolr on a Joomla! 3.x site.
 * 
 * If you are using Joomla! 2.5 you will need to configure your JSolr Dropdown 
 * Form Field (or any field derived from the JSolr Dropdown) with the class 
 * "jsolr-dropdown". Alternatively, to use the Bootstrap dropdown, use 
 * "dropdown". 
 */ 
(function($) {	
	$(document).ready( function() {
		$('.jsolr-dropdown').on('click', function(e){
			e.stopPropagation();
			$(this).addClass('jsolr-dropdown-active');
		});
		$('.jsolr-dropdown').on('click', 'li', function(e){
			var option = $(this);
			if( option.parents('.jsolr-dropdown-active').length > 0 ) {
				e.stopPropagation();
				var dropdown = option.parents('.jsolr-dropdown');
				dropdown.find('li').removeClass('active');
				$('.jsolr-dropdown').removeClass('jsolr-dropdown-active');
				dropdown.find('.jsolr-dropdown > input[type="hidden"]').val(option.data('value'));
				dropdown.find('.jsolr-dropdown > .label').html(option.html());
				option.addClass('active');
			}
		});
		$('body').on('click', function(){
			$('.jsolr-dropdown').removeClass('jsolr-dropdown-active');
		});
	});
})(jQuery);