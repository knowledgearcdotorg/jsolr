window.addEvent("domready", function() {
	$$("button.test-button").addEvent("click", function(e) {
		var event = new Event(e).stop();
		
		var url = adminOptions.testURL;

		var a = new Ajax(url, {
			method: "get",
			onRequest: function() {
				$(event.target.id+"Message").setText(adminOptions.pleaseWait);
			},
			onComplete: function(response) {				
	        	var r = Json.evaluate(response);
	        	$(event.target.id+"Message").setText(r.message);
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
	
	$$("input[name=extractor]").addEvent("change", function(e) {
		var event = new Event(e);
		switch (event.target.id) {
			case "extractorlocal":				
				$("localTika").removeClass("hide");
				$("remoteTika").addClass("hide");
				$("solrServer").addClass("hide");
				break;
				
			case "extractorremote":
				$("localTika").addClass("hide");
				$("remoteTika").removeClass("hide");
				$("solrServer").addClass("hide");
				break;
				
			case "extractorsolr":
				$("localTika").addClass("hide");
				$("remoteTika").addClass("hide");
				$("solrServer").removeClass("hide");
				break;
				
			default:
				
				break;
		}
	});
});