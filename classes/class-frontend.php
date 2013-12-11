<?php
if ( !defined( 'ABSPATH' ) ) {
	exit;
}

class FES_Frontend {
	function __construct() {
		add_shortcode( 'fes_vendor_dashboard', array(
			 $this,
			'display_fes_dashboard' 
		) );
		add_action( 'template_redirect', array(
			 $this,
			'check_access' 
		) );
		add_filter( 'edd_download_supports', array(
			 $this,
			'enable_reviews' 
		) );
		add_action( 'template_redirect', array(
			 $this,
			'shortcode_redirects' 
		) );
	}
	
	public function check_access() {
		global $post;
		if ( is_page( EDD_FES()->fes_options->get_option( 'vendor-dashboard-page' ) ) && ( has_shortcode( $post->post_content, 'fes_vendor_dashboard' ) ) ) {
			$task = !empty( $_GET[ 'task' ] ) ? $_GET[ 'task' ] : '';
			if ( $task == 'logout' ) {
				$this->edd_fes_secure_logout();
			}
			if ( is_user_logged_in() && !EDD_FES()->vendor_permissions->vendor_is_vendor() && !isset( $_GET[ 'view' ] ) ) {
				EDD_FES()->vendor_permissions->vendor_not_a_vendor_redirect();
			}
		}
	}
	
	public function display_fes_dashboard( $atts ) {
		$view = !empty( $_REQUEST[ 'view' ] ) ? $_REQUEST[ 'view' ] : 'login-register';
		if ( $view && !EDD_FES()->vendor_permissions->vendor_is_vendor() ) {
			ob_start();
			switch ( $view ) {
				case 'login':
					EDD_FES()->templates->fes_get_template_part( 'frontend', 'login' );
					break;
				case 'logout':
					EDD_FES()->templates->fes_get_template_part( 'frontend', 'logout' );
					break;
				case 'register':
					EDD_FES()->templates->fes_get_template_part( 'frontend', 'register' );
					break;
				case 'application':
					EDD_FES()->templates->fes_get_template_part( 'frontend', 'application' );
					break;
				case 'pending':
					EDD_FES()->templates->fes_get_template_part( 'frontend', 'pending' );
					break;
				case 'denied':
					EDD_FES()->templates->fes_get_template_part( 'frontend', 'denied' );
					break;
				case 'login-register':
					EDD_FES()->templates->fes_get_template_part( 'frontend', 'login-register' );
					break;
				default:
					EDD_FES()->templates->fes_get_template_part( 'frontend', 'login-register' );
					break;
			}
			return ob_get_clean();
		} else {
			extract( shortcode_atts( array(
				 'user_id' => get_current_user_id() 
			), $atts ) );
			$task = !empty( $_GET[ 'task' ] ) ? $_GET[ 'task' ] : '';
			ob_start();
			/* Load Header (FES core doesn't use it, but basically for if theme authors 
			want to put something above the nav bar. */
			EDD_FES()->frontend->get_header();
			/* Load Menu */
			EDD_FES()->frontend->get_menu();
			/* Get page options */
			switch ( $task ) {
				case 'dashboard':
					EDD_FES()->templates->fes_get_template_part( 'frontend', 'dashboard' );
					break;
				case 'new':
					require_once fes_plugin_dir . 'classes/forms/post-form-functions.php';
					EDD_FES()->templates->fes_get_template_part( 'frontend', 'new-product' );
					break;
				case 'earnings':
					EDD_FES()->templates->fes_get_template_part( 'frontend', 'earnings' );
					break;
				case 'settings':
					EDD_FES()->templates->fes_get_template_part( 'frontend', 'settings' );
					break;
				case 'products':
					EDD_FES()->templates->fes_get_template_part( 'frontend', 'my-products' );
					break;
				case 'profile':
					EDD_FES()->templates->fes_get_template_part( 'frontend', 'profile' );
					break;
				case 'reports':
					EDD_FES()->templates->fes_get_template_part( 'frontend', 'reports' );
					break;
				case '':
				default:
					EDD_FES()->templates->fes_get_template_part( 'frontend', 'dashboard' );
					break;
			}
			/* Load Footer */
			EDD_FES()->frontend->get_footer();
			return ob_get_clean();
		}
	}
	
	public function get_menu() {
		EDD_FES()->templates->fes_get_template_part( 'frontend', 'menu' );
	}
	
	public function get_header() {
		EDD_FES()->templates->fes_get_template_part( 'frontend', 'header' );
	}
	
	public function get_footer() {
		EDD_FES()->templates->fes_get_template_part( 'frontend', 'footer' );
	}
	
	public function array_values_recursive( $arr ) {
		$arr = array_values( $arr );
		foreach ( $arr as $key => $val ) {
			if ( array_values( (array) $val ) === $val ) {
				$arr[ $key ] = $this->array_values_recursive( $val );
			}
		}
		return $arr;
	}
	
	public function enable_reviews( $supports ) {
		return array_merge( $supports, array(
			 'reviews' 
		) );
	}
	
	public function edd_fes_secure_logout() {
		if ( is_user_logged_in() ) {
			wp_logout();
			$base_url = get_permalink( EDD_FES()->fes_options->get_option( 'vendor-dashboard-page' ) );
			$base_url = add_query_arg( array(
				 'view' => 'login',
				'task' => false 
			), $base_url );
			wp_redirect( $base_url );
			exit;
		}
	}
	
	public function shortcode_redirects(){
		global $post;
		if ( 'page' != get_post_type( $post->ID ) ){
			return;
		}
		if ( has_shortcode( $post->post_content, 'edd_fes_login_form' ) && is_user_logged_in() ) {
			$url = get_permalink( EDD_FES()->fes_options->get_option( 'vendor-dashboard-page' ) );
			wp_redirect( $url );
			exit;
		}
		else if( has_shortcode( $post->post_content, 'edd_fes_register_form' ) && !( !EDD_FES()->vendor_permissions->vendor_is_vendor() && !is_user_logged_in() ) ){
			EDD_FES()->vendor_permissions->vendor_not_a_vendor_redirect();
		}
		else if ( has_shortcode( $post->post_content, 'edd_fes_combo_form' ) && EDD_FES()->vendor_permissions->vendor_is_vendor() && is_user_logged_in() ){
			$url = get_permalink( EDD_FES()->fes_options->get_option( 'vendor-dashboard-page' ) );
			wp_redirect( $url );
			exit;
		}
		else if ( has_shortcode( $post->post_content, 'edd_fes_combo_form' ) && ( current_user_can( 'pending_vendor' ) || is_user_logged_in() ) ){
			EDD_FES()->vendor_permissions->vendor_not_a_vendor_redirect();
		}
	}	

}