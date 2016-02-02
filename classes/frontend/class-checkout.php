<?php
/**
 * CFM Profile
 *
 * This file deals with the rendering and saving of CFM forms,
 * particularly from shortcodes.
 *
 * @package CFM
 * @subpackage Frontend
 * @since 2.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) { 
	exit;
}

/**
 * CFM Forms.
 *
 * Register the form shortcodes and create render/save 
 * ajax functions for them.
 *
 * @since 2.0.0
 * @access public
 */
class CFM_Checkout {
	/**
	 * CFM Form Actions and Shortcodes.
	 *
	 * Registers ajax endpoints to save CFM forms with
	 * on the frontend as well as registers shortcodes for
	 * the default CFM forms.
	 *
	 * @since 2.0.0
	 * @access public
	 * 
	 * @return void
	 */	
	function __construct() {
		// save actions
		add_action( 'edd_insert_payment', array($this,'submit_checkout_form'),10,2);
		//add_filter( 'edd_purchase_form_required_fields', array($this, 'req_fields'), 10, 3);
		remove_action( 'edd_register_fields_before', 'edd_user_info_fields' );
		add_action( 'edd_register_fields_before', array( $this, 'render_checkout_form' ) );
		remove_action( 'edd_purchase_form_after_user_info', 'edd_user_info_fields' );
		add_action( 'edd_purchase_form_after_user_info', array( $this, 'render_checkout_form' ) );
	}


	/**
	 * Render Profile Form.
	 *
	 * Renders profile form.
	 *
	 * @since 2.0.0
	 * @access public
	 * 
	 * @param int  $post_id User id to edit.
	 * @param bool $readonly Whether the form is readonly.
	 * @param array $args Additional arguments to send 
	 *                    to form rendering functions.
	 * @return string HTML of profile form.
	 */
	function render_checkout_form( ) {
		$user_id = get_current_user_id();
		$form_id = get_option( 'cfm-checkout-form', -2 );
		
		// load the scripts so others don't have to
		EDD_CFM()->setup->enqueue_form_assets();

		$output = '';

		// Make the CFM Form
		$form = EDD_CFM()->helper->get_form_by_id( $form_id, $user_id );
		$output .= $form->render_form_frontend( $user_id, false );
		echo $output;
	}

	/**
	 * Submit Checkout Form.
	 *
	 * Submit profile form on the frontend
	 * My Account page.
	 *
	 * @since 2.0.0
	 * @access public
	 * 
	 * @param int  $id User id to edit.
	 * @param array $values Values to save.
	 * @param array $args Additional arguments to send 
	 *                    to form rendering functions.
	 * @return void
	 */
	function submit_checkout_form( $payment_id, $payment_data ) {
		$form_id   = isset( $_REQUEST['form_id'] )   ? absint( $_REQUEST['form_id'] ) : get_option( 'cfm-checkout-form', -2 );
		$values    = $_POST;
		// Make the CFM Form
		$form      = new CFM_Checkout_Form( $form_id, 'id', $payment_id );
		// Save the CFM Form
		$form->save_form_frontend( $values, get_current_user_id(), false );
	}
}
