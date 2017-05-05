<?php
/**
 * Plugin Name:         Easy Digital Downloads - Checkout Fields Manager
 * Plugin URI:          https://easydigitaldownloads.com/extensions/checkout-fields-manager/
 * Description:         Easily add and control EDD's checkout fields!
 * Author:              Easy Digital Downloads
 * Author URI:          https://easydigitaldownloads.com
 *
 * Version:             2.0.12
 * Requires at least:   4.3
 * Tested up to:        4.7.4
 *
 * Text Domain:         edd_cfm
 * Domain Path:         /languages/
 *
 * @category            Plugin
 * @copyright           Copyright © 2016 Easy Digital Downloads, LLC
 * @author              Easy Digital Downloads
 * @package             CFM
 */

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Loads this file which is used in the check to see if Easy Digital Downloads is active
 */
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

/**
 * The main EDD CFM class
 *
 * This class loads all of the CFM files and constants as well
 * as loads the l10n files.
 *
 * @since 2.0.0
 * @access public
 */
class EDD_Checkout_Fields_Manager {

	/**
	 * CFM plugin object
	 *
	 * @since 2.0.0
	 * @access public
	 * @var EDD_Checkout_Fields_Manager $instance Singleton object of CFM.
	 *      Use it to call all CFM methods instead of calling CFM functions directly.
	 */
	private static $instance;

	/**
	 * CFM plugin id string
	 *
	 * @since 2.0.0
	 * @access public
	 * @var string $id
	 */
	public $id = 'edd_cfm';

	/**
	 * CFM plugin basename
	 *
	 * @since 2.0.0
	 * @access public
	 * @var string $basename
	 */
	public $basename;

	/**
	 * CFM Setup
	 *
	 * @since 2.0.0
	 * @access public
	 * @var CFM_Setup $setup Use to access any function in CFM_Setup class.
	 */
	public $setup;

	/**
	 * CFM Emails
	 *
	 * @since 2.0.0
	 * @access public
	 * @var CFM_Emails $emails Use to access any function in CFM_Emails class.
	 */
	public $emails;


	/**
	 * CFM Menu
	 *
	 * @since 2.0.0
	 * @access public
	 * @var CFM_Menu $menu Use to access any function in CFM_Menu class.
	 */
	public $menu;

	/**
	 * CFM Helper
	 *
	 * @since 2.0.0
	 * @access public
	 * @var CFM_Helper $helper Use to access any function in CFM_Helper class.
	 */
	public $helper;

	/**
	 * CFM Export
	 *
	 * @since 2.0.0
	 * @access public
	 * @var CFM_Export $export Use to access any function in CFM_Export class.
	 */
	public $export;

	/**
	 * CFM Tools
	 *
	 * @since 2.0.0
	 * @access public
	 * @var CFM_Tools $tools Use to access any function in CFM_Tools class.
	 */
	public $tools;

	/**
	 * CFM Admin Customer Profile
	 *
	 * @since 2.0.0
	 * @access public
	 * @var CFM_Admin_Customer_Profile $admin_profile Use to access any function in CFM_Admin_Customer_Profile class.
	 */
	public $admin_profile;

	/**
	 * CFM Frontend Customer Profile
	 *
	 * @since 2.0.0
	 * @access public
	 * @var CFM_Frontend_Customer_Profile $admin_profile Use to access any function in CFM_Frontend_Customer_Profile class.
	 */
	public $frontend_profile;

	/**
	 * CFM Checkout Form
	 *
	 * @since 2.0.0
	 * @access public
	 * @var CFM_Checkout_Form $checkout Use to access any function in CFM_Checkout_Form class.
	 */
	public $checkout;


	/**
	 * CFM Edit Payment
	 *
	 * @since 2.0.0
	 * @access public
	 * @var CFM_Edit_Payment $edit_payment Use to access any function in CFM_Edit_Payment class.
	 */
	public $edit_payment;

	/**
	 * CFM Forms objects
	 *
	 * @since 2.0.0
	 * @access public
	 * @var array $load_forms Contains array of each registered CFM Form as empty
	 *					 instantiated object.
	 */
	public $load_forms;

	/**
	 * CFM Field objects
	 *
	 * @since 2.0.0
	 * @access public
	 * @var array $load_fields Contains array of each registered CFM Field as empty
	 *					 instantiated object.
	 */
	public $load_fields;


	/**
	 * Main EDD_Checkout_Fields_Manager Instance
	 *
	 * Insures that only one instance of EDD_Checkout_Fields_Manager exists in memory at any one
	 * time. Also prevents needing to define globals all over the place.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @uses EDD_Checkout_Fields_Manager::define_globals() Setup the globals needed
	 * @uses cfm_call_install() Run a version to version background upgrade routine if required
	 * @uses EDD_Checkout_Fields_Manager::includes() Include the required files
	 * @uses EDD_Checkout_Fields_Manager::setup() Setup the hooks and actions
	 * @uses EDD_Checkout_Fields_Manager::wp_notice() Throws admin notice if WP version < CFM min WP version
	 * @uses EDD_Checkout_Fields_Manager::edd_notice() Throws admin notice if EDD version < CFM min EDD version
	 *
	 * @var array $instance
	 * @global string $wp_version WordPress version (provided by WordPress core).
	 * @return EDD_Checkout_Fields_Manager The one true instance of EDD_Checkout_Fields_Manager
	 */
	public static function instance() {
		global $wp_version;

		// If the WordPress site doesn't meet the correct EDD and WP version requirements, deactivate and show notice.
		if ( version_compare( $wp_version, '4.3', '<' ) ) {
			add_action( 'admin_notices', array( 'EDD_Checkout_Fields_Manager','wp_notice' ) );
			return;
		} else if ( !class_exists( 'Easy_Digital_Downloads' ) || version_compare( EDD_VERSION, '2.5', '<' ) ) {
			add_action( 'admin_notices', array( 'EDD_Checkout_Fields_Manager','edd_notice' ) );
			return;
		}

		if ( !isset( self::$instance ) && !( self::$instance instanceof EDD_Checkout_Fields_Manager ) ) {
			self::$instance = new EDD_Checkout_Fields_Manager;
			self::$instance->define_globals();
			self::$instance->includes();

			$cfm_version = get_option( 'cfm_current_version', '1.0' );

			// this does the version to version background update routines including schema correction
			if ( version_compare( $cfm_version, '2.0', '<' ) ) {
				cfm_call_install();
			}

			self::$instance->setup();

			/*
			 * Here we're loading all of the registered CFM Form and Field objects.
			 * We do this on an add_action on plugins_loaded to ensure other plugins
			 * can register custom CFM Form and CFM Field classes. The plugins_loaded
			 * makes it so we don't run too early for this.
			 */
			add_action( 'plugins_loaded', array( self::$instance, 'load_abstracts' ) );

			// Setup class instances
			self::$instance->helper 			   = new CFM_Helpers;
			self::$instance->emails                = new CFM_Emails;

			if ( cfm_is_admin() ) {
				self::$instance->menu                  = new CFM_Menu;
				self::$instance->edit_payment          = new CFM_Edit_Payment;
				self::$instance->admin_profile         = new CFM_Admin_Customer_Profile;
				self::$instance->export     	       = new CFM_Export;
				self::$instance->tools         		   = new CFM_Tools;
				self::$instance->formbuilder_templates = new CFM_Formbuilder_Templates;
			}
			self::$instance->frontend_profile         = new CFM_Frontend_Customer_Profile;
			self::$instance->checkout         		  = new CFM_Checkout;

			/*
			 * We have to load EDD's upload functions and misc functions files manually
			 * to garuntee that everywhere in CFM we can use those functions
			 */
			require_once EDD_PLUGIN_DIR . 'includes/admin/upload-functions.php';
			require_once EDD_PLUGIN_DIR . 'includes/misc-functions.php';
		}
		return self::$instance;
	}

	/**
	 * Throw error on object clone
	 *
	 * The whole idea of the singleton design pattern is that there is a single
	 * object therefore, we don't want the object to be cloned.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @return void
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'edd_cfm' ), '2.0' );
	}

	/**
	 * Disable unserializing of the class
	 *
	 * Attempting to wakeup an CFM instance will throw a doing it wrong notice.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @return void
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'edd_cfm' ), '2.0' );
	}

	/**
	 * Define CFM globals
	 *
	 * This function defines all of the CFM PHP constants and a few object attributes.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @return void
	 */
	public function define_globals() {
		$this->title	= __( 'Checkout Fields Manager', 'edd_cfm' );
		$this->file		= __FILE__;
		$basename		= plugin_basename( $this->file );

		/**
		 * CFM basename.
		 *
		 * This filter allows you to edit the CFM object basename field.
		 *
		 * @since 2.0.0
		 *
		 * @param string  $basename Basename of CFM.
		 */
		$this->basename = apply_filters( 'cfm_plugin_basename', $basename );

		// Plugin Name
		if ( !defined( 'cfm_plugin_name' ) ) {
			define( 'cfm_plugin_name', 'Checkout Fields Manager' );
		}
		// Plugin Version
		if ( !defined( 'cfm_plugin_version' ) ) {
			define( 'cfm_plugin_version', '2.0.12' );
		}
		// Plugin Root File
		if ( !defined( 'cfm_plugin_file' ) ) {
			define( 'cfm_plugin_file', __FILE__ );
		}
		// Plugin Folder Path
		if ( !defined( 'cfm_plugin_dir' ) ) {
			define( 'cfm_plugin_dir', plugin_dir_path( cfm_plugin_file ) );
		}
		// Plugin Folder URL
		if ( !defined( 'cfm_plugin_url' ) ) {
			define( 'cfm_plugin_url', plugin_dir_url( cfm_plugin_file ) );
		}
		// Plugin Assets URL
		if ( !defined( 'cfm_assets_url' ) ) {
			define( 'cfm_assets_url', cfm_plugin_url . 'assets/' );
		}
	}

	/**
	 * Loads Abstracts
	 *
	 * This function loads CFM Field and Form abstracts
	 * as well as any classes that extend them.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @uses CFM_Setup::load_forms() Load all CFM Form classes.
	 * @uses CFM_Setup::load_fields() Load all CFM Field classes.
	 *
	 * @return void
	 */
	public function load_abstracts(){
		// load form abstract and extending forms
		self::$instance->load_forms 	= self::$instance->setup->load_forms();

		// load field abstract and extending fields
		self::$instance->load_fields 	= self::$instance->setup->load_fields();
	}

	/**
	 * Load CFM files
	 *
	 * This function loads the majority of CFM's files.
	 *
	 * @since 2.0.0
	 * @access public
	 * @todo Use better check for admin (cfm_is_admin())
	 *
	 * @return void
	 */
	public function includes() {
		require_once cfm_plugin_dir . 'classes/class-helpers.php';
		require_once cfm_plugin_dir . 'classes/class-emails.php';
		require_once cfm_plugin_dir . 'classes/misc-functions.php';

		if ( is_admin() ){
			require_once cfm_plugin_dir . 'classes/schema.php';
			require_once cfm_plugin_dir . 'classes/admin/class-update.php';
			require_once cfm_plugin_dir . 'classes/admin/customers/class-admin-customer-profile.php';
			require_once cfm_plugin_dir . 'classes/admin/formbuilder/class-formbuilder.php';
			require_once cfm_plugin_dir . 'classes/admin/formbuilder/class-formbuilder-templates.php';
			require_once cfm_plugin_dir . 'classes/admin/payments/class-edit-payment.php';
			require_once cfm_plugin_dir . 'classes/admin/reporting/class-export.php';
			require_once cfm_plugin_dir . 'classes/admin/class-tools.php';
			require_once cfm_plugin_dir . 'classes/admin/class-menu.php';
		}
		require_once cfm_plugin_dir . 'classes/frontend/class-checkout.php';
		require_once cfm_plugin_dir . 'classes/frontend/class-frontend-customer-profile.php';
	}

	/**
	 * Initial CFM setup
	 *
	 * This function sets up CFM's post type, runs the hooks/filters in the CFM_Setup
	 * class, loads the textdomain, loads the CFM settings, and registers EDD license checks
	 * for the CFM plugin.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @uses CFM_Setup::load_settings() Loads CFM settings
	 *
	 * @return void
	 */
	public function setup() {
		require_once cfm_plugin_dir . 'classes/class-post-types.php';
		require_once cfm_plugin_dir . 'classes/class-setup.php';

		// load textdomains
		$this->load_textdomain();

		self::$instance->setup = $this->setup = new CFM_Setup;

		// load license
		if ( class_exists( 'EDD_License' ) ) {
			$license = new EDD_License( __FILE__, cfm_plugin_name, cfm_plugin_version, 'Chris Christoff' );
		}
	}

	/**
	 * Load CFM Textdomain
	 *
	 * This function attempts to find CFM translation files and load them. It
	 * uses a system similar to EDD core's.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @return void
	 */
	public function load_textdomain() {
		// This filter is already documented in WordPress core
		$locale        = apply_filters( 'plugin_locale', get_locale(), 'edd_cfm' );

		$mofile        = sprintf( '%1$s-%2$s.mo', 'edd_cfm', $locale );

		$mofile_local  = trailingslashit( cfm_plugin_dir . 'languages' ) . $mofile;
		$mofile_global = WP_LANG_DIR . '/edd_cfm/' . $mofile;

		if ( file_exists( $mofile_global ) ) {
			return load_textdomain( 'edd_cfm', $mofile_global );
		} else if ( file_exists( $mofile_local ) ) {
			return load_textdomain( 'edd_cfm', $mofile_local );
		} else{
			load_plugin_textdomain( 'edd_cfm', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
		}
	}

	/**
	 * CFM minimum EDD version notice
	 *
	 * This function is used to throw an admin notice when the WordPress install
	 * does not meet CFM's minimum EDD version requirements.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @return void
	 */
	public static function edd_notice() { ?>
		<div class="updated">
			<p><?php printf( __( '<strong>Notice:</strong> Easy Digital Downloads Checkout Fields Manager requires Easy Digital Downloads 2.5 or higher in order to function properly.', 'edd_cfm' ) ); ?></p>
		</div>
		<?php
	}

	/**
	 * CFM minimum WP version notice
	 *
	 * This function is used to throw an admin notice when the WordPress install
	 * does not meet CFM's minimum WP version requirements.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @return void
	 */
	public static function wp_notice() { ?>
		<div class="updated">
			<p><?php printf( __( '<strong>Notice:</strong> Easy Digital Downloads Checkout Fields Manager requires WordPress 4.3 or higher in order to function properly.', 'edd_cfm' ) ); ?></p>
		</div>
		<?php
	}
}

/**
 * The main function responsible for returning the one true EDD_Checkout_Fields_Manager
 * Instance to functions everywhere.
 *
 * Use this function like you would a global variable, except without needing
 * to declare the global.
 *
 * Example: <?php $edd_cfm = EDD_CFM(); ?>
 *
 * @since 2.0.0
 *
 * @uses EDD_Checkout_Fields_Manager::instance() Retrieve CFM instance.
 *
 * @return EDD_Checkout_Fields_Manager The singleton CFM instance.
 */
function EDD_CFM() {
	return EDD_Checkout_Fields_Manager::instance();
}

EDD_CFM();

/**
 * CFM Install
 *
 * This function is used install CFM
 *
 * @since 2.0.0
 * @access public
 *
 * @global string $wp_version WordPress version (provided by WordPress core).
 * @uses EDD_Checkout_Fields_Manager::load_settings() Loads CFM settings
 * @uses CFM_Install::init() Runs install process
 *
 * @return void
 */
function CFM_Install() {
	global $wp_version;

	// If the WordPress site doesn't meet the correct EDD and WP version requirements, don't activate CFM
	if ( version_compare( $wp_version, '4.3', '<' ) ) {
		if ( is_plugin_active( EDD_CFM()->basename ) ) {
			return;
		}
	} else if ( !class_exists( 'Easy_Digital_Downloads' ) || version_compare( EDD_VERSION, '2.5', '<' ) ) {
		if ( is_plugin_active( EDD_CFM()->basename ) ) {
			return;
		}
	}

	// Load schema.php (contains initial CFM Form schema as well as install functions)
	require_once cfm_plugin_dir . 'classes/schema.php';

	// Load the CFM_Forms post type
	require_once cfm_plugin_dir . 'classes/class-post-types.php';

	// Load the CFM_Install class
	require_once cfm_plugin_dir . 'classes/admin/class-install.php';

	// Run the CFM install
	$install = new CFM_Install;
	$install->init();
}

/**
 * CFM check for update processes
 *
 * This function is used to call the CFM install class, which in turn
 * checks to see if there are any update procedures to be run, and if
 * so runs them
 *
 * @since 2.0.0
 * @access public
 *
 * @uses CFM_Install() Runs install process
 *
 * @return void
 */
function cfm_call_install(){
	add_action( 'wp_loaded', 'CFM_Install' );
}
