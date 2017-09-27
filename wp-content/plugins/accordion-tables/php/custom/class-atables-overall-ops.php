<?php
/**
 * This class builds additional panels inside wph front end editor that are specific to the plugin.
 * It uses a WPH_Writer instance for doing so.
 
 */
class aTables_Overall_Ops extends WPH_Style_Ops_Writer{

public $textdomain = "accordion-tables";

public $structure = 
	array(
		"Overall Options"=>
			array(
				"Column width before expansion"=>
					array(
						"small input",
					),
				"Column width after expansion"=>
					array(
						"small input",
					),
				"Column width calculated as"=>
					array(
						"Column width calculated as"=>
							array(
								"Pixel",
								"Percentage",
							),
					),
				"Max columns per row"=>
					array(
						"small input",
					),
				"Animation speed"=>
					array(
						"small input",
					),
				"Sliding speed"=>
					array(
						"small input",
					),
				"Expansion type"=>
					array(
						"Expansion type"=>
							array(
								"Push",
								"Overlap",
							),
					),
				"Column shadow"=>
					array(
						"Column shadow"=>
							array(
								"Light",
								"Medium",
								"Heavy",
								"Disabled",
							),
					),
				"Column gap horizontal"=>
					array(
						"small input",
					),
				"Column gap vertical"=>
					array(
						"small input",
					),
				"Table padding horizontal"=>
					array(
						"small input",
					),
				"Table padding vertical"=>
					array(
						"small input",
					),
				/*
				"Column max-width"=>
					array(
						"small input",
					),
				"Column min-width"=>
					array(
						"small input",
					),
				*/
				/*
				"Columns per row on PC"=>
					array(
						"small input",
					),
				"Columns per row on tablet"=>
					array(
						"small input",
					),
				"Columns per row on mobile"=>
					array(
						"small input",
					),
				*/
				/* Not needed for now since all settings here support live editing
				"Apply cover"=>
					array(
						"Apply",
					),
				*/
			),
	);
	
public $material = 
	array(
		"Overall Options"=>
			array(
				"template"=>"section",
				"class"=>"aTables_overallOps liveChanges",
				"data"=>
					array(
						"execution"=>"live_changes_overall_execution",
						"default-attr-label"=>"overall-settings",
						"attr-label"=>"overall-settings",
					),
			),
		"Column width before expansion"=>
			array(
				"template"=>"slider",
				"data"=>
					array(
						"min"=>100,
						"max"=>500,
						"step"=>10,
						"val"=>150,
					),
			),
		"Column width after expansion"=>
			array(
				"template"=>"slider",
				"data"=>
					array(
						"min"=>100,
						"max"=>500,
						"step"=>10,
						"val"=>320,
					),
			),
		"Animation speed"=>
			array(
				"template"=>"slider",
				"data"=>
					array(
						"min"=>0,
						"max"=>1000,
						"step"=>25,
						"val"=>150,
					),
			),
		"Sliding speed"=>
			array(
				"template"=>"slider",
				"data"=>
					array(
						"min"=>0,
						"max"=>1000,
						"step"=>25,
						"val"=>250,
					),
			),
		"Column width calculated as"=>
			array(
				"template"=>"select",
			),
		"Pixel"=>
			array(
				"template"=>"option",
			),			
		"Percentage"=>
			array(
				"template"=>"option",
			),
		"Expansion type"=>
			array(
				"template"=>"select",
			),
		"Push"=>
			array(
				"template"=>"option",
			),
		"Overlap"=>
			array(
				"template"=>"option",
			),
		"Column shadow"=>
			array(
				"template"=>"select",
			),
		"Enabled"=>
			array(
				"template"=>"option",
			),
		"Disabled"=>
			array(
				"template"=>"option",
			),
		"Light"=>
			array(
				"template"=>"option",
			),
		"Medium"=>
			array(
				"template"=>"option",
			),
		"Heavy"=>
			array(
				"template"=>"option",
			),
		"Left" =>
			array(
				"template"=>"option",
			),
		"Right" =>
			array(
				"template"=>"option",
			),
		"Center" =>
			array(
				"template"=>"option",
			),
		"No" =>
			array(
				"template"=>"option",
			),
		"Yes" =>
			array(
				"template"=>"option",
			),
		"Column gap horizontal"=>
			array(
				"template"=>"slider",
				"data"=>
					array(
						"min"=>0,
						"max"=>100,
						"val"=>5,
					),
			),
		"Column gap vertical"=>
			array(
				"template"=>"slider",
				"data"=>
					array(
						"min"=>0,
						"max"=>100,
						"val"=>5,
					),
			),
		"Table padding horizontal"=>
			array(
				"template"=>"slider",
				"data"=>
					array(
						"min"=>0,
						"max"=>100,
						"val"=>20,
					),
			),
		"Table padding vertical"=>
			array(
				"template"=>"slider",
				"data"=>
					array(
						"min"=>0,
						"max"=>100,
						"val"=>20,
					),
			),
		"Column max-width"=>
			array(
				"template"=>"slider",
				"data"=>
					array(
						"min"=>100,
						"max"=>500,
						"step"=>10,
					),
			),
		"Column min-width"=>
			array(
				"template"=>"slider",
				"data"=>
					array(
						"min"=>50,
						"max"=>300,
						"step"=>10,
					),
			),
		"Max columns per row"=>
			array(
				"template"=>"slider",
				"data"=>
					array(
						"min"=>1,
						"max"=>15,
						"step"=>1,
						"val"=>8,
					),
			),
		"Columns per row on PC"=>
			array(
				"template"=>"slider",
				"data"=>
					array(
						"min"=>1,
						"max"=>10,
						"step"=>1,
						"val"=>4,
					),
			),
		"Columns per row on tablet"=>
			array(
				"template"=>"slider",
				"data"=>
					array(
						"min"=>1,
						"max"=>8,
						"step"=>1,
						"val"=>2,
					),
			),
		"Columns per row on mobile"=>
			array(
				"template"=>"slider",
				"data"=>
					array(
						"min"=>1,
						"max"=>5,
						"step"=>1,
						"val"=>1,
					),
			),
		"Apply cover"=>
			array(
				"tag"=>"p",
				"class"=>"p",
				"style"=>"text-align:right",
			),
		"Apply"=>
			array(
				"tag"=>"button",
				"content"=>"Apply",
				"class"=>"aTables_overallOps_button",
				"no label"=>true,
				"data"=>
					array(
						"style"=>"width:100",
					),
			)
	);

}
?>