//background-settings.php file script for upload or reset images
//================================================================
//upload button click event for background
jQuery("#upload-background").bind("click", function(e){
jQuery("#show-background").show(500);
jQuery("#background-re").show(500);
jQuery("#background-img").hide(500);
});

// display image "preview" of "background" image onclick preview
jQuery("#display-background").bind("click", function(e){
var background_url = jQuery("#background-text").val();
if(background_url=="")
{
 jQuery('#no-background-media').show(100);
 jQuery("#no-background-media").fadeOut(2000);
}
else
{
jQuery("#background-img").attr("src",background_url).toggle(500);
}
});

//display none on click none button
jQuery("#background-re").bind("click", function(e){
jQuery(this).closest('td').find('input:text').val('');
jQuery("#background-re").hide(500);
jQuery("#background-img").hide(500);
});

