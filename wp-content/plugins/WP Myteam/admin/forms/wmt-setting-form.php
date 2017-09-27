<?php
/**
 * Click Price Table
 * @since 1.0.0
 */

// Exit if accessed directly 
if ( !defined( 'ABSPATH' ) ) exit;
?>
<!-- . begining of wrap -->
<div class="wrap">
  <!-- beginning of the plugin options form -->
    <?php
    global $post;
    // Get Post Meta
    $wmt_options = get_post_meta( $post->ID, 'wmt_myteam', true );

	$wmt_title           = isset( $wmt_options['wmt_title'] ) ? $wmt_options['wmt_title'] : "";
	$wmt_columns         = isset( $wmt_options['wmt_columns'] ) ? $wmt_options['wmt_columns'] : "";
	$wmt_type            = isset( $wmt_options['wmt_type'] ) ? $wmt_options['wmt_type'] : "";
	$wmt_booknow         = isset( $wmt_options['wmt_booknow'] ) ? $wmt_options['wmt_booknow'] : "";
	$wmt_color           = isset( $wmt_options['wmt_color'] ) ? $wmt_options['wmt_color'] : "";
  $wmt_style           = isset( $wmt_options['wmt_style'] ) ? $wmt_options['wmt_style'] : "";
  $wmt_support           = isset( $wmt_options['wmt_support'] ) ? $wmt_options['wmt_support'] : "";
	$wmt_bg_image           = isset( $wmt_options['wmt_bg_image'] ) ? $wmt_options['wmt_bg_image'] : "";
	    ?>
    <?php wp_nonce_field( 'wmt_nonce_action', 'wmt_nonce_field' ); ?>
    <!-- beginning of the settings meta box --> 
    <table class="form-table">
        <tr>
          <th class="row"><?php _e( 'Team Section Heading', 'wpwmt'); ?></th>
          <td><input type="text" name="wmt_option[wmt_title]" value="<?php echo esc_attr( $wmt_title ); ?>"><br>
          <p class="description"><?php _e( 'Please enter Team Section heading.', 'wpwmt' ) ?></p></td>
        </tr>
        <tr>
          <th class="row"><?php _e( 'Display Type', 'wpwmt'); ?></th>
          <td>
              <select name="wmt_option[wmt_type]">
                  <option value="b" <?php selected( $wmt_type, 'b' ); ?>><?php _e( 'Box','wpwmt' ); ?></option>
                  <option value="fw" <?php selected( $wmt_type, 'fw' ); ?>><?php _e( 'Full width','wpwmt' ); ?></option>
              </select>
              <br>
              <p class="description"><?php _e( 'Select display type box or full width. ( Default : Box )', 'wpwmt' ) ?></p>
          </td>
        </tr>
        <tr>
          <th class="row"><?php _e( 'Select Style Color', 'wpwmt'); ?></th>
          <td><select name="wmt_option[wmt_color]">
                  <option value=""><?php _e( 'Blue', 'wpwmt' ); ?></option>
                  <option value="burnt_orange" <?php selected( $wmt_color, 'burnt_orange' ) ?>><?php _e( 'Burnt Orange', 'wpwmt' ); ?></option>
                  <option value="dark_blue" <?php selected( $wmt_color, 'dark_blue' ) ?>><?php _e( 'Dark Blue', 'wpwmt' ) ?></option>
                  <option value="green" <?php selected( $wmt_color, 'green' ) ?>><?php _e( 'Green', 'wpwmt' ) ?></option>
                  <option value="light_yellow" <?php selected( $wmt_color, 'light_yellow' ) ?>><?php _e( 'Light Yellow', 'wpwmt' ) ?></option>
                  <option value="ny_pink" <?php selected( $wmt_color, 'ny_pink' ) ?>><?php _e( 'Ny Pink', 'wpwmt' ) ?></option>
                  <option value="orange" <?php selected( $wmt_color, 'orange' ) ?>><?php _e( 'Orange', 'wpwmt' ) ?></option>
                  <option value="piction_blue" <?php selected( $wmt_color, 'piction_blue' ) ?>><?php _e( 'Piction Blue', 'wpwmt' ) ?></option>
                  <option value="purple" <?php selected( $wmt_color, 'purple' ) ?>><?php _e( 'Purple', 'wpwmt' ) ?></option>
                  <option value="red" <?php selected( $wmt_color, 'red' ) ?>><?php _e( 'Red', 'wpwmt' ) ?></option>
                  <option value="rose" <?php selected( $wmt_color, 'rose' ) ?>><?php _e( 'Rose', 'wpwmt' ) ?></option>
                  <option value="shamrock_green" <?php selected( $wmt_color, 'shamrock_green' ) ?>><?php _e( 'Shamrock Green', 'wpwmt' ) ?></option>
                  <option value="wistful" <?php selected( $wmt_color, 'wistful' ) ?>><?php _e( 'Wistful', 'wpwmt' ) ?></option>
              </select>
              <br>
          <p class="description"><?php _e( 'Please Select Style Color','wpwmt' ); ?></p></td>
        </tr>
        
        <tr>
          <th class="row"><?php _e( 'Select Style', 'wpwmt'); ?></th>
          <td><select name="wmt_option[wmt_style]" class="cust-style">
                  <option value=""><?php _e( 'Select style', 'wpwmt' ); ?></option>
                  <option value="1" <?php selected( $wmt_style, '1' ) ?>><?php _e( 'Style 1', 'wpwmt' ) ?></option>
                  <option value="2" <?php selected( $wmt_style, '2' ) ?>><?php _e( 'Style 2', 'wpwmt' ) ?></option>
                  <option value="3" <?php selected( $wmt_style, '3' ) ?>><?php _e( 'Style 3', 'wpwmt' ) ?></option>
                  <option value="4" <?php selected( $wmt_style, '4' ) ?>><?php _e( 'Style 4', 'wpwmt' ) ?></option>
                  <option value="5" <?php selected( $wmt_style, '5' ) ?>><?php _e( 'Style 5', 'wpwmt' ) ?></option>
                  <option value="6" <?php selected( $wmt_style, '6' ) ?>><?php _e( 'Style 6', 'wpwmt' ) ?></option>
                  <option value="7" <?php selected( $wmt_style, '7' ) ?>><?php _e( 'Style 7', 'wpwmt' ) ?></option>
                  <option value="8" <?php selected( $wmt_style, '8' ) ?>><?php _e( 'Style 8', 'wpwmt' ) ?></option>
                  <option value="9" <?php selected( $wmt_style, '9' ) ?>><?php _e( 'Style 9', 'wpwmt' ) ?></option>
                  <option value="10" <?php selected( $wmt_style, '10' ) ?>><?php _e( 'Style 10', 'wpwmt' ) ?></option>
                  <option value="11" <?php selected( $wmt_style, '11' ) ?>><?php _e( 'Style 11', 'wpwmt' ) ?></option>
                  <option value="12" <?php selected( $wmt_style, '12' ) ?>><?php _e( 'Style 12', 'wpwmt' ) ?></option>
                  <option value="13" <?php selected( $wmt_style, '13' ) ?>><?php _e( 'Style 13', 'wpwmt' ) ?></option>
                  <option value="14" <?php selected( $wmt_style, '14' ) ?>><?php _e( 'Style 14', 'wpwmt' ) ?></option>
                  <option value="15" <?php selected( $wmt_style, '15' ) ?>><?php _e( 'Style 15', 'wpwmt' ) ?></option>
                  <option value="16" <?php selected( $wmt_style, '16' ) ?>><?php _e( 'Style 16', 'wpwmt' ) ?></option>
                  <option value="17" <?php selected( $wmt_style, '17' ) ?>><?php _e( 'Style 17', 'wpwmt' ) ?></option>
                  <option value="18" <?php selected( $wmt_style, '18' ) ?>><?php _e( 'Style 18', 'wpwmt' ) ?></option>
                  <option value="19" <?php selected( $wmt_style, '19' ) ?>><?php _e( 'Style 19', 'wpwmt' ) ?></option>
                  <option value="20" <?php selected( $wmt_style, '20' ) ?>><?php _e( 'Style 20', 'wpwmt' ) ?></option>
                  <option value="21" <?php selected( $wmt_style, '21' ) ?>><?php _e( 'Style 21', 'wpwmt' ) ?></option>
                  <option value="22" <?php selected( $wmt_style, '22' ) ?>><?php _e( 'Style 22', 'wpwmt' ) ?></option>
                  <option value="23" <?php selected( $wmt_style, '23' ) ?>><?php _e( 'Style 23', 'wpwmt' ) ?></option>
                  <option value="24" <?php selected( $wmt_style, '24' ) ?>><?php _e( 'Style 24', 'wpwmt' ) ?></option>
                  <option value="25" <?php selected( $wmt_style, '25' ) ?>><?php _e( 'Style 25', 'wpwmt' ) ?></option>
                  <option value="26" <?php selected( $wmt_style, '26' ) ?>><?php _e( 'Style 26', 'wpwmt' ) ?></option>
                  <option value="27" <?php selected( $wmt_style, '27' ) ?>><?php _e( 'Style 27', 'wpwmt' ) ?></option>
                  <option value="28" <?php selected( $wmt_style, '28' ) ?>><?php _e( 'Style 28', 'wpwmt' ) ?></option>
                  <option value="29" <?php selected( $wmt_style, '29' ) ?>><?php _e( 'Style 29', 'wpwmt' ) ?></option>
                  <option value="30" <?php selected( $wmt_style, '30' ) ?>><?php _e( 'Style 30', 'wpwmt' ) ?></option>
                  <option value="31" <?php selected( $wmt_style, '31' ) ?>><?php _e( 'Style 31', 'wpwmt' ) ?></option>
                  <option value="32" <?php selected( $wmt_style, '32' ) ?>><?php _e( 'Style 32', 'wpwmt' ) ?></option>
                  <option value="33" <?php selected( $wmt_style, '33' ) ?>><?php _e( 'Style 33', 'wpwmt' ) ?></option>
                  <option value="34" <?php selected( $wmt_style, '34' ) ?>><?php _e( 'Style 34', 'wpwmt' ) ?></option>
                  <option value="35" <?php selected( $wmt_style, '35' ) ?>><?php _e( 'Style 35', 'wpwmt' ) ?></option>
                  <option value="36" <?php selected( $wmt_style, '36' ) ?>><?php _e( 'Style 36', 'wpwmt' ) ?></option>
                  <option value="37" <?php selected( $wmt_style, '37' ) ?>><?php _e( 'Style 37', 'wpwmt' ) ?></option>
                  <option value="38" <?php selected( $wmt_style, '38' ) ?>><?php _e( 'Style 38', 'wpwmt' ) ?></option>
                  <option value="39" <?php selected( $wmt_style, '39' ) ?>><?php _e( 'Style 39', 'wpwmt' ) ?></option>
                  <option value="40" <?php selected( $wmt_style, '40' ) ?>><?php _e( 'Style 40', 'wpwmt' ) ?></option>
                  <option value="41" <?php selected( $wmt_style, '41' ) ?>><?php _e( 'Style 41', 'wpwmt' ) ?></option>
                  <option value="42" <?php selected( $wmt_style, '42' ) ?>><?php _e( 'Style 42', 'wpwmt' ) ?></option>
                  <option value="43" <?php selected( $wmt_style, '43' ) ?>><?php _e( 'Style 43', 'wpwmt' ) ?></option>
                  <option value="44" <?php selected( $wmt_style, '44' ) ?>><?php _e( 'Style 44', 'wpwmt' ) ?></option>
                  <option value="45" <?php selected( $wmt_style, '45' ) ?>><?php _e( 'Style 45', 'wpwmt' ) ?></option>
                  <option value="46" <?php selected( $wmt_style, '46' ) ?>><?php _e( 'Style 46', 'wpwmt' ) ?></option>
                  <option value="47" <?php selected( $wmt_style, '47' ) ?>><?php _e( 'Style 47', 'wpwmt' ) ?></option>
                  <option value="48" <?php selected( $wmt_style, '48' ) ?>><?php _e( 'Style 48', 'wpwmt' ) ?></option>
                  <option value="49" <?php selected( $wmt_style, '49' ) ?>><?php _e( 'Style 49', 'wpwmt' ) ?></option>
                  <option value="50" <?php selected( $wmt_style, '50' ) ?>><?php _e( 'Style 50', 'wpwmt' ) ?></option>
              </select>
              <br>
          <p class="description"><?php _e( 'Please select different team member style', 'wpwmt' ) ?></p></td>
        </tr>
        <?php
          if( !empty( $wmt_bg_image ) ) { //check connect button image
          $show_img_bg_image = ' <img src="'.$wmt_bg_image.'" alt="'.__('Image','wpwmt').'" />';
          } else {
          $show_img_bg_image = '';
          }
          ?>
        <tr>
          <th class="row"><?php _e( 'Style Background Image', 'wpwmt'); ?></th>
          <td>
              <input class="regular-text" type="text" id="wmt-settings-image" name="wmt_option[wmt_bg_image]" value="<?php echo $wmt_bg_image; ?>" size="63" />
              <input type="button" class="button-secondary wmt-img-uploader-bg-image" id="wmt-img-btn" name="wmt_img" value="<?php echo __( 'Choose image.', 'wpwmt' ) ?>"><br />
              <span class="description"><?php echo __( 'Choose image.', 'wpwmt' ) ?></span>
              <div id="wmt-setting-image-view"><?php echo $show_img_bg_image ?></div>
              <br>
              <p class="description"><?php _e( 'Please select Background Image For Parallax Scrolling', 'wpwmt' ); ?></p>
          </td>
        </tr>
        <tr>
          <th class="row"><?php _e( 'Check if theme supporting bootstrap?', 'wpwmt' ); ?></th>
          <td>
            <input type="checkbox" name="wmt_option[wmt_support]" <?php checked( $wmt_support, 1 ); ?> value="1" />
            <br>
            <p class="description"><?php _e( 'check this opiton when bootstrap support available in your theme.'); ?></p>
          </td>
        </tr>
        <tr>
          <th class="row"><?php _e( 'Select Number of Columns', 'wpwmt'); ?></th>
          <?php $colarr = array( 17, 18, 19, 20, 21, 22, 23, 24, 31, 40 );
                $span_text = "";
                $disableselect = "";
                if( in_array( $wmt_style, $colarr ) ){
                  $wmt_columns = 4;
                  $span_text = '<span class="tmember">Style has 4 Team members</span>';
                  $disableselect = 'disabled="disabled"';
                }
          ?>
          <td><select name="wmt_option[wmt_columns]" class="number-column" <?php echo $disableselect; ?>>
                  <option value="2" <?php selected( $wmt_columns, 2 ); ?>>2</option>
                  <option value="3" <?php selected( $wmt_columns, 3 ); ?>>3</option>
                  <option value="4" <?php selected( $wmt_columns, 4 ); ?>>4</option>
              </select>
              <?php echo $span_text; ?>
              <br>
          <p class="description"><?php _e( 'Select Number of Columns from 2,3,4. ( Default : 2 )', 'wpwmt' ) ?></p></td>
        </tr>
        <tr>
          <th class="row"><?php _e( 'Team Member\'s detail', 'wpwmt' ); ?></th>
          <td>
            <div class="price-panel">
              <div class="price-panel-header"><?php _e( 'Team Members', 'wpwmt' ); ?></div>
              <div class="price-panel-tab">
                <ul class="wmt-tab">
                  <li class="active" data-tab="nav-1"><?php _e( 'Member 1', 'wpwmt' ); ?></li>
                  <li data-tab="nav-2"><?php _e( 'Member 2', 'wpwmt' ); ?></li>
                  <li data-tab="nav-3"><?php _e( 'Member 3', 'wpwmt' ); ?></li>
                  <li data-tab="nav-4"><?php _e( 'Member 4', 'wpwmt' ); ?></li>
                </ul>
              </div>
                   
				<div class="price-panel-body inside">
				<?php for( $i = 1; $i < 5; $i++ ): ?>
				<?php
            $name = isset( $wmt_options['name_'.$i] ) ? $wmt_options['name_'.$i] : "";
            $email = isset( $wmt_options['email_'.$i] ) ? $wmt_options['email_'.$i] : "";
            $facebook = isset( $wmt_options['facebook_'.$i] ) ? $wmt_options['facebook_'.$i] : "";
            $twitter = isset( $wmt_options['twitter_'.$i] ) ? $wmt_options['twitter_'.$i] : "";
            $linkedin = isset( $wmt_options['linkedin_'.$i] ) ? $wmt_options['linkedin_'.$i] : "";
            $designation = isset( $wmt_options['designation_'.$i] ) ? $wmt_options['designation_'.$i] : "";
            $detail = isset( $wmt_options['detail_'.$i] ) ? $wmt_options['detail_'.$i] : "";
						$image = isset( $wmt_options['image_'.$i] ) ? $wmt_options['image_'.$i] : "";
            

						
						$class = "";
						if( $i == 1)
						$class = 'active';
					?>
					<div id="nav-<?php echo $i; ?>" class="<?php $class; ?>">
					    <table>
					      <tbody>
                  <tr>
                    <th class="row"><?php _e( 'Name', 'wpwmt' );  ?></th>
                    <td><input type="text" name="wmt_option[name_<?php echo $i; ?>]" value="<?php echo esc_attr( $name ); ?>"><br>
                    <p class="description"><?php _e( 'Please enter Name', 'wpwmt' ); ?></p></td>
                  </tr>
					        <tr>
					          <th class="row"><?php _e( 'Designation', 'wpwmt' );  ?></th>
					          <td><input type="text" name="wmt_option[designation_<?php echo $i; ?>]" value="<?php echo esc_attr( $designation ); ?>"><br>
					          <p class="description"><?php _e( 'Please enter Designation', 'wpwmt' ); ?></p></td>
					        </tr>
                  <?php
                    if( !empty( $image ) ) { //check connect button image
                      $show_img_connect = ' <img src="'.$image.'" alt="'.__('Image','wpwmt').'" />';
                    } else {
                      $show_img_connect = '';
                    }
                    ?>
                  <tr>
                    <th class="row"><?php _e( 'Team Member Image', 'wpwmt' );  ?></th>
                    <td>
                        <input class="regular-text" type="text" id="wmt-settings-image" name="wmt_option[image_<?php echo $i; ?>]" value="<?php echo $image; ?>" size="63" />
                        <input type="button" class="button-secondary wmt-img-uploader-<?php echo $i; ?>" id="wmt-img-btn" name="wmt_img" value="<?php echo __( 'Choose image.', 'wpwmt' ) ?>"><br />
                        <span class="description"><?php echo __( 'Choose image.', 'wpwmt' ) ?></span>
                        <div id="wmt-setting-image-view"><?php echo $show_img_connect ?></div>
                    <br>
                    <p class="description"><?php _e( 'Please select subscription', 'wpwmt' ); ?></p></td>
                  </tr>
                  <tr>
                    <th class="row"><?php _e( 'Description', 'wpwmt' );  ?></th>
                    <td><input type="text" name="wmt_option[detail_<?php echo $i; ?>]" value="<?php echo esc_attr( $detail ); ?>"><br>
                    <p class="description"><?php _e( 'Please enter Description', 'wpwmt' ); ?></p></td>
                  </tr>
                  <?php 
                  $social_icon = isset( $wmt_options['icon_'.$i] ) ? $wmt_options['icon_'.$i] : array();
                  $social_url = isset( $wmt_options['social_url_'.$i] ) ? $wmt_options['social_url_'.$i] : array();
                  if( !empty( $social_icon ) && is_array( $social_icon ) ):   
                  	
                  foreach( $social_icon as $key=>$icon ): ?>
                   <tr class="wmt-social-wrapper">
                    <th><?php _e( 'Social Icon', 'wpwmt' ); ?></th>
                    <td><input type="text" placeholder="<?php _e( 'Please Enter Social URL.','wpwmt' ); ?>" name="wmt_option[social_url_<?php echo $i; ?>][]" value="<?php echo $social_url[$key]; ?>">
                    <input placeholder="<?php _e('Please choose social icon.','wpwmt'); ?>" type="text" name="wmt_option[icon_<?php echo $i; ?>][]" value="<?php echo $social_icon[$key]; ?>">
                    <button class="wmt_icon_choose button"><?php _e('Choose Icon', 'wpwmt') ?></button></td><td><i class="fa fa-minus"></i><i class="fa fa-plus"></i></td>
                  </tr>
                  <?php endforeach; 
                    else: ?>
                    <tr class="wmt-social-wrapper">
                      <th><?php _e( 'Social Icon', 'wpwmt' ); ?></th>
                      <td><input type="text" placeholder="<?php _e( 'Please Enter Social URL.','wpwmt' ); ?>" name="wmt_option[social_url_<?php echo $i; ?>][]" value="">
                      <input placeholder="<?php _e('Please choose social icon.','wpwmt'); ?>" type="text" name="wmt_option[icon_<?php echo $i; ?>][]" value="">
                      <button class="wmt_icon_choose button"><?php _e('Choose Icon', 'wpwmt') ?></button></td><td><i class="fa fa-minus"></i><i class="fa fa-plus"></i></td>
                    </tr>
                    <?php 
                  endif;   

                  ?>
					      </tbody>
					    </table>
					</div>
				<?php endfor; ?>
              </div>
            </div>
          </td>
        </tr>
    </table>
  <!-- end of the settings meta box -->   
</div><!-- .end of wrap -->

<div class="iconpicker">
    <span class="fa fa-android"></span>
    <span class="fa fa-angellist"></span>
    <span class="fa fa-apple"></span>
    <span class="fa fa-behance"></span>
    <span class="fa fa-behance-square"></span>
    <span class="fa fa-bitbucket"></span>
    <span class="fa fa-bitbucket-square"></span>
    <span class="fa fa-bitcoin"></span>
    <span class="fa fa-black-tie"></span>
    <span class="fa fa-btc"></span>
    <span class="fa fa-buysellads"></span>
    <span class="fa fa-cc-amex"></span>
    <span class="fa fa-cc-diners-club"></span>
    <span class="fa fa-cc-discover"></span>
    <span class="fa fa-cc-jcb"></span>
    <span class="fa fa-cc-mastercard"></span>
    <span class="fa fa-cc-paypal"></span>
    <span class="fa fa-cc-stripe"></span>
    <span class="fa fa-cc-visa"></span>
    <span class="fa fa-chrome"></span>
    <span class="fa fa-codepen"></span>
    <span class="fa fa-connectdevelop"></span>
    <span class="fa fa-contao"></span>
    <span class="fa fa-css3"></span>
    <span class="fa fa-dashcube"></span>
    <span class="fa fa-delicious"></span>
    <span class="fa fa-deviantart"></span>
    <span class="fa fa-digg"></span>
    <span class="fa fa-dribbble"></span>
    <span class="fa fa-dropbox"></span>
    <span class="fa fa-drupal"></span>
    <span class="fa fa-empire"></span>
    <span class="fa fa-expeditedssl"></span>
    <span class="fa fa-fa"></span>
    <span class="fa fa-facebook"></span>
    <span class="fa fa-facebook-f"></span>
    <span class="fa fa-facebook-official"></span>
    <span class="fa fa-facebook-square"></span>
    <span class="fa fa-firefox"></span>
    <span class="fa fa-flickr"></span>
    <span class="fa fa-fonticons"></span>
    <span class="fa fa-forumbee"></span>
    <span class="fa fa-foursquare"></span>
    <span class="fa fa-ge"></span>
    <span class="fa fa-get-pocket"></span>
    <span class="fa fa-gg"></span>
    <span class="fa fa-gg-circle"></span>
    <span class="fa fa-git"></span>
    <span class="fa fa-git-square"></span>
    <span class="fa fa-github"></span>
    <span class="fa fa-github-alt"></span>
    <span class="fa fa-github-square"></span>
    <span class="fa fa-gittip"></span>
    <span class="fa fa-google"></span>
    <span class="fa fa-google-plus"></span>
    <span class="fa fa-google-plus-square"></span>
    <span class="fa fa-google-wallet"></span>
    <span class="fa fa-gratipay"></span>
    <span class="fa fa-hacker-news"></span>
    <span class="fa fa-houzz"></span>
    <span class="fa fa-html5"></span>
    <span class="fa fa-instagram"></span>
    <span class="fa fa-internet-explorer"></span>
    <span class="fa fa-ioxhost"></span>
    <span class="fa fa-joomla"></span>
    <span class="fa fa-jsfiddle"></span>
    <span class="fa fa-lastfm"></span>
    <span class="fa fa-lastfm-square"></span>
    <span class="fa fa-leanpub"></span>
    <span class="fa fa-linkedin"></span>
    <span class="fa fa-linkedin-square"></span>
    <span class="fa fa-linux"></span>
    <span class="fa fa-maxcdn"></span>
    <span class="fa fa-meanpath"></span>
    <span class="fa fa-medium"></span>
    <span class="fa fa-odnoklassniki"></span>
    <span class="fa fa-odnoklassniki-square"></span>
    <span class="fa fa-opencart"></span>
    <span class="fa fa-openid"></span>
    <span class="fa fa-opera"></span>
    <span class="fa fa-optin-monster"></span>
    <span class="fa fa-pagelines"></span>
    <span class="fa fa-paypal"></span>
    <span class="fa fa-pied-piper"></span>
    <span class="fa fa-pied-piper-alt"></span>
    <span class="fa fa-pinterest"></span>
    <span class="fa fa-pinterest-p"></span>
    <span class="fa fa-pinterest-square"></span>
    <span class="fa fa-qq"></span>
    <span class="fa fa-ra"></span>
    <span class="fa fa-rebel"></span>
    <span class="fa fa-reddit"></span>
    <span class="fa fa-reddit-square"></span>
    <span class="fa fa-renren"></span>
    <span class="fa fa-safari"></span>
    <span class="fa fa-sellsy"></span>
    <span class="fa fa-share-alt"></span>
    <span class="fa fa-share-alt-square"></span>
    <span class="fa fa-shirtsinbulk"></span>
    <span class="fa fa-simplybuilt"></span>
    <span class="fa fa-skyatlas"></span>
    <span class="fa fa-skype"></span>
    <span class="fa fa-slack"></span>
    <span class="fa fa-slideshare"></span>
    <span class="fa fa-soundcloud"></span>
    <span class="fa fa-spotify"></span>
    <span class="fa fa-stack-exchange"></span>
    <span class="fa fa-stack-overflow"></span>
    <span class="fa fa-steam"></span>
    <span class="fa fa-steam-square"></span>
    <span class="fa fa-stumbleupon"></span>
    <span class="fa fa-stumbleupon-circle"></span>
    <span class="fa fa-tencent-weibo"></span>
    <span class="fa fa-trello"></span>
    <span class="fa fa-tripadvisor"></span>
    <span class="fa fa-tumblr"></span>
    <span class="fa fa-tumblr-square"></span>
    <span class="fa fa-twitch"></span>
    <span class="fa fa-twitter"></span>
    <span class="fa fa-twitter-square"></span>
    <span class="fa fa-viacoin"></span>
    <span class="fa fa-vimeo"></span>
    <span class="fa fa-vimeo-square"></span>
    <span class="fa fa-vine"></span>
    <span class="fa fa-vk"></span>
    <span class="fa fa-wechat"></span>
    <span class="fa fa-weibo"></span>
    <span class="fa fa-weixin"></span>
    <span class="fa fa-whatsapp"></span>
    <span class="fa fa-wikipedia-w"></span>
    <span class="fa fa-windows"></span>
    <span class="fa fa-wordpress"></span>
    <span class="fa fa-xing"></span>
    <span class="fa fa-xing-square"></span>
    <span class="fa fa-y-combinator"></span>
    <span class="fa fa-y-combinator-square"></span>
    <span class="fa fa-yahoo"></span>
    <span class="fa fa-yc"></span>
    <span class="fa fa-yc-square"></span>
    <span class="fa fa-yelp"></span>
    <span class="fa fa-youtube"></span>
    <span class="fa fa-youtube-play"></span>
    <span class="fa fa-youtube-square"></span>
</div> 