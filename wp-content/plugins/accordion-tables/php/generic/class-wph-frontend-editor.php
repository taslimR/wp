<?php
/**
* This class uses a WPH_Writer object to generate markup for the frontend editor
*/
class WPH_Frontend_Editor {

/**
* WPH_Writer object
*/	
public $writer;

/**
* textdomain
*/	
public $textdomain = "wph-editor";

public $structure = 
	array(
		"windowOps" => array(//prepend classes: (wph_editor_)save and data with: (data-wph-)save 
			"Visibility",
			"Save",
			//"Drag",
		),

		 "settingsType" => array(
			"Settings",
			"Style",
			"Content",
			"Type",
			"Overall",
			"Other",
			"Target",
		),

		 "componentIndex" => array(
			"Components",
			"Overall",
			"Title",
			"Icon",
			"Value",
			"Details",
		),
		
		 "contentSource" => array(
			"Source",
			"Text/HTML",
			"WP Editor",
			/*
			"Post/Page",
			"Video",
			"Map",
			"Image",
			*/
		),

		 "componentState" => array (
			"Idle",
			"Hover",
			"Active",
		),
		
		 "componentStyleIndex" => array (
			"Font",
			"Dimensions",
			"Placement",
			"Border",
			"Background",
			"Shadow",
		),

		"componentStyles",
		"componentContent"=>array(
			"contentTextarea",
		),

		"fontAwesomeIndex",
		
		"wp-editor",

		"imageSelectorContainer"=>array(
			"imageSelectorInput",
			"imageSelectorButton",
		),

	);	

public $material = 
	array(
		//windowOps
		"windowOps"=>array(
			"tag"=>"div",
			"content"=>"",
			"class"=> "windowOps",
			"class no prefix"=> "wph_editor_children_non_selectable",
		),
		"Visibility"=>array(
			"tag"=>"span",
			"content"=>"",
			"class"=> "visibility",
			"class no prefix"=> "fa fa-eye-slash",
		),
		"Save"=>array(
			"tag"=>"span",
			"content"=>"",
			"class"=> "save unsaved",
			"class no prefix"=> "fa fa-save",
		),
		"Drag"=>array(
			"tag"=>"span",
			"class"=> "body_draggable_handle",
			"class no prefix"=> "fa fa-arrows",
		),
		//component styles
		"componentStyles"=>array(
			"tag"=>"div",
			"class"=>"componentStyles liveChanges",
			"data"=>array(
				"execution"=>"live_changes_style_execution",
			)
		),
		
		//component content
		"componentContent"=>array(
			"tag"=>"div",
			"class"=>"componentContent liveChanges",
			"data"=>array(
				"execution"=>"live_changes_content_execution",
			)
		),
		
		//component content textarea
		"contentTextarea"=>array(
			"tag"=>"textarea",
			"class"=>"contentTextarea",
			"class no prefix"=>"mousetrap",
			"placeholder"=>"Enter your text/html content here..."
		),
		
		//font awesome
		"fontAwesomeIndex"=>array(
			"tag"=>"div",
			"class"=>"fontAwesomeIndex liveChanges perfectScrollbar",
			"data"=>array(
				"width"=>"100%",
				"height"=>"300px",
				"execution"=>"live_changes_icon_execution",
			)
		),
		
		//image selector
		"imageSelectorContainer"=>array(
			"tag"=>"div",
			"class"=>"liveChanges imageSelectorContainer",
			"data"=>array(
				"execution"=>"live_changes_image_execution",
			)
		),
		//image selection 
		"imageSelectorInput"=>array(
			"tag"=>"input",
			"class"=>"imageSelectorInput",
		),
		
		"imageSelectorButton"=>array(
			"tag"=>"button",
			"content"=>"Select",
			"class"=>"imageSelectorButton",
		),

		//labels
		"Settings" =>array(
			"tag"=>"span",
			"content"=>"Settings:",
		),
		
		"Components" =>array(
			"tag"=>"span",
			"content"=>"Components:",
		),
		
		"wp-editor" =>array(
			"tag"=>"div",
			"class"=>"wp-editor",
			"content"=>"",
		),
		
		"Source" =>array(
			"tag"=>"span",
			"content"=>"Source:",
		),

	);

/**
* Programmatically filled up
*/
public $new_material = array();
public $callback = "fill_up_material_template";

function fill_up_material_template ($args) {
	$label = $args[0];
	$type = $args[1];
	if (isset($this->writer->material[$label])) return;
	$class = str_replace(" ", "-", $label);
	if ($type === "val") {
		$this->writer->material[$label] =
			array(
			"tag"=>"span",
			"content"=>$label,
			"class" => $class,
			"data"=>
				array(
					"type"=>$label,
				)
			);
	} else {
		$this->writer->material[$label] =
			array(
			"tag"=>"div",
			"class" => $class,
			"data"=>
				array(
					"type"=>$label,
				)
			);
	}
}

function fill_up_material (&$material, $structure) {
	$this->writer->iterate($structure, array($this, $this->callback));

	/**
	 * Style ops markup
	 */
	$aTables_frontend_editor_style_ops = new WPH_Style_Ops_Writer();
	$aTables_frontend_editor_style_ops->build();
	$style_ops = $aTables_frontend_editor_style_ops->html;
	$this->writer->material["componentStyles"]["content"] = $style_ops;
	
	/**
	 * Types index markup

	$aTables_frontend_editor_types = new aTables_Types_Markup_Generator();
	$aTables_frontend_editor_types->build();
	$types_index = $aTables_frontend_editor_types->html;
	$this->writer->material["componentTypes"]["content"] = $types_index;
	 */

	/**
	 * Font Awesome index markup
	 */
	global $wph_fa_icons_index;
	$this->writer->material["fontAwesomeIndex"]["content"] = $wph_fa_icons_index;
	
	/**
	 * WP Editor markup
	 */
	 
	//$this->writer->material["wp-editor"]["content"] = "<script>tinymce.init({    selector: '.wph_editor_wp-editor' });</script>";
	ob_start();
	$args = array(
		'wpautop' => false,
	);
	wp_editor( '', 'wph_editor_wp_editor' );
	$this->writer->material["wp-editor"]["content"] = ob_get_contents();
	ob_end_clean();
	
	
}

function init_writer () {
	if (!$this->writer) $this->writer = new WPH_Writer();
	$this->writer->data_prefix = "data-wph-";
	$this->writer->class_prefix = "wph_editor_";
	$this->writer->structure = $this->structure;
	$this->writer->material = $this->material;
	$this->writer->textdomain = $this->textdomain;
}

function __construct ($structure = false, $material = false) {
	if( defined( 'WPH_Frontend_Editor' ) ) {
		throw new Exception( 'Attempting to instantiate WPH_Frontend_Editor more than once!' );
	};
	if ($structure) $this->structure = $structure;
	if ($material) $this->material = $material;
	$this->init_writer ();
	define( 'WPH_Frontend_Editor', TRUE );

}

function get_keys () {
	//print keys
	$key_writer = new WPH_Frontend_Editor_Keys();
	$keys = $key_writer->return_keys_obj();
	return "<script>var wph_editor_keys = $keys;</script>";
}

/**
* Makes any changes needed such as array modification and then uses writer to build markup
*/
function assemble () {
	$this->writer->structure = apply_filters( "wph-frontend-editor-assemble-structure", $this->writer->structure ); // filter structure
	$this->writer->material = apply_filters( "wph-frontend-editor-assemble-material", $this->writer->material ); // filter material

	$this->fill_up_material($this->writer->material, $this->writer->structure); // manipulate
	$this->writer->build();
	$html = $this->writer->flush_html( );
	$html = apply_filters( "wph-frontend-editor-assemble", $html ); // filter hook
	$html .= $this->get_keys(); // adds the editor keys
	return "<div class='wph_editor_undocked wph_editor_invisible wph_editor_body'>".$html."</div>";
	
}

function return_markup () {
	return $this->assemble();
	
}

function echo_markup () {
	echo $this->assemble();

}

}
?>