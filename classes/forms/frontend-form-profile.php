<?php
if ( !defined( 'ABSPATH' ) ) {
	exit;
}

class FES_Frontend_Form_Profile extends FES_Render_Form {
	function __construct() {
		add_shortcode( 'fes_profile', array(
			 $this,
			'shortcode_handler' 
		) );
		add_action( 'wp_ajax_fes_update_profile', array(
			 $this,
			'update_profile' 
		) );
	}
	
	public function shortcode_handler() {
		ob_start();
		$id            = EDD_FES()->fes_options->get_option( 'fes-profile-form' );
		$form_vars     = get_post_meta( $id, self::$meta_key, true );
		$form_settings = get_post_meta( $id, 'fes-form_settings', true );
		if ( !$form_vars ) {
			return;
		}
		if ( isset( $_GET[ 'msg' ] ) && $_GET[ 'msg' ] == 'profile_update' ) {
			echo '<div class="fes-success">';
			echo 'Updated Successfully'; //$form_settings['update_message'];
			echo '</div>';
		}
		$this->profile_edit( $id, $form_vars, $form_settings );
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}
	
	public function profile_edit( $form_id, $form_vars, $form_settings ) {
		echo '<form class="fes-form-add" action="" method="post">';
		echo '<div class="fes-form">';
		$this->render_items( $form_vars, get_current_user_id(), 'user', $form_id, $form_settings );
		$this->submit_button( $form_id, $form_settings, 0 );
		echo '</div>';
		echo '</form>';
	}
	
	public function submit_button( $form_id, $form_settings, $post_id = 0 ) {
		$form_settings[ 'update_text' ] = 'Submit';
?>
        <li class="fes-submit">
            <div class="fes-label">
                &nbsp;
            </div>

            <?php
		wp_nonce_field( 'fes-form_add' );
?>
            <input type="hidden" name="form_id" value="<?php
		echo $form_id;
?>">
            <input type="hidden" name="page_id" value="<?php
		echo get_the_ID();
?>">

                <input type="hidden" name="action" value="fes_update_profile">
                <input type="submit" name="submit" value="<?php
		echo $form_settings[ 'update_text' ];
?>" />
        </li>
        <?php
	}
	
	public static function update_user_meta( $meta_vars, $user_id ) {
		// prepare meta fields
		list( $meta_key_value, $multi_repeated, $files ) = self::prepare_meta_fields( $meta_vars );
		// set featured image if there's any
		if ( isset( $_POST[ 'fes_files' ][ 'avatar' ] ) ) {
			$attachment_id = $_POST[ 'fes_files' ][ 'avatar' ][ 0 ];
			fes_update_avatar( $user_id, $attachment_id );
		}
		// save all custom fields
		foreach ( $meta_key_value as $meta_key => $meta_value ) {
			update_user_meta( $user_id, $meta_key, $meta_value );
		}
		// save any multicolumn repeatable fields
		foreach ( $multi_repeated as $repeat_key => $repeat_value ) {
			// first, delete any previous repeatable fields
			delete_user_meta( $user_id, $repeat_key );
			// now add them
			foreach ( $repeat_value as $repeat_field ) {
				add_user_meta( $user_id, $repeat_key, $repeat_field );
			}
		} //foreach
		// save any files attached
		foreach ( $files as $file_input ) {
			// delete any previous value
			delete_user_meta( $user_id, $file_input[ 'name' ] );
			foreach ( $file_input[ 'value' ] as $attachment_id ) {
				add_user_meta( $user_id, $file_input[ 'name' ], $attachment_id );
			}
		}
	}
	
	public function update_profile() {
		check_ajax_referer( 'fes-form_add' );
		@header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );
		$form_id       = isset( $_POST[ 'form_id' ] ) ? intval( $_POST[ 'form_id' ] ) : 0;
		$form_vars     = $this->get_input_fields( $form_id );
		$form_settings = get_post_meta( $form_id, 'fes-form_settings', true );
		list( $user_vars, $taxonomy_vars, $meta_vars ) = $form_vars;
		$user_id  = get_current_user_id();
		$userdata = array(
			 'ID' => $user_id 
		);
		if ( $this->search( $user_vars, 'name', 'first_name' ) ) {
			$userdata[ 'first_name' ] = $_POST[ 'first_name' ];
		}
		if ( $this->search( $user_vars, 'name', 'last_name' ) ) {
			$userdata[ 'last_name' ] = $_POST[ 'last_name' ];
		}
		if ( $this->search( $user_vars, 'name', 'nickname' ) ) {
			$userdata[ 'nickname' ] = $_POST[ 'nickname' ];
		}
		if ( $this->search( $user_vars, 'name', 'display_name' ) ) {
			$userdata[ 'display_name' ] = $_POST[ 'display_name' ];
		}
		if ( $this->search( $user_vars, 'name', 'user_url' ) ) {
			$userdata[ 'user_url' ] = $_POST[ 'user_url' ];
		}
		if ( $this->search( $user_vars, 'name', 'user_email' ) ) {
			$userdata[ 'user_email' ] = $_POST[ 'user_email' ];
		}
		if ( $this->search( $user_vars, 'name', 'description' ) ) {
			$userdata[ 'description' ] = $_POST[ 'description' ];
		}
		// check if password filled out
		// verify password
		if ( $pass_element = $this->search( $user_vars, 'name', 'password' ) ) {
			$pass_element    = current( $pass_element );
			$password        = $_POST[ 'pass1' ];
			$password_repeat = $_POST[ 'pass2' ];
			// check only if it's filled
			if ( $pass_length = strlen( $password ) ) {
				// min length check
				if ( $pass_length < intval( $pass_element[ 'min_length' ] ) ) {
					$this->send_error( sprintf( __( 'Password must be %s character long', 'edd_fes' ), $pass_element[ 'min_length' ] ) );
				}
				// repeat password check
				if ( $password != $password_repeat ) {
					$this->send_error( __( 'Password didn\'t match', 'edd_fes' ) );
				}
				// seems like he want to change the password
				$userdata[ 'user_pass' ] = $password;
			}
		}
		$userdata = apply_filters( 'fes_update_profile_vars', $userdata, $form_id, $form_settings );
		$user_id  = wp_update_user( $userdata );
		if ( $user_id ) {
			// update meta fields
			$this->update_user_meta( $meta_vars, $user_id );
			do_action( 'fes_update_profile', $user_id, $form_id, $form_settings );
		}
		//redirect URL
		$redirect_to = get_permalink( $_POST[ 'page_id' ] );
		$redirect_to = add_query_arg( array(
			 'task' => 'profile' 
		), $redirect_to );
		// send the response
		$response    = array(
			 'success' => true,
			'redirect_to' => $redirect_to,
			'message' => 'Profile Successfully Updated',
			'is_post' => false 
		);
		$response    = apply_filters( 'fes_update_profile_resp', $response, $user_id, $form_id, $form_settings );
		echo json_encode( $response );
		exit;
	}
}