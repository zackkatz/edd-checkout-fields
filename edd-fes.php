<?php
/**
 * Plugin Name:         Easy Digital Downloads - Frontend Submissions
 * Plugin URI:          https://easydigitaldownloads.com/extension/frontend-submissions/
 * Description:         Mimick eBay, Envato, or Amazon type sites with this plugin and Easy Digital Downloads combined!
 * Author:              Chris Christoff
 * Author URI:          http://www.chriscct7.com
 *
 * Version:             2.0.3
 * Requires at least:   3.6
 * Tested up to:        3.6
 *
 * Text Domain:         edd_fes
 * Domain Path:         /edd_fes/languages/
 *
 * @category            Plugin
 * @copyright           Copyright © 2013 Chris Christoff
 * @author              Chris Christoff
 * @package             FES
 */
 
if ( !defined( 'ABSPATH' ) ) {
	exit;
}

/** Check if Easy Digital Downloads is active */
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

class EDD_Front_End_Submissions {
	/**
	 * @var EDD_Front_End_Submissions The one true EDD_Front_End_Submissions
	 * @since 1.4
	 */
	private static $instance;
	public $id = 'edd_fes';
	public $fes_options;
	public $basename;
	
	// Setup objects for each class
	public $render_form;
	public $admin;
	public $admin_form;
	public $admin_posting;
	public $admin_posting_profile;
	public $application;
	public $emails;
	public $frontend;
	public $frontend_form_profile;
	public $install;
	public $login_register;
	public $menu;
	public $queries;
	public $templates;
	public $vendor_applicants;
	public $vendor_permissions;
	public $vendor_shop;
	public $vendors;
	public $upload;
	
	/**
	 * Main EDD_Front_End_Submissions Instance
	 *
	 * Insures that only one instance of EDD_Front_End_Submissions exists in memory at any one
	 * time. Also prevents needing to define globals all over the place.
	 *
	 * @since 1.4
	 * @static
	 * @staticvar array $instance
	 * @uses EDD_Front_End_Submissions::setup_globals() Setup the globals needed
	 * @uses EDD_Front_End_Submissions::includes() Include the required files
	 * @uses EDD_Front_End_Submissions::setup_actions() Setup the hooks and actions
	 * @see EDD()
	 * @return The one true EDD_Front_End_Submissions
	 */
	public static function instance() {
		if ( !isset( self::$instance ) && !( self::$instance instanceof EDD_Front_End_Submissions ) ) {
			self::$instance = new EDD_Front_End_Submissions;
			self::$instance->define_globals();
			self::$instance->includes();
			self::$instance->setup();
			// Setup class instances
			self::$instance->render_form           = new FES_Render_Form;
			self::$instance->login_register        = new FES_Login_Register;
			self::$instance->application           = new FES_Application;
			self::$instance->templates             = new FES_Templates;
			self::$instance->setup                 = new FES_Setup;
			self::$instance->emails                = new FES_Emails;
			self::$instance->vendors               = new FES_Vendors;
			self::$instance->vendor_permissions    = new FES_Vendor_Permissions;
			self::$instance->vendor_applicants     = new FES_Vendor_Applicants;
			self::$instance->vendor_shop           = new FES_Vendor_Shop;
			self::$instance->upload                = new FES_Upload;
			self::$instance->frontend              = new FES_Frontend;
			self::$instance->frontend_form_profile = new FES_Frontend_Form_Profile;
			self::$instance->frontend_form_post    = new FES_Frontend_Form_Post;
			self::$instance->queries               = new FES_Queries;
			self::$instance->menu                  = new FES_Menu;
			if ( is_admin() ) {
				self::$instance->admin                 = new FES_Admin;
				self::$instance->admin_form            = new FES_Admin_Form;
				self::$instance->admin_posting         = new FES_Admin_Posting;
				self::$instance->admin_posting_profile = new FES_Admin_Posting_Profile;
			}
		}
		return self::$instance;
	}
	
	public function define_globals() {
		$this->title    = __( 'Frontend Submissions', 'edd_fes' );
		$this->file     = __FILE__;
		$this->basename = apply_filters( 'edd_fes_plugin_basename', plugin_basename( $this->file ) );
		// Plugin Name
		if ( !defined( 'fes_plugin_name' ) ) {
			define( 'fes_plugin_name', 'Frontend Submissions' );
		}
		// Plugin Version
		if ( !defined( 'fes_plugin_version' ) ) {
			define( 'fes_plugin_version', '2.0.3' );
		}
		// Plugin Root File
		if ( !defined( 'fes_plugin_file' ) ) {
			define( 'fes_plugin_file', __FILE__ );
		}
		// Plugin Folder Path
		if ( !defined( 'fes_plugin_dir' ) ) {
			define( 'fes_plugin_dir', WP_PLUGIN_DIR . '/' . basename( dirname( __FILE__ ) ) . '/' );
		}
		// Plugin Folder URL
		if ( !defined( 'fes_plugin_url' ) ) {
			define( 'fes_plugin_url', plugin_dir_url( fes_plugin_file ) );
		}
		// Plugin Assets URL
		if ( !defined( 'fes_assets_url' ) ) {
			define( 'fes_assets_url', fes_plugin_url . 'assets/' );
		}
		if ( !class_exists( 'EDD_License' ) ) {
			require_once fes_plugin_dir . 'assets/lib/EDD_License_Handler.php';
		}
		$license = new EDD_License( __FILE__, fes_plugin_name, fes_plugin_version, 'Chris Christoff' );
	}
	
	public function includes() {
		require_once fes_plugin_dir . 'classes/class-vendor-shop.php';
		require_once fes_plugin_dir . 'classes/class-templates.php';
		require_once fes_plugin_dir . 'classes/class-queries.php';
		require_once fes_plugin_dir . 'classes/class-vendors.php';
		require_once fes_plugin_dir . 'classes/class-vendor-permissions.php';
		require_once fes_plugin_dir . 'classes/class-frontend.php';
		require_once fes_plugin_dir . 'classes/class-login-register.php';
		require_once fes_plugin_dir . 'classes/class-application.php';
		require_once fes_plugin_dir . 'classes/class-emails.php';
		require_once fes_plugin_dir . 'classes/class-setup.php';
		require_once fes_plugin_dir . 'classes/class-vendor-applicants.php';
		require_once fes_plugin_dir . 'classes/class-menu.php';
		require_once fes_plugin_dir . 'classes/forms/render-form.php';
		require_once fes_plugin_dir . 'classes/forms/frontend-form-post.php';
		require_once fes_plugin_dir . 'classes/forms/frontend-form-profile.php';
		require_once fes_plugin_dir . 'classes/forms/upload.php';
		require_once fes_plugin_dir . 'classes/forms/functions.php';
		if ( is_admin() ) {
			if ( !class_exists( 'WP_List_Table' ) ) {
				require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
			}
			require_once fes_plugin_dir . 'classes/class-fes-list-table.php';
			require_once fes_plugin_dir . 'classes/class-admin.php';
			require_once fes_plugin_dir . 'classes/forms/admin-form.php';
			require_once fes_plugin_dir . 'classes/forms/admin-posting.php';
			require_once fes_plugin_dir . 'classes/forms/admin-posting-profile.php';
			require_once fes_plugin_dir . 'classes/forms/admin-template.php';
			require_once fes_plugin_dir . 'classes/forms/admin-template-post.php';
			require_once fes_plugin_dir . 'classes/forms/admin-template-profile.php';
		}
		if ( !function_exists( 'recaptcha_get_html' ) ) {
			require_once fes_plugin_dir . 'assets/lib/recaptchalib.php';
		}
	}
	
	public static function install() {
		require_once fes_plugin_dir . 'classes/class-install.php';
		$install = new FES_Install;
		$install->init();
	}
	
	public function setup() {
		$this->load_settings();
		$this->setup = new FES_Setup;
		do_action( 'edd_fes_setup_actions' );
	}
	
	public function load_settings() {
		if ( empty( $this->fes_options ) ) {
			require_once fes_plugin_dir . 'classes/settings/classes/sf-class-settings.php';
			$this->fes_options = new SF_Settings_API( $this->id, 'Settings', 'fes-about', __FILE__ );
			$this->fes_options->load_options( fes_plugin_dir . 'classes/settings/sf-options.php' );
		}
	}
}

/**
 * The main function responsible for returning the one true EDD_Front_End_Submissions
 * Instance to functions everywhere.
 *
 * Use this function like you would a global variable, except without needing
 * to declare the global.
 *
 * Example: <?php $edd_fes = EDD_FES(); ?>
 *
 * @since 2.0
 * @return object The one true EDD_Front_End_Submissions Instance
 */
function EDD_FES() {
	return EDD_Front_End_Submissions::instance();
}

EDD_FES();

function EDD_FES_Install() {
	EDD_FES()->install();
}

register_activation_hook( __FILE__, 'EDD_FES_Install' );