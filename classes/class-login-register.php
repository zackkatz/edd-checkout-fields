<?php
if ( !defined( 'ABSPATH' ) ) {
	exit;
}

class FES_Login_Register
{
	function __construct()
	{
		add_action('init', array($this,'fes_add_new_member'));
		add_shortcode('edd_fes_login_form',  array($this,'edd_fes_login_form'));		
		add_shortcode('edd_fes_register_form',  array($this,'edd_fes_register_form'));
		add_shortcode('edd_fes_combo_form',  array($this,'edd_fes_combo_form'));
		add_action( 'init', array($this, 'fes_login_member' ) );

	}
			
    function display_login_form(){
		ob_start(); 
		
		// show any error messages after form submission
		EDD_FES()->login_register->fes_login_show_error_messages(); ?>
		
			<form id="fes_login_form"  class="fes-form" action="" method="POST">
				<table>
				<h3 class="fes_header"><?php _e('Login'); ?></h3>
				<tr>
					<td>
						<label for="fes_user_login">Username</label>
					</td>
					<td>
						<?php $value = isset( $_POST[ 'fes_user_login' ] ) ? $_POST[ 'fes_user_login' ] : ''; ?>
						<input name="fes_user_login" id="fes_user_login" class="required" type="text" value="<?php echo $value; ?>" />
					</td>
				</tr>
				<tr>
					<td>
						<label for="fes_user_pass">Password</label>
					</td>
					<td>
						<input name="fes_user_pass" id="fes_user_pass" class="required" type="password"/>
					</td>
				</tr>
				<?php do_action('edd_fes_in_login_form'); ?>
				<tr>
					<td colspan="2">
						<input type="hidden" name="fes_login_nonce" value="<?php echo wp_create_nonce('fes-login-nonce'); ?>"/>
						<input id="fes_login_submit" type="submit" value="Login"/>
					</td>
				</tr>
				</table>
			</form>
		<?php
		return ob_get_clean();
	}
	function display_register_form(){
		ob_start();
		
		// show any error messages after form submission
		EDD_FES()->login_register->fes_register_show_error_messages(); ?>
			<form id="fes_registration_form" class="fes-form" action="" method="POST">
				<table>
					<h3 class="fes_header"><?php _e('Register New Vendor Account'); ?></h3>
					<tr>
						<td>
							<label for="fes_user_first"><?php _e('First Name'); ?></label>
						</td>
						<td>
							<?php $value = isset( $_POST[ 'fes_user_first' ] ) ? $_POST[ 'fes_user_first' ] : ''; ?>
							<input name="fes_user_first" id="fes_user_first" class="required" type="text" value="<?php echo $value; ?>" />
						</td>
					</tr>
					<tr>
						<td>
							<label for="fes_user_last"><?php _e('Last Name'); ?></label>
						</td>
						<td>
							<?php $value = isset( $_POST[ 'fes_user_last' ] ) ? $_POST[ 'fes_user_last' ] : ''; ?>
							<input name="fes_user_last" id="fes_user_last" class="required" type="text" value="<?php echo $value; ?>" />
						</td>
					</tr>				
					<tr>
						<td>
							<label for="fes_user_email"><?php _e('Email'); ?></label>
						</td>
						<td>
							<?php $value = isset( $_POST[ 'fes_user_email' ] ) ? $_POST[ 'fes_user_email' ] : ''; ?>
							<input name="fes_user_email" id="fes_user_email" class="required" type="email" value="<?php echo $value; ?>" />
						</td>
					</tr>
					<tr>
						<td>
							<label for="fes_registration_login"><?php _e('Username'); ?></label>
						</td>
						<td>
							<?php $value = isset( $_POST[ 'fes_registration_login' ] ) ? $_POST[ 'fes_registration_login' ] : ''; ?>
							<input name="fes_registration_login" id="fes_registration_login" class="required" type="text" value="<?php echo $value; ?>" />
						</td>
					</tr>
					<tr>
						<td>
							<label for="fes_user_pass"><?php _e('Password'); ?></label>
						</td>
						<td>						
							<?php $value = isset( $_POST[ 'fes_user_pass' ] ) ? $_POST[ 'fes_user_pass' ] : ''; ?>
							<input name="fes_user_pass" id="fes_user_pass" class="required" type="password" value="<?php echo $value; ?>" />
						</td>
					</tr>
					<?php 
					if($terms_page = EDD_FES()->fes_options->get_option( 'terms_to_apply_page' )){	?>
					<tr>
						<td>
							<label for="fes_user_agree"><?php _e('Terms of Service', 'edd_fes');?></label>
						</td>
						<td>
							<?php printf(__( 'I accept the <a href="%s">Terms of Service</a>', 'edd_fes' ), get_permalink( $terms_page ) ); ?>
							<input class="input-checkbox" id="fes_user_agree" <?php checked( isset( $_POST['fes_user_agree'] ), true ) ?> type="checkbox" name="fes_user_agree" value="1" />
						</td>
					</tr>
					<?php } ?>
					<?php do_action('edd_fes_in_register_form'); ?>
					<tr>
						<td colspan="2">
							<input type="hidden" name="fes_register_nonce" value="<?php echo wp_create_nonce('fes-register-nonce'); ?>"/>
							<input type="submit" value="<?php _e('Register Your Account'); ?>"/>
						</td>
					</tr>
				</table>
			</form>
		<?php

		return ob_get_clean();
	}
	// register a new user
	function fes_add_new_member() {
		if (isset( $_POST["fes_registration_login"] ) &&  isset($_POST['fes_register_nonce']) &&  wp_verify_nonce($_POST['fes_register_nonce'], 'fes-register-nonce')) {
			$user_login		= $_POST["fes_registration_login"];	
			$user_email		= $_POST["fes_user_email"];
			$user_first 	= $_POST["fes_user_first"];
			$user_last	 	= $_POST["fes_user_last"];
			$user_pass		= $_POST["fes_user_pass"];
			
			if ( EDD_FES()->fes_options->get_option( 'terms_to_apply_page' )){
			$user_accept    = $_POST["fes_user_agree"];
			
				if(!isset($user_accept) || $user_accept != 1 ){
					EDD_FES()->login_register->fes_register_errors()->add('user_not_accept', __('You must agree to the terms'));				
				}
			}
			
			if(!isset($user_first) || $user_first == ''){
				// No first name? Better validation in 2.1
				EDD_FES()->login_register->fes_register_errors()->add('first_name_empty', __('Please enter your first name'));			
			}
			if(!isset($user_last)|| $user_last == ''){
				// No first name? Better validation in 2.1
				EDD_FES()->login_register->fes_register_errors()->add('last_name_empty', __('Please enter your last name'));			
			}
			if(!isset($user_email )|| $user_email == ''){
				// No first name? Better validation in 2.1
				EDD_FES()->login_register->fes_register_errors()->add('email_name_empty', __('Please enter your email'));			
			}
			if(username_exists($user_login)) {
				// Username already registered
				EDD_FES()->login_register->fes_register_errors()->add('username_unavailable', __('Username already taken'));
			}
			if(!validate_username($user_login)) {
				// invalid username
				EDD_FES()->login_register->fes_register_errors()->add('username_invalid', __('Invalid username'));
			}
			if($user_login == '') {
				// empty username
				EDD_FES()->login_register->fes_register_errors()->add('username_empty', __('Please enter a username'));
			}
			if( $user_email != '' && !is_email($user_email)) {
				//invalid email
				EDD_FES()->login_register->fes_register_errors()->add('email_invalid', __('Invalid email'));
			}
			if( $user_email != '' && email_exists($user_email)) {
				//Email address already registered
				EDD_FES()->login_register->fes_register_errors()->add('email_used', __('Email already registered'));
			}
			if($user_pass == '') {
				// passwords do not match
				EDD_FES()->login_register->fes_register_errors()->add('password_empty', __('Please enter a password'));
			}
			do_action('edd_fes_errors_in_register_form', $_POST); 
			
			$errors = EDD_FES()->login_register->fes_register_errors()->get_error_messages();
			
			// only create the user in if there are no errors
			if(empty($errors)) {
				if( (bool) EDD_FES()->fes_options->get_option( 'edd_fes_auto_approve_vendors' )){
					$role = 'frontend_vendor';
				} else {
					$role = 'pending_vendor';
				}
				
				$new_user_id = wp_insert_user(array(
						'user_login'		=> $user_login,
						'user_pass'	 		=> $user_pass,
						'user_email'		=> $user_email,
						'first_name'		=> $user_first,
						'last_name'			=> $user_last,
						'user_registered'	=> date('Y-m-d H:i:s'),
						'role'				=> $role
					)
				);
				do_action('edd_fes_register_form');
				if($new_user_id) {		
					if(! (bool) EDD_FES()->fes_options->get_option( 'edd_fes_auto_approve_vendors' )){
						EDD_FES()->emails->notify_user_new_app($new_user_id);
						EDD_FES()->emails->notify_admin_new_app($new_user_id);
					}
					else{
						EDD_FES()->emails->fes_notify_user_app_accepted($new_user_id);
						$user = new WP_User($new_user_id);
						$user->add_cap( 'fes_is_vendor');
					}
					wp_new_user_notification($new_user_id);				
					$user = new WP_User($new_user_id);
					// log the new user in
					wp_set_auth_cookie($new_user_id, true);
					wp_set_current_user($new_user_id, $user_login);	
					do_action('wp_login', $user_login);
					
					// send the newly created user to the home page after logging them in
					wp_redirect(get_permalink(EDD_FES()->fes_options->get_option( 'vendor-dashboard-page' ))); exit;
				}
				
			}
		
		}
	}


	// logs a member in after submitting a form
	function fes_login_member() {
		if(isset($_POST['fes_user_login']) && isset($_POST['fes_login_nonce']) && wp_verify_nonce($_POST['fes_login_nonce'], 'fes-login-nonce')) {
					
			// this returns the user ID and other info from the user name
			$user = get_user_by('login',$_POST['fes_user_login']);
			
			if(!$user || !is_object($user)) {
				// if the user name doesn't exist
				EDD_FES()->login_register->fes_login_errors()->add('empty_username', __('Incorrect username'));
			}
			
			if(!isset($_POST['fes_user_pass']) || $_POST['fes_user_pass'] == '') {
				// if no password was entered
				EDD_FES()->login_register->fes_login_errors()->add('empty_password', __('Please enter a password'));
			}
			
			// check the user's login with their password
			if(is_object($user) && $_POST['fes_user_pass'] != '' && !wp_check_password($_POST['fes_user_pass'], $user->user_pass, $user->ID)) {
				// if the password is incorrect for the specified user
				EDD_FES()->login_register->fes_login_errors()->add('empty_password', __('Incorrect password'));
			}
			
			do_action('edd_fes_errors_in_login_form');
			// retrieve all error messages
			$errors = EDD_FES()->login_register->fes_login_errors()->get_error_messages();
			
			// only log the user in if there are no errors
			if(empty($errors)) {
				$user = get_user_by('login',$_POST['fes_user_login']);
				wp_set_auth_cookie($user->ID, true);
				wp_set_current_user($user->ID, $_POST['fes_user_login']);	
				do_action('wp_login', $_POST['fes_user_login']);
				do_action('edd_fes_login_form');
				if(EDD_FES()->vendor_permissions->vendor_is_vendor($user->ID)){
					wp_redirect(get_permalink(EDD_FES()->fes_options->get_option( 'vendor-dashboard-page' ))); exit;
				}
				else {
					EDD_FES()->vendor_permissions->vendor_not_a_vendor_redirect($user->ID);
				}
			}
		}
	}


	// displays error messages from form submissions
	function fes_login_show_error_messages() {
		if($codes = EDD_FES()->login_register->fes_login_errors()->get_error_codes()) {
			echo '<div class="fes_login_errors edd_errors">';
			    // Loop error codes and display errors
			   foreach($codes as $code){
			        $message = EDD_FES()->login_register->fes_login_errors()->get_error_message($code);
			        echo '<span class="fes-error edd_error"><strong>' . __('Error') . '</strong>: ' . $message . '</span><br/>';
			    }
			echo '</div>';
		}	
	}

	// used for tracking error messages
	function fes_login_errors(){
	    static $wp_error; // Will hold global variable safely
	    return isset($wp_error) ? $wp_error : ($wp_error = new WP_Error(null, null, null));
	} 
	// displays error messages from form submissions
	function fes_register_show_error_messages() {
		if($codes = EDD_FES()->login_register->fes_register_errors()->get_error_codes()) {
			echo '<div class="fes_login_errors edd_errors">';
			    // Loop error codes and display errors
			   foreach($codes as $code){
			        $message = EDD_FES()->login_register->fes_register_errors()->get_error_message($code);
			        echo '<span class="fes-error edd_error"><strong>' . __('Error') . '</strong>: ' . $message . '</span><br/>';
			    }
			echo '</div>';
		}	
	}

	// used for tracking error messages
	function fes_register_errors(){
	    static $wp_error; // Will hold global variable safely
	    return isset($wp_error) ? $wp_error : ($wp_error = new WP_Error(null, null, null));
	} 

	function edd_fes_login_form(){
		if(!is_user_logged_in()){
			return EDD_FES()->login_register->display_login_form();
		}
		else{
			EDD_FES()->vendor_permissions->vendor_not_a_vendor_redirect();
		}
	}

	function edd_fes_register_form(){
		if(!is_user_logged_in()){
			return EDD_FES()->login_register->display_register_form();		
		}
		else{
			EDD_FES()->vendor_permissions->vendor_not_a_vendor_redirect();
		}
	}
	function edd_fes_combo_form(){
		if(!is_user_logged_in()){
			// if registrations allowed
			echo '<table><tr><td id="fes_half" style="width: 48%; clear: none; float: left;">';
			// endif
			echo EDD_FES()->login_register->display_login_form();
			// if registrations allowed
			echo '</td><td id="fes_half" style="width: 48%; float: left;">';
			// endif
			if(EDD_FES()->fes_options->get_option( 'show_vendor_registration')){
			echo EDD_FES()->login_register->display_register_form();
			}
			// if registrations allowed
			echo '</td></tr></div></table>';
			// endif
		}
		else{
			EDD_FES()->vendor_permissions->vendor_not_a_vendor_redirect();
		}
	}
	
}
