// js to active the link of option pannel
 jQuery(document).ready(function() {
	jQuery('ul li.active ul').slideDown();
	// menu click	
	jQuery('#nav > li > a').click(function(){		
		if (jQuery(this).attr('class') != 'active')
		{ 		
			jQuery('#nav li ul').slideUp(350);
			jQuery(this).next().slideToggle(350);
			jQuery('#nav li a').removeClass('active');
			jQuery(this).addClass('active');
		  
			jQuery('ul.options_tabs li').removeClass('active');
			jQuery(this).parent().addClass('active');		
			var divid =  jQuery(this).attr("id");	
			var add="div#option-"+divid;
			var strlenght = add.length;
			
			if(strlenght<17)
			{	
				var add="div#option-ui-id-"+divid;
				var ulid ="#ui-id-"+divid;
				jQuery('ul.options_tabs li li ').removeClass('currunt');
				jQuery(ulid).parent().addClass('currunt');	
			}			
			jQuery('div.ui-tabs-panel').addClass('deactive');
			jQuery('div.ui-tabs-panel').removeClass('active');
			jQuery(add).removeClass('deactive');		
			jQuery(add).addClass('active');		
		}
	});
	
	// child submenu click
	jQuery('ul.options_tabs li li ').click(function() {			
		jQuery('ul.options_tabs li li ').removeClass('currunt');
		jQuery(this).addClass('currunt');
		var sub_a_id =  jQuery(this).children("a").attr("id");
		var option_add="div#option-"+sub_a_id;
		jQuery('div.ui-tabs-panel').addClass('deactive');
		jQuery('div.ui-tabs-panel').removeClass('active');
		jQuery(option_add).removeClass('deactive');		
		jQuery(option_add).addClass('active');
		
	});
	
});

/*Admin options pannal data value*/
	function easymaintenance_option_data_save(id) 
	{ 	
		var easy_maintenance_lite_settings_save= "#easy_maintenance_lite_settings_save_"+id;
		var easy_maintenance_lite_theme_options = "#easy_maintenance_lite_theme_options_"+id;
		var easy_maintenance_lite_settings_save_success = easy_maintenance_lite_settings_save+"_success";
		var loding_image ="#easymaintenance_loding_"+id;
		var easymaintenance_loding_image = loding_image +"_image";
		
		jQuery(easymaintenance_loding_image).show();
		jQuery(easy_maintenance_lite_settings_save).val("1");        
	    jQuery.ajax({
				   url:'admin.php?page=easy-maintenance-mode',
				   type:'post',
				   data : jQuery(easy_maintenance_lite_theme_options).serialize(),
				   success : function(data)
					{  					
						jQuery(easymaintenance_loding_image).fadeOut();						
						jQuery(easy_maintenance_lite_settings_save_success).show();
						jQuery(easy_maintenance_lite_settings_save_success).fadeOut(5000);
					}			
				});
	}
	
	/*Admin options value reset */
	function easymaintenance_option_data_reset(id) 
	{   var r=confirm("Do you want reset your theme setting!")
		if (r==true)
		  {		var easy_maintenance_lite_settings_save= "#easy_maintenance_lite_settings_save_"+id;
				var easy_maintenance_lite_theme_options = "#easy_maintenance_lite_theme_options_"+id;
				var easy_maintenance_lite_settings_save_reset = easy_maintenance_lite_settings_save+"_reset";				
				jQuery(easy_maintenance_lite_settings_save).val("2");       
				jQuery.ajax({
						   url:'admin.php?page=easy-maintenance-mode',
						   type:'post',
						   data : jQuery(easy_maintenance_lite_theme_options).serialize(),
						   success : function(data)
							{  	jQuery(easy_maintenance_lite_settings_save_reset).show();
								jQuery(easy_maintenance_lite_settings_save_reset).fadeOut(5000);
							}			
						});
		  }
		else
		  {
			alert("Cancel! reset theme setting process")
		  }		
	}