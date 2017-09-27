<?php
/**
 * text-design.php file
 *
 * using for add text color,size and fonts etc
 */
 $text_design= get_option('easy_maintenance_mode_text_design_setting');

 
?>
<script>
		//save button onclick function for post value through ajax in option table
        function text_design_setting_save()
        {
            
		    var headline_color = jQuery('#headline-color').val();
			var description_color = jQuery('#description-color').val();
		
			var headline_size = jQuery('#headline-size').val();
			var description_size = jQuery('#description-size').val();
		
			var headline_family_post_title = jQuery('#headline_family_post_title option:selected').text();
			var description_family_post_title = jQuery('#description_family_post_title option:selected').text();
		
				
			
			jQuery('#loading5').show();
			
		
            jQuery.ajax(
            {
	    	    type: "POST",
		        url: ajaxurl,
	
		        data : {
			    'action':'maintenance_text_design_setting_save_post',
			    'headline_color':headline_color,
			    'description_color':description_color,
				
				'headline_size':headline_size,
				'description_size':description_size,
				
				' headline_family_post_title': headline_family_post_title,
				'description_family_post_title': description_family_post_title,
				
			        },
                success : function(data){
									jQuery('#loading5').hide();
                                   jQuery('#success_message_text_design_setting_save').show();
								   jQuery("#success_message_text_design_setting_save").fadeOut(2000);
								   
                                   }			
            });
        }

 //headline font-size slider
  jQuery(function() {
    jQuery( "#headline-size-slider" ).slider({
      orientation: "horizontal",
      range: "min",
	    max: 100,
		min:0,
		slide: function( event, ui ) {
        jQuery( "#headline-size" ).val( ui.value );
      }
		});
		
		jQuery( "#headline-size-slider" ).slider("value", <?php if($text_design['headline_size']!==''){echo ($text_design['headline_size']); } else { echo('0');} ?> );
   jQuery( "#headline-size" ).val( jQuery( "#headline-size-slider" ).slider( "value") );
    
  });
  
  //description font-size slider
  jQuery(function() {
    jQuery( "#description-size-slider" ).slider({
      orientation: "horizontal",
      range: "min",
	    max: 100,
		min:0,
		slide: function( event, ui ) {
        jQuery( "#description-size" ).val( ui.value );
      }
		});
		
		jQuery( "#description-size-slider" ).slider("value", <?php if($text_design['description_size']!==''){echo ($text_design['description_size']); } else { echo('0');} ?> );
   jQuery( "#description-size" ).val( jQuery( "#description-size-slider" ).slider( "value") );
    
  });
  
  
 
  
</script>

<table class="form-table">
        <tr>
		<th scope="row" class="page-title"><span class="icon-title header-title"></span><?php _e('Text Settings','')?></th>
		<td></td>
		</tr>
		
		<tr>
            <th scope="row" ><?php _e('Headline Font Color','')?></th>
            <td>
               <!-- Headline color tooltip ---->
                <span class="icon help" style="float:right">
                    <span class="tooltip"><?php _e('Select Headline Color.','')?></span>
                </span>
			 <input id="headline-color" name="headline-color" type="text" value="<?php echo $text_design['headline_color'] ?>" class="my-color-field" data-default-color="#ffffff" />
            </td>
        </tr>
		
		<tr>
            <th scope="row" ><?php _e('Description Font Color','')?></th>
            <td>
               <!-- Description color tooltip ---->
                <span class="icon help" style="float:right">
                    <span class="tooltip"><?php _e('Select Desscription Color.','')?></span>
                </span>
			 <input id="description-color" name="description-color" type="text" value="<?php echo $text_design['description_color'] ?>" class="my-color-field" data-default-color="#ffffff" />
            </td>
        </tr>
		
		
		<tr>
            <th scope="row" ><?php _e('Headline Font size','')?></th>
            <td>
				<!-- Headline Font-size Tooltip ---->
                <span class="icon help" style="float:right">
                    <span class="tooltip"><?php _e('Select headline font-size','')?></span>
                </span>
				<div class="input-append">
				 <input type="text" class="my-slider-text" id="headline-size" readonly="readonly" name="headline-size"  >
				   <span class="add-on">Px</span>
				   </div>
					<div id="headline-size-slider" class="size-slider" style="width: 25%;display:inline-block"></div>
			</td>
        </tr>
		
		<tr>
            <th scope="row" ><?php _e('Description Font size','')?></th>
            <td>
				<!-- Description Font size tooltip ---->
                <span class="icon help" style="float:right">
                    <span class="tooltip"><?php _e('Select Description font-size.','')?></span>
                </span>
				<div class="input-append">
				 <input type="text" class="my-slider-text" id="description-size" value="10" name="description-size" readonly="readonly" >
				   <span class="add-on">Px</span>
				   </div>
					<div id="description-size-slider" class="size-slider" style="width: 25%;display:inline-block"></div>
			</td>
        </tr>
		
	
		
		<tr>
            <th scope="row" ><?php _e('Headline Font Style','')?></th>
            <td>
               <!-- Headline Font Style tooltip ---->
                <span class="icon help" style="float:right">
                    <span class="tooltip"><?php _e('Select headline font style','')?></span>
                </span>
				<?php $headline_family_post_title = $text_design['headline_family_post_title']; ?>
				<select  name="headline_family_post_title" class="standard-dropdown" id="headline_family_post_title">
									
				<optgroup label="Default Fonts">
				<option value="Arial" <?php selected($headline_family_post_title, 'Arial' ); ?>>Arial</option>
				<option value="_arial_black" <?php selected($headline_family_post_title, 'Arial Black' ); ?>>Arial Black</option>
				<option value="Courier New" <?php selected($headline_family_post_title, 'Courier New' ); ?>>Courier New</option>
				<option value="georgia" <?php selected($headline_family_post_title, 'Georgia' ); ?>>Georgia</option>
				<option value="grande"<?php selected($headline_family_post_title, 'Grande' ); ?>>Grande</option>
				<option value="_helvetica_neue" <?php selected($headline_family_post_title, 'Helvetica Neue' ); ?>>Helvetica Neue</option>
				<option value="_impact" <?php selected($headline_family_post_title, 'Impact' ); ?>>Impact</option>
				<option value="_lucida" <?php selected($headline_family_post_title, 'Lucida' ); ?>>Lucida</option>
				<option value="_lucida"<?php selected($headline_family_post_title, 'Lucida Grande' ); ?>>Lucida Grande</option>
				<option value="_OpenSansBold" <?php selected($headline_family_post_title, 'OpenSansBold' ); ?>>OpenSansBold</option>
				<option value="_palatino" <?php selected($headline_family_post_title, 'Palatino' ); ?>>Palatino</option>
				<option value="_sans" <?php selected($headline_family_post_title, 'Sans' ); ?>>Sans</option>
				<option value="_sans" <?php selected($headline_family_post_title, 'Sans-Serif' ); ?>>Sans-Serif</option>
				<option value="_tahoma" <?php selected($headline_family_post_title, 'Tahoma' ); ?>>Tahoma</option>
				<option value="_times"<?php selected($headline_family_post_title, 'Times New Roman' ); ?>>Times New Roman</option>
				<option value="_trebuchet" <?php selected($headline_family_post_title, 'Trebuchet' ); ?>>Trebuchet</option>
				<option value="_verdana" <?php selected($headline_family_post_title, 'Verdana' ); ?>>Verdana</option>
				</optgroup>
				
			   </select>
            </td>
        </tr>
		<tr>
		
		
		
            <th scope="row" ><?php _e('Description Font Style','')?></th>
            <td>
               <!-- Meta Title tooltip ---->
                <span class="icon help" style="float:right">
                    <span class="tooltip"><?php _e('Select description font style.','')?></span>
                </span>
			<?php $description_family_post_title = $text_design['description_family_post_title']; ?>
				<select class="standard-dropdown" name="description_family_post_title" class="select" id="description_family_post_title">
									
				<optgroup label="Default Fonts">
				<option value="Arial" <?php selected($description_family_post_title, 'Arial' ); ?>>Arial</option>
				<option value="_arial_black" <?php selected($description_family_post_title, 'Arial Black' ); ?>>Arial Black</option>
				<option value="Courier New" <?php selected($description_family_post_title, 'Courier New' ); ?>>Courier New</option>
				<option value="georgia" <?php selected($description_family_post_title, 'Georgia' ); ?>>Georgia</option>
				<option value="grande"<?php selected($description_family_post_title, 'Grande' ); ?>>Grande</option>
				<option value="_helvetica_neue" <?php selected($description_family_post_title, 'Helvetica Neue' ); ?>>Helvetica Neue</option>
				<option value="_impact" <?php selected($description_family_post_title, 'Impact' ); ?>>Impact</option>
				<option value="_lucida" <?php selected($description_family_post_title, 'Lucida' ); ?>>Lucida</option>
				<option value="_lucida"<?php selected($description_family_post_title, 'Lucida Grande' ); ?>>Lucida Grande</option>
				<option value="_OpenSansBold" <?php selected($description_family_post_title, 'OpenSansBold' ); ?>>OpenSansBold</option>
				<option value="_palatino" <?php selected($description_family_post_title, 'Palatino' ); ?>>Palatino</option>
				<option value="_sans" <?php selected($description_family_post_title, 'Sans' ); ?>>Sans</option>
				<option value="_sans" <?php selected($description_family_post_title, 'Sans-Serif' ); ?>>Sans-Serif</option>
				<option value="_tahoma" <?php selected($description_family_post_title, 'Tahoma' ); ?>>Tahoma</option>
				<option value="_times"<?php selected($description_family_post_title, 'Times New Roman' ); ?>>Times New Roman</option>
				<option value="_trebuchet" <?php selected($description_family_post_title, 'Trebuchet' ); ?>>Trebuchet</option>
				<option value="_verdana" <?php selected($description_family_post_title, 'Verdana' ); ?>>Verdana</option>
				</optgroup>
				
			   </select>
            </td>
        </tr>
		
		
        <tr style="border-bottom:none;">
            <td>
                <input type="button" class="btn btn-primary"  value= "<?php _e('Save Changes', '');?>" onclick="text_design_setting_save()"/>
				<img class="loading" id="loading5"  src="<?php echo WBR_EM_PLUGIN_URL.'/images/loading.gif';?>" />
            </td>
			<td>
                
				
			 <div id="success_message_text_design_setting_save" class="success_message"><?php _e('Data save sucessfully','');?></div>
			  
            </td>
           
        </tr>
		
 </table>