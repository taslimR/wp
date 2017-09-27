<?php
/**
 * background-settings.php file
 *
 * using for add background,slider etc.
 */
 $background= get_option('easy_maintenance_mode_background_setting_save');
$background_option = $background['background_option'];

?>
<script>
	function background_setting_save()
    {
            
		var background_option = jQuery('#select-background option:selected').val();
		var background_color = jQuery('#background-color').val();
		var background_effect = jQuery('input:radio[name="background_effect"]:checked').val();
		var background_text = jQuery('#background-text').val();
		
		
			
		
		jQuery('#background_loading').show();
		
	
		jQuery.ajax(
		{
			type: "POST",
			url: ajaxurl,

			data : {
			'action':'maintenance_background_setting_save_post',
			'background_option':background_option,
			'background_color':background_color,
			'background_effect':background_effect,
			'background_text':background_text,
		
				},
			success : function(data){
								jQuery('#background_loading').hide();
							   jQuery('#success_message_background_setting_save').show();
							   jQuery("#success_message_background_setting_save").fadeOut(2000);
							   
							   }			
		});
    }
</script>
<table class="form-table">
		<tr>
		<th scope="row" class="page-title"><?php _e('Background Settings','')?></th>
		<td></td>
		</tr>
		<tr>
            <th scope="row" ><?php _e('Select Background','')?></th>
            <td>
               
                <span class="icon help" style="float:right">
                    <span class="tooltip"><?php _e('Select coming soon page background options','')?></span>
                </span>
				
				 <select id="select-background" class="standard-dropdown" name="select-background" onchange="select_background()" >
					<option value="no-background" <?php selected($background_option, 'no-background' ); ?>><?php _e('No background Selected','')?></option>
					<optgroup label="Select background">
					<option value="static-background-color" <?php selected($background_option, 'static-background-color' ); ?>><?php _e('Static Background Color','')?></option>
					<option value="static-background-image" <?php selected($background_option, 'static-background-image' ); ?>><?php _e('Static Background Image','')?></option>
				
					</optgroup>
			  
				</select>
				<br>
				<img src="<?php echo WBR_EM_PLUGIN_URL.'/images/static-background.png'; ?>" class="wbr-bg-image" id="static-color" alt="" style="display:<?php if($background['background_option']!=="static-background-color") echo "none"; ?>">
				<img src="<?php echo WBR_EM_PLUGIN_URL.'/images/static-image.png'; ?>" class="wbr-bg-image" id="static-img" alt=""   style="display:<?php if($background['background_option']!=="static-background-image") echo "none"; ?>">
			
            </td>
			<td>
			</td>
        </tr>
		
		
</table>

<!-- Start of static background Color fields -->
		<!-- =================================================== -->		
<table class="form-table" id="static_color_field" style="display:<?php if($background['background_option']!=="static-background-color") echo "none"; ?>">		
		
		<tr>
            <th scope="row" ><?php _e('Background Color','')?></th>
            <td>
				
                <span class="icon help" style="float:right">
                    <span class="tooltip"><?php _e('Select page background color here','')?></span>
                </span>
				
               <input id="background-color" name="background-color" type="text" value="<?php echo $background['background_color']; ?>" class="my-color-field" data-default-color="#ffffff" />
			   
			</td>
        </tr>
		<tr>
            <th scope="row" ><?php _e('Background Effect','')?></th>
            <td>
              
                <span class="icon help" style="float:right">
                    <span class="tooltip"><?php _e('Apply noisy background effect here','easy-maintenance-mode-coming-soon')?></span>
                </span>
				
				<input type="radio" name="background_effect" value="none" id="background_effect"  <?php if($background['background_effect']=='none') echo 'checked' ?> />&nbsp;<?php _e('None','easy-maintenance-mode-coming-soon')?><br>
                <input type="radio" name="background_effect" value="noise" id="background_effect"  <?php if($background['background_effect']=='noise') echo 'checked' ?> />&nbsp;<?php _e('Noise','easy-maintenance-mode-coming-soon')?>
                
            </td>
        </tr>
		
		
</table>
<!-- Start of static background Image fields -->
		<!-- =================================================== -->
<table class="form-table" id="static_image_field" style="display:<?php if($background['background_option']!=="static-background-image") echo "none"; ?>">		
		<tr>
            <th scope="row" ><?php _e('Background Iamge','easy-maintenance-mode-coming-soon')?></th>
            <td>
				
                <span class="icon help" style="float:right">
                    <span class="tooltip"><?php _e('Select coming soon page background image','easy-maintenance-mode-coming-soon')?></span>
                </span>
				
                <input type="text"  id="background-text" class="pro_text" placeholder="<?php _e('No media Selected','easy-maintenance-mode-coming-soon');?>" value="<?php echo $background['background_text'];?>"  readonly="readonly" />
				 
				<input type="button" value="<?php _e('Upload','easy-maintenance-mode-coming-soon')?>" id="upload-background" class="button-primary"/>
				<input type="button" id="display-background" value="<?php _e('Preview','easy-maintenance-mode-coming-soon')?>" class="btn " />
				<input type="button" id="background-re" value="<?php _e('None','easy-maintenance-mode-coming-soon')?>" class="btn btn-danger" style="display:<?php if($background['background_text']=="") echo "none"; ?>;"/> <br>
				<br>
				<img src="" class="wbr-csp-image" id="background-img" alt=""  style="display:none">
				<div class="description" id="no-background-media" style="display:none"><?php _e('No media Selected','easy-maintenance-mode-coming-soon');?> </div>
			</td>
        </tr>
		
		
</table>		
		

 
<table class="form-table">			
         <tr style="border-bottom:none;">
            <td>
                <input type="button" class="btn btn-primary"  value= "<?php _e('Save Changes','easy-maintenance-mode-coming-soon');?>" onclick="background_setting_save()"/>
				<img class="loading" id="background_loading" src="<?php echo WBR_EM_PLUGIN_URL.'/images/loading.gif';?>" />
            </td>
			<td>
                
				
			 <div id="success_message_background_setting_save" class="success_message"><?php _e('Data save sucessfully','easy-maintenance-mode-coming-soon');?></div>
			  
            </td>
           
        </tr>
		
 </table>