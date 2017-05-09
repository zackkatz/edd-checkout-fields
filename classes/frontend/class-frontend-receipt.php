<?php
/**
 * CFM Frontend Receipt.
 *
 * This file deals with the rendering the data entered on checkout on the receipt page.
 *
 * @package CFM
 * @subpackage Frontend
 * @since 2.1
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * CFM Frontend Receipt.
 *
 * @since 2.1
 * @access public
 */
class CFM_Frontend_Receipt {

	/**
	 * Add out actions
	 *
	 * @since 2.1
	 * @access public
	 *
	 * @return void
	 */
	public function __construct() {
		add_action( 'edd_payment_receipt_before', array( $this, 'render' ), 10, 2 );
	}

	/**
	 * Render fields on the receipt.
	 *
	 * @since 2.1
	 * @access public
	 *
	 * @return void
	 */
	public function render( $payment, $receipt_args ) {

		$form_id = get_option( 'cfm-checkout-form', false );

		// if we can't find the checkout form, echo an error
		if ( empty( $form_id ) ) {
			return;
		}

		$form = new CFM_Checkout_Form( $form_id, 'id', $payment->ID );

		if( ! empty( $form->fields ) ) {

			echo '<tr class="edd-cfm-receipt-fields"><th colspan="2"><strong>' . __( 'Information', 'edd_cfm' ) . '</strong></th></tr>';

			foreach( $form->fields as $field ) {

				echo '<tr class="edd-cfm-receipt-field">';

					echo '<td id="edd-cfm-field-' . $field->name() . '">' . $field->get_label() . '</td>';

					$value = $field->get_field_value_frontend( $payment->ID, get_current_user_id() );

					if( is_array( $value ) ) {

						echo '<td>' . implode( ', ', $value ) . '</td>';

					} else {

						echo '<td>' . $value . '</td>';

					}

				echo '</tr>';

			}

		}

	}

}