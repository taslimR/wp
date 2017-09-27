<?php 
/**
 * This class is more for helping the author compile presets than the users. But it also 
 * replaces needle with array of dummy images.
 */

class WPH_Preset_Handler {
	
	public $posttype;
	public $meta_key;
	public $title_needle;
	public $location;
	public $variable;
	
	// gets hold of all the presets and prints them onto a php file
	function compile_presets ($posttype, $meta_key, $title_needle="preset:", $location, $variable) {
		$this->posttype = $posttype;
		$this->meta_key = $meta_key;
		$this->title_needle = $title_needle;
		$this->location = $location;
		$this->variable = $variable;

		add_action( 'shutdown', array($this, 'print_presets'));

	}
	
	// gather presets from among the accordion tables custom posts into an array
	function gather_presets() {
		$presets = array(
			'map'=>array(),
		);
		$offset = 1; // offset caused by string keys. update this while adding more string keys
		$posttype = $this->posttype;
		$query = new WP_Query(array('post_type' => $posttype, 'posts_per_page'=>-1));		

		while ($query->have_posts()) : $query->the_post();
			$title = strtolower( get_the_title( ) );
			if ( strpos( $title, $this->title_needle ) !== false ){
				if( gettype( $this->meta_key ) === 'string' ){ // single meta key
					// get the preset HTML
					$preset = get_post_meta( get_the_ID(), $this->meta_key, true );
					// replace image source with needle
					$doc = new DOMDocument();
					$doc->loadHTML($preset);
					$tags = $doc->getElementsByTagName('img');
					foreach ($tags as $tag) {
						$new_src_url = '%image%';
						$tag->setAttribute('src', $new_src_url);
					}
					$preset = $doc->saveHTML();
					
				}else{ // multiple meta needed
					$preset = array( );
					foreach( $this->meta_key as $handle => $meta_key){
						$preset[ $handle ] = get_post_meta( get_the_ID( ), $meta_key, true );
					}
				}
				
				// add the preset to the array
				$presets[ ] = $preset;
				// now updating the map:
				// clip and trim title - prepare for explosion to extract the hierarchy in the map
				$title = trim( str_replace( $this->title_needle, '', $title ) );
				// exploding - splitting the title into an array
				$exploded = explode( '|', $title );
				// creating an array hierarchy based on pipes
				$presets_map_depth = &$presets['map'];
				foreach( $exploded as $val ) {
					$val = trim( $val );
					if ( ! isset( $presets_map_depth[ $val ] ) ) $presets_map_depth[ $val ] = "";
					$presets_map_depth = &$presets_map_depth[ $val ];
				}
				// add preset position to the map
				$presets_map_depth = count( $presets ) - $offset - 1; // removing offset caused by associative keys and index difference from length
			}
		endwhile;
		wp_reset_postdata();
		return $presets;

	}
	
	// prints the presets onto a file
	function print_presets () {
		$presets = var_export($this->gather_presets(), true);
		file_put_contents($this->location, '<?php $'.$this->variable.' = '.$presets.'?>', LOCK_EX);

	}

	// each time needle is found, new replacement is used
	function replace_needles_with_array($needle, $replacements, $haystack) {
		if( is_array( $replacements ) ){
			foreach ($replacements as $replacement) {
				$pos = strpos($haystack,$needle);
				if ($pos !== false) {
					$haystack = substr_replace($haystack,$replacement,$pos,strlen($needle));
				}
			}
		}else{
			$haystack = str_replace( $needle, $replacements, $haystack );
		}
		return $haystack;
		
	}

	// replace dummy image needles with actual images
	function insert_dummy_images ($haystack, $needle= "%image%", $imgs_path, $imgs_url) {
		$replacements = array(); // array of dummy images

		if( defined( 'GLOB_BRACE' ) ){
			$imgs = glob($imgs_path."/*.{jpg,png,gif}", GLOB_BRACE); // get all image filenames
			foreach ($imgs as $img) {
				$replacements[] = $imgs_url.basename($img); // converted to urls
			}
		}else{
			$replacements = $imgs_url."brown-buttons.jpg";
		}
		return $this->replace_needles_with_array($needle, $replacements, $haystack);

	}

}
?>