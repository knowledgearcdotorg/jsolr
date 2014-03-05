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
		$('.jsolr-searchtool').on('click', function(e){
			e.stopPropagation();
			$('.jsolr-searchtool > ul').addClass('open');
		});

		$('body').on('click', function(){
			$('.jsolr-searchtool > ul').removeClass('open');
		});
	});
})(jQuery);