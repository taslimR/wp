<?php
/*
 * Plugin Name: Accordion Tables
 * Version: 1.6.0
 * Plugin URI: http://accordiontables.webfixfast.com
 * Description: Exciting UI tool for creating tables with expanding cells. Opens tons of possibilities. Smooth animation, responsive, direct front-end editing as well as live editing preview! Makes it super convenient to set up beautiful tables. Check out its dozen presets covering a variety of cool applications.
 * Author: WordPressaHolic
 * Requires at least: 4.0
 *
 * Text Domain: accordion-tables
 *
 */

/**
 * Load generic
 */
function aTables_require_once ( ) {
	$prefix = 'php/generic/';
	$require = array(
			'WPH_Duplicate_Posts' => 'class-wph-duplicate-posts.php',
			'WPH_Shortcode_Printer' => 'class-wph-shortcode-printer.php',
			'WPH_Frontend_Editor' => 'class-wph-frontend-editor.php',
			'WPH_Writer' => 'class-wph-writer.php',
			'WPH_Frontend_Editor_Keys' => 'class-wph-frontend-editor-keys.php',
			'WPH_Style_Ops_Writer' => 'class-wph-style-ops-writer.php',
			'WPH_Preset_Handler' => 'class-wph-presets-handler.php',
		);
	foreach ($require as $name => $path)
		if (!class_exists($name)) require_once($prefix.$path);

}
aTables_require_once( );
require_once('php/generic/fa-icons-index.php');

/**
 * Load custom
 */
require_once( 'php/custom/class-atables-overall-ops.php' );
require_once( 'php/custom/class-atables-device-groups-menu.php' );
require_once( 'php/custom/class-atables-target-ops.php' );
require_once( 'php/custom/class-atables-image-other-ops.php' );
require_once( 'php/custom/class-atables-cell-types.php' );
require_once( 'php/custom/class-atables-custom-post-type.php' );
require_once( 'php/custom/class-atables-markup-generator.php' );
require_once( 'php/presets/presets.php' );

/**
 * Common variables
 */
$aTables_assets = plugins_url().'/accordion-tables/assets';
$aTables_images_url = $aTables_assets.'/images';
$aTables_images_path = dirname(__FILE__) .'/assets/images';

/**
 * Load plugin textdomain.
 */
$wph_editor_translatables_aTables; // wph editor translations specific to accordion tables
$wph_editor_translatables_common; // wph editor translations common to all plugins
add_action( 'plugins_loaded', 'aTables_load_textdomain' );
function aTables_load_textdomain() {
	load_plugin_textdomain( 'accordion-tables', false,  dirname( plugin_basename( __FILE__ ) ) .'/language' );
	load_plugin_textdomain( 'wph-editor', false,  dirname( plugin_basename( __FILE__ ) ) .'/language' );

	// first opportunity to expose strings for translation
	global $wph_editor_translatables_aTables;
	global $wph_editor_translatables_common;

	require_once(dirname(__FILE__) .'/language/wph-editor/common/common.php');
	require_once(dirname(__FILE__) .'/language/wph-editor/plugin-specific/atables.php');
}

/**
 * Register post type
 * must be fired after plugin text domain is loaded
 */
add_action( 'plugins_loaded', 'aTables_post_type' );
function aTables_post_type() {
	global $aTables_assets;
	$aTables_Custom_Post_Type = new aTables_Custom_Post_Type('Accordion Tables', __( 'Accordion Tables', 'accordion-tables' ), __( 'Accordion Table', 'accordion-tables' ), '', $aTables_assets.'/logo/Accordion-Tables-Logo-white-20x20.png');

}

/**
 * Preview table on front-end
 */
add_filter( 'the_content', 'aTables_view_post' );
function aTables_view_post ($content) {
	global $post;
	if (get_post_type( $post ) === "accordiontables") {
		$table = get_post_meta( $post->ID, 'aTables', true );
		$content .= $table;
	};
	return $content;

}

$aTables_presets_path = plugin_dir_path(__FILE__).'php/presets/presets.php';
$aTables_presets_handler = new WPH_Preset_Handler ();
//uncomment the following to collect presets
// $aTables_presets_handler->compile_presets('accordiontables', 'aTables', 'preset:', $aTables_presets_path, 'aTables_presets');

/**
 * Register shortcode
 */
$aTables_shortcode_printer = new WPH_Shortcode_Printer('aTables', 'Accordion Tables', 'http://accordiontables.webfixfast.com/');

/*Enqueue scripts*/
function aTables_scripts () {
	$admin_view = current_user_can('manage_options') ? true : false;

	global $aTables_assets;
	//CSS
	//--editor related
	if ($admin_view) {
		wp_register_style('perfect-scrollbar',  $aTables_assets.'/css/perfect-scrollbar.min.css');
		wp_enqueue_style( 'perfect-scrollbar' );

		wp_register_style('spectrum',  $aTables_assets.'/css/spectrum.css');
		wp_enqueue_style( 'spectrum' );

		wp_register_style('wph_editor',  $aTables_assets.'/css/wph_editor.css');
		wp_enqueue_style( 'wph_editor' );

		wp_register_style('wph_keys',  $aTables_assets.'/css/wph_keys.css');
		wp_enqueue_style( 'wph_keys' );

		wp_register_style('tooltipster',  $aTables_assets.'/css/tooltipster.css');
		wp_enqueue_style( 'tooltipster' );

		wp_register_style('tooltipster-light',  $aTables_assets.'/css/tooltipster-light.css');
		wp_enqueue_style( 'tooltipster-light' );
	}

	//--viewer related
	wp_register_style('aTables_style',  $aTables_assets.'/css/aTables.css');
	wp_enqueue_style( 'aTables_style' );

	if (strpos(home_url(),'localhost') !== false) wp_register_style('fawesome', 'http://localhost/font-awesome/4.3.0/css/font-awesome.min.css'); // load fa locally
	wp_register_style('fontawesome', '//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css');
	wp_enqueue_style( 'fontawesome' );

	//JS
	//--editor related
	if ($admin_view) {
		wp_enqueue_media();

		wp_enqueue_script('jquery-ui-sortable');
		wp_enqueue_script('jquery-ui-draggable');
		wp_enqueue_script('jquery-ui-slider');
		wp_enqueue_script('jquery-ui-button');

		wp_register_script('perfect-scrollbar',  $aTables_assets.'/js/perfect-scrollbar.min.js', array('jquery'));
		wp_enqueue_script( 'perfect-scrollbar' );

		wp_register_script('spectrum',  $aTables_assets.'/js/spectrum.js', array('jquery'));
		wp_enqueue_script( 'spectrum' );

		wp_register_script('jquery.copycss',  $aTables_assets.'/js/jquery.copycss.js', array('jquery'));
		wp_enqueue_script('jquery.copycss');

		wp_register_script('mousetrap.min',  $aTables_assets.'/js/mousetrap.min.js', array('jquery'));
		wp_enqueue_script('mousetrap.min');

		wp_register_script('wph_editor_privileged',  $aTables_assets.'/js/wph_editor_privileged.js', array('jquery'));
		wp_enqueue_script('wph_editor_privileged');
		wp_localize_script( 'wph_editor_privileged', 'wph_ajax', array( "url"=>admin_url( 'admin-ajax.php' ), "nonce"=> wp_create_nonce( 'wph-nonce' ) ) );

		wp_register_script('jquery.tooltipster.min',  $aTables_assets.'/js/jquery.tooltipster.min.js', array('jquery'));
		wp_enqueue_script('jquery.tooltipster.min');

		if ( 'accordiontables' == get_post_type( ) ) wp_dequeue_script( 'autosave' );
	}

	//--viewer related
	wp_register_script('jquery.color.min',  $aTables_assets.'/js/jquery.color.min.js', array('jquery'));
	wp_enqueue_script('jquery.color.min');

	wp_register_script('jquery.animate-shadow-min',  $aTables_assets.'/js/jquery.animate-shadow-min.js', array('jquery'));
	wp_enqueue_script('jquery.animate-shadow-min');

	wp_register_script('wph_editor_public',  $aTables_assets.'/js/wph_editor_public.js', array('jquery'));
	wp_enqueue_script('wph_editor_public');
	if( ! $admin_view )
		wp_localize_script( 'wph_editor_public', 'wph_ajax', array( "url"=>admin_url( 'admin-ajax.php' ) ) );

	wp_register_script('aTables_script',  $aTables_assets.'/js/aTables.min.js', array('jquery', 'wph_editor_public'));
	wp_enqueue_script('aTables_script');

	wp_register_script('aTables_imageCenter',  $aTables_assets.'/js/jquery.blImageCenter.aTables.js', array('jquery'));
	wp_enqueue_script('aTables_imageCenter');

	//Mobile Detect
	if( ! class_exists( 'Mobile_Detect' ) ) require_once('php/generic/Mobile_Detect.php');

	$mobile_detect = new Mobile_Detect;
	$device_group = 'pc';
	if( $mobile_detect->isTablet(  ) ){ // Tablets
		$device_group = 'tablet';
	} else if( $mobile_detect->isMobile( ) && ! $mobile_detect->isTablet( ) ){ // Mobile phones
		$device_group = 'mobile';
	}

	echo "<script type='text/javascript'> var aTables_device_group = '$device_group' ;</script>";

}
add_action('wp_enqueue_scripts', 'aTables_scripts');
add_action('admin_enqueue_scripts', 'aTables_scripts');

/**
 * Duplicate table
 */
if (class_exists ('WPH_Duplicate_Posts')) new WPH_Duplicate_Posts ('accordiontables');

/**
 * Remove 'Comments' column
 */
add_filter( 'manage_edit-accordiontables_columns', 'aTables_remove_comments_column' );
function aTables_remove_comments_column ($columns) {
    unset( $columns['comments'] );
    return $columns;

}

/**
 * Clear the custom post type page
 */
add_action('admin_init', 'aTables_clear_post_screen');
function aTables_clear_post_screen( ){
	global $pagenow;

	if ($pagenow !== "post.php" && $pagenow !== "post-new.php") return;

	if ($pagenow === "post-new.php" && !(isset($_REQUEST["post_type"]))) return;
	if ($pagenow === "post-new.php" && $_REQUEST["post_type"] !== "accordiontables") return;

	if ($pagenow === "post.php" && !isset($_REQUEST["post"])) return;
	if ($pagenow === "post.php" &&  get_post_type( $_REQUEST["post"] ) !== "accordiontables") return;

	$remove = array('editor', 'thumbnail', 'excerpt', 'comments', 'revisions', 'post-formats');
	foreach ($remove as $item) {
		remove_post_type_support('accordiontables', $item);
	}
	remove_meta_box('submitdiv', 'accordiontables', 'side');

	// hide sortables
	add_action( 'admin_enqueue_scripts', 'aTables_hide_sortables' );

}

function aTables_hide_sortables( ){
	echo "
			<style>
			#poststuff #post-body.columns-2 {
			width:100%;
			margin-right: 0;
			}

			#postbox-container-1 {
			display:none;
			}

			body #post-body #normal-sortables {
			min-height: 0!important;
			}

			</style>
	";

};

/**
 * Add a preview metabox to the main column on the Post and Page edit screens.
 */
add_action( 'add_meta_boxes', 'aTables_add_meta_box' );
function aTables_add_meta_box() {
	// presets
	add_meta_box(
		'aTables_presets',
		__( 'Presets', 'accordion-tables' ),
		'aTables_presets_meta_box_callback',
		'accordiontables'
	);
	// color theme
	add_meta_box(
		'aTables_color_theme',
		__( 'Color Theme', 'accordion-tables' ),
		'aTables_color_theme_meta_box_callback',
		'accordiontables'
	);
	// preview
	add_meta_box(
		'aTables_preview',
		__( 'Preview', 'accordion-tables' ),
		'aTables_preview_meta_box_callback',
		'accordiontables'
	);
}

/**
 * Presets meta box
 */
function aTables_presets_meta_box_callback( $post ) {

	// provide js with the presets map
	global $aTables_presets;
	$aTables_presets_json_map = json_encode($aTables_presets['map']);
	echo "<script> var aTables_presets_map = $aTables_presets_json_map</script>";

	// select fields
	//-- name
	echo
	'<span>'.__('Name: ', 'accordion-tables').'</span>
	<select class="atw_preset_input atw_preset_name">
		<option value="default">Default</option>
	</select>';

	//-- type
	echo
	'<span>'.__('Type: ', 'accordion-tables').'</span>
	<select class="atw_preset_input atw_preset_type">
		<option value="default">Default</option>
	</select>';

	//-- color theme
	echo
	'<span>'.__('Color theme: ', 'accordion-tables').'</span>
	<select class="atw_preset_input atw_preset_color_scheme">
		<option value="default">Default</option>
	</select>';

	echo // submit
	'<span class="button atw_preset_input_button" data-atw-message="'.__('Loading a preset table will overwrite the current table, leading to loss of any unsaved work.', 'accordion-tables').'">'.__('Go!', 'accordion-tables').'</span>';

}

/**
 * Color theme meta box
 */
function aTables_color_theme_meta_box_callback ($post) {
	$id = $post->ID;
	echo // submit
	'<span class="button atw_extract_color_theme_button" data-wph-for-post-id="'.$id.'">'.__('Extract color theme', 'accordion-tables').'</span>';
}

function aTables_markup_generator () {
	$aTables_markup_generator = new aTables_Markup_Generator();
	$aTables_markup_generator->duplicate(array("column"=>4, "cover-cell"=>4));
	return $aTables_markup_generator->return_markup();
}

/**
 * Preview meta box
 */
function aTables_preview_meta_box_callback( $post ) {
	$value = get_post_meta( $post->ID, 'aTables', true );

	if (empty($value)) {
		$value = aTables_get_preset(false, 'attributes', 'type a', 'blue');
		$presets_handler = new WPH_Preset_Handler();
		$imgs_path = $GLOBALS["aTables_images_path"].'\\';
		$imgs_url = $GLOBALS["aTables_images_url"].'/';
		$value = $presets_handler->insert_dummy_images($value, '%image%', $imgs_path, $imgs_url); // replace %image% with dummy images
	}
	global $post;
	$id = $post->ID;
	echo '<strong>'.__('Shortcode', 'accordion-tables').':</strong> [aTables id="'.$id.'"]';
	echo "<span class='wph_post_id' data-wph-post-id='$id'></span>";
	echo "<div class='aTables_preview' style='margin-top:15px;'>".str_replace("%post-id%", $id, $value).'</div>';

}

/**
 * Save a table
 */
add_action('wp_ajax_aTables_save', 'aTables_save');
add_action('wp_ajax_nopriv_aTables_save', 'aTables_save');
function aTables_save () {
	if (!isset($_REQUEST['nonce']) || !wp_verify_nonce( $_REQUEST['nonce'], 'wph-nonce' )) die('Not permitted');
	$id = $_REQUEST['post-id'];
	update_post_meta($id, "aTables", $_REQUEST['html']);
	$status = get_post_status( $id );
	if ($status !== "publish") wp_publish_post( $id );
	echo json_encode(array("result"=>"success", "message"=>"The table has been saved."));
	die();

}

/**
 * Get a table preset
 */
add_action('wp_ajax_aTables_get_preset_via_ajax', 'aTables_get_preset_via_ajax');
function aTables_get_preset_via_ajax () {
	if (!isset($_REQUEST['nonce']) || !wp_verify_nonce( $_REQUEST['nonce'], 'wph-nonce' )) die('Not permitted');
	$name = trim(strtolower($_REQUEST['name']));
	$type = trim(strtolower($_REQUEST['type']));
	$color = trim(strtolower($_REQUEST['color']));

	aTables_get_preset (true, $name, $type, $color);
	die();
}

function aTables_get_preset ($ajax, $name, $type, $color) {
	$message = __( 'The preset has been successfully retrieved.', 'accordion-tables' );
	$html = false;

	//get the html
	global $aTables_presets;
	$map = $aTables_presets['map'];
	if (!isset($map[$name]) || !isset($map[$name][$type]) || !isset($map[$name][$type][$color])) {
		$message = __( 'Sorry, there is no presets with these specifications.', 'accordion-tables' );
	} else {
		$pos = $map[$name][$type][$color];
		$html = $aTables_presets[$pos];
		global $aTables_presets_handler;
		$imgs_path = $GLOBALS["aTables_images_path"].'\\';
		$imgs_url = $GLOBALS["aTables_images_url"].'/';
		$html = stripslashes($aTables_presets_handler->insert_dummy_images ($html, "%image%", $imgs_path, $imgs_url));

	}

	$result = $html ? "success" : "failure";

	if ($ajax) {
		echo json_encode(array("result"=>$result, "message"=>$message, "html"=>$html));
	} else {
		return $html;
	}
}

/**
 * Print out the frontend editor
 */
add_action ( 'get_footer', 'aTables_print_frontend_editor' );
add_action ( 'admin_footer', 'aTables_print_frontend_editor' );
function aTables_print_frontend_editor( ){
	// check viewer status
	$admin_view = current_user_can( 'manage_options' ) ? true : false;
	if( ! $admin_view ) return;

	$aTables_frontend_editor = new WPH_Frontend_Editor();
	return $aTables_frontend_editor->echo_markup( );

}

/**
 * Print out the frontend editor modifier
 */
add_filter("wph-frontend-editor-assemble", "aTables_frontend_editor_modifier");
function aTables_frontend_editor_modifier ($html) {

	// Device groups menu
	$aTables_Device_Groups_Menu = new aTables_Device_Groups_Menu();
	$aTables_Device_Groups_Menu->build();
	$html = $html.$aTables_Device_Groups_Menu->html;

	// Overall ops
	//-- pc
	$aTables_Overall_Ops = new aTables_Overall_Ops();
	$aTables_Overall_Ops->build();
	$html = $html.$aTables_Overall_Ops->html;

	//-- tablet
	$aTables_Overall_Ops = new aTables_Overall_Ops();
	$aTables_Overall_Ops->material["Overall Options"] = array(
			"template"=>"section",
			"class"=>"aTables_overallOps_tablet liveChanges",
			"data"=>
				array(
						"execution"=>"live_changes_overall_execution",
						"default-attr-label"=>"overall-settings",
						"attr-label"=>"overall-settings-tablet",
					),
		);
	$aTables_Overall_Ops->build();
	$html = $html.$aTables_Overall_Ops->html;

	//-- mobile
	$aTables_Overall_Ops = new aTables_Overall_Ops();
	$aTables_Overall_Ops->material["Overall Options"] = array(
			"template"=>"section",
			"class"=>"aTables_overallOps_mobile liveChanges",
			"data"=>
				array(
						"execution"=>"live_changes_overall_execution",
						"default-attr-label"=>"overall-settings",
						"attr-label"=>"overall-settings-mobile",
					),
		);
	$aTables_Overall_Ops->build();
	$html = $html.$aTables_Overall_Ops->html;

	// Image other ops
	$aTables_Image_Other_Ops = new aTables_Image_Other_Ops();
	$aTables_Image_Other_Ops->build();
	$html = $html.$aTables_Image_Other_Ops->html;

	// Target
	$aTables_Target_Ops = new aTables_Target_Ops();
	$aTables_Target_Ops->build();
	$html = $html.$aTables_Target_Ops->html;

	// Types
	$structure =
		array(
			"Container"=>array(),
		);

	$material =
		array(
			"Container"=>
				array(
					"tag"=>"div",
					"class"=>"aTables_cellTypes types liveChanges",
					"data"=>
						array(
							"execution"=>"live_changes_type_execution"
						)
				),
		);

	$types =
		array(
			"Regular",
			"Column Title",
			"Image",
			"Price",
			"Link"
		);

	foreach ($types as $type) {
		$type_ = $type;
		if ($type === "Title") $type = "Column Title";
		$material[$type] =
			array(
				'tag' => 'span',
				'content' => $type,
				'class' => 'clickable',
				'data' =>
					array(
						"type"=> $type_,
					),
			);
		$structure['Container'][] = $type;
	}

	$aTables_Types = new WPH_Writer($structure, $material);
	$aTables_Types->class_prefix = "wph_editor_";
	$aTables_Types->data_prefix = "data-wph-";

	$aTables_Types->build();
	$html = $html.$aTables_Types->html;

	return $html;
}

/**
 * Licensing
 */

if( ! class_exists( 'WPH_License_Manager' ) )  require_once( plugin_dir_path( __FILE__ ) . '/license-manager/class-wph-license-manager.php' );
$args = array(
		'plugin_name' => 'Accordion Tables',
		'plugin_prefix' => 'aTables',
		'page_type_key' => 'post_type',
		'page_type_val' => 'accordiontables',
		'store' => 'http://shop.webfixfast.com',
		'path' => __FILE__,
		'version' => '1.6.0',
		'mode' => 'cc',
		'updates' => true,
);
$wph_license_manager_new = new WPH_License_Manager( $args );

/**
 * Print out cell types
 */
function aTables_cell_types_js_obj ( ){
	// check viewer status
	$admin_view = current_user_can( 'manage_options' ) ? true : false;
	if( ! $admin_view ) return;

	$aTables_cell_types_arr = array();
	$structure = aTables_Cell_Types::$structure;
	foreach ($structure as $type=>$arr) {
		$obj = new aTables_Cell_Types($type);
		$aTables_cell_types_arr[$type] = $obj->return_markup();
	}
	$aTables_cell_types_arr = json_encode ($aTables_cell_types_arr);
	echo "<script>var aTables_cell_types = $aTables_cell_types_arr;</script>";
}
add_action ( 'get_footer', 'aTables_cell_types_js_obj' );
add_action ( 'admin_footer', 'aTables_cell_types_js_obj' );

/**
 * Print translations array
 */
if (!isset($wph_editor_translatables_aTables)) $wph_editor_translatables_aTables = array();
if (!isset($wph_editor_translatables_common)) $wph_editor_translatables_common = array();
if (!isset($wph_editor_translation_array_labels)) $wph_editor_translation_array_labels = array();

if (!isset($wph_editor_translation_array_labels['accordion-tables'])) $wph_editor_translation_array_labels['accordion-tables'] = &$wph_editor_translatables_aTables;
if (!isset($wph_editor_translation_array_labels['wph-editor'])) $wph_editor_translation_array_labels['wph-editor'] = &$wph_editor_translatables_common;
//note to self: enable the following section when collecting translation strings
//disable this -- start
//add_action('wp_after_admin_bar_render', 'aTables_print_wph_editor_translation_array');
//disable this -- end
function aTables_print_wph_editor_translation_array () {
	echo __FILE__;
	//common
	$file_wph_editor_common = 'C:\wamp\www\wordpress\wp-content\plugins\accordion-tables\language\wph-editor\common\common.php';

	global $wph_editor_translatables_common;
	$wph_editor_translatables_common = var_export($wph_editor_translatables_common, true);
	$wph_editor_translatables_common = stripslashes($wph_editor_translatables_common);

	$wph_editor_translatables_common = str_replace(" '__(", "__(", $wph_editor_translatables_common);
	$wph_editor_translatables_common = str_replace("',", ",", $wph_editor_translatables_common);

	file_put_contents($file_wph_editor_common, '<?php $wph_editor_translatables_common = '.$wph_editor_translatables_common.'?>');

	//aTables
	$file_wph_editor_aTables = 'C:\wamp\www\wordpress\wp-content\plugins\accordion-tables\language\wph-editor\plugin-specific\atables.php';

	global $wph_editor_translatables_aTables;
	$wph_editor_translatables_aTables = var_export($wph_editor_translatables_aTables, true);
	$wph_editor_translatables_aTables = stripslashes($wph_editor_translatables_aTables);

	$wph_editor_translatables_aTables = str_replace(" '__(", "__(", $wph_editor_translatables_aTables);
	$wph_editor_translatables_aTables = str_replace("',", ",", $wph_editor_translatables_aTables);

	file_put_contents($file_wph_editor_aTables, '<?php $wph_editor_translatables_aTables = '.$wph_editor_translatables_aTables.'?>');
	return;

}

?>
