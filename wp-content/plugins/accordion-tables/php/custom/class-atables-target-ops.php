<?php
/**
 * This class builds additional panels inside wph front end editor that are specific to the plugin.
 * It uses a WPH_Writer instance for doing so.
 */
class aTables_Target_Ops extends WPH_Style_Ops_Writer{

public $textdomain = "accordion-tables";

public $structure = 
	array(
		"Target Options"=>
			array(
				"Columns"=>
					array(
						"Columns"=>
							array(
								"All",
								"First",
								"Last",
								"Even",
								"Odd",
								"Custom",
								"None",
							),
					),
				"Custom columns"	=>
					array(
						"input",
					),
				"Rows"=>
					array(
						"Rows"=>
							array(
								"All",
								"First",
								"Last",
								"Even",
								"Odd",
								"Custom",
							),
					),
				"Custom rows"=>
					array(
						"input",
					),
				"Cell type"=>
					array(
						"Cell type"=>
							array(
								"All",
								"Regular",
								"Column Title",
								"Image",
								"Price",
								"Link",
								"Multiple",
							),
					),
				"Multiple cell types"=>
					array(
						"input",
					),
			)
	);
	
public $material = 
	array(
		"Target Options"=>
			array(
				"template"=>"section",
				"class"=>"aTables_targetOps liveChanges",
				"data"=>
					array(
						"execution"=>"live_changes_aTables_target_execution",
					),
			),
		"Columns"=>
			array(
				"tag"=>"select",
			),
		"Custom columns cover"=>
			array(
				"template"=>"p",
				"add"=>
					array(
						"class" => " hide",
					),
			),
		"Rows"=>
			array(
				"tag"=>"select",
			),
		"Custom rows cover"=>
			array(
				"template"=>"p",
				"add"=>
					array(
						"class" => " hide",
					),
			),
		"Cell type"=>
			array(
				"tag"=>"select",
			),
		"Multiple cell types cover"=>
			array(
				"template"=>"p",
				"add"=>
					array(
						"class" => " hide",
					),
			),
		"All"=>
			array(
				"template"=>"option",
			),
		"First"=>
			array(
				"template"=>"option",
			),
		"Last"=>
			array(
				"template"=>"option",
			),
		"Even"=>
			array(
				"template"=>"option",
			),
		"Odd"=>
			array(
				"template"=>"option",
			),
		"Custom"=>
			array(
				"template"=>"option",
			),
		"Regular"=>
			array(
				"template"=>"option",
			),
		"Column Title"=>
			array(
				"template"=>"option",
			),
		"Image"=>
			array(
				"template"=>"option",
			),
		"Price"=>
			array(
				"template"=>"option",
			),
		"Link"=>
			array(
				"template"=>"option",
			),
		"Multiple"=>
			array(
				"template"=>"option",
			),
	);

}
?>