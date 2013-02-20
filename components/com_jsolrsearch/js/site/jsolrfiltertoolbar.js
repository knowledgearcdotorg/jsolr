jQuery(function() {

    jQuery('#jSolrShowRange').click(function(){
        if ( jQuery('.jSolrRanges').is(':visible') ) {
            jQuery('.jSolrRanges').hide() ;
        } else {
            jQuery('.jSolrRanges').show() ;
        }
    }) ;
});