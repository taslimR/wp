<!-- retireve admin settings options  -->
<?php $color_val = get_option('bg-color'); ?>
<?php $animated_img = get_option('img-file'); ?>
<!-- Title -->
<h2> WP Preloader Settings </h2>
<hr>
<form method="post" action="options.php" enctype="multipart/form-data"> 
    <?php settings_fields( 'wppreloader_options' ); ?>
	<?php do_settings_sections( 'wppreloader_options' ); ?>
	<?php settings_fields("section"); ?>
        <table class="form-table">  
            <!-- Select Preloader Image -->
            <tr valign="top">
                <th scope="row"><label for="wppreloader_img">Preloader image</label></th>
                <td><input type="file" name="img-file" /></td>
            </tr>
            <!-- Image Preview -->
            <tr valign="top">
                <?php if ( $animated_img && isset( $animated_img ) ) { ?>
                <th></th>
                <td><img src="<?php echo $animated_img; ?>" alt="preloader image" width="50" height="50"></td>
                <?php } ?>
            </tr>
            <!-- Color Picker -->
            <tr valign="top">
                <th scope="row"><label for="wppreloader_bg_color">Background Color</label></th>
                <td><input type="text" value="<?php echo $color_val['color'];?>"  name="bg-color[color]" class="wp-preloader-color" /></td>
            </tr> 
    </table>
    <?php submit_button(); ?>
</form>
 




