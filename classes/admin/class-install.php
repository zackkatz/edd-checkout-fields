<?php
/**
 * CFM Installation and Automatic Upgrades.
 *
 * This file handles setting up new
 * CFM installs as well as performing
 * behind the scene upgrades between
 * CFM versions.
 *
 * @package CFM
 * @subpackage Install/Upgrade
 * @since 2.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) { 
	exit;
}

/**
 * CFM Install.
 *
 * This class handles a new CFM install
 * as well as automatic (non-user initiated) 
 * upgrade routines.
 *
 * @since 2.0.0
 * @access public
 */
class CFM_Install {

	/**
	 * Install/Upgrade routine.
	 *
	 * This function is what is called to actually 
	 * install CFM data on new installs and to do
	 * behind the scenes upgrades on CFM upgrades.
	 * If this function contains a bug, the results 
	 * can be catastrophic.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @todo  I'd like to add preflight checks here.
	 * @todo  I'd like to add a recovery system here.
	 * 
	 * @return void
	 */
	public function init() {
		// Attempt to get the current version.
		$version = get_option( 'cfm_current_version', '1.0' );

		// If we don't need to upgrade, abort.
		if ( version_compare( $version, '2.0', '>=' )  ) {
			return;
		}

		/**
		 * If you're having deja vu, you're not seeing things.
		 * This call was done 10 lines up. The first time we have
		 * to default the get_option call to 2.0, so in case of 
		 * a pre-2.0 install or a new install we can use 
		 * version_compare accurately. Now that we're passed the
		 * version check we now need to be able to differentiate
		 * pre-2.0 installs from new installs. Cancel your eye
		 * doctor appointment. Your eyes are fine :-).
		 */
		$version = get_option( 'cfm_current_version', false );
		
		/** 
		 * In old CFM installs, cfm_current_version didn't exist
		 * but instead edd_cfm_version did
		 */
		$old = get_option( 'edd_cfm_version', false );

		// if new install
		if ( !$version && !$old ) {
			$this->cfm_new_install();
			// This is the version used for CFM upgrade routines.
			update_option( 'cfm_db_version', '2.0' );
		} else {
			if ( version_compare( $version, '2.0', '<' ) ) {
				$this->cfm_v20_upgrades();
			}

			/** 
			 * The Great CFM Eraser
			 *
			 * When you were a child you probably
			 * did all of your exams in pencil so you
			 * could correct mistakes later using an 
			 * eraser. This is sort of like a giant
			 * virtual eraser. We use schema correction
			 * to correct past mistakes (or "features")
			 * involving the saved schema (aka characteristics)
			 * of fields and forms. 
			 *
			 * Example:
			 * If a built in field saved without a `name` attribute
			 * we'd use schema correction to automatically fix this 
			 * mistake.
			 */
			$this->schema_corrector();
		}

		// This is the version of CFM installed
		update_option( 'cfm_current_version', '2.0' );

		// There's no code for this function below this. Just an explanation
		// of the CFM core options.

		/** 
		 * Explanation of CFM core options
		 *
		 * cfm_current_version: This starts with the actual version CFM was
		 * 						installed on. We use this version to 
		 * 						determine whether or not a site needs
		 * 						to run one of the behind the scenes
		 * 						CFM upgrade routines. This version is updated
		 * 						every time a minor or major background upgrade
		 * 						routine is run. Generally lags behind the 
		 * 						CFM_VERSION constant by at most a couple minor
		 * 						versions. Never lags behind by 1 major version
		 * 						or more.
		 *
		 * cfm_db_version: 		This is different from cfm_current_version.
		 * 						Unlike the former, this is used to determine
		 * 						if a site needs to run a *user* initiated
		 * 						upgrade routine (see CFM_Upgrade class). This
		 * 						value is only update when a user initiated
		 * 						upgrade routine is done. Because we do very
		 * 						few user initiated upgrades compared to 
		 * 						automatic ones, this version can lag behind by
		 * 						2 or even 3 major versions. Generally contains
		 * 						the current major version.
		 */			
	}

	/**
	 * New CFM Install routine.
	 *
	 * This function installs all of the default
	 * things on new CFM installs. Flight 4953 with 
	 * non-stop service to a whole world of 
	 * possibilities is now boarding.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @uses CFM_Install::create_default_checkout_form() Creates the CFM default checkout form.
	 * 
	 * @return void
	 */
	public function cfm_new_install() {
		$this->create_default_checkout_form();
	}

	/**
	 * CFM Version 2.0 upgrades.
	 *
	 * This function used to do the
	 * upgrade routine from CFM 1.x->2.0.
	 *
	 * @since 2.0.0
	 * @access public
	 * 
	 * @return void
	 */
	public function cfm_v20_upgrades() {
		$post_id = get_option( 'edd_cfm_id', false );
		update_option( 'cfm-checkout-form', $post_id );
		$fields = get_post_meta( $post_id, 'edd-checkout-fields', true );
		update_post_meta( $post_id, 'cfm-form', $fields );
		update_post_meta( $post_id, 'cfm-form-name', 'checkout' );
		update_post_meta( $post_id, 'cfm-form-class', 'CFM_Checkout_Form' );
	}

	/**
	 * CFM Schema correction.
	 * 
	 * When you were a child you probably
	 * did all of your exams in pencil so you
	 * could correct mistakes later using an 
	 * eraser. This is sort of like a giant
	 * virtual eraser. We use schema correction
	 * to correct past mistakes (or "features")
	 * involving the saved schema (aka characteristics)
	 * of fields and forms. If a built in field saved 
	 * without a `name` attribute we'd use schema correction
	 * to automatically fix this mistake.
	 *
	 * @since 2.0.0
	 * @access public
	 * 
	 * @return void
	 */
	public function schema_corrector(){
		// @todo: extend this into a for loop on the post type
		
		$post_id = get_option( 'cfm-checkout-form', false );
		if ( $post_id !== false ){
			$old_fields = get_post_meta( $post_id, 'cfm-form', true );
			if ( is_array( $old_fields ) ) {
				foreach ( $old_fields as $id => $field ) {
					$field = cfm_upgrade_field( $field ); // upgrade field
					$old_fields[ $id ] = $field; // save new field back
				}
				update_post_meta( $post_id, 'cfm-form', $old_fields );
			}
		}
	}

	/**
	 * Create Default Checkout Form.
	 * 
	 * Checks to ensure the checkout
	 * form doesn't already exist
	 * and if it doesn't then creates it, and
	 * inserts the post id into the CFM settings.
	 *
	 * @since 2.0.0
	 * @access public
	 * 
	 * @return void
	 */
	public function create_default_checkout_form() {
		$post_id = get_option( 'cfm-checkout-form', false );
		if ( $post_id ) {
			return;
		}
		
		$data = array(
			'post_status' => 'publish',
			'post_type' => 'edd-checkout-fields',
			'post_author' => get_current_user_id(),
			'post_title' => __( 'Checkout Fields', 'edd_cfm' )
		);
		$post_id   = wp_insert_post( $data );
		cfm_save_initial_checkout_form( $post_id );
	}
}
