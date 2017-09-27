<?php
/*
Plugin Name:Easy Maintenance Mode
Description: Easy Maintenance Mode plugin allows you to quickly create under construction, launch , coming soon, landing  page for your website. Collect E-mails and connect with users on Social Networks.
Version: 1.4.2
Author:Ankit Agarwal,Priyanshu Mittal
Author URI: http://easycomingsoon.com/
Plugin URI: http://easycomingsoon.com/
Text Domain: easy-maintenance-mode-coming-soon
*/

require 'default_settings.php';

define( 'WBR_EM_PLUGIN_PATH', plugin_dir_path( __FILE__ ) ); 
define( 'WBR_EM_PLUGIN_URL', plugin_dir_url( __FILE__ ) ); 
define("easy-maintenance-mode-coming-soon", "easy-maintenance-mode-coming-soon");

/*
* Loading a text domain code for plugin translation 
*/
add_action('plugins_loaded', 'wan_load_textdomain');
function wan_load_textdomain() {
	load_plugin_textdomain( 'easy-maintenance-mode-coming-soon', false, dirname( plugin_basename(__FILE__) ) . '/languages/' );
}

/*
* Webriti coming soon admin menu hook add
*/
add_action('admin_menu','easy_maintenance_mode_menu');

/*
* coming soon add plugin  menu function
*/
function easy_maintenance_mode_menu()
{
    //plugin menu name for coming soon plugin
    $menu = add_menu_page('Easy Maintenance Mode', __('Easy Maintenance Mode','easy-maintenance-mode-coming-soon'),'administrator', 'easy-maintenance-mode','easy_maintenance_mode_plugin_construction');
    //add hook to add styles and scripts for coming soon admin page
    add_action( 'admin_print_styles-' . $menu, 'easy_maintenance_mode_plugin_js_css' );
}


function easy_maintenance_mode_plugin_js_css()
{

    //enequeue scripts page for coming soon plugin admin panel
    wp_enqueue_media();
    wp_enqueue_script('theme-preview');
    wp_enqueue_script('media-uploads',WBR_EM_PLUGIN_URL.'js/media-upload-script.js',array('media-upload','thickbox','jquery'));
    wp_enqueue_script('wbr_soon_pro_tab',WBR_EM_PLUGIN_URL.'js/wbr_soon_pro_tab.js');
    wp_enqueue_script('my-color-picker-script', WBR_EM_PLUGIN_URL.'js/my-color-picker-script.js', array( 'wp-color-picker' ), false, true );

    //enequeue css page for coming soon plugin admin panel
    wp_enqueue_style('dashboard');
    wp_enqueue_style('wp-color-picker');
    wp_enqueue_style('style-option', WBR_EM_PLUGIN_URL.'css/easy_maintenance_mode_panel_style.css');
    wp_enqueue_style('my-bootstrap', WBR_EM_PLUGIN_URL.'css/easy_maintenance_mode_bootstrap.css');
    wp_enqueue_style('tooltip', WBR_EM_PLUGIN_URL.'css/easy_maintenance_mode_tooltip.css');
    //wp_enqueue_style('jquery-ui', WBR_EM_PLUGIN_URL.'css/easy_maintenance_mode_jquery-ui.css');
    wp_enqueue_style('thickbox');
   
}


//custom admin css for admin menu bar 
function load_custom_easy_maintenance_mode_wp_admin_style() 
{
    wp_register_style( 'custom_wbr_admin_css', WBR_EM_PLUGIN_URL.'/css/wbr-admin-menu.css', false, '1.0.0' );
    wp_enqueue_style( 'custom_wbr_admin_css' );
}
add_action( 'admin_enqueue_scripts', 'load_custom_easy_maintenance_mode_wp_admin_style' );
	

//admin bar menu function hook
add_action('admin_bar_menu', 'easy_maintenance_mode_custom_menu', 1000);
function easy_maintenance_mode_custom_menu()
{
    global $wp_admin_bar;
	$value=get_option('easy_maintenance_mode_general_settings');
    if($value['status']=='0') return;
    // Add Parent Menu
	if($value['status']=='1'){
    $argsParent=array(
        'id' => 'wbrmenu',
        'title' => 'Maintenance Mode Enable',
		 //'parent' => 'top-secondary',
        'href' => '?page=easy-maintenance-mode',
		'meta'   => array( 'class' => 'wbr-admin-menu' ),
    );
    $wp_admin_bar->add_menu($argsParent);
	}
	 
}


/*
* Webriti coming soon pro plugin admin page function to create plugin panel
*/
function easy_maintenance_mode_plugin_construction()
{  
    require "theme_options/webriti_option_pannel.php"; 
	
}


/*
* Includes all option panel scripts
*/      
add_action('admin_enqueue_scripts', 'easy_maintenance_admin_enqueue_script');
function easy_maintenance_admin_enqueue_script($hook)
{ 
  if ($hook == 'toplevel_page_easy-maintenance-mode')
    {

      wp_enqueue_script('easy_maintenance-tab',plugin_dir_url( __FILE__ ).'theme_options/js/option-panel-js.js',array('media-upload','jquery-ui-sortable'));    
      wp_enqueue_style('easy_maintenance-option',plugin_dir_url( __FILE__ ).'theme_options/css/style-option.css');

      wp_enqueue_style('easy_maintenance-optionpanal-dragdrop',plugin_dir_url( __FILE__ ).'theme_options/css/optionpanal-dragdrop.css');
      
      //Custom plugin
      wp_enqueue_script('easy_maintenance_admin_js',plugin_dir_url( __FILE__ ).'theme_options/js/my-custom.js');
      wp_enqueue_script ('wff_custom_wp_admin_js');
      wp_enqueue_script('eif_custom_wp_admin_js',plugin_dir_url( __FILE__ ).'theme_options/js/my-custom.js',array('jquery','jquery-ui-tabs'));
      
      //css
      wp_register_style ('wff_custom_wp_admin_css',plugin_dir_url( __FILE__ ). 'theme_options/css/wff-admin.css');
      wp_enqueue_style( 'wff_custom_wp_admin_css' );
  
  }
} 

 
/*template redirect */
require_once('redirect.php');

?>