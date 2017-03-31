<?php
/**
 * Schema
 *
 * Contains the default fields for each form
 * as well as schema correction and other helper
 * functions.
 *
 * @package CFM
 * @subpackage Schema
 * @since 2.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Default Checkout Form Fields.
 *
 * The default fields used on the checkout
 * form. This function is used to retrieve those
 * fields so they can be used in things like creating
 * the forms for the first time, as well as resetting
 * them.
 *
 * @since 2.0.0
 * @access public
 *
 * @return array Default fields.
 */
function cfm_get_default_checkout_form_fields(){
	$fields = array(
			1 => array(
				'template' => 'first_name',
				'required' => 'yes',
				'label' => 'First Name',
				'name' => 'edd_first',
				'is_meta' => true,
				'help' => 'We will use this to personalize your account experience.',
				'css' => '',
				'placeholder' => '',
				'default' => '',
				'size' => '40',
				'public'  => "public",
				'show_in_exports' => "noexport",
			),
			2 => array(
				'template' => 'last_name',
				'required' => 'yes',
				'label' => 'Last Name',
				'name' => 'edd_last',
				'is_meta' => true,
				'help' => 'We will use this as well to personalize your account experience.',
				'css' => '',
				'placeholder' => '',
				'default' => '',
				'size' => '40',
				'public'  => "public",
				'show_in_exports' => "noexport",
			),
			3 => array(
				'template' => 'user_email',
				'required' => 'yes',
				'label' => 'Email Address',
				'name' => 'edd_email',
				'is_meta' => true,
				'help' => 'We will send the purchase receipt to this address.',
				'css' => '',
				'placeholder' => '',
				'default' => '',
				'size' => '40',
				'public'  => "public",
				'show_in_exports' => "noexport",
			)
	);
	return $fields;
}


/**
 * Save Initial Checkout Form.
 *
 * Saves meta for the checkout form, as well as optionally
 * saves/resets the default fields.
 *
 * @since 2.0.0
 * @access public
 *
 * @param int $post_id Post id of the CFM form.
 * @param bool $reset_fields Whether to reset fields.
 * @return void
 */
function cfm_save_initial_checkout_form( $post_id = -2, $reset_fields = true ){
	if ( $post_id === -2 ){
		return false;
	}

	if ( $reset_fields ){
		$fields = cfm_get_default_checkout_form_fields();
		update_post_meta( $post_id, 'cfm-form', $fields );
	}

	update_post_meta( $post_id, 'cfm-form-name', 'checkout' );
	update_post_meta( $post_id, 'cfm-form-class', 'CFM_Checkout_Form' );
	update_option( 'cfm-checkout-form', $post_id );
}

/**
 * Schema Correction.
 *
 * Attempts to correct all mistakes (and also
 * runs all version upgrade routines that
 * need to change saved characteristics of
 * a field). If this function has a bug, the
 * results can be catastrophic. *crosses fingers*
 *
 * @since 2.0.0
 * @access public
 *
 * @param array $field Field characteristics.
 * @return array Field characteristics to save.
 */
function cfm_upgrade_field( $field ) {
	// if there's no template, set it as the input_type
	if ( !isset( $field['template'] ) && isset( $field['input_type'] ) ) {
		$field['template'] = $field['input_type'];
	}

	// if its recaptcha, set the name to recaptcha
	if ( !isset( $field['name'] ) && isset( $field['template'] ) && $field['template'] == 'recaptcha' ) {
		$field['name'] = 'recaptcha';
	}

	// action hooks used the label field as their name. That is incredibly dumb and problematic. Let's fix it.
	if ( isset( $field['template'] ) && $field['template'] == 'action_hook' && isset( $field['label'] ) && !isset( $field['name'] ) ) {
		$field['name'] = $field['label'];
	}

	// Prettify the template names of our fields (and back convert to template, if did template = input_type above)
	switch ( $field['template'] ) {
		case 'edd_first':
			$field['public'] = "public";
			$field['show_in_exports'] = "noexport";
			$field['meta_type'] = "payment";
			$field['is_meta'] = true;
			$field['template'] = "first_name";
			break;

		case 'edd_last':
			$field['public'] = "public";
			$field['show_in_exports'] = "noexport";
			$field['meta_type'] = "payment";
			$field['is_meta'] = true;
			$field['template'] = "last_name";
			break;

		case 'edd_email':
			$field['public'] = "public";
			$field['show_in_exports'] = "noexport";
			$field['meta_type'] = "payment";
			$field['is_meta'] = true;
			$field['template'] = "user_email";
			break;

		case 'checkbox_field':
			$field['template'] = 'checkbox';
			break;

		case 'custom_hidden_field':
			$field['template'] = 'hidden';
			break;

		case 'radio_field':
			$field['template'] = 'radio';
			break;

		case 'textarea_field':
			$field['template'] = 'textarea';
			break;

		case 'text_field':
			$field['template'] = 'text';
			break;

		case 'website_url':
			$field['template'] = 'url';
			break;

		case 'custom_html':
			$field['template'] = 'html';
			break;

		case 'repeat_field':
			$field['template'] = 'repeat';
			break;

		case 'custom_select':
			$field['template'] = 'select';
			break;

		case 'dropdown_field':
			$field['template'] = 'select';
			break;

		case 'multiple_select':
			$field['template'] = 'multiselect';
			break;

		case 'date_field':
			$field['template'] = 'date';
			break;

		case 'email_address':
			$field['template'] = 'email';
			break;

		default:
			break;
	}


	// if there's still no name, and it's not meta, grab it from the template
	if ( !isset( $field['name'] ) && isset( $field['template'] ) ) {
		$field['name'] = $field['template'];
	}

	// get rid of this key. We don't use it anywhere.
	// Only serves to confuse
	if ( isset( $field['input_type'] ) ) {
		unset( $field['input_type'] );
	}

	// If there's no name, which is nearly impossible, they'll be in trouble,
	// but at least we can prevent immediate fatal errors
	if ( !isset( $field['name'] ) ) {
		$field['name'] = 'custom_' . time();
	} else {
		 // automatically remove special characters from the meta key
		$field['name'] = sanitize_key( $field['name'] );
	}

	if ( isset( $field['is_meta'] ) && $field['is_meta'] === 'no' ){
		$field['is_meta'] = false;
	}

	if ( isset( $field['is_meta'] ) && $field['is_meta'] === 'yes' ){
		$field['is_meta'] = true;
	}

	// if its a meta, but has no meta type, default meta type to payment meta
	if ( isset( $field['is_meta'] ) && $field['is_meta'] && empty( $field['meta_type'] ) ){
		$field['meta_type'] = 'payment';
	}

	return $field;
}