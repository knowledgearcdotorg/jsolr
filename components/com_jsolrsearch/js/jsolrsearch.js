/*
window.addEvent("domready", function() {	
	var query = new Hash({
		build : function() {
			var q = "";
			
			q += this.buildAND() + " " + this.buildExact() + " " + this.buildOR() + " " + this.buildNOT();
			
			return q;
		},
		buildOR : function() {
			var q = "";
			
			var j = 0;

			for (var i = 0; i < 3; i++) {
				if ($("oq"+i).get("value") != "") {
					if (j > 0) {
						q += " OR ";
					}
					
					if ($("oq"+i).get("value").split(" ").length > 1) {
						q += "\"";
						
						$("oq"+i).get("value").split(" ").each(function(item, key) {
							q += item.trim();
						});
						
						q += "\"";
					} else {
						q += $("oq"+i).get("value");
					}
					
					j++;
				}
			}
			
			return q;
		},
		buildAND : function() {
			return $("aq").get("value");
		},
		buildExact : function() {
			var q = "";
			
			if ($("eq").get("value") != "") {
				q = "\"" + $("eq").get("value") + "\"";
			}
			
			return q;
		},
		buildNOT : function() {
			var q = "";
			
			var str = $("nq").get("value").trim().split(" ");

			str.each(function(item, index) {
				if (item.trim() != "") {
					q += " -" + item.trim();
				}
			});
			
			return q;
		}
	});
	
	window.addEvent("load", function() {
		$$(".jsolr-advanced-search #query").set("text", query.build());
	});
	
	$$(".jsolrquery").addEvent("keyup", function(e) {
		var event = new Event(e);
		
		$$(".jsolr-advanced-search #query").set("text", query.build());
	});
	
	$$(".jsolrquery").addEvent("change", function(e) {
		var event = new Event(e);

		$$(".jsolr-advanced-search #query").set("text", query.build());
	});
});
*/

window.addEvent("domready", function() {
    
    $$(".jSolrShowRange, .jSolrShowRangeIcon").addEvent("click", function(e) {

        $$('.jSolrRanges').toggle() ;
        return false ;
    });
    
    $$("#jsolr-searchtools").addEvent("click", function(e) {

        $$('#jSolrSearchDates').toggle() ;
        return false ;
    });
    
    $$("#jsolr-custom-range-toggle").addEvent("click", function(e) {
        
        $$('#jSolrDateRange').toggle() ;
        return false ;
    });
    
    $$("#jSolrDateRangeClose").addEvent("click", function(e) {
        
        $$('#jSolrDateRange').toggle() ;
        return false ;
    });
    
    $$("#jsolr-submit-advanced").addEvent("click", function(e) {

        var Query = '' ;
        
        if ( $$('#jform_oq').get('value') != '' ) {
            
            var AnyOfThese = $('#jform_oq').get('value').replace(/\ /g,' OR ') ;
            var Query = Query+' '+AnyOfThese ;
        }
        
        if ( $$('#jform_eq').get('value') != '' ) {
            
            var Query = Query+' "'+$$('#jform_eq').get('value')+'"' ;
        }
        
        if ( $$('#jform_aq').get('value') != '' ) {
            
            var Query = Query+' '+$$('#jform_aq').get('value') ;
        }
        
        if ( $$('#jform_nq').get('value') != '' ) {
            
            var NoneOfThese = '-'+$$('#jform_nq').get('value').replace(/\ /g,' -') ;            
            var Query = Query+' '+NoneOfThese ;
        }
        
        var Url = '/jsolrsearch?q='+Query ;
        var Url = Url.replace('?q= ','?q=') ;
        
        window.location=Url ;
    });
});