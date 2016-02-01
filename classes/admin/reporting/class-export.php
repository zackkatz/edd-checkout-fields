<?php
// @todo Finish this post framework migration
/**
 * CFM Export
 *
 * This is used to add certain columns to
 * the exports of EDD CSVs.
 *
 * @package CFM
 * @subpackage Export
 * @since 1.3.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) { 
	exit;
}

/**
 * CFM Export.
 *
 * Adds custom fields to the exports.
 *
 * @since 2.0.0
 * @access public
 *
 * @todo Extend to multiple forms.
 */
class CFM_Export {

	public function __construct() {
		add_filter( 'edd_export_csv_cols_payments', array( $this, 'columns' ) );
		add_filter( 'edd_export_get_data_payments', array( $this, 'data' ) );
	}
	
	public function columns( $cols ){
		$form_id = get_option( 'cfm-checkout-form', false );
		if ( !$form_id ){
			return $cols;
		}
		
		$form = new CFM_Checkout_Form( $form_id );
		foreach( $form->fields as $field ){
			if ( ! is_object( $field ) ) {
				continue;
			}
			if ( ! $field->can_export() ) {
				continue;
			}
			$cols[$field->name()] = $field->get_label();
		}
		return $cols;
	}

	public function data( $data ){
		$form_id = get_option( 'cfm-checkout-form', false );
		if ( !$form_id ){
			return $data;
		}
		
		$form = new CFM_Checkout_Form( $form_id );
		foreach ( $data as $index => $row ){
			$order_id = $row['id'];
			foreach( $form->fields as $field ){
				if ( ! is_object( $field ) ) {
					continue;
				}
				if ( ! $field->can_export() ) {
					continue;
				}
				$data[$index][$field->name()] = $field->export_data( $order_id );
			}
		}
		return $data;
	}
}