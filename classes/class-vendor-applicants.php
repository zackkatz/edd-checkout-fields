<?php
if ( !defined( 'ABSPATH' ) ) {
	exit;
}

class FES_Vendor_Applicants {
	function __construct() {
		add_filter( 'user_row_actions', array(
			 $this,
			'user_row_actions' 
		), 10, 2 );
		add_filter( 'load-users.php', array(
			 $this,
			'user_row_actions_commit' 
		) );
	}
	
	public function user_row_actions( $actions, $user_object ) {
		if ( !empty( $_GET[ 'role' ] ) && $_GET[ 'role' ] == 'pending_vendor' ) {
			$actions[ 'approve_vendor' ] = "<a href='?role=pending_vendor&action=approve_vendor&user_id=" . $user_object->ID . "'>" . __( 'Approve', 'edd_fes' ) . "</a>";
			$actions[ 'deny_vendor' ]    = "<a href='?role=pending_vendor&action=deny_vendor&user_id=" . $user_object->ID . "'>" . __( 'Deny', 'edd_fes' ) . "</a>";
		}
		return $actions;
	}
	
	public function user_row_actions_commit() {
		if ( !empty( $_GET[ 'action' ] ) && !empty( $_GET[ 'user_id' ] ) ) {
			$wp_user_object = new WP_User( (int) $_GET[ 'user_id' ] );
			if ( $_GET[ 'action' ] == 'approve_vendor' ) {
				$role = 'frontend_vendor';
				$wp_user_object->set_role( $role );
				add_action( 'admin_notices', array(
					 $this,
					'approved' 
				) );
				EDD_FES()->emails->fes_notify_user_app_accepted( (int) $_GET[ 'user_id' ] );
			} else {
				$role = 'subscriber';
				$wp_user_object->set_role( $role );
				add_action( 'admin_notices', array(
					 $this,
					'denied' 
				) );
				EDD_FES()->emails->fes_notify_user_app_denied( (int) $_GET[ 'user_id' ] );
			}
		}
	}
	
	public function denied() {
		echo '<div class="updated">';
		echo '<p>' . __( 'Vendor has been <b>denied</b>.', 'edd_fes' ) . '</p>';
		echo '</div>';
	}
	
	public function approved() {
		echo '<div class="updated">';
		echo '<p>' . __( 'Vendor has been <b>approved</b>.', 'edd_fes' ) . '</p>';
		echo '</div>';
	}
	
	public function show_pending_vendors_link( $values ) {
		$values[ 'pending_vendors' ] = '<a href="?role=asd">' . __( 'Pending Vendors', 'edd_fes' ) . ' <span class="count">(3)</span></a>';
		return $values;
	}
}
