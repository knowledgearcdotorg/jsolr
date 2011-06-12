window.addEvent("domready", function() {	
	$$("button#testButton").addEvent("click", function(e) {
		var event = new Event(e).stop();
		
		var url = adminOptions.testURL;

		var a = new Ajax(url, {
			method: "get",
			onRequest: function() {
				
			},
			onComplete: function(response) {
	        	var r = Json.evaluate(response);

	        	$("testMessage").setText(r.message);
	    	},    		
			onFailure: function() {
	    		
	    	}
		}).request();
	});
});