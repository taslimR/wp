
 function set_default()
        {
            alert("Are you sure to reset settings?")
		
            jQuery.ajax(
            {
	    	    type: "POST",
		        url: ajaxurl,
	
		        data : {
			    'action':'reset_data_post',
			   
			   
			        },
                success : function(data){
									
                                  
								   jQuery('#reset_data').show();
								   jQuery("#reset_data").fadeOut(2000);
								    jQuery('#reset_data1').show();
								   jQuery("#reset_data1").fadeOut(2000);
								   location.href='?page=easy-maintenance-mode';
								   
                                   }			
            });
        }



 

