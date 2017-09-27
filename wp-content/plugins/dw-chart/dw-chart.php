<?php
/**
 * Plugin Name: DW Chart
 * Description: DW Chart is a WordPress plugin which helps you easily build Google Charts based on the available data imported.
 * Author: DesignWall
 * Author URI: http://www.designwall.com
 * Version: 1.0.0
 * Text Domain: dwgc
 * Domain Path: /languages
 */

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

if ( !class_exists( 'DW_Chart' ) ) :

final class DW_Chart {
	private $_data = array();

	public function __construct() {}

	public function __clone() {
		_doing_it_wrong( __FUNCTION__, __( 'Are you cheating huh?', 'dwgc' ), '1.0.0' );
	}

	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, __( 'Are you cheating huh?', 'dwgc' ), '1.0.0' );
	}

	public function __isset( $key ) {
		return isset( $this->_data[ $key ] );
	}

	public function __unset( $key ) {
		if ( isset( $this->_data[ $key ] ) ) {
			unset( $this->_data[ $key ] );
		}
	}

	public function __set( $key, $value ) {
		$this->_data[ $key ] = $value;
	}

	public function __get( $key ) {
		return isset( $this->_data[ $key ] ) ? $this->_data[ $key ] : null;
	}

	public static function instance() {
		static $_instance = null;

		if ( is_null( $_instance ) ) {
			$_instance = new self();
			$_instance->setup_global();
			$_instance->includes();
			$_instance->setup_actions();
		}

		return $_instance;
	}

	private function setup_global() {
		$this->version = '1.0.0';
		$this->file = __FILE__;

		$this->plugin_dir = trailingslashit( plugin_dir_path( $this->file ) );
		$this->plugin_uri = trailingslashit( plugin_dir_url( $this->file ) );
		$this->template_dir = trailingslashit( get_template_directory() );
		$this->template_uri = trailingslashit( get_template_directory_uri() );
		$this->stylesheet_dir = trailingslashit( get_stylesheet_directory() );
		$this->stylesheet_uri = trailingslashit( get_stylesheet_directory_uri() );

		$this->tpl_dir = $this->plugin_dir . 'template/';
		$this->inc_dir = $this->plugin_dir . 'inc/';
		$this->lib_dir = $this->plugin_dir . 'lib/';
		$this->assets_dir = $this->plugin_dir . 'assets/';
		$this->assets_uri = $this->plugin_uri . 'assets/';
		$this->example_dir = $this->plugin_dir . 'example/';
		$this->example_uri = $this->plugin_uri . 'example/';
	}

	private function includes() {
		include( $this->inc_dir . 'Post.php' );
		include( $this->inc_dir . 'Shortcode.php' );
		include( $this->inc_dir . 'Metabox.php' );
		include( $this->inc_dir . 'Editor.php' );

		// include metabox class
		include( $this->inc_dir . 'metabox/chart-setting.php' );
		include( $this->inc_dir . 'metabox/chart-data.php' );
		include( $this->inc_dir . 'metabox/advanced-setting.php' );
	}

	private function setup_actions() {
		new DW_Chart_Post();
		new DW_Chart_Shortcode();
		new DW_Chart_MetaBox();
		new DW_Chart_Editor();

		add_action( 'init', array( $this, 'init' ) );
	}

	public function init() {
		load_plugin_textdomain( 'dwgc', false,  plugin_basename( dirname( $this->file ) )  . '/languages' );
	}

	public function chart_type() {
		return apply_filters( 'dwc_chart_type', array(
			'area' 						=> __( 'Area', 'dwgc' ),
			'bar' 						=>  __( 'Bar', 'dwgc' ),
			'column' 					=>  __( 'Column', 'dwgc' ),
			'geo' 						=>  __( 'Geo', 'dwgc' ),
			'line' 						=>  __( 'Line', 'dwgc' ),
			'pie' 						=>  __( 'Pie', 'dwgc' ),
			'combo' 					=>  __( 'Combo', 'dwgc' ),
			// 'steppedarea' 	=> __( 'Stepped Area', 'dwgc' ),
			'waterfall' 			=>  __( 'Waterfall', 'dwgc' ),
			'scatter' 				=>  __( 'Scatter', 'dwgc' ),
			'trendlines' 			=> __( 'Trendlines', 'dwgc' ),
		) );
	}

	public function fonts() {
		return apply_filters( 'dwc_chart_fonts', array(
				'none' 					=> __( 'Use Global', 'dwgc' ),
				'arial' 				=> __( 'Arial', 'dwgc' ),
				'sans' 					=> __( 'SansSerif', 'dwgc' ),
				'serif' 				=> __( 'Serif', 'dwgc' ),
				'wide' 					=> __( 'Wide', 'dwgc' ),
				'narrow' 				=> __( 'Narrow', 'dwgc' ),
				'comic' 				=> __( 'Comic Sans MS', 'dwgc' ),
				'courier' 			=> __( 'Courier New', 'dwgc' ),
				'garamond' 			=> __( 'Garamond', 'dwgc' ),
				'georgia' 			=> __( 'Georgia', 'dwgc' ),
				'tahoma' 				=> __( 'Tahoma', 'dwgc' ),
				'verdana' 			=> __( 'Verdana', 'dwgc' )
		) );
	}
}

function dw_chart() {
	return DW_Chart::instance();
}

$GLOBALS['dw_chart'] = dw_chart();

endif;