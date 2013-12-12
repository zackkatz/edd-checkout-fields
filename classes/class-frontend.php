<?php
if ( !defined( 'ABSPATH' ) ) {
	exit;
}

class CFM_Frontend {
	public function __construct() {
		remove_action( 'edd_purchase_form_after_user_info', 'edd_user_info_fields' );
		add_action('edd_purchase_form_after_user_info', array ($this, 'add_fields'));
	}
	function add_fields(){
		echo do_shortcode('[edd-checkout-fields]');
	}
}