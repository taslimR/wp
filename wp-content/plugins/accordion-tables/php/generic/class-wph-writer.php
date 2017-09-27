<?php
class WPH_Writer {
  
	/**
	* String that will contain the output
	*/	
	public $html = "";

	/**
	* Prefixes used by the build_attr method 
	*/		
	public $data_prefix = "data-wph-";
	public $class_prefix = "wph_op_";	
	
	/**
	* Text domain
	*/
	public $textdomain = "general";

	/**
	* The $structure array gives a bird's eye view of the markup structure. 
	* We need to refer to the $material array while building the markup.
	*/
	public $structure = 
		array(
		  "cover"=>array(
				"label",
				"input"
			)
		);
	
	public $material = 
		array(
			"cover"=>array(
				"tag"=>"div",
				"class"=>"cover",
				"data"=>array(
					"key1"=>"val1",
					"key2"=>"val2"
				)
			),
			"label"=>array(
				"tag"=>"label",
				"content"=>"The label goes here",
				"class"=>"label",
				"data"=>array(
					"key1"=>"val1",
				)
			),
			"input"=>array(
				"tag"=>"input",
				"class"=>"input",
				"attr"=>array(
					"placeholder"=>"The input goes here"
				),
				"data"=>array(
					"key1"=>"val1",
					"key2"=>"val2"
				)
			),
			"radio"=>array(
				"tag"=>"input",
				"class"=>"input",
				"attr"=>array(
					"name"=>"wph_name",
					"type"=>"radio",
				),
			),
		);

	function __construct ($structure = false, $material = false) {
		if ($structure) $this->structure = $structure;
		if ($material) $this->material = $material;
	}
		
	/**
	* Handy function iterates over each element (key & val) of array, passes each to callback
	*/
	function iterate (&$label, $callback, $label_type="val", &$ref_val = false) {
		if (is_array ($label)) {
			foreach ($label as $key=>&$val) {
				if (!is_numeric($key)) 
					$this->iterate($key, $callback, "key", $val);
				$this->iterate($val, $callback, "val");
			}
		} else {
			call_user_func( $callback, array( $label, $label_type, $ref_val ) );
		}
	}

	/**
	* Build HTML attribute string for individual elements
	*/
	function build_attr ($arr) {
		$attr ="";
		//class
		$classes = "";
		if (!empty($arr["class"])) {
			$classes_arr = explode(" ", $arr["class"]);
			foreach ($classes_arr as $class) {
				if ($class === " ") continue;
				$classes.= $this->class_prefix.$class." ";
			}
		}
		if (!empty($arr["class no prefix"])) $classes.= " ".$arr["class no prefix"];
		if ($classes == true) {
			$classes = trim($classes);
			$attr.= " class='{$classes}' ";
		}

		//data
		if(!empty($arr["data"])) {
			foreach ($arr["data"] as $key=>$val) {
				$attr .= " {$this->data_prefix}{$key}=\"{$val}\" ";
			}
		}
		
		//common attrs
		$common_attrs = array ("style", "src", "value", "type", "name", "id", "placeholder", "for");
		foreach ($common_attrs as $item) {
			if(!empty($arr[$item])) $attr .= " $item=\"{$arr[$item]}\" ";
		}
		
		//other attrs
		if(!empty($arr["attr"])) {
			foreach ($arr["attr"] as $key=>$val) {
				$attr .= " {$key}=\"{$val}\" ";
			}
		}
		return $attr;
	}

	/**
	* Build individual elements, prepare the opening and closing wrapper sub-parts
	*/
	function build_element (&$arr) {
		$wrapper = array ("open"=>"", "close"=>"");

		if (!empty($arr)) {
			//Templating
			if (!empty($arr["template"])) {
				$template_arr = $this->material[$arr["template"]];
				
				if (!empty($arr["remove"])) {
					foreach ($arr["remove"] as $key=>$val) {
						if (!empty($template_arr[$key])) {
							if (is_array($val)) {
								unset($template_arr[$key]);
							} else {
								$template_arr[$key] = str_replace($val, "", $template_arr[$key]);
							}
						}
					}
				}
				if (!empty($arr["add"])) {
					foreach ($arr["add"] as $key=>$val) {
						if (!empty($template_arr[$key])) {
							if (is_array($val)) {
								$template_arr[$key] = array_merge($template_arr[$key], $val);
							} else {
								$template_arr[$key] = $template_arr[$key].$val;
							}
						}
					}
				}
				
				//overwrite all keys after these changes
				foreach ($arr as $key=>$val) $template_arr[$key] = $val;

				$arr = $template_arr;
			}

			$attr = $this->build_attr($arr);
			$content = isset($arr["content"])? $arr["content"]: "";
			//translation
			if (isset($arr['tag']) && trim($content) !== "" && $this->textdomain !== "general" && ($arr['tag'] === "label" || $arr['tag'] === "span" || $arr['tag'] === "option")) {
				
				global $wph_editor_translation_array_labels;
				$wph_translations = &$wph_editor_translation_array_labels[$this->textdomain];

				//note to self: enable the following section when collecting translation strings
				//disable this -- start
				//$wph_translations[$content] = "__('".$content."' , '$this->textdomain')";
				//disable this -- end

				//note to self: disable the following section when collecting translation strings
				//disable this -- start
				if (isset($wph_translations) && isset($wph_translations[$content])) {
					$content = $wph_translations[$content];
				}
				//disable this -- end
			}
			if (empty($arr['tag'])) $arr['tag'] = "div";
			$wrapper["open"] = "<".$arr['tag'].$attr.">".$content;
			$non_closing = array("input", "img");
			$wrapper['close'] = (in_array($arr["tag"], $non_closing))? "" : "</{$arr['tag']}>";
		}
		return $wrapper;
	}

	/**
	* Builds the HTML string based on structure and material
	*/
	function build ($val=false, $key=false) {
		if (!$val) $val = $this->structure;
		
		//getting $wrapper variable...
		if (is_array($val))	{
			$build_with = false; //This case occurs in first iteration where $structure is $val
			if (isset($this->material[$key])) $build_with = &$this->material[$key];
		} else {
			//in case of individual elements, the $val, not $key (numeric) will point to $material index.
			$build_with = false;
			if (isset($this->material[$val])) $build_with = &$this->material[$val];
		}
		$wrapper = $this->build_element($build_with);
		
		//wrapping
		if (is_array($val))	{
			$this->html .= $wrapper["open"];
			foreach($val as $sub_key=>$sub_val) {
			  $this->html .= $this->build($sub_val, $sub_key);
			}
			$this->html .= $wrapper["close"];
		}
		else {
			$this->html.= $wrapper["open"].$wrapper["close"];
		}
	}

	function write ($val=false, $key=false) {
		$this->build($val=false, $key=false);
		echo $this->html;
	}
	
	function flush_html( ){
		$html = $this->html;
		$this->html = "";
		return $html;
	}
	
	function clear () {
		$this->html = "";
	}

}

?>