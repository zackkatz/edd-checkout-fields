<?php
if ( !defined( 'ABSPATH' ) ) {
	exit;
}

class FES_Vendor_Shop {
	public function __construct() {
		add_filter( 'init', array(
			 $this,
			'add_rewrite_rules' 
		), 0 );
		add_action( 'pre_get_posts', array(
			 $this,
			'vendor_me' 
		) );
	}
	
	public function add_rewrite_rules() {
		add_rewrite_tag( '%vendor%', '([^&]+)' );
		$page_id = EDD_FES()->fes_options->get_option( 'vendor-page' );
		add_rewrite_rule( 'vendor/([^/]*)', 'index.php?page_id=' . $page_id . '&vendor=$matches[1]', 'top' );
	}
	
	public function vendor_me( $query ) {
		global $wp_query;
		global $post;
		if ( is_admin() ) {
			return;
		}
		if ( !is_page() ) {
			return;
		}
		if ( !isset( $wp_query->query_vars[ 'page_id' ] ) || $wp_query->query_vars[ 'page_id' ] == '' ) {
			return;
		}
		$post = get_post( $wp_query->query_vars[ 'page_id' ] );
		if ( has_shortcode( $post->post_content, 'downloads' ) ) {
			if ( isset( $wp_query->query_vars[ 'vendor' ] ) ) {
				add_filter( 'edd_downloads_query', array(
					 $this,
					'set_shortcode' 
				) );
				$vendor_nicename = $wp_query->query_vars[ 'vendor' ];
				$vendor_id       = get_user_by( 'slug', $vendor_nicename );
				add_filter( 'the_title', array(
					 $this,
					'me_callback' 
				) );
			}
		}
	}
	
	public function set_shortcode( $query ) {
		global $wp_query;
		$vendor_nicename   = $wp_query->query_vars[ 'vendor' ];
		$vendor_id         = get_user_by( 'slug', $vendor_nicename );
		$query[ 'author' ] = $vendor_id->ID;
		return $query;
	}
	
	public function me_callback( $title ) {
		global $post;
		if ( $post->ID != EDD_FES()->fes_options->get_option( 'vendor-page' ) || !in_the_loop() ) {
			return $title;
		}
		global $wp_query;
		$vendor_nicename = $wp_query->query_vars[ 'vendor' ];
		$vendor          = get_user_by( 'slug', $vendor_nicename );
		return 'The Shop of Vendor ' . $vendor->display_name;
	}
}