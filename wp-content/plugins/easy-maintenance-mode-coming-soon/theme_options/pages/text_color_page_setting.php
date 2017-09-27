<div class="block ui-tabs-panel active" id="option-ui-id-3" >
<?php $current_options = wp_parse_args( get_option( 'easy_maintenance_mode_text_design_setting', array() ), text_design_setting() );

		if(isset($_POST['easy_maintenance_lite_settings_save_3'])) 
		{
				

				$current_options['headline_color']=$_POST['headline_color'];
				$current_options['description_color']=$_POST['description_color'];
				$current_options['headline_size']=$_POST['headline_size'];
				$current_options['description_size']=$_POST['description_size'];
				$current_options['headline_family_post_title']=$_POST['headline_family_post_title'];
				$current_options['description_family_post_title']=$_POST['description_family_post_title'];

				update_option('easy_maintenance_mode_text_design_setting',$current_options);

		}
			
		if(isset($_POST['easy_maintenance_lite_settings_reset_3'])) 
		{
			$current_options['headline_color']='#ffffff';
			$current_options['description_color']='#ffffff';
			$current_options['headline_size']='30';
			$current_options['description_size']='18';
			$current_options['headline_family_post_title']='Arial';
			$current_options['description_family_post_title']='Arial';

			update_option('easy_maintenance_mode_text_design_setting',$current_options);
		}
	?>
	<form method="post" action="#section_text-color">
		<div id="heading">
			<table style="width:100%;"><tr>
				<td><h2><?php _e('Text font & color settings','easy-maintenance-mode-coming-soon');?></h2></td>
				<td style="width:30%;">
					<div class="easy_maintenance_lite_settings_loding" id="easy-maintenance-mode-coming-soon_loding_3_image"></div>
					<div class="easy_maintenance_lite_settings_massage" id="easy_maintenance_lite_settings_save_3_success" ><?php _e('Options data successfully Saved','easy-maintenance-mode-coming-soon');?></div>
					<div class="easy_maintenance_lite_settings_massage" id="easy_maintenance_lite_settings_save_3_reset" ><?php _e('Options data successfully reset','easy-maintenance-mode-coming-soon');?></div>
				</td>
				<td style="text-align:right;">					
					<input class="button" type="submit" name="easy_maintenance_lite_settings_reset_3" value="Restore Defaults">&nbsp;
					<input class="button button-primary button-large" name="easy_maintenance_lite_settings_save_3" type="submit" value="Save Options">
				</td>
				</tr>
			</table>			
		</div>	

        <div class="section">
            <div class="element">
            	<h3><?php _e('Headline Font Color','easy-maintenance-mode-coming-soon');?></h3>
                <input type="text" id="headline_color" name="headline_color" value="<?php if(!empty($current_options['headline_color'])){ echo $current_options['headline_color']; } ?>">
            </div>
        </div>

        <div class="section">
            <div class="element">
            	<h3><?php _e('Description Font Color','easy-maintenance-mode-coming-soon');?></h3>
                <input type="text" id="description_color" name="description_color" value="<?php if(!empty($current_options['description_color'])){ echo $current_options['description_color']; } ?>">
            </div>
        </div>

        <div class="section">
            <div class="element">
            	<h3><?php _e('Headline Font size','easy-maintenance-mode-coming-soon');?></h3>
            	<select id="headline_size" name="headline_size">
	                <?php for( $i=10;  $i<=100 ; $i++) { ?>
	                <option value="<?php echo $i; ?>" <?php if( $i == $current_options['headline_size']){ echo "selected"; } ?>><?php echo $i; ?></option>
	                <?php } ?>
            	</select>
            </div>
        </div>

        <div class="section">
            <div class="element">
            	<h3><?php _e('Description Font size','easy-maintenance-mode-coming-soon');?></h3>
            	<select id="description_size" name="description_size">
	                <?php for( $i=10;  $i<=100 ; $i++) { ?>
	                <option value="<?php echo $i; ?>" <?php if( $i == $current_options['description_size']){ echo "selected"; } ?>><?php echo $i; ?></option>
	                <?php } ?>
            	</select>
            </div>
        </div>

        <div class="section">
            <div class="element">
            	<h3><?php _e('Headline Font Style','easy-maintenance-mode-coming-soon');?></h3>
            	<?php $headline_family = $current_options['headline_family_post_title']; ?>
            	<select id="headline_family_post_title" name="headline_family_post_title">				
				<optgroup label="Default Fonts">
				<option value="Arial" <?php selected($headline_family, 'Arial' ); ?>>Arial</option>
				<option value="_arial_black" <?php selected($headline_family, '_arial_black' ); ?>>Arial Black</option>
				<option value="Courier New" <?php selected($headline_family, 'Courier New' ); ?>>Courier New</option>
				<option value="georgia" <?php selected($headline_family, 'georgia' ); ?>>Georgia</option>
				<option value="grande" <?php selected($headline_family, 'grande' ); ?>>Grande</option>
				<option value="_helvetica_neue" <?php selected($headline_family, '_helvetica_neue' ); ?>>Helvetica Neue</option>
				<option value="_impact" <?php selected($headline_family, '_impact' ); ?>>Impact</option>
				<option value="_lucida" <?php selected($headline_family, '_lucida' ); ?>>Lucida</option>
				<option value="_lucida" <?php selected($headline_family, '_lucida' ); ?>>Lucida Grande</option>
				<option value="_OpenSansBold" <?php selected($headline_family, '_OpenSansBold' ); ?>>OpenSansBold</option>
				<option value="_palatino" <?php selected($headline_family, '_palatino' ); ?>>Palatino</option>
				<option value="_sans" <?php selected($headline_family, '_sans' ); ?>>Sans</option>
				<option value="_Sans-Serif" <?php selected($headline_family, '_Sans-Serif' ); ?>>Sans-Serif</option>
				<option value="_tahoma" <?php selected($headline_family, '_tahoma' ); ?>>Tahoma</option>
				<option value="_times" <?php selected($headline_family, '_times' ); ?>>Times New Roman</option>
				<option value="_trebuchet" <?php selected($headline_family, '_trebuchet' ); ?>>Trebuchet</option>
				<option value="_verdana" <?php selected($headline_family, '_verdana' ); ?>>Verdana</option>
				</optgroup>
			   </select>
            </div>
        </div>


        <div class="section">
            <div class="element">
            	<h3><?php _e('Headline Font Style','easy-maintenance-mode-coming-soon');?></h3>
            	<?php $description_family = $current_options['description_family_post_title']; ?>
            	<select id="description_family_post_title" name="description_family_post_title">				
				<optgroup label="Default Fonts">
				<option value="Arial" <?php selected($description_family, 'Arial' ); ?>>Arial</option>
				<option value="_arial_black" <?php selected($description_family, '_arial_black' ); ?>>Arial Black</option>
				<option value="Courier New" <?php selected($description_family, 'Courier New' ); ?>>Courier New</option>
				<option value="georgia" <?php selected($description_family, 'georgia' ); ?>>Georgia</option>
				<option value="grande" <?php selected($description_family, 'grande' ); ?>>Grande</option>
				<option value="_helvetica_neue" <?php selected($description_family, '_helvetica_neue' ); ?>>Helvetica Neue</option>
				<option value="_impact" <?php selected($description_family, '_impact' ); ?>>Impact</option>
				<option value="_lucida" <?php selected($description_family, '_lucida' ); ?>>Lucida</option>
				<option value="_lucida" <?php selected($description_family, '_lucida' ); ?>>Lucida Grande</option>
				<option value="_OpenSansBold" <?php selected($description_family, '_OpenSansBold' ); ?>>OpenSansBold</option>
				<option value="_palatino" <?php selected($description_family, '_palatino' ); ?>>Palatino</option>
				<option value="_sans" <?php selected($description_family, '_sans' ); ?>>Sans</option>
				<option value="_Sans-Serif" <?php selected($description_family, '_Sans-Serif' ); ?>>Sans-Serif</option>
				<option value="_tahoma" <?php selected($description_family, '_tahoma' ); ?>>Tahoma</option>
				<option value="_times" <?php selected($description_family, '_times' ); ?>>Times New Roman</option>
				<option value="_trebuchet" <?php selected($description_family, '_trebuchet' ); ?>>Trebuchet</option>
				<option value="_verdana" <?php selected($description_family, '_verdana' ); ?>>Verdana</option>
				</optgroup>
			   </select>
            </div>
        </div>


		<div id="button_section">
			<input class="button button-primary button-large" name="easy_maintenance_lite_settings_save_3" type="submit" value="Save Options">&nbsp;
			<input class="button" type="submit" name="easy_maintenance_lite_settings_reset_3" value="Restore Defaults">
		</div>
		<?php wp_nonce_field('easy_maintenancecolortextsetingpage_security_check','easy_maintenance_settingstextcolorpage_settings'); ?>
	</form>
</div>