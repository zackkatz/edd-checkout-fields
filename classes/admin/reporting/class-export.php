<?php
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
	/**
	 * CFM Export Actions.
	 *
	 * Runs actions required to add fields to exports.
	 *
	 * @since 2.0.0
	 * @access public
	 * 
	 * @return void
	 */	
	public function __construct() {
		add_filter( 'edd_export_csv_cols_payments', array( $this, 'columns' ) );
		add_filter( 'edd_export_get_data_payments', array( $this, 'data' ) );
	}
	
	/**
	 * CFM Export Columns.
	 *
	 * Adds columns to the export files.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @param  array $cols Array of columns on the export.
	 * @return array Array of cols on the export.
	 */	
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

	/**
	 * CFM Export Data.
	 *
	 * Adds data to the export files.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @param  array $data Array of data on the export.
	 * @return array Array of data on the export.
	 */	
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