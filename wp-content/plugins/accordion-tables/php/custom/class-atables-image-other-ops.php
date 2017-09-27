<?php
/**
 * This class builds additional panels inside wph front end editor that are specific to the plugin.
 * It uses a WPH_Writer instance for doing so.
 
 */
class aTables_Image_Other_Ops extends WPH_Style_Ops_Writer{

public $textdomain = "accordion-tables";

public $structure = 
	array(
		"Other Options"=>
			array(
				"Image centering style"=>
					array(
						"Image centering style"=>
							array(
								"Outside",
								"Inside",
							),
					),
				"Image title"=>
					array(
						"input"
					),
			),
	);
	
public $material = 
	array(
		"Other Options"=>
			array(
				"template"=>"section",
				"class"=>"aTables_imageOtherOps liveChanges",
				"data"=>
					array(
						"execution"=>"live_changes_plugin_execution",
						"attr-label"=>"other-settings",
					),
			),
		"Image centering style"=>
			array(
				"tag"=>"select",
			),
		"Outside"=>
			array(
				"template"=>"option",
			),
		"Inside"=>
			array(
				"template"=>"option",
			),
	);

}
?>