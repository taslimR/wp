<?php

/*
 * this class should be used to include ajax actions
 */

class Dauc_Ajax {

	protected static $instance = null;
	private $shared = null;

	private function __construct() {

		//assign an instance of the plugin info
		$this->shared = Dauc_Shared::get_instance();

		//ajax requests --------------------------------------------------------

		//for logged-in and not-logged-in users --------------------------------

		//for logged-in users --------------------------------------------------
		add_action( 'wp_ajax_save_data', array( $this, 'save_data' ) );
		add_action( 'wp_ajax_retrieve_row_data', array( $this, 'retrieve_row_data' ) );
		add_action( 'wp_ajax_update_data_structure', array( $this, 'update_data_structure' ) );
		add_action( 'wp_ajax_retrieve_chart_data', array( $this, 'retrieve_chart_data' ) );
		add_action( 'wp_ajax_add_remove_rows', array( $this, 'add_remove_rows' ) );
		add_action( 'wp_ajax_add_remove_columns', array( $this, 'add_remove_columns' ) );
		add_action( 'wp_ajax_load_model', array( $this, 'load_model' ) );

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
	 * Ajax handler used to save the data of the chart
	 *
	 * This method is called when:
	 *
	 * - The "Add Chart" is clicked
	 * - The "Update Chart" button is clicked
	 * - The chart preview tab is opened
	 * - The chart preview is refreshed with the "Save your changes and refresh the preview." button
	 */
	public function save_data() {

		//check the referer
		if ( ! check_ajax_referer( 'dauc', 'security', false ) ) {
			echo "Invalid AJAX Request";
			die();
		}

		//check the capability
		if ( !current_user_can(get_option( $this->shared->get('slug') . "_charts_menu_capability")) )  {
			echo 'Invalid Capability';
			die();
		}

        //Set the custom "Max Execution Time" defined in the options
        ini_set('max_execution_time', intval(get_option($this->shared->get('slug') . "_max_execution_time"), 10));

        //Set the custom "Memory Limit" (in megabytes) defined in the options
        ini_set('memory_limit', intval(get_option($this->shared->get('slug') . "_memory_limit"), 10) . 'M');

		extract($_POST);

		//validate data ------------------------------------------------------------------------------------------------
		$fields_with_errors_a = array();

		//define regex patterns ----------------------------------------------------------------------------------------

		//match an integer or a float value
		$patt_integer_or_float = '/^(\d+\.\d+)|\d+$/';

		//match an integer or a float value with sign
		$patt_integer_or_float_with_sign = '/^(\+|-)?(\d+\.\d+)|\d+$/';

		//match a float value
		$patt_float = '/^\d\.\d$/';

        //match a hex rgb color or a rgba color
		$patt_color = '/^((\#([0-9a-fA-F]{3}){1,2})|(rgba\(\d{1,3},\d{1,3},\d{1,3},(\d{1}|\d{1}\.\d{1,2})\)))$/';

		//match a hex rgb color, a rgba color or a comma separated list of hex rgb colors and rgba colors
		$patt_color_or_colors = '/^((\#([0-9a-fA-F]{3}){1,2})|(rgba\(\d{1,3},\d{1,3},\d{1,3},(\d{1}|\d{1}\.\d{1,2})\)))(,(\#([0-9a-fA-F]{3}){1,2}|rgba\(\d{1,3},\d{1,3},\d{1,3},(\d{1}|\d{1}\.\d{1,2})\)))*$/';

		//match an integer or a comma separated list of integers
		$patt_integer_or_integers = '/^(\d+|(\d+(,\d+)*))$/';

        //match an integer
		$patt_integer = '/^\d+$/';

        //match a font family or a comma separated list of font families
		$patt_font_family_or_font_families = "/^(('[^'\"]+'|[^,\"]+)(,('[^'\"]+'|[^,\"]+))*)$/";

		//match a moment.js format tokens, for details see: http://momentjs.com/docs/#/displaying/format/
		$patt_moment_format_tokens = '/^(\s|\/|,|:|M|Mo|MM|MMM|MMMM|Q|Qo|D|Do|DD|DDD|DDDo|DDDD|d|do|dd|ddd|dddd|e|E|w|ww|W|Wo|WW|YY|YYYY|Y|gg|gggg|GG|GGGG|A|a|H|HH|h|hh|k|kk|m|mm|s|ss|S|SS|SSS|z|zz|Z|ZZ|X|x)+$/';

		//match a moment.js format output, for details see: http://momentjs.com/docs/#/displaying/format/
		$patt_moment_format_output = '/^(\s|[0-9]|[a-z]|[A-Z]|\+|-|\[|\]|:|\.|\/|,)+$/';

        //validate data ------------------------------------------------------------------------------------------------

		//Basic Info
		if( strlen(trim($name)) < 1 or strlen(trim($name)) > 200 ){$fields_with_errors_a[] = 'name';}
		if( strlen(trim($description)) < 1 or strlen(trim($description)) > 200 ){$fields_with_errors_a[] = 'description';}

        //Common Chart Configuration
		if(!preg_match($patt_color, $canvas_backgroundColor)){$fields_with_errors_a[] = 'canvas_backgroundColor';}
		if(!preg_match($patt_integer, $margin_top)){$fields_with_errors_a[] = 'margin_top';}
		if(!preg_match($patt_integer, $margin_bottom)){$fields_with_errors_a[] = 'margin_bottom';}
		if(!preg_match($patt_integer, $width)){$fields_with_errors_a[] = 'width';}
		if(!preg_match($patt_integer, $height)){$fields_with_errors_a[] = 'height';}
		if(!preg_match($patt_integer, $responsiveAnimationDuration)){$fields_with_errors_a[] = 'responsiveAnimationDuration';}
		if(!preg_match($patt_integer, $fixed_height)){$fields_with_errors_a[] = 'fixed_height';}

        //Title Configuration
		if(!preg_match($patt_integer, $title_fontSize)){$fields_with_errors_a[] = 'title_fontSize';}
		if(!preg_match($patt_font_family_or_font_families, $title_fontFamily)){$fields_with_errors_a[] = 'title_fontFamily';}
		if(!preg_match($patt_color, $title_fontColor)){$fields_with_errors_a[] = 'title_fontColor';}
		if(!preg_match($patt_integer, $title_padding)){$fields_with_errors_a[] = 'title_padding';}

        //Legend Label Configuration
		if(!preg_match($patt_integer, $legend_labels_boxWidth)){$fields_with_errors_a[] = 'legend_labels_boxWidth';}
		if(!preg_match($patt_integer, $legend_labels_fontSize)){$fields_with_errors_a[] = 'legend_labels_fontSize';}
		if(!preg_match($patt_color, $legend_labels_fontColor)){$fields_with_errors_a[] = 'legend_labels_fontColor';}
		if(!preg_match($patt_font_family_or_font_families, $legend_labels_fontFamily)){$fields_with_errors_a[] = 'legend_labels_fontFamily';}
		if(!preg_match($patt_integer, $legend_labels_padding)){$fields_with_errors_a[] = 'legend_labels_padding';}

        //Tooltip Configuration
		if(!preg_match($patt_color, $tooltips_backgroundColor)){$fields_with_errors_a[] = 'tooltips_backgroundColor';}
		if(!preg_match($patt_font_family_or_font_families, $tooltips_titleFontFamily)){$fields_with_errors_a[] = 'tooltips_titleFontFamily';}
		if(!preg_match($patt_integer, $tooltips_titleFontSize)){$fields_with_errors_a[] = 'tooltips_titleFontSize';}
		if(!preg_match($patt_color, $tooltips_titleFontColor)){$fields_with_errors_a[] = 'tooltips_titleFontColor';}
		if(!preg_match($patt_integer, $tooltips_titleMarginBottom)){$fields_with_errors_a[] = 'tooltips_titleMarginBottom';}
		if(!preg_match($patt_font_family_or_font_families, $tooltips_bodyFontFamily)){$fields_with_errors_a[] = 'tooltips_bodyFontFamily';}
		if(!preg_match($patt_integer, $tooltips_bodyFontSize)){$fields_with_errors_a[] = 'tooltips_bodyFontSize';}
		if(!preg_match($patt_color, $tooltips_bodyFontColor)){$fields_with_errors_a[] = 'tooltips_bodyFontColor';}
		if(!preg_match($patt_font_family_or_font_families, $tooltips_footerFontFamily)){$fields_with_errors_a[] = 'tooltips_footerFontFamily';}
		if(!preg_match($patt_integer, $tooltips_footerFontSize)){$fields_with_errors_a[] = 'tooltips_footerFontSize';}
		if(!preg_match($patt_color, $tooltips_footerFontColor)){$fields_with_errors_a[] = 'tooltips_footerFontColor';}
		if(!preg_match($patt_integer, $tooltips_footerMarginTop)){$fields_with_errors_a[] = 'tooltips_footerMarginTop';}
		if(!preg_match($patt_integer, $tooltips_xPadding)){$fields_with_errors_a[] = 'tooltips_xPadding';}
		if(!preg_match($patt_integer, $tooltips_yPadding)){$fields_with_errors_a[] = 'tooltips_yPadding';}
		if(!preg_match($patt_integer, $tooltips_caretSize)){$fields_with_errors_a[] = 'tooltips_caretSize';}
		if(!preg_match($patt_integer, $tooltips_cornerRadius)){$fields_with_errors_a[] = 'tooltips_cornerRadius';}
		if(!preg_match($patt_color, $tooltips_multiKeyBackground)){$fields_with_errors_a[] = 'tooltips_multiKeyBackground';}

        //Hover Configuration
		if(!preg_match($patt_integer, $hover_animationDuration)){$fields_with_errors_a[] = 'hover_animationDuration';}

        //Animation Configuration
		if(!preg_match($patt_integer, $animation_duration)){$fields_with_errors_a[] = 'animation_duration';}

		//X Scale Grid Line
		if(!preg_match($patt_color_or_colors, $scales_xAxes_gridLines_color)){$fields_with_errors_a[] = 'Color (X Scale Grid Line)';}
		if(!preg_match($patt_integer_or_integers, $scales_xAxes_gridLines_lineWidth)){$fields_with_errors_a[] = 'Line Width (X Scale Grid Line)';}
		if(!preg_match($patt_integer, $scales_xAxes_gridLines_tickMarkLength)){$fields_with_errors_a[] = 'Tick Mark Length (X Scale Grid Line)';}
		if(!preg_match($patt_integer, $scales_xAxes_gridLines_zeroLineWidth)){$fields_with_errors_a[] = 'Zero Line Width (X Scale Grid Line)';}
		if(!preg_match($patt_color, $scales_xAxes_gridLines_zeroLineColor)){$fields_with_errors_a[] = 'Zero Line Color (X Scale Grid Line)';}

		//X Scale Title
		if(!preg_match($patt_color, $scales_xAxes_scaleLabel_fontColor)){$fields_with_errors_a[] = 'Font Color (X Scale Title)';}
		if(!preg_match($patt_font_family_or_font_families, $scales_xAxes_scaleLabel_fontFamily)){$fields_with_errors_a[] = 'Font Family (X Scale Title)';}
		if(!preg_match($patt_integer, $scales_xAxes_scaleLabel_fontSize)){$fields_with_errors_a[] = 'Font Size (X Scale Title)';}

		//X Scale Tick
		if(!preg_match($patt_color, $scales_xAxes_ticks_fontColor)){$fields_with_errors_a[] = 'Font Color (X Scale Tick)';}
		if(!preg_match($patt_font_family_or_font_families, $scales_xAxes_ticks_fontFamily)){$fields_with_errors_a = 'Font Family (X Scale Tick)';}
		if(!preg_match($patt_integer, $scales_xAxes_ticks_fontSize)){$fields_with_errors_a = 'Font Size (X Scale Tick)';}
		if(!preg_match($patt_integer, $scales_xAxes_ticks_labelOffset)){$fields_with_errors_a[] = 'Label Offset (X Scale Tick)';}
		if(!preg_match($patt_integer, $scales_xAxes_ticks_maxRotation)){$fields_with_errors_a[] = 'Max Rotation (X Scale Tick)';}
		if(!preg_match($patt_integer, $scales_xAxes_ticks_minRotation)){$fields_with_errors_a[] = 'Min Rotation (X Scale Tick)';}
		if( !preg_match($patt_integer, $scales_xAxes_ticks_round) && strlen(trim($scales_xAxes_ticks_round)) !== 0  ){$fields_with_errors_a[] = 'Round (X Scale Tick)';}

		//X Scale Options
		if( !preg_match($patt_integer_or_float_with_sign, $scales_xAxes_ticks_min) && strlen(trim($scales_xAxes_ticks_min)) !== 0  ){$fields_with_errors_a[] = 'Min (X Scale Options)';}
		if( !preg_match($patt_integer_or_float_with_sign, $scales_xAxes_ticks_max) && strlen(trim($scales_xAxes_ticks_max)) !== 0  ){$fields_with_errors_a[] = 'Max (X Scale Options)';}
		if( !preg_match($patt_integer, $scales_xAxes_ticks_maxTicksLimit) && strlen(trim($scales_xAxes_ticks_maxTicksLimit)) !== 0 ){$fields_with_errors_a = 'Max Limit (X Scale Options)';}
		if( !preg_match($patt_integer_or_float, $scales_xAxes_ticks_stepSize) && strlen(trim($scales_xAxes_ticks_stepSize)) !== 0  ){$fields_with_errors_a[] = 'Step Size (X Scale Options)';}
		if( !preg_match($patt_integer_or_float_with_sign, $scales_xAxes_ticks_suggestedMax) && strlen(trim($scales_xAxes_ticks_suggestedMax)) !== 0  ){$fields_with_errors_a[] = 'Suggested Max (X Scale Options)';}
		if( !preg_match($patt_integer_or_float_with_sign, $scales_xAxes_ticks_suggestedMin) && strlen(trim($scales_xAxes_ticks_suggestedMin)) !== 0  ){$fields_with_errors_a[] = 'Suggested Min (X Scale Options)';}
		if( !preg_match($patt_integer_or_float, $scales_xAxes_ticks_fixedStepSize) && strlen(trim($scales_xAxes_ticks_fixedStepSize)) !== 0  ){$fields_with_errors_a[] = 'Fixed Step Size (X Scale Options)';}
		if(!preg_match($patt_integer_or_float, $scales_xAxes_categoryPercentage)){$fields_with_errors_a[] = 'Category Percentage (X Scale Options)';}
		if(!preg_match($patt_integer_or_float, $scales_xAxes_barPercentage)){$fields_with_errors_a[] = 'Bar Percentage (X Scale Options)';}

		//X Scale Time
		if(!preg_match($patt_moment_format_tokens, $scales_xAxes_time_format)){$fields_with_errors_a[] = 'Time Format (X Scale Time)';}
		if(!preg_match($patt_moment_format_tokens, $scales_xAxes_time_tooltipFormat)){$fields_with_errors_a[] = 'Tooltip Format (X Scale Time)';}
		if(!preg_match($patt_moment_format_tokens, $scales_xAxes_time_unit_format)){$fields_with_errors_a[] = 'Unit Format (X Scale Time)';}
		if( !preg_match($patt_moment_format_output, $scales_xAxes_time_min) && strlen(trim($scales_xAxes_time_min)) !== 0 ){$fields_with_errors_a[] = 'Min (X Scale Time)';}
		if( !preg_match($patt_moment_format_output, $scales_xAxes_time_max) && strlen(trim($scales_xAxes_time_max)) !== 0 ){$fields_with_errors_a[] = 'Max (X Scale Time)';}
		if(!preg_match($patt_integer, $scales_xAxes_time_unitStepSize)){$fields_with_errors_a[] = 'Unit Step Size (X Scale Time)';}

		//Y Scale Grid Line
		if(!preg_match($patt_color_or_colors, $scales_yAxes_gridLines_color)){$fields_with_errors_a[] = 'Color (Y Scale Grid Line)';}
		if(!preg_match($patt_integer_or_integers, $scales_yAxes_gridLines_lineWidth)){$fields_with_errors_a[] = 'Line Width (Y Scale Grid Line)';}
		if(!preg_match($patt_integer, $scales_yAxes_gridLines_tickMarkLength)){$fields_with_errors_a[] = 'Tick Mark Length (Y Scale Grid Line)';}
		if(!preg_match($patt_integer, $scales_yAxes_gridLines_zeroLineWidth)){$fields_with_errors_a[] = 'Zero Line Width (Y Scale Grid Line)';}
		if(!preg_match($patt_color, $scales_yAxes_gridLines_zeroLineColor)){$fields_with_errors_a[] = 'Zero Line Color (Y Scale Grid Line)';}

		//Y Scale Title
		if(!preg_match($patt_color, $scales_yAxes_scaleLabel_fontColor)){$fields_with_errors_a[] = 'Font Color (Y Scale Title)';}
		if(!preg_match($patt_font_family_or_font_families, $scales_yAxes_scaleLabel_fontFamily)){$fields_with_errors_a[] = 'Font Family (Y Scale Title)';}
		if(!preg_match($patt_integer, $scales_yAxes_scaleLabel_fontSize)){$fields_with_errors_a[] = 'Font Size (Y Scale Title)';}

		//Y Scale Tick
		if(!preg_match($patt_color, $scales_yAxes_ticks_fontColor)){$fields_with_errors_a[] = 'Font Color (Y Scale Tick)';}
		if(!preg_match($patt_font_family_or_font_families, $scales_yAxes_ticks_fontFamily)){$fields_with_errors_a = 'Font Family (Y Scale Tick)';}
		if(!preg_match($patt_integer, $scales_yAxes_ticks_fontSize)){$fields_with_errors_a = 'Font Size (Y Scale Tick)';}
		if(!preg_match($patt_integer, $scales_yAxes_ticks_maxRotation)){$fields_with_errors_a[] = 'Max Rotation (Y Scale Tick)';}
		if(!preg_match($patt_integer, $scales_yAxes_ticks_minRotation)){$fields_with_errors_a[] = 'Min Rotation (Y Scale Tick)';}
		if(!preg_match($patt_integer, $scales_yAxes_ticks_padding)){$fields_with_errors_a[] = 'Padding (Y Scale Tick)';}
		if( !preg_match($patt_integer, $scales_yAxes_ticks_round) && strlen(trim($scales_yAxes_ticks_round)) !== 0  ){$fields_with_errors_a[] = 'Round (Y Scale Tick)';}

		//Y Scale Options
		if( !preg_match($patt_integer_or_float_with_sign, $scales_yAxes_ticks_min) && strlen(trim($scales_yAxes_ticks_min)) !== 0  ){$fields_with_errors_a[] = 'Min (Y Scale Options)';}
		if( !preg_match($patt_integer_or_float_with_sign, $scales_yAxes_ticks_max) && strlen(trim($scales_yAxes_ticks_max)) !== 0  ){$fields_with_errors_a[] = 'Max (Y Scale Options)';}
		if( !preg_match($patt_integer, $scales_yAxes_ticks_maxTicksLimit) && strlen(trim($scales_yAxes_ticks_maxTicksLimit)) !== 0 ){$fields_with_errors_a = 'Max Limit (Y Scale Options)';}
		if( !preg_match($patt_integer_or_float, $scales_yAxes_ticks_stepSize) && strlen(trim($scales_yAxes_ticks_stepSize)) !== 0  ){$fields_with_errors_a[] = 'Step Size (Y Scale Options)';}
		if( !preg_match($patt_integer_or_float_with_sign, $scales_yAxes_ticks_suggestedMax) && strlen(trim($scales_yAxes_ticks_suggestedMax)) !== 0  ){$fields_with_errors_a[] = 'Suggested Max (Y Scale Options)';}
		if( !preg_match($patt_integer_or_float_with_sign, $scales_yAxes_ticks_suggestedMin) && strlen(trim($scales_yAxes_ticks_suggestedMin)) !== 0  ){$fields_with_errors_a[] = 'Suggested Min (Y Scale Options)';}
		if( !preg_match($patt_integer_or_float, $scales_yAxes_ticks_fixedStepSize) && strlen(trim($scales_yAxes_ticks_fixedStepSize)) !== 0  ){$fields_with_errors_a[] = 'Fixed Step Size (Y Scale Options)';}
		if(!preg_match($patt_integer_or_float, $scales_yAxes_categoryPercentage)){$fields_with_errors_a[] = 'Category Percentage (Y Scale Options)';}
		if(!preg_match($patt_integer_or_float, $scales_yAxes_barPercentage)){$fields_with_errors_a[] = 'Bar Percentage (Y Scale Options)';}

		//Y2 Scale Grid Line
		if(!preg_match($patt_color_or_colors, $scales_y2Axes_gridLines_color)){$fields_with_errors_a[] = 'Color (Y2 Scale Grid Line)';}
		if(!preg_match($patt_integer_or_integers, $scales_y2Axes_gridLines_lineWidth)){$fields_with_errors_a[] = 'Line Width (Y2 Scale Grid Line)';}
		if(!preg_match($patt_integer, $scales_y2Axes_gridLines_tickMarkLength)){$fields_with_errors_a[] = 'Tick Mark Length (Y2 Scale Grid Line)';}
		if(!preg_match($patt_integer, $scales_y2Axes_gridLines_zeroLineWidth)){$fields_with_errors_a[] = 'Zero Line Width (Y2 Scale Grid Line)';}
		if(!preg_match($patt_color, $scales_y2Axes_gridLines_zeroLineColor)){$fields_with_errors_a[] = 'Zero Line Color (Y2 Scale Grid Line)';}

		//Y2 Scale Title
		if(!preg_match($patt_color, $scales_y2Axes_scaleLabel_fontColor)){$fields_with_errors_a[] = 'Font Color (Y2 Scale Title)';}
		if(!preg_match($patt_font_family_or_font_families, $scales_y2Axes_scaleLabel_fontFamily)){$fields_with_errors_a[] = 'Font Family (Y2 Scale Title)';}
		if(!preg_match($patt_integer, $scales_y2Axes_scaleLabel_fontSize)){$fields_with_errors_a[] = 'Font Size (Y2 Scale Title)';}

		//Y2 Scale Tick
		if(!preg_match($patt_color, $scales_y2Axes_ticks_fontColor)){$fields_with_errors_a[] = 'Font Color (Y2 Scale Tick)';}
		if(!preg_match($patt_font_family_or_font_families, $scales_y2Axes_ticks_fontFamily)){$fields_with_errors_a = 'Font Family (Y2 Scale Tick)';}
		if(!preg_match($patt_integer, $scales_y2Axes_ticks_fontSize)){$fields_with_errors_a = 'Font Size (Y2 Scale Tick)';}
		if(!preg_match($patt_integer, $scales_y2Axes_ticks_maxRotation)){$fields_with_errors_a[] = 'Max Rotation (Y2 Scale Tick)';}
		if(!preg_match($patt_integer, $scales_y2Axes_ticks_minRotation)){$fields_with_errors_a[] = 'Min Rotation (Y2 Scale Tick)';}
		if(!preg_match($patt_integer, $scales_y2Axes_ticks_padding)){$fields_with_errors_a[] = 'Padding (Y2 Scale Tick)';}
		if( !preg_match($patt_integer, $scales_y2Axes_ticks_round) && strlen(trim($scales_y2Axes_ticks_round)) !== 0  ){$fields_with_errors_a[] = 'Round (Y2 Scale Tick)';}

		//Y2 Scale Options
		if( !preg_match($patt_integer_or_float_with_sign, $scales_y2Axes_ticks_min) && strlen(trim($scales_y2Axes_ticks_min)) !== 0  ){$fields_with_errors_a[] = 'Min (Y2 Scale Options)';}
		if( !preg_match($patt_integer_or_float_with_sign, $scales_y2Axes_ticks_max) && strlen(trim($scales_y2Axes_ticks_max)) !== 0  ){$fields_with_errors_a[] = 'Max (Y2 Scale Options)';}
		if( !preg_match($patt_integer, $scales_y2Axes_ticks_maxTicksLimit) && strlen(trim($scales_y2Axes_ticks_maxTicksLimit)) !== 0 ){$fields_with_errors_a = 'Max Limit (Y2 Scale Options)';}
		if( !preg_match($patt_integer_or_float, $scales_y2Axes_ticks_stepSize) && strlen(trim($scales_y2Axes_ticks_stepSize)) !== 0  ){$fields_with_errors_a[] = 'Step Size (Y2 Scale Options)';}
		if( !preg_match($patt_integer_or_float_with_sign, $scales_y2Axes_ticks_suggestedMax) && strlen(trim($scales_y2Axes_ticks_suggestedMax)) !== 0  ){$fields_with_errors_a[] = 'Suggested Max (Y2 Scale Options)';}
		if( !preg_match($patt_integer_or_float_with_sign, $scales_y2Axes_ticks_suggestedMin) && strlen(trim($scales_y2Axes_ticks_suggestedMin)) !== 0  ){$fields_with_errors_a[] = 'Suggested Min (Y2 Scale Options)';}
		if( !preg_match($patt_integer_or_float, $scales_y2Axes_ticks_fixedStepSize) && strlen(trim($scales_y2Axes_ticks_fixedStepSize)) !== 0  ){$fields_with_errors_a[] = 'Fixed Step Size (Y2 Scale Options)';}

		/* RL Scale Grid Line Configuration */
		if(!preg_match($patt_color_or_colors, $scales_rl_gridLines_color)){$fields_with_errors_a[] = 'Color (RL Scale Grid Line)';}
		if(!preg_match($patt_integer_or_integers, $scales_rl_gridLines_lineWidth)){$fields_with_errors_a[] = 'Line Width (RL Scale Grid Line)';}

		/* RL Scale Angle Line Configuration */
		if(!preg_match($patt_color, $scales_rl_angleLines_color)){$fields_with_errors_a[] = 'Color (RL Scale Angle Line)';}
		if(!preg_match($patt_integer, $scales_rl_angleLines_lineWidth)){$fields_with_errors_a[] = 'Line Width (RL Scale Angle Line)';}

		/* RL Scale Point Label Configuration */
		if(!preg_match($patt_integer, $scales_rl_pointLabels_fontSize)){$fields_with_errors_a[] = 'Font Size (RL Scale Point Label)';}
		if(!preg_match($patt_color, $scales_rl_pointLabels_fontColor)){$fields_with_errors_a[] = 'Font Color (RL Scale Point Label)';}
		if(!preg_match($patt_font_family_or_font_families, $scales_rl_pointLabels_fontFamily)){$fields_with_errors_a[] = 'Font Family (RL Scale Point Label)';}

		/* RL Scale Tick Configuration */
		if(!preg_match($patt_integer, $scales_rl_ticks_round) && strlen(trim($scales_rl_ticks_round)) !== 0  ){$fields_with_errors_a[] = 'Round (RL Scale Tick)';}
		if(!preg_match($patt_integer, $scales_rl_ticks_fontSize)){$fields_with_errors_a[] = 'Font Size (RL Scale Tick)';}
		if(!preg_match($patt_color, $scales_rl_ticks_fontColor)){$fields_with_errors_a[] = 'Font Color (RL Scale Tick)';}
		if(!preg_match($patt_font_family_or_font_families, $scales_rl_ticks_fontFamily)){$fields_with_errors_a[] = 'Font Family (RL Scale Tick)';}

		/* RL Scale Configuration Options */
		if( !preg_match($patt_integer_or_float_with_sign, $scales_rl_ticks_min) && strlen(trim($scales_rl_ticks_min)) !== 0  ){$fields_with_errors_a[] = 'Min (RL Scale Options)';}
		if( !preg_match($patt_integer_or_float_with_sign, $scales_rl_ticks_max) && strlen(trim($scales_rl_ticks_max)) !== 0  ){$fields_with_errors_a[] = 'Max (RL Scale Options)';}
		if( !preg_match($patt_integer_or_float_with_sign, $scales_rl_ticks_suggestedMin) && strlen(trim($scales_rl_ticks_suggestedMin)) !== 0  ){$fields_with_errors_a[] = 'Suggested Min (RL Scale Options)';}
		if( !preg_match($patt_integer_or_float_with_sign, $scales_rl_ticks_suggestedMax) && strlen(trim($scales_rl_ticks_suggestedMax)) !== 0  ){$fields_with_errors_a[] = 'Suggested Max (RL Scale Options)';}
		if( !preg_match($patt_integer_or_float, $scales_rl_ticks_stepSize) && strlen(trim($scales_rl_ticks_stepSize)) !== 0  ){$fields_with_errors_a[] = 'Step Size (RL Scale Options)';}
		if( !preg_match($patt_integer_or_float, $scales_rl_ticks_fixedStepSize) && strlen(trim($scales_rl_ticks_fixedStepSize)) !== 0 ){$fields_with_errors_a[] = 'Fixed Step Size (RL Scale Options)';}
		if( !preg_match($patt_integer, $scales_rl_ticks_maxTicksLimit) && strlen(trim($scales_rl_ticks_maxTicksLimit)) !== 0 ){$fields_with_errors_a[] = 'Max Ticks Limit (RL Scale Options)';}
		if(!preg_match($patt_color, $scales_rl_ticks_backdropColor)){$fields_with_errors_a[] = 'Backdrop Color (RL Scale Options)';}
		if(!preg_match($patt_integer, $scales_rl_ticks_backdropPaddingX)){$fields_with_errors_a[] = 'Backdrop Padding X (RL Scale Options)';}
		if(!preg_match($patt_integer, $scales_rl_ticks_backdropPaddingY)){$fields_with_errors_a[] = 'Backdrop Padding Y (RL Scale Options)';}

		//return an error message if the submitted data are not valid
        if( count($fields_with_errors_a) > 0){
	        echo 'Failed validation on the following fields: ' . implode(', ', $fields_with_errors_a);
	        die();
        }

		//UPDATE THE CHART ---------------------------------------------------------------------------------------------

		//get the labels from the first row of the table
		$chart_data   = json_decode( stripslashes( $_POST['chart_data'] ) );
		$chart_data_a = $chart_data->data;
		$labels = json_encode( $chart_data_a[0] );

		//save the chart data in the 'chart' db table
		global $wpdb;
		$table_name = $wpdb->prefix . $this->shared->get( 'slug' ) . "_chart";
		$safe_sql   = $wpdb->prepare( "UPDATE $table_name SET

			name = %s,
			description = %s,
			type = %s,
			rows = %d,
			columns = %d,
			labels = %s,
			is_model = %d,

			/* Common Chart Configuration */
			canvas_transparent_background = %d,
			canvas_backgroundColor = %s,
			margin_top = %d,
			margin_bottom = %d,
			width = %d,
			height = %d,
			responsive = %d,
			responsiveAnimationDuration = %d,
			maintainAspectRatio = %d,
			fixed_height = %d,

			/* Title Configuration */
			title_display = %d,
			title_position = %s,
			title_fullWidth = %d,
			title_fontSize = %d,
			title_fontFamily = %s,
			title_fontColor = %s,
			title_fontStyle = %s,
			title_padding = %d,

			/* Legend Configuration */
			legend_display = %d,
			legend_position = %s,
			legend_fullWidth = %d,
			legend_toggle_dataset = %d,

			/* Legend Label Configuration */
			legend_labels_boxWidth = %d,
			legend_labels_fontSize = %d,
			legend_labels_fontStyle = %s,
			legend_labels_fontColor = %s,
			legend_labels_fontFamily = %s,
			legend_labels_padding = %d,

			/* Tooltip Configuration */
			tooltips_enabled = %d,
			tooltips_mode = %s,
			tooltips_backgroundColor = %s,
			tooltips_titleFontFamily = %s,
			tooltips_titleFontSize = %d,
			tooltips_titleFontStyle = %s,
			tooltips_titleFontColor = %s,
			tooltips_titleMarginBottom = %d,
			tooltips_bodyFontFamily = %s,
			tooltips_bodyFontSize = %d,
			tooltips_bodyFontStyle = %s,
			tooltips_bodyFontColor = %s,
			tooltips_footerFontFamily = %s,
			tooltips_footerFontSize = %d,
			tooltips_footerFontStyle = %s,
			tooltips_footerFontColor = %s,
			tooltips_footerMarginTop = %d,
			tooltips_xPadding = %d,
			tooltips_yPadding = %d,
			tooltips_caretSize = %d,
			tooltips_cornerRadius = %d,
			tooltips_multiKeyBackground = %s,
            tooltips_beforeTitle = %s,
            tooltips_afterTitle = %s,
            tooltips_beforeBody = %s,
            tooltips_afterBody = %s,
            tooltips_beforeLabel = %s,
            tooltips_afterLabel = %s,
            tooltips_beforeFooter = %s,
            tooltips_footer = %s,
            tooltips_afterFooter = %s,

			/* Hover Configuration */
			hover_animationDuration = %d,

			/* Animation Configuration */
			animation_duration = %d,
			animation_easing = %s,
			animation_animateRotate = %d,
			animation_animateScale = %d,

			/* Misc Configuration */
			elements_rectangle_borderSkipped = %s,

			/* Scales Common Configuration X */
			scales_xAxes_type = %s,
			scales_xAxes_display = %d,
			scales_xAxes_position = %s,
			scales_xAxes_stacked = %d,

			/* Scales Grid Line Configuration X */
            scales_xAxes_gridLines_display = %d,
            scales_xAxes_gridLines_color = %s,
            scales_xAxes_gridLines_lineWidth = %s,
            scales_xAxes_gridLines_drawBorder = %d,
            scales_xAxes_gridLines_drawOnChartArea = %d,
            scales_xAxes_gridLines_drawTicks = %d,
            scales_xAxes_gridLines_tickMarkLength = %d,
            scales_xAxes_gridLines_zeroLineWidth = %d,
            scales_xAxes_gridLines_zeroLineColor = %s,
            scales_xAxes_gridLines_offsetGridLines = %d,

            /* Scales Title Configuration X */
            scales_xAxes_scaleLabel_display = %d,
            scales_xAxes_scaleLabel_labelString = %s,
            scales_xAxes_scaleLabel_fontColor = %s,
            scales_xAxes_scaleLabel_fontFamily = %s,
            scales_xAxes_scaleLabel_fontSize = %d,
            scales_xAxes_scaleLabel_fontStyle = %s,

            /* Scales Tick Configuration X */
            scales_xAxes_ticks_autoskip = %d,
            scales_xAxes_ticks_display = %d,
            scales_xAxes_ticks_fontColor = %s,
            scales_xAxes_ticks_fontFamily = %s,
            scales_xAxes_ticks_fontSize = %d,
            scales_xAxes_ticks_fontStyle = %s,
            scales_xAxes_ticks_labelOffset = %d,
            scales_xAxes_ticks_maxRotation = %d,
            scales_xAxes_ticks_minRotation = %d,
            scales_xAxes_ticks_reverse = %d,
            scales_xAxes_ticks_prefix = %s,
            scales_xAxes_ticks_suffix = %s,
            scales_xAxes_ticks_round = %s,

            /* Scale Configuration Options X */
            scales_xAxes_ticks_min = %s,
            scales_xAxes_ticks_max = %s,
            scales_xAxes_ticks_beginAtZero = %d,
            scales_xAxes_ticks_maxTicksLimit = %s,
            scales_xAxes_ticks_stepSize = %s,
            scales_xAxes_ticks_suggestedMax = %s,
            scales_xAxes_ticks_suggestedMin = %s,
            scales_xAxes_ticks_fixedStepSize = %s,
            scales_xAxes_categoryPercentage = %f,
            scales_xAxes_barPercentage = %f,

            /* Time Scale Configuration Options X */
            scales_xAxes_time_format = %s,
            scales_xAxes_time_tooltipFormat = %s,
            scales_xAxes_time_unit_format = %s,
            scales_xAxes_time_unit = %s,
            scales_xAxes_time_unitStepSize = %d,
            scales_xAxes_time_max = %s,
            scales_xAxes_time_min = %s,

			/* Scales Common Configuration Y */
			scales_yAxes_type = %s,
			scales_yAxes_display = %d,
			scales_yAxes_position = %s,
			scales_yAxes_stacked = %d,

			/* Scales Grid Line Configuration Y */
            scales_yAxes_gridLines_display = %d,
            scales_yAxes_gridLines_color = %s,
            scales_yAxes_gridLines_lineWidth = %s,
            scales_yAxes_gridLines_drawBorder = %d,
            scales_yAxes_gridLines_drawOnChartArea = %d,
            scales_yAxes_gridLines_drawTicks = %d,
            scales_yAxes_gridLines_tickMarkLength = %d,
            scales_yAxes_gridLines_zeroLineWidth = %d,
            scales_yAxes_gridLines_zeroLineColor = %s,
            scales_yAxes_gridLines_offsetGridLines = %d,

            /* Scales Title Configuration Y */
            scales_yAxes_scaleLabel_display = %d,
            scales_yAxes_scaleLabel_labelString = %s,
            scales_yAxes_scaleLabel_fontColor = %s,
            scales_yAxes_scaleLabel_fontFamily = %s,
            scales_yAxes_scaleLabel_fontSize = %d,
            scales_yAxes_scaleLabel_fontStyle = %s,

            /* Scales Tick Configuration Y */
            scales_yAxes_ticks_autoskip = %d,
            scales_yAxes_ticks_display = %d,
            scales_yAxes_ticks_fontColor = %s,
            scales_yAxes_ticks_fontFamily = %s,
            scales_yAxes_ticks_fontSize = %d,
            scales_yAxes_ticks_fontStyle = %s,
            scales_yAxes_ticks_maxRotation = %d,
            scales_yAxes_ticks_minRotation = %d,
            scales_yAxes_ticks_mirror = %d,
            scales_yAxes_ticks_padding = %d,
            scales_yAxes_ticks_reverse = %d,
            scales_yAxes_ticks_prefix = %s,
            scales_yAxes_ticks_suffix = %s,
            scales_yAxes_ticks_round = %s,

            /* Scale Configuration Options Y */
            scales_yAxes_ticks_min = %s,
            scales_yAxes_ticks_max = %s,
            scales_yAxes_ticks_beginAtZero = %d,
            scales_yAxes_ticks_maxTicksLimit = %s,
            scales_yAxes_ticks_stepSize = %s,
            scales_yAxes_ticks_suggestedMax = %s,
            scales_yAxes_ticks_suggestedMin = %s,
            scales_yAxes_ticks_fixedStepSize = %s,
            scales_yAxes_categoryPercentage = %f,
            scales_yAxes_barPercentage = %f,

            /* Scales Common Configuration Y2 */
			scales_y2Axes_type = %s,
			scales_y2Axes_display = %d,
			scales_y2Axes_position = %s,

			/* Scales Grid Line Configuration Y2 */
            scales_y2Axes_gridLines_display = %d,
            scales_y2Axes_gridLines_color = %s,
            scales_y2Axes_gridLines_lineWidth = %s,
            scales_y2Axes_gridLines_drawBorder = %d,
            scales_y2Axes_gridLines_drawOnChartArea = %d,
            scales_y2Axes_gridLines_drawTicks = %d,
            scales_y2Axes_gridLines_tickMarkLength = %d,
            scales_y2Axes_gridLines_zeroLineWidth = %d,
            scales_y2Axes_gridLines_zeroLineColor = %s,
            scales_y2Axes_gridLines_offsetGridLines = %d,

            /* Scales Title Configuration Y2 */
            scales_y2Axes_scaleLabel_display = %d,
            scales_y2Axes_scaleLabel_labelString = %s,
            scales_y2Axes_scaleLabel_fontColor = %s,
            scales_y2Axes_scaleLabel_fontFamily = %s,
            scales_y2Axes_scaleLabel_fontSize = %d,
            scales_y2Axes_scaleLabel_fontStyle = %s,

            /* Scales Tick Configuration Y2 */
            scales_y2Axes_ticks_autoskip = %d,
            scales_y2Axes_ticks_display = %d,
            scales_y2Axes_ticks_fontColor = %s,
            scales_y2Axes_ticks_fontFamily = %s,
            scales_y2Axes_ticks_fontSize = %d,
            scales_y2Axes_ticks_fontStyle = %s,
            scales_y2Axes_ticks_maxRotation = %d,
            scales_y2Axes_ticks_minRotation = %d,
            scales_y2Axes_ticks_mirror = %d,
            scales_y2Axes_ticks_padding = %d,
            scales_y2Axes_ticks_reverse = %d,
            scales_y2Axes_ticks_prefix = %s,
            scales_y2Axes_ticks_suffix = %s,
            scales_y2Axes_ticks_round = %s,

            /* Scale Configuration Options Y2 */
            scales_y2Axes_ticks_min = %s,
            scales_y2Axes_ticks_max = %s,
            scales_y2Axes_ticks_beginAtZero = %d,
            scales_y2Axes_ticks_maxTicksLimit = %s,
            scales_y2Axes_ticks_stepSize = %s,
            scales_y2Axes_ticks_suggestedMax = %s,
            scales_y2Axes_ticks_suggestedMin = %s,
            scales_y2Axes_ticks_fixedStepSize = %s,

            /* RL Scale Common Configuration */
			scales_rl_display = %d,

			/* RL Scale Grid Line Configuration */
			scales_rl_gridLines_display = %d,
			scales_rl_gridLines_color = %s,
			scales_rl_gridLines_lineWidth = %s,

			/* RL Scale Angle Line Configuration */
			scales_rl_angleLines_display = %d,
			scales_rl_angleLines_color = %s,
			scales_rl_angleLines_lineWidth = %s,

			/* RL Scale Point Label Configuration */
			scales_rl_pointLabels_fontSize = %d,
			scales_rl_pointLabels_fontColor = %s,
			scales_rl_pointLabels_fontFamily = %s,
			scales_rl_pointLabels_fontStyle = %s,

			/* RL Scale Tick Configuration */
			scales_rl_ticks_display = %d,
			scales_rl_ticks_autoskip = %d,
			scales_rl_ticks_reverse = %d,
			scales_rl_ticks_prefix = %s,
			scales_rl_ticks_suffix = %s,
			scales_rl_ticks_round = %s,
			scales_rl_ticks_fontSize = %d,
			scales_rl_ticks_fontColor = %s,
			scales_rl_ticks_fontFamily = %s,
			scales_rl_ticks_fontStyle = %s,

			/* RL Scale Configuration Options */
			scales_rl_ticks_min = %s,
			scales_rl_ticks_max = %s,
			scales_rl_ticks_suggestedMin = %s,
			scales_rl_ticks_suggestedMax = %s,
			scales_rl_ticks_stepSize = %s,
			scales_rl_ticks_fixedStepSize = %s,
			scales_rl_ticks_maxTicksLimit = %s,
			scales_rl_ticks_beginAtZero = %d,
			scales_rl_ticks_showLabelBackdrop = %d,
			scales_rl_ticks_backdropColor = %s,
			scales_rl_ticks_backdropPaddingX = %d,
			scales_rl_ticks_backdropPaddingY = %d,

              temporary = 0
              WHERE id = %d",
			$name,
			$description,
			$type,
			$rows,
			$columns,
			$labels,
			$is_model,

			//Common Chart Configuration
			$canvas_transparent_background,
			$canvas_backgroundColor,
			$margin_top,
			$margin_bottom,
			$width,
			$height,
			$responsive,
			$responsiveAnimationDuration,
			$maintainAspectRatio,
            $fixed_height,

			//Title Configuration
			$title_display,
			$title_position,
			$title_fullWidth,
			$title_fontSize,
			$title_fontFamily,
			$title_fontColor,
			$title_fontStyle,
			$title_padding,

			//Legend Configuration
			$legend_display,
			$legend_position,
			$legend_fullWidth,
			$legend_toggle_dataset,

			//Legend Label configuration
			$legend_labels_boxWidth,
			$legend_labels_fontSize,
			$legend_labels_fontStyle,
			$legend_labels_fontColor,
			$legend_labels_fontFamily,
			$legend_labels_padding,

			//Tooltip Configuration
			$tooltips_enabled,
			$tooltips_mode,
			$tooltips_backgroundColor,
			$tooltips_titleFontFamily,
			$tooltips_titleFontSize,
			$tooltips_titleFontStyle,
			$tooltips_titleFontColor,
			$tooltips_titleMarginBottom,
			$tooltips_bodyFontFamily,
			$tooltips_bodyFontSize,
			$tooltips_bodyFontStyle,
			$tooltips_bodyFontColor,
			$tooltips_footerFontFamily,
			$tooltips_footerFontSize,
			$tooltips_footerFontStyle,
			$tooltips_footerFontColor,
			$tooltips_footerMarginTop,
			$tooltips_xPadding,
			$tooltips_yPadding,
			$tooltips_caretSize,
			$tooltips_cornerRadius,
			$tooltips_multiKeyBackground,
			$tooltips_beforeTitle,
            $tooltips_afterTitle,
            $tooltips_beforeBody,
            $tooltips_afterBody,
            $tooltips_beforeLabel,
            $tooltips_afterLabel,
            $tooltips_beforeFooter,
            $tooltips_footer,
            $tooltips_afterFooter,

			//Hover Configuration
			$hover_animationDuration,

			//Animation Configuration
			$animation_duration,
			$animation_easing,
			$animation_animateRotate,
			$animation_animateScale,

			//Misc Configuration
			$elements_rectangle_borderSkipped,

			//Scales Common Configuration X
			$scales_xAxes_type,
			$scales_xAxes_display,
			$scales_xAxes_position,
			$scales_xAxes_stacked,

			//Scales Grid Line Configuration X
			$scales_xAxes_gridLines_display,
			$scales_xAxes_gridLines_color,
			$scales_xAxes_gridLines_lineWidth,
			$scales_xAxes_gridLines_drawBorder,
			$scales_xAxes_gridLines_drawOnChartArea,
			$scales_xAxes_gridLines_drawTicks,
			$scales_xAxes_gridLines_tickMarkLength,
			$scales_xAxes_gridLines_zeroLineWidth,
			$scales_xAxes_gridLines_zeroLineColor,
			$scales_xAxes_gridLines_offsetGridLines,

			//Scales Title Configuration X
			$scales_xAxes_scaleLabel_display,
			$scales_xAxes_scaleLabel_labelString,
			$scales_xAxes_scaleLabel_fontColor,
			$scales_xAxes_scaleLabel_fontFamily,
			$scales_xAxes_scaleLabel_fontSize,
			$scales_xAxes_scaleLabel_fontStyle,

			//Scales Tick Configuration X
			$scales_xAxes_ticks_autoskip,
			$scales_xAxes_ticks_display,
			$scales_xAxes_ticks_fontColor,
			$scales_xAxes_ticks_fontFamily,
			$scales_xAxes_ticks_fontSize,
			$scales_xAxes_ticks_fontStyle,
			$scales_xAxes_ticks_labelOffset,
			$scales_xAxes_ticks_maxRotation,
			$scales_xAxes_ticks_minRotation,
			$scales_xAxes_ticks_reverse,
			$scales_xAxes_ticks_prefix,
			$scales_xAxes_ticks_suffix,
			$scales_xAxes_ticks_round,

			//Scale Configuration Options X
			$scales_xAxes_ticks_min,
			$scales_xAxes_ticks_max,
			$scales_xAxes_ticks_beginAtZero,
			$scales_xAxes_ticks_maxTicksLimit,
			$scales_xAxes_ticks_stepSize,
			$scales_xAxes_ticks_suggestedMax,
			$scales_xAxes_ticks_suggestedMin,
			$scales_xAxes_ticks_fixedStepSize,
			$scales_xAxes_categoryPercentage,
			$scales_xAxes_barPercentage,

			//Time Scale Configuration Options X
			$scales_xAxes_time_format,
			$scales_xAxes_time_tooltipFormat,
			$scales_xAxes_time_unit_format,
			$scales_xAxes_time_unit,
			$scales_xAxes_time_unitStepSize,
			$scales_xAxes_time_max,
			$scales_xAxes_time_min,

			//Scales Common Configuration Y
			$scales_yAxes_type,
			$scales_yAxes_display,
			$scales_yAxes_position,
			$scales_yAxes_stacked,

			//Scales Grid Line Configuration Y
			$scales_yAxes_gridLines_display,
			$scales_yAxes_gridLines_color,
			$scales_yAxes_gridLines_lineWidth,
			$scales_yAxes_gridLines_drawBorder,
			$scales_yAxes_gridLines_drawOnChartArea,
			$scales_yAxes_gridLines_drawTicks,
			$scales_yAxes_gridLines_tickMarkLength,
			$scales_yAxes_gridLines_zeroLineWidth,
			$scales_yAxes_gridLines_zeroLineColor,
			$scales_yAxes_gridLines_offsetGridLines,

			//Scales Title Configuration Y
			$scales_yAxes_scaleLabel_display,
			$scales_yAxes_scaleLabel_labelString,
			$scales_yAxes_scaleLabel_fontColor,
			$scales_yAxes_scaleLabel_fontFamily,
			$scales_yAxes_scaleLabel_fontSize,
			$scales_yAxes_scaleLabel_fontStyle,

			//Scales Tick Configuration Y
			$scales_yAxes_ticks_autoskip,
			$scales_yAxes_ticks_display,
			$scales_yAxes_ticks_fontColor,
			$scales_yAxes_ticks_fontFamily,
			$scales_yAxes_ticks_fontSize,
			$scales_yAxes_ticks_fontStyle,
			$scales_yAxes_ticks_maxRotation,
			$scales_yAxes_ticks_minRotation,
			$scales_yAxes_ticks_mirror,
			$scales_yAxes_ticks_padding,
			$scales_yAxes_ticks_reverse,
			$scales_yAxes_ticks_prefix,
			$scales_yAxes_ticks_suffix,
			$scales_yAxes_ticks_round,

			//Scale Configuration Options Y
			$scales_yAxes_ticks_min,
			$scales_yAxes_ticks_max,
			$scales_yAxes_ticks_beginAtZero,
			$scales_yAxes_ticks_maxTicksLimit,
			$scales_yAxes_ticks_stepSize,
			$scales_yAxes_ticks_suggestedMax,
			$scales_yAxes_ticks_suggestedMin,
			$scales_yAxes_ticks_fixedStepSize,
			$scales_yAxes_categoryPercentage,
			$scales_yAxes_barPercentage,

			//Scales Common Configuration Y2
			$scales_y2Axes_type,
			$scales_y2Axes_display,
			$scales_y2Axes_position,

			//Scales Grid Line Configuration Y2
			$scales_y2Axes_gridLines_display,
			$scales_y2Axes_gridLines_color,
			$scales_y2Axes_gridLines_lineWidth,
			$scales_y2Axes_gridLines_drawBorder,
			$scales_y2Axes_gridLines_drawOnChartArea,
			$scales_y2Axes_gridLines_drawTicks,
			$scales_y2Axes_gridLines_tickMarkLength,
			$scales_y2Axes_gridLines_zeroLineWidth,
			$scales_y2Axes_gridLines_zeroLineColor,
			$scales_y2Axes_gridLines_offsetGridLines,

			//Scales Title Configuration Y2
			$scales_y2Axes_scaleLabel_display,
			$scales_y2Axes_scaleLabel_labelString,
			$scales_y2Axes_scaleLabel_fontColor,
			$scales_y2Axes_scaleLabel_fontFamily,
			$scales_y2Axes_scaleLabel_fontSize,
			$scales_y2Axes_scaleLabel_fontStyle,

			//Scales Tick Configuration Y2
			$scales_y2Axes_ticks_autoskip,
			$scales_y2Axes_ticks_display,
			$scales_y2Axes_ticks_fontColor,
			$scales_y2Axes_ticks_fontFamily,
			$scales_y2Axes_ticks_fontSize,
			$scales_y2Axes_ticks_fontStyle,
			$scales_y2Axes_ticks_maxRotation,
			$scales_y2Axes_ticks_minRotation,
			$scales_y2Axes_ticks_mirror,
			$scales_y2Axes_ticks_padding,
			$scales_y2Axes_ticks_reverse,
			$scales_y2Axes_ticks_prefix,
			$scales_y2Axes_ticks_suffix,
			$scales_y2Axes_ticks_round,

			//Scale Configuration Options Y2
			$scales_y2Axes_ticks_min,
			$scales_y2Axes_ticks_max,
			$scales_y2Axes_ticks_beginAtZero,
			$scales_y2Axes_ticks_maxTicksLimit,
			$scales_y2Axes_ticks_stepSize,
			$scales_y2Axes_ticks_suggestedMax,
			$scales_y2Axes_ticks_suggestedMin,
			$scales_y2Axes_ticks_fixedStepSize,

			/* RL Scale Common Configuration */
			$scales_rl_display,

			/* RL Scale Grid Line Configuration */
			$scales_rl_gridLines_display,
			$scales_rl_gridLines_color,
			$scales_rl_gridLines_lineWidth,

			/* RL Scale Angle Line Configuration */
			$scales_rl_angleLines_display,
			$scales_rl_angleLines_color,
			$scales_rl_angleLines_lineWidth,

			/* RL Scale Point Label Configuration */
			$scales_rl_pointLabels_fontSize,
			$scales_rl_pointLabels_fontColor,
			$scales_rl_pointLabels_fontFamily,
			$scales_rl_pointLabels_fontStyle,

			/* RL Scale Tick Configuration */
			$scales_rl_ticks_display,
			$scales_rl_ticks_autoskip,
			$scales_rl_ticks_reverse,
			$scales_rl_ticks_prefix,
			$scales_rl_ticks_suffix,
			$scales_rl_ticks_round,
			$scales_rl_ticks_fontSize,
			$scales_rl_ticks_fontColor,
			$scales_rl_ticks_fontFamily,
			$scales_rl_ticks_fontStyle,

			/* RL Scale Configuration Options */
			$scales_rl_ticks_min,
			$scales_rl_ticks_max ,
			$scales_rl_ticks_suggestedMin,
			$scales_rl_ticks_suggestedMax,
			$scales_rl_ticks_stepSize,
			$scales_rl_ticks_fixedStepSize,
			$scales_rl_ticks_maxTicksLimit,
			$scales_rl_ticks_beginAtZero,
			$scales_rl_ticks_showLabelBackdrop,
			$scales_rl_ticks_backdropColor,
			$scales_rl_ticks_backdropPaddingX,
			$scales_rl_ticks_backdropPaddingY,

			$chart_id);

		$result     = $wpdb->query( $safe_sql );

		//save the chart data in the 'data' db table
		$chart_data   = json_decode( stripslashes( $_POST['chart_data'] ) );
		$chart_data_a = $chart_data->data;

		foreach ( $chart_data_a as $row_index => $row_data ) {

			//skip the first row, because it stores the labels
			if($row_index === 0){continue;}

			$row_data_json = json_encode( $row_data );

			//save in the db table
			global $wpdb;
			$table_name = $wpdb->prefix . $this->shared->get( 'slug' ) . "_data";
			$safe_sql   = $wpdb->prepare( "UPDATE $table_name SET
                content = %s
                WHERE chart_id = %d AND row_index = %d",
				$row_data_json,
				$chart_id,
				$row_index
			);

			$query_result = $wpdb->query( $safe_sql );

		}

		//send output
		echo 'success';
		die();

	}


	/*
	 * Ajax handler used to add and remove rows
	 *
	 * This method is called when the value of the "Rows" field changes
	 */
	public function add_remove_rows() {

		//check the referer
		if ( ! check_ajax_referer( 'dauc', 'security', false ) ) {
			echo "Invalid AJAX Request";
			die();
		}

		//check the capability
		if ( !current_user_can(get_option( $this->shared->get('slug') . "_charts_menu_capability")) )  {
			echo 'Invalid Capability';
			die();
		}

        //Set the custom "Max Execution Time" defined in the options
        ini_set('max_execution_time', intval(get_option($this->shared->get('slug') . "_max_execution_time"), 10));

        //Set the custom "Memory Limit" (in megabytes) defined in the options
        ini_set('memory_limit', intval(get_option($this->shared->get('slug') . "_memory_limit"), 10) . 'M');

		//prepare values
		$chart_id = intval($_POST['chart_id'], 10);
		$current_number_of_rows = intval($_POST['current_number_of_rows'], 10);
		$new_number_of_rows = intval($_POST['new_number_of_rows'], 10);
		$current_number_of_columns = intval($_POST['current_number_of_columns'], 10);

        //update the "data" db table -----------------------------------------------------------------------------------
		if($new_number_of_rows > $current_number_of_rows){

            /*
             * ---------------------------------------------------------------------------------------------------------
             *
             * Generate insert multirows query with placeholders:
             *
             *   INSERT INTO tbl_name
             *      (col1,col2,col3)
             *   VALUES
             *      (1,2,3),
             *      (4,5,6),
             *      (7,8,9);
             */

            //Create the first part of the insert multirows query
            $values = array();
            $place_holders = array();
            global $wpdb;
            $table_name = $wpdb->prefix . $this->shared->get('slug') . "_data";
            $query = "INSERT INTO $table_name (
                chart_id,
                row_index,
                content,
                label,
                fill,
                lineTension,
                backgroundColor,
                borderWidth,
                borderColor,
                borderCapStyle,
                borderDash,
                borderDashOffset,
                borderJoinStyle,
                pointBorderColor,
                pointBackgroundColor,
                pointBorderWidth,
                pointRadius,
                pointHoverRadius,
                pointHitRadius,
                pointHoverBackgroundColor,
                pointHoverBorderColor,
                pointHoverBorderWidth,
                pointStyle,
                showLine,
                spanGaps,
                hoverBackgroundColor,
                hoverBorderColor,
                hoverBorderWidth,
                hitRadius,
                hoverRadius,
                plotY2
                ) VALUES ";

            //add the rows ---------------------------------------------------------------------------------------------
            $row_difference = $new_number_of_rows - $current_number_of_rows;
            for ($i = 1; $i <= $row_difference; $i++) {

                $row_index = $current_number_of_rows + $i;
                $row_data = array_fill(0, $current_number_of_columns, 0);
                $row_data_json = json_encode($row_data);

                //prepare the values and the placeholders of the insert multirows query
                array_push(
                    $values,
                    $chart_id,
                    $row_index,
                    $row_data_json,
                    'Label ' . $row_index,
                    false,
                    0,
                    'rgba(0,0,0,0.1)',
                    3,
                    'rgba(0,0,0,0.1)',
                    'butt',
                    0,
                    0.0,
                    'miter',
                    'rgba(0,0,0,0.1)',
                    'rgba(0,0,0,0.1)',
                    1,
                    1,
                    1,
                    5,
                    'rgba(0,0,0,0.1)',
                    'rgba(0,0,0,0.1)',
                    2,
                    'circle',
                    true,
                    false,
                    'rgba(0,0,0,0.1)',
                    'rgba(0,0,0,0.1)',
                    1,
                    1,
                    1,
                    0
                );
                $place_holders[] = "(
                '%d',
                '%d',
                '%s',
                '%s',
                '%d',
                '%f',
                '%s',
                '%d',
                '%s',
                '%s',
                '%s',
                '%f',
                '%s',
                '%s',
                '%s',
                '%d',
                '%d',
                '%d',
                '%d',
                '%s',
                '%s',
                '%d',
                '%s',
                '%d',
                '%d',
                '%s',
                '%s',
                '%d',
                '%d',
                '%d',
                '%d'
                )";

            }

            //execute insert multirows query
            $query .= implode(', ', $place_holders);
            $safe_sql = $wpdb->prepare("$query ", $values);
            $result = $wpdb->query($safe_sql);

		}elseif($new_number_of_rows < $current_number_of_rows){

            /*
             * ---------------------------------------------------------------------------------------------------------
             *
             * Generate delete multirows query with placeholders:
             *
             * DELETE FROM table WHERE (col1,col2) IN ((1,2),(3,4),(5,6))
             */

            //create the first part of the delete multirows query
            global $wpdb;
            $table_name = $wpdb->prefix . $this->shared->get('slug') . "_data";
            $query = "DELETE FROM $table_name WHERE (chart_id, row_index) IN ";
            $values = array();
            $place_holders = array();

            //add the values of the delete multirows query
            $row_difference = $current_number_of_rows - $new_number_of_rows;
            for ($i = 1; $i <= $row_difference; $i++) {
                array_push($values, $chart_id, $new_number_of_rows + $i);
                $place_holders[] = "('%d', '%d')";
            }

            //execute the delete multirows query
            $query .= '(' . implode(',', $place_holders) . ')';
            $safe_sql = $wpdb->prepare($query, $values);
            $result = $wpdb->query($safe_sql);

		}

        //update the number of rows in the "chart" db table ------------------------------------------------------------
        global $wpdb;
        $table_name = $wpdb->prefix . $this->shared->get('slug') . "_chart";
        $safe_sql = $wpdb->prepare("UPDATE $table_name SET rows = %d WHERE id = %d ", $new_number_of_rows, $chart_id);
        $result = $wpdb->query($safe_sql);

		//send output
		echo 'success';
		die();

	}

	/*
	 * Ajax handler used to add and remove columns
	 *
	 * This method is called when the value of the "Columns" field changes
	 */
	public function add_remove_columns() {

		//check the referer
		if ( ! check_ajax_referer( 'dauc', 'security', false ) ) {
			echo "Invalid AJAX Request";
			die();
		}

		//check the capability
		if ( !current_user_can(get_option( $this->shared->get('slug') . "_charts_menu_capability")) )  {
			echo 'Invalid Capability';
			die();
		}

        //Set the custom "Max Execution Time" defined in the options
        ini_set('max_execution_time', intval(get_option($this->shared->get('slug') . "_max_execution_time"), 10));

        //Set the custom "Memory Limit" (in megabytes) defined in the options
        ini_set('memory_limit', intval(get_option($this->shared->get('slug') . "_memory_limit"), 10) . 'M');

		//prepare values
		$chart_id = intval($_POST['chart_id'], 10);
		$new_number_of_columns = intval($_POST['new_number_of_columns'], 10);

        //update the "data" db table -----------------------------------------------------------------------------------

		global $wpdb;
		$table_name = $wpdb->prefix . $this->shared->get( 'slug' ) . "_data";
		$safe_sql   = $wpdb->prepare( "SELECT * FROM $table_name WHERE chart_id = %d", $chart_id );
		$results = $wpdb->get_results($safe_sql);

		//parse through all the data with a foreach
		foreach($results as $key => $result){

			$content_a = json_decode($result->content);

			if($new_number_of_columns > count($content_a)){

				$difference = $new_number_of_columns - count($content_a);
				for($i=1;$i<=$difference;$i++){
					array_push($content_a, 0);
				}

			}elseif($new_number_of_columns < count($content_a)){

				$difference = count($content_a) - $new_number_of_columns;
				for($i=1;$i<=$difference;$i++){
					array_pop($content_a);
				}

			}

			$content = json_encode($content_a);
			$id = $result->id;
			$table_name = $wpdb->prefix . $this->shared->get( 'slug' ) . "_data";
			$safe_sql   = $wpdb->prepare( "UPDATE $table_name SET content = %s WHERE id = %d ", $content, $id );
			$result = $wpdb->query($safe_sql);

		}

        //update the number of columns in the "chart" db table ---------------------------------------------------------
        global $wpdb;
        $table_name = $wpdb->prefix . $this->shared->get('slug') . "_chart";
        $safe_sql = $wpdb->prepare("UPDATE $table_name SET columns = %d WHERE id = %d ", $new_number_of_columns, $chart_id);
        $result = $wpdb->query($safe_sql);

		//send output
		echo 'success';
		die();

	}

	/*
	* Ajax handler used to retrieve the data structure of a single row in the JSON format
	*/
	public function retrieve_row_data() {

		//check the referer
		if ( ! check_ajax_referer( 'dauc', 'security', false ) ) {
			echo "Invalid AJAX Request";
			die();
		}

		//check the capability
		if ( !current_user_can(get_option( $this->shared->get('slug') . "_charts_menu_capability")) )  {
			echo 'Invalid Capability';
			die();
		}

		//prepare data
		$chart_id = intval($_POST['chart_id'], 10);
		$row = intval($_POST['row'], 10);

		global $wpdb;
		$table_name = $wpdb->prefix . $this->shared->get('slug') . "_data";
		$safe_sql = $wpdb->prepare("SELECT * FROM $table_name WHERE chart_id = %d AND row_index = %d ", $chart_id, $row);
		$row_obj = $wpdb->get_row( $safe_sql );

		echo json_encode($this->shared->object_stripslashes($row_obj));
		die();

	}

	/*
	* Ajax handler used to update the data structure of a single row
	*/
	public function update_data_structure() {

		//check the referer
		if ( ! check_ajax_referer( 'dauc', 'security', false ) ) {
			echo "Invalid AJAX Request";
			die();
		}

		//check the capability
		if ( !current_user_can(get_option( $this->shared->get('slug') . "_charts_menu_capability")) )  {
			echo 'Invalid Capability';
			die();
		}

		extract($_POST);

		//validation ---------------------------------------------------------------------------------------------------

		//init variables
		$fields_with_errors_a = array();

		//define patterns

		//match an integer or a float value
		$patt_integer_or_float = '/^(\d+\.\d+)|\d+$/';

        //match a hex rgb color, a rgba color or a comma separated list of hex rgb colors and rgba colors
		$patt_color_or_colors = '/^((\#([0-9a-fA-F]{3}){1,2})|(rgba\(\d{1,3},\d{1,3},\d{1,3},(\d{1}|\d{1}\.\d{1,2})\)))(,(\#([0-9a-fA-F]{3}){1,2}|rgba\(\d{1,3},\d{1,3},\d{1,3},(\d{1}|\d{1}\.\d{1,2})\)))*$/';

		//match an integer or a comma separated list of integers
		$patt_integer_or_integers = '/^(\d+|(\d+(,\d+)*))$/';

        //match an integer
		$patt_integer = '/^\d+$/';

        //match a pointStyle or a comma separated list of pointStyles
		$patt_pointStyle_or_pointStyles = '/^((circle|triangle|rect|rectRot|cross|crossRot|star|line|dash)|(circle|triangle|rect|rectRot|cross|crossRot|star|line|dash)(,(circle|triangle|rect|rectRot|cross|crossRot|star|line|dash))*)$/';

		if(!preg_match($patt_integer_or_float, $lineTension)){$fields_with_errors_a[] = 'lineTension';}
		if(!preg_match($patt_color_or_colors, $backgroundColor)){$fields_with_errors_a[] = 'backgroundColor';}
		if(!preg_match($patt_integer_or_integers, $borderWidth)){$fields_with_errors_a[] = 'borderWidth';}
		if(!preg_match($patt_color_or_colors, $borderColor)){$fields_with_errors_a[] = 'borderColor';}
		if(!preg_match($patt_integer_or_integers, $borderDash)){$fields_with_errors_a[] = 'borderDash';}
		if(!preg_match($patt_integer_or_float, $borderDashOffset)){$fields_with_errors_a[] = 'borderDashOffset';}
		if(!preg_match($patt_color_or_colors, $pointBorderColor)){$fields_with_errors_a[] = 'pointBorderColor';}
		if(!preg_match($patt_color_or_colors, $pointBackgroundColor)){$fields_with_errors_a[] = 'pointBackgroundColor';}
		if(!preg_match($patt_integer_or_integers, $pointBorderWidth)){$fields_with_errors_a[] = 'pointBorderWidth';}
		if(!preg_match($patt_integer_or_integers, $pointRadius)){$fields_with_errors_a[] = 'pointRadius';}
		if(!preg_match($patt_integer_or_integers, $pointHoverRadius)){$fields_with_errors_a[] = 'pointHoverRadius';}
		if(!preg_match($patt_integer_or_integers, $pointHitRadius)){$fields_with_errors_a[] = 'pointHitRadius';}
		if(!preg_match($patt_color_or_colors, $pointHoverBackgroundColor)){$fields_with_errors_a[] = 'pointHoverBackgroundColor';}
		if(!preg_match($patt_color_or_colors, $pointHoverBorderColor)){$fields_with_errors_a[] = 'pointHoverBorderColor';}
		if(!preg_match($patt_integer_or_integers, $pointHoverBorderWidth)){$fields_with_errors_a[] = 'pointHoverBorderWidth';}
		if(!preg_match($patt_pointStyle_or_pointStyles, $pointStyle)){$fields_with_errors_a[] = 'pointStyle';}
		if(!preg_match($patt_color_or_colors, $hoverBackgroundColor)){$fields_with_errors_a[] = 'hoverBackgroundColor';}
		if(!preg_match($patt_color_or_colors, $hoverBorderColor)){$fields_with_errors_a[] = 'hoverBorderColor';}
		if(!preg_match($patt_integer_or_integers, $hoverBorderWidth)){$fields_with_errors_a[] = 'hoverBorderWidth';}
		if(!preg_match($patt_integer_or_integers, $hitRadius)){$fields_with_errors_a[] = 'hitRadius';}
		if(!preg_match($patt_integer_or_integers, $hoverRadius)){$fields_with_errors_a[] = 'hoverRadius';}

		if(count($fields_with_errors_a) > 0){
			echo 'Failed validation on the following fields: ' . implode(', ', $fields_with_errors_a);
			die();
		}

		global $wpdb;
		$table_name = $wpdb->prefix . $this->shared->get('slug') . "_data";
		$safe_sql   = $wpdb->prepare( "UPDATE $table_name SET
			label = %s,
			fill = %d,
			lineTension = %f,
			backgroundColor = %s,
			borderWidth = %s,
			borderColor = %s,
			borderCapStyle = %s,
			borderDash = %s,
			borderDashOffset = %f,
			borderJoinStyle = %s,
			pointBorderColor = %s,
			pointBackgroundColor = %s,
			pointBorderWidth = %s,
			pointRadius = %s,
			pointHoverRadius = %s,
			pointHitRadius = %s,
			pointHoverBackgroundColor = %s,
			pointHoverBorderColor = %s,
			pointHoverborderWidth = %s,
			pointStyle = %s,
			showLine = %d,
			spanGaps = %d,
			hoverBackgroundColor = %s,
			hoverBorderColor = %s,
			hoverBorderWidth = %s,
			hitRadius = %d,
			hoverRadius = %s,
			plotY2 = %d
			WHERE chart_id = %d AND row_index = %d",
			$label,
			$fill,
			$lineTension,
			$backgroundColor,
			$borderWidth,
			$borderColor,
			$borderCapStyle,
			$borderDash,
			$borderDashOffset,
			$borderJoinStyle,
			$pointBorderColor,
			$pointBackgroundColor,
			$pointBorderWidth,
			$pointRadius,
			$pointHoverRadius,
			$pointHitRadius,
			$pointHoverBackgroundColor,
			$pointHoverBorderColor,
			$pointHoverBorderWidth,
			$pointStyle,
			$showLine,
			$spanGaps,
			$hoverBackgroundColor,
			$hoverBorderColor,
			$hoverBorderWidth,
			$hitRadius,
			$hoverRadius,
			$plotY2,
			$chart_id,
			$row_index);
		$result = $wpdb->query( $safe_sql );

		if($result === false){
			echo 'unable to save the data structure';
			die();
		}

		/*
		 * Globalize the data structure of this row if the "Update and Globalize" button has been clicked
		 *
		 * All the data structure fields except 'label' will be saved on all the rows of the
		 * chart
		 */
		if($globalize == 'true'){

			global $wpdb;
			$table_name = $wpdb->prefix . $this->shared->get('slug') . "_data";
			$safe_sql   = $wpdb->prepare( "UPDATE $table_name SET
				fill = %d,
				lineTension = %f,
				backgroundColor = %s,
				borderWidth = %s,
				borderColor = %s,
				borderCapStyle = %s,
				borderDash = %s,
				borderDashOffset = %f,
				borderJoinStyle = %s,
				pointBorderColor = %s,
				pointBackgroundColor = %s,
				pointBorderWidth = %s,
				pointRadius = %s,
				pointHoverRadius = %s,
				pointHitRadius = %s,
				pointHoverBackgroundColor = %s,
				pointHoverBorderColor = %s,
				pointHoverborderWidth = %s,
				pointStyle = %s,
				showLine = %d,
				spanGaps = %d,
				hoverBackgroundColor = %s,
				hoverBorderColor = %s,
				hoverBorderWidth = %s,
				hitRadius = %d,
				hoverRadius = %s,
				plotY2 = %d
				WHERE chart_id = %d",
				$fill,
				$lineTension,
				$backgroundColor,
				$borderWidth,
				$borderColor,
				$borderCapStyle,
				$borderDash,
				$borderDashOffset,
				$borderJoinStyle,
				$pointBorderColor,
				$pointBackgroundColor,
				$pointBorderWidth,
				$pointRadius,
				$pointHoverRadius,
				$pointHitRadius,
				$pointHoverBackgroundColor,
				$pointHoverBorderColor,
				$pointHoverBorderWidth,
				$pointStyle,
				$showLine,
				$spanGaps,
				$hoverBackgroundColor,
				$hoverBorderColor,
				$hoverBorderWidth,
				$hitRadius,
				$hoverRadius,
				$plotY2,
				$chart_id,
				$row_index);
			$result = $wpdb->query( $safe_sql );

			if($result === false){
				echo 'Unable to globalize the data structure';
				die();
			}

		}

		echo 'success';
		die();

	}

	/*
	* Ajax handler used to return the data of a specified chart in the json format
	 *
	 * The returned data in the JSON format will be used to initialize the handsontable table
	*/
	public function retrieve_chart_data() {

		//check the referer
		if ( ! check_ajax_referer( 'dauc', 'security', false ) ) {
			echo "Invalid AJAX Request";
			die();
		}

		//check the capability
		if ( !current_user_can(get_option( $this->shared->get('slug') . "_charts_menu_capability")) )  {
			echo 'Invalid Capability';
			die();
		}

        //Set the custom "Max Execution Time" defined in the options
        ini_set('max_execution_time', intval(get_option($this->shared->get('slug') . "_max_execution_time"), 10));

        //Set the custom "Memory Limit" (in megabytes) defined in the options
        ini_set('memory_limit', intval(get_option($this->shared->get('slug') . "_memory_limit"), 10) . 'M');

		//get data
		$chart_id = intval($_POST['chart_id'], 10);

		//retrieve the labels
		global $wpdb;
		$table_name = $wpdb->prefix . $this->shared->get('slug') . "_chart";
		$safe_sql = $wpdb->prepare("SELECT * FROM $table_name WHERE id = %d ", $chart_id);
		$chart = $wpdb->get_row($safe_sql);
		$labels[0] = json_decode($chart->labels);

		//retrieve the data
		global $wpdb;
		$table_name = $wpdb->prefix . $this->shared->get('slug') . "_data";
		$safe_sql = $wpdb->prepare("SELECT * FROM $table_name WHERE chart_id = %d ", $chart_id);
		$data_a = $wpdb->get_results($safe_sql);

		//load the data in an array
		foreach($data_a as $key => $data){
			$data_content[] = json_decode($data->content);
		}

		//merge the array with the labels with the array with the data
		$merge = array_merge($labels, $data_content);

		//encode the resulting merged array to json
		$data_json = json_encode( $merge );

		echo $data_json;
		die();

	}

	/*
	* All the data of the chart and of the data structure of the loaded chart will be assigned to the chart that is
	 * loading the model
	 *
	 * The data of the handsontable table, the data of all the fields available in the main form and all the data of the
	 * data structure will be returned. The values of the handsontable table, the fields of the main form and the fields
	 * of the data structure will be modified by javascript using the data received with this ajax request. ( returned
	 * by this method )
	*/
	public function load_model() {

		//check the referer
		if ( ! check_ajax_referer( 'dauc', 'security', false ) ) {
			echo "Invalid AJAX Request";
			die();
		}

		//check the capability
		if ( !current_user_can(get_option( $this->shared->get('slug') . "_charts_menu_capability")) )  {
			echo 'Invalid Capability';
			die();
		}

        //Set the custom "Max Execution Time" defined in the options
        ini_set('max_execution_time', intval(get_option($this->shared->get('slug') . "_max_execution_time"), 10));

        //Set the custom "Memory Limit" (in megabytes) defined in the options
        ini_set('memory_limit', intval(get_option($this->shared->get('slug') . "_memory_limit"), 10) . 'M');

		//get data
		$chart_id = intval($_POST['chart_id'], 10);
		$model_id = intval($_POST['model_id'], 10);

		/*
		 * Get the data of the model (identified with $model_id) from the 'chart' table and assign these data to the
		 * considered chart ($chart_id)
		 */
		global $wpdb; $table_name = $wpdb->prefix . $this->shared->get('slug') . "_chart";
		$safe_sql = $wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $model_id);
		$model_obj = $wpdb->get_row($safe_sql);

		//copy the data of the model in the considered chart
		global $wpdb;
		$table_name = $wpdb->prefix . $this->shared->get('slug') . "_chart";
		$safe_sql = $wpdb->prepare("UPDATE $table_name SET
				name = %s,
				description = %s,
				type = %s,
				rows = %d,
				columns = %d,
				labels = %s,
				is_model = %d,

				/* Common Chart Configuration */
				canvas_transparent_background = %d,
				canvas_backgroundColor = %s,
				margin_top = %d,
				margin_bottom = %d,
				width = %d,
				height = %d,
				responsive = %d,
				responsiveAnimationDuration = %d,
				maintainAspectRatio = %d,
				fixed_height = %d,

				/* Title Configuration */
				title_display = %d,
				title_position = %s,
				title_fullWidth = %d,
				title_fontSize = %d,
				title_fontFamily = %s,
				title_fontColor = %s,
				title_fontStyle = %s,
				title_padding = %d,

				/* Legend Configuration */
				legend_display = %d,
				legend_position = %s,
				legend_fullWidth = %d,
				legend_toggle_dataset = %d,

				/* Legend Label Configuration */
				legend_labels_boxWidth = %d,
				legend_labels_fontSize = %d,
				legend_labels_fontStyle = %s,
				legend_labels_fontColor = %s,
				legend_labels_fontFamily = %s,
				legend_labels_padding = %d,

				/* Tooltip Configuration */
				tooltips_enabled = %d,
				tooltips_mode = %s,
				tooltips_backgroundColor = %s,
				tooltips_titleFontFamily = %s,
				tooltips_titleFontSize = %d,
				tooltips_titleFontStyle = %s,
				tooltips_titleFontColor = %s,
				tooltips_titleMarginBottom = %d,
				tooltips_bodyFontFamily = %s,
				tooltips_bodyFontSize = %d,
				tooltips_bodyFontStyle = %s,
				tooltips_bodyFontColor = %s,
				tooltips_footerFontFamily = %s,
				tooltips_footerFontSize = %d,
				tooltips_footerFontStyle = %s,
				tooltips_footerFontColor = %s,
				tooltips_footerMarginTop = %d,
				tooltips_xPadding = %d,
				tooltips_yPadding = %d,
				tooltips_caretSize = %d,
				tooltips_cornerRadius = %d,
				tooltips_multiKeyBackground = %s,
	            tooltips_beforeTitle = %s,
	            tooltips_afterTitle = %s,
	            tooltips_beforeBody = %s,
	            tooltips_afterBody = %s,
	            tooltips_beforeLabel = %s,
	            tooltips_afterLabel = %s,
	            tooltips_beforeFooter = %s,
	            tooltips_footer = %s,
	            tooltips_afterFooter = %s,

				/* Hover Configuration */
				hover_animationDuration = %d,

				/* Animation Configuration */
				animation_duration = %d,
				animation_easing = %s,
				animation_animateRotate = %d,
				animation_animateScale = %d,

				/* Misc Configuration */
				elements_rectangle_borderSkipped = %s,

				/* Scales Common Configuration X */
				scales_xAxes_type = %s,
				scales_xAxes_display = %d,
				scales_xAxes_position = %s,
				scales_xAxes_stacked = %d,

	            /* Scales Grid Line Configuration X */
	            scales_xAxes_gridLines_display = %d,
	            scales_xAxes_gridLines_color = %s,
	            scales_xAxes_gridLines_lineWidth = %s,
	            scales_xAxes_gridLines_drawBorder = %d,
	            scales_xAxes_gridLines_drawOnChartArea = %d,
	            scales_xAxes_gridLines_drawTicks = %d,
	            scales_xAxes_gridLines_tickMarkLength = %d,
	            scales_xAxes_gridLines_zeroLineWidth = %d,
	            scales_xAxes_gridLines_zeroLineColor = %s,
	            scales_xAxes_gridLines_offsetGridLines = %d,

	            /* Scales Title Configuration X */
	            scales_xAxes_scaleLabel_display = %d,
	            scales_xAxes_scaleLabel_labelString = %s,
	            scales_xAxes_scaleLabel_fontColor = %s,
	            scales_xAxes_scaleLabel_fontFamily = %s,
	            scales_xAxes_scaleLabel_fontSize = %d,
	            scales_xAxes_scaleLabel_fontStyle = %s,

	            /* Scales Tick Configuration X */
	            scales_xAxes_ticks_autoskip = %d,
	            scales_xAxes_ticks_display = %d,
	            scales_xAxes_ticks_fontColor = %s,
	            scales_xAxes_ticks_fontFamily = %s,
	            scales_xAxes_ticks_fontSize = %d,
	            scales_xAxes_ticks_fontStyle = %s,
	            scales_xAxes_ticks_labelOffset = %d,
	            scales_xAxes_ticks_maxRotation = %d,
	            scales_xAxes_ticks_minRotation = %d,
	            scales_xAxes_ticks_reverse = %d,
	            scales_xAxes_ticks_prefix = %s,
	            scales_xAxes_ticks_suffix = %s,
	            scales_xAxes_ticks_round = %s,

	            /* Scale Configuration Options X */
	            scales_xAxes_ticks_min = %s,
	            scales_xAxes_ticks_max = %s,
	            scales_xAxes_ticks_beginAtZero = %d,
	            scales_xAxes_ticks_maxTicksLimit = %s,
	            scales_xAxes_ticks_stepSize = %s,
	            scales_xAxes_ticks_suggestedMax = %s,
	            scales_xAxes_ticks_suggestedMin = %s,
	            scales_xAxes_ticks_fixedStepSize = %s,
	            scales_xAxes_categoryPercentage = %f,
	            scales_xAxes_barPercentage = %f,

	            /* Time Scale Configuration Options X */
	            scales_xAxes_time_format = %s,
	            scales_xAxes_time_tooltipFormat = %s,
	            scales_xAxes_time_unit_format = %s,
	            scales_xAxes_time_unit = %s,
	            scales_xAxes_time_unitStepSize = %d,
	            scales_xAxes_time_max = %s,
	            scales_xAxes_time_min = %s,

				/* Scales Common Configuration Y */
				scales_yAxes_type = %s,
				scales_yAxes_display = %d,
				scales_yAxes_position = %s,
				scales_yAxes_stacked = %d,

	            /* Scales Grid Line Configuration Y */
	            scales_yAxes_gridLines_display = %d,
	            scales_yAxes_gridLines_color = %s,
	            scales_yAxes_gridLines_lineWidth = %s,
	            scales_yAxes_gridLines_drawBorder = %d,
	            scales_yAxes_gridLines_drawOnChartArea = %d,
	            scales_yAxes_gridLines_drawTicks = %d,
	            scales_yAxes_gridLines_tickMarkLength = %d,
	            scales_yAxes_gridLines_zeroLineWidth = %d,
	            scales_yAxes_gridLines_zeroLineColor = %s,
	            scales_yAxes_gridLines_offsetGridLines = %d,

	            /* Scales Title Configuration Y */
	            scales_yAxes_scaleLabel_display = %d,
	            scales_yAxes_scaleLabel_labelString = %s,
	            scales_yAxes_scaleLabel_fontColor = %s,
	            scales_yAxes_scaleLabel_fontFamily = %s,
	            scales_yAxes_scaleLabel_fontSize = %d,
	            scales_yAxes_scaleLabel_fontStyle = %s,

	            /* Scales Tick Configuration Y */
	            scales_yAxes_ticks_autoskip = %d,
	            scales_yAxes_ticks_display = %d,
	            scales_yAxes_ticks_fontColor = %s,
	            scales_yAxes_ticks_fontFamily = %s,
	            scales_yAxes_ticks_fontSize = %d,
	            scales_yAxes_ticks_fontStyle = %s,
	            scales_yAxes_ticks_maxRotation = %d,
	            scales_yAxes_ticks_minRotation = %d,
	            scales_yAxes_ticks_mirror = %d,
	            scales_yAxes_ticks_padding = %d,
	            scales_yAxes_ticks_reverse = %d,
	            scales_yAxes_ticks_prefix = %s,
	            scales_yAxes_ticks_suffix = %s,
	            scales_yAxes_ticks_round = %s,

	            /* Scale Configuration Options Y */
	            scales_yAxes_ticks_min = %s,
	            scales_yAxes_ticks_max = %s,
	            scales_yAxes_ticks_beginAtZero = %d,
	            scales_yAxes_ticks_maxTicksLimit = %s,
	            scales_yAxes_ticks_stepSize = %s,
	            scales_yAxes_ticks_suggestedMax = %s,
	            scales_yAxes_ticks_suggestedMin = %s,
	            scales_yAxes_ticks_fixedStepSize = %s,
                scales_yAxes_categoryPercentage = %f,
	            scales_yAxes_barPercentage = %f,

                /* Scales Common Configuration Y */
				scales_y2Axes_type = %s,
				scales_y2Axes_display = %d,
				scales_y2Axes_position = %s,

	            /* Scales Grid Line Configuration Y */
	            scales_y2Axes_gridLines_display = %d,
	            scales_y2Axes_gridLines_color = %s,
	            scales_y2Axes_gridLines_lineWidth = %s,
	            scales_y2Axes_gridLines_drawBorder = %d,
	            scales_y2Axes_gridLines_drawOnChartArea = %d,
	            scales_y2Axes_gridLines_drawTicks = %d,
	            scales_y2Axes_gridLines_tickMarkLength = %d,
	            scales_y2Axes_gridLines_zeroLineWidth = %d,
	            scales_y2Axes_gridLines_zeroLineColor = %s,
	            scales_y2Axes_gridLines_offsetGridLines = %d,

	            /* Scales Title Configuration Y */
	            scales_y2Axes_scaleLabel_display = %d,
	            scales_y2Axes_scaleLabel_labelString = %s,
	            scales_y2Axes_scaleLabel_fontColor = %s,
	            scales_y2Axes_scaleLabel_fontFamily = %s,
	            scales_y2Axes_scaleLabel_fontSize = %d,
	            scales_y2Axes_scaleLabel_fontStyle = %s,

	            /* Scales Tick Configuration Y */
	            scales_y2Axes_ticks_autoskip = %d,
	            scales_y2Axes_ticks_display = %d,
	            scales_y2Axes_ticks_fontColor = %s,
	            scales_y2Axes_ticks_fontFamily = %s,
	            scales_y2Axes_ticks_fontSize = %d,
	            scales_y2Axes_ticks_fontStyle = %s,
	            scales_y2Axes_ticks_maxRotation = %d,
	            scales_y2Axes_ticks_minRotation = %d,
	            scales_y2Axes_ticks_mirror = %d,
	            scales_y2Axes_ticks_padding = %d,
	            scales_y2Axes_ticks_reverse = %d,
	            scales_y2Axes_ticks_prefix = %s,
	            scales_y2Axes_ticks_suffix = %s,
	            scales_y2Axes_ticks_round = %s,

	            /* Scale Configuration Options Y */
	            scales_y2Axes_ticks_min = %s,
	            scales_y2Axes_ticks_max = %s,
	            scales_y2Axes_ticks_beginAtZero = %d,
	            scales_y2Axes_ticks_maxTicksLimit = %s,
	            scales_y2Axes_ticks_stepSize = %s,
	            scales_y2Axes_ticks_suggestedMax = %s,
	            scales_y2Axes_ticks_suggestedMin = %s,
	            scales_y2Axes_ticks_fixedStepSize = %s,

                /* RL Scale Common Configuration */
				scales_rl_display = %d,

				/* RL Scale Grid Line Configuration */
				scales_rl_gridLines_display = %d,
				scales_rl_gridLines_color = %s,
				scales_rl_gridLines_lineWidth = %s,

				/* RL Scale Angle Line Configuration */
				scales_rl_angleLines_display = %d,
				scales_rl_angleLines_color = %s,
				scales_rl_angleLines_lineWidth = %s,

				/* RL Scale Point Label Configuration */
				scales_rl_pointLabels_fontSize = %d,
				scales_rl_pointLabels_fontColor = %s,
				scales_rl_pointLabels_fontFamily = %s,
				scales_rl_pointLabels_fontStyle = %s,

				/* RL Scale Tick Configuration */
				scales_rl_ticks_display = %d,
				scales_rl_ticks_autoskip = %d,
				scales_rl_ticks_reverse = %d,
				scales_rl_ticks_prefix = %s,
				scales_rl_ticks_suffix = %s,
				scales_rl_ticks_round = %s,
				scales_rl_ticks_fontSize = %d,
				scales_rl_ticks_fontColor = %s,
				scales_rl_ticks_fontFamily = %s,
				scales_rl_ticks_fontStyle = %s,

				/* RL Scale Configuration Options */
				scales_rl_ticks_min = %s,
				scales_rl_ticks_max = %s,
				scales_rl_ticks_suggestedMin = %s,
				scales_rl_ticks_suggestedMax = %s,
				scales_rl_ticks_stepSize = %s,
				scales_rl_ticks_fixedStepSize = %s,
				scales_rl_ticks_maxTicksLimit = %s,
				scales_rl_ticks_beginAtZero = %d,
				scales_rl_ticks_showLabelBackdrop = %d,
				scales_rl_ticks_backdropColor = %s,
				scales_rl_ticks_backdropPaddingX = %d,
				scales_rl_ticks_backdropPaddingY = %d

				WHERE id = %d",
			$model_obj->name,
			$model_obj->description,
			$model_obj->type,
			$model_obj->rows,
			$model_obj->columns,
			$model_obj->labels,
			0,

			//Common Chart Configuration
			$model_obj->canvas_transparent_background,
			$model_obj->canvas_backgroundColor,
			$model_obj->margin_top,
			$model_obj->margin_bottom,
			$model_obj->width,
			$model_obj->height,
			$model_obj->responsive,
			$model_obj->responsiveAnimationDuration,
			$model_obj->maintainAspectRatio,
            $model_obj->fixed_height,

			//Title Configuration
			$model_obj->title_display,
			$model_obj->title_position,
			$model_obj->title_fullWidth,
			$model_obj->title_fontSize,
			$model_obj->title_fontFamily,
			$model_obj->title_fontColor,
			$model_obj->title_fontStyle,
			$model_obj->title_padding,

			//Legend Configuration
			$model_obj->legend_display,
			$model_obj->legend_position,
			$model_obj->legend_fullWidth,
			$model_obj->legend_toggle_dataset,

			//Legend Label configuration
			$model_obj->legend_labels_boxWidth,
			$model_obj->legend_labels_fontSize,
			$model_obj->legend_labels_fontStyle,
			$model_obj->legend_labels_fontColor,
			$model_obj->legend_labels_fontFamily,
			$model_obj->legend_labels_padding,

			//Tooltip Configuration
			$model_obj->tooltips_enabled,
			$model_obj->tooltips_mode,
			$model_obj->tooltips_backgroundColor,
			$model_obj->tooltips_titleFontFamily,
			$model_obj->tooltips_titleFontSize,
			$model_obj->tooltips_titleFontStyle,
			$model_obj->tooltips_titleFontColor,
			$model_obj->tooltips_titleMarginBottom,
			$model_obj->tooltips_bodyFontFamily,
			$model_obj->tooltips_bodyFontSize,
			$model_obj->tooltips_bodyFontStyle,
			$model_obj->tooltips_bodyFontColor,
			$model_obj->tooltips_footerFontFamily,
			$model_obj->tooltips_footerFontSize,
			$model_obj->tooltips_footerFontStyle,
			$model_obj->tooltips_footerFontColor,
			$model_obj->tooltips_footerMarginTop,
			$model_obj->tooltips_xPadding,
			$model_obj->tooltips_yPadding,
			$model_obj->tooltips_caretSize,
			$model_obj->tooltips_cornerRadius,
			$model_obj->tooltips_multiKeyBackground,
			$model_obj->tooltips_beforeTitle,
			$model_obj->tooltips_afterTitle,
			$model_obj->tooltips_beforeBody,
			$model_obj->tooltips_afterBody,
			$model_obj->tooltips_beforeLabel,
			$model_obj->tooltips_afterLabel,
			$model_obj->tooltips_beforeFooter,
			$model_obj->tooltips_footer,
			$model_obj->tooltips_afterFooter,

			//Hover Configuration
			$model_obj->hover_animationDuration,

			//Animation Configuration
			$model_obj->animation_duration,
			$model_obj->animation_easing,
			$model_obj->animation_animateRotate,
			$model_obj->animation_animateScale,

			//Misc Configuration
			$model_obj->elements_rectangle_borderSkipped,

			//Scales Common Configuration X
			$model_obj->scales_xAxes_type,
			$model_obj->scales_xAxes_display,
			$model_obj->scales_xAxes_position,
			$model_obj->scales_xAxes_stacked,

			//Scales Grid Line Configuration X
			$model_obj->scales_xAxes_gridLines_display,
			$model_obj->scales_xAxes_gridLines_color,
			$model_obj->scales_xAxes_gridLines_lineWidth,
			$model_obj->scales_xAxes_gridLines_drawBorder,
			$model_obj->scales_xAxes_gridLines_drawOnChartArea,
			$model_obj->scales_xAxes_gridLines_drawTicks,
			$model_obj->scales_xAxes_gridLines_tickMarkLength,
			$model_obj->scales_xAxes_gridLines_zeroLineWidth,
			$model_obj->scales_xAxes_gridLines_zeroLineColor,
			$model_obj->scales_xAxes_gridLines_offsetGridLines,

			//Scales Title Configuration X
			$model_obj->scales_xAxes_scaleLabel_display,
			$model_obj->scales_xAxes_scaleLabel_labelString,
			$model_obj->scales_xAxes_scaleLabel_fontColor,
			$model_obj->scales_xAxes_scaleLabel_fontFamily,
			$model_obj->scales_xAxes_scaleLabel_fontSize,
			$model_obj->scales_xAxes_scaleLabel_fontStyle,

			//Scales Tick Configuration X
			$model_obj->scales_xAxes_ticks_autoskip,
			$model_obj->scales_xAxes_ticks_display,
			$model_obj->scales_xAxes_ticks_fontColor,
			$model_obj->scales_xAxes_ticks_fontFamily,
			$model_obj->scales_xAxes_ticks_fontSize,
			$model_obj->scales_xAxes_ticks_fontStyle,
			$model_obj->scales_xAxes_ticks_labelOffset,
			$model_obj->scales_xAxes_ticks_maxRotation,
			$model_obj->scales_xAxes_ticks_minRotation,
			$model_obj->scales_xAxes_ticks_reverse,
			$model_obj->scales_xAxes_ticks_prefix,
			$model_obj->scales_xAxes_ticks_suffix,
			$model_obj->scales_xAxes_ticks_round,

			//Scale Configuration Options X
			$model_obj->scales_xAxes_ticks_min,
			$model_obj->scales_xAxes_ticks_max,
			$model_obj->scales_xAxes_ticks_beginAtZero,
			$model_obj->scales_xAxes_ticks_maxTicksLimit,
			$model_obj->scales_xAxes_ticks_stepSize,
			$model_obj->scales_xAxes_ticks_suggestedMax,
			$model_obj->scales_xAxes_ticks_suggestedMin,
			$model_obj->scales_xAxes_ticks_fixedStepSize,
			$model_obj->scales_xAxes_categoryPercentage,
			$model_obj->scales_xAxes_barPercentage,

			//Time Scale Configuration Options X
			$model_obj->scales_xAxes_time_format,
			$model_obj->scales_xAxes_time_tooltipFormat,
			$model_obj->scales_xAxes_time_unit_format,
			$model_obj->scales_xAxes_time_unit,
			$model_obj->scales_xAxes_time_unitStepSize,
			$model_obj->scales_xAxes_time_max,
			$model_obj->scales_xAxes_time_min,

			//Scales Common Configuration Y
			$model_obj->scales_yAxes_type,
			$model_obj->scales_yAxes_display,
			$model_obj->scales_yAxes_position,
			$model_obj->scales_yAxes_stacked,

			//Scales Grid Line Configuration Y
			$model_obj->scales_yAxes_gridLines_display,
			$model_obj->scales_yAxes_gridLines_color,
			$model_obj->scales_yAxes_gridLines_lineWidth,
			$model_obj->scales_yAxes_gridLines_drawBorder,
			$model_obj->scales_yAxes_gridLines_drawOnChartArea,
			$model_obj->scales_yAxes_gridLines_drawTicks,
			$model_obj->scales_yAxes_gridLines_tickMarkLength,
			$model_obj->scales_yAxes_gridLines_zeroLineWidth,
			$model_obj->scales_yAxes_gridLines_zeroLineColor,
			$model_obj->scales_yAxes_gridLines_offsetGridLines,

			//Scales Title Configuration Y
			$model_obj->scales_yAxes_scaleLabel_display,
			$model_obj->scales_yAxes_scaleLabel_labelString,
			$model_obj->scales_yAxes_scaleLabel_fontColor,
			$model_obj->scales_yAxes_scaleLabel_fontFamily,
			$model_obj->scales_yAxes_scaleLabel_fontSize,
			$model_obj->scales_yAxes_scaleLabel_fontStyle,

			//Scales Tick Configuration Y
			$model_obj->scales_yAxes_ticks_autoskip,
			$model_obj->scales_yAxes_ticks_display,
			$model_obj->scales_yAxes_ticks_fontColor,
			$model_obj->scales_yAxes_ticks_fontFamily,
			$model_obj->scales_yAxes_ticks_fontSize,
			$model_obj->scales_yAxes_ticks_fontStyle,
			$model_obj->scales_yAxes_ticks_maxRotation,
			$model_obj->scales_yAxes_ticks_minRotation,
			$model_obj->scales_yAxes_ticks_mirror,
			$model_obj->scales_yAxes_ticks_padding,
			$model_obj->scales_yAxes_ticks_reverse,
			$model_obj->scales_yAxes_ticks_prefix,
			$model_obj->scales_yAxes_ticks_suffix,
			$model_obj->scales_yAxes_ticks_round,

			//Scale Configuration Options Y
			$model_obj->scales_yAxes_ticks_min,
			$model_obj->scales_yAxes_ticks_max,
			$model_obj->scales_yAxes_ticks_beginAtZero,
			$model_obj->scales_yAxes_ticks_maxTicksLimit,
			$model_obj->scales_yAxes_ticks_stepSize,
			$model_obj->scales_yAxes_ticks_suggestedMax,
			$model_obj->scales_yAxes_ticks_suggestedMin,
			$model_obj->scales_yAxes_ticks_fixedStepSize,
			$model_obj->scales_yAxes_categoryPercentage,
			$model_obj->scales_yAxes_barPercentage,

			//Scales Common Configuration Y
			$model_obj->scales_y2Axes_type,
			$model_obj->scales_y2Axes_display,
			$model_obj->scales_y2Axes_position,

			//Scales Grid Line Configuration Y
			$model_obj->scales_y2Axes_gridLines_display,
			$model_obj->scales_y2Axes_gridLines_color,
			$model_obj->scales_y2Axes_gridLines_lineWidth,
			$model_obj->scales_y2Axes_gridLines_drawBorder,
			$model_obj->scales_y2Axes_gridLines_drawOnChartArea,
			$model_obj->scales_y2Axes_gridLines_drawTicks,
			$model_obj->scales_y2Axes_gridLines_tickMarkLength,
			$model_obj->scales_y2Axes_gridLines_zeroLineWidth,
			$model_obj->scales_y2Axes_gridLines_zeroLineColor,
			$model_obj->scales_y2Axes_gridLines_offsetGridLines,

			//Scales Title Configuration Y
			$model_obj->scales_y2Axes_scaleLabel_display,
			$model_obj->scales_y2Axes_scaleLabel_labelString,
			$model_obj->scales_y2Axes_scaleLabel_fontColor,
			$model_obj->scales_y2Axes_scaleLabel_fontFamily,
			$model_obj->scales_y2Axes_scaleLabel_fontSize,
			$model_obj->scales_y2Axes_scaleLabel_fontStyle,

			//Scales Tick Configuration Y
			$model_obj->scales_y2Axes_ticks_autoskip,
			$model_obj->scales_y2Axes_ticks_display,
			$model_obj->scales_y2Axes_ticks_fontColor,
			$model_obj->scales_y2Axes_ticks_fontFamily,
			$model_obj->scales_y2Axes_ticks_fontSize,
			$model_obj->scales_y2Axes_ticks_fontStyle,
			$model_obj->scales_y2Axes_ticks_maxRotation,
			$model_obj->scales_y2Axes_ticks_minRotation,
			$model_obj->scales_y2Axes_ticks_mirror,
			$model_obj->scales_y2Axes_ticks_padding,
			$model_obj->scales_y2Axes_ticks_reverse,
			$model_obj->scales_y2Axes_ticks_prefix,
			$model_obj->scales_y2Axes_ticks_suffix,
			$model_obj->scales_y2Axes_ticks_round,

			//Scale Configuration Options Y
			$model_obj->scales_y2Axes_ticks_min,
			$model_obj->scales_y2Axes_ticks_max,
			$model_obj->scales_y2Axes_ticks_beginAtZero,
			$model_obj->scales_y2Axes_ticks_maxTicksLimit,
			$model_obj->scales_y2Axes_ticks_stepSize,
			$model_obj->scales_y2Axes_ticks_suggestedMax,
			$model_obj->scales_y2Axes_ticks_suggestedMin,
			$model_obj->scales_y2Axes_ticks_fixedStepSize,

			/* RL Scale Common Configuration */
			$model_obj->scales_rl_display,

			/* RL Scale Grid Line Configuration */
			$model_obj->scales_rl_gridLines_display,
			$model_obj->scales_rl_gridLines_color,
			$model_obj->scales_rl_gridLines_lineWidth,

			/* RL Scale Angle Line Configuration */
			$model_obj->scales_rl_angleLines_display,
			$model_obj->scales_rl_angleLines_color,
			$model_obj->scales_rl_angleLines_lineWidth,

			/* RL Scale Point Label Configuration */
			$model_obj->scales_rl_pointLabels_fontSize,
			$model_obj->scales_rl_pointLabels_fontColor,
			$model_obj->scales_rl_pointLabels_fontFamily,
			$model_obj->scales_rl_pointLabels_fontStyle,

			/* RL Scale Tick Configuration */
			$model_obj->scales_rl_ticks_display,
			$model_obj->scales_rl_ticks_autoskip,
			$model_obj->scales_rl_ticks_reverse,
			$model_obj->scales_rl_ticks_prefix,
			$model_obj->scales_rl_ticks_suffix,
			$model_obj->scales_rl_ticks_round,
			$model_obj->scales_rl_ticks_fontSize,
			$model_obj->scales_rl_ticks_fontColor,
			$model_obj->scales_rl_ticks_fontFamily,
			$model_obj->scales_rl_ticks_fontStyle,

			/* RL Scale Configuration Options */
			$model_obj->scales_rl_ticks_min,
			$model_obj->scales_rl_ticks_max,
			$model_obj->scales_rl_ticks_suggestedMin,
			$model_obj->scales_rl_ticks_suggestedMax,
			$model_obj->scales_rl_ticks_stepSize,
			$model_obj->scales_rl_ticks_fixedStepSize,
			$model_obj->scales_rl_ticks_maxTicksLimit,
			$model_obj->scales_rl_ticks_beginAtZero,
			$model_obj->scales_rl_ticks_showLabelBackdrop,
			$model_obj->scales_rl_ticks_backdropColor,
			$model_obj->scales_rl_ticks_backdropPaddingX,
			$model_obj->scales_rl_ticks_backdropPaddingY,

			$chart_id);
		$result = $wpdb->query($safe_sql);

		/*
		 * Get the data of the model (identified with $model_id) from the 'data' table and assign these data to the
		 * considered chart data structure (identified with $chart_id)
		 */

		//delete the data structure of the considered chart (identified with $chart_id)
		global $wpdb; $table_name = $wpdb->prefix . $this->shared->get('slug') . "_data";
		$safe_sql   = $wpdb->prepare( "DELETE FROM $table_name WHERE chart_id = %d", $chart_id );
		$result     = $wpdb->query( $safe_sql );

		//assign the new values (the values of the model) to the data structure of the considered chart
		global $wpdb; $table_name = $wpdb->prefix . $this->shared->get('slug') . "_data";
		$safe_sql = $wpdb->prepare("SELECT * FROM $table_name WHERE chart_id = %d ORDER BY row_index ASC", $model_id);
		$data_a = $wpdb->get_results($safe_sql, ARRAY_A);
		foreach($data_a as $key => $data){

			global $wpdb;
			$table_name = $wpdb->prefix . $this->shared->get('slug') . "_data";
			$safe_sql   = $wpdb->prepare( "INSERT INTO $table_name SET
				chart_id = %d,
				row_index = %d,
				content = %s,
				label = %s,
				fill = %d,
				lineTension = %f,
				backgroundColor = %s,
				borderWidth = %s,
				borderColor = %s,
				borderCapStyle = %s,
				borderDash = %s,
				borderDashOffset = %f,
				borderJoinStyle = %s,
				pointBorderColor = %s,
				pointBackgroundColor = %s,
				pointBorderWidth = %s,
				pointRadius = %s,
				pointHoverRadius = %s,
				pointHitRadius = %s,
				pointHoverBackgroundColor = %s,
				pointHoverBorderColor = %s,
				pointHoverborderWidth = %s,
				pointStyle = %s,
				showLine = %d,
				spanGaps = %d,
				hoverBackgroundColor = %s,
				hoverBorderColor = %s,
				hoverBorderWidth = %s,
				hitRadius = %d,
				hoverRadius = %s,
				plotY2 = %d",
				$chart_id,
				$data['row_index'],
				$data['content'],
				$data['label'],
				$data['fill'],
				$data['lineTension'],
				$data['backgroundColor'],
				$data['borderWidth'],
				$data['borderColor'],
				$data['borderCapStyle'],
				$data['borderDash'],
				$data['borderDashOffset'],
				$data['borderJoinStyle'],
				$data['pointBorderColor'],
				$data['pointBackgroundColor'],
				$data['pointBorderWidth'],
				$data['pointRadius'],
				$data['pointHoverRadius'],
				$data['pointHitRadius'],
				$data['pointHoverBackgroundColor'],
				$data['pointHoverBorderColor'],
				$data['pointHoverBorderWidth'],
				$data['pointStyle'],
				$data['showLine'],
				$data['spanGaps'],
				$data['hoverBackgroundColor'],
				$data['hoverBorderColor'],
				$data['hoverBorderWidth'],
				$data['hitRadius'],
				$data['hoverRadius'],
				$data['plotY2']);
			$result = $wpdb->query( $safe_sql );

		}

		/*
		 Unescape $model_obj and $data_a with the object_stripslashes() method to avoid double escaping on the generated
		 json string (because special characters are also escaped by json_encode )
		 */

		//escape $model_obj
		$model_obj = $this->shared->object_stripslashes($model_obj);

		//escape the values in $data_a and convert the array in an object
		foreach($data_a as $key => $value){
			$data_a[$key] = $this->shared->object_stripslashes((object) $value);
		}
		$data_obj = (object) $data_a;

		//data of all the fields of the main form
		$chart_json = json_encode($model_obj);

		//data of the fields of the data structure
		$data_json = json_encode($data_obj);

		//prepare the answer
		$answer_a = array(
			'chart' => $chart_json,
			'data' => $data_json
		);

		//encode the answer in the JSON format
		$answer_json = json_encode($answer_a);

		echo $answer_json;
		die();

	}

}