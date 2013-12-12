<?php
if ( !defined( 'ABSPATH' ) ) {
	exit;
}

class CFM_Setup {
	public function __construct() {
		add_action( 'admin_init', array(
			 $this,
			'is_wp_36_and_edd_activated' 
		), 1 );
		add_action( 'init', array(
			 $this,
			'register_post_type' 
		) );
		add_action( 'plugins_loaded', array(
			 $this,
			'load_textdomain' 
		) );
		add_action( 'wp_enqueue_scripts', array(
			 $this,
			'enqueue_scripts' 
		) );
		add_action( 'wp_enqueue_scripts', array(
			 $this,
			'enqueue_styles' 
		) );
		add_action( 'admin_enqueue_scripts', array(
			 $this,
			'admin_enqueue_scripts' 
		) );
		add_action( 'admin_enqueue_scripts', array(
			 $this,
			'admin_enqueue_styles' 
		) );
		add_action( 'wp_head', array(
			 $this,
			'cfm_version' 
		) );
	}
	public function is_wp_36_and_edd_activated() {
		global $wp_version;
		if ( version_compare( $wp_version, '3.6', '< ' ) ) {
			if ( is_plugin_active( EDD_cfm()->basename ) ) {
				deactivate_plugins( EDD_cfm()->basename );
				unset( $_GET[ 'activate' ] );
				add_action( 'admin_notices', array(
					 $this,
					'wp_notice' 
				) );
			}
		} else if ( !class_exists( 'Easy_Digital_Downloads' ) || ( version_compare( EDD_VERSION, '1.8' ) < 0 ) ) {
			if ( is_plugin_active( EDD_cfm()->basename ) ) {
				deactivate_plugins( EDD_cfm()->basename );
				unset( $_GET[ 'activate' ] );
				add_action( 'admin_notices', array(
					 $this,
					'edd_notice' 
				) );
			}
		}
	}
	
	public function edd_notice() {
?>
	<div class="updated">
		<p><?php
		printf( __( '<strong>Notice:</strong> Easy Digital Downloads Checkout Fields Manager requires Easy Digital Downloads 1.8 or higher in order to function properly.', 'edd_cfm' ) );
?>
		</p>
	</div>
	<?php
	}
	public function wp_notice() {
?>
	<div class="updated">
		<p><?php
		printf( __( '<strong>Notice:</strong> Easy Digital Downloads Checkout Fields Manager requires WordPress 3.6 or higher in order to function properly.', 'edd_cfm' ) );
?>
		</p>
	</div>
	<?php
	}
	
	public function load_textdomain() {
		load_plugin_textdomain( 'edd_cfm', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}
	
	public function enqueue_scripts() {
		if ( is_admin() ) {
			return;
		}
		global $post;
		if ( edd_is_checkout() ) {
			$scheme = is_ssl() ? 'https' : 'http';
			wp_enqueue_script( 'jquery' );
			wp_enqueue_style( 'cfm-css', cfm_plugin_url . 'assets/css/frontend.css' );
			wp_enqueue_script( 'cfm-form', cfm_plugin_url . 'assets/js/frontend-form.js', array(
				 'jquery' 
			) );
			wp_enqueue_script( 'jquery-ui-datepicker' );
			wp_enqueue_script( 'jquery-ui-autocomplete' );
			wp_enqueue_script( 'suggest' );
			wp_enqueue_script( 'jquery-ui-slider' );
			wp_enqueue_script( 'plupload-handlers' );
			wp_enqueue_script( 'jquery-ui-timepicker', cfm_plugin_url . 'assets/js/jquery-ui-timepicker-addon.js', array(
				 'jquery-ui-datepicker' 
			) );
			wp_enqueue_script( 'cfm-upload', cfm_plugin_url . 'assets/js/upload.js', array(
				 'jquery',
				'plupload-handlers' 
			) );
			wp_localize_script( 'cfm-form', 'cfm_frontend', array(
				 'ajaxurl' => admin_url( 'admin-ajax.php' ),
				'error_message' => __( 'Please fix the errors to proceed', 'edd_cfm' ),
				'nonce' => wp_create_nonce( 'cfm_nonce' ) 
			) );
			wp_localize_script( 'cfm-upload', 'cfm_frontend_upload', array(
				 'confirmMsg' => __( 'Are you sure?', 'edd_cfm' ),
				'nonce' => wp_create_nonce( 'cfm_nonce' ),
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
				'plupload' => array(
					 'url' => admin_url( 'admin-ajax.php' ) . '?nonce=' . wp_create_nonce( 'cfm_featured_img' ),
					'flash_swf_url' => includes_url( 'js/plupload/plupload.flash.swf' ),
					'filters' => array(
						 array(
							 'title' => __( 'Allowed Files' ),
							'extensions' => '*' 
						) 
					),
					'multipart' => true,
					'urlstream_upload' => true 
				) 
			) );
		}
	}
	
	public function admin_enqueue_scripts() {
		if ( !is_admin() ) {
			return;
		}
		global $pagenow, $post;
		$current_screen = get_current_screen();
		if ( $current_screen->post_type === 'edd-checkout-fields' || $current_screen->post_type === 'shop_orders' ) {
			$scheme = is_ssl() ? 'https' : 'http';
			wp_register_script( 'jquery-tiptip', cfm_plugin_url . 'assets/js/jquery-tiptip/jquery.tipTip.min.js', array(
				 'jquery' 
			), '2.0', true );
			wp_enqueue_script( 'jquery-ui-autocomplete' );
			wp_enqueue_script( 'jquery-smallipop', cfm_plugin_url . 'assets/js/jquery.smallipop-0.4.0.min.js', array(
				 'jquery' 
			) );
			wp_enqueue_script( 'cfm-formbuilder', cfm_plugin_url . 'assets/js/formbuilder.js', array(
				 'jquery',
				'jquery-ui-sortable' 
			) );
			wp_enqueue_script( 'cfm-upload', cfm_plugin_url . 'assets/js/upload.js', array(
				 'jquery',
				'plupload-handlers' 
			) );
			wp_enqueue_script( 'jquery-ui-datepicker' );
			wp_enqueue_script( 'jquery-ui-autocomplete' );
			wp_enqueue_script( 'suggest' );
			wp_enqueue_script( 'jquery-ui-slider' );
			wp_localize_script( 'cfm-form', 'cfm_frontend', array(
				 'ajaxurl' => admin_url( 'admin-ajax.php' ),
				'error_message' => __( 'Please fix the errors to proceed', 'edd_cfm' ),
				'nonce' => wp_create_nonce( 'cfm_nonce' ) 
			) );
			wp_localize_script( 'cfm-upload', 'cfm_frontend_upload', array(
				 'confirmMsg' => __( 'Are you sure?', 'edd_cfm' ),
				'nonce' => wp_create_nonce( 'cfm_nonce' ),
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
				'plupload' => array(
					 'url' => admin_url( 'admin-ajax.php' ) . '?nonce=' . wp_create_nonce( 'cfm_featured_img' ),
					'flash_swf_url' => includes_url( 'js/plupload/plupload.flash.swf' ),
					'filters' => array(
						 array(
							 'title' => __( 'Allowed Files' ),
							'extensions' => '*' 
						) 
					),
					'multipart' => true,
					'urlstream_upload' => true 
				) 
			) );
		}
	}
	
	public function admin_enqueue_styles() {
		if ( !is_admin() ) {
			return;
		}
		$current_screen = get_current_screen();
		if ( $current_screen->post_type === 'edd-checkout-fields' || $current_screen->post_type === 'shop_orders' ) {
			wp_enqueue_style( 'jquery-smallipop', cfm_plugin_url . 'assets/css/jquery.smallipop.css' );
			wp_enqueue_style( 'cfm-formbuilder', cfm_plugin_url . 'assets/css/formbuilder.css' );
			wp_enqueue_style( 'jquery-ui-core', cfm_plugin_url . 'assets/css/jquery-ui-1.9.1.custom.css' );
		}
	}
	
	public function enqueue_styles() {
		if ( is_admin() ) {
			return;
		}
		if ( edd_is_checkout() ) {
			wp_enqueue_style( 'cfm-css', cfm_plugin_url . 'assets/css/frontend.css' );
			wp_enqueue_style( 'jquery-ui', cfm_plugin_url . 'assets/css/jquery-ui-1.9.1.custom.css' );
		}
	}
	
	public function cfm_version() {
		// Newline on both sides to avoid being in a blob
		echo '<meta name="generator" content="EDD CFM v' . cfm_plugin_version . '" />' . "\n";
	}

	public function register_post_type() {
		$capability = 'manage_options';
		register_post_type( 'edd-checkout-fields', array(
			'label' => __( 'EDD CFM Forms', 'edd_cfm' ),
			'public' => false,
			'show_ui' => true,
			'rewrites' => false,
			'show_in_menu' => 'cfm-admin-opt',
			'capability_type' => 'post',
			'capabilities' => array(
				 'publish_posts' => 'cap_that_doesnt_exist',
				'edit_posts' => $capability,
				'edit_others_posts' => $capability,
				'delete_posts' => 'cap_that_doesnt_exist',
				'delete_others_posts' => 'cap_that_doesnt_exist',
				'read_private_posts' => 'cap_that_doesnt_exist',
				'edit_post' => $capability,
				'delete_post' => 'cap_that_doesnt_exist',
				'read_post' => $capability,
				'create_posts' => 'cap_that_doesnt_exist' 
			),
			'hierarchical' => false,
			'query_var' => false,
			'supports' => array(
				 'title' 
			),
			'labels' => array(
				 'name' => __( 'EDD cfm Forms', 'edd_cfm' ),
				'singular_name' => __( 'cfm Form', 'edd_cfm' ),
				'menu_name' => __( 'cfm Forms', 'edd_cfm' ),
				'add_new' => __( 'Add cfm Form', 'edd_cfm' ),
				'add_new_item' => __( 'Add New Form', 'edd_cfm' ),
				'edit' => __( 'Edit', 'edd_cfm' ),
				'edit_item' => __( '', 'edd_cfm' ),
				'new_item' => __( 'New cfm Form', 'edd_cfm' ),
				'view' => __( 'View cfm Form', 'edd_cfm' ),
				'view_item' => __( 'View cfm Form', 'edd_cfm' ),
				'search_items' => __( 'Search cfm Forms', 'edd_cfm' ),
				'not_found' => __( 'No cfm Forms Found', 'edd_cfm' ),
				'not_found_in_trash' => __( 'No cfm Forms Found in Trash', 'edd_cfm' ),
				'parent' => __( 'Parent cfm Form', 'edd_cfm' ) 
			) 
		) );
	}
}