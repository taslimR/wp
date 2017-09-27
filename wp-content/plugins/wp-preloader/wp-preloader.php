<?php 
/*
Plugin Name: WP Preloader
Plugin URI: http://www.pranms.com/wp-preloader/
Description: WP Preloader is a simple and customizable wordpress preloader plugin.Very easy to use.
Author: Mamunur Rashid
Version: 1.0
Author URI: https://profiles.wordpress.org/mamunitiw/
License: GPL2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

// blocking direct access
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

do_action( 'plugins_loaded', 'wp_preloader_load', 25 );

if ( !function_exists( 'wp_preloader_load' ) ) {
	function wp_preloader_load() {
		load_plugin_textdomain( 'wp_preloader', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
		new wp_preloader();
	}
}

if ( !class_exists('wp_preloader', false) ) {
	class wp_preloader {
		
		/**
		 * @TODO Add class constructor description.
		 */
		public function __construct() {
			if ( is_admin() ) {
		    	// We are in admin mode
		    	require plugin_dir_path( __FILE__ ) . 'admin/wp-preloader-admin.php';
				new wp_preloader_admin();
			}
			// Register style sheet.
			add_action( 'wp_enqueue_scripts', array( $this, 'register_plugin_styles' ) );
			// Register script.
			add_action( 'wp_enqueue_scripts', array( $this, 'register_plugin_scripts' ) );
		}


		/**
		 * Register and enqueue style sheet.
		 */
		public function register_plugin_styles() {
			wp_register_style( 'wp-preloader-style', plugins_url( 'wp-preloader/css/style.css' ) );
			wp_enqueue_style( 'wp-preloader-style' );
		}


		/**
		 * Register and enqueue script.
		 */
		public function register_plugin_scripts() {
			wp_register_script( 'wp-preloader-script', plugins_url( 'wp-preloader/js/script.js' ), array('jquery'), 1.0 );
    		wp_enqueue_script( 'wp-preloader-script' );
		}


		/**
		 * Preloader html code.
		 */
		public function preloader_document_object() {
			require plugin_dir_path( __FILE__ ) . 'includes/content.php';
		}
		
	}

}

// shortcode: [new_loader]
$new_loader  = new wp_preloader();
add_shortcode( 'new_loader', array( $new_loader, 'preloader_document_object') );