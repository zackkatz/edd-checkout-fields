<?php
/**
 * CFM Helpers
 *
 * This file contains helper functions that
 * are useful on the frontend and admin.
 *
 * @package CFM
 * @subpackage Misc
 * @since 2.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) { 
	exit;
}

/**
 * CFM Helpers.
 *
 * Contains a bunch of useful functions, including
 * a lot of form retrieval and setting maniupulation
 * functions.
 *
 * @since 2.0.0
 * @access public
 */
class CFM_Helpers {

	/**
	 * Get Form Name by ID.
	 *
	 * Retrieve an CFM form name when you know
	 * the id of the form.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @param int $id Post id of form.
	 * @return string Form name.
	 */
	public function get_form_name_by_id( $id ){
		$name = get_post_meta( $id, 'cfm-form-name', true );
		if ( ! $name ){
			if ( get_option( 'cfm-checkout-form', false ) == $id ) {
				$name = 'submission';
			}
		}
		return $name;
	}

	/**
	 * Get Form Type by ID.
	 *
	 * Retrieve an CFM form type when you know
	 * the id of the form.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @param int $id Post id of form.
	 * @return string Form type.
	 */
	public function get_form_type_by_id( $id ){
		$type = get_post_meta( $id, 'cfm-form-type', true );
		if ( !$type ){
			if ( get_option( 'cfm-checkout-form', false ) == $id ) {
				$type = 'post';
			}
		}
		return $type;
	}

	/**
	 * Get Form Class by ID.
	 *
	 * Retrieve an CFM form class when you know
	 * the id of the form.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @param int $id Post id of form.
	 * @return string Form class.
	 */
	public function get_form_class_by_id( $id ){
		$class = get_post_meta( $id, 'cfm-form-class', false );
		if ( !$class ){
			if ( get_option( 'cfm-checkout-form', false ) == $id ) {
				$class = 'CFM_Checkout_Form';
			}
		}
		return $class;
	}

	/**
	 * Get Form ID by Name.
	 *
	 * Retrieve an CFM form ID when you know
	 * the name of the form.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @param string $name Form name.
	 * @return int Form ID.
	 */
	public function get_form_id_by_name( $name ){
		if ( $name === 'checkout' ) {
			return get_option( 'cfm-checkout-form', -2 );
		} else {
			return -2;
		}
	}

	/**
	 * Get Form Class by Name.
	 *
	 * Retrieve an CFM form Class when you know
	 * the name of the form.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @param string $name Form name.
	 * @return string Form class.
	 */
	public function get_form_class_by_name( $name ){
		if ( cfm_is_key( $name, EDD_CFM()->load_forms ) ){
			return EDD_CFM()->load_forms[ $name ];
		} else {
			return false;
		}
	}

	/**
	 * Get Form by Name.
	 *
	 * Retrieve an CFM form object when you know
	 * the name of the form.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @param string $name Form name.
	 * @param int $save_to ID of object to save to. 
	 * @return CFM_Form Form object.
	 */
	public function get_form_by_name( $name, $save_to = false ){
		$class = get_post_meta( EDD_CFM()->helper->get_form_id_by_name( $name ), 'cfm-form-class', true );
		if ( $class ){
			$form = new $class( EDD_CFM()->helper->get_form_id_by_name( $name ), 'id', $save_to );
			return $form;
		} else {
			return false;
		}
	}

	/**
	 * Get Form by ID.
	 *
	 * Retrieve an CFM form object when you know
	 * the ID of the form.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @param int $id Form id.
	 * @param int $save_to ID of object to save to.
	 * @return CFM_Form Form object.
	 */
	public function get_form_by_id( $id = 0, $save_to = false ){
		$class = get_post_meta( $id, 'cfm-form-class', true );
		$form = new $class( $id, 'id', $save_to );
		return $form;
	}

	/**
	 * Is CFM Form.
	 *
	 * Based on the post id, see if the post
	 * is a CFM Form.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @param int $id Form id.
	 * @return bool Is CFM Form.
	 */
	public function is_cfm_form( $id = 0 ){
		$found = false;
		$form = get_post_meta( $id, 'cfm-form-class', true );
		if ( !empty( $form ) ) {
			$found = true;
		}
		return $found;
	}

	/**
	 * Get CFM Form Name by Class.
	 *
	 * Given the class of a form, find
	 * the form name.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @param string $class Form class.
	 * @return string CFM form name.
	 */
	public function get_form_name_by_class( $class ){
		if ( 'submission' == $class ) {
			$class = 'CFM_Checkout_Form';
		}
		return $class;
	}

	/**
	 * Get CFM Form Class by Name.
	 *
	 * Given the name of a form, find
	 * the form class.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @param string $name Form name.
	 * @return string CFM form class.
	 */
	public function get_field_class_by_name( $name ){
		if ( cfm_is_key( $name, EDD_CFM()->load_fields ) ){
			return EDD_CFM()->load_fields[ $name ];
		} else {
			return false;
		}
	}
}