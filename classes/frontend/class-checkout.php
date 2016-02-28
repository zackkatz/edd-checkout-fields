<?php
/**
 * CFM Checkout
 *
 * This file deals with the rendering and saving of CFM forms on
 * the checkout page.
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
 * CFM Checkout Form.
 *
 * Renders, validates and saves the checkout form fields.
 *
 * @since 2.0.0
 * @access public
 */
class CFM_Checkout {

	/**
	 * CFM Checkout Actions.
	 *
	 * Registers ajax endpoints to register, validate and save CFM forms with
	 * on the frontend.
	 *
	 * @since 2.0.0
	 * @access public
	 * 
	 * @return void
	 */	
	function __construct() {
		// save actions
		add_action( 'edd_insert_payment', array($this,'submit_checkout_form'), 10, 2);
		remove_action( 'edd_register_fields_before', 'edd_user_info_fields' );
		add_action( 'edd_register_fields_before', array( $this, 'render_checkout_form' ) );
		remove_action( 'edd_purchase_form_after_user_info', 'edd_user_info_fields' );
		add_action( 'edd_purchase_form_after_user_info', array( $this, 'render_checkout_form' ) );
		add_action( 'edd_checkout_error_checks', array( $this, 'validate' ), 10, 2 );
	}

	/**
	 * Render Checkout Form.
	 *
	 * Renders checkout form.
	 *
	 * @since 2.0.0
	 * @access public
	 * 
	 * @return string HTML of fields to add to checkout form.
	 */
	public function render_checkout_form( ) {
		$user_id = get_current_user_id();
		$form_id = get_option( 'cfm-checkout-form', -2 );
		
		// load the scripts so others don't have to
		EDD_CFM()->setup->enqueue_form_assets();
		add_action( 'wp_enqueue_scripts',	 array( EDD_CFM()->setup, 'enqueue_scripts' ) );
		add_action( 'wp_enqueue_scripts',	 array( EDD_CFM()->setup, 'enqueue_styles'  ) );

		$output = '';

		// Make the CFM Form
		$form = EDD_CFM()->helper->get_form_by_id( $form_id, $user_id );
		$output .= $form->render_form_frontend( $user_id, false );
		echo $output;
	}
	
	/**
	 * Validate Checkout Form.
	 *
	 * Validate checkout form.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @param array $valid_data Unused.
	 * @param array $post_data POST'd data to validate.
	 * @return void.
	 */
	public function validate( $valid_data, $post_data ){
		$form_id   = isset( $post_data['form_id'] )   ? absint( $post_data['form_id'] ) : get_option( 'cfm-checkout-form', -2 );
		// Make the CFM Form
		$form      = new CFM_Checkout_Form( $form_id, 'id', -2 );
		// Save the CFM Form
		$form->validate_form_frontend( $post_data, get_current_user_id(), false );
	}

	/**
	 * Submit Checkout Form.
	 *
	 * Submit checkout form.
	 *
	 * @since 2.0.0
	 * @access public
	 * 
	 * @param int  $payment_id Payment ID to save data to.
	 * @param array $payment_data Values to save.
	 * @return void
	 */
	public function submit_checkout_form( $payment_id, $payment_data ) {
		$form_id   = isset( $_REQUEST['form_id'] )   ? absint( $_REQUEST['form_id'] ) : get_option( 'cfm-checkout-form', -2 );
		$values    = $_POST;
		// Make the CFM Form
		$form      = new CFM_Checkout_Form( $form_id, 'id', $payment_id );
		// Save the CFM Form
		$form->save_form_frontend( $values, get_current_user_id(), false );
	}
}
