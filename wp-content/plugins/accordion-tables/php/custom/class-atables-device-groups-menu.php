<?php
/**
 * This class builds additional panels inside wph front end editor that are specific to the plugin.
 * It uses a WPH_Writer instance for doing so.
 
 */
class aTables_Device_Groups_Menu extends WPH_Writer{
	
	public $class_prefix = 'wph_editor_';
	public $data_prefix = 'data-wph-';
	
	public $structure = array(
		'container'=> array(
				'device',
				'pc',
				'tablet',
				'mobile',
			),
	);

	public $material = array(
	
		//container
		"container" =>array(
				"tag"=>"div",
				"class" => 'device_groups_menu',
				"data"=>
					array(
						"type"=>'device-group',
					),
			),

		//label
		"device" =>array(
				"tag"=>"span",
				"content"=>"Device:",
			),
			
		//options
		'pc'=>array(
			"tag"=>"span",
			"content"=> 'PC',
			"class" => 'pc',
			"data"=>
				array(
					"type"=>'pc',
				)
			),
			
		'mobile'=>array(
			"tag"=>"span",
			"content"=> 'Mobile',
			"class" => 'mobile',
			"data"=>
				array(
					"type"=>'mobile',
				),
			),
			
		'tablet'=>array(
			"tag"=>"span",
			"content"=> 'Tablet',
			"class" => 'tablet',
			"data"=>
				array(
					"type"=>'tablet',
				),
			),
	);


}

?>