<?php
/**
 * CFM Privacy
 *
 * This file deals with CFM's privcay methods and tools.
 *
 * @package CFM
 * @subpackage Administration
 *
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CFM Menu.
 *
 * Creates all of the menu and submenu items CFM adds to the backend.
 *
 *
 * @access public
 */
class CFM_Privacy {

	/**
	 * CFM Menu Actions.
	 *
	 * Runs actions required to add menus and submenus.
	 *
	 *
	 * @access public
	 *
	 * @return void
	 */
	public function __construct() {
		add_action( 'edd_privacy_order_details_item', array( $this, 'order_exporter_data' ), 10, 2 );
		add_action( 'edd_anonymize_payment', array( $this, 'anonymize_payment_fields' ), 10, 1 );
	}

	/**
	 * Add CFM data that is necessary to the data exporter.
	 *
	 *
	 * @access public
	 *
	 * @return void
	 */
	public function order_exporter_data( $data_points = array(), EDD_Payment $payment ) {
		$form_fields = $this->_get_payment_form_fields( $payment );

		if ( empty( $form_fields ) ) {
			return $data_points;
		}

		foreach ( $form_fields as $field ) {
			if ( empty( $field->characteristics['show_in_privacy_export'] ) ) {
				continue;
			}

			$value = $field->get_field_value( $payment->ID );
			if ( empty( $value ) ) {
				continue;
			}

			if ( is_array( $value ) && 'file_upload' !== $field->characteristics['template'] ) {
				$value = implode( ', ', $value );
			} elseif( 'file_upload' === $field->characteristics['template'] ) {
				foreach ( $value as $key => $attachment_id ) {
					$value[ $key ] = wp_get_attachment_url( $attachment_id );
				}

				$value = make_clickable( implode( ', ', $value ) );
			}

			$data_points[] = array(
				'name'  => $field->get_label(),
				'value' => $value,
			);

		}

		return $data_points;
	}

	/**
	 * when a payment is being anonymized, perform actions on the CFM dta
	 *
	 * @param $payment
	 */
	public function anonymize_payment_fields( $payment ) {
		$form_fields = $this->_get_payment_form_fields( $payment );

		if ( empty( $form_fields ) ) {
			return;
		}

		foreach ( $form_fields as $field ) {
			$action = ! empty( $field->characteristics['privacy_eraser_action'] ) ?
				$field->characteristics['privacy_eraser_action'] :
				'none';

			if ( 'none' === $action ) {
				continue;
			}

			// If we're supposed to anonymize the data, we need to determine what type of data it is.
			if ( 'anonymize' === $action ) {

				$template = $field->characteristics['template'];
				$value    = $field->get_field_value( $payment->ID );
				$value    = $this->_anonymize_field_value( $value, $template );

				$field->save_field_admin( $payment->ID, - 2, $value, - 2 );

			} else {

				if ( (bool) $field->meta ) {
					$meta_type = $field->meta_type();

					if ( $meta_type === 'user' ) {

						$value = delete_user_meta( $field->user_id, $field->id );

					} else {

						$value = delete_post_meta( $payment->ID, $field->id );

					}

				} else {

					$user = get_userdata( $field->user_id );

					if ( $user && isset( $field->id ) ) {
						$arr               = array();
						$arr['ID']         = $field->user_id;
						$arr[ $field->id ] = '';
						wp_update_user( $arr );
					}

				}

			}
		}
	}

	public function _anonymize_field_value( $value, $template = false ) {

		if ( is_array( $value ) ) {
			foreach ( $value as $key => $item ) {
				$value[ $key ] = $this->_anonymize_field_value( $item, $template );
			}
		} else {
			// First check if the value is an IP Address
			$is_ip = filter_var( $value, FILTER_VALIDATE_IP );
			if ( $is_ip ) {
				$value = wp_privacy_anonymize_ip( $value );
				} else {
				switch( $template ) {
					case 'email':
					case 'user_email':
						$type = 'email';
						break;

					case 'url':
						$type = 'url';
						break;

					case 'date':
						$type = 'date';
						break;

					case 'text':
						$type = is_email( $value ) ? 'email' : 'text';
						break;

					case 'html':
					case 'textarea':
						$type = 'longtext';
						break;

					default:
						$type = '';

				}

				$value = wp_privacy_anonymize_data( $type, $value );
			}
		}

		return $value;
	}

	private function _get_payment_form_fields( $payment ) {
		// attempt to get the form id of the checkout form
		$form_id = get_option( 'cfm-checkout-form', false );

		// if we can't find the checkout form
		if ( ! $form_id ) {
			return false;
		}

		$form = new CFM_Checkout_Form( $form_id, 'id', $payment->ID );
		if ( empty( $form->fields ) ) {
			return false;
		}

		return $form->fields;
	}
}
