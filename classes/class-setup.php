<?php
/**
 * CFM Setup
 *
 * This file contains code that needs to run
 * before most other things in CFM
 *
 * @package CFM
 * @subpackage Setup
 * @since 2.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) { 
	exit;
}

/**
 * CFM Setup.
 *
 * This file contains code that needs to run
 * before most other things in CFM
 *
 * @since 2.0.0
 * @access public
 */
class CFM_Setup {

	/**
	 * CFM Setup action/filters.
	 *
	 * Registers the actions and filters.
	 *
	 * @since 2.3.0
	 * @access public
	 * 
	 * @return void
	 */	
	public function __construct() {
		add_action( 'wp_enqueue_scripts',	 array( $this, 'enqueue_scripts' ) );
		add_action( 'wp_enqueue_scripts',	 array( $this, 'enqueue_styles'  ) );
		add_action( 'admin_enqueue_scripts', array(	$this, 'admin_enqueue_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_styles' ) );
		add_action( 'wp_head', 				 array( $this, 'cfm_version' ) );
		add_action( 'admin_notices', 		 array( $this, 'no_checkout_form_set' ) );
	}

	/**
	 * CFM No Vendor Dashboard Set Notice.
	 *
	 * Shows an admin notice if the vendor dashboard
	 * page isn't set in the CFM settings.
	 *
	 * @since 2.3.0
	 * @access public
	 * 
	 * @return void
	 */
	public function no_checkout_form_set(){
		if ( ! get_option( 'cfm-checkout-form', false ) ){
			echo '<div class="error"><p>';
				echo __( 'Warning: The checkout form isn\'t set. Go to EDD->Tools->Checkout Fields Manager, and reset the checkout form.', 'edd_cfm' );
				echo '</p>';
			echo '</div>';
		}
	}

	/**
	 * CFM Enqueue Form Assets.
	 *
	 * This function can be manually called
	 * to enqueue the scripts and styles needed for
	 * CFM forms.
	 *
	 * @since 2.0.0
	 * @access public
	 * 
	 * @return void
	 */
	public function enqueue_form_assets() {
		EDD_CFM()->setup->enqueue_styles( true );
		EDD_CFM()->setup->enqueue_scripts( true );
	}

	/**
	 * CFM Enqueue Scripts.
	 *
	 * Loads the scripts CFM needs on the
	 * frontend.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @param bool $override If true load on page even if the
	 *                       page isn't the vendor dashboard.
	 * @return void
	 */
	public function enqueue_scripts( $override = false ) {
		if ( !cfm_is_frontend() ) {
			return;
		}
		global $post;
		if ( is_page( get_option( 'cfm-checkout-form', false ) ) || $override ) {
			wp_enqueue_script( 'jquery' );
			wp_enqueue_script( 'underscore' );
			// CFM outputs minified scripts by default on the frontend. To load full versions, hook into this and return empty string.
			$minify = ( defined( 'WP_DEBUG' ) && WP_DEBUG ) || ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
			$minify = apply_filters( 'cfm_output_minified_versions', $minify );
			wp_enqueue_script( 'cfm_form', cfm_plugin_url . 'assets/js/frontend-form' . $minify . '.js', array(
					'jquery'
				), cfm_plugin_version );

			$options = array(
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
				'error_message' => __( 'Please fix the errors to proceed', 'edd_cfm' ),
				'nonce' => wp_create_nonce( 'cfm_nonce' ),
				'file_title' =>  __( 'Choose a file', 'edd_cfm' ),
				'file_button' =>  __( 'Insert file URL', 'edd_cfm' ),
				'too_many_files_pt_1' => __( 'You may not add more than ', 'edd_cfm' ),
				'too_many_files_pt_2' => __( ' files!', 'edd_cfm' ),
			);
			
			$options = apply_filters( 'cfm_forms_options_frontend', $options );
			wp_localize_script( 'cfm_form', 'cfm_form', $options );
			wp_enqueue_media();
			wp_enqueue_script( 'comment-reply' );
			wp_enqueue_script( 'jquery-ui-datepicker' );
			wp_enqueue_script( 'jquery-ui-autocomplete' );
			wp_enqueue_script( 'suggest' );
			wp_enqueue_script( 'jquery-ui-slider' );
			wp_enqueue_script( 'jquery-ui-timepicker', cfm_plugin_url . 'assets/js/jquery-ui-timepicker-addon.js', array( 'jquery-ui-datepicker' ) );
		}
	}

	/**
	 * CFM Enqueue Styles.
	 *
	 * Loads the styles CFM needs on the
	 * frontend.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @param bool $override If true load on page even if the
	 *                       page isn't the vendor dashboard.
	 * @return void
	 */
	public function enqueue_styles( $override = false ) {
		if ( !cfm_is_frontend() ) {
			return;
		}
		global $post;
		if ( is_page( get_option( 'cfm-checkout-form', false ) ) || $override ) {
			// CFM outputs minified scripts by default on the frontend. To load full versions, hook into this and return empty string.
			$minify = ( defined( 'WP_DEBUG' ) && WP_DEBUG ) || ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
			$minify = apply_filters( 'cfm_output_minified_versions', $minify );
			if ( EDD_CFM()->helper->get_option( 'cfm-use-css', true ) ) {
				wp_enqueue_style( 'cfm-css', cfm_plugin_url . 'assets/css/frontend' . $minify . '.css' );
			}
			wp_enqueue_style( 'jquery-ui', cfm_plugin_url . 'assets/css/jquery-ui-1.9.1.custom.css' );
		}
	}

	/**
	 * CFM Admin Enqueue Scripts.
	 *
	 * Loads the scripts CFM needs on the
	 * admin.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @return void
	 */
	public function admin_enqueue_scripts() {
		if ( !cfm_is_admin() ) {
			return;
		}
		$current_screen = get_current_screen();
		$is_cfm_page = false;
		$is_formbuilder = false;

		if ( is_object( $current_screen ) && isset( $current_screen->post_type ) && $current_screen->post_type === 'edd-checkout-fields' ) { 
			$is_cfm_page    = true;
			$is_formbuilder = true;
		} else if ( is_object( $current_screen ) && isset( $current_screen->post_type ) && $current_screen->post_type === 'edd_payment' ) { 
			$is_cfm_page    = true;
			$is_formbuilder = false;
		} 

		if ( $is_cfm_page ){
			wp_enqueue_script( 'jquery' );
			wp_enqueue_script( 'jquery-ui-autocomplete' );
			wp_enqueue_script( 'jquery-smallipop', cfm_plugin_url . 'assets/js/jquery.smallipop-0.4.0.min.js', array( 'jquery' ) );
			if ( $is_formbuilder ) {
				wp_enqueue_script( 'cfm-formbuilder', cfm_plugin_url . 'assets/js/formbuilder.js', array( 'jquery', 'jquery-ui-sortable' ) );
			}
			wp_register_script( 'jquery-tiptip', cfm_plugin_url . 'assets/js/jquery-tiptip/jquery.tipTip.min.js', array( 'jquery' ), '2.0', true );
			wp_enqueue_script( 'jquery-ui-autocomplete' );
			wp_enqueue_script( 'underscore' );
			wp_enqueue_script( 'cfm_form', cfm_plugin_url . 'assets/js/frontend-form.js', array( 'jquery' ) );

			$options = array(
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
				'error_message' => __( 'Please fix the errors to proceed', 'edd_cfm' ),
				'nonce' => wp_create_nonce( 'cfm_nonce' ),
				'file_title' =>  __( 'Choose a file', 'edd_cfm' ),
				'file_button' =>  __( 'Insert file URL', 'edd_cfm' ),
				'too_many_files_pt_1' => __( 'You may not add more than ', 'edd_cfm' ),
				'too_many_files_pt_2' => __( ' files!', 'edd_cfm' ),
			);
			
			$options = apply_filters( 'cfm_cfm_forms_options_admin', $options );
			wp_localize_script( 'cfm_form', 'cfm_form', $options );
			wp_enqueue_script( 'jquery-ui-datepicker' );
			wp_enqueue_script( 'jquery-ui-autocomplete' );
			wp_enqueue_script( 'suggest' );
			wp_enqueue_script( 'jquery-ui-slider' );
			wp_enqueue_script( 'jquery-ui-timepicker', cfm_plugin_url . 'assets/js/jquery-ui-timepicker-addon.js', array( 'jquery-ui-datepicker' ) );
			wp_register_script( 'jquery-chosen', EDD_PLUGIN_URL . 'assets/js/chosen.jquery.js', array( 'jquery' ), EDD_VERSION );
			wp_enqueue_script( 'jquery-chosen' );
		}
	}

	/**
	 * CFM Admin Enqueue Styles.
	 *
	 * Loads the styles CFM needs on the
	 * admin.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @return void
	 */
	public function admin_enqueue_styles() {
		if ( !cfm_is_admin() ) {
			return;
		}

		$current_screen = get_current_screen();
		$is_cfm_page    = false;
		$is_formbuilder = false;
		if ( is_object( $current_screen ) && isset( $current_screen->post_type ) && $current_screen->post_type === 'edd-checkout-fields' ) { 
			$is_cfm_page    = true;
			$is_formbuilder = true;
		} else if ( is_object( $current_screen ) && isset( $current_screen->post_type ) && $current_screen->post_type === 'edd_payment' ) { 
			$is_cfm_page    = true;
			$is_formbuilder = false;
		} 

		if ( $is_cfm_page ){
			if ( $is_formbuilder ) {
				wp_enqueue_style( 'cfm-formbuilder', cfm_plugin_url . 'assets/css/formbuilder.css' );
			}
			edd_register_styles();
			wp_enqueue_style( 'cfm-css', cfm_plugin_url . 'assets/css/frontend.css' );
			wp_enqueue_style( 'cfm-admin-css', cfm_plugin_url . 'assets/css/admin.css' );
			wp_enqueue_style( 'jquery-smallipop', cfm_plugin_url . 'assets/css/jquery.smallipop.css' );
			wp_enqueue_style( 'jquery-ui-core', cfm_plugin_url . 'assets/css/jquery-ui-1.9.1.custom.css' );
			wp_enqueue_style( 'cfm-sw-css', cfm_plugin_url . 'assets/css/spin.css' );
			wp_enqueue_style( 'cfm-spin-css', cfm_plugin_url . 'assets/css/sw.css' );
			wp_register_style( 'jquery-chosen', EDD_PLUGIN_URL . 'assets/css/chosen.css', array(), EDD_VERSION );
			wp_enqueue_style( 'jquery-chosen' );
		}
	}

	/**
	 * CFM Version meta generator.
	 *
	 * Outputs CFM version for support
	 * purposes.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @return void
	 */
	public function cfm_version() {
		// Newline on both sides to avoid being in a blob
		echo '<meta name="generator" content="EDD CFM v' . cfm_plugin_version . '" />' . "\n";
	}

	/**
	 * CFM Load Fields.
	 *
	 * Loads the abstract and then all of the extended
	 * CFM Fields.
	 *
	 * @since 2.3.0
	 * @access public
	 *
	 * @return void
	 */
	public function load_fields() {
		// require abstract
		require_once cfm_plugin_dir . 'classes/abstracts/class-field.php';

		// require fields
		require_once cfm_plugin_dir . 'classes/fields/text.php';
		require_once cfm_plugin_dir . 'classes/fields/textarea.php';
		require_once cfm_plugin_dir . 'classes/fields/action_hook.php';
		//require_once cfm_plugin_dir . 'classes/fields/birthday.php';
		require_once cfm_plugin_dir . 'classes/fields/checkbox.php';
		require_once cfm_plugin_dir . 'classes/fields/country.php';
		require_once cfm_plugin_dir . 'classes/fields/date.php';
		require_once cfm_plugin_dir . 'classes/fields/email.php';
		//require_once cfm_plugin_dir . 'classes/fields/file_upload.php';
		require_once cfm_plugin_dir . 'classes/fields/first_name.php';
		require_once cfm_plugin_dir . 'classes/fields/hidden.php';
		require_once cfm_plugin_dir . 'classes/fields/multiselect.php';
		require_once cfm_plugin_dir . 'classes/fields/honeypot.php';
		require_once cfm_plugin_dir . 'classes/fields/html.php';
		require_once cfm_plugin_dir . 'classes/fields/last_name.php';
		require_once cfm_plugin_dir . 'classes/fields/radio.php';
		require_once cfm_plugin_dir . 'classes/fields/recaptcha.php';
		require_once cfm_plugin_dir . 'classes/fields/repeat.php';
		require_once cfm_plugin_dir . 'classes/fields/section_break.php';
		require_once cfm_plugin_dir . 'classes/fields/select.php';
		require_once cfm_plugin_dir . 'classes/fields/toc.php';
		require_once cfm_plugin_dir . 'classes/fields/url.php';
		//require_once cfm_plugin_dir . 'classes/fields/user_email.php';

		/**
		 * CFM Load Fields Require
		 *
		 * To add a custom CFM Field, you should hook into this 
		 * action and require_once your field here. Warning to devs:
		 * See "Planned Potentially Breaking Changes" section in README.
		 *
		 * @since 2.1.0
		 */
		//do_action( 'cfm_load_fields_require' );

		/**
		 * CFM Load Fields Array
		 *
		 * To add a custom CFM Field, you should hook into this 
		 * filter and add your template -> class relationship.
		 *
		 * @since 2.1.0
		 *
		 * @param array $fields Template -> Class array.
		 */
		//$fields = apply_filters( 'cfm_load_fields_array',
		$fields = 	array(
			'action_hook'		  => 'CFM_Action_Hook_Field',
			//'birthday'		 	  => 'CFM_Birthday_Field',
			'checkbox'			  => 'CFM_Checkbox_Field',
			'country'			  => 'CFM_Country_Field',
			'date'				  => 'CFM_Date_Field',
			'email'		 		  => 'CFM_Email_Field',
			//'file_upload'		  => 'CFM_File_Upload_Field',
			'first_name'		  => 'CFM_First_Name_Field',
			'hidden'			  => 'CFM_Hidden_Field',
			'honeypot'			  => 'CFM_Honeypot_Field',
			'html'				  => 'CFM_HTML_Field',
			'last_name'			  => 'CFM_Last_Name_Field',
			'multiselect'		  => 'CFM_Multiselect_Field',
			'radio'		 		  => 'CFM_Radio_Field',
			'recaptcha'			  => 'CFM_Recaptcha_Field',
			'repeat'			  => 'CFM_Repeat_Field',
			'section_break'       => 'CFM_Section_Break_Field',
			'select'        	  => 'CFM_Select_Field',
			'text'          	  => 'CFM_Text_Field',
			'textarea'        	  => 'CFM_Textarea_Field',
			'toc'                 => 'CFM_Toc_Field',
			'url'           	  => 'CFM_Url_Field',
			//'user_email'          => 'CFM_User_Email_Field',
		);
		//);
		return $fields;
	}

	/**
	 * CFM Load Forms.
	 *
	 * Loads the abstract and then all of the extended
	 * CFM Forms.
	 *
	 * @since 2.3.0
	 * @access public
	 *
	 * @return void
	 */
	public function load_forms() {
		// require abstract
		require_once cfm_plugin_dir . 'classes/abstracts/class-form.php';

		// require forms
		require_once cfm_plugin_dir . 'classes/forms/checkout.php';

		// do_action( 'cfm_load_forms_require' ); Allow starting 2.4

		// get names ( name -> class)
		//$forms = apply_filters( 'cfm_load_forms_array', array( 
		$forms = array(
			'checkout'	 => 'CFM_Checkout_Form'
		);
		//) );

		return $forms;
	}
}
