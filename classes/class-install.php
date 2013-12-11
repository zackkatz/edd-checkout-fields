<?php
if ( !defined( 'ABSPATH' ) ) {
	exit;
}

class CFM_Install {
	public function init() {
		$db_version = get_option( 'edd_cfm_version' )
		EDD_CFM()->setup->register_post_type();
		if ( !$db_version ) {
			$this->install_fes();
			update_option( 'edd_cfm_version', '1.0' );
		} 
		else {
			return;
		}
	}
	
	private function install_fes() {
		$this->create_post();
		set_transient( '_edd_cfm_activation_redirect', true, 30 );
	}
	

	public function create_post() {
		global $wpdb, $wp_version;
		$page_id = get_option( 'edd_cfm_id' );
		if ( $page_id != false ) {
			return;
		}
		$page_data = array(
			'post_status' => 'publish',
			'post_type' => 'edd-checkout-fields',
			'post_author' => 1,
			'post_title' => 'Checkout Fields' 
		);
		$page_id   = wp_insert_post( $page_data );
		update_option( 'edd_cfm_id', $page_id );
		return;
	}
	
	public function update_to() {
		$version = EDD_CFM()->fes_options->get_option( 'db_version' );
		switch ( $version ) {
			case '1.0':
				break;
			default:
				// clean
				break;
		}
	}
}