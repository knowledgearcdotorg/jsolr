(function($) {
	$(document).ready(function() {
	    var value = $("#qdr").val();
	    
	    if (value != null) {
	    	var pattern = /^min:[0-9]{4}-[0-9]{2}-[0-9]{2},max:[0-9]{4}-[0-9]{2}-[0-9]{2}$/;
	    	
	    	if (pattern.test(value)) {
	    		var parts = value.split(',');
	    		
	    		if (parts.length == 2) {
	    			var min = parts[0].split(':');
	    			var max = parts[1].split(':');
	
	    			if (min.length == 2) {
	    				$("#custom-dates-form").children().find("#qdr_min").val(min[1]);
	    			}
	    			
	    			if (max.length == 2) {
	    				$("#custom-dates-form").children().find("#qdr_max").val(max[1]);
	    			}

	    			$("#custom-dates").addClass("show");
	    		}	
	    	}
	    }
	    
	    $("#calendar-picker").on('click', function(e) {
	    	e.preventDefault();
	    	
	    	// if custom dates form is already open don't change anything.
	    	if ($("#custom-dates").hasClass("show") == false) {
		    	$("#custom-dates").addClass("show");		    	
		    	$('div.jsolr-searchtool ul').removeClass('open');

		    	if ($(this).parent("li").data("value") != $("#qdr-selected").data('original')) {
		    		$("#qdr-selected").html($(this).html());
		    	}
	    	}
	    });
		
		$("#custom-dates-form").on('submit', function(e) {			
			e.preventDefault();
			var href = $(this).attr('action');
			var qs = "";

			if (value = $(this).children().find("#qdr_min").val()) {
				qs += "min:" + value;

				if (value = $(this).children().find("#qdr_max").val()) {
					qs += ",max:" + value;
					console.log(qs);
					qs = encodeURIComponent(qs);
					
					if (href.indexOf("?") >= 0) {
						href+="&qdr="+qs;
					} else {
						href+="?qdr="+qs;
					}
					
					window.location = href.toString();
				}
			}
		});
		
		$("#custom-dates-cancel").on('click', function(e) {
			e.preventDefault();
	    	
			var pattern = /^min:[0-9]{4}-[0-9]{2}-[0-9]{2},max:[0-9]{4}-[0-9]{2}-[0-9]{2}$/;
			
			// if custom is current selected date, don't change text.
	    	if (!pattern.test($("#qdr-selected").data('original'))) {
				$("#qdr-selected").html($('div.jsolr-searchtool ul li.active a').text());
	    	}

	    	$(this).parents("#custom-dates").removeClass('show');
		});
	});
})(jQuery);