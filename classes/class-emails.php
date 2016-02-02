<?php
if ( !defined( 'ABSPATH' ) ) {
	exit;
}
class CFM_Emails {

	public function __construct() {
		add_action( 'edd_sale_notification', array( $this, 'email_body' ), 10, 2 );
		add_action( 'edd_purchase_receipt', array( $this, 'email_body' ), 10, 2 );
		add_action( 'eddc_sale_alert_email', array( $this, 'commissions_email' ), 10, 6 );
	}

	public function email_body( $message, $post_id ){
		$form_id = get_option( 'cfm-checkout-form', false );
		if ( $form_id ){
			$message = EDD_CFM()->emails->custom_meta_values( $post_id, $message );
		}
		return $message;
	}
	
	public function custom_meta_values( $post_id, $message ){
		$form = '';
		foreach( EDD_CFM()->load_forms as $template => $class ){
			$form = EDD_CFM()->helper->get_form_by_name( $template, $id );
			foreach( $form->fields as $field ){
				if ( ! is_object( $field ) ) {
					continue;
				}
				$message = str_replace('{'. $field->name() .'}', $field->export_data( $post_id ), $message );
			}
		}
		return $message;
	}
	
	public function commissions_email( $message, $user_id, $commission_amount, $rate, $download_id, $commission_id ){
		$form_id = get_option( 'cfm-checkout-form', false );
		if ( $form_id && $commission_id ){
			$post_id = get_post_meta( $commission_id, '_edd_commission_payment_id', true ); // try to get payment_id from post_id
			if ( $post_id ){ // if we got the payment
				$message = EDD_CFM()->emails->custom_meta_values( $post_id, $message );
			}
		}
		return $message;
	}
}