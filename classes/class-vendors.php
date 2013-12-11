<?php
if ( !defined( 'ABSPATH' ) ) {
	exit;
}

class FES_Vendors {
	function __construct() {
		add_action( 'admin_init', array(
			 $this,
			'fes_prevent_admin_access' 
		), 1000 );
		add_action( 'add_meta_boxes', array(
			 &$this,
			'change_author_meta_box_title' 
		) );
		add_filter( 'edd_use_35_media_ui', array(
			 $this,
			'fes_is_s3_active_num' 
		) );
		add_action( 'template_redirect', array( 
			$this,
			'vendor_archive_redirect' 
		) );
	}
	public function fes_prevent_admin_access() {
		if (
			// Look for the presence of /wp-admin/ in the url
			stripos( $_SERVER[ 'REQUEST_URI' ], '/wp-admin/' ) !== false && 
			// Allow calls to async-upload.php
			stripos( $_SERVER[ 'REQUEST_URI' ], 'async-upload.php' ) == false && 
			// Allow calls to media-upload.php
			stripos( $_SERVER[ 'REQUEST_URI' ], 'media-upload.php' ) == false && 
			// Allow calls to admin-ajax.php
			stripos( $_SERVER[ 'REQUEST_URI' ], 'admin-ajax.php' ) == false ) {
			$user_id = get_current_user_id();
			if ( $user_id != 0 ) {
				$user = new WP_User( $user_id );
				if ( !empty( $user->roles ) && is_array( $user->roles ) && in_array( 'frontend_vendor', $user->roles ) && count( $user->roles ) == '1' ) {
					wp_safe_redirect( get_permalink( EDD_FES()->fes_options->get_option( 'vendor-dashboard-page' ) ) );
					exit();
				}
			}
		}
	}
	
	/**
	 * Checks whether the ID provided is vendor capable or not
	 * This method is deprecated. Use the one in the vendor permissions class instead. 
	 * Will be removed in 2.1.
	 * @param int     $user_id
	 * @return bool
	 */
	public static function is_vendor( $user_id ) {
		$user      = get_userdata( $user_id );
		$role      = !empty( $user->roles ) ? array_shift( $user->roles ) : false;
		$is_vendor = $role == 'frontend_vendor';
		return apply_filters( 'edd_fes_is_vendor', $is_vendor, $user_id );
	}
	
	/**
	 * Grabs the vendor ID whether a username or an int is provided
	 * and returns the vendor_id if it's actually a vendor
	 * This method is deprecated. Use the one in the vendor permissions class instead. 
	 * Will be removed in 2.1.
	 * @param unknown $input
	 * @return unknown
	 */
	public static function get_vendor_id( $input ) {
		$int_vendor = (int) $input;
		$vendor     = !empty( $int_vendor ) ? get_userdata( $input ) : get_user_by( 'login', $input );
		if ( !$vendor )
			return false;
		$vendor_id = $vendor->ID;
		if ( self::is_vendor( $vendor_id ) ) {
			return $vendor_id;
		} else {
			return false;
		}
	}
	
	public static function is_pending( $user_id ) {
		$user       = get_userdata( $user_id );
		$role       = !empty( $user->roles ) ? array_shift( $user->roles ) : false;
		$is_pending = ( $role == 'pending_vendor' );
		return $is_pending;
	}
	
	public static function is_commissions_active() {
		if ( !defined( 'EDDC_PLUGIN_DIR' ) ) {
			return false;
		} else {
			return true;
		}
	}
	
	public function is_s3_active() {
		if ( defined( 'EDD_AS3_VERSION' ) ) {
			return true;
		} else {
			return false;
		}
	}
	
	public function fes_is_s3_active_num() {
		if ( defined( 'EDD_AS3_VERSION' ) ) {
			return 3;
		} else {
			return 2;
		}
	}
	
	public function change_author_meta_box_title() {
		global $wp_meta_boxes;
		$wp_meta_boxes[ 'download' ][ 'normal' ][ 'core' ][ 'authordiv' ][ 'title' ] = __( 'Vendor', 'edd_fes' );
	}
	
	public function vendor_archive_redirect() {
		global $post;
		$enable_redirect = apply_filters( 'edd_fes_vendor_archive_switch', true );
		if( is_author() && $enable_redirect && EDD_FES()->vendor_permissions->vendor_is_vendor( $post->post_author ) ) {
			$user = new WP_User( $post->post_author );
			$vendor_url = add_query_arg( 'vendor', $user->user_nicename, get_permalink( EDD_FES()->fes_options->get_option( 'vendor-page') ) );
			$vendor_url = apply_filters( 'edd_fes_vendor_archive_url', $vendor_url, $user );
			wp_redirect( $vendor_url , 301 );
			exit;
		}
	}
}