<?php 
if ( !class_exists('wp_preloader_admin', false) ) {
	class wp_preloader_admin {
		
		/**
		 * @TODO Add class constructor description.
		 */
		public function __construct() {
			// Register admin submenu
			add_action( 'admin_menu', array( $this, 'wp_preloader_submenu' ) );
			// Register settings
			add_action( 'admin_init', array( $this, 'wp_preloader_register_settings' ) );
			// Register color scripts
			add_action( 'admin_enqueue_scripts', array( $this, 'wp_preloader_color_picker' ) );
		}

		
		/**
		 * Add an admin submenu link under Settings
		 */
		public function wp_preloader_submenu() {
		     add_submenu_page(
		          'options-general.php',       
		          __( 'WP Preloader', 'wppreloader' ), 
		          __( 'WP Preloader', 'wppreloader'  ), 
		          'manage_options',            
		          'wppreloader_options',       
		          array($this, 'wp_preloader_options_page') 
		     );
		}


		/**
		 * Register the settings
		 */
		public function wp_preloader_register_settings() {
    		 register_setting("section", "img-file", array($this, "wp_preloader_file_upload") );
		   	 register_setting( 'section', 'bg-color' );
		}
		

		/**
		 * Build the options page: menu callback
		 */
		public function wp_preloader_options_page() {
		    // Render the settings template
		    include plugin_dir_path( __FILE__ ) . '../includes/settings.php';
		}


		/**
		 * Preloader color picker.
		 */
		function wp_preloader_color_picker( $hook_suffix ) {
		    // first check that $hook_suffix is appropriate for your admin page
		    wp_enqueue_style( 'wp-color-picker' );
		    wp_enqueue_script( 'admin-script-handle', plugins_url('js/admin-script.js', __FILE__ ), array( 'wp-color-picker' ), false, true );
		}


		/**
		 * Uplaod animated image
		 */
		function wp_preloader_file_upload($option)
		{
			if ( ! function_exists( 'wp_handle_upload' ) ) {
			    require_once( ABSPATH . 'wp-admin/includes/file.php' );
			}

			$uploadedfile = $_FILES['img-file'];
			$upload_overrides = array( 'test_form' => false );
			$movefile = wp_handle_upload( $uploadedfile, $upload_overrides );

			if ( $movefile && ! isset( $movefile['error'] ) ) {
			    $temp = $movefile["url"];
		    	return $temp;
			}

			if ( get_option( 'img-file' ) )
			   return get_option('img-file');
			else 
			   return $option;
		}
		
	}

}