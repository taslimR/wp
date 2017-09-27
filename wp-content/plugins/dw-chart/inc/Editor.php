<?php

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

class DW_Chart_Editor {
	public function __construct() {
		add_filter( 'mce_buttons', array( $this, 'add_button' ) );
		add_filter( 'mce_external_plugins', array( $this, 'register_script' ) );
		add_action( 'wp_ajax_dw_chart_select_chart', array( $this, 'thickbox' ) );
	}

	public function add_button( $buttons ) {
		array_push( $buttons, 'dw_chart' );
		return $buttons;
	}

	public function register_script( $pArray ) {
		$pArray['dw_chart'] = dw_chart()->assets_uri . 'js/tinymce.js';
		return $pArray;
	}

	public function thickbox() {
		$charts = $this->all_chart();
		if ( $charts ) :
			?> <select id="dw_chart_posts"> <?php
			foreach( $charts as $id => $title ) :
				?>
				<option value="<?php echo esc_attr( $id ) ?>"><?php echo esc_html( $title ) ?></option>
				<?php
			endforeach;
			?> 
			</select>
			<button class="button button-primary" id="dw_chart_submit"><?php _e( 'Select', 'dwgc' ) ?></button>
			<?php
		else :
			printf( __( "Please create your chart %shere%s", 'dwgc' ), '<a target="_blank" href="'.admin_url('edit.php?post_type=dw_chart').'">', '</a>' );
		endif;
		?>
		<script type="text/javascript">
			document.getElementById('dw_chart_submit').onclick = function(e){
				e.preventDefault();
				var id = document.getElementById('dw_chart_posts').value;
				tinyMCE.activeEditor.execCommand('mceInsertContent',0, '[dw_chart id="'+id+'"]' );
				tb_remove();
			}
		</script>
		<?php
		die;
	}

	public function all_chart() {
		$args = array(
			'post_type' => 'dw_chart',
			'nopaging' => true,
			'fields' => 'ids',
			'post_status' => 'any',
			'no_found_rows' => true,
			'update_post_term_cache' => false,
			'update_post_meta_cache' => false
		);

		$posts = wp_cache_get( 'dw_chart_get_chart' );

		if ( !$posts ) {
			$query = new WP_Query( $args );
			foreach( $query->posts as $id ) {
				$posts[ $id ] = get_post_field( 'post_title', $id );
			}

			wp_cache_set( 'dw_chart_get_chart', $posts );
		}

		return $posts;
	}
}