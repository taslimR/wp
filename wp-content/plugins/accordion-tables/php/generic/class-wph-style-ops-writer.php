<?php
/**
 * The Form Writer class is meant to return markup that is ready for jQuery UI to use 
 * Basic rules:
 * Every secondary level element in the structure is going to be wrapped in a p tag
 * It is also going to be preceded by a label node
 * Adding a ['no label'] key in material will ensure that structure element will not get a label inserted before it
 */


class WPH_Style_Ops_Writer extends WPH_Writer {

public $class_prefix = "wph_editor_";	

public $textdomain = "wph-editor";

public $structure =
	array(
		//Dimensions
		"Dimensions"=>array(
			"Width"=>array(
				"small input",
			),
			
			"Height"=>array(
				"small input",
			),
			
			"Max width"=>array(
				"small input",
			),
			
			"Max height"=>array(
				"small input",
			),
			
			"Min width"=>array(
				"small input",
			),
			
			"Min height"=>array(
				"small input",
			),
			
			"Padding top"=>array(
				"small input",
			),
			"Padding right"=>array(
				"small input",
			),
			"Padding bottom"=>array(
				"small input",
			),
			"Padding left"=>array(
				"small input",
			),

			"Padding"=>array(
				"input",
			),
			
		),
		
		//Font
		"Font"=>array(
			"Font size"=>array(
				"small input",
			),
			
			"Line height"=>array(
				"small input",
			),
			
			"Color",
			
			"Font family",
			
			"Font weight"=>array(
				"Font weight"=>array(
					"Normal",
					"Bold",
					"Bolder",
					"Light",
					"100",
					"200",
					"300",
					"400",
					"500",
					"600",
					"700",
					"800",
					"900",
				),
			),
			
			"Text align"=>array(
				"Text align"=>array(
					"Left",
					"Right",
					"Center",
					"Justify",
				),
			),
			
			"Text decoration"=>array(
				"Text decoration"=>array(
					"None",
					"Underline",
					"Line-Through",
					"Overline",
				),
			),
			
			"Font style"=>array(
				"Font style"=>array(
					"Normal",
					"Italic",
				),
			),
			
			"Text transform"=>array(
				"Text transform"=>array(
					"None",
					"Capitalize",
					"Uppercase",
					"Lowercase",
				),
			),
			
			"Cursor"=>array(
				"Cursor"=>array(
					"Auto",
					"Default",
					"Pointer",
				),
			),
		),
		
		//Placement
		"Placement"=> array(

			"Margin top"=>array(
				"small input",
			),
			"Margin right"=>array(
				"small input",
			),
			"Margin bottom"=>array(
				"small input",
			),
			"Margin left"=>array(
				"small input",
			),
			"Margin"=>array(
				"input",
			),
			/*
			"Z index"=>array(
				"small input",
			),

			"Position"=>array(
				"Position"=>array(
					"Relative",
					"Absolute",
					"Static",
				),
			),
			
			"Top pos"=>array(
				"top pos label",
				"small input",
			),
			
			"Right pos"=>array(
				"right pos label",
				"small input",
			),
			
			"Bottom pos"=>array(
				"bottom pos label",
				"small input",
			),

			"Left pos"=>array(
				"left pos label",
				"small input",
			),
			*/
			"Display"=>array(
				"Display"=>array(
					"Inline-block",
					"Inline",
					"Block",
					"Table",
					"Table-cell",
					"None",
				),
			),
			
			"Float"=>array(
				"Float"=>array(
					"Left",
					"Right",
					"None",
				),
			),

			"Clear"=>array(
				"Clear"=>array(
					"None",
					"Left",
					"Right",
					"Both",
				),
			),
			
			"Vertical align"=>array(
				"Vertical align"=>array(
					"Baseline",
					"Top",
					"Middle",
					"Bottom",
				),
			),

		),
		
		//Borders
		"Border_"=>array(		
			"Border"=>array(
				"input",
			),
			"Border top width"=>array(
				"small input",
			),			
			"Border right width"=>array(
				"small input",
			),
			"Border bottom width"=>array(
				"small input",
			),
			"Border left width"=>array(
				"small input",
			),
			
			"Border radius"=>array(
				"input",
			),
			"Border top left radius"=>array(
				"small input",
			),
			"Border top right radius"=>array(
				"small input",
			),
			"Border bottom right radius"=>array(
				"small input",
			),
			"Border bottom left radius"=>array(
				"small input",
			),
			
			"Border color",
			
			"Border style"=>array(
				"Border style"=>array(
					"None",
					"Solid",
					"Dotted",
					"Dashed",
					"Double",				
				)
			),
		),
			
		//Background
		"Background"=>array(

			"Background color",
			
			"Background image"=>array(
				"imageSelectorInput",
				"imageSelectorButton",				
			),
			
			"Opacity"=>array(
				"small input",
			),

			"Background position"=>array(
				"Background position"=>array(
					"center center",
					"left top",
					"left center",
					"left bottom",
					"right top",
					"right center",
					"right bottom",
					"center top",
					"center bottom",
				),
			),

			"Background repeat"=>array(
				"Background repeat"=>array(
					"repeat",
					"repeat-x",
					"repeat-y",
					"no-repeat",
				),
			),

			"Background attachment"=>array(
				"Background attachment"=>array(
					"scroll",
					"fixed",
					"local",
				),
			),
		),
		
		//Shadow
		"Shadow"=>array(
		
			"Shadow color",
			
			"Shadow offset x"=>array(
				"small input",
			),
			
			"Shadow offset y"=>array(
				"small input",
			),
			
			"Shadow blur"=>array(
				"small input",
			),
			
			"Shadow radius"=>array(
				"small input",
			),
			
			"Shadow inset"=>array(
				"Shadow inset"=>array(
					"No",
					"Yes",
				),
			),
			
			"Shadow"=>array(
				"input",
			),
			
		),
		
		//Unde Settings
		"undoSetting",

	);

public $templates = 
	array (
		//Section template
		"section"=>array(
			"tag"=>"div",
			"class"=>"section",
		),

		//Option templates
		"p"=>array(
			"tag"=>"p",
			"class"=>"p",
		),
		"input"=>array(
			"tag"=>"input",
			"class"=>"input"
		),
		"small input"=>array(
			"tag"=>"input",
			"type"=>"number",
			"class"=>"small-input"
		),
		"label"=>array(
			"tag"=>"label",
			"class"=>"label"
		),
		"slider" =>array(
			"tag"=>"span",
			"class"=>"slider",
		),
		"select"=>array(
			"tag"=>"select",
			"class"=>"select",
		),
		"checkbox"=>array(
			"tag"=>"input",
			"type"=>"checkbox",
			"class"=>"checkbox",
			"name"=>"my_checkbox",
			"options"=>array(
				"Option 1"=>"val1",
				"Option 2"=>"val2",
				"Option 3"=>"val3",
			),
		),
		"radio"=>array(
			"tag"=>"input",
			"type"=>"radio",
			"class"=>"radio",
			"name"=>"my_radio",
		),
		"color picker"=>array(
			"tag"=>"input",
			"class"=>"color_picker",
			"type"=>"text",
		),
		"option"=>array(
			"tag"=>"option",
			"value"=>"value",
			"content"=>"option",
		),
	);
	
public $material = 
	array (
		//Options
		//Dimensions
		"Dimensions" => array(
			"template"=>"section",
			"class"=>"Dimensions",
		),
		//Width
		"Width" =>array(
			"template"=>"slider",
			"data"=>array(
				"min"=>0,
				"max"=>400,
				"step"=>5,
			),
		),
		//Height
		"Height" =>array(
			"template"=>"slider",
			"data"=>array(
				"min"=>0,
				"max"=>400,
				"step"=>5,
			),
		),
		
		//Max-Width
		"Max width" =>array(
			"template"=>"Width",
		),
		//Max-Height
		"Max height" =>array(
			"template"=>"Height",
		),
		//Min-Width
		"Min width" =>array(
			"template"=>"Width",
		),
		//Min-Height
		"Min height" =>array(
			"template"=>"Height",
		),

		//Font
		"Font" => array(
			"template"=>"section",
			"class"=>"Font",
		),		
		//Font Size
		"Font size label"=>array(
			"template"=>"label",
		),
		"Font size" =>array(
			"template"=>"slider",
		),
		
		//Line Height
		"Line height label"=>array(
			"template"=>"label",
			"content"=>"Line Height:"
		),
		"Line height" =>array(
			"template"=>"slider",
		),		
		
		//Font Color
		"Color"=>array(
			"template"=>"color picker",
		),
		
		//Font Family
		"Font family"=>array(
			"template"=>"input",
		),
		
		//Font Weight
		"Font weight" =>array(
			"template"=>"select",
		),
		"Normal" =>array(
			"template"=>"option",
		),
		"Bold" =>array(
			"template"=>"option",
		),
		"Bolder" =>array(
			"template"=>"option",
		),		
		"Light" =>array(
			"template"=>"option",
		),
		"100"=>array(
			"template"=>"option",
		),
		"200"=>array(
			"template"=>"option",
		),
		"300"=>array(
			"template"=>"option",
		),
		"400" =>array(
			"template"=>"option",
		),
		"500"=>array(
			"template"=>"option",
		),
		"600"=>array(
			"template"=>"option",
		),
		"700"=>array(
			"template"=>"option",
		),
		"800"=>array(
			"template"=>"option",
		),
		"900"=>array(
			"template"=>"option",
		),
		
		
		//Text Decoration
		"Text decoration" =>array(
			"template"=>"select",
		),
		"None" =>array(
			"template"=>"option",
			"value"=>"none",
			"content"=>"None",
		),
		"Underline" =>array(
			"template"=>"option",
		),
		"Line-Through" =>array(
			"template"=>"option",
		),
		"Overline" =>array(
			"template"=>"option",
		),
		
		//Font Style
		"Font style"=>array(
			"template"=>"select",
		),
		
		"Italic" =>array(
			"template"=>"option",
		),
		
		//Text align
		"Text align"=>array(
			"template"=>"select",
		),
		
		"Center"=>array(
			"template"=>"option",
		),
		
		"Justify"=>array(
			"template"=>"option",
		),
		
		//Text Transform
		"Text transform"=>array(
			"template"=>"select",
		),
		
		"Capitalize"=>array(
			"template"=>"option",
		),

		"Uppercase"=>array(
			"template"=>"option",
		),

		"Lowercase"=>array(
			"template"=>"option",
		),
		
		//Cursor
		"Cursor"=>array(
			"template"=>"select",
		),
		
		"Auto"=>array(
			"template"=>"option",
		),
		
		"Default"=>array(
			"template"=>"option",
		),

		"Pointer"=>array(
			"template"=>"option",
		),
		
		//Placement		
		"Placement" => array(
			"template"=>"section",
			"class"=>"Placement",
		),

		//Position
		"Position"=>array(
			"template"=>"select",
		),
		
		"Absolute"=>array(
			"template"=>"option",
		),
		
		"Relative"=>array(
			"template"=>"option",
		),
		
		"Static"=>array(
			"template"=>"option",
		),

		//Top
		"Top pos"=>array(
			"template"=>"slider",
			"data"=>array(
				"property"=>"top",
				"max"=>"100",
				"min"=>"-20",
			)
		),
		"Top pos label"=>array(
			"template"=>"label",
			"content"=>"Top",
			"data"=>array(
				"property"=>"top",
			)
		),

		//Right
		"Right pos"=>array(
			"template"=>"slider",
			"data"=>array(
				"max"=>"100",
				"min"=>"-20",
			)
		),
		"Right pos label"=>array(
			"template"=>"label",
			"content"=>"Right",
			"data"=>array(
				"property"=>"right",
			)
		),
		
		//Bottom
		"Bottom pos"=>array(
			"template"=>"slider",
			"data"=>array(
				"property"=>"bottom",
				"max"=>"100",
				"min"=>"-20",
			)
		),
		"Bottom pos label"=>array(
			"template"=>"label",
			"content"=>"Bottom",
			"data"=>array(
				"property"=>"bottom",
			)
		),
		
		//Left
		"Left pos"=>array(
			"template"=>"slider",
			"data"=>array(
				"max"=>"100",
				"min"=>"-20",
			)
		),
		"Left pos label"=>array(
			"template"=>"label",
			"content"=>"Left",
			"data"=>array(
				"property"=>"left",
			)
		),
		
		//Display
		"Display"=>array(
			"template"=>"select",
		),

		"Inline-block"=>array(
			"template"=>"option",
		),
		
		"Inline"=>array(
			"template"=>"option",
		),
		
		"Block"=>array(
			"template"=>"option",
		),
		
		"Table"=>array(
			"template"=>"option",
		),

		"Table-cell"=>array(
			"template"=>"option",
		),		
		
		//Float
		"Float"=>array(
			"template"=>"select",
		),
		
		"Left"=>array(
			"template"=>"option",
		),
		
		"Right"=>array(
			"template"=>"option",
		),

		//Clear
		"Clear"=>array(
			"template"=>"select",
		),
		
		"Left"=>array(
			"template"=>"option",
		),
		
		"Right"=>array(
			"template"=>"option",
		),
		
		"Both"=>array(
			"template"=>"option",
		),

		//Vertical align
		"Vertical align"=>array(
			"template"=>"select",
		),
		
		"Baseline"=>array(
			"template"=>"option",
		),
		
		"Top"=>array(
			"template"=>"option",
		),
		
		"Both"=>array(
			"template"=>"option",
		),
		
		"Middle"=>array(
			"template"=>"option",
		),
		
		"Bottom"=>array(
			"template"=>"option",
		),

		//Margin
		"Margin label"=>array(
			"template"=>"label",
			"data"=>array(
				"decompose"=>"margin",
			)
		),
		
		"Margin top"=>array(
			"template"=>"slider",
		),		
		"Margin top label"=>array(
			"template"=>"label",
			"data"=>array(
				"compose"=>"margin",
			)
		),

		"Margin right"=>array(
			"template"=>"slider",
		),
		"Margin right label"=>array(
			"template"=>"label",
			"data"=>array(
				"compose"=>"margin",
			)
		),
		
		"Margin bottom"=>array(
			"template"=>"slider",
		),
		"Margin bottom label"=>array(
			"template"=>"label",
			"data"=>array(
				"compose"=>"margin",
			)
		),
		
		"Margin left"=>array(
			"template"=>"slider",
		),
		"Margin left label"=>array(
			"template"=>"label",
			"data"=>array(
				"compose"=>"margin",
			)
		),
		
		//Z index
		"Z index"=>array(
			"template"=>"slider",
		),		

		//Padding
		"Padding label"=>array(
			"template"=>"label",
			"data"=>array(
				"decompose"=>"padding",
			)
		),

		"Padding top"=>array(
			"template"=>"slider",
		),		
		"Padding top label"=>array(
			"template"=>"label",
			"data"=>array(
				"compose"=>"padding",
			)
		),
		
		"Padding right"=>array(
			"template"=>"slider",
		),
		"Padding right label"=>array(
			"template"=>"label",
			"data"=>array(
				"compose"=>"padding",
			)
		),
		
		"Padding bottom"=>array(
			"template"=>"slider",
		),
		"Padding bottom label"=>array(
			"template"=>"label",
			"data"=>array(
				"compose"=>"padding",
			)
		),
		
		"Padding left"=>array(
			"template"=>"slider",
		),
		"Padding left label"=>array(
			"template"=>"label",
			"data"=>array(
				"compose"=>"padding",
			)
		),

		//Border
		"Border_"=>array(
			"template"=>"section",
			"class"=>"Border",
		),

		"Border label"=>array(
			"template"=>"label",
			"data"=>array(
				"decompose"=>"border",
			),
		),
		//Width
		"Border top width"=>array(
			"template"=>"slider",
		),
		"Border top width label"=>array(
			"template"=>"label",
			"data"=>array(
				"compose"=>"border",
			)
		),

		"Border right width"=>array(
			"template"=>"slider",
		),
		"Border right width label"=>array(
			"template"=>"label",
			"data"=>array(
				"compose"=>"border",
			)
		),

		"Border bottom width"=>array(
			"template"=>"slider",
		),
		"Border bottom width label"=>array(
			"template"=>"label",
			"data"=>array(
				"compose"=>"border",
			)
		),

		"Border left width"=>array(
			"template"=>"slider",
		),
		"Border left width label"=>array(
			"template"=>"label",
			"data"=>array(
				"compose"=>"border",
			)
		),
		
		//Border Radius
		"Border radius label"=>array(
			"template"=>"label",
			"data"=>array(
				"decompose"=>"borderRadius",
			)
		),
		
		"Border top left radius"=>array(
			"template"=>"slider",
		),
		"Border top left radius label"=>array(
			"template"=>"label",
			"data"=>array(
				"compose"=>"borderRadius",
			)
		),
		
		"Border top right radius"=>array(
			"template"=>"slider",
		),
		"Border top right radius label"=>array(
			"template"=>"label",
			"data"=>array(
				"compose"=>"borderRadius",
			)
		),

		"Border bottom right radius"=>array(
			"template"=>"slider",
		),
		"Border bottom right radius label"=>array(
			"template"=>"label",
			"data"=>array(
				"compose"=>"borderRadius",
			)
		),

		"Border bottom left radius"=>array(
			"template"=>"slider",
		),
		"Border bottom left radius label"=>array(
			"template"=>"label",
			"data"=>array(
				"compose"=>"borderRadius",
			)
		),
		
		//Border Color
		"Border color"=>array(
			"template"=>"color picker",
		),
		"Border color label"=>array(
			"template"=>"label",
			"data"=>array(
				"compose"=>"border",
			)
		),
		
		//Border Style
		"Border style"=>array(
			"template"=>"select",
		),
		"Border style label"=>array(
			"template"=>"label",
			"data"=>array(
				"compose"=>"border",
			)
		),
		"Solid"=>array(
			"template"=>"option",
		),
		"Dotted"=>array(
			"template"=>"option",
		),
		"Dashed"=>array(
			"template"=>"option",
		),
		"Double"=>array(
			"template"=>"option",
		),
		
		//Background
		"Background"=>array(
			"template"=>"section",
			"class"=>"Background",
		),
		
		//Background Color
		"Background color"=>array(
			"template"=>"color picker",
		),
		
		//Background Image
		"Background image label"=>array(
			"template"=>"label",
			"data"=>array(
				"prepend"=>"url(",
				"append"=>")",
			),
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
		
		//Opacity
		"Opacity"=>array(
			"template"=>"slider",
		),
		
		//Background Position
		"Background position"=>array(
			"template"=>"select",
		),
		
		"left top"=>array(
			"template"=>"option",
			"value"=>"left top",
		),
		"left center"=>array(
			"template"=>"option",
			"value"=>"left center",
		),
		"left bottom"=>array(
			"template"=>"option",
			"value"=>"left bottom",
		),
		"right top"=>array(
			"template"=>"option",
			"value"=>"right top",
		),
		"right center"=>array(
			"template"=>"option",
			"value"=>"right center",
		),
		"right bottom"=>array(
			"template"=>"option",
			"value"=>"right bottom",
		),
		"center top"=>array(
			"template"=>"option",
			"value"=>"center top",
		),
		"center center"=>array(
			"template"=>"option",
			"value"=>"center center",
		),
		"center bottom"=>array(
			"template"=>"option",
			"value"=>"center bottom",
		),
		
		//Background repeat
		"Background repeat"=>array(
			"template"=>"select",
		),
		
		"repeat"=>array(
			"template"=>"option",
			"value"=>"repeat",
		),
		"repeat-x"=>array(
			"template"=>"option",
			"value"=>"repeat-x",
		),
		"repeat-y"=>array(
			"template"=>"option",
			"value"=>"repeat-y",
		),
		"no-repeat"=>array(
			"template"=>"option",
			"value"=>"no-repeat",
		),
		
		//Background attachment
		"Background attachment"=>array(
			"template"=>"select",
		),

		"scroll"=>array(
			"template"=>"option",
			"value"=>"scroll",
		),
		"fixed"=>array(
			"template"=>"option",
			"value"=>"fixed",
		),
		"local"=>array(
			"template"=>"option",
			"value"=>"local",
		),

		//Shadow
		"Shadow"=>array(
			"template"=>"section",
			"class"=>"Shadow",
		),
		"Shadow label"=>array(
			"template"=>"label",
			"data"=>array(
				"property"=>"boxShadow",
				"decompose"=>"boxShadow",
			)
		),

		//Shadow Color
		"Shadow color"=>array(
			"template"=>"color picker",
		),
		"Shadow color label"=>array(
			"template"=>"label",
			"data"=>array(
				"compose"=>"boxShadow",
			)
		),

		//Shadow Dimensions
		"Shadow offset x"=>array(
			"template"=>"slider",
		),		
		"Shadow offset x label"=>array(
			"template"=>"label",
			"data"=>array(
				"compose"=>"boxShadow",
			)
		),
		"Shadow offset y"=>array(
			"template"=>"slider",
		),
		"Shadow offset y label"=>array(
			"template"=>"label",
			"data"=>array(
				"compose"=>"boxShadow",
			)
		),
		"Shadow blur"=>array(
			"template"=>"slider",
		),
		"Shadow blur label"=>array(
			"template"=>"label",
			"data"=>array(
				"compose"=>"boxShadow",
			)
		),
		"Shadow radius"=>array(
			"template"=>"slider",
		),
		"Shadow radius label"=>array(
			"template"=>"label",
			"data"=>array(
				"compose"=>"boxShadow",
			)
		),
		
		"Shadow inset"=>array(
			"template"=>"select",
		),
		"Shadow inset label"=>array(
			"template"=>"label",
			"data"=>array(
				"compose"=>"boxShadow",
			)
		),
		
		//Others
		"Yes"=>array(
			"template"=>"option",
		),
		"No"=>array(
			"template"=>"option",
		),
		
		//undo setting
		"undoSetting" =>array(
			"tag"=>"i",
			"class"=>"undo_setting",
			"class no prefix"=>"fa fa-close",
			"content"=>"",
			"no_cover"=>true,
		),
		
	);

protected $iteration = 0;	
function __construct ($structure=false, $material=false, $templates=false) {
	if ($structure) $this->structure = $structure;
	if ($material) $this->material = $material;
	if ($templates) $this->templates = $templates;

	$this->material = $this->templates + $this->material;

	//vals need to be appended with px
	$px = array("Line height", "Font size", "Padding top", "Padding right", "Padding bottom", "Padding left", "Margin top", "Margin right", "Margin bottom", "Margin left", "Border top width",	"Border right width", "Border bottom width", "Border left width", "Border top left radius", "Border top right radius", "Border bottom right radius", "Border bottom left radius", "Height", "Width", "Max height", "Max width", "Min height", "Min width");
	
//	$replace = array("TopPos")
	
	//add covers in structure
	foreach ($this->structure as $key_1=>&$val_1) {
		if (is_numeric($key_1) || (isset($this->material[$key_1]) && isset($this->material[$key_1]['no_cover']))) continue;

		$new = array();
		foreach ($val_1 as $key_2=>&$val_2) {
			if (is_numeric($key_2)) {
				$key_2 = $val_2;
				$val_2 = array();
			}

			//All entries must have names like "Abc Cover"
			if (strpos($key_2,'cover') !== false) $key_2 = str_replace(' cover', '', $key_2);
			$new_key = $key_2." cover";
			
			//add label key in structure
			if (!isset($this->material[$key_2]['no label'])) {
				$label_key = $key_2." label";
				if (!in_array($label_key, array_values($val_2))) array_unshift($val_2, $label_key);
			}
			if (!in_array($key_2, array_values($val_2)) && !isset($val_2[$key_2])) $val_2[] = $key_2;

			$new[$new_key] = $val_2;

			//Defining the cover in materials
			if (!isset($this->material[$new_key])) $this->material[$new_key] = array("template"=>"p");
			
			//ensure there is a data array with a property value. Keep previous data values if found
			if(function_exists('lcfirst') === false) {
				function lcfirst($str) {
					$str[0] = strtolower($str[0]);
					return $str;
				}
			}
			$property = str_replace(" ", "-", lcfirst(str_replace(" ", "", ucwords(trim($key_2)))));
			if (isset ($this->material[$new_key]['data'])) {
				if (!isset ($this->material[$new_key]['data']['property'])) $this->material[$new_key]['data']['property'] = $property;
				$data = $this->material[$new_key]['data'];
			}
			else $data = array("property"=> $property);
			
			//add label key in materials
			if (!isset($this->material[$key_2]['no label'])) {			
				//create a label if it is not present
				if (!isset($this->material[$label_key])) $this->material[$label_key] = array("template"=>"label", "content"=>$key_2.":", "data"=>$data);
				else {
					//if label element exists, ensure there is a property
					if (!isset($this->material[$label_key]['content'])) $this->material[$label_key]['content'] = $key_2.":";
					if (!isset($this->material[$label_key]['data'])) $this->material[$label_key]['data'] = array();
					if (!isset($this->material[$label_key]['data']['property'])) $this->material[$label_key]['data']['property'] = $property;
				}

				//append px
				if (in_array($key_2, $px)) {
					$this->material[$key_2." label"]["data"]["append"]="px";
				}
			
			}
		}
		$val_1 = $new;
	}
	
	//changes to material
	foreach ($this->material as $key=>&$val) {
		//fill values and content in select's options
		if (isset($val["template"]) && ($val["template"]==="option" || $val["template"]==="checkbox")) {
			if (!isset($val["content"])) $val["content"] = $key;
			if (!isset($val["value"])) $val["value"] = strtolower($key);
		}
	}
	
}

}
?>
