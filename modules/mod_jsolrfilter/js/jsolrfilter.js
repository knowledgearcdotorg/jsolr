var selectedRange = null;

window.addEvent("domready", function() {
	selectedRange = $$(".jsolr-range-selected").getProperty("id");

	$$("#jSolrCustom a").addEvent("click", function(e) {
		var event = new Event(e);
		event.stop();

		$("jSolrDateRange").addClass("jsolr-show");
		$("jSolrDateRange").removeClass("jsolr-hide");
		$$("#jSolrCustom a").addClass("jsolr-custom-range-selected");
		$(""+selectedRange).removeClass("jsolr-range-selected");
	});
	
	$$("#jSolrSearchDates .jsolr-range-selected").addEvent("click", function (e) {
		var event = new Event(e);
		event.stop();
		
		$("jSolrDateRange").removeClass("jsolr-show");
		$("jSolrDateRange").addClass("jsolr-hide");
		$(""+selectedRange).addClass("jsolr-range-selected");
		$$("#jSolrCustom a").removeClass("jsolr-custom-range-selected");
	});
});