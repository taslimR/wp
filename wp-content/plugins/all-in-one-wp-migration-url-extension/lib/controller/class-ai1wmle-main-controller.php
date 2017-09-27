<?php
/**
 * Copyright (C) 2014-2017 ServMask Inc.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * ███████╗███████╗██████╗ ██╗   ██╗███╗   ███╗ █████╗ ███████╗██╗  ██╗
 * ██╔════╝██╔════╝██╔══██╗██║   ██║████╗ ████║██╔══██╗██╔════╝██║ ██╔╝
 * ███████╗█████╗  ██████╔╝██║   ██║██╔████╔██║███████║███████╗█████╔╝
 * ╚════██║██╔══╝  ██╔══██╗╚██╗ ██╔╝██║╚██╔╝██║██╔══██║╚════██║██╔═██╗
 * ███████║███████╗██║  ██║ ╚████╔╝ ██║ ╚═╝ ██║██║  ██║███████║██║  ██╗
 * ╚══════╝╚══════╝╚═╝  ╚═╝  ╚═══╝  ╚═╝     ╚═╝╚═╝  ╚═╝╚══════╝╚═╝  ╚═╝
 */

class Ai1wmle_Main_Controller {

	/**
	 * Main Application Controller
	 *
	 * @return Ai1wmle_Main_Controller
	 */
	public function __construct() {
		register_activation_hook( AI1WMLE_PLUGIN_BASENAME, array( $this, 'activation_hook' ) );

		// Activate hooks
		$this->activate_actions()
			 ->activate_filters()
			 ->activate_textdomain();
	}

	/**
	 * Activation hook callback
	 *
	 * @return Object Instance of this class
	 */
	public function activation_hook() {

	}

	/**
	 * Initializes language domain for the plugin
	 *
	 * @return Object Instance of this class
	 */
	private function activate_textdomain() {
		load_plugin_textdomain( AI1WMLE_PLUGIN_NAME, false, false );

		return $this;
	}

	/**
	 * Register plugin menus
	 *
	 * @return void
	 */
	public function admin_menu() {

	}

	/**
	 * Register scripts and styles for Import Controller
	 *
	 * @return void
	 */
	public function register_import_scripts_and_styles( $hook ) {
		if ( 'all-in-one-wp-migration_page_site-migration-import' !== $hook ) {
			return;
		}

		wp_enqueue_style(
			'ai1wmle-css-import',
			Ai1wm_Template::asset_link( 'css/import.min.css', 'AI1WMLE' )
		);
		wp_enqueue_script(
			'ai1wmle-js-import',
			Ai1wm_Template::asset_link( 'javascript/import.min.js', 'AI1WMLE' ),
			array( 'jquery' )
		);
	}

	/**
	 * Outputs menu icon between head tags
	 *
	 * @return void
	 */
	public function admin_head() {
		?>
		<style type="text/css" media="all">
			.ai1wm-label {
				border: 1px solid #5cb85c;
				background-color: transparent;
				color: #5cb85c;
				cursor: pointer;
				text-transform: uppercase;
				font-weight: 600;
				outline: none;
				transition: background-color 0.2s ease-out;
				padding: .2em .6em;
				font-size: 0.8em;
				border-radius: 5px;
				text-decoration: none !important;
			}

			.ai1wm-label:hover {
				background-color: #5cb85c;
				color: #fff;
			}
		</style>
	<?php
	}

	/**
	 * Register listeners for actions
	 *
	 * @return Object Instance of this class
	 */
	private function activate_actions() {
		// Init
		add_action( 'admin_init', array( $this, 'init' ) );

		// Admin header
		add_action( 'admin_head', array( $this, 'admin_head' ) );

		// All in One WP Migration
		add_action( 'plugins_loaded', array( $this, 'ai1wm_loaded' ), 10 );

		// Export and import commands
		add_action( 'plugins_loaded', array( $this, 'ai1wm_commands' ), 20 );

		// Add import scripts and styles
		add_action( 'admin_enqueue_scripts', array( $this, 'register_import_scripts_and_styles' ), 20 );

		return $this;
	}

	/**
	 * Register listeners for filters
	 *
	 * @return Object Instance of this class
	 */
	private function activate_filters() {

		return $this;
	}

	/**
	 * Export and import commands
	 *
	 * @return void
	 */
	public function ai1wm_commands() {
		if ( isset( $_REQUEST['url'] ) ) {
			// Add import commands
			add_filter( 'ai1wm_import', 'Ai1wmle_Import_Url::execute', 20 );
			add_filter( 'ai1wm_import', 'Ai1wmle_Import_Download::execute', 30 );

			// Remove import commands
			remove_filter( 'ai1wm_import', 'Ai1wm_Import_Upload::execute', 5 );
		}
	}

	/**
	 * Check whether All in one WP Migration has been loaded
	 *
	 * @return void
	 */
	public function ai1wm_loaded() {
		if ( ! defined( 'AI1WM_PLUGIN_NAME' ) ) {
			if ( is_multisite() ) {
				add_action( 'network_admin_notices', array( $this, 'ai1wm_notice' ) );
			} else {
				add_action( 'admin_notices', array( $this, 'ai1wm_notice' ) );
			}
		} else {
			if ( is_multisite() ) {
				add_action( 'network_admin_menu', array( $this, 'admin_menu' ), 20 );
			} else {
				add_action( 'admin_menu', array( $this, 'admin_menu' ), 20 );
			}

			// Picker
			add_action( 'ai1wm_import_left_end', 'Ai1wmle_Import_Controller::picker' );

			// Add import button
			add_filter( 'ai1wm_import_url', 'Ai1wmle_Import_Controller::button' );

			// Add import unlimited
			add_filter( 'ai1wm_max_file_size', array( $this, 'max_file_size' ) );
		}
	}

	/**
	 * Display All in one WP Migration notice
	 *
	 * @return void
	 */
	public function ai1wm_notice() {
		?>
		<div class="error">
			<p>
				<?php
				_e(
					'All in One WP Migration is not activated. Please activate the plugin in order to use URL extension.',
					AI1WMLE_PLUGIN_NAME
				);
				?>
			</p>
		</div>
		<?php
	}

	/**
	 * Max file size callback
	 *
	 * @return string
	 */
	public function max_file_size() {
		return AI1WMLE_MAX_FILE_SIZE;
	}

	/**
	 * Register initial parameters
	 *
	 * @return void
	 */
	public function init() {
		// Set Purchase ID
		if ( ! get_option( 'ai1wmle_plugin_key' ) ) {
			update_option( 'ai1wmle_plugin_key', AI1WMLE_PURCHASE_ID );
		}
	}
}
