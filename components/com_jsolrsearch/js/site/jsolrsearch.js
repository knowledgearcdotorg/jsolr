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

$(function() {
    
    $('#jsolr-submit-advanced').click(function(){

        var Query = '' ;
        
        if ( $('#jform_oq').val().length ) {
            
            var AnyOfThese = $('#jform_oq').val().replace(/\ /g,' OR ') ;            
            var Query = Query+' '+AnyOfThese ;
        }
        
        if ( $('#jform_eq').val().length ) {
            
            var Query = Query+' "'+$('#jform_eq').val()+'"' ;
        }
        
        if ( $('#jform_aq').val().length ) {
            
            var Query = Query+' '+$('#jform_aq').val() ;
        }
        
        if ( $('#jform_nq').val().length ) {
            
            var NoneOfThese = '-'+$('#jform_nq').val().replace(/\ /g,' -') ;            
            var Query = Query+' '+NoneOfThese ;
        }
        
        var Url = '/jsolrsearch?q='+Query ;
        var Url = Url.replace('?q= ','?q=') ;
        
        window.location=Url ;
    }) ;
});