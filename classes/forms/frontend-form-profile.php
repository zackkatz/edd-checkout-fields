<?php
if ( !defined( 'ABSPATH' ) ) {
	exit;
}

class FES_Frontend_Form_Profile extends FES_Render_Form {
	public static function update_user_meta( $meta_vars, $user_id ) {
		// prepare meta fields
		list( $meta_key_value, $multi_repeated, $files ) = self::prepare_meta_fields( $meta_vars );
		// save all custom fields
		foreach ( $meta_key_value as $meta_key => $meta_value ) {
			update_user_meta( $user_id, $meta_key, $meta_value );
		}
	}
	
	public function update_profile() {
		list( $user_vars, $taxonomy_vars, $meta_vars ) = $form_vars;
		$user_id  = get_current_user_id();
		$userdata = array(
			 'ID' => $user_id 
		);
		if ( $this->search( $user_vars, 'name', 'edd_first' ) ) {
			$userdata[ 'edd_first' ] = $_POST[ 'edd_first' ];
		}
		if ( $this->search( $user_vars, 'name', 'edd_last' ) ) {
			$userdata[ 'edd_last' ] = $_POST[ 'edd_last' ];
		}
		if ( $this->search( $user_vars, 'name', 'edd_email' ) ) {
			$userdata[ 'edd_email' ] = $_POST[ 'edd_email' ];
		}
		$userdata = apply_filters( 'fes_update_profile_vars', $userdata, $form_id, $form_settings );
		$user_id  = wp_update_user( $userdata );
		if ( $user_id ) {
			// update meta fields
			$this->update_user_meta( $meta_vars, $user_id );
			do_action( 'fes_update_profile', $user_id, $form_id, $form_settings );
		}
	}
}