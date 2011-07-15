(function(){
	var page = 0;
	
	var activityFeed = function(options){
	
		var defaults = {
			action:"load_activity_feed",
			start:parseInt(jQuery('#activity_container').find('article').size() + 1),
			limit:10,
			elem:"#activity_container"
		}
		
		var data = jQuery.extend( {}, defaults, options );
		
		var ajax_url = "/wp-admin/admin-ajax.php";
			
		jQuery("#load_more_activities").hide();
		jQuery('span.wait').show();
		
			
		jQuery.post(ajax_url, data , function(response){
			if( response && ! response.error ){
				jQuery('span.wait').hide();
				jQuery("#load_more_activities").show();
				jQuery(data.elem).append(response);
			}
		});
	
	}
	
	jQuery('body').delegate('.load_activities', 'click', function(e){
		e.preventDefault();
		activityFeed(jQuery(this).data());
	});
	
	

 
})();