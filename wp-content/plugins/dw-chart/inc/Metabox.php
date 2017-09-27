<?php

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

class DW_Chart_MetaBox {
	public function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'add_metabox' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_script' ) );
		add_action( 'save_post', array( $this, 'save' ), 10, 2 );

		add_action( 'dw_chart_metabox_layout_general', array( $this, 'general_tabs' ) );
		add_action( 'dw_chart_metabox_layout_geo_chart', array( $this, 'geo_chart_tabs' ) );
	}

	public function admin_enqueue_script() {
		$dw_chart = dw_chart();
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'image-picker', $dw_chart->assets_uri . 'js/image-picker.min.js', array(), $dw_chart->version );
		wp_enqueue_script( 'spectrum', $dw_chart->assets_uri . 'js/spectrum.js', array(), $dw_chart->version );
		wp_enqueue_style( 'image-picker', $dw_chart->assets_uri . 'css/image-picker.css', array(), $dw_chart->version );
		wp_enqueue_style( 'spectrum', $dw_chart->assets_uri . 'css/spectrum.css', array(), $dw_chart->version );
		wp_enqueue_script( 'google_script', 'https://www.gstatic.com/charts/loader.js' );
		wp_enqueue_script( 'dw_chart_script', $dw_chart->assets_uri . 'js/admin.js', array(), $dw_chart->version );
		wp_enqueue_style( 'dw_chart_style', $dw_chart->assets_uri . 'css/admin.css', array(), $dw_chart->version );

		$localize = array(
			'example_uri' => $dw_chart->example_uri,
			'example_dir' => $dw_chart->example_dir
		);

		wp_localize_script( 'dw_chart_script', 'dw_chart', $localize );
	}

	public function add_metabox() {
		add_meta_box( 'dw_chart_preview', __( 'Chart Preview', 'dwgc' ), array( $this, 'preview' ), 'dw_chart', 'normal', 'high' );
		add_meta_box( 'dw_chart_settings', __( 'Chart Settings', 'dwgc' ), 'DW_Chart_Settings_Meta_Box::output', 'dw_chart', 'normal', 'high' );
		add_meta_box( 'dw_chart_data', __( 'Chart Data', 'dwgc' ), 'DW_Chart_Data_Meta_Box::output', 'dw_chart', 'normal', 'high' );
		add_meta_box( 'dw_advanced_settings', __( 'Advanced Settings', 'dwgc' ), 'DW_Chart_Advanced_Settings_Meta_Box::output', 'dw_chart', 'normal', 'high' );
		add_meta_box( 'dw_other_field', __( 'Other', 'dwgc' ), array( $this, 'other_metabox' ), 'dw_chart', 'side' );
	}

	public function preview() {
		?>
		<div id="google_chart_preview">
			<div id="dwgc_chart_preview"></div>
		</div>
		<?php
	}

	public function other_metabox() {
		printf( __( 'You can insert the shortcode <code>[dw_chart id="%1$s"]</code> into your posts by using either hand or editor button.<br><br> You can find %2$sDocuments%3$s. We provide support on our %4$ssupport page%3$s on DesignWall.', 'dwgc' ), get_the_ID(), '<a target="_blank" href="https://www.designwall.com/guide/dw-chart">', '</a>', '<a target="_blank" href="https://www.designwall.com/question/">' );
	}

	public function save( $post_id, $post ) {
		
		if ( empty( $post_id ) || empty( $post ) ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) || is_int( wp_is_post_revision( $post ) ) || is_int( wp_is_post_autosave( $post ) ) ) {
			return;
		}

		if ( empty( $_POST['post_ID'] ) || $_POST['post_ID'] != $post_id ) {
			return;
		}

		if ( !current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		if ( get_post_type( $post_id ) !== 'dw_chart' ) {
			return;
		}

		if ( isset( $_POST['dw_chart_options'] ) ) {
			$options = $_POST['dw_chart_options'];
			update_post_meta( $post_id, 'dw_chart_options', $options );
		}
	}
}
