<?php
/*
Plugin Name: UberChart
Description: Generates simple or complex charts easily in WordPress.
Version: 1.10
Author: DAEXT
Author URI: http://daext.com
*/

//Prevent direct access to this file
if ( ! defined( 'WPINC' ) ) {
	die();
}

//Shared across public and admin
require_once( plugin_dir_path( __FILE__ ) . 'shared/class-dauc-shared.php' );
require_once( plugin_dir_path( __FILE__ ) . '/vendor/autoload.php' );

require_once( plugin_dir_path( __FILE__ ) . 'public/class-dauc-public.php' );
add_action( 'plugins_loaded', array( 'Dauc_Public', 'get_instance' ) );

//Admin
if ( is_admin() && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) ) {

	//Admin
	require_once( plugin_dir_path( __FILE__ ) . 'admin/class-dauc-admin.php' );
	add_action( 'plugins_loaded', array( 'Dauc_Admin', 'get_instance' ) );

	//Activate
	register_activation_hook( __FILE__, array( Dauc_Admin::get_instance(), 'ac_activate' ) );

}

//Ajax
if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {

	//Admin
	require_once( plugin_dir_path( __FILE__ ) . 'class-dauc-ajax.php' );
	add_action( 'plugins_loaded', array( 'Dauc_Ajax', 'get_instance' ) );

}