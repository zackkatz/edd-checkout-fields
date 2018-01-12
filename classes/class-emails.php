<?php
/**
 * CFM Emails
 *
 * This file contains email functionality for Checkout
 * Fields Manager.
 *
 * @package CFM
 * @subpackage Emails
 * @since 1.2.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) { 
	exit;
}

/**
 * CFM Emails.
 *
 * Contains the functions needed to add CFM fields to EDD emails
 * like the sales notifications to author of products, commission emails,
 * and sales receipts.
 *
 * @since 1.2.0
 * @access public
 */
class CFM_Emails {
	/**
	 * Register email actions.
	 *
	 * Hooks into actions provided by EDD core and Commissions
	 * to filter CFM tags into their respective values.
	 *
	 * @since 1.3.0
	 * @access public
	 * 
	 * @return void
	 */		
	public function __construct() {
		add_action( 'edd_sale_notification', array( $this, 'email_body' ), 10, 2 );
		add_action( 'edd_purchase_receipt', array( $this, 'email_body' ), 10, 2 );
		add_action( 'eddc_sale_alert_email', array( $this, 'commissions_email' ), 10, 6 );
	}

	/**
	 * CFM Email Body Tag Replacement.
	 *
	 * If CFM can find the checkout form, then
	 * calls the function to replace the CFM tags with
	 * respective values.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @todo  2.0.0: This can probably be removed next version (2.1.0) when we open
	 * up the CFM Form API.
	 *
	 * @param  int $post_id Payment post ID.
	 * @param  string $message The email message prior to CFM tag replacement.
	 * @return string The message after replacing CFM tags.
	 */		
	public function email_body( $message, $post_id ){
		$form_id = get_option( 'cfm-checkout-form', false );
		if ( $form_id ){
			$message = EDD_CFM()->emails->custom_meta_values( $message, $post_id );
		}
		return $message;
	}
	
	/**
	 * Custom Meta Value Replacement.
	 *
	 * Given the payment id and a string message,
	 * cycles through all registered CFM forms, and attempts
	 * to find CFM tags and replace them with their value.
	 *
	 * @since 2.0.0
	 * @access public
	 * 
	 * @param  int $post_id Payment post ID.
	 * @param  string $message The email message prior to CFM tag replacement.
	 * @return string The message after replacing CFM tags.
	 */	
	public function custom_meta_values( $message, $post_id ) {
		$form = '';
		foreach( EDD_CFM()->load_forms as $template => $class ){
			$form = EDD_CFM()->helper->get_form_by_name( $template, $post_id );
			foreach( $form->fields as $field ){
				if ( ! is_object( $field ) ) {
					continue;
				}
				$message = str_replace('{'. $field->name() .'}', $field->export_data( $post_id ), $message );
			}
		}
		return $message;
	}
	
	/**
	 * Commissions Email Tag Replacement.
	 *
	 * This works identically to the normal email tag replacement
	 * function except that commissions doesn't give us the payment id upfront
	 * so we have to derive it by looking it up from the commissions ID they do give.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @todo  2.0.0: This can probably be simplified next version (2.1.0) when we open
	 * up the CFM Form API.
	 *
	 * @param  string $message The email message prior to CFM tag replacement.
	 * @param  double $commission_amount Unused.
	 * @param  string $rate The email message Unused.
	 * @param  int $download_id Unused
	 * @param  int $commission_id The ID of the commission payment the email is being triggered on.
	 * @return string The message after replacing CFM tags.
	 */
	public function commissions_email( $message, $user_id, $commission_amount, $rate, $download_id, $commission_id ){
		$form_id = get_option( 'cfm-checkout-form', false );
		if ( $form_id && $commission_id ){
			$post_id = get_post_meta( $commission_id, '_edd_commission_payment_id', true ); // try to get payment_id from post_id
			if ( $post_id ){ // if we got the payment
				$message = EDD_CFM()->emails->custom_meta_values( $message, $post_id );
			}
		}
		return $message;
	}
}