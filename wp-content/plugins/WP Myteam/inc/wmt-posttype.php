<?php 
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
 * Class : Wp_Wmt_Post_Type
 *
 * @since  1.0.0
 * @access public
 */
if ( ! class_exists( 'Wp_Wmt_Post_Type' ) ) :
    class Wp_Wmt_Post_Type {

    	/**
		 * Constructor
		 */
		public function __construct() {
			add_action( 'init', array( $this, 'register_post_types' ), 0 );
		}

		public function register_post_types(){
			if ( post_type_exists( "wmt-myteam" ) )
			return;

			/**
			* Registers a new post type
			* @uses $wp_post_types Inserts new post type object into the list
			*
			* @param string  Post type key, must not exceed 20 characters
			* @param array|string  See optional args description above.
			* @return object|WP_Error the registered post type object, or an error object
			*/
			
			$labels = array(
				'name'                => __( 'My Team', 'wpwmt' ),
				'singular_name'       => __( 'My Team', 'wpwmt' ),
				'add_new'             => _x( 'Add New My Team', 'wpwmt', 'wpwmt' ),
				'add_new_item'        => __( 'Add New My Team', 'wpwmt' ),
				'edit_item'           => __( 'Edit My Team', 'wpwmt' ),
				'new_item'            => __( 'New My Team', 'wpwmt' ),
				'view_item'           => __( 'View My Team', 'wpwmt' ),
				'search_items'        => __( 'Search My Team', 'wpwmt' ),
				'not_found'           => __( 'No My Team found', 'wpwmt' ),
				'not_found_in_trash'  => __( 'No My Team found in Trash', 'wpwmt' ),
				'parent_item_colon'   => __( 'Parent My Team:', 'wpwmt' ),
				'menu_name'           => __( 'My Team', 'wpwmt' ),
			);
		
			$args = array(
				'labels'                   => $labels,
				'hierarchical'        => false,
				'description'         => 'description',
				'taxonomies'          => array(),
				'public'              => true,
				'show_ui'             => true,
				'show_in_menu'        => true,
				'show_in_admin_bar'   => true,
				'menu_position'       => null,
				'menu_icon'           => wp_wmt_myteam()->img_uri . 'favicon.ico',
				'show_in_nav_menus'   => true,
				'publicly_queryable'  => false,
				'exclude_from_search' => true,
				'has_archive'         => true,
				'query_var'           => true,
				'can_export'          => true,
				'rewrite'             => true,
				'capability_type'     => 'post',
				'supports'            => array(
											'title'
											)
			);

			register_post_type( 'wmt-myteam', $args );
		}
    }
endif;