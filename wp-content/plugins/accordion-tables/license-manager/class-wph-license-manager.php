<?php
/**
* License management, uses EDD
*/

class WPH_License_Manager {

	public $plugin_name;
	public $plugin_prefix;
	public $page_type_key;
	public $page_type_val;
	public $store;
	public $path;
	public $version;
	public $mode;
	public $w = 'wph';
	public $r;
	public $s;

	function __construct( $args ){
		$this->plugin_name = $args['plugin_name'];
		$this->plugin_prefix = $args['plugin_prefix'];
		$this->page_type_key = $args['page_type_key'];
		$this->page_type_val = $args['page_type_val'];
		$this->store = $args['store'];
		$this->path = $args['path'];
		$this->version = $args['version'];
		$this->mode = $args['mode'];
		$this->r = 'ready';
		$this->s = '_start';

		// activation request
		add_action( 'plugins_loaded', array( $this, 'permit_form' ) );

		// ajax action
		add_action( 'wp_ajax_wph_license_manager', array( $this, 'ajax_action' ) );
		
		// set mode based on past
		$known_mode = get_option( $this->plugin_prefix .'_prev_license_mode', false );
		if( ! $known_mode ){
			update_option( $this->plugin_prefix .'_prev_license_mode', $this->mode );
		}else{
			$this->mode = $known_mode;
		}

		// verification variable js
		add_action( 'wp_enqueue_scripts', array( $this, 'verification_var_js' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'verification_var_js' ) );
		
		// update
		if( $args['updates'] ) add_action( 'plugins_loaded', array( $this, 'update' ), 0 );
	}

	function verification_var_js( ){
		$prefix = $this->plugin_prefix;
		$status = get_option( $prefix . '_license_status', false );
		echo '<script type="text/javascript"> var ' . $prefix . '_license_status = "' . get_option( $prefix . '_license_status', 'invalid' ) . '";</script>';
	}
	
	function permit_form( ){
		if( ! empty( $_REQUEST[ $this->page_type_key ] ) && $_REQUEST[ $this->page_type_key ] === $this->page_type_val )
			$this->pre_form( );

		if( ! defined( strtoupper( $this->w.'_'.$this->r ) ) ){
			define( strtoupper( $this->w.'_'.$this->r ), TRUE );
		}
		/*
		if( get_option( $this->plugin_prefix.'_license_status' ) ){
			define( strtoupper( $this->plugin_prefix.'_'.$this->r ), TRUE );
		}
		*/
	}
	
	function pre_form( ){		
		add_action( 'admin_enqueue_scripts', array( $this, 'form_scripts' ) );
		add_action( 'admin_notices', array( $this, 'form' ) );
		
	}
	
	function form_scripts( ){
	
		// include scripts and styles
		wp_register_style( 'wph_license_manager.css',  plugins_url( '/assets/css/wph_license_manager.css', __FILE__ ) );
		wp_enqueue_style( 'wph_license_manager.css' );
		wp_register_script( 'wph_license_manager.js',  plugins_url( '/assets/js/wph_license_manager.js', __FILE__ ) );
		wp_enqueue_script( 'wph_license_manager.js' );
		wp_localize_script( 'wph_license_manager.js', 'wph_ajax', array( "url"=>admin_url( 'admin-ajax.php' ), "nonce"=> wp_create_nonce( 'wph-nonce' ) ) );
		
		// fontawesome
		wp_register_style('fontawesome', '//maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css');
		wp_enqueue_style( 'fontawesome' );

		// tooltipster
		wp_register_style( 'tooltipster',  plugins_url( '/assets/css/tooltipster.css', __FILE__ ) );
		wp_enqueue_style( 'tooltipster' );
		wp_register_script( 'jquery.tooltipster.min',  plugins_url( '/assets/js/jquery.tooltipster.min.js', __FILE__ ), array( 'jquery' ) );
		wp_enqueue_script( 'jquery.tooltipster.min' );
		//-- theme
		wp_register_style( 'tooltipster-light',  plugins_url( '/assets/css/tooltipster-light.css', __FILE__ ) );
		wp_enqueue_style( 'tooltipster-light' );
		
	}

	function form( ){

		$plugin_name = $this->plugin_name;
		$plugin_prefix = $this->plugin_prefix;
		$mode = $this->mode;

		// get options
		$username = get_option( $plugin_prefix.'_username', '' );
		$api_key = get_option( $plugin_prefix.'_api_key', '' );
		$license_key = get_option( $plugin_prefix.'_license_key', '' );
		$status = get_option( $plugin_prefix.'_license_status', false );
		$purchase_code = get_option( $plugin_prefix.'_purchase_code', '' );
		$action_type = $status ? "deactivate_license" : "activate_license";

		$api_key_msg = __("Head over to your CodeCanyon profile, then choose 'Settings' among the tabs, and then 'API Keys'. Finally, click on 'Generate API Key'.");
		
		$purchase_id_msg = __("Head over to your CodeCanyon downloads page (http://codecanyon.net/downloads). Locate the plugin, click on 'Download'. Select 'License certificate & purchase code' and the document will be downloaded. In it you'll find the 'Item Purchase Code' for the plugin.");
		
		$why_activation_msg = ( $mode === 'cc' ? __("Several new enhancements have been implemented for this plugin and many more on the way! In order to support these and future upgrades, a licensing system is necessary to ensure fair purchase of the plugin as per the licensing policy of CodeCanyon - 1 license per website. In case of any inconvenience in activation please contact support at: wordpressaholic@gmail.com") : __("Several new enhancements have been implemented for this plugin and many more on the way! In order to support these and future upgrades, a licensing system is necessary to ensure fair purchase of the plugin as per the licensing policy - 1 license per website. In case of any inconvenience in activation please contact support at: wordpressaholic@gmail.com") );

		?>
		<div class="update-nag wph_license_form wph_<?php echo $action_type; ?> wph_license_mode_<?php echo $mode; ?> ">
			<h2>
				<?php printf( __( '%s License Manager' ), $plugin_name );?>
			</h2>
			<p class="wph_description">
				<?php printf( __( 'Hey! This plugin is not activated yet. Activation is necessary for receiving automatic updates, premium support and smooth functioning. <span class="wph-tooltip wph_activate_license_why" title="%s">WHY?</span>'), $why_activation_msg ); ?>
			</p>
			<p>
				<!--Hidden inputs-->
				<input  name="action type" value="<?php echo $action_type; ?>" type="hidden"> 
				<input  name="plugin prefix" value="<?php echo $plugin_prefix; ?>" type="hidden">
				<input  name="plugin name" value="<?php echo $plugin_name; ?>" type="hidden">
				<input  name="license key" value="<?php echo $license_key; ?>" placeholder="<?php _e( 'License Key'); ?>">
				<input  name="mode" value="<?php echo $mode; ?>" type="hidden"> 
				<!--Visible inputs-->
				<input  name="username" value="<?php echo $username; ?>" placeholder="<?php _e( 'Envato username'); ?>"> 
				<input name="api key" value= "<?php echo $api_key; ?>" placeholder="<?php _e( 'API Key'); ?>" class="wph-tooltip" title="<?php echo $api_key_msg ?>"> 
				<input name="purchase code" value= "<?php echo $purchase_code; ?>" placeholder="<?php _e( 'Purchase code'); ?>"  class="wph-tooltip" title="<?php echo $purchase_id_msg ?>">
				<button class="wph_license_manager" data-wph-action="activate"><?php _e( 'Activate License'); ?></button>
				<button class="wph_license_manager" data-wph-action="deactivate"><?php _e( 'Deactivate License'); ?></button>
			</p>
		</div>
		
		<script>
			jQuery(function( $ ){
				$( '.wph-tooltip' ).tooltipster({
					theme: 'tooltipster-light',
					maxWidth: 400,
				});
			})
		</script>
		<?php
	}
	
	function ajax_action ( ) {
		
		if( ! isset( $_REQUEST[ 'nonce' ] ) || ! wp_verify_nonce( $_REQUEST[ 'nonce' ], 'wph-nonce' ) ) die( 'Not permitted' );
		if( ! isset( $_REQUEST[ 'plugin_prefix' ] ) || $_REQUEST[ 'plugin_prefix' ] !== $this->plugin_prefix ) return; // call relates to another instance

		$plugin_prefix = $_REQUEST['plugin_prefix'];
		$action_type = $_REQUEST['action_type'];
		$plugin_name = $_REQUEST['plugin_name'];
		$username = $_REQUEST['username'];
		$api_key = $_REQUEST['api_key'];
		$purchase_code = $_REQUEST['purchase_code'];
		$license_key = $_REQUEST['license_key'];
		$mode = $_REQUEST['mode'];
		$url = is_multisite( ) ?  network_site_url( ) : home_url( );

		update_option( $plugin_prefix.'_username', $username );
		update_option( $plugin_prefix.'_api_key', $api_key );
		update_option( $plugin_prefix.'_license_key', $license_key );
		update_option( $plugin_prefix.$this->s.'er', $license_key );
		update_option( $plugin_prefix.'_purchase_code', $purchase_code );

		// data to send in our API request
		$api_params = array(
			'wph_license_action' => $action_type,
			'plugin_name' => urlencode( $plugin_name ),
			'item_name' => urlencode( $plugin_name ), // the name of our product in EDD
			'username' => urlencode( $username ),
			'api_key' => $api_key,
			'purchase_code' => $purchase_code,
			'license_key' => $license_key,
			'url' => urlencode( $url ),
		);
		
		if( 'cc' === $mode ){ // in case of code canyon mode we need to verify purchase with cc
		
			// Get License
			$response = wp_remote_get( add_query_arg( $api_params, $this->store ), array( 'timeout' => 15, 'sslverify' => false ) );

			// make sure the response came back okay
			if ( is_wp_error( $response ) ){
				$error_string = $response->get_error_message();
				die( json_encode( array( 'message' => 'Issue connecting with server', 'type' => $error_string ) ) );

			}
			
			// the license
			$license_key = wp_remote_retrieve_body( $response );

			if( empty( $license_key ) || 'Invalid username / purchase code' == $license_key || 'Item does not belong to this shop' == $license_key ){
				echo json_encode( array( 'message' => $license_key ) );
				die( );
			}

		}

		// add params for EDD query
		$api_params[ 'edd_action' ] = $action_type;
		$api_params[ 'license' ] = $license_key;
		unset( $api_params[ 'wph_license_action' ] ); // do not want to init wrong code block

		// EDD License API
		$response = wp_remote_get( add_query_arg( $api_params, $this->store ), array( 'timeout' => 15, 'sslverify' => false ) );

		// make sure the response came back okay
		if ( is_wp_error( $response ) )
			return false;

		// license data from shop
		$license_data = json_decode( wp_remote_retrieve_body( $response ) );

		$result = 'failure';
		$message = __( 'There was an issue in activating your license!' );
		$more = $license_data;
		
		// upon activation $license_data->license will either be "valid" or "invalid"
		if( $license_data->license == 'valid' ) {
			$ld = $license_data->license;
			update_option( $plugin_prefix.'_license_status', $ld );
			update_option( $plugin_prefix.'_on', $ld );
			update_option( $plugin_prefix.'_license_key', $license_key );
			update_option( $plugin_prefix.$this->s.'er', $license_key );
			$result = 'success';
			$message = __('The license is activated!');
		}

		// upon deactivation $license_data->license will be either "deactivated" or "failed"
		if( $license_data->license == 'deactivated' || $license_data->license == 'failed' ) {
			delete_option( $plugin_prefix.'_license_status' );
			delete_option( $plugin_prefix.'_on' );
			$result = 'success';
			$message = __('The license is deactivated!');
		}

		// already activated on max sites
		if( isset( $license_data->error ) && $license_data->error == 'no_activations_left' ) {
			delete_option( $plugin_prefix.'_license_status' );
			$result = 'failure';
			$message = __('The license is already activated elsewhere. To activate it here, please deactivate it from the other website first!');
		}

		// ajax feedback to user
		echo json_encode( array( 'result' => $result, 'message' => $message, 'more' => $more ) );
		die( );

	}
	
	function update( ){
		$plugin_prefix = $this->plugin_prefix;
		$plugin_name = $this->plugin_name;
		$store = $this->store;
		$license_key = trim( get_option( $plugin_prefix.'_license_key' ) );
		$path = $this->path;
		$version = $this->version;
		$url = is_multisite( ) ? network_site_url( ) : home_url( );

		// setup the updater
		if( ! class_exists( 'EDD_SL_Plugin_Updater' ) )  require_once( plugin_dir_path( __FILE__ ) . 'EDD_SL_Plugin_Updater.php' );
		$edd_updater = new EDD_SL_Plugin_Updater( $store, $path, array(
				'version' 	=> $version,
				'license' 	=> $license_key,
				'item_name' => $plugin_name,
				'author' 	=> 'WordPressaHolic',
				'url'           => $url,
			)
		);

	}
	
}

?>