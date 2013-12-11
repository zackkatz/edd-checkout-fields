<div id="fes-vendor-dashboard">
	<div id="fes-vendor-store-link">
	<?php
		$user = new WP_User(get_current_user_id());
		$user = get_userdata(get_current_user_id());
		$vendor_url = add_query_arg( 'vendor', $user->user_nicename, get_permalink( EDD_FES()->fes_options->get_option( 'vendor-page') ) );
		printf( __(' Your store url is: %s', 'edd_fes' ), '<a href="' . $vendor_url . '">' . $vendor_url . '</a>' );
	?>
	</div>
	<br />
	<?php echo apply_filters( 'the_content', EDD_FES()->fes_options->get_option( 'dashboard-page-template' ) ); ?>
</div>
