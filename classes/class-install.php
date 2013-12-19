<?php
if ( !defined( 'ABSPATH' ) ) {
	exit;
}

class CFM_Install {
	public function init() {
		$db_version = get_option( 'edd_cfm_version' );
		EDD_CFM()->setup->register_post_type();
		if ( !$db_version ) {
			$this->install_cfm();
			update_option( 'edd_cfm_version', '1.0' );
		} 
		else {
			return;
		}
	}
	
	private function install_cfm() {
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
		$data = 'a:3:{i:0;a:11:{s:10:"input_type";s:5:"email";s:8:"template";s:9:"edd_email";s:8:"required";s:3:"yes";s:5:"label";s:5:"Email";s:4:"name";s:9:"edd_email";s:7:"is_meta";s:2:"no";s:4:"help";s:50:"We will send the purchase receipt to this address.";s:3:"css";s:0:"";s:11:"placeholder";s:0:"";s:7:"default";s:0:"";s:4:"size";s:2:"40";}i:1;a:11:{s:10:"input_type";s:4:"text";s:8:"template";s:9:"edd_first";s:8:"required";s:3:"yes";s:5:"label";s:10:"First Name";s:4:"name";s:9:"edd_first";s:7:"is_meta";s:2:"no";s:4:"help";s:56:"We will use this to personalize your account experience.";s:3:"css";s:0:"";s:11:"placeholder";s:0:"";s:7:"default";s:0:"";s:4:"size";s:2:"40";}i:2;a:11:{s:10:"input_type";s:4:"text";s:8:"template";s:8:"edd_last";s:8:"required";s:3:"yes";s:5:"label";s:9:"Last Name";s:4:"name";s:8:"edd_last";s:7:"is_meta";s:2:"no";s:4:"help";s:64:"We will use this as well to personalize your account experience.";s:3:"css";s:0:"";s:11:"placeholder";s:0:"";s:7:"default";s:0:"";s:4:"size";s:2:"40";}}';
		update_post_meta($page_id,'edd-checkout-fields', $data);
		update_option( 'edd_cfm_id', $page_id );
		return;
	}
	
	public function update_to() {
		$version = get_option( 'edd_cfm_version', '1.0' );
		switch ( $version ) {
			case '1.0':
				break;
			default:
				// clean
				break;
		}
	}
}