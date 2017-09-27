<?php
/**
* This class uses a WPH_Writer object to generate markup for the component types of Accordion Tables
*/	
class aTables_Cell_Types {

/**
* WPH_Writer object
*/	
public $writer;

/**
* Textdomain
*/	
public $textdomain = "accordion-tables";

/**
* Mostly the $structure and $material arrays will be changed by client code
*/
public static $structure =
	array (
		"Column Title"=>array(
			"column-title"=>array(
				"column-title-icon",
				"column-title-text",
			),
		),
		"Image"=>array(
			"image-container"=>array(
				"image",
			),
		),
		"Price"=>array(
			"price"=>array(
				"prev",
				"current",
				"duration",
			),
		),
		"Regular"=>array(
			"cell"=>array(
				"cell-title"=>array(
					"cell-val",
					"cell-title-icon",
					"cell-title-text",
					"cell-title-icon-2",
				),
				"cell-details",
			),
		),
		"Link"=>array(
			"link"=>array(
				"link-icon",
				"link-text"
			),
		),
	);
	
public $material =
	array (
		"container"=>array(
			"tag"=>"div",
			"class no prefix"=>"wph_editor_aTables_componentTypes",
		),
		
		"column-title"=>array(
			"tag"=>"div",
			"class"=>"column_title cell trigger",
		),
		"column-title-icon"=>array(
			"tag"=>"div",
			"class"=>"column_title_icon",
			"class no prefix" => "fa fa-plus",
			"data"=>array(
				"icon"=>"fa fa-plus",
			),
		),
		"column-title-text"=>array(
			"tag"=>"div",
			"class"=>"column_title_text",
			"content"=>"Column Title",
		),
		"image-container"=>array(
			"tag"=>"div",
			"class"=>"image cell",
		),
		"image"=>array(
			"tag"=>"img",
			"class"=>"trigger",
			"src"=>"%image%",
		),
		"price"=>array(
			"tag"=>"div",
			"class"=>"price cell trigger",
		),
		"prev"=>array(
			"tag"=>"span",
			"class"=>"prev_price",
			"content"=>"$250",
		),
		"current"=>array(
			"tag"=>"span",
			"class"=>"current_price",
			"content"=>"$200",
		),
		"duration"=>array(
			"tag"=>"span",
			"class"=>"price_duration",
			"content"=>"/ mo.",
		),		
		"cover-cell"=>array(
			"tag"=>"div",
			"class"=>"cover",
			"data"=>array(
				"key-type"=>"cell",
			)
		),
		"cover-title" =>array(
			"template"=>"cover-cell",
		),
		"cover-image" =>array(
			"template"=>"cover-cell",
		),
		"cover-price" =>array(
			"template"=>"cover-cell",
		),
		"cover-link" =>array(
			"template"=>"cover-cell",
		),
		"cell"=>array(
			"tag"=>"div",
			"class"=>"cell",
		),
		"cell-title"=>array(
			"tag"=>"div",
			"class"=>"title trigger",
		),
		"cell-title-icon"=>array(
			"tag"=>"div",
			"class"=>"cell_title_icon",
			"class no prefix" => "fa fa-plus",
			"data"=>array(
				"icon"=>"fa fa-plus",
			),
		),
		"cell-title-icon-2"=>array(
			"template"=>"cell-title-icon",
			"class"=>"cell_title_icon_2",
		),
		"cell-title-text"=>array(
			"tag"=>"div",
			"class"=>"cell_title_text",
			"content"=>"Cell Title",
		),
		"cell-val"=>array(
			"tag"=>"div",
			"class"=>"cell_title_val",
			"content"=>"40",
		),
		"cell-details"=>array(
			"tag"=>"div",
			"class"=>"details",
			"content"=>"Cell details lorem ipsum dolor sit lorem ipsum dolor sit lorem ipsum dolor sit lorem ipsum.",
		),
		"link"=>array(
			"tag"=>"div",
			"class"=>"link cell",
		),
		"link-text"=>array(
			"tag"=>"a",
			"content"=>"Know More",
			"class"=>"link_text",
			"attr"=>array(
				"href"=>"#top"
			),
		),
		"link-icon"=>array(
			"tag"=>"div",
			"class"=>"link_icon",
			"class no prefix" => "fa fa-plus",
			"data"=>array(
				"icon"=>"fa fa-plus",
			),
		),
	);

function __construct ($type = "Regular", $structure = false, $material = false) {
	if ($structure) $this->structure = $structure;
	if ($material) $this->material = $material;
	$this->init_writer ($type);

}
	
function init_writer ($type) {
	if (!$this->writer) $this->writer = new WPH_Writer();
	$this->writer->data_prefix = "data-wph-";
	$this->writer->class_prefix = "atw_";
	$this->writer->structure = aTables_Cell_Types::$structure[$type];
	$this->writer->material = $this->material;
	$this->writer->textdomain = $this->textdomain;

}

function assemble () {
	$this->writer->build();
	$html = $this->writer->html;
	
	// insert dummy image(s)
	global $aTables_presets_handler;
	$imgs_path = $GLOBALS["aTables_images_path"].'\\';
	$imgs_url = $GLOBALS["aTables_images_url"].'/';
	$html = $aTables_presets_handler->insert_dummy_images($html, '%image%', $imgs_path, $imgs_url); // replace %image% with dummy images	

	$this->writer->clear();
	return $html;
}

function return_markup () {
	return $this->assemble();
}

function echo_markup () {
	echo $this->assemble();
}

}

?>