<?php
/**
* This class uses a WPH_Writer object to write out the editor keys
*/	
class WPH_Frontend_Editor_Keys {

/**
* WPH_Writer object
*/	
public $writer;

/**
* textdomain
*/	
public $textdomain = "wph-editor";

public $data_val_prefix = "wph_";

public $structure =
	array(
		"keys"=>array(
			"settings",
			"copy",
			"select",
			"delete",
			"move",
		)
	);

public $material =
	array(
		"keys"=>array(
			"tag"=>"div",
			"class"=>"keys",
		),
		
		"settings"=>array(
			"tag"=>"div",
			"class"=>"editor_key_settings key",
		),
		
		"copy"=>array(
			"tag"=>"div",
			"class"=>"editor_key_copy key",
		),
		
		"select"=>array(
			"tag"=>"div",
			"class"=>"editor_key_select key",
		),
		
		"delete"=>array(
			"tag"=>"div",
			"class"=>"editor_key_delete key",
		),
		
		"move"=>array(
			"tag"=>"div",
			"class"=>"editor_key_move key",
		)
		
	);

public $icons = 
	array(
		"settings" => "cog",
		"copy"=> "files-o",
		"select"=> "check",
		"delete" => "trash",
		"move" => "arrows",
	);

public function setIcons () {
	$icon_keys = array_keys($this->icons);
	foreach ($this->material as $key=>&$val) {
		if (in_array($key, $icon_keys)) $val["class no prefix"] = " fa fa-".$this->icons[$key]." ";
	}
}
	
function __construct ($structure = false, $material = false, $icons = false) {
	if ($structure) $this->structure = $structure;
	if ($material) $this->material = $material;
	if ($icons) $this->icon = $icons;
	//set icons
	$this->setIcons($this->material);
	//init the WPH_Writer object
	$this->writer = new WPH_Writer();
	$this->writer->data_prefix = "data-wph-";
	$this->writer->class_prefix = "wph_";
	$this->writer->structure = $this->structure;
	$this->writer->material = $this->material;	
	$this->writer->textdomain = $this->textdomain;	
}

public function correct_data ($type) {
	foreach ($this->writer->material as $key=>&$val) {
		if ($key !== "keys") $val["data"] = array("trigger"=>$this->data_val_prefix.$type."_".$key);
	}
}

public function setup ($type) {
	$this->correct_data($type);//fix the data 'trigger' value
	$this->writer->material["keys"]["class"] = $this->material["keys"]["class"]." ".$type."_keys";
}

/**
* While setup fixes up the data that is to be parsed, assemble simply helps put together the parts, building for master/column/cell
*/	
function assemble ($type) {
	$this->setup($type);
	$this->writer->build();
	$html = $this->writer->html;
	$this->writer->clear();
	return $html;
}

function return_master_keys () {
	return $this->assemble("container");
}

function return_column_keys () {
	return $this->assemble("column");	
}

function return_cell_keys () {
	return $this->assemble("cell");
}

function return_keys_obj () {
	$master = $this->return_master_keys();
	$column = $this->return_column_keys();
	$cell = $this->return_cell_keys();

	$keys_obj = 
	array(
		"container" => $master,
		"column" => $column,
		"cell" => $cell
	);
	return json_encode($keys_obj);
}

}

?>