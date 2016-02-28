<?php
/**
 * CFM Customer Frontned Pofile.
 *
 * This file deals with the rendering and saving of CFM forms on 
 * the frontend but not the checkout form.
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
 * CFM Frontend Customer Profile.
 *
 * Renders, validates and saves the frontend profile fields.
 *
 * @since 2.0.0
 * @access public
 */
class CFM_Frontend_Customer_Profile {

	/**
	 * CFM Frontend Customer Profile Actions.
	 *
	 * Registers ajax endpoints to register, validate and save CFM forms with
	 * on the frontend custom profile.
	 *
	 * @since 2.0.0
	 * @access public
	 * 
	 * @return void
	 */	
	function __construct() {
		add_action( 'edd_profile_editor_after_email', array( $this, 'render' ) );
		add_action( 'edd_pre_update_user_profile', array( $this, 'validate' ) );
		add_action( 'edd_user_profile_updated', array( $this, 'save' ) );
	}

	/**
	 * Render Customer Profile Form.
	 *
	 * Renders customer profile form.
	 *
	 * @since 2.0.0
	 * @access public
	 * 
	 * @return string HTML of fields to add to [edd_profile] form.
	 */
	public function render() {
		$user_id = get_current_user_id();

		$form_id = get_option( 'cfm-checkout-form', false );
		
		// load the scripts so others don't have to
		EDD_CFM()->setup->enqueue_form_assets();
		
		$output = '';

		// Make the CFM Form
		$form = new CFM_Checkout_Form( $form_id, 'id', -2, $user_id );
		if ( $form->has_fields_to_render_frontend( $user_id, true ) ) {
			$output .= $form->render_form_frontend( $user_id, true );
			echo $output;
		}
	}
	
	/**
	 * Validate Frontend Customer Profile Form.
	 *
	 * Validate frontend profile form.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @param array $valid_data Unused.
	 * @param array $post_data POST'd data to validate.
	 * @return void.
	 */
	public function validate() {
		$user_id = get_current_user_id();
		$form_id = isset( $_REQUEST['form_id'] )   ? absint( $_REQUEST['form_id'] )   : get_option( 'cfm-checkout-form', false );
		$values  = $_POST;
		// Make the CFM Form
		$form      = new CFM_Checkout_Form( $form_id, 'id', -2, $user_id );
		// Save the CFM Form
		$form->validate_form_frontend( $values, $user_id, true );
	}

	/**
	 * Submit Frontend Customer Profile Form.
	 *
	 * Submit customer profile form.
	 *
	 * @since 2.0.0
	 * @access public
	 * 
	 * @return void
	 */
	public function save() {
		$user_id = get_current_user_id();
		$form_id   = isset( $_REQUEST['form_id'] )   ? absint( $_REQUEST['form_id'] )   : get_option( 'cfm-checkout-form', false );
		$values    = $_POST;

		// Make the CFM Form
		
		$form      = new CFM_Checkout_Form( $form_id, 'id', -2, $user_id );
		// Save the CFM Form
		$form->save_form_frontend( $values, $user_id, true );
	}
}
