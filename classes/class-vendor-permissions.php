<?php
if ( !defined( 'ABSPATH' ) ) {
	exit;
}

class FES_Vendor_Permissions {
	function __construct() {
	}
	function vendor_can_create_product( $user_id = -2 ) {
		if ( $user_id == -2 ) {
			$user_id = get_current_user_id();
		}
		if ( EDD_FES()->vendor_permissions->vendor_is_vendor( $user_id ) ) {
			return true;
		} else {
			return false;
		}
	}
	
	// Let's make some magic
	function vendor_is_vendor( $user_id = -2 ) {
		if ( $user_id == -2 ) {
			$user_id = get_current_user_id();
		}
		if ( $user_id == 0 ) {
			// This is a logged out user, since get_current_user_id returns 0 for non logged in
			// since we can't do anything with them, lets get them out of here. They aren't vendors.
			return false;
		}
		$user = new WP_User( $user_id );
		// This allows devs to take what would normally be a vendor and say they aren't a vendor.
		$bool = false;
		$bool = apply_filters( 'fes_skip_is_vendor', $bool, $user );
		// Note to developers: I passed in the entire user object above. 
		// So expect either an object (logged in user) or false (not logged in user).
		if ( $bool ) {
			return false;
		}
		// Authentication Attempt #1: okay let's try caps
		// $vendor_caps = array ( 'fes_is_vendor', 'fes_is_admin');
		// $vendor_caps = apply_filters('fes_vendor_caps', $vendor_caps);
		if ( current_user_can( 'fes_is_vendor' ) || current_user_can( 'fes_is_admin' ) || current_user_can( 'frontend-vendor' ) || current_user_can( 'frontend_vendor' ) ) {
			return true;
		}
		// Authentication Attempt #2:  maybe a developer has a reason for wanting to hook a user in?
		$bool = false;
		$bool = apply_filters( 'fes_is_vendor_check_override', $bool, $user );
		// Note to developers: I passed in the entire user object above. 
		// So expect either an object (logged in user) or false (not logged in user).
		if ( $bool ) {
			return true;
		}
		// end of the line
		return false;
	}
	
	// User id if present/logged in
	// $ref is the url we want to bring the user back to if applicable
	function vendor_not_a_vendor_redirect( $user_id = -2 ) {
		// lets try the grab user_id trick
		if ( $user_id == -2 ) {
			$user_id = get_current_user_id();
		}
		if ( $user_id == 0 ) {
			// This is a logged out user, since get_current_user_id returns 0 for non logged in
			// So let's log them in, and then attempt redirect to ref
			$base_url = get_permalink( EDD_FES()->fes_options->get_option( 'vendor-dashboard-page' ) );
			$base_url = add_query_arg( 'view', 'login-register', $base_url );
			wp_redirect( $base_url );
			exit;
		} else {
			$user = new WP_User( $user_id );
			if ( current_user_can( 'pending_vendor' ) ) {
				// are they a pending vendor: display not approved display
				$base_url = get_permalink( EDD_FES()->fes_options->get_option( 'vendor-dashboard-page' ) );
				$base_url = add_query_arg( 'user_id', $user_id, $base_url );
				$base_url = add_query_arg( 'view', 'pending', $base_url );
				wp_redirect( $base_url );
				exit;
			} else {
				// are they not a vendor yet: show registration page
				$base_url = get_permalink( EDD_FES()->fes_options->get_option( 'vendor-dashboard-page' ) );
				$base_url = add_query_arg( 'user_id', $user_id, $base_url );
				$base_url = add_query_arg( 'view', 'application', $base_url );
				wp_redirect( $base_url );
				exit;
			}
		}
	}
	
	// WARNING: FUNCTION NOT IN USE. It's for 2.1. Don't use it yet.
	function vendor_not_enough_permissions( $user_id = -2, $ref = -2 ) {
		// lets try the grab user_id trick
		if ( $user_id == -2 ) {
			$user_id = get_current_user_id();
		}
		if ( $ref == -2 ) {
			$ref = wp_get_referer();
			if ( $ref == false ) {
				$ref = 'unknown page';
			}
		}
		// lets also log this
		//fes_simple_log( $logname = 'Vendor Access Denied Log', $text = "User $user_id, attempted to access $ref and was denied", $severity = 3 );
		$base_url = get_permalink( EDD_FES()->fes_options->get_option( 'vendor-dashboard-page' ) );
		add_query_arg( 'ref', $ref, $base_url );
		add_query_arg( 'user_id', $user_id, $base_url );
		add_query_arg( 'view', 'pending', $base_url );
		wp_redirect( $base_url );
		exit;
	}
}