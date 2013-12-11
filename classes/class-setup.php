<?php
if ( !defined( 'ABSPATH' ) ) {
	exit;
}

class FES_Setup {
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
			'fes_version' 
		) );
		add_action( 'edd_system_info_after', array(
			 $this,
			'fes_add_below_system_info' 
		) );
	}
	
	public function is_wp_36_and_edd_activated() {
		global $wp_version;
		if ( version_compare( $wp_version, '3.6', '< ' ) ) {
			if ( is_plugin_active( EDD_FES()->basename ) ) {
				deactivate_plugins( EDD_FES()->basename );
				unset( $_GET[ 'activate' ] );
				add_action( 'admin_notices', array(
					 $this,
					'wp_notice' 
				) );
			}
		} else if ( !class_exists( 'Easy_Digital_Downloads' ) || ( version_compare( EDD_VERSION, '1.8' ) < 0 ) ) {
			if ( is_plugin_active( EDD_FES()->basename ) ) {
				deactivate_plugins( EDD_FES()->basename );
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
		printf( __( '<strong>Notice:</strong> Easy Digital Downloads Frontend Submissions requires Easy Digital Downloads 1.8 or higher in order to function properly.', 'edd_fes' ) );
?>
		</p>
	</div>
	<?php
	}
	public function wp_notice() {
?>
	<div class="updated">
		<p><?php
		printf( __( '<strong>Notice:</strong> Easy Digital Downloads Frontend Submissions requires WordPress 3.6 or higher in order to function properly.', 'edd_fes' ) );
?>
		</p>
	</div>
	<?php
	}
	
	public function load_textdomain() {
		load_plugin_textdomain( 'edd_fes', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}
	
	public function enqueue_scripts() {
		if ( is_admin() ) {
			return;
		}
		global $post;
		if ( is_page( EDD_FES()->fes_options->get_option( 'vendor-dashboard-page' ) ) ) {
			$scheme = is_ssl() ? 'https' : 'http';
			wp_enqueue_script( 'jquery' );
			wp_enqueue_script( 'google-maps', $scheme . '://maps.google.com/maps/api/js?sensor=true' );
			wp_enqueue_style( 'fes-css', fes_plugin_url . 'assets/css/frontend.css' );
			wp_enqueue_script( 'fes-form', fes_plugin_url . 'assets/js/frontend-form.js', array(
				 'jquery' 
			) );
			wp_enqueue_script( 'jquery-ui-datepicker' );
			wp_enqueue_script( 'jquery-ui-autocomplete' );
			wp_enqueue_script( 'suggest' );
			wp_enqueue_script( 'jquery-ui-slider' );
			wp_enqueue_script( 'plupload-handlers' );
			wp_enqueue_script( 'zxcvbn', includes_url( '/js/zxcvbn.min.js' ) );
			wp_enqueue_script( 'jquery-ui-timepicker', fes_plugin_url . 'assets/js/jquery-ui-timepicker-addon.js', array(
				 'jquery-ui-datepicker' 
			) );
			wp_enqueue_script( 'fes-upload', fes_plugin_url . 'assets/js/upload.js', array(
				 'jquery',
				'plupload-handlers' 
			) );
			wp_localize_script( 'fes-form', 'fes_frontend', array(
				 'ajaxurl' => admin_url( 'admin-ajax.php' ),
				'error_message' => __( 'Please fix the errors to proceed', 'edd_fes' ),
				'nonce' => wp_create_nonce( 'fes_nonce' ) 
			) );
			wp_localize_script( 'fes-upload', 'fes_frontend_upload', array(
				 'confirmMsg' => __( 'Are you sure?', 'edd_fes' ),
				'nonce' => wp_create_nonce( 'fes_nonce' ),
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
				'plupload' => array(
					 'url' => admin_url( 'admin-ajax.php' ) . '?nonce=' . wp_create_nonce( 'fes_featured_img' ),
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
		if ( $current_screen->post_type === 'fes-forms' || $current_screen->post_type === 'download' || $pagenow == 'profile.php' ) {
			$scheme = is_ssl() ? 'https' : 'http';
			wp_enqueue_script( 'google-maps', $scheme . '://maps.google.com/maps/api/js?sensor=true' );
			wp_register_script( 'jquery-tiptip', fes_plugin_url . 'assets/js/jquery-tiptip/jquery.tipTip.min.js', array(
				 'jquery' 
			), '2.0', true );
			wp_enqueue_script( 'edd-fes-admin-js', fes_plugin_url . 'assets/js/admin.js', array(
				 'jquery',
				'jquery-tiptip' 
			), '2.0', true );
			wp_enqueue_script( 'jquery-ui-autocomplete' );
			wp_enqueue_script( 'jquery-smallipop', fes_plugin_url . 'assets/js/jquery.smallipop-0.4.0.min.js', array(
				 'jquery' 
			) );
			wp_enqueue_script( 'fes-formbuilder', fes_plugin_url . 'assets/js/formbuilder.js', array(
				 'jquery',
				'jquery-ui-sortable' 
			) );
			wp_enqueue_script( 'fes-upload', fes_plugin_url . 'assets/js/upload.js', array(
				 'jquery',
				'plupload-handlers' 
			) );
			wp_enqueue_script( 'jquery-ui-datepicker' );
			wp_enqueue_script( 'jquery-ui-autocomplete' );
			wp_enqueue_script( 'suggest' );
			wp_enqueue_script( 'jquery-ui-slider' );
			wp_localize_script( 'fes-form', 'fes_frontend', array(
				 'ajaxurl' => admin_url( 'admin-ajax.php' ),
				'error_message' => __( 'Please fix the errors to proceed', 'edd_fes' ),
				'nonce' => wp_create_nonce( 'fes_nonce' ) 
			) );
			wp_localize_script( 'fes-upload', 'fes_frontend_upload', array(
				 'confirmMsg' => __( 'Are you sure?', 'edd_fes' ),
				'nonce' => wp_create_nonce( 'fes_nonce' ),
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
				'plupload' => array(
					 'url' => admin_url( 'admin-ajax.php' ) . '?nonce=' . wp_create_nonce( 'fes_featured_img' ),
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
		if ( $current_screen->post_type === 'fes-forms' || $current_screen->post_type === 'download' ) {
			wp_enqueue_style( 'edd-fes-admin-css', fes_plugin_url . 'assets/css/admin.css' );
			wp_enqueue_style( 'jquery-smallipop', fes_plugin_url . 'assets/css/jquery.smallipop.css' );
			wp_enqueue_style( 'fes-formbuilder', fes_plugin_url . 'assets/css/formbuilder.css' );
			wp_enqueue_style( 'jquery-ui-core', fes_plugin_url . 'assets/css/jquery-ui-1.9.1.custom.css' );
		}
	}
	
	public function enqueue_styles() {
		if ( is_admin() ) {
			return;
		}
		global $post;
		if ( is_page( EDD_FES()->fes_options->get_option( 'vendor-dashboard-page' ) ) && EDD_FES()->fes_options->get_option( 'edd_fes_use_css' ) ) {
			wp_enqueue_style( 'fes-css', fes_plugin_url . 'assets/css/frontend.css' );
			wp_enqueue_style( 'jquery-ui', fes_plugin_url . 'assets/css/jquery-ui-1.9.1.custom.css' );
		}
	}
	
	public function fes_add_below_system_info() {
		echo "\n\n\nFrontend Submissions System Info \n";
		echo "FES version: " . fes_plugin_version . "\n";
		echo "\nFES Settings: \n";
		echo print_r( EDD_FES()->fes_options ) . "\n";
		echo "\n\n";
	}
	
	public function fes_version() {
		// Newline on both sides to avoid being in a blob
		echo '<meta name="generator" content="EDD FES v' . fes_plugin_version . '" />' . "\n";
	}

	public function register_post_type() {
		$capability = 'manage_options';
		register_post_type( 'fes-forms', array(
			'label' => __( 'EDD FES Forms', 'edd_fes' ),
			'public' => false,
			'show_ui' => true,
			'rewrites' => false,
			'show_in_menu' => 'fes-admin-opt',
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
				 'name' => __( 'EDD FES Forms', 'edd_fes' ),
				'singular_name' => __( 'FES Form', 'edd_fes' ),
				'menu_name' => __( 'FES Forms', 'edd_fes' ),
				'add_new' => __( 'Add FES Form', 'edd_fes' ),
				'add_new_item' => __( 'Add New Form', 'edd_fes' ),
				'edit' => __( 'Edit', 'edd_fes' ),
				'edit_item' => __( '', 'edd_fes' ),
				'new_item' => __( 'New FES Form', 'edd_fes' ),
				'view' => __( 'View FES Form', 'edd_fes' ),
				'view_item' => __( 'View FES Form', 'edd_fes' ),
				'search_items' => __( 'Search FES Forms', 'edd_fes' ),
				'not_found' => __( 'No FES Forms Found', 'edd_fes' ),
				'not_found_in_trash' => __( 'No FES Forms Found in Trash', 'edd_fes' ),
				'parent' => __( 'Parent FES Form', 'edd_fes' ) 
			) 
		) );
	}
}