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
class CFM_Frontend_Customer_Profile {
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
		// add_action( 'wp_ajax_cfm_submit_profile_form', array( $this, 'submit_profile_form' ) );
		// add_action( 'wp_ajax_nopriv_cfm_submit_profile_form', array( $this, 'submit_profile_form' ) );
		// @todo: add fields to the customer profile page + offer to save them
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
	function render_profile_form( $user_id = -2, $profile = false, $args = array() ) {
		if ( $user_id === -2 || empty( $user_id ) ) {
			$user_id = get_current_user_id();
		}

		$form_id = get_option( 'cfm-checkout-form', false );

		// load the scripts so others don't have to
		EDD_CFM()->setup->enqueue_form_assets();

		$output = '';

		// Make the CFM Form
		$form = new CFM_Checkout_Form( $form_id, 'id', -2, $user_id );

		$output .= $form->render_form_frontend( $user_id, true );
		return $output;
	}

	/**
	 * Submit Profile Form.
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
	function submit_profile_form( $id = 0, $values = array(), $args = array() ) {
		$form_id   = !empty( $values ) && isset( $values['form_id'] )   ? absint( $values['form_id'] )   : ( isset( $_REQUEST['form_id'] )   ? absint( $_REQUEST['form_id'] )   : get_option( 'cfm-checkout-form', false ) );
		$values    = !empty( $values ) ? $values : $_POST;
		// Make the CFM Form
		$form      = new CFM_Checkout_Form( $form_id, 'id', -2, $id );
		// Save the CFM Form
		$form->save_form_frontend( $values, get_current_user_id(), true );
	}
}
