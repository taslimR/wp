<?php
// retireve admin settings options 
$bg_color_code = get_option('bg-color');
$animated_img = get_option('img-file');

// set animated image from plugin settings
if(isset($animated_img) && !empty($animated_img))
	$animated_img = "<style> #wp-preloader-animation { background-image: url($animated_img);  } </style>";
else 
	$animated_img = "";

// set background color from plugin settings
if($bg_color_code) 
	$bg_color_code = "<style> #wp-preloader-container { background-color: $bg_color_code[color]; } </style>";

$html_content = <<<WPreloader
<div id="wp-preloader-container">
  <div id="wp-preloader-animation">&nbsp;</div>
</div>
$animated_img
$bg_color_code
WPreloader;

echo $html_content;
?>

