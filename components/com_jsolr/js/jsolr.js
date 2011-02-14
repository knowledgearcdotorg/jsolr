window.addEvent("domready", function() {
	$$("button#testButton").addEvent("click", function(e) {
		var event = new Event(e).stop();
		
		var url = adminOptions.testURL;

		var a = new Ajax(url, {
			method: "get",
			onRequest: function() {
				$("testMessage").setText(adminOptions.pleaseWait);
			},
			onComplete: function(response) {
	        	var r = Json.evaluate(response);

	        	$("testMessage").setText(r.message);
	    	},    		
			onFailure: function() {
	    		
	    	}
		}).request();
	});
	
	$$("button#indexButton").addEvent("click", function(e) {
		var event = new Event(e).stop();
		
		var url = adminOptions.indexURL;

		var a = new Ajax(url, {
			method: "get",
			onRequest: function() {
				$("indexMessage").setText(adminOptions.pleaseWait);
			},
			onComplete: function(response) {
	        	var r = Json.evaluate(response);

	        	$("indexMessage").setText(r.message);
	    	},    		
			onFailure: function() {
	    		
	    	}
		}).request();
	});
	
	$$("button#purgeButton").addEvent("click", function(e) {
		var event = new Event(e).stop();
		
		var url = adminOptions.purgeURL;

		var a = new Ajax(url, {
			method: "get",
			onRequest: function() {
				$("purgeMessage").setText(adminOptions.pleaseWait);
			},
			onComplete: function(response) {
	        	var r = Json.evaluate(response);

	        	$("purgeMessage").setText(r.message);
	    	},    		
			onFailure: function() {
	    		
	    	}
		}).request();
	});
});