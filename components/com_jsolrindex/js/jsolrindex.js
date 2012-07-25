window.addEvent("domready", function() {
	$$("#jSolrIndexManagementButtons button").addEvent("click", function(e) {
		var event = new Event(e).stop();

		var cancel = 'jSolrIndexCancel';
		
		jsolrindex.request = new Request.JSON({
			url: jsolrindex.options[event.target.id].url,
			method: "get",
			onRequest: function() {
				$("jsolrIndexManagementMessage").set('text', jsolrindex.language.pleaseWait);
				
				var button = document.createElement('button');
								
				button.setAttribute('name', cancel);
				button.setAttribute('id', cancel);
				button.set('text', 'Cancel');
				button.addEvent("click", function() {
					$("jsolrIndexManagementMessage").set('text', jsolrindex.language.cancelling);
					jsolrindex.request.cancel();
				});
				
				$("jSolrIndexManagementButtons").appendChild(button);
				
				$$("#jSolrIndexManagementButtons button").each(function(item, index) {
					if (item.id != cancel) {
						item.set("disabled", "disabled");
					}
				});
			},
			onSuccess: function(response) {
	        	$("jsolrIndexManagementMessage").set('text', response.message);
	    	},
			onFailure: function() {
				$("jsolrIndexManagementMessage").set('text', jsolrindex.language.failed);
	    	},
	    	onComplete: function() {
				$$("#jSolrIndexManagementButtons button").each(function(item, index) {
					if (item.id != cancel) {
						item.erase("disabled");
					}
				});
				
				var button = document.getElementById(cancel);
				$("jSolrIndexManagementButtons").removeChild(button);
	    	},
	    	onCancel: function() {
	    		$("jsolrIndexManagementMessage").set('text', jsolrindex.language.cancelled);
	    		
				$$("#jSolrIndexManagementButtons button").each(function(item, index) {
					if (item.id != cancel) {
						item.erase("disabled");
					}
				});
				
				var button = document.getElementById(cancel);
				$("jSolrIndexManagementButtons").removeChild(button);	    		
	    	}
		}).send();
	});

	$$("button#jsolrIndexTestTika").addEvent("click", function(e) {
		var event = new Event(e).stop();
		
		var url = adminOptions.testTikaURL;

		var request = new Request.JSON({
			url: url,
			method: "get",
			onRequest: function() {
				$("jsolrIndexAttachmentIndexingMessage").set('text', adminOptions.pleaseWait);
			},
			onSuccess: function(response) {
	        	$("jsolrIndexAttachmentIndexingMessage").set('text', response.message);
	    	},    		
			onFailure: function() {
				$("jsolrIndexAttachmentIndexingMessage").set('text', adminOptions.failed);
	    	}
		}).send();
	});
});