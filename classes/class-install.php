<?php
if ( !defined( 'ABSPATH' ) ) {
	exit;
}

class FES_Install {
	public function init() {
		$db_version = get_option( 'fes_checkout_fields_version' )
		EDD_FES()->setup->register_post_type();
		if ( !$db_version ) {
			$this->install_fes();
			update_option( 'fes_checkout_fields_version', '1.0' );
		} 
		else {
			return;
		}
	}
	
	private function install_fes() {
		$this->create_post();
		set_transient( '_edd_fes_activation_redirect', true, 30 );
	}
	

	public function create_post() {
		global $wpdb, $wp_version;
		$page_id = get_option( 'fes_checkout_field_post_id' );
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
		update_option( 'fes_checkout_field_post_id', $page_id );
		return;
	}
	
	public function update_to() {
		$version = EDD_FES()->fes_options->get_option( 'db_version' );
		switch ( $version ) {
			case '1.0':
				break;
			default:
				// clean
				break;
		}
	}
}