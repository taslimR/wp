<div class="block ui-tabs-panel " id="option-ui-id-2" >	
	<?php $current_options = wp_parse_args( get_option( 'easy_maintenance_mode_background_setting_save', array() ),background_setting() );
  	
		if(isset($_POST['easy_maintenance_lite_settings_save_2'])) 
		{
			
		    $current_options['background_option']=$_POST['background_option'];
		    $current_options['background_color']=$_POST['background_color'];
		    $current_options['background_effect']=$_POST['background_effect'];
		    $current_options['background_text']=$_POST['background_text'];
	
			update_option('easy_maintenance_mode_background_setting_save',$current_options);
		}	


		if(isset($_POST['easy_maintenance_lite_settings_reset_2'])) 
		{
			$current_options['background_option']='static-background-color';
			$current_options['background_color']='#2f96b4';
			$current_options['background_effect']='none';
			$current_options['background_text']='';
					
			update_option('easy_maintenance_mode_background_setting_save',$current_options);
		}  
?>	
	<form method="post" action="#section_background">
		<div id="heading">
			<table style="width:100%;"><tr>
				<td><h2><?php _e('Background Settings','easy-maintenance-mode-coming-soon');?></h2></td>
				<td style="width:30%;">
					<div class="easy_maintenance_lite_settings_loding" id="easymaintenance_loding_2_image"></div>
					<div class="easy_maintenance_lite_settings_massage" id="easy_maintenance_lite_settings_save_2_success" ><?php _e('Options data successfully Saved','easy-maintenance-mode-coming-soon');?></div>
					<div class="easy_maintenance_lite_settings_massage" id="easy_maintenance_lite_settings_save_2_reset" ><?php _e('Options data successfully reset','easy-maintenance-mode-coming-soon');?></div>
				</td>
				<td style="text-align:right;">
					<input class="button" type="submit" name="easy_maintenance_lite_settings_reset_2" value="Restore Defaults">&nbsp;
					<input class="button button-primary button-large" name="easy_maintenance_lite_settings_save_2" type="submit" value="Save Options">
				</td>
				</tr>
			</table>	
		</div>

		<div class="section">
			<h3><?php _e('Select Background','easy-maintenance-mode-coming-soon'); ?>  </h3>
			<select id="background_option" name="background_option" onChange="select_background()">
				<option value="no-background" <?php selected($current_options['background_option'], 'no-background' ); ?>><?php _e("No background Selected","easy-maintenance-mode-coming-soon"); ?></option>
					<optgroup label="Select background">
						<option value="static-background-color" <?php selected($current_options['background_option'], 'static-background-color' ); ?>><?php _e("Static Background Color","easy-maintenance-mode-coming-soon"); ?></option>
						<option value="static-background-image" <?php selected($current_options['background_option'], 'static-background-image' ); ?>><?php _e("Static Background Image","easy-maintenance-mode-coming-soon"); ?></option>
					</optgroup>
			</select>
		</div>


		<div class="section background_color_section">
			<h3><?php _e('Background Color','easy-maintenance-mode-coming-soon'); ?></h3>
			<input type="text" id="background_color" name="background_color" value="<?php if(!empty($current_options['background_color'])){ echo $current_options['background_color']; } ?>"/>
		</div>

		<div class="section background_effect_section">
            <div class="element">
            	<h3><?php _e('Background Noise Effect','easy-maintenance-mode-coming-soon');?></h3>
                <input type="radio" name="background_effect" value="none" id="background_effect"  <?php if($current_options['background_effect']=='none') echo 'checked' ?>/>&nbsp;<?php _e('None','easy-maintenance-mode-coming-soon'); ?><br>
                <input type="radio" name="background_effect" value="noise" id="background_effect" <?php if($current_options['background_effect']=='noise') echo 'checked' ?>/>&nbsp;<?php _e('Noise','easy-maintenance-mode-coming-soon'); ?>
            </div>
        </div>


		<div class="section background_text_section">
            <div class="element">
            	<h3><?php _e('Select background image','easy-maintenance-mode-coming-soon');?></h3>
                <input type="text" value="<?php if(!empty($current_options['background_text'])){ echo $current_options['background_text']; } ?>" id="background_text" name="background_text" size="36">
                <input type="button" value="Upload Background Image" class="button-primary upload_image_button" style="margin-left:10px;height:30px">
            </div>
            <br/>
            <?php if(!empty($current_options['background_text'])) { ?>
            <div><img id="image-prev" style="display: inline; width:200px; border:1px solid #3894BD;" src="<?php echo $current_options['background_text']; ?>"></div>
        	<?php } ?>
        </div>

				
		<div id="button_section">
			<input class="button button-primary button-large" name="easy_maintenance_lite_settings_save_2" type="submit" value="Save Options">&nbsp;
			<input class="button" type="submit" name="easy_maintenance_lite_settings_reset_2" value="Restore Defaults">					
		</div>
		<?php wp_nonce_field('easy_maintenancebgpagesetting_security_check','easy_maintenance_groundpageback_settings'); ?>
	</form>
</div>

<script>

	<?php  if($current_options['background_option'] == 'no-background'){ echo "all_hidefeild()"; }else if($current_options['background_option'] == 'static-background-color'){ echo "static_background_color();";}else{ echo "static_background_image();"; } ?>

	function select_background(){
		var background_option = jQuery("#background_option").val();
		//alert(background_option);
		if( background_option == "no-background")
		{ 
			all_hidefeild();
		}
		if(background_option == "static-background-color")
	    {
	    	static_background_color();
	    }
	    if(background_option == "static-background-image")
	    {
	    	static_background_image();
	    }
	}

	function static_background_image()
	{
		jQuery('.background_color_section').hide();
	    jQuery('.background_effect_section').hide();
	    jQuery('.background_text_section').show();
	}

	function static_background_color()
	{
		jQuery('.background_text_section').hide();
	    jQuery('.background_color_section').show();
	    jQuery('.background_effect_section').show();
	}

	function all_hidefeild()
	{
		jQuery('.background_text_section').hide();
		jQuery('.background_effect_section').hide();
		jQuery('.background_color_section').hide();
	}

	jQuery("#background_color").wpColorPicker();

</script>
