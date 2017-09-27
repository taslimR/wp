 function select_background()
{
	var background_type = jQuery("#select-background").val();
	
	if(background_type=="no-background")
	{
	jQuery("#static_color_field").fadeOut(500);
		jQuery("#static_image_field").fadeOut(500);
		jQuery("#slider_field").fadeOut(500);
		jQuery("#video_field").fadeOut(500);
		
		jQuery("#static-color").hide(500);
		jQuery("#static-img").hide(500);
		jQuery("#slider-img").hide(500);
		jQuery("#video-img").hide(500);
	}
	
	//STATIC BACKGROUND Color.............
	if(background_type=="static-background-color")
	{
		jQuery("#static_color_field").fadeIn(500);
		jQuery("#static_image_field").fadeOut(500);
		jQuery("#slider_field").fadeOut(500);
		jQuery("#video_field").fadeOut(500);
		
		jQuery("#static-color").show(500);
		jQuery("#static-img").hide(500);
		jQuery("#slider-img").hide(500);
		jQuery("#video-img").hide(500);
		
	}
	
	if(background_type=="static-background-image")
	{
	//show fields
		jQuery("#static_color_field").fadeOut(500);
		jQuery("#static_image_field").fadeIn(500);
		jQuery("#slider_field").fadeOut(500);
		jQuery("#video_field").fadeOut(500);
		
		jQuery("#static-color").hide(500);
		jQuery("#static-img").show(500);
		jQuery("#slider-img").hide(500);
		jQuery("#video-img").hide(500);
		
		
		
	}
	
	//SlIDER BACKGROUND..................
	if(background_type=="slider background")
	{
		jQuery("#static_color_field").fadeOut(500);
		jQuery("#static_image_field").fadeOut(500);
		jQuery("#slider_field").fadeIn(500);
		jQuery("#video_field").fadeOut(500);
		
		jQuery("#static-color").hide(500);
		jQuery("#static-img").hide(500);
		jQuery("#slider-img").show(500);
		jQuery("#video-img").hide(500);
	}
	
	//VIDEO BACKGROUND...................
	if(background_type=="video background")
	{
	
		jQuery("#static_color_field").fadeOut(500);
		jQuery("#static_image_field").fadeOut(500);
		jQuery("#slider_field").fadeOut(500);
		jQuery("#video_field").fadeIn(500);
		
		jQuery("#static-color").hide(500);
		jQuery("#static-img").hide(500);
		jQuery("#slider-img").hide(500);
		jQuery("#video-img").show(500);
	}
	
	
	
}
  
 // function for strech backround select
function strech_bg()
{
	
	 if(jQuery('#strech-bg').is(':checked'))
	
	{
		for(i=1;i<=3;i++)
		{
			 jQuery("#static-strech-tr"+i).hide(500);
		}
	}
	else
	{
	for(i=1;i<=3;i++)
		{
			 jQuery("#static-strech-tr"+i).show(500);
		}
	}
}

// function for slideshow select 
function  select_slideshow()
{
	var nof_slideshow = jQuery("#nof-slideshow").val();

	//first hide all
	for(i=0; i<= 9; i++){
		jQuery("#slidershow-no"+i).hide();
	}
	//then show selected
	for(i=0; i<= nof_slideshow; i++){
		jQuery("#slidershow-no"+i).show();
	}
	
}