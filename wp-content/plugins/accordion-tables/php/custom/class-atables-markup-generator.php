<?php
/**
* This class uses a WPH_Writer object to generate markup for Accordion Tables
*/	
class aTables_Markup_Generator {

/**
* WPH_Writer object
*/	
public $writer;

/**
* textdomain
*/	
public $textdomain = "accordion-tables";

/**
* Mostly the $structure and $material arrays will be changed by client code
*/	
public $structure =
	array (
	"outer-container"=>array(
			"table-container"=>array(
				"column"=>array(
					"column-details"=>array(
						"cover-column-title"=>array(
							"column-title",
						),
						"cover-image"=>array(
							"image"=>array(
								"img",
							),
						),
						"cover-price"=>array(
							"price"=>array(
								"prev-price",
								"current-price",
								"price-duration",
							)
						),
						"cover-cell"=>array(
							"cell"=>array(
								"cell-title"=>array(
									"cell-val",
									"cell-title-icon",
									"cell-title-text",
								),
								"cell-details",
							),
						),
						"cover-link"=>array(
							"link"=>array(
								"link-icon",
								"link-text"
							),
						),
					)
				),
			)
		)
	);
	
public $material =
	array (
		"outer-container"=>array(
			"tag"=>"div",
			"class"=>"outer_container",
		),
		"table-container"=>array(
			"tag"=>"div",
			"class"=>"container",
			"class no prefix" => "wph_container wph_key_keeper wph_column_level_sortable",
			"data"=>array(
				"key-type"=>"container",
				"plugin"=>"aTables",
				"post-id"=>"%post-id%",
			)
		),
		"column"=>array(
			"tag"=>"div",
			"class"=>"info_col",
			"class no prefix" => "wph_column wph_key_keeper",
			"data"=>array(
				"key-type"=>"column",
			),
		),
		"column-details"=>array(
			"tag"=>"div",
			"class"=>"info_col_details",
			"class no prefix" => "wph_cell_level_sortable",
		),
		"column-title"=>array(
			"tag"=>"div",
			"class"=>"column_title trigger cell",
			"content"=>"Column Title",
		),
		"image"=>array(
			"tag"=>"div",
			"class"=>"image cell",
		),
		"img"=>array(
			"tag"=>"img",
			"class"=>"trigger",
			"attr"=>array(
				"src"=>"%image%",
			),
		),
		"price"=>array(
			"tag"=>"div",
			"class"=>"price cell",
		),
		"prev-price"=>array(
			"tag"=>"div",
			"class"=>"prev_price",
			"content"=> "$60"
		),
		"current-price"=>array(
			"tag"=>"div",
			"class"=>"current_price",
			"content"=> "$49"
		),
		"price-duration"=>array(
			"tag"=>"div",
			"class"=>"price_duration",
			"content"=> "per month"
		),
		"cover-cell"=>array(
			"tag"=>"div",
			"class"=>"cover",
			"class no prefix" => "wph_cell wph_key_keeper",
			"data"=>array(
				"key-type"=>"cell",
				"element-name"=>"regular",
			)
		),
		"cover-column-title" =>array(
			"template"=>"cover-cell",
			"add"=>array(
				"data"=>array(
					"element-name"=>"column title",
				),			
			),
		),
		"cover-image" =>array(
			"template"=>"cover-cell",
			"add"=>array(
				"data"=>array(
					"element-name"=>"image",
				),
			),
		),
		"cover-price" =>array(
			"template"=>"cover-cell",
			"add"=>array(
				"data"=>array(
					"element-name"=>"price",
				),
			),
		),
		"cover-link" =>array(
			"template"=>"cover-cell",
			"add"=>array(
				"data"=>array(
					"element-name"=>"link",
				),
			),
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
			"class no prefix"=>"wph_editor_no_anim wph_editor_no_transition",
			"content"=>"Cell details lorem ipsum dolor sit lorem ipsum dolor sit lorem ipsum dolor sit lorem ipsum. Dolor sit lorem ipsum dolor sit lorem ipsum dolor sit lorem ipsum dolor sit lorem ipsum dolor sit lorem ipsum dolor sit lorem ipsum dolor sit.",
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
	

/**
* Recursively searches array for string ($needle), which may be array key or value.
* Creates as many copies of it as asked, appending _num to the name of the original.
* Pointer/loops moves on to skip those copies.
* Catches, copies all instances of the needle.
*/
function duplicate_array_items($needle, $copies, &$arr = false, $recursive = true)
{
	if ($copies < 1 || !is_numeric ($copies)) return;
	
	if (is_array($arr) && $recursive) {
		foreach ($arr as $key => &$val_) {
			if (is_array($val_)) {
				$this->duplicate_array_items($needle, $copies, $val_);
			}
		}
	}

    $arr_keys = array_keys($arr);
    for ($i = 0; $i < count($arr_keys); $arr_keys = array_keys($arr), ++$i) {
	
        $key = $arr_keys[$i];
        $val = &$arr[$arr_keys[$i]];

        if ($key !== $needle && $val !== $needle)
            continue;

		if ($val === $needle && !is_numeric($key)){//case 1
			$val = array();
			for ($i1=0; $i1<$copies; $i1++) array_push($val, $needle);
			continue;
		}

        $prepend_arr = array_slice($arr, 0, $i + 1);
        $new_data    = array();

        $i1 = $i + 1;
        for ($i2 = 1; $i2 < ($copies + 1); $i2++) {
            if ($val === $needle && is_numeric($key)) {//case 2
                $add_key = $i;
                $add_val = $needle . "-" . $i2;
            } else {//case 3
                $add_key = $needle . "-" . $i2;
                $add_val = $arr[$needle];
            }//does not work for case where key is not numeric
            $new_data[$add_key] = $add_val;
            $i++;
        }
        $append_arr = array_slice($arr, $i1, null);

        $arr = array_merge($prepend_arr, $new_data, $append_arr);
    }
}

/**
* Duplicates elements to quickly modify the $structure and $materials 
* Modifies 'structure' to accommodate request for duplicates
* Call it like so: duplicate(array("regular"=>3, "column"=>2))
* Or: duplicate("regular", 3)
* Or: duplicate("regular")
*/
function duplicate ($elm, $copies=1) {
	if (is_array($elm)) {
		foreach ($elm as $key=>$val) {
			$this->duplicate_array_items ($key, $val, $this->structure);
			$this->duplicate_array_items ($key, $val, $this->material, false);
		}
	}
	else {
		$this->duplicate_array_items ($elm, $copies, $this->structure);
		$this->duplicate_array_items ($elm, $copies, $this->material, false);
	}
	$this->init_writer ();
}

function init_writer () {
	if (!$this->writer) $this->writer = new WPH_Writer();
	$this->writer->data_prefix = "data-wph-";
	$this->writer->class_prefix = "atw_";
	$this->writer->structure = $this->structure;
	$this->writer->material = $this->material;
	$this->writer->textdomain = $this->textdomain;
}

function __construct ($structure = false, $material = false) {
	if ($structure) $this->structure = $structure;
	if ($material) $this->material = $material;
	$this->init_writer ();
}

function assemble () {
	$this->writer->build();
	$html = $this->writer->html;
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