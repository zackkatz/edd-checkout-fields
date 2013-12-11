<?php
if ( !defined( 'ABSPATH' ) ) {
	exit;
}

class FES_Emails {
	function __construct() {
		add_filter( 'transition_post_status', array(
			 $this,
			'post_status' 
		), 10, 3 );
	}
	
	function post_status( $lateststatus, $previousstatus, $post ) {
		global $current_user;
		global $post;
		// Not an object if its not a draft yet. So prior to autosave this might throw warnings
		// We can prevent this by returning till it's been autosaved. This is when it becomes an obj.
		if ( !is_object( $post ) ) {
			return;
		}
		if ( $post->post_type != 'download' ) {
			return;
		}
		if ( $previousstatus == 'pending' && $lateststatus == 'trash' ) {
			EDD_FES()->emails->edd_fes_submission_declined_email( $post );
		}
	}
	
	public function new_edd_fes_submission_admin( $post ) {
		// Retrieve stored message
		$email   = EDD_FES()->fes_options->get_option( 'new_edd_fes_submission_admin_message' );
		$user    = new WP_User( $post->post_author );
		// Get the body ready
		$message = edd_get_email_body_header();
		$message .= EDD_FES()->emails->edd_fes_replace_placeholders_emails( $post, $email, $user );
		$message .= edd_get_email_body_footer();
		$from_name  = isset( $edd_options[ 'from_name' ] ) ? $edd_options[ 'from_name' ] : get_bloginfo( 'name' );
		$from_email = isset( $edd_options[ 'from_email' ] ) ? $edd_options[ 'from_email' ] : get_option( 'admin_email' );
		$subject    = __( 'New Vendor Submission Received', 'edd_fes' );
		if ( EDD_FES()->fes_options->get_option( 'edd_fes_auto_approve_submissions' ) ) {
			$subject = $subject = __( 'New Vendor Submission Posted', 'edd_fes' );
		}
		$subject = apply_filters( 'edd_fes_sub_admin_email_subj', $subject, 0 );
		$headers = "From: " . stripslashes_deep( html_entity_decode( $from_name, ENT_COMPAT, 'UTF-8' ) ) . " <$from_email>\r\n";
		$headers .= "Reply-To: " . $from_email . "\r\n";
		$headers .= "MIME-Version: 1.0\r\n";
		$headers .= "Content-Type: text/html; charset=utf-8\r\n";
		if ( EDD_FES()->fes_options->get_option( 'edd_fes_notify_admin_new_app_toggle' ) ) {
			wp_mail( edd_get_admin_notice_emails(), $subject, $message, $headers );
		}
	}
	
	public function new_edd_fes_submission_user( $post ) {
		// Retrieve stored message
		$email   = EDD_FES()->fes_options->get_option( 'new_edd_fes_submission_user_message' );
		$user    = new WP_User( $post->post_author );
		// Get the body ready
		$message = edd_get_email_body_header();
		$message .= EDD_FES()->emails->edd_fes_replace_placeholders_emails( $post, $email, $user );
		$message .= edd_get_email_body_footer();
		$from_name  = isset( $edd_options[ 'from_name' ] ) ? $edd_options[ 'from_name' ] : get_bloginfo( 'name' );
		$from_email = isset( $edd_options[ 'from_email' ] ) ? $edd_options[ 'from_email' ] : get_option( 'admin_email' );
		$subject    = apply_filters( 'edd_fes_sub_user_email_subj', __( 'Submission Received', 'edd_fes' ), 0 );
		$headers    = "From: " . stripslashes_deep( html_entity_decode( $from_name, ENT_COMPAT, 'UTF-8' ) ) . " <$from_email>\r\n";
		$headers .= "Reply-To: " . $from_email . "\r\n";
		$headers .= "MIME-Version: 1.0\r\n";
		$headers .= "Content-Type: text/html; charset=utf-8\r\n";
		global $current_user;
		wp_mail( $current_user->user_email, $subject, $message, $headers );
	}
	
	public function edd_fes_submission_declined_email( $post ) {
		// Retrieve stored message
		$email   = EDD_FES()->fes_options->get_option( 'edd_fes_submission_declined_message' );
		$user    = new WP_User( $post->post_author );
		// Get the body ready
		$message = edd_get_email_body_header();
		$message .= EDD_FES()->emails->edd_fes_replace_placeholders_emails( $post, $email, $user );
		$message .= edd_get_email_body_footer();
		$from_name  = isset( $edd_options[ 'from_name' ] ) ? $edd_options[ 'from_name' ] : get_bloginfo( 'name' );
		$from_email = isset( $edd_options[ 'from_email' ] ) ? $edd_options[ 'from_email' ] : get_option( 'admin_email' );
		$subject    = apply_filters( 'edd_fes_submission_declined_message_subj', __( 'Submission Declined :( ', 'edd_fes' ), 0 );
		$headers    = "From: " . stripslashes_deep( html_entity_decode( $from_name, ENT_COMPAT, 'UTF-8' ) ) . " <$from_email>\r\n";
		$headers .= "Reply-To: " . $from_email . "\r\n";
		$headers .= "MIME-Version: 1.0\r\n";
		$headers .= "Content-Type: text/html; charset=utf-8\r\n";
		wp_mail( $user->user_email, $subject, $message, $headers );
	}
	
	public function edd_fes_submission_accepted_email( $post ) {
		// Retrieve stored message
		$email   = EDD_FES()->fes_options->get_option( 'edd_fes_submission_accepted_message' );
		$user    = new WP_User( $post->post_author );
		// Get the body ready
		$message = edd_get_email_body_header();
		$message .= EDD_FES()->emails->edd_fes_replace_placeholders_emails( $post, $email, $user );
		$message .= edd_get_email_body_footer();
		$from_name  = isset( $edd_options[ 'from_name' ] ) ? $edd_options[ 'from_name' ] : get_bloginfo( 'name' );
		$from_email = isset( $edd_options[ 'from_email' ] ) ? $edd_options[ 'from_email' ] : get_option( 'admin_email' );
		$subject    = apply_filters( 'edd_fes_submission_accepted_subj', __( 'Submission Accepted :) ', 'edd_fes' ), 0 );
		$headers    = "From: " . stripslashes_deep( html_entity_decode( $from_name, ENT_COMPAT, 'UTF-8' ) ) . " <$from_email>\r\n";
		$headers .= "Reply-To: " . $from_email . "\r\n";
		$headers .= "MIME-Version: 1.0\r\n";
		$headers .= "Content-Type: text/html; charset=utf-8\r\n";
		wp_mail( $user->user_email, $subject, $message, $headers );
	}
	
	public function edd_fes_replace_placeholders_emails( $post, $email, $user ) {
		global $edd_options;
		$message  = $email;
		$has_tags = ( strpos( $message, '{' ) !== false );
		if ( !$has_tags )
			return $message;
		$firstname = '';
		$lastname  = '';
		$fullname  = '';
		$username  = '';
		if ( isset( $user->ID ) && $user->ID > 0 && isset( $user->first_name ) ) {
			$user_data = get_userdata( $user->ID );
			$firstname = $user->first_name;
			$lastname  = $user->last_name;
			$fullname  = $user->first_name . ' ' . $user->last_name;
			$username  = $user_data->user_login;
		} elseif ( isset( $user->first_name ) ) {
			$firstname = $user->first_name;
			$lastname  = $user->last_name;
			$fullname  = $user->first_name . ' ' . $user->last_name;
			$username  = $user->first_name;
		} else {
			$name      = $user->user_email;
			$firstname = $name;
			$lastname  = $name;
			$fullname  = $name;
			$username  = $name;
		}
		$message = str_replace( '{firstname}', $lastname, $message );
		$message = str_replace( '{lastname}', $firstname, $message );
		$message = str_replace( '{fullname}', $fullname, $message );
		$message = str_replace( '{username}', $username, $message );
		$message = str_replace( '{sitename}', get_bloginfo( 'name' ), $message );
		$message = str_replace( '{post-title}', $post->post_title, $message );
		$message = str_replace( '{post-content}', $post->post_content, $message );
		$message = str_replace( '{post-date}', $post->post_date, $message );
		$message = str_replace( '{post-excerpt}', $post->post_excerpt, $message );
		$message = apply_filters( 'edd_fes_email_template_tags', $message, $email, $post, $user );
		return $message;
	}
	
	// Emails for registration
	public function notify_user_new_app( $user_id ) {
		// Retrieve stored message
		$email   = EDD_FES()->fes_options->get_option( 'edd_fes_notify_user_new_app_message' );
		$user    = new WP_User( $user_id );
		// Get the body ready
		$message = edd_get_email_body_header();
		$message .= EDD_FES()->emails->edd_fes_replace_placeholders_app_emails( $user_id, $email, $user );
		$message .= edd_get_email_body_footer();
		$from_name  = isset( $edd_options[ 'from_name' ] ) ? $edd_options[ 'from_name' ] : get_bloginfo( 'name' );
		$from_email = isset( $edd_options[ 'from_email' ] ) ? $edd_options[ 'from_email' ] : get_option( 'admin_email' );
		$subject    = apply_filters( 'edd_fes_notify_user_new_app_subj', __( 'Application Receieved', 'edd_fes' ), 0 );
		$headers    = "From: " . stripslashes_deep( html_entity_decode( $from_name, ENT_COMPAT, 'UTF-8' ) ) . " <$from_email>\r\n";
		$headers .= "Reply-To: " . $from_email . "\r\n";
		$headers .= "MIME-Version: 1.0\r\n";
		$headers .= "Content-Type: text/html; charset=utf-8\r\n";
		wp_mail( $user->user_email, $subject, $message, $headers );
	}
	
	public function notify_admin_new_app( $user_id ) {
		// Retrieve stored message
		$email   = EDD_FES()->fes_options->get_option( 'edd_fes_notify_admin_new_app_message' );
		$user    = new WP_User( $user_id );
		// Get the body ready
		$message = edd_get_email_body_header();
		$message .= EDD_FES()->emails->edd_fes_replace_placeholders_app_emails( $user_id, $email, $user );
		$message .= edd_get_email_body_footer();
		$from_name  = isset( $edd_options[ 'from_name' ] ) ? $edd_options[ 'from_name' ] : get_bloginfo( 'name' );
		$from_email = isset( $edd_options[ 'from_email' ] ) ? $edd_options[ 'from_email' ] : get_option( 'admin_email' );
		$subject    = apply_filters( 'edd_fes_notify_admin_new_app_subj', __( 'New Vendors Application Receieved', 'edd_fes' ), 0 );
		$headers    = "From: " . stripslashes_deep( html_entity_decode( $from_name, ENT_COMPAT, 'UTF-8' ) ) . " <$from_email>\r\n";
		$headers .= "Reply-To: " . $from_email . "\r\n";
		$headers .= "MIME-Version: 1.0\r\n";
		$headers .= "Content-Type: text/html; charset=utf-8\r\n";
		if ( EDD_FES()->fes_options->get_option( 'edd_fes_notify_admin_new_app_toggle' ) ) {
			wp_mail( edd_get_admin_notice_emails(), $subject, $message, $headers );
		}
	}
	
	public function fes_notify_user_app_accepted( $user_id ) {
		// Retrieve stored message
		$email   = EDD_FES()->fes_options->get_option( 'edd_fes_notify_user_app_accepted_message' );
		$user    = new WP_User( $user_id );
		// Get the body ready
		$message = edd_get_email_body_header();
		$message .= EDD_FES()->emails->edd_fes_replace_placeholders_app_emails( $user_id, $email, $user );
		$message .= edd_get_email_body_footer();
		$from_name  = isset( $edd_options[ 'from_name' ] ) ? $edd_options[ 'from_name' ] : get_bloginfo( 'name' );
		$from_email = isset( $edd_options[ 'from_email' ] ) ? $edd_options[ 'from_email' ] : get_option( 'admin_email' );
		$subject    = apply_filters( 'edd_fes_notify_user_app_accepted_subj', __( 'Application Accepted :)', 'edd_fes' ), 0 );
		$headers    = "From: " . stripslashes_deep( html_entity_decode( $from_name, ENT_COMPAT, 'UTF-8' ) ) . " <$from_email>\r\n";
		$headers .= "Reply-To: " . $from_email . "\r\n";
		$headers .= "MIME-Version: 1.0\r\n";
		$headers .= "Content-Type: text/html; charset=utf-8\r\n";
		wp_mail( $user->user_email, $subject, $message, $headers );
	}
	
	public function fes_notify_user_app_denied( $user_id ) {
		// Retrieve stored message
		$email   = EDD_FES()->fes_options->get_option( 'edd_fes_notify_user_app_denied_message' );
		$user    = new WP_User( $user_id );
		// Get the body ready
		$message = edd_get_email_body_header();
		$message .= EDD_FES()->emails->edd_fes_replace_placeholders_app_emails( $user_id, $email, $user );
		$message .= edd_get_email_body_footer();
		$from_name  = isset( $edd_options[ 'from_name' ] ) ? $edd_options[ 'from_name' ] : get_bloginfo( 'name' );
		$from_email = isset( $edd_options[ 'from_email' ] ) ? $edd_options[ 'from_email' ] : get_option( 'admin_email' );
		$subject    = apply_filters( 'edd_fes_notify_user_app_denied_subj', __( 'Application Denied', 'edd_fes' ), 0 );
		$headers    = "From: " . stripslashes_deep( html_entity_decode( $from_name, ENT_COMPAT, 'UTF-8' ) ) . " <$from_email>\r\n";
		$headers .= "Reply-To: " . $from_email . "\r\n";
		$headers .= "MIME-Version: 1.0\r\n";
		$headers .= "Content-Type: text/html; charset=utf-8\r\n";
		wp_mail( $user->user_email, $subject, $message, $headers );
	}
	
	public function edd_fes_replace_placeholders_app_emails( $user_id, $email, $user ) {
		global $edd_options;
		$message  = $email;
		$has_tags = ( strpos( $message, '{' ) !== false );
		if ( !$has_tags )
			return $message;
		$user     = new WP_User( $user_id );
		$fullname = '';
		if ( isset( $user->ID ) && $user->ID > 0 && isset( $user->first_name ) ) {
			$user_data = get_userdata( $user->ID );
			$firstname = $user->first_name;
			$lastname  = $user->last_name;
			$fullname  = $user->first_name . ' ' . $user->last_name;
			$username  = $user_data->user_login;
		} elseif ( isset( $user->first_name ) ) {
			$firstname = $user->first_name;
			$lastname  = $user->last_name;
			$fullname  = $user->first_name . ' ' . $user->last_name;
			$username  = $user->first_name;
		} else {
			$name      = $user->user_email;
			$firstname = $name;
			$lastname  = $name;
			$fullname  = $name;
			$username  = $name;
		}
		$message = str_replace( '{firstname}', $firstname, $message );
		$message = str_replace( '{lastname}', $lastname, $message );
		$message = str_replace( '{fullname}', $fullname, $message );
		$message = str_replace( '{username}', $username, $message );
		$message = str_replace( '{sitename}', get_bloginfo( 'name' ), $message );
		$message = apply_filters( 'edd_fes_app_email_template_tags', $message, $email, $user );
		return $message;
	}
}