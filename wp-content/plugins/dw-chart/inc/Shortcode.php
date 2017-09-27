<?php

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

class DW_Chart_Shortcode {
	public function __construct() {
		$shortcodes = array(
			'dw_chart' => __CLASS__ . '::chart_output'
		);

		foreach( $shortcodes as $tag => $func ) {
			add_shortcode( $tag, $func );
		}

		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_script' ) );
		add_action( 'wp_head', array( $this, 'google_load' ) );
	}

	public function enqueue_script() {
		wp_enqueue_script( 'dw_chart_google_api', 'https://www.gstatic.com/charts/loader.js', array(), dw_chart()->version );
	}

	public function google_load() {
		?>
		<script type="text/javascript">
			google.charts.load('current', {'packages':['corechart']});
		</script>
		<?php
	}

	public static function locate_template( $name, $extend = false, $args = array(), $echo = true ) {
		if ( $args && is_array( $args ) ) {
			extract( $args );
		}

		if ( $extend ) {
			$name = $name . '-' . $extend;
		}
		$dw_chart = dw_chart();
		$template = false;
		if ( file_exists( $dw_chart->tpl_dir . $name . '.php' ) ) {
			$template = $dw_chart->tpl_dir . $name . '.php';
		}

		if ( $echo ) {
			echo $template;
		}

		include( $template );
	}

	public static function chart_output( $atts = array() ) {
		extract( shortcode_atts( array(
			'id' => 0
		), $atts, 'dw_chart' ) );

		ob_start();
		self::locate_template( 'chart', false, array( 'chart_id' => $id ), false );
		return ob_get_clean();
	}
}