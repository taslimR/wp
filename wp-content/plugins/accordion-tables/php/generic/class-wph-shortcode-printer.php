<?php 

class WPH_Shortcode_Printer {
	
	/**
	 * The shortcode tag.
	 * @var string
	 */
	public $shortcode;
	public $verification;
	public $name;
	public $link;

	function __construct ( $shortcode= "wph", $name = "", $link = "http://www.webfixfast.com/" ) {
		$this->shortcode = $shortcode;
		add_shortcode($this->shortcode, array($this, 'generate_html'));
		$this->verification = $shortcode.'_license_status';
		$this->name = $name;
		$this->link = $link;

	}

	function generate_html( $atts ) {
		extract ( shortcode_atts ( array (
								'id' => 10,
								'mobile'=> false,
								'tablet'=> false,
								'post_id'=> false,
								'key'=> false,
								'single' => true,
								), $atts
							)
				);

		//Case of custom field
		if( ! empty( $post_id ) && ! empty( $key ) )
			return get_post_meta( $post_id, $key, $single );

		//Mobile Detect
		if( ! class_exists( 'Mobile_Detect' ) ) require_once( 'Mobile_Detect.php' );
		$detect = new Mobile_Detect;
		if( $detect->isTablet() && $tablet ){ // Tablets
			return get_post_meta( $tablet, $this->shortcode, true );
		} else if( $detect->isMobile() && !$detect->isTablet() && $mobile ){ // Mobile phones
			return get_post_meta( $mobile, $this->shortcode, true );
		}

		$verification = get_option($this->verification, false);
		$admin_view = current_user_can('activate_plugins') ? true : false;
		
		if ( ! $verification && ! $admin_view ) return "<span>Please activate your <a href='{$this->link}'>{$this->name}</a> plugin license.</span>";
		// don't do shortcodes if in admin view. Else risk rolling shortcode content into editor
		else{
			
			// turn off wpautop for this
			remove_filter( 'the_content', 'wpautop' );
			
			// no filters applied in admin view 
			if ( $verification && $admin_view ) return '<span class="wph_post_id" data-wph-post-id="'.$id.'"></span>'. get_post_meta( $id, $this->shortcode, true );
			// the_content filters applied in common view
			else return '<span class="wph_post_id" data-wph-post-id="'.$id.'"></span>' . apply_filters( 'the_content', get_post_meta( $id, $this->shortcode, true ) );
			
			// turn off wpautop for this
			add_filter( 'the_content', 'wpautop' );

		}
		
	}
}

?>