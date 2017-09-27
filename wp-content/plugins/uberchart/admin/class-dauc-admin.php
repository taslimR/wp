<?php

/*
 * this class should be used to work with the administrative side of wordpress
 */

class Dauc_Admin {

	protected static $instance = null;
	private $shared = null;

	private $screen_id_charts = null;
	private $screen_id_import = null;
	private $screen_id_export = null;
	private $screen_id_options = null;

	private function __construct() {

		//assign an instance of the plugin info
		$this->shared = Dauc_Shared::get_instance();

		//Load admin stylesheets and JavaScript
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );

		//Write in back end head
		add_action('admin_head', array( $this, 'wr_admin_head' ));

		//Add the admin menu
		add_action( 'admin_menu', array( $this, 'me_add_admin_menu' ) );

		//Load the options API registrations and callbacks
		add_action('admin_init', array( $this, 'op_register_options' ) );

		//Create tinymce plugin
		add_action('init', array( $this, 'create_tinymce_plugin') );

		//this hook is triggered during the creation of a new blog
		add_action( 'wpmu_new_blog', array( $this, 'new_blog_create_options_and_tables' ), 10, 6 );

		//this hook is triggered during the deletion of a blog
		add_action( 'delete_blog', array( $this, 'delete_blog_delete_options_and_tables' ), 10, 1 );

		//Export CSV controller
		add_action( 'init', array( $this, 'export_xml_controller' ) );

	}

	/*
	 * return an instance of this class
	 */
	public static function get_instance() {

		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;

	}

	/*
	 * write in the admin head
	 */
	public function wr_admin_head(){

		echo '<script type="text/javascript">';
		echo 'var dauc_ajax_url = "' . admin_url('admin-ajax.php') . '";';
		echo 'var dauc_nonce = "' . wp_create_nonce( "dauc" ) . '";';
		echo 'var dauc_admin_url ="' . get_admin_url() . '";';
		echo 'var dauc_site_url ="' . get_site_url() . '";';
		echo '</script>';

	}

	public function enqueue_admin_styles() {

		$screen = get_current_screen();

		//menu charts
		if ( $screen->id == $this->screen_id_charts ) {
			wp_enqueue_style( $this->shared->get( 'slug' ) . '-framework-menu', $this->shared->get( 'url' ) . 'admin/assets/css/framework/menu.css', array(), $this->shared->get( 'ver' ) );
			wp_enqueue_style( $this->shared->get( 'slug' ) . '-menu-charts', $this->shared->get( 'url' ) . 'admin/assets/css/menu-charts.css', array(), $this->shared->get( 'ver' ) );
			wp_enqueue_style( $this->shared->get( 'slug' ) . '-jquery-ui-tooltip', $this->shared->get( 'url' ) . 'admin/assets/css/jquery-ui-tooltip.css', array(), $this->shared->get( 'ver' ) );
			wp_enqueue_style( $this->shared->get( 'slug' ) . '-handsontable-full', $this->shared->get( 'url' ) . 'admin/assets/inc/handsontable/handsontable.full.min.css', array(), $this->shared->get( 'ver' ) );
			wp_enqueue_style( $this->shared->get('slug') .'-chosen', $this->shared->get('url') . 'admin/assets/inc/chosen/chosen-min.css', array(), $this->shared->get('ver') );
			wp_enqueue_style( $this->shared->get('slug') .'-chosen-custom', $this->shared->get('url') . 'admin/assets/css/chosen-custom.css', array(), $this->shared->get('ver') );
			wp_enqueue_style( $this->shared->get('slug') .'-spectrum', $this->shared->get('url') . 'admin/assets/inc/spectrum/spectrum.css', array(), $this->shared->get('ver') );
		}

		//menu options
		if ( $screen->id == $this->screen_id_options ) {
			wp_enqueue_style( $this->shared->get('slug') .'-framework-options', $this->shared->get('url') . 'admin/assets/css/framework/options.css', array(), $this->shared->get('ver') );
			wp_enqueue_style( $this->shared->get('slug') .'-jquery-ui-tooltip', $this->shared->get('url') . 'admin/assets/css/jquery-ui-tooltip.css', array(), $this->shared->get('ver') );
			wp_enqueue_style( $this->shared->get('slug') .'-chosen', $this->shared->get('url') . 'admin/assets/inc/chosen/chosen-min.css', array(), $this->shared->get('ver') );
			wp_enqueue_style( $this->shared->get('slug') .'-chosen-custom', $this->shared->get('url') . 'admin/assets/css/chosen-custom.css', array(), $this->shared->get('ver') );
		}

		$args = array(
			'show_ui' => true
		);
		$post_types_with_ui = get_post_types($args);
		unset($post_types_with_ui['attachment']);

		if ( in_array( $screen->id, $post_types_with_ui ) ) {

			/*
			 * This enables the use of custom icons from Dashicons without using a .png image.
			 *
			 * For details: https://www.gavick.com/blog/wordpress-tinymce-custom-buttons#tc-section-4
			 */
			wp_enqueue_style( $this->shared->get( 'slug' ) . '-enable-dashicons-in-tinymce', $this->shared->get( 'url' ) . 'admin/assets/css/enable-dashicons-in-tinymce.css', array(), $this->shared->get( 'ver' ) );

		}

	}

	/*
	 * enqueue admin-specific javascript
	 */
	public function enqueue_admin_scripts() {

		$screen = get_current_screen();

		//menu charts
		if ( $screen->id == $this->screen_id_charts ) {
			wp_enqueue_script( 'jquery-ui-tooltip' );
			wp_enqueue_script( $this->shared->get( 'slug' ) . '-jquery-ui-tooltip-init', $this->shared->get( 'url' ) . 'admin/assets/js/jquery-ui-tooltip-init.js', 'jquery', $this->shared->get( 'ver' ) );
			wp_enqueue_script( $this->shared->get( 'slug' ) . '-handsontable-full', $this->shared->get( 'url' ) . 'admin/assets/inc/handsontable/handsontable.full.min.js', 'jquery', $this->shared->get( 'ver' ) );
			wp_enqueue_script( $this->shared->get( 'slug' ) . '-charts-menu', $this->shared->get( 'url' ) . 'admin/assets/js/charts-menu.js', 'jquery', $this->shared->get( 'ver' ) );

			//pass the objectL10n object to this javascript file
			wp_localize_script( $this->shared->get( 'slug' ) . '-charts-menu', 'objectL10n', array(
				'cancelText'  => __('Cancel', 'dauc'),
				'chooseText' => __('Add Color', 'dauc'),
				'name' => __('Name', 'dauc'),
				'description' => __('Description', 'dauc'),
				'labels' => __('Labels', 'dauc'),
				'margin_top' => __("Margin Top (Common)", 'dauc'),
				'margin_bottom' => __("Margin Bottom (Common)", 'dauc'),
				'canvas_backgroundColor' => __("Background Color (Common)", 'dauc'),
				'width' => __("Width (Common)", 'dauc'),
				'height' => __("Height (Common)", 'dauc'),
				'responsiveAnimationDuration' => __("Responsive Animation Duration (Common)", 'dauc'),
				'fixed_height' => __("Fixed Height (Common)", 'dauc'),
				'title_fontSize' => __('Font Size (Title)', 'dauc'),
				'title_fontFamily' => __('Font Family (Title)', 'dauc'),
				'title_fontColor' => __('Font Color (Title)', 'dauc'),
				'title_padding' => __('Padding (Title)', 'dauc'),
				'legend_labels_boxWidth' => __('Box Width (Legend Label)', 'dauc'),
				'legend_labels_fontSize' => __('Font Size (Legend Label)', 'dauc'),
				'legend_labels_fontColor' => __('Font Color (Legend Label)', 'dauc'),
				'legend_labels_fontFamily' => __('Font Family (Legend Label)', 'dauc'),
				'legend_labels_padding' => __('Padding (Legend Label)', 'dauc'),
				'tooltips_backgroundColor' => __('Background Color (Tooltip)', 'dauc'),
				'tooltips_titleFontFamily' => __('Title Font Family (Tooltip)', 'dauc'),
				'tooltips_titleFontSize' => __('Title Font Size (Tooltip)', 'dauc'),
				'tooltips_titleFontColor' => __('Title Font Color (Tooltip)', 'dauc'),
				'tooltips_titleMarginBottom' => __('Title Margin Bottom (Tooltip)', 'dauc'),
				'tooltips_bodyFontFamily' => __('Body Font Family (Tooltip)', 'dauc'),
				'tooltips_bodyFontSize' => __('Body Font Size (Tooltip)', 'dauc'),
				'tooltips_bodyFontColor' => __('Body Font Color (Tooltip)', 'dauc'),
				'tooltips_footerFontFamily' => __('Footer Font Family (Tooltip)', 'dauc'),
				'tooltips_footerFontSize' => __('Footer Font Size (Tooltip)', 'dauc'),
				'tooltips_footerFontColor' => __('Footer Font Color (Tooltip)', 'dauc'),
				'tooltips_footerMarginTop' => __('Footer Margin Top (Tooltip)', 'dauc'),
				'tooltips_xPadding' => __('X Padding (Tooltip)', 'dauc'),
				'tooltips_yPadding' => __('Y Padding (Tooltip)', 'dauc'),
				'tooltips_caretSize' => __('Caret Size (Tooltip)', 'dauc'),
				'tooltips_cornerRadius' => __('Corner Radius (Tooltip)', 'dauc'),
				'tooltips_multiKeyBackground' => __('Multi Key Background (Tooltip)', 'dauc'),
				'hover_animationDuration' => __('Animation Duration (Tooltip)', 'dauc'),
				'animation_duration' => __('Duration (Animation)', 'dauc'),
				'scales_xAxes_gridLines_color' => __('Color (X Scale Grid Line)', 'dauc'),
				'scales_xAxes_gridLines_lineWidth' => __('Line Width (X Scale Grid Line)', 'dauc'),
				'scales_xAxes_gridLines_tickMarkLength' => __('Tick Mark Length (X Scale Grid Line)', 'dauc'),
				'scales_xAxes_gridLines_zeroLineWidth' => __('Zero Line Width (X Scale Grid Line)', 'dauc'),
				'scales_xAxes_gridLines_zeroLineColor' => __('Zero Line Color (X Scale Grid Line)', 'dauc'),
				'scales_xAxes_scaleLabel_fontColor' => __('Font Color (X Scale Title)', 'dauc'),
				'scales_xAxes_scaleLabel_fontFamily' => __('Font Family (X Scale Title)', 'dauc'),
				'scales_xAxes_scaleLabel_fontSize' => __('Font Size (X Scale Title)', 'dauc'),
				'scales_xAxes_ticks_fontColor' => __('Font Color (X Scale Tick)', 'dauc'),
				'scales_xAxes_ticks_fontFamily' => __('Font Family (X Scale Tick)', 'dauc'),
				'scales_xAxes_ticks_fontSize' => __('Font Size (X Scale Tick)', 'dauc'),
				'scales_xAxes_ticks_labelOffset' => __('Label Offset (X Scale Tick)', 'dauc'),
				'scales_xAxes_ticks_maxRotation' => __('Max Rotation (X Scale Tick)', 'dauc'),
				'scales_xAxes_ticks_minRotation' => __('Min Rotation (X Scale Tick)', 'dauc'),
				'scales_xAxes_ticks_min' => __('Min (X Scale Options)', 'dauc'),
				'scales_xAxes_ticks_max' => __('Max (X Scale Options)', 'dauc'),
				'scales_xAxes_ticks_round' => __('Round (X Scale Options)', 'dauc'),
				'scales_xAxes_ticks_maxTicksLimit' => __('Max Limit (X Scale Options)', 'dauc'),
				'scales_xAxes_ticks_stepSize' => __('Step Size (X Scale Options)', 'dauc'),
				'scales_xAxes_ticks_suggestedMax' => __('Suggested Max (X Scale Options)', 'dauc'),
				'scales_xAxes_ticks_suggestedMin' => __('Suggested Min (X Scale Options)', 'dauc'),
				'scales_xAxes_ticks_fixedStepSize' => __('Fixed Step Size (X Scale Options)', 'dauc'),
				'scales_xAxes_categoryPercentage' => __('Category Percentage (X Scale Options)', 'dauc'),
				'scales_xAxes_barPercentage' => __('Bar Percentage (X Scale Options)', 'dauc'),
				'scales_xAxes_time_format' => __('Time Format (X Scale Time)', 'dauc'),
				'scales_xAxes_time_tooltipFormat' => __('Tooltip Format (X Scale Time)', 'dauc'),
				'scales_xAxes_time_unit_format' => __('Unit Format (X Scale Time)', 'dauc'),
				'scales_xAxes_time_min' => __('Min (X Scale Time)', 'dauc'),
				'scales_xAxes_time_max' => __('Max (X Scale Time)', 'dauc'),
				'scales_xAxes_time_unitStepSize' => __('Unit Step Size (X Scale Time)', 'dauc'),
				'scales_yAxes_gridLines_color' => __('Color (Y Scale Grid Line)', 'dauc'),
				'scales_yAxes_gridLines_lineWidth' => __('Line Width (Y Scale Grid Line)', 'dauc'),
				'scales_yAxes_gridLines_tickMarkLength' => __('Tick Mark Length (Y Scale Grid Line)', 'dauc'),
				'scales_yAxes_gridLines_zeroLineWidth' => __('Zero Line Width (Y Scale Grid Line)', 'dauc'),
				'scales_yAxes_gridLines_zeroLineColor' => __('Zero Line Color (Y Scale Grid Line)', 'dauc'),
				'scales_yAxes_scaleLabel_fontColor' => __('Font Color (Y Scale Title)', 'dauc'),
				'scales_yAxes_scaleLabel_fontFamily' => __('Font Family (Y Scale Title)', 'dauc'),
				'scales_yAxes_scaleLabel_fontSize' => __('Font Size (Y Scale Title)', 'dauc'),
				'scales_yAxes_ticks_fontColor' => __('Font Color (Y Scale Tick)', 'dauc'),
				'scales_yAxes_ticks_fontFamily' => __('Font Family (Y Scale Tick)', 'dauc'),
				'scales_yAxes_ticks_fontSize' => __('Font Size (Y Scale Tick)', 'dauc'),
				'scales_yAxes_ticks_maxRotation' => __('Max Rotation (Y Scale Tick)', 'dauc'),
				'scales_yAxes_ticks_minRotation' => __('Min Rotation (Y Scale Tick)', 'dauc'),
				'scales_yAxes_ticks_padding' => __('Padding (Y Scale Tick)', 'dauc'),
				'scales_yAxes_ticks_min' => __('Min (Y Scale Options)', 'dauc'),
				'scales_yAxes_ticks_max' => __('Max (Y Scale Options)', 'dauc'),
				'scales_yAxes_ticks_round' => __('Round (Y Scale Options)', 'dauc'),
				'scales_yAxes_ticks_maxTicksLimit' => __('Max Limit (Y Scale Options)', 'dauc'),
				'scales_yAxes_ticks_stepSize' => __('Step Size (Y Scale Options)', 'dauc'),
				'scales_yAxes_ticks_suggestedMax' => __('Suggested Max (Y Scale Options)', 'dauc'),
				'scales_yAxes_ticks_suggestedMin' => __('Suggested Min (Y Scale Options)', 'dauc'),
				'scales_yAxes_ticks_fixedStepSize' => __('Fixed Step Size (Y Scale Options)', 'dauc'),
				'scales_yAxes_categoryPercentage' => __('Category Percentage (Y Scale Options)', 'dauc'),
				'scales_yAxes_barPercentage' => __('Bar Percentage (Y Scale Options)', 'dauc'),
				'scales_y2Axes_gridLines_color' => __('Color (Y2 Scale Grid Line)', 'dauc'),
				'scales_y2Axes_gridLines_lineWidth' => __('Line Width (Y2 Scale Grid Line)', 'dauc'),
				'scales_y2Axes_gridLines_tickMarkLength' => __('Tick Mark Length (Y2 Scale Grid Line)', 'dauc'),
				'scales_y2Axes_gridLines_zeroLineWidth' => __('Zero Line Width (Y2 Scale Grid Line)', 'dauc'),
				'scales_y2Axes_gridLines_zeroLineColor' => __('Zero Line Color (Y2 Scale Grid Line)', 'dauc'),
				'scales_y2Axes_scaleLabel_fontColor' => __('Font Color (Y2 Scale Title)', 'dauc'),
				'scales_y2Axes_scaleLabel_fontFamily' => __('Font Family (Y2 Scale Title)', 'dauc'),
				'scales_y2Axes_scaleLabel_fontSize' => __('Font Size (Y2 Scale Title)', 'dauc'),
				'scales_y2Axes_ticks_fontColor' => __('Font Color (Y2 Scale Tick)', 'dauc'),
				'scales_y2Axes_ticks_fontFamily' => __('Font Family (Y2 Scale Tick)', 'dauc'),
				'scales_y2Axes_ticks_fontSize' => __('Font Size (Y2 Scale Tick)', 'dauc'),
				'scales_y2Axes_ticks_maxRotation' => __('Max Rotation (Y2 Scale Tick)', 'dauc'),
				'scales_y2Axes_ticks_minRotation' => __('Min Rotation (Y2 Scale Tick)', 'dauc'),
				'scales_y2Axes_ticks_padding' => __('Padding (Y2 Scale Tick)', 'dauc'),
				'scales_y2Axes_ticks_min' => __('Min (Y2 Scale Options)', 'dauc'),
				'scales_y2Axes_ticks_max' => __('Max (Y2 Scale Options)', 'dauc'),
				'scales_y2Axes_ticks_round' => __('Round (Y2 Scale Options)', 'dauc'),
				'scales_y2Axes_ticks_maxTicksLimit' => __('Max Limit (Y2 Scale Options)', 'dauc'),
				'scales_y2Axes_ticks_stepSize' => __('Step Size (Y2 Scale Options)', 'dauc'),
				'scales_y2Axes_ticks_suggestedMax' => __('Suggested Max (Y2 Scale Options)', 'dauc'),
				'scales_y2Axes_ticks_suggestedMin' => __('Suggested Min (Y2 Scale Options)', 'dauc'),
				'scales_y2Axes_ticks_fixedStepSize' => __('Fixed Step Size (Y2 Scale Options)', 'dauc'),
				'scales_rl_gridLines_color' => __('Color (RL Scale Grid Line)', 'dauc'),
				'scales_rl_gridLines_lineWidth' => __('Line Width (RL Scale Grid Line)', 'dauc'),
				'scales_rl_angleLines_color' => __('Color (RL Scale Angle Line)', 'dauc'),
				'scales_rl_angleLines_lineWidth' => __('Line Width (RL Scale Angle Line)', 'dauc'),
				'scales_rl_pointLabels_fontSize' => __('Font Size (RL Scale Point Label)', 'dauc'),
				'scales_rl_pointLabels_fontColor' => __('Font Color (RL Scale Point Label)', 'dauc'),
				'scales_rl_pointLabels_fontFamily' => __('Font Family (RL Scale Point Label)', 'dauc'),
				'scales_rl_ticks_round' => __('Round (RL Scale Tick)', 'dauc'),
				'scales_rl_ticks_fontSize' => __('Font Size (RL Scale Tick)', 'dauc'),
				'scales_rl_ticks_fontColor' => __('Font Color (RL Scale Tick)', 'dauc'),
				'scales_rl_ticks_fontFamily' => __('Font Family (RL Scale Tick)', 'dauc'),
				'scales_rl_ticks_min' => __('Min (RL Scale Options)', 'dauc'),
				'scales_rl_ticks_max' => __('Max (RL Scale Options)', 'dauc'),
				'scales_rl_ticks_suggestedMin' => __('Suggested Min (RL Scale Options)', 'dauc'),
				'scales_rl_ticks_suggestedMax' => __('Suggested Max (RL Scale Options)', 'dauc'),
				'scales_rl_ticks_stepSize' => __('Step Size (RL Scale Options)', 'dauc'),
				'scales_rl_ticks_fixedStepSize' => __('Fixed Step Size (RL Scale Options)', 'dauc'),
				'scales_rl_ticks_maxTicksLimit' => __('Max Ticks Limit (RL Scale Options)', 'dauc'),
				'scales_rl_ticks_backdropColor' => __('Backdrop Color (RL Scale Options)', 'dauc'),
				'scales_rl_ticks_backdropPaddingX' => __('Backdrop Padding X (RL Scale Options)', 'dauc'),
				'scales_rl_ticks_backdropPaddingY' => __('Backdrop Padding Y (RL Scale Options)', 'dauc'),
				'data_structure_lineTension' => __('Line Tension', 'dauc'),
				'data_structure_backgroundColor' => __('Background Color', 'dauc'),
				'data_structure_borderWidth' => __('Border Width', 'dauc'),
				'data_structure_borderColor' => __('Border Color', 'dauc'),
				'data_structure_borderDash' => __('Border Dash', 'dauc'),
				'data_structure_borderDashOffset' => __('Border Dash Offset', 'dauc'),
				'data_structure_pointBorderColor' => __('Point Border Color', 'dauc'),
				'data_structure_pointBackgroundColor' => __('Point Background Color', 'dauc'),
				'data_structure_pointBorderWidth' => __('Point Border Width', 'dauc'),
				'data_structure_pointRadius' => __('Point Radius', 'dauc'),
				'data_structure_pointHoverRadius' => __('Point Hover Radius', 'dauc'),
				'data_structure_pointHitRadius' => __('Point Hit Radius', 'dauc'),
				'data_structure_pointHoverBackgroundColor' => __('Point Hover Background Color', 'dauc'),
				'data_structure_pointHoverBorderColor' => __('Point Hover Border Color', 'dauc'),
				'data_structure_pointHoverBorderWidth' => __('Point Hover Border Width', 'dauc'),
				'data_structure_pointStyle' => __('Point Style', 'dauc'),
				'data_structure_hoverBackgroundColor' => __('Hover Background Color', 'dauc'),
				'data_structure_hoverBorderColor' => __('Hover Border Color', 'dauc'),
				'data_structure_hoverBorderWidth' => __('Hover Border Width', 'dauc'),
				'data_structure_hitRadius' => __('Hit Radius', 'dauc'),
				'data_structure_hoverRadius' => __('Hover Radius', 'dauc'),
			) );

			wp_enqueue_script( $this->shared->get('slug') . '-chosen', $this->shared->get('url') . 'admin/assets/inc/chosen/chosen-min.js', 'jquery', $this->shared->get('ver') );
			wp_enqueue_script( $this->shared->get('slug') . '-spectrum', $this->shared->get('url') . 'admin/assets/inc/spectrum/spectrum.js', 'jquery', $this->shared->get('ver') );
		}

		//menu options
		if ( $screen->id == $this->screen_id_options ) {
			wp_enqueue_script( 'jquery-ui-tooltip' );
			wp_enqueue_script( $this->shared->get( 'slug' ) . '-jquery-ui-tooltip-init', $this->shared->get( 'url' ) . 'admin/assets/js/jquery-ui-tooltip-init.js', 'jquery', $this->shared->get( 'ver' ) );
			wp_enqueue_script( $this->shared->get('slug') . '-chosen', $this->shared->get('url') . 'admin/assets/inc/chosen/chosen-min.js', 'jquery', $this->shared->get('ver') );
			wp_enqueue_script( $this->shared->get( 'slug' ) . '-jquery-ui-chosen-init', $this->shared->get( 'url' ) . 'admin/assets/js/chosen-init.js', 'jquery', $this->shared->get( 'ver' ) );
		}

	}

	/*
	 * plugin activation
	 */
	public function ac_activate( $networkwide ) {

		/*
		 * create options and tables for all the sites in the network
		 */
		if ( function_exists( 'is_multisite' ) and is_multisite() ) {

			/*
			 * if this is a "Network Activation" create the options and tables
			 * for each blog
			 */
			if ( $networkwide ) {

				//get the current blog id
				global $wpdb;
				$current_blog = $wpdb->blogid;

				//create an array with all the blog ids
				$blogids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );

				//iterate through all the blogs
				foreach ( $blogids as $blog_id ) {

					//switch to the iterated blog
					switch_to_blog( $blog_id );

					//create options and tables for the iterated blog
					$this->ac_initialize_options();
					$this->ac_create_database_tables();

				}

				//switch to the current blog
				switch_to_blog( $current_blog );

			} else {

				/*
				 * if this is not a "Network Activation" create options and
				 * tables only for the current blog
				 */
				$this->ac_initialize_options();
				$this->ac_create_database_tables();

			}

		} else {

			/*
			 * if this is not a multisite installation create options and
			 * tables only for the current blog
			 */
			$this->ac_initialize_options();
			$this->ac_create_database_tables();

		}

	}

	//create the options and tables for the newly created blog
	public function new_blog_create_options_and_tables( $blog_id, $user_id, $domain, $path, $site_id, $meta ) {

		global $wpdb;

		/*
		 * if the plugin is "Network Active" create the options and tables for
		 * this new blog
		 */
		if ( is_plugin_active_for_network( 'uberchart/init.php' ) ) {

			//get the id of the current blog
			$current_blog = $wpdb->blogid;

			//switch to the blog that is being activated
			switch_to_blog( $blog_id );

			//create options and database tables for the new blog
			$this->ac_initialize_options();
			$this->ac_create_database_tables();

			//switch to the current blog
			switch_to_blog( $current_blog );

		}

	}

	//delete options and tables for the deleted blog
	public function delete_blog_delete_options_and_tables( $blog_id ) {

		global $wpdb;

		//get the id of the current blog
		$current_blog = $wpdb->blogid;

		//switch to the blog that is being activated
		switch_to_blog( $blog_id );

		//create options and database tables for the new blog
		$this->un_delete_options();
		$this->un_delete_database_tables();

		//switch to the current blog
		switch_to_blog( $current_blog );

	}

	/*
	 * initialize plugin options
	 */
	private function ac_initialize_options() {

		//database version -----------------------------------------------------
		add_option( $this->shared->get( 'slug' ) . "_database_version", "0" );

		//general --------------------------------------------------------------
		add_option( $this->shared->get( 'slug' ) . "_charts_menu_capability", "manage_options" );
		add_option( $this->shared->get( 'slug' ) . "_import_menu_capability", "manage_options" );
		add_option( $this->shared->get( 'slug' ) . "_export_menu_capability", "manage_options" );
		add_option( $this->shared->get( 'slug' ) . "_chartjs_library_url", $this->shared->get('url') . 'shared/assets/js/Chart.bundle.min.js' );
		add_option( $this->shared->get( 'slug' ) . "_compress_output", "1" );
        add_option( $this->shared->get( 'slug' ) . "_max_execution_time", "300");
        add_option( $this->shared->get( 'slug' ) . "_memory_limit", "512");

	}

	/*
	 * create the plugin database tables
	 */
	private function ac_create_database_tables() {

		//check database version and create the database
		if ( intval( get_option( $this->shared->get( 'slug' ) . '_database_version' ), 10 ) < 2 ) {

			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

			//create *prefix*_chart
			global $wpdb;
			$table_name = $wpdb->prefix . $this->shared->get( 'slug' ) . "_chart";
			$sql        = "CREATE TABLE $table_name (
                id bigint(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                type varchar(13) DEFAULT NULL,
                name varchar(200) DEFAULT NULL,
                description varchar(200) DEFAULT NULL,
                rows int(11) DEFAULT NULL,
                columns int(11) DEFAULT NULL,
                labels text,
                temporary tinyint(1) DEFAULT '1',
                canvas_transparent_background tinyint(1) DEFAULT NULL,
                canvas_backgroundColor varchar(22) DEFAULT NULL,
                margin_top int(11) DEFAULT NULL,
                margin_bottom int(11) DEFAULT NULL,
                width int(11) DEFAULT NULL,
                height int(11) DEFAULT NULL,
                responsive tinyint(1) DEFAULT NULL,
                responsiveAnimationDuration int(11) DEFAULT NULL,
                maintainAspectRatio tinyint(1) DEFAULT NULL,
                fixed_height int(11) DEFAULT NULL,
                is_model tinyint(1) DEFAULT NULL,
                title_display tinyint(1) DEFAULT NULL,
                title_position varchar(6) DEFAULT NULL,
                title_fullWidth tinyint(1) DEFAULT NULL,
                title_fontSize int(11) DEFAULT NULL,
                title_fontFamily varchar(200) DEFAULT NULL,
                title_fontColor varchar(22) DEFAULT NULL,
                title_fontStyle varchar(7) DEFAULT NULL,
                title_padding int(11) DEFAULT NULL,
                legend_display tinyint(1) DEFAULT NULL,
                legend_position varchar(6) DEFAULT NULL,
                legend_fullWidth tinyint(1) DEFAULT NULL,
                legend_toggle_dataset tinyint(1) DEFAULT NULL,
                legend_labels_boxWidth int(11) DEFAULT NULL,
                legend_labels_fontSize int(11) DEFAULT NULL,
                legend_labels_fontStyle varchar(7) DEFAULT NULL,
                legend_labels_fontColor varchar(22) DEFAULT NULL,
                legend_labels_fontFamily varchar(200) DEFAULT NULL,
                legend_labels_padding int(11) DEFAULT NULL,
                tooltips_enabled tinyint(1) DEFAULT NULL,
                tooltips_mode varchar(6) DEFAULT NULL,
                tooltips_backgroundColor varchar(22) DEFAULT NULL,
                tooltips_titleFontFamily varchar(200) DEFAULT NULL,
                tooltips_titleFontSize int(11) DEFAULT NULL,
                tooltips_titleFontStyle varchar(7) DEFAULT NULL,
                tooltips_titleFontColor varchar(22) DEFAULT NULL,
                tooltips_titleMarginBottom int(11) DEFAULT NULL,
                tooltips_bodyFontFamily varchar(200) DEFAULT NULL,
                tooltips_bodyFontSize int(11) DEFAULT NULL,
                tooltips_bodyFontStyle varchar(7) DEFAULT NULL,
                tooltips_bodyFontColor varchar(22) DEFAULT NULL,
                tooltips_footerFontFamily varchar(200) DEFAULT NULL,
                tooltips_footerFontSize int(11) DEFAULT NULL,
                tooltips_footerFontStyle varchar(7) DEFAULT NULL,
                tooltips_footerFontColor varchar(22) DEFAULT NULL,
                tooltips_footerMarginTop int(11) DEFAULT NULL,
                tooltips_xPadding int(11) DEFAULT NULL,
                tooltips_yPadding int(11) DEFAULT NULL,
                tooltips_caretSize int(11) DEFAULT NULL,
                tooltips_cornerRadius int(11) DEFAULT NULL,
                tooltips_multiKeyBackground varchar(22) DEFAULT NULL,
                tooltips_beforeTitle varchar(200) DEFAULT NULL,
                tooltips_afterTitle varchar(200) DEFAULT NULL,
                tooltips_beforeBody varchar(200) DEFAULT NULL,
                tooltips_afterBody varchar(200) DEFAULT NULL,
                tooltips_beforeLabel varchar(200) DEFAULT NULL,
                tooltips_afterLabel varchar(200) DEFAULT NULL,
                tooltips_beforeFooter varchar(200) DEFAULT NULL,
                tooltips_footer varchar(200) DEFAULT NULL,
                tooltips_afterFooter varchar(200) DEFAULT NULL,
                hover_animationDuration int(11) DEFAULT NULL,
                animation_duration int(11) DEFAULT NULL,
                animation_easing varchar(16) DEFAULT NULL,
                animation_animateRotate tinyint(1) DEFAULT NULL,
                animation_animateScale tinyint(1) DEFAULT NULL,
                elements_rectangle_borderSkipped varchar(6) DEFAULT NULL,
                scales_xAxes_type varchar(11) DEFAULT NULL,
                scales_xAxes_display tinyint(1) DEFAULT NULL,
                scales_xAxes_position varchar(6) DEFAULT NULL,
                scales_xAxes_stacked tinyint(1) DEFAULT NULL,
                scales_xAxes_gridLines_display tinyint(1) DEFAULT NULL,
                scales_xAxes_gridLines_color text,
                scales_xAxes_gridLines_lineWidth text,
                scales_xAxes_gridLines_drawBorder tinyint(1) DEFAULT NULL,
                scales_xAxes_gridLines_drawOnChartArea tinyint(1) DEFAULT NULL,
                scales_xAxes_gridLines_drawTicks tinyint(1) DEFAULT NULL,
                scales_xAxes_gridLines_tickMarkLength int(11) DEFAULT NULL,
                scales_xAxes_gridLines_zeroLineWidth int(11) DEFAULT NULL,
                scales_xAxes_gridLines_zeroLineColor varchar(22) DEFAULT NULL,
                scales_xAxes_gridLines_offsetGridLines tinyint(1) DEFAULT NULL,
                scales_xAxes_scaleLabel_display tinyint(1) DEFAULT NULL,
                scales_xAxes_scaleLabel_labelString varchar(200) DEFAULT NULL,
                scales_xAxes_scaleLabel_fontColor varchar(22) DEFAULT NULL,
                scales_xAxes_scaleLabel_fontFamily varchar(200) DEFAULT NULL,
                scales_xAxes_scaleLabel_fontSize int(11) DEFAULT NULL,
                scales_xAxes_scaleLabel_fontStyle varchar(7) DEFAULT NULL,
                scales_xAxes_ticks_autoskip tinyint(1) DEFAULT NULL,
                scales_xAxes_ticks_display tinyint(1) DEFAULT NULL,
                scales_xAxes_ticks_fontColor varchar(22) DEFAULT NULL,
                scales_xAxes_ticks_fontFamily varchar(200) DEFAULT NULL,
                scales_xAxes_ticks_fontSize int(11) DEFAULT NULL,
                scales_xAxes_ticks_fontStyle varchar(7) DEFAULT NULL,
                scales_xAxes_ticks_labelOffset int(11) DEFAULT NULL,
                scales_xAxes_ticks_maxRotation int(11) DEFAULT NULL,
                scales_xAxes_ticks_minRotation int(11) DEFAULT NULL,
                scales_xAxes_ticks_reverse tinyint(1) DEFAULT NULL,
                scales_xAxes_ticks_prefix varchar(50) DEFAULT NULL,
                scales_xAxes_ticks_suffix varchar(50) DEFAULT NULL,
                scales_xAxes_ticks_round varchar(2) DEFAULT NULL,
                scales_xAxes_ticks_min varchar(20) DEFAULT NULL,
                scales_xAxes_ticks_max varchar(20) DEFAULT NULL,
                scales_xAxes_ticks_beginAtZero tinyint(1) DEFAULT NULL,
                scales_xAxes_ticks_maxTicksLimit varchar(20) DEFAULT NULL,
                scales_xAxes_ticks_stepSize varchar(20) DEFAULT NULL,
                scales_xAxes_ticks_suggestedMax varchar(20) DEFAULT NULL,
                scales_xAxes_ticks_suggestedMin varchar(20) DEFAULT NULL,
                scales_xAxes_ticks_fixedStepSize varchar(20) DEFAULT NULL,
                scales_xAxes_categoryPercentage float DEFAULT NULL,
                scales_xAxes_barPercentage float DEFAULT NULL,
                scales_xAxes_time_format varchar(50) DEFAULT NULL,
                scales_xAxes_time_tooltipFormat varchar(50) DEFAULT NULL,
                scales_xAxes_time_unit_format varchar(50) DEFAULT NULL,
                scales_xAxes_time_unit varchar(11) DEFAULT NULL,
                scales_xAxes_time_unitStepSize int(11) DEFAULT NULL,
                scales_xAxes_time_max varchar(50) DEFAULT NULL,
                scales_xAxes_time_min varchar(50) DEFAULT NULL,
                scales_yAxes_type varchar(11) DEFAULT NULL,
                scales_yAxes_display tinyint(1) DEFAULT NULL,
                scales_yAxes_position varchar(6) DEFAULT NULL,
                scales_yAxes_stacked tinyint(1) DEFAULT NULL,
                scales_yAxes_gridLines_display tinyint(1) DEFAULT NULL,
                scales_yAxes_gridLines_color text,
                scales_yAxes_gridLines_lineWidth text,
                scales_yAxes_gridLines_drawBorder tinyint(1) DEFAULT NULL,
                scales_yAxes_gridLines_drawOnChartArea tinyint(1) DEFAULT NULL,
                scales_yAxes_gridLines_drawTicks tinyint(1) DEFAULT NULL,
                scales_yAxes_gridLines_tickMarkLength int(11) DEFAULT NULL,
                scales_yAxes_gridLines_zeroLineWidth int(11) DEFAULT NULL,
                scales_yAxes_gridLines_zeroLineColor varchar(22) DEFAULT NULL,
                scales_yAxes_gridLines_offsetGridLines tinyint(1) DEFAULT NULL,
                scales_yAxes_scaleLabel_display tinyint(1) DEFAULT NULL,
                scales_yAxes_scaleLabel_labelString varchar(200) DEFAULT NULL,
                scales_yAxes_scaleLabel_fontColor varchar(22) DEFAULT NULL,
                scales_yAxes_scaleLabel_fontFamily varchar(200) DEFAULT NULL,
                scales_yAxes_scaleLabel_fontSize int(11) DEFAULT NULL,
                scales_yAxes_scaleLabel_fontStyle varchar(7) DEFAULT NULL,
                scales_yAxes_ticks_autoskip tinyint(1) DEFAULT NULL,
                scales_yAxes_ticks_display tinyint(1) DEFAULT NULL,
                scales_yAxes_ticks_fontColor varchar(22) DEFAULT NULL,
                scales_yAxes_ticks_fontFamily varchar(200) DEFAULT NULL,
                scales_yAxes_ticks_fontSize int(11) DEFAULT NULL,
                scales_yAxes_ticks_fontStyle varchar(7) DEFAULT NULL,
                scales_yAxes_ticks_maxRotation int(11) DEFAULT NULL,
                scales_yAxes_ticks_minRotation int(11) DEFAULT NULL,
                scales_yAxes_ticks_mirror tinyint(1) DEFAULT NULL,
                scales_yAxes_ticks_padding int(11) DEFAULT NULL,
                scales_yAxes_ticks_reverse tinyint(1) DEFAULT NULL,
                scales_yAxes_ticks_prefix varchar(50) DEFAULT NULL,
                scales_yAxes_ticks_suffix varchar(50) DEFAULT NULL,
                scales_yAxes_ticks_round varchar(2) DEFAULT NULL,
                scales_yAxes_ticks_min varchar(20) DEFAULT NULL,
                scales_yAxes_ticks_max varchar(20) DEFAULT NULL,
                scales_yAxes_ticks_beginAtZero tinyint(1) DEFAULT NULL,
                scales_yAxes_ticks_maxTicksLimit varchar(20) DEFAULT NULL,
                scales_yAxes_ticks_stepSize varchar(20) DEFAULT NULL,
                scales_yAxes_ticks_suggestedMax varchar(20) DEFAULT NULL,
                scales_yAxes_ticks_suggestedMin varchar(20) DEFAULT NULL,
                scales_yAxes_ticks_fixedStepSize varchar(20) DEFAULT NULL,
                scales_yAxes_categoryPercentage float DEFAULT NULL,
                scales_yAxes_barPercentage float DEFAULT NULL,
                scales_y2Axes_type varchar(11) DEFAULT NULL,
                scales_y2Axes_display tinyint(1) DEFAULT NULL,
                scales_y2Axes_position varchar(6) DEFAULT NULL,
                scales_y2Axes_gridLines_display tinyint(1) DEFAULT NULL,
                scales_y2Axes_gridLines_color text,
                scales_y2Axes_gridLines_lineWidth text,
                scales_y2Axes_gridLines_drawBorder tinyint(1) DEFAULT NULL,
                scales_y2Axes_gridLines_drawOnChartArea tinyint(1) DEFAULT NULL,
                scales_y2Axes_gridLines_drawTicks tinyint(1) DEFAULT NULL,
                scales_y2Axes_gridLines_tickMarkLength int(11) DEFAULT NULL,
                scales_y2Axes_gridLines_zeroLineWidth int(11) DEFAULT NULL,
                scales_y2Axes_gridLines_zeroLineColor varchar(22) DEFAULT NULL,
                scales_y2Axes_gridLines_offsetGridLines tinyint(1) DEFAULT NULL,
                scales_y2Axes_scaleLabel_display tinyint(1) DEFAULT NULL,
                scales_y2Axes_scaleLabel_labelString varchar(200) DEFAULT NULL,
                scales_y2Axes_scaleLabel_fontColor varchar(22) DEFAULT NULL,
                scales_y2Axes_scaleLabel_fontFamily varchar(200) DEFAULT NULL,
                scales_y2Axes_scaleLabel_fontSize int(11) DEFAULT NULL,
                scales_y2Axes_scaleLabel_fontStyle varchar(7) DEFAULT NULL,
                scales_y2Axes_ticks_autoskip tinyint(1) DEFAULT NULL,
                scales_y2Axes_ticks_display tinyint(1) DEFAULT NULL,
                scales_y2Axes_ticks_fontColor varchar(22) DEFAULT NULL,
                scales_y2Axes_ticks_fontFamily varchar(200) DEFAULT NULL,
                scales_y2Axes_ticks_fontSize int(11) DEFAULT NULL,
                scales_y2Axes_ticks_fontStyle varchar(7) DEFAULT NULL,
                scales_y2Axes_ticks_maxRotation int(11) DEFAULT NULL,
                scales_y2Axes_ticks_minRotation int(11) DEFAULT NULL,
                scales_y2Axes_ticks_mirror tinyint(1) DEFAULT NULL,
                scales_y2Axes_ticks_padding int(11) DEFAULT NULL,
                scales_y2Axes_ticks_reverse tinyint(1) DEFAULT NULL,
                scales_y2Axes_ticks_prefix varchar(50) DEFAULT NULL,
                scales_y2Axes_ticks_suffix varchar(50) DEFAULT NULL,
                scales_y2Axes_ticks_round varchar(2) DEFAULT NULL,
                scales_y2Axes_ticks_min varchar(20) DEFAULT NULL,
                scales_y2Axes_ticks_max varchar(20) DEFAULT NULL,
                scales_y2Axes_ticks_beginAtZero tinyint(1) DEFAULT NULL,
                scales_y2Axes_ticks_maxTicksLimit varchar(20) DEFAULT NULL,
                scales_y2Axes_ticks_stepSize varchar(20) DEFAULT NULL,
                scales_y2Axes_ticks_suggestedMax varchar(20) DEFAULT NULL,
                scales_y2Axes_ticks_suggestedMin varchar(20) DEFAULT NULL,
                scales_y2Axes_ticks_fixedStepSize varchar(20) DEFAULT NULL,
                scales_rl_display tinyint(1) DEFAULT NULL,
                scales_rl_gridLines_display tinyint(1) DEFAULT NULL,
                scales_rl_gridLines_color text,
                scales_rl_gridLines_lineWidth text,
                scales_rl_angleLines_display tinyint(1) DEFAULT NULL,
                scales_rl_angleLines_color text,
                scales_rl_angleLines_lineWidth text,
                scales_rl_pointLabels_fontSize int(11) DEFAULT NULL,
                scales_rl_pointLabels_fontColor varchar(22) DEFAULT NULL,
                scales_rl_pointLabels_fontFamily varchar(200) DEFAULT NULL,
                scales_rl_pointLabels_fontStyle varchar(7) DEFAULT NULL,
                scales_rl_ticks_display tinyint(1) DEFAULT NULL,
                scales_rl_ticks_autoskip tinyint(1) DEFAULT NULL,
                scales_rl_ticks_reverse tinyint(1) DEFAULT NULL,
                scales_rl_ticks_prefix varchar(50) DEFAULT NULL,
                scales_rl_ticks_suffix varchar(50) DEFAULT NULL,
                scales_rl_ticks_round varchar(2) DEFAULT NULL,
                scales_rl_ticks_fontSize int(11) DEFAULT NULL,
                scales_rl_ticks_fontColor varchar(22) DEFAULT NULL,
                scales_rl_ticks_fontFamily varchar(200) DEFAULT NULL,
                scales_rl_ticks_fontStyle varchar(7) DEFAULT NULL,
                scales_rl_ticks_min varchar(20) DEFAULT NULL,
                scales_rl_ticks_max varchar(20) DEFAULT NULL,
                scales_rl_ticks_suggestedMin varchar(20) DEFAULT NULL,
                scales_rl_ticks_suggestedMax varchar(20) DEFAULT NULL,
                scales_rl_ticks_stepSize varchar(20) DEFAULT NULL,
                scales_rl_ticks_fixedStepSize varchar(20) DEFAULT NULL,
                scales_rl_ticks_maxTicksLimit varchar(20) DEFAULT NULL,
                scales_rl_ticks_beginAtZero tinyint(1) DEFAULT NULL,
                scales_rl_ticks_showLabelBackdrop tinyint(1) DEFAULT NULL,
                scales_rl_ticks_backdropColor varchar(22) DEFAULT NULL,
                scales_rl_ticks_backdropPaddingX int(11) DEFAULT NULL,
                scales_rl_ticks_backdropPaddingY int(11) DEFAULT NULL
            )
            COLLATE = utf8_general_ci
            ";

			dbDelta( $sql );

			//create *prefix*_data
			global $wpdb;
			$table_name = $wpdb->prefix . $this->shared->get( 'slug' ) . "_data";
			$sql        = "CREATE TABLE $table_name (
				  id bigint(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                  chart_id bigint(20) DEFAULT NULL,
                  row_index bigint(20) DEFAULT NULL,
                  content longtext,
                  label varchar(200) DEFAULT NULL,
                  fill tinyint(1) DEFAULT NULL,
                  lineTension float DEFAULT NULL,
                  backgroundColor text,
                  borderWidth text,
                  borderColor text,
                  borderCapStyle varchar(6) DEFAULT NULL,
                  borderDash varchar(200) DEFAULT NULL,
                  borderDashOffset float DEFAULT NULL,
                  borderJoinStyle varchar(5) DEFAULT NULL,
                  pointBorderColor text,
                  pointBackgroundColor text,
                  pointBorderWidth text,
                  pointRadius text,
                  pointHoverRadius text,
                  pointHitRadius text,
                  pointHoverBackgroundColor text,
                  pointHoverBorderColor text,
                  pointHoverBorderWidth text,
                  pointStyle text,
                  showLine tinyint(1) DEFAULT NULL,
                  spanGaps tinyint(1) DEFAULT NULL,
                  hoverBackgroundColor text,
                  hoverBorderColor text,
                  hoverBorderWidth text,
                  hitRadius text,
                  hoverRadius text,
                  plotY2 tinyint(1) DEFAULT NULL
            )
            COLLATE = utf8_general_ci
            ";

			dbDelta( $sql );

			//Update database version
			update_option( $this->shared->get( 'slug' ) . '_database_version', "2" );

		}

	}

	/*
	 * plugin delete
	 */
	static public function un_delete() {

		/*
		 * delete options and tables for all the sites in the network
		 */
		if ( function_exists( 'is_multisite' ) and is_multisite() ) {

			//get the current blog id
			global $wpdb;
			$current_blog = $wpdb->blogid;

			//create an array with all the blog ids
			$blogids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );

			//iterate through all the blogs
			foreach ( $blogids as $blog_id ) {

				//switch to the iterated blog
				switch_to_blog( $blog_id );

				//create options and tables for the iterated blog
				Dauc_Admin::un_delete_options();
				Dauc_Admin::un_delete_database_tables();

			}

			//switch to the current blog
			switch_to_blog( $current_blog );

		} else {

			/*
			 * if this is not a multisite installation delete options and
			 * tables only for the current blog
			 */
			Dauc_Admin::un_delete_options();
			Dauc_Admin::un_delete_database_tables();

		}

	}

	/*
	 * delete plugin options
	 */
	static public function un_delete_options() {

		//assign an instance of Dauc_Shared
		$shared = Dauc_Shared::get_instance();

		//database version -----------------------------------------------------
		delete_option( $shared->get( 'slug' ) . "_database_version", "0" );

		//general --------------------------------------------------------------
		delete_option( $shared->get( 'slug' ) . "_charts_menu_capability" );
		delete_option( $shared->get( 'slug' ) . "_import_menu_capability" );
		delete_option( $shared->get( 'slug' ) . "_export_menu_capability" );
		delete_option( $shared->get( 'slug' ) . "_chartjs_library_url" );
		delete_option( $shared->get( 'slug' ) . "_compress_output" );
        delete_option( $shared->get( 'slug' ) . "_max_execution_time");
        delete_option( $shared->get( 'slug' ) . "_memory_limit");

	}

	/*
	 * delete plugin database tables
	 */
	static public function un_delete_database_tables() {

		//assign an instance of Dauc_Shared
		$shared = Dauc_Shared::get_instance();

		global $wpdb;

		$table_name = $wpdb->prefix . $shared->get( 'slug' ) . "_chart";
		$sql        = "DROP TABLE $table_name";
		$wpdb->query( $sql );

		$table_name = $wpdb->prefix . $shared->get( 'slug' ) . "_data";
		$sql        = "DROP TABLE $table_name";
		$wpdb->query( $sql );

	}

	/*
	 * register the admin menu
	 */
	public function me_add_admin_menu() {

		add_menu_page(
			'UC',
			__( 'UberChart', 'dauc' ),
			get_option( $this->shared->get('slug') . "_charts_menu_capability"),
			$this->shared->get( 'slug' ) . '-charts',
			array( $this, 'me_display_menu_charts' ),
			'dashicons-chart-line'
		);

		$this->screen_id_charts = add_submenu_page(
			$this->shared->get( 'slug' ) . '-charts',
			'UC - Charts',
			__( 'Charts', 'dauc' ),
			get_option( $this->shared->get('slug') . "_charts_menu_capability"),
			$this->shared->get( 'slug' ) . '-charts',
			array( $this, 'me_display_menu_charts' )
		);

		$this->screen_id_import = add_submenu_page(
			$this->shared->get( 'slug' ) . '-charts',
			'UC - Import',
			__( 'Import', 'dauc' ),
			get_option( $this->shared->get('slug') . "_import_menu_capability"),
			$this->shared->get( 'slug' ) . '-import',
			array( $this, 'me_display_menu_import' )
		);

		$this->screen_id_export = add_submenu_page(
			$this->shared->get( 'slug' ) . '-charts',
			'UC - Export',
			__( 'Export', 'dauc' ),
			get_option( $this->shared->get('slug') . "_export_menu_capability"),
			$this->shared->get( 'slug' ) . '-export',
			array( $this, 'me_display_menu_export' )
		);

		$this->screen_id_options = add_submenu_page(
			$this->shared->get( 'slug' ) . '-charts',
			'UC - Options',
			__( 'Options', 'dauc' ),
			'manage_options',
			$this->shared->get( 'slug' ) . '-options',
			array( $this, 'me_display_menu_options' )
		);

	}

	/*
	 * includes the charts view
	 */
	public function me_display_menu_charts() {
		include_once( 'view/charts.php' );
	}

	/*
	 * includes the import view
	 */
	public function me_display_menu_import() {
		include_once( 'view/import.php' );
	}

	/*
	 * includes the export view
	 */
	public function me_display_menu_export() {
		include_once( 'view/export.php' );
	}

	/*
	 * includes the options view
	 */
	public function me_display_menu_options() {
		include_once( 'view/options.php' );
	}

	/*
	 * register options
	 */
	public function op_register_options() {

		//section general ----------------------------------------------------------
		add_settings_section(
			'dauc_general_settings_section',
			null,
			null,
			'dauc_general_options'
		);

		add_settings_field(
			'charts_menu_capability',
			__( 'Charts Menu Capability', 'dauc' ),
			array( $this, 'charts_menu_capability_callback' ),
			'dauc_general_options',
			'dauc_general_settings_section'
		);

		register_setting(
			'dauc_general_options',
			'dauc_charts_menu_capability',
			array( $this, 'charts_menu_capability_validation' )
		);

		add_settings_field(
			'import_menu_capability',
			__( 'Import Menu Capability', 'dauc' ),
			array( $this, 'import_menu_capability_callback' ),
			'dauc_general_options',
			'dauc_general_settings_section'
		);

		register_setting(
			'dauc_general_options',
			'dauc_import_menu_capability',
			array( $this, 'import_menu_capability_validation' )
		);

		add_settings_field(
			'export_menu_capability',
			__( 'Export Menu Capability', 'dauc' ),
			array( $this, 'export_menu_capability_callback' ),
			'dauc_general_options',
			'dauc_general_settings_section'
		);

		register_setting(
			'dauc_general_options',
			'dauc_export_menu_capability',
			array( $this, 'export_menu_capability_validation' )
		);

		add_settings_field(
			'chartjs_library_url',
			__( 'Chart.js Library URL', 'dauc' ),
			array( $this, 'chartjs_library_url_callback' ),
			'dauc_general_options',
			'dauc_general_settings_section'
		);

		register_setting(
			'dauc_general_options',
			'dauc_chartjs_library_url',
			array( $this, 'chartjs_library_url_validation' )
		);

		add_settings_field(
			'compress_output',
			__( 'Compress Output', 'dauc' ),
			array( $this, 'compress_output_callback' ),
			'dauc_general_options',
			'dauc_general_settings_section'
		);

		register_setting(
			'dauc_general_options',
			'dauc_compress_output',
			array( $this, 'compress_output_validation' )
		);

        add_settings_field(
            'max_execution_time',
            __('Max Execution Time', 'dauc'),
            array($this, 'max_execution_time_callback'),
            'dauc_general_options',
            'dauc_general_settings_section'
        );

        register_setting(
            'dauc_general_options',
            'dauc_max_execution_time',
            array($this, 'max_execution_time_validation')
        );

        add_settings_field(
            'memory_limit',
            __('Memory Limit', 'dauc'),
            array($this, 'memory_limit_callback'),
            'dauc_general_options',
            'dauc_general_settings_section'
        );

        register_setting(
            'dauc_general_options',
            'dauc_memory_limit',
            array($this, 'memory_limit_validation')
        );

	}

	public function charts_menu_capability_callback($args){

		$html = '<input autocomplete="off" type="text" id="dauc-charts-menu-capability" name="dauc_charts_menu_capability" class="regular-text" value="' . esc_attr(get_option("dauc_charts_menu_capability")) . '" />';
		$html .= '<div class="help-icon" title="' . esc_attr(__('The capability required to get access on the "Charts" menu.', 'dauc')) . '"></div>';

		echo $html;

	}

	public function charts_menu_capability_validation($input){

		if(!preg_match($this->shared->regex_capability, $input)){
			add_settings_error( 'dauc_charts_menu_capability', 'dauc_charts_menu_capability', __('Please enter a valid capability in the "Charts Menu Capability" option.', 'dauc') );
			$output = get_option('dauc_charts_menu_capability');
		}else{
			$output = $input;
		}

		return trim($output);

	}

	public function import_menu_capability_callback($args){

		$html = '<input autocomplete="off" type="text" id="dauc-import-menu-capability" name="dauc_import_menu_capability" class="regular-text" value="' . esc_attr(get_option("dauc_import_menu_capability")) . '" />';
		$html .= '<div class="help-icon" title="' . esc_attr(__('The capability required to get access on the "Import" menu.', 'dauc')) . '"></div>';

		echo $html;

	}

	public function import_menu_capability_validation($input){

		if(!preg_match($this->shared->regex_capability, $input)){
			add_settings_error( 'dauc_import_menu_capability', 'dauc_import_menu_capability', __('Please enter a valid capability in the "Import Menu Capability" option.', 'dauc') );
			$output = get_option('dauc_import_menu_capability');
		}else{
			$output = $input;
		}

		return trim($output);

	}

	public function export_menu_capability_callback($args){

		$html = '<input autocomplete="off" type="text" id="dauc-export-menu-capability" name="dauc_export_menu_capability" class="regular-text" value="' . esc_attr(get_option("dauc_export_menu_capability")) . '" />';
		$html .= '<div class="help-icon" title="' . esc_attr(__('The capability required to get access on the "Export" menu.', 'dauc')) . '"></div>';

		echo $html;

	}

	public function export_menu_capability_validation($input){

		if(!preg_match($this->shared->regex_capability, $input)){
			add_settings_error( 'dauc_export_menu_capability', 'dauc_export_menu_capability', __('Please enter a valid capability in the "Export Menu Capability" option.', 'dauc') );
			$output = get_option('dauc_export_menu_capability');
		}else{
			$output = $input;
		}

		return trim($output);

	}

	public function chartjs_library_url_callback($args){

		$html = '<input autocomplete="off" type="text" id="dauc-chartjs-library-url" name="dauc_chartjs_library_url" class="regular-text" value="' . esc_attr(get_option("dauc_chartjs_library_url")) . '" />';
		$html .= '<div class="help-icon" title="' . esc_attr(__('The URL where the Chart.js library is located.', 'dauc')) . '"></div>';

		echo $html;

	}

	public function chartjs_library_url_validation($input){

		$output = $input;

		return trim($output);

	}

	public function compress_output_callback($args){

		$html = '<select id="dauc-compress-output" name="dauc_compress_output">';
		$html .= '<option ' . selected(intval(get_option("dauc_compress_output")), 0, false) . ' value="0">' . __('No', 'dauc') . '</option>';
		$html .= '<option ' . selected(intval(get_option("dauc_compress_output")), 1, false) . ' value="1">' . __('Yes', 'dauc') . '</option>';
		$html .= '</select>';
		$html .= '<div class="help-icon" title="' . esc_attr(__('This option determines if the JavaScript code used to create the charts in the front-end should be compressed. Please note that this function will pose an additional load on your server CPU and should be disabled if you want to decrease the time required to generate the charts.', 'dauc')) . '"></div>';

		echo $html;

	}

	public function compress_output_validation($input){

		return intval($input, 10) == 1 ? '1' : '0';

	}

    public function max_execution_time_callback($args)
    {

        $html = '<input autocomplete="off" type="text" id="dauc-max-execution-time" name="dauc_max_execution_time" class="regular-text" value="' . esc_attr(get_option("dauc_max_execution_time")) . '" />';
        $html .= '<div class="help-icon" title="' . esc_attr(__('Please enter a number from 1 to 1000000. This value determines the maximum number of seconds allowed to execute the PHP scripts used by this plugin to alter or display the data of the charts.', 'dauc')) . '"></div>';

        echo $html;

    }

    public function max_execution_time_validation($input)
    {

        if (!preg_match($this->shared->digits_regex, $input) or intval($input, 10) < 1 or intval($input, 10) > 1000000) {
            add_settings_error('dauc_max_execution_time', 'dauc_max_execution_time', __('Please enter a number from 1 to 1000000 in the "Max Execution Time Value" option.', 'dauc'));
            $output = get_option('dauc_max_execution_time');
        } else {
            $output = $input;
        }

        return intval($output, 10);

    }

    public function memory_limit_callback($args)
    {

        $html = '<input autocomplete="off" type="text" id="dauc-memory-limit" name="dauc_memory_limit" class="regular-text" value="' . esc_attr(get_option("dauc_memory_limit")) . '" />';
        $html .= '<div class="help-icon" title="' . esc_attr(__('Please enter a number from 1 to 1000000. This value determines the PHP memory limit in megabytes allowed to execute the PHP scripts used by this plugin to alter or display the data of the charts.', 'dauc')) . '"></div>';

        echo $html;

    }

    public function memory_limit_validation($input)
    {

        if (!preg_match($this->shared->digits_regex, $input) or intval($input, 10) < 1 or intval($input, 10) > 1000000) {
            add_settings_error('dauc_memory_limit', 'dauc_memory_limit', __('Please enter a number from 1 to 1000000 in the "Memory Limit Value" option.', 'dauc'));
            $output = get_option('dauc_memory_limit');
        } else {
            $output = $input;
        }

        return intval($output, 10);

    }

	/*
	 * Initialize the chart data based on the defined chart id, number of rows and number of columns
	 */
	public function initialize_chart_data($chart_id, $number_of_rows, $number_of_columns){

		//delete previous chart data
		global $wpdb;
		$table_name = $wpdb->prefix . $this->shared->get('slug') . "_data";
		$safe_sql = $wpdb->prepare("DELETE FROM $table_name WHERE chart_id = %d ", $chart_id);
		$query_result = $wpdb->query( $safe_sql );

		//create new chart data
		for($row_index=1;$row_index<=$number_of_rows;$row_index++){

			$row_data = array_fill(0, $number_of_columns, 0);
			$row_data_json = json_encode( $row_data );

			$this->shared->data_insert_default_record($chart_id, $row_index, $row_data_json);

		}

	}

	function create_tinymce_plugin() {

		if ( !current_user_can('manage_options') ) {
			return;
		}

		if ( get_user_option('rich_editing') == 'true' ) {

			//filter used to create the tinymce plugin
			add_filter( 'mce_external_plugins', array( $this, 'add_tinymce_plugin' ) );

			//filter used to create the tinymce button
			add_filter( 'mce_buttons', array( $this, 'register_tinymce_buttons' ) );

		}

	}

	//add the uberchart tinymce plugin
	public function add_tinymce_plugin( $plugin_array ) {

		$plugin_array['uberchart'] = $this->shared->get("url") . 'admin/assets/mceplugin/uberchart.js';
		return $plugin_array;

	}

	/*
	 * add the "selectuberchart" tinymce buttons
	 *
	 * @return array
	 */
	public function register_tinymce_buttons( $buttons ) {

		array_push( $buttons, "", "selectuberchart" );
		return $buttons;

	}

	public function disable_model_input($chart_id){

		global $wpdb; $table_name = $wpdb->prefix . $this->shared->get('slug') . "_chart";
		$safe_sql = $wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $chart_id);
		$chart_obj = $wpdb->get_row($safe_sql);

		if($chart_obj->is_model == 1){
			echo 'disabled="disabled"';
		}

	}

	/*
	 * Returns the number of records available in the charts db table
	 *
	 * @return int The number of records available in the charts db table
	 */
	public function number_of_charts(){

		global $wpdb;
		$table_name  = $wpdb->prefix . $this->shared->get( 'slug' ) . "_chart";
		$total_items = $wpdb->get_var( "SELECT COUNT(*) FROM $table_name WHERE temporary = 0" );

		return $total_items;

	}

	/*
	 * The click on the "Export" button available in the "Export" menu is intercepted and the
	 * method that generates the specific downloadable XML file is called
	 */
	public function export_xml_controller() {

		/*
		 * Intercept requests that come from the "Export" button from the
		 * "UberChart -> Export" menu and generate the downloadable XML file
		 */
		if ( isset( $_POST['dauc_export'] ) ) {

			//verify capability
			if ( !current_user_can(get_option( $this->shared->get('slug') . "_export_menu_capability")) )  {
				wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
			}

			//get the data from the db table
			global $wpdb;
			$table_name = $wpdb->prefix . $this->shared->get( 'slug' ) . "_chart";
			$chart_a    = $wpdb->get_results( "SELECT * FROM $table_name WHERE temporary = 0 ORDER BY id ASC", ARRAY_A );

			//if there are data generate the csv header and content
			if ( count( $chart_a ) > 0 ) {

				//generate the header of the XML file
				header( 'Content-Encoding: UTF-8' );
				header( 'Content-type: text/xml; charset=UTF-8' );
				header( "Content-Disposition: attachment; filename=uberchart-" . time() . ".xml" );
				header( "Pragma: no-cache" );
				header( "Expires: 0" );

				//generate initial part of the XML file
				$out =  '<?xml version="1.0" encoding="UTF-8" ?>';
				$out .= '<root>';

				//set column content
				foreach ( $chart_a as $chart ) {

					$out .= "<chart>";

						//get all the indexes of the $chart array
						$chart_keys = array_keys($chart);

						//cycle through all the indexes of $chart and create all the tags related to this record
						foreach($chart_keys as $key){

							$out .=     "<" . $key . ">" . esc_attr($chart[$key]) . "</" . $key . ">";

						}

						//add the data associated with this chart from the data db table
						$chart_id = $chart['id'];
						$table_name = $wpdb->prefix . $this->shared->get( 'slug' ) . "_data";
						$data_a    = $wpdb->get_results( "SELECT * FROM $table_name WHERE chart_id = $chart_id ORDER BY id ASC", ARRAY_A );

						$out .= "<dataset>";

						//create all the tags of the data structure enclosed in the <dataset> tag, each single record
						//is enclosed in the <data> tag
						foreach($data_a as $data){

							$out .= "<data>";

								//get all the indexes of the $data array
								$data_keys = array_keys($data);

								foreach($data_keys as $data_key){

									$out .=     "<" . $data_key . ">" . esc_attr($data[$data_key]) . "</" . $data_key . ">";

								}

							$out .= "</data>";

						}

						$out .= "</dataset>";

					$out .= "</chart>";

				}

				//generate the final part of the XML file
				$out .= '</root>';

			} else {
				return false;
			}

			echo $out;
			die();

		}

	}

	/*
	 * Convert the chart type saved in the database to the complete version that uses internationalization
	 */
	public function chart_type_nice_name($type){

		switch($type){

			case 'line':

				$out = __('Line', 'dauc');

				break;

			case 'bar':

				$out = __('Bar', 'dauc');

				break;

			case 'horizontalBar':

				$out = __('Horizontal Bar', 'dauc');

				break;

			case 'radar':

				$out = __('Radar', 'dauc');

				break;

			case 'polarArea':

				$out = __('Polar Area', 'dauc');

				break;

			case 'pie':

				$out = __('Pie', 'dauc');

				break;

			case 'doughnut':

				$out = __('Doughnut', 'dauc');

				break;

			case 'bubble':

				$out = __('Bubble', 'dauc');

				break;

		}

		return $out;

	}

	/*
	 * If the temporary chart are more than 100 clear the older (first inserted) temporary chart.
	 *
	 * This method is used to avoid un unlimited number of temporary charts stored in the 'chart' and 'data' db tables.
	 *
	 * By deleting all the temporary charts (and not only the last one like this method does) wouldn't be possible to
	 * work on multiple tabs on the 'Charts' menu without being unable to save the charts associated with the first
	 * opened tabs.
	 *
	 * With this method a maximum of 100 tabs can be opened in the 'Chart' menu to create a chart at the same time. If
	 * 101 tabs are for example opened, in the first of these 101 tabs the data of the chart will not be saved because
	 * the temporary data are deleted.
	 *
	 */
	public function delete_older_temporary_chart(){

		//get all the temporary charts as an array
		global $wpdb;
		$table_name   = $wpdb->prefix . $this->shared->get( 'slug' ) . "_chart";
		$temporary_chart_a = $wpdb->get_results( "SELECT * FROM $table_name WHERE temporary = 1 ORDER BY id", ARRAY_A );

		//verify if the temporary charts are more than 100
		if(count($temporary_chart_a) > 100){

			//get the id of the older (first) inserted chart
			$older_id = $temporary_chart_a[0]['id'];

			//delete all the older (first inserted) temporary charts
			global $wpdb;
			$table_name   = $wpdb->prefix . $this->shared->get( 'slug' ) . "_chart";
			$safe_sql = $wpdb->prepare( "DELETE FROM $table_name WHERE id = %d", $older_id );
			$result     = $wpdb->query( $safe_sql );

			//delete all the data associated with the older (first inserted) temporary charts
			$table_name = $wpdb->prefix . $this->shared->get( 'slug' ) . "_data";
			$safe_sql   = $wpdb->prepare( "DELETE FROM $table_name WHERE chart_id = %d", $older_id );
			$result     = $wpdb->query( $safe_sql );

		}

	}

}