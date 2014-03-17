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
window.addEvent('domready', function() {
	$$('div.jsolr-searchtool').addEvent('click', function(e) {
		event = new Event(e);
		event.stopPropagation();
		
		var isOpen = this.getChildren('ul.dropdown-menu').hasClass("open");

		this.getChildren('ul.dropdown-menu').removeClass("open");
		
		if (isOpen == "false") {
			this.getChildren('ul.dropdown-menu').addClass("open");
		}		
	});

	$(document.body).addEvent('click', function(e){
		event = new Event(e);
		$$('div.jsolr-searchtool ul').removeClass('open');
	});
});