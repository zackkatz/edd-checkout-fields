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
		add_action( 'edd_profile_editor_after_email', array( $this, 'render' ) );
		add_action( 'edd_pre_update_user_profile', array( $this, 'validate' ) );
		add_action( 'edd_user_profile_updated', array( $this, 'save' ) );
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
	
	public function validate() {
		$user_id = get_current_user_id();
		$form_id   = isset( $_REQUEST['form_id'] )   ? absint( $_REQUEST['form_id'] )   : get_option( 'cfm-checkout-form', false );
		// Make the CFM Form
		$form      = new CFM_Checkout_Form( $form_id, 'id', -2, $user_id );
		// Save the CFM Form
		$form->validate_form_frontend( $post_data, $user_id, true );
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
	public function save() {
		$user_id = get_current_user_id();
		$form_id   = isset( $_REQUEST['form_id'] )   ? absint( $_REQUEST['form_id'] )   : get_option( 'cfm-checkout-form', false );
		$values    = $_POST;
		// Make the CFM Form
		$form      = new CFM_Checkout_Form( $form_id, 'id', -2, $user_id );
		// Save the CFM Form
		$form->save_form_frontend( $values, user_id, true );
	}
}
