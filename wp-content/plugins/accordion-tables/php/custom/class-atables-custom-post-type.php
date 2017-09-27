<?php

class aTables_Custom_Post_Type {

	public $post_type;
	public $plural;
	public $single;
	public $description;
	public $icon_url;

	public function __construct ( $post_type = '', $plural = '', $single = '', $description = '', $icon_url ) {

		if( ! $post_type || ! $plural || ! $single ) return;

		// Post type name and labels
		$this->post_type = $post_type;
		$this->plural = $plural;
		$this->single = $single;
		$this->description = $description;
		$this->icon_url = $icon_url;
		

		// Regsiter post type
		add_action( 'init' , array( $this, 'register_post_type' ) );

		// Display custom update messages for posts edits
		add_filter( 'post_updated_messages', array( $this, 'updated_messages' ) );
		add_filter( 'bulk_post_updated_messages', array( $this, 'bulk_updated_messages' ), 10, 2 );
		
		//ajax for saving post title
		add_action('wp_ajax_wph_save_title', array( $this, 'save_title'));

	}

	/**
	 * Register new post type
	 * @return void
	 */
	public function register_post_type () {

		$labels = array(
			'name' => $this->plural,
			'singular_name' => $this->single,
			'name_admin_bar' => $this->single,
			'add_new' => _x( 'Add New', $this->post_type , 'accordion-tables' ),
			'add_new_item' => sprintf( __( 'Add New %s' , 'accordion-tables' ), $this->single ),
			'edit_item' => sprintf( __( 'Edit %s' , 'accordion-tables' ), $this->single ),
			'new_item' => sprintf( __( 'New %s' , 'accordion-tables' ), $this->single ),
			'all_items' => sprintf( __( 'All %s' , 'accordion-tables' ), $this->plural ),
			'view_item' => sprintf( __( 'View %s' , 'accordion-tables' ), $this->single ),
			'search_items' => sprintf( __( 'Search %s' , 'accordion-tables' ), $this->plural ),
			'not_found' =>  sprintf( __( 'No %s Found' , 'accordion-tables' ), $this->plural ),
			'not_found_in_trash' => sprintf( __( 'No %s Found In Trash' , 'accordion-tables' ), $this->plural ),
			'parent_item_colon' => sprintf( __( 'Parent %s' ), $this->single ),
			'menu_name' => $this->plural,
		);

		$args = array(
			'labels' => apply_filters( $this->post_type . '_labels', $labels ),
			'description' => $this->description,
			'public' => true,
			'publicly_queryable' => true,
			'exclude_from_search' => false,
			'show_ui' => true,
			'show_in_menu' => true,
			'show_in_nav_menus' => true,
			'query_var' => true,
			'can_export' => true,
			'rewrite' => true,
			'capability_type' => 'post',
			'has_archive' => true,
			'hierarchical' => true,
			'supports' => array( 'title', 'editor', 'excerpt', 'comments', 'thumbnail' ),
			'menu_position' => 5,
			'menu_icon' => $this->icon_url,
		);

		register_post_type( $this->post_type, apply_filters( $this->post_type . '_register_args', $args, $this->post_type ) );
	}

	/**
	 * Set up admin messages for post type
	 * @param  array $messages Default message
	 * @return array           Modified messages
	 */
	public function updated_messages ( $messages = array() ) {
	  global $post, $post_ID;

	  $messages[ $this->post_type ] = array(
	    0 => '',
	    1 => sprintf( __( '%1$s updated. %2$sView %3$s%4$s.' , 'accordion-tables' ), $this->single, '<a href="' . esc_url( get_permalink( $post_ID ) ) . '">', $this->single, '</a>' ),
	    2 => __( 'Custom field updated.' , 'accordion-tables' ),
	    3 => __( 'Custom field deleted.' , 'accordion-tables' ),
	    4 => sprintf( __( '%1$s updated.' , 'accordion-tables' ), $this->single ),
	    5 => isset( $_GET['revision'] ) ? sprintf( __( '%1$s restored to revision from %2$s.' , 'accordion-tables' ), $this->single, wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
	    6 => sprintf( __( '%1$s published. %2$sView %3$s%4s.' , 'accordion-tables' ), $this->single, '<a href="' . esc_url( get_permalink( $post_ID ) ) . '">', $this->single, '</a>' ),
	    7 => sprintf( __( '%1$s saved.' , 'accordion-tables' ), $this->single ),
	    8 => sprintf( __( '%1$s submitted. %2$sPreview post%3$s%4$s.' , 'accordion-tables' ), $this->single, '<a target="_blank" href="' . esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ) . '">', $this->single, '</a>' ),
	    9 => sprintf( __( '%1$s scheduled for: %2$s. %3$sPreview %4$s%5$s.' , 'accordion-tables' ), $this->single, '<strong>' . date_i18n( __( 'M j, Y @ G:i' , 'accordion-tables' ), strtotime( $post->post_date ) ) . '</strong>', '<a target="_blank" href="' . esc_url( get_permalink( $post_ID ) ) . '">', $this->single, '</a>' ),
	    10 => sprintf( __( '%1$s draft updated. %2$sPreview %3$s%4$s.' , 'accordion-tables' ), $this->single, '<a target="_blank" href="' . esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ) . '">', $this->single, '</a>' ),
	  );

	  return $messages;
	}

	/**
	 * Set up bulk admin messages for post type
	 * @param  array  $bulk_messages Default bulk messages
	 * @param  array  $bulk_counts   Counts of selected posts in each status
	 * @return array                Modified messages
	 */
	public function bulk_updated_messages ( $bulk_messages = array(), $bulk_counts = array() ) {

		$bulk_messages[ $this->post_type ] = array(
	        'updated'   => sprintf( _n( '%1$s %2$s updated.', '%1$s %3$s updated.', $bulk_counts['updated'], 'accordion-tables' ), $bulk_counts['updated'], $this->single, $this->plural ),
	        'locked'    => sprintf( _n( '%1$s %2$s not updated, somebody is editing it.', '%1$s %3$s not updated, somebody is editing them.', $bulk_counts['locked'], 'accordion-tables' ), $bulk_counts['locked'], $this->single, $this->plural ),
	        'deleted'   => sprintf( _n( '%1$s %2$s permanently deleted.', '%1$s %3$s permanently deleted.', $bulk_counts['deleted'], 'accordion-tables' ), $bulk_counts['deleted'], $this->single, $this->plural ),
	        'trashed'   => sprintf( _n( '%1$s %2$s moved to the Trash.', '%1$s %3$s moved to the Trash.', $bulk_counts['trashed'], 'accordion-tables' ), $bulk_counts['trashed'], $this->single, $this->plural ),
	        'untrashed' => sprintf( _n( '%1$s %2$s restored from the Trash.', '%1$s %3$s restored from the Trash.', $bulk_counts['untrashed'], 'accordion-tables' ), $bulk_counts['untrashed'], $this->single, $this->plural ),
	    );

	    return $bulk_messages;
	}
	
	/**
	 * Save title
	 */
	function save_title () {
		$id = $_REQUEST['post-id'];
		$title = $_REQUEST['title'];
		if ($id && $title) {
			wp_update_post(
					array (
					  'ID'            => $id, 
					  'post_title'    => $title,
					  'post_status' => 'publish',
			));

		}
		die( );
	}

}
?>