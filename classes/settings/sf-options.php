<?php
$options = array();
$options[] = array( 'name' => __( 'General', 'edd_fes' ), 'type' => 'heading' );
$options[] = array( 'name' => __( 'General options', 'edd_fes' ), 'type' => 'title');
$options[] = array(
	 'name' => __( 'Registration', 'edd_fes' ),
	 'desc' => __( 'Allow users or guests to apply to become a vendor', 'edd_fes' ),
	 'tip'  => __( 'Disable the ability on the Vendor Dashboard for users to apply to become a vendor.', 'edd_fes' ),
	 'id'   => 'show_vendor_registration',
	 'type' => 'checkbox',
	 'std'  => true,
);
$options[] = array(
	'name' => __( 'Allow WP Backend Access to Vendors?', 'edd_fes' ),
	'tip'  => __('Not Recommended', 'edd_fes'),
	'std'  => '1',
	'id'   => 'vendors_bea',
	'type' => 'radio',
	'options' => array(
		'1' => 'No',
		'2' => 'Yes'
	)
);
$options[] = array(
	 'name' => __( 'Automatically Approve Vendors?', 'edd_fes' ),
	 'desc' => __( 'If checked, vendors will automatically be approved', 'edd_fes' ),
	 'id'   => 'edd_fes_auto_approve_vendors',
	 'type' => 'checkbox',
	 'std'  => true,
);
$options[] = array(
	 'name' => __( 'Automatically Approve Submissions?', 'edd_fes' ),
	 'desc' => __( 'If checked, submissions will automatically be approved', 'edd_fes' ),
	 'id'   => 'edd_fes_auto_approve_submissions',
	 'type' => 'checkbox',
	 'std'  => false,
);
$options[] = array(
	 'name' => __( 'Show custom fields on the post? (beta)', 'edd_fes' ),
	 'desc' => __( 'If checked, custom fields on downloads will be shown on the product page', 'edd_fes' ),
	 'id'   => 'edd_fes_show_custom_meta',
	 'type' => 'checkbox',
	 'std'  => false,
);
$options[] = array(
	 'name' => __( 'Use FES\'s CSS', 'edd_fes' ),
	 'desc' => __( 'Highly Recommended. Those who don\'t are braver souls than most.', 'edd_fes' ),
	 'id'   => 'edd_fes_use_css',
	 'type' => 'checkbox',
	 'std'  => true,
);
$options[] = array(
    'name' => __( 'Dashboard Page Template', 'edd_fes' ),
    'desc' => __( 'This is what\'s shown on the dashboard for logged in vendors', 'edd_fes' ),
    'id'   => 'dashboard-page-template',
    'type' => 'wysiwyg',
);
$options[] = array( 'name' => __( 'Pages', 'edd_fes' ), 'type' => 'heading' );
$options[] = array( 'name' => __( 'Page configuration', 'edd_fes' ), 'type' => 'title');

$options[] = array(
	'name' => __( 'Vendor dashboard', 'edd_fes' ),
	'desc' => __( 'Choose the page that has the shortcode <code>[fes_vendor_dashboard]</code><br/>By default, Vendor Dashboard should have the shortcode.', 'edd_fes' ),
	'id'   => 'vendor-dashboard-page',
	'type' => 'single_select_page',
	'select2' => true,
);

$options[] = array(
	'name' => __( 'Vendor Page', 'edd_fes' ),
	'desc' => __( 'Choose the page used for vendor store pages', 'edd_fes' ),
	'id'   => 'vendor-page',
	'type' => 'single_select_page',
	'select2' => true,
);

$options[] = array(
	'name' => __( 'Vendor Terms', 'edd_fes' ),
	'desc' => __( 'These terms are shown to a user when submitting an application to become a vendor.<br/>If left blank, no terms will be shown to the applicant.', 'edd_fes' ),
	'id'   => 'terms_to_apply_page',
	'type' => 'single_select_page',
	'select2' => true,
);
$options[] = array( 'name' => __( 'Emails', 'edd_fes' ), 'type' => 'heading' );
$options[] = array( 'name' => __( 'Admin Emails', 'edd_fes' ), 'type' => 'title', 'desc' => __( 'Emails sent to admin', 'edd_fes' ) );
$options[] = array(
	 'name' => __( 'Admin Email Toggle', 'edd_fes' ),
	 'desc' => __( 'If unchecked, no emails will be sent to the admin users', 'edd_fes' ),
	 'id'   => 'edd_fes_notify_admin_new_app_toggle',
	 'type' => 'checkbox',
	 'std'  => true,
);
$options[] = array(
	 'name'    => __( 'New Vendor Application', 'edd_fes' ),
	 'desc'    => __( 'Email sent to admin on user applying to become a vendor', 'edd_fes' ),
	 'id'      => 'edd_fes_notify_admin_new_app_message',
	 'type'     => 'textarea',
 );
$options[] = array(
	 'name'    => __( 'Submission Received Email', 'edd_fes' ),
	 'desc'    => __( 'Email sent to admin when their submission is received', 'edd_fes' ),
	 'id'      => 'new_edd_fes_submission_admin_message',
	 'type'     => 'textarea',
 );
 $options[] = array( 'name' => __( 'Vendor Emails', 'edd_fes' ), 'type' => 'title', 'desc' => __( 'Emails Sent to Vendors', 'edd_fes' ) );
$options[] = array(
	 'name'    => __( 'Vendor Application received Email', 'edd_fes' ),
	 'desc'    => __( 'Email sent to user after they apply to become a vendor', 'edd_fes' ),
	 'id'      => 'edd_fes_notify_user_new_app_message',
	 'type'     => 'textarea',
 );
$options[] = array(
	 'name'    => __( 'Vendor Approved Email', 'edd_fes' ),
	 'desc'    => __( 'Email sent to user on approving them as a vendor', 'edd_fes' ),
	 'id'      => 'edd_fes_notify_user_app_accepted_message',
	 'type'     => 'textarea',
 );
$options[] = array(
	 'name'    => __( 'Vendor Denied Email', 'edd_fes' ),
	 'desc'    => __( 'Email sent to user on denying them as a vendor', 'edd_fes' ),
	 'id'      => 'edd_fes_notify_user_app_denied_message',
	 'type'     => 'textarea',
 );
$options[] = array(
	 'name'    => __( 'Submission Received Email', 'edd_fes' ),
	 'desc'    => __( 'Email sent to user when their submission is received', 'edd_fes' ),
	 'id'      => 'new_edd_fes_submission_user_message',
	 'type'     => 'textarea',
 );
 $options[] = array(
	 'name'    => __( 'Submission Accepted Email', 'edd_fes' ),
	 'desc'    => __( 'Email sent to user when their submission is accepted', 'edd_fes' ),
	 'id'      => 'edd_fes_submission_accepted_message',
	 'type'     => 'textarea',
 );
$options[] = array(
	 'name'    => __( 'Submission Declined Email', 'edd_fes' ),
	 'desc'    => __( 'Email sent to user when their submission is declined', 'edd_fes' ),
	 'id'      => 'edd_fes_submission_declined_message',
	 'type'     => 'textarea',
 );
$options[] = array( 'name' => __( 'Integrations', 'edd_fes' ), 'type' => 'heading' );
$options[] = array( 'name' => __( 'reCAPTCHA', 'edd_fes' ), 'type' => 'title' );
$options[] = array(
    'name' => __( 'Public Key', 'edd_fes' ),
    'desc' => __( 'Please your public reCAPTCHA key here', 'edd_fes' ),
    'id'   => 'recaptcha_public',
    'type' => 'text',
);
$options[] = array(
    'name' => __( 'Private Key', 'edd_fes' ),
    'desc' => __( 'Please your private reCAPTCHA key here', 'edd_fes' ),
    'id'   => 'recaptcha_private',
    'type' => 'text',
);