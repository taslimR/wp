<?php

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

class DW_Chart_Post {
	public function __construct() {
		add_action( 'init', array( $this, 'register_post' ) );

		add_filter( 'manage_dw_chart_posts_columns', array( $this, 'edit_columns' ) );
		add_filter( 'manage_dw_chart_posts_custom_column', array( $this, 'custom_columns' ), 10, 2 );
	}

	public function register_post() {
		$labels = array(
			'name'								=> __( 'DW Chart', 'dwgc' ),
			'singular_name' 			=> __( 'DW Chart', 'dwgc' ),
			'menu_name'						=> __( 'DW Chart', 'dwgc' ),
			'name_admin_bar' 			=> __( 'DW Chart', 'dwgc' ),
			'add_new'							=> __( 'Add New', 'dwgc' ),
			'add_new_item' 				=> __( 'Add New Chart', 'dwgc' ),
			'new_item'						=> __( 'New Chart', 'dwgc' ),
			'edit_item'						=> __( 'Edit Chart', 'dwgc' ),
			'view_item' 					=> __( 'View Chart', 'dwgc' ),
			'all_items' 					=> __( 'All Charts', 'dwgc' ),
			'search_items' 				=> __( 'Search Charts', 'dwgc' ),
			'parent_item_colon' 	=> __( 'Parent Charts:', 'dwgc' ),
			'not_found' 					=> __( 'No charts found.', 'dwgc' ),
			'not_found_in_trash' 	=> __( 'No charts found in trash', 'dwgc' ),
		);

		$args = array(
			'labels' 							=> $labels,
			'description' 				=> __( 'Description.', 'dwgc' ),
			'public' 							=> false,
			'publicly_queryable' 	=> false,
			'show_ui' 						=> true,
			'exclude_from_search' => true,
			'show_in_nav_menus'		=> false,
			'show_in_menu' 				=> true,
			'query_var' 					=> false,
			'rewrite' 						=> false,
			'capability_type' 		=> 'post',
			'has_archive' 				=> false,
			'hierarchical' 				=> false,
			'menu_icon' 					=> 'dashicons-chart-area',
			'supports' 						=> array( 'title' ),
		);

		register_post_type( 'dw_chart', $args );
	}

	public function edit_columns( $columns ) {
		$new_columns = array();
		$new_columns['cb'] = $columns['cb'];
		$new_columns['title'] = $columns['title'];
		$new_columns['shortcode'] = __( 'Shortcode', 'dwgc' );

		return $new_columns;
	}

	public function custom_columns( $columns, $post_id ) {
		$post = get_post( $post_id );

		switch ( $columns ) {
			case 'shortcode':
				echo '<code>[dw_chart id="'.$post_id.'"]</code>';
				break;
		}
	}
}