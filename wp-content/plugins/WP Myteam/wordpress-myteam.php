<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
 * Plugin Name: WordPress My Team
 * Plugin URI:  http://www.mediacity.co.in/myteam
 * Description: WordPress My Team help you to add team member in your existing website section along with multiple unique style.
 * Version:     1.0.2
 * Author:      Punit Patel
 * Author URI:  https://codecanyon.net/user/pintupatel05
 * Text Domain: wpwmt
 * Domain Path: /languages
 */

/**
 * Class : Wp_Wmt_Myteam
 *
 * @since  1.0.0
 * @access public
 */
if ( ! class_exists( 'Wp_Wmt_Myteam' ) ) :
    class Wp_Wmt_Myteam {

        /**
         * Plugin directory path.
         *
         * @since  1.0.0
         * @access public
         * @var    string
         */
        public $dir_path = '';

        /**
         * Plugin directory URI.
         *
         * @since  1.0.0
         * @access public
         * @var    string
         */
        public $dir_uri = '';

        /**
         * Plugin admin directory path.
         *
         * @since  1.0.0
         * @access public
         * @var    string
         */
        public $admin_dir = '';

        /**
         * Plugin includes directory path.
         *
         * @since  1.0.0
         * @access public
         * @var    string
         */
        public $inc_dir = '';

        /**
         * Plugin CSS directory URI.
         *
         * @since  1.0.0
         * @access public
         * @var    string
         */
        public $css_uri = '';

        /**
         * Plugin JS directory URI.
         *
         * @since  1.0.0
         * @access public
         * @var    string
         */
        public $js_uri = '';

        /**
         * Returns the instance.
         *
         * @since  1.0.0
         * @access public
         * @return object
         */
        public static function get_instance() {

            static $instance = null;

            if ( is_null( $instance ) ) {
                $instance = new Wp_Wmt_Myteam;
                $instance->setup();
                $instance->includes();
                $instance->setup_actions();
            }

            return $instance;
        }

        /**
         * Sets up globals.
         *
         * @since  1.0.0
         * @access private
         * @return void
         */
        private function setup() {

            // Main plugin directory path and URI.
            $this->dir_path      = trailingslashit( plugin_dir_path( __FILE__ ) );
            $this->dir_uri       = trailingslashit( plugin_dir_url(  __FILE__ ) );
            
            // Plugin directory paths.
            $this->inc_dir       = trailingslashit( $this->dir_path . 'inc'       );
            $this->admin_dir     = trailingslashit( $this->dir_path . 'admin'     );
            $this->templates_dir = trailingslashit( $this->dir_path . 'templates' );
            
            // Plugin directory URIs.
            $this->css_uri       = trailingslashit( $this->dir_uri . 'assets/css' );
            $this->js_uri        = trailingslashit( $this->dir_uri . 'assets/js'  );
            $this->img_uri       = trailingslashit( $this->dir_uri . 'assets/images'  );

            // Plugin Version
            $this->version  = "1.0.0";
        }

        /**
         * Loads the translation files.
         *
         * @since  1.0.0
         * @access public
         * @return void
         */
        public function i18n() {
            load_plugin_textdomain( 'wpwmt', false, trailingslashit( dirname( plugin_basename( __FILE__ ) ) ). 'languages' );
        }
        
        /**
         * Loads files needed by the plugin.
         *
         * @since  1.0.0
         * @access private
         * @return void
         */
        private function includes() {

            // Load admin files.
            if ( is_admin() ) {

                // General admin functions.
                if( file_exists( $this->admin_dir . 'class-admin-wmttable.php' )) {
                    require_once( $this->admin_dir . 'class-admin-wmttable.php' );
                    $admin_instance = new Admin_Wmt_Pricetable;// Call Default functions
                    $admin_instance->admin_hook();
                }
            }else{
                if( file_exists( $this->inc_dir . 'function.php' )) {
                    require_once( $this->inc_dir . 'function.php' );
                }
            }

            // Add Post type my team
            if( file_exists( $this->inc_dir . 'wmt-posttype.php' )) {
                require_once( $this->inc_dir . 'wmt-posttype.php' );
                $wmt_post_type = new Wp_Wmt_Post_Type();
            }

        }

        /**
         * Sets up main plugin actions and filters.
         *
         * @since  1.0.0
         * @access private
         * @return void
         */
        private function setup_actions() {

            // Internationalize the text strings used.
            add_action( 'plugins_loaded', array( $this, 'i18n' ), 2 );
            // On plugin activation
            register_activation_hook( __FILE__, array( $this, 'wmt_flush_rewrites' ) );
        }

        public function wmt_flush_rewrites(){

            if( file_exists( $this->inc_dir . 'wmt-posttype.php' )) {
                require_once( $this->inc_dir . 'wmt-posttype.php' );
                $wmt_post_type = new Wp_Wmt_Post_Type();
            }
            $wmt_options = get_post_meta( $post->ID, 'wmt_myteam', true );
            
            for( $i = 1; $i < 5; $i++ ):
                $social_email = isset( $wmt_options['email_'.$i] ) ? $wmt_options['email_'.$i] : '';
                if( !empty( $social_email )):
                    $wmt_options[ 'social_url_'.$i][] = $social_email;
                    $wmt_options[ 'icon_'.$i][] = 'fa fa-envelope';
                endif;
                $social_facebook = isset( $wmt_options['facebook_'.$i] ) ? $wmt_options['facebook_'.$i] : '';
                if( !empty( $social_facebook )):
                    $wmt_options[ 'social_url_'.$i][] = $social_facebook;
                    $wmt_options[ 'icon_'.$i][] = 'fa fa-facebook';
                endif;
                $social_twitter = isset( $wmt_options['twitter_'.$i] ) ? $wmt_options['twitter_'.$i] : '';
                if( !empty( $social_twitter )):
                    $wmt_options[ 'social_url_'.$i][] = $social_twitter;
                    $wmt_options[ 'icon_'.$i][] = 'fa fa-twitter';
                endif;
                $social_linkedin = isset( $wmt_options['linkedin_'.$i] ) ? $wmt_options['linkedin_'.$i] : '';
                if( !empty( $social_linkedin )):
                    $wmt_options[ 'social_url_'.$i][] = $social_linkedin;
                    $wmt_options[ 'icon_'.$i][] = 'fa fa-envelope';
                endif;
            endfor;
            update_option( 'wmt_myteam', $wmt_options );
            // Flush Rewrite Rule
            flush_rewrite_rules();
        }


    }
endif;
/**
 * Gets the instance of the `Wp_Wmt_Myteam` class.  This function is useful for quickly grabbing data
 * used throughout the plugin.
 *
 * @since  1.0.0
 * @access public
 * @return object
 */
function wp_wmt_myteam() {
    return Wp_Wmt_Myteam::get_instance();
}

// Let's roll!
wp_wmt_myteam();