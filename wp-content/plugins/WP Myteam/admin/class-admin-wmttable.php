<?php 
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
 * Class : Admin_Wmt_Pricetable
 *
 * @since  1.0.0
 * @access public
 */
if ( ! class_exists( 'Admin_Wmt_Pricetable' ) ) :
    class Admin_Wmt_Pricetable {

		/**
		 * Texonomy Meta Data Page
		 *
		 * @since  1.0.0
         * @access public
         * @return void
		 */
		public function wmt_setting_page() { 

			// Add Setting page
			add_menu_page( __( 'My Team', 'wpwmt' ), __( 'My Team', 'wpwmt' ), 'manage_options', 'wmt_setting', array( $this, 'wmt_setting_html' ), wp_wmt_myteam()->img_uri . 'favicon.ico' );

		}

		/**
		 * Enqueue scripts and styles for the front end.
		 *
		 * @since  1.0.0
         * @access public
         * @return void
		 */
		public function wmt_setting_html(){
			// Check file availability
			if( file_exists( wp_wmt_myteam()->admin_dir.'forms/wmt-setting-form.php' )){
				include_once( wp_wmt_myteam()->admin_dir.'forms/wmt-setting-form.php' );
			}
			
		}

		/**
		 * Validate Options
		 *
		 * @since  1.0.0
         * @access public
         * @return array
		 */
		public function wmt_validate_options( $wmt_option ){
			$wmt_option['wmt_title'] = isset( $wmt_option['wmt_title'] ) ? sanitize_text_field( $wmt_option['wmt_title'] ) : "";
			$wmt_option['wmt_currency_symbol'] = isset( $wmt_option['wmt_currency_symbol'] ) ? sanitize_text_field( $wmt_option['wmt_currency_symbol'] ) : "";
			$wmt_option['wmt_booknow'] = isset( $wmt_option['wmt_booknow'] ) ? sanitize_text_field( $wmt_option['wmt_booknow'] ) : "";
			$wmt_option['heading_1'] = isset( $wmt_option['heading_1'] ) ? sanitize_text_field( $wmt_option['heading_1'] ) : "";
			$wmt_option['heading_2'] = isset( $wmt_option['heading_2'] ) ? sanitize_text_field( $wmt_option['heading_2'] ) : "";
			$wmt_option['heading_3'] = isset( $wmt_option['heading_3'] ) ? sanitize_text_field( $wmt_option['heading_3'] ) : "";
			$wmt_option['heading_4'] = isset( $wmt_option['heading_4'] ) ? sanitize_text_field( $wmt_option['heading_4'] ) : "";
			$wmt_option['heading_5'] = isset( $wmt_option['heading_5'] ) ? sanitize_text_field( $wmt_option['heading_5'] ) : "";
			$wmt_option['heading_6'] = isset( $wmt_option['heading_6'] ) ? sanitize_text_field( $wmt_option['heading_6'] ) : "";
			return $wmt_option;
		}
		
		/**
		 * Admin enqueue script
		 *
		 * @since 1.0.0
		 * @access public
		 * @return void
		 */
		public function wmt_admin_script( $hook_suffix ) {
			global $wp_version, $post_type;
			
			if( $post_type == 'wmt-myteam'){
				// Admin Style
				
				wp_enqueue_style( 'admin-wmt-custom-style', wp_wmt_myteam()->css_uri . 'admin-wmt-custom.css' );
				wp_enqueue_style( 'admin-wmt-font-awesome', wp_wmt_myteam()->css_uri . 'font-awesome.css' );
				wp_enqueue_style( 'admin-jquery-min-css', wp_wmt_myteam()->css_uri . 'jquery-ui.min.css' );
				// Admin Script
				wp_enqueue_script( 'jquery' );
				wp_enqueue_script( 'jquery-ui-dialog' );

				wp_enqueue_script( 'admin-wmt-custom-formscript', wp_wmt_myteam()->js_uri . 'admin-wmt-custom.js', array('jquery','jquery-ui-dialog' ), wp_wmt_myteam()->version, true );
				//localize script
				$newui = $wp_version >= '3.5' ? '1' : '0'; //check wp version for showing media uploader
				wp_localize_script( 'admin-wmt-custom-formscript', 'WMTSettings', array( 
																						'new_media_ui'	=>	$newui,
				          															));
				//for new media uploader
				wp_enqueue_media();
			}
			wp_enqueue_script('tinymce' );
			wp_enqueue_script('wmt-iframe-scripts' , wp_wmt_myteam()->js_uri ."wmt-editor.js", array( 'jquery','tinymce' ), false, false);
		}

		/**
		 *
		 * WMT metabox
		 * Shortcode generated after saving post
		 *
		*/
		public function wmt_meta_boxes_setup(){
			add_meta_box(
				'wmt_shortcode',
				__( 'My Team Shortcode', 'wpwmt' ),
				array( $this, 'wmt_shortcode_meta_box' ),
				array('wmt-myteam'),
				'side',
				'core'
			); 
		}
		
		/**
		 *
		 * WP My Team shortcode metabox
		 * Shortcode generated after saving post
		 *
		*/
		public function wmt_shortcode_meta_box() {
			
			global $post;
			
			$post_id = $post->ID;

			$output = '<p id="shortcode-container">';
					$output .= '<span><b>'. __('Shortcode for your slider ', 'wpwmt').': </b></span>';	
					$output .= '[wmt_myteam id="'.$post_id.'"]';
			$output .= '</p>';
			
			$output .= '<div class="list-row split">';
			$output .= '</div>';
			echo $output;
		}

		/**
		 * Remove view Link on WP Admin Bar
		 *
		 * @access public
		 * @since 1.0.0
		 * @return void
		 */
		public function wmt_remove_admin_bar_links( ) {
			global $wp_admin_bar, $post, $pagenow;
			
			$page_arg = array(
				'post.php',
				'post-new.php' 
			);
			
			if ( in_array( $pagenow, $page_arg ) ) {
				if ( $post->post_type == 'wmt-myteam' )
					$wp_admin_bar->remove_menu( 'view' );
			}
		}

		/**
         * Add Custom Metabox
         *
         * @since  1.0.0
         * @access public
         * @return void
         */
		public function wmt_custom_meta_boxes(){
			add_meta_box( 
		        'wmt-myteam-metabox',
		        __( 'Wp My Team', 'wpwmt' ),
		        array( $this, 'wmt_render_metabox' ),
		        'wmt-myteam',
		        'normal',
		        'default'
		    );
		}

		/**
         * Display Metabox HTML
         *
         * @since  1.0.0
         * @access public
         * @return void
         */
		public function wmt_render_metabox(){
			// Check file availability
			if( file_exists( wp_wmt_myteam()->admin_dir.'forms/wmt-setting-form.php' )){
				include_once( wp_wmt_myteam()->admin_dir.'forms/wmt-setting-form.php' );
			}
		}

    	/**
         * Loads functions
         *
         * @since  1.0.0
         * @access public
         * @return void
         */
    	public function wmt_save_post( $post_id, $post ){
    		//Validate Post 
    		if ( empty( $post_id ) || empty( $post ) || empty( $_POST ) ) return;
			if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) return;
			if ( is_int( wp_is_post_revision( $post ) ) ) return;
			if ( is_int( wp_is_post_autosave( $post ) ) ) return;
			if ( empty($_POST['wmt_nonce_field']) || ! wp_verify_nonce( $_POST['wmt_nonce_field'], 'wmt_nonce_action' ) ) return;
			if ( ! current_user_can( 'edit_post', $post_id ) ) return;
			if ( $post->post_type != 'wmt-myteam' ) return;

			$wmt_option = isset( $_POST['wmt_option'] ) ? $_POST['wmt_option'] : ""; 
			$wmt_option['wmt_title'] = isset( $wmt_option['wmt_title'] ) ? sanitize_text_field( $wmt_option['wmt_title'] ) : "";
			
			for( $i=1; $i < 5 ; $i++ ){
				$wmt_option['name_'.$i] = isset( $wmt_option['name_'.$i] ) ? sanitize_text_field( $wmt_option['name_'.$i] ) : "";	
				$wmt_option['designation_'.$i] = isset( $wmt_option['designation_'.$i] ) ? sanitize_text_field( $wmt_option['designation_'.$i] ) : "";	
				$wmt_option['detail_'.$i] = isset( $wmt_option['detail_'.$i] ) ? sanitize_text_field( $wmt_option['detail_'.$i] ) : "";	
				$wmt_option['email_'.$i] = isset( $wmt_option['email_'.$i] ) ? sanitize_text_field( $wmt_option['email_'.$i] ) : "";	
				$wmt_option['facebook_'.$i] = isset( $wmt_option['facebook_'.$i] ) ? sanitize_text_field( $wmt_option['facebook_'.$i] ) : "";	
				$wmt_option['twitter_'.$i] = isset( $wmt_option['twitter_'.$i] ) ? sanitize_text_field( $wmt_option['twitter_'.$i] ) : "";	
				$wmt_option['linkedin_'.$i] = isset( $wmt_option['linkedin_'.$i] ) ? sanitize_text_field( $wmt_option['linkedin_'.$i] ) : "";	
			}
			
			// Update Post metadata_exists( $meta_type, $object_id, $meta_key )
			update_post_meta( $post_id, 'wmt_myteam', $wmt_option );
    		
    	}

    	/**
         * Click Price table post type columns
         *
         * @since  1.0.0
         * @access public
         * @return void
         */	
    	public function wmt_display_column( $columns, $p_id ){
    		if ( $columns == 'wmt_shortcode')
          		echo '[wmt-myteam id="'.$p_id.'"]';
    	}
    	/**
         * Click Price table post type columns
         *
         * @since  1.0.0
         * @access public
         * @return void
         */	
    	public function wmt_cpt_columns( $columns ){
    		unset( $columns['date'] );
    		$columns["wmt_shortcode"] = __( "Shortcode",'wpwmt');
    		$columns["date"] = "Date";
    		return $columns;
    	}

    	/**
		 * Register Buttons
		 *
		 * Register the different content locker buttons for the editor
		 *
		 * @since 1.0.0
		 */
		public function wmt_tinymce_manager() {
			
			if ( ! current_user_can('edit_posts') && ! current_user_can('edit_pages') ) {
					return;
			}
		 
			if ( get_user_option( 'rich_editing' ) == 'true' ) {
				add_filter( 'mce_buttons', array( $this, 'wmt_iframe_register_button') );	
				add_filter( 'mce_external_plugins',  array( $this, 'wmt_iframe_popup_button' ) );	     
			}
		}
		/**
		 * Register button in tinymce
		 */
		public function wmt_iframe_register_button( $buttons ) {	

		 	array_push( $buttons, "|", "wmtmyteamtable" );
		 	return $buttons;	 	
		}
		/**
		 * Tinymce Button supported script enqueue
		 */
		public function wmt_iframe_popup_button( $plugin_array ) {
		  
		   wp_enqueue_script( 'tinymce' );
		   
		   $plugin_array['wmtmyteamtable'] = wp_wmt_myteam()->js_uri ."wmt-editor.js";

		  return $plugin_array;
		 }
		/**
		 * Footer HTML for add tinymce data
		 */
		 public function wmt_footer_html(){

			$wmt_ids    = get_posts( array( 	'posts_per_page'=> -1,
												'post_type' => 'wmt-myteam',
												'fields'    => 'ids'));
			
		 	?>
		 	<select id="wmt-ids" class="hidden">
		 		<?php 
		 		if( count( $wmt_ids ) ):
		 			foreach( $wmt_ids as $ids ): ?>
		 			<option value="<?php echo $ids; ?>">My Team Table <?php echo $ids; ?></option>
		 			<?php endforeach; 
		 		endif;
		 		?>
		 	</select>
		 	<?php
		 }

    	/**
         * Loads functions
         *
         * @since  1.0.0
         * @access public
         * @return void
         */
    	public function admin_hook(){
    		// Add List table shortcode columns
    		add_filter('manage_edit-wmt-myteam_columns', array( $this, 'wmt_cpt_columns') );
			add_filter('manage_edit-wmt-myteam_sortable_columns', array( $this, 'wmt_cpt_columns') );
			// Display Columns data
			add_action('manage_wmt-myteam_posts_custom_column', array( $this, 'wmt_display_column' ), 10, 2);
    		// Add style admin setting page
    		add_action( 'admin_enqueue_scripts', array( $this, 'wmt_admin_script' ) );

    		// Add Metabox
    		add_action( 'add_meta_boxes', array( $this, 'wmt_meta_boxes_setup' ) );

    		// Remove View in Post type
			add_action( 'wp_before_admin_bar_render', array( $this, 'wmt_remove_admin_bar_links') );
			// Add admin Metabox
			add_action( 'add_meta_boxes', array( $this, 'wmt_custom_meta_boxes' ), 10, 2 );
			// Save POST Meta
			add_action( 'save_post', array( $this, 'wmt_save_post' ), 10, 2 );
			// Tinymce Manager
			add_action( 'init' , array( $this, 'wmt_tinymce_manager') );
			// Footer HTML
			add_action( 'admin_footer', array( $this,'wmt_footer_html' ));
			add_action( 'admin_footer', array( $this,'wmt_footer_html' ));
    	}
    }
endif;



