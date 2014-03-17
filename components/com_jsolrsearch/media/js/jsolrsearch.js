window.addEvent("domready", function() {
    var value = $("qdr").getAttribute('value');
    
    if (value != null) {
    	if (value.test("^min:[0-9]{4}-[0-9]{2}-[0-9]{2},max:[0-9]{4}-[0-9]{2}-[0-9]{2}$")) {
    		var parts = value.split(',');
    		
    		if (parts.length == 2) {
    			var min = parts[0].split(':');
    			var max = parts[1].split(':');

    			if (min.length == 2) {
    				$("custom-dates-form").getChildren("#qdr_min").setProperty("value", min[1]);
    			}
    			
    			if (max.length == 2) {
    				$("custom-dates-form").getChildren("#qdr_max").setProperty("value", max[1]);
    			}
    			
    			$("custom-dates").addClass("show");
    		}	
    	}
    }
    
    $("calendar-picker").addEvent('click', function(e) {
    	e.stop();
    	$("custom-dates").addClass("show");
    	$("qdr-selected").set('html', this.get('html'));
    	// force the closure of the menu using an existing event.
    	$$('div.jsolr-searchtool').fireEvent('click');
    });
	
	$("custom-dates-form").addEvent('submit', function(e) {
		e.stop();
		var href = new URI(this.getAttribute('action'));
		
		var qs = "";
		
		if (value = this.getChildren("#qdr_min").getProperty("value").toString().stripScripts()) {
			qs += "min:" + value;
			
			if (value = this.getChildren("#qdr_max").getProperty("value").toString().stripScripts()) {
				qs += ",max:" + value;
				
				href.setData('qdr', qs);

				window.location = href.toString();
			}
		}
	});
	
	$("custom-dates-cancel").addEvent('click', function(e) {
		e.stop();
		$("qdr-selected").set('html', $$('div.jsolr-searchtool ul li.active a').get('html'));
		this.getParent("#custom-dates").removeClass('show');
	});
});