window.addEvent("domready", function() {
	$$("#jSolrSearchManagementButtons button").addEvent("click", function(e) {
		var event = new Event(e).stop();

		var cancel = 'jsolrSearchCancel';
		
		jsolrsearch.request = new Request.JSON({
			url: jsolrsearch.options[event.target.id].url,
			method: "get",
			onRequest: function() {
				$("jsolrSearchManagementMessage").set('text', jsolrsearch.language.pleaseWait);
				
				var button = document.createElement('button');
								
				button.setAttribute('name', cancel);
				button.setAttribute('id', cancel);
				button.set('text', 'Cancel');
				button.addEvent("click", function() {
					$("jsolrSearchManagementMessage").set('text', jsolrsearch.language.cancelling);
					jsolrsearch.request.cancel();
				});
				
				$("jSolrSearchManagementButtons").appendChild(button);
				
				$$("#jSolrSearchManagementButtons button").each(function(item, index) {
					if (item.id != cancel) {
						item.set("disabled", "disabled");
					}
				});
			},
			onSuccess: function(response) {
	        	$("jsolrSearchManagementMessage").set('text', response.message);
	    	},
			onFailure: function() {
				$("jsolrSearchManagementMessage").set('text', jsolrsearch.language.failed);
	    	},
	    	onComplete: function() {
				$$("#jSolrSearchManagementButtons button").each(function(item, index) {
					if (item.id != cancel) {
						item.erase("disabled");
					}
				});
				
				var button = document.getElementById(cancel);
				$("jSolrSearchManagementButtons").removeChild(button);
	    	},
	    	onCancel: function() {
	    		$("jsolrSearchManagementMessage").set('text', jsolrsearch.language.cancelled);
	    		
				$$("#jSolrSearchManagementButtons button").each(function(item, index) {
					if (item.id != cancel) {
						item.erase("disabled");
					}
				});
				
				var button = document.getElementById(cancel);
				$("jSolrSearchManagementButtons").removeChild(button);	    		
	    	}
		}).send();
	});
});