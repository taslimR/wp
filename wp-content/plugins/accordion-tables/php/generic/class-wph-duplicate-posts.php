<?php
/**
 * This class helps duplicate posts - creates the duplicating mechanism and provides a duplication button
 */	
class WPH_Duplicate_Posts {

public $post_type;

// duplicates the post that was indicated by user
function duplicate (){

	// check if we need to proceed
	global $wpdb;
	if (!( isset( $_REQUEST['post_type']) && $_REQUEST['post_type'] === $this->post_type && isset($_REQUEST['wph_duplicate']) ) ) return;

	// get the original post id
	$post_id = $_REQUEST['post_id'];
	// and all the original post data then
	$post = get_post( $post_id );

	// if post data exists, create the post duplicate
	if (isset( $post ) && $post != null) { 
		// new post data array
		$args = array(
			'comment_status' => $post->comment_status,
			'ping_status'    => $post->ping_status,
			'post_author'    => $post->post_author,
			'post_content'   => $post->post_content,
			'post_excerpt'   => $post->post_excerpt,
			'post_name'      => $post->post_name,
			'post_parent'    => $post->post_parent,
			'post_password'  => $post->post_password,
			'post_status'    => $post->post_status,
			'post_title'     => $post->post_title." ".__('Copy'),
			'post_type'      => $post->post_type,
			'to_ping'        => $post->to_ping,
			'menu_order'     => $post->menu_order
		);

		// insert the post by wp_insert_post() function
		$new_post_id = wp_insert_post( $args );
 
		// get all current post terms ad set them to the new post draft
		$taxonomies = get_object_taxonomies($post->post_type); // returns array of taxonomy names for post type, ex array("category", "post_tag");
		foreach ($taxonomies as $taxonomy) {
			$post_terms = wp_get_object_terms($post_id, $taxonomy, array('fields' => 'slugs'));
			wp_set_object_terms($new_post_id, $post_terms, $taxonomy, false);
		}

		// duplicate all post meta
		$post_meta_infos = $wpdb->get_results("SELECT meta_key, meta_value FROM $wpdb->postmeta WHERE post_id=$post_id");
		if (count($post_meta_infos)!=0) {
			$sql_query = "INSERT INTO $wpdb->postmeta (post_id, meta_key, meta_value) ";
			foreach ($post_meta_infos as $meta_info) {
				$meta_key = $meta_info->meta_key;
				$meta_value = addslashes($meta_info->meta_value);
				$sql_query_sel[]= "SELECT $new_post_id, '$meta_key', '$meta_value'";
			}
			$sql_query.= implode(" UNION ALL ", $sql_query_sel);
			$wpdb->query($sql_query);
		}
		
		//redirect to change url duplicate keeps when trash is clicked
		$url = remove_query_arg( array('wph_duplicate'), false );
		wp_safe_redirect( $url, $status );
		exit;

	}
}

// add the duplicate button to target post type
function duplicate_button( $actions, $post ) {
	if (current_user_can('edit_posts') && isset($_REQUEST['post_type']) && $_REQUEST['post_type'] === $this->post_type) {
		$path = "edit.php?post_type=$this->post_type&post_id=$post->ID&wph_duplicate=true";
		$url = admin_url($path);
		$actions['wph_duplicate'] = '<a href="' . $url . '" title="'.__('Copy').'" rel="permalink">'.__('Copy').'</a>';
	}
	return $actions;
}

function __construct($post_type) {
	$this->post_type = $post_type;
	add_action( 'admin_init', array($this, 'duplicate'));
	add_filter( 'page_row_actions', array($this, 'duplicate_button'), 10, 2 );
	
}

}
?>