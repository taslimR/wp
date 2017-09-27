<?php
if($_POST)
	 {
			$checkone = wp_verify_nonce($_POST['easy_maintenance_headerpage_settings'], 'easy_maintenance_security_check');
			$checktwo = wp_verify_nonce($_POST['easy_maintenance_groundpageback_settings'], 'easy_maintenancebgpagesetting_security_check');
			$checkthree  = wp_verify_nonce($_POST['easy_maintenance_settingstextcolorpage_settings'], 'easy_maintenancecolortextsetingpage_security_check');
		
			if($checkone == 1 || $checktwo == 1 || $checkthree == 1 )
			{
				
			}
			else
			{
				echo "Sorry given field are not correct";
				exit;
			}
			
			
	 }
?>
<div class="wrap settings-wrap" id="page-settings">
    <h2><?php _e('Settings','easy-maintenance-mode-coming-soon'); ?>>></h2>
    <div id="option-tree-header-wrap">
        <ul id="option-tree-header">
            <li id=""><a href="" target="_blank"></a></li>
            <li id="option-tree-version"><span><?php _e('Easy Maintenance Mode','easy-maintenance-mode-coming-soon'); ?></span></li>
			<a style="margin-right:16px;" target="_blank" href="<?php bloginfo("url"); ?>/?easy_maintenance_mode_preview=true&TB_iframe=true&width=500&height=532" class="button button-primary button-large fb-btn"><?php _e('Preview','easy-maintenance-mode-coming-soon'); ?></a>
			<a style="margin-right:16px;" target="_blank" href="http://easycomingsoon.com/" class="button button-primary button-large fb-btn"><?php _e('Upgrade To Pro Version','easy-maintenance-mode-coming-soon'); ?></a>
            <a style="margin-right:16px;" target="_blank" href="https://wordpress.org/plugins/easy-maintenance-mode-coming-soon/" class="button button-primary button-large fb-btn"><?php _e('Rate us on wordpress','easy-maintenance-mode-coming-soon'); ?></a>
        </ul>
		
    </div>
    <div id="option-tree-settings-api">
    <div id="option-tree-sub-header"></div>
        <div class = "ui-tabs ui-widget ui-widget-content ui-corner-all">
            <ul>
				
                <li id="tab_create_setting"><a href="#section_general"><?php _e('General Settings','easy-maintenance-mode-coming-soon');?></a>
                </li>
				<li id="tab_background_setting"><a href="#section_background"><?php _e('Background Settings','easy-maintenance-mode-coming-soon');?></a>
                </li>
                <li id="tab_text-color_setting"><a href="#section_text-color"><?php _e('Text & Color','easy-maintenance-mode-coming-soon');?></a>
                </li>
                <li id="tab_live_preview_setting"><a href="#section_live_preview"><?php _e('Live Preview','easy-maintenance-mode-coming-soon');?></a>
                </li>
                <li id="tab_templates_setting"><a href="#section_templates"><?php _e('Templates','easy-maintenance-mode-coming-soon');?></a>
                </li>
                <li id="tab_pro_setting"><a href="#section_pro"><?php _e('Pro Features','easy-maintenance-mode-coming-soon');?></a>
                </li>
				<li id="tab_aboutproversion"><a href="#section_aboutproversion"><?php _e('Whats Included In The Pro Version','easy-maintenance-mode-coming-soon');?></a>
                </li>
				<li id="tab_pricing_setting"><a href="#section_pricing_version"><?php _e('Pricing','easy-maintenance-mode-coming-soon');?></a>
                </li>
				<li id="tab_section_lovetab"><a href="#section_lovetab"><?php _e('Show Some Love','easy-maintenance-mode-coming-soon');?></a>
                </li>
				<li id="tab_section_videotab"><a href="#section_videotab"><?php _e('Video Tutorial','easy-maintenance-mode-coming-soon');?></a>
                </li>
               
            </ul>
    <div id="poststuff" class="metabox-holder">
        <div id="post-body" style="min-height:400px;">
			<div id="post-body-content">
                <div id="section_general" class = "postbox">
                    <div class="inside">
                        <div id="setting_theme_options_ui_text" class="format-settings">
                            <div class="format-setting-wrap">             
                    <?php load_template( dirname( __FILE__ ) . '/pages/header_page_settings.php' );  ?>    
                </div>
            </div>
        </div>
    </div>

    
	<div id="section_background" class = "postbox">
        <div class="inside">
            <div id="design_customization_settings" class="format-settings">
                <div class="format-setting-wrap">
      <div class = "format-setting type-textarea has-desc">
        <div class = "format-setting-inner">
        
		<?php  load_template( dirname( __FILE__ ) . '/pages/backgorund_page_settings.php' ); ?>
                                              
						</div>
					</div>
				</div>
			</div>
		</div>
    </div>


    <div id="section_text-color" class = "postbox">
        <div class="inside">
            <div id="design_customization_settings" class="format-settings">
                <div class="format-setting-wrap">
      <div class = "format-setting type-textarea has-desc">
        <div class = "format-setting-inner">
        
        <?php  load_template( dirname( __FILE__ ) . '/pages/text_color_page_setting.php' ); ?>
                                              
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div id="section_live_preview" class = "postbox">
        <div class="inside">
            <div id="design_customization_settings" class="format-settings">
                <div class="format-setting-wrap">
      <div class = "format-setting type-textarea has-desc">
        <div class = "format-setting-inner">
        
        <?php  load_template( dirname( __FILE__ ) . '/pages/live_preview_settings.php' ); ?>
                                              
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div id="section_templates" class = "postbox">
        <div class="inside">
            <div id="design_customization_settings" class="format-settings">
                <div class="format-setting-wrap">
					<div class = "format-setting type-textarea has-desc">
						<div class = "format-setting-inner">
        
        <?php  load_template( dirname( __FILE__ ) . '/pages/templates_settings.php' ); ?>
                                              
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>



    <div id="section_pro" class = "postbox">
        <div class="inside">
            <div id="design_customization_settings" class="format-settings">
                <div class="format-setting-wrap">
					<div class = "format-setting type-textarea has-desc">
						<div class = "format-setting-inner">
        
        <?php  load_template( dirname( __FILE__ ) . '/pages/pro_features_settings.php' ); ?>
                                              
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
	
	
	<div id="section_aboutproversion" class="postbox">
        <div class="inside">
            <div id="design_customization_settings" class="format-settings">
                <div class="format-setting-wrap">
					<div class = "format-setting type-textarea has-desc">
						<div class = "format-setting-inner">
        
        <?php  load_template( dirname( __FILE__ ) . '/pages/about_pro_version.php' ); ?>
                                              
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
	
	<div id="section_pricing_version" class = "postbox">
        <div class="inside">
            <div id="design_customization_settings" class="format-settings">
                <div class="format-setting-wrap">
      <div class = "format-setting type-textarea has-desc">
        <div class = "format-setting-inner">
        
        <?php  load_template( dirname( __FILE__ ) . '/pages/pricing_settings.php' ); ?>
                                              
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
	
	
	<div id="section_lovetab" class = "postbox">
        <div class="inside">
            <div id="design_customization_settings" class="format-settings">
                <div class="format-setting-wrap">
				  <div class = "format-setting type-textarea has-desc">
					<div class = "format-setting-inner">
						<div class="block ui-tabs-panel active" id="option-ui-id-5" >
							<form method="post" id="easy-maintenance-mode-coming-soon_lite_theme_options_5">
									<div id="heading">
										<table style="width:100%;"><tr>
											<td><h2><?php _e('Show Some Love','easy-maintenance-mode-coming-soon');?></h2>
											<br>
											<p><?php _e("Like this plugin? Show your support by","easy-maintenance-mode-coming-soon"); ?></p>
											</td>
											<td style="width:30%;">
											</td>
											<td style="text-align:right;">					
												
											</tr>
										</table>			
									</div>

									<div class="section">
										<a class="button button-primary button-large" target="_blank" href="https://wordpress.org/support/view/plugin-reviews/easy-maintenance-mode-coming-soon"><?php _e('Rate It','easy-maintenance-mode-coming-soon'); ?></a>
										<a class="button button-primary button-large" target="_blank" href="http://twitter.com/share?url=https%3A%2F%2Fwordpress.org%2Fplugins%2Feasy-coming-soon%2F&amp;text=Check out this awesome %23WordPress Plugin I'm using,  Easy Coming Soon by @webriti"><i class="fa fa-twitter"></i><?php _e('Tweet It','easy-maintenance-mode-coming-soon'); ?></a>
										<a class="button button-primary button-large" target="_blank" href="http://member.easycomingsoon.com/pricing/"><?php _e('Buy Pro','easy-maintenance-mode-coming-soon'); ?></a>
									</div>	
							</form>
						</div>								  
					</div>
				</div>
				</div>
			</div>
		</div>
    </div>
	
	
	<div id="section_videotab" class="postbox">
        <div class="inside">
            <div id="design_customization_settings" class="format-settings">
                <div class="format-setting-wrap">
				  <div class = "format-setting type-textarea has-desc">
					<div class = "format-setting-inner">
						<div class="block ui-tabs-panel active" id="option-ui-id-5" >
							<form method="post" id="easy-maintenance-mode-coming-soon_lite_theme_options_5">
									<div id="heading">
										<table style="width:100%;"><tr>
											<td><h2><?php _e('Fully Functional Coming Soon Page in 30 minutes','easy-maintenance-mode-coming-soon');?></h2>
											<br>
											<p><?php sprintf(__("In this <a target = '_blank' href = 'https://www.youtube.com/watch?v=jwXOO9DDSpY'> Video Tutorial </a> , I will demonstrate how you can create a fully functional Coming Soon Page in as little as 30 minutes.</p><p> Here is what we the Landing page will look like.","easy-maintenance-mode-coming-soon")); ?>
											<div class="span6" style="width:100%">
											<img  style="height:50%; width:50%" src="<?php echo plugins_url('/pages/images/img/video-thumb.jpg',__FILE__);?>" alt="" style="width:100%"> 
											</div>
                                            <br></br>
											
											<b><?php _e('This video tutorial is for the premium version of the plugin. As you can see, it has','easy-maintenance-mode-coming-soon'); ?></b>
										    <br></br>
											
											<ul><?php _e('1. Company logo','easy-maintenance-mode-coming-soon'); ?></ul>
											<ul><?php _e('2. An Image slide show backGround','easy-maintenance-mode-coming-soon'); ?></ul>
											<ul><?php _e('3. Email capture box, with pption to capture first name and last name','easy-maintenance-mode-coming-soon'); ?></ul>
											<ul><?php _e("4. CountDown timer","easy-maintenance-mode-coming-soon"); ?></ul>
											<ul><?php _e('5. A progress bar','easy-maintenance-mode-coming-soon'); ?></ul>
											<ul><?php _e('6. Social media icons','easy-maintenance-mode-coming-soon'); ?></ul>
											<ul><?php _e('7. The video also contains a brief overview of the features like multiple templates, IP based access, newsletter integration etc.','easy-maintenance-mode-coming-soon'); ?> </ul>
											
											
											</p><?php echo sprintf(__("The premium version is priced at 29 USD and lets you use the plugin on unlimited website. </p><p>Here is the <a target = '_blank' ' href = 'https://youtu.be/JEbKUdvbzys'>link to the Video.</a> Enjoy!!!</p>","easy-maintenance-mode-coming-soon")); ?>
											</td>
											<td style="width:30%;">
											</td>
											<td style="text-align:right;">					
												
											</tr>
										</table>			
									</div>

									
							</form>
						</div>								  
					</div>
				</div>
				</div>
			</div>
		</div>
    </div>
	

		</div>
    </div>
    </div>
	<div class="webriti-submenu" style="height:35px;">			
            <div class="webriti-submenu-links" style="margin-top:5px;">
			<form method="POST">
				<input type="submit" onclick="return confirm( 'Click OK to reset theme data. Theme settings will be lost!' );" value="<?php _e('Restore All Defaults','easy-maintenance-mode-coming-soon'); ?>" name="restore_all_defaults" id="restore_all_defaults" class="reset-button btn">
			<form>
            </div><!-- webriti-submenu-links -->
        </div>
        <div class="clear"></div>
        </div>
    </div>
</div>

<?php
// Restore all defaults
if(isset($_POST['restore_all_defaults'])) 
	{
		update_option('easy_maintenance_mode_general_settings',general_setting());
        update_option('easy_maintenance_mode_text_design_setting',text_design_setting());
        update_option('easy_maintenance_mode_background_setting_save',background_setting());
	}
?>