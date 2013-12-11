<?php
if ( !defined( 'ABSPATH' ) ) {
	exit;
}

class FES_Application
{
	function __construct()
	{
		add_action('init', array($this,'fes_application'));
		add_shortcode('edd_fes_application_form',  array($this,'edd_fes_application_form'));
	}

	function display_application_form(){
		ob_start();
		
		// show any error messages after form submission
		EDD_FES()->application->fes_application_show_error_messages(); ?>
			<form id="fes_application_form" class="fes-form" action="" method="POST">
				<table>
					<h3 class="fes_header"><?php _e('Apply to Become a Vendor'); ?></h3>
					<?php 
					$terms_page = EDD_FES()->fes_options->get_option( 'terms_to_apply_page');?>
					<tr>
						<td>
							<?php _e('You are not currently a vendor. You may apply to become a vendor:','edd_fes'); ?>
							</td>
					</tr>
					<tr>
						<td>
							<?php printf(__( 'I accept the <a href="%s">Terms of Service</a>', 'edd_fes' ), get_permalink( $terms_page ) ); ?>
							<input class="input-checkbox" id="fes_user_agree" <?php checked( isset( $_POST['fes_user_agree'] ), 1); ?> type="checkbox" name="fes_user_agree" />
						</td>
					</tr>
					<tr>
						<td>
							<input type="hidden" name="fes_application_nonce" value="<?php echo wp_create_nonce('fes-application-nonce'); ?>"/>
							<input type="hidden" name="fes_application_nonce2" value="<?php echo wp_create_nonce('fes-application-nonce2'); ?>"/>
							<input type="submit"  value="<?php _e('Submit Application'); ?>"/>
						</td>
					</tr>
				</table>
			</form>
		<?php

		return ob_get_clean();
	}
	// application a new user
	function fes_application() {
		if (isset($_POST['fes_application_nonce']) &&  wp_verify_nonce($_POST['fes_application_nonce'], 'fes-application-nonce') && isset($_POST['fes_application_nonce2']) &&  wp_verify_nonce($_POST['fes_application_nonce2'], 'fes-application-nonce2')) {
			$user_accept  = isset($_POST["fes_user_agree"])? $_POST["fes_user_agree"] : '';
			if(!isset($user_accept) || $user_accept !== 'on' ){
				EDD_FES()->application->fes_application_errors()->add('user_not_accept', __('You must agree to the terms'));				
			} 
			$errors = EDD_FES()->application->fes_application_errors()->get_error_messages();
			if(empty($errors)) {
				$user = new WP_User( get_current_user_id() );
				if( (bool) EDD_FES()->fes_options->get_option( 'edd_fes_auto_approve_vendors' ) ) {
					$user->set_role('frontend_vendor');
					$user->add_cap( 'fes_is_vendor');
					EDD_FES()->emails->fes_notify_user_app_accepted(get_current_user_id());
				} else if( ! (bool) EDD_FES()->fes_options->get_option( 'edd_fes_auto_approve_vendors' ) ) {
					EDD_FES()->emails->notify_user_new_app(get_current_user_id());
					EDD_FES()->emails->notify_admin_new_app(get_current_user_id());
				}
				else{
					EDD_FES()->emails->fes_notify_user_app_accepted(get_current_user_id());
				}
				wp_new_user_notification(get_current_user_id());		
				wp_redirect(get_permalink(EDD_FES()->fes_options->get_option( 'vendor-dashboard-page' ))); exit;
			}
		
		}
	}


// displays error messages from form submissions
function fes_application_show_error_messages() {
	if($codes = EDD_FES()->application->fes_application_errors()->get_error_codes()) {
		echo '<div class="fes_application_errors">';
		    // Loop error codes and display errors
		   foreach($codes as $code){
		        $message = EDD_FES()->application->fes_application_errors()->get_error_message($code);
		        echo '<span class="fes-error"><strong>' . __('Error') . '</strong>: ' . $message . '</span><br/>';
		    }
		echo '</div>';
	}	
}

// used for tracking error messages
function fes_application_errors(){
    static $wp_error; // Will hold global variable safely
    return isset($wp_error) ? $wp_error : ($wp_error = new WP_Error(null, null, null));
} 

function edd_fes_application_form(){
	if(is_user_logged_in()){
		return EDD_FES()->application->display_application_form();		
	}
	// TODO: maybe show a button to bring to teh dashboard
}
	
}
