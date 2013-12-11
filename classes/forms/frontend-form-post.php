<?php
if ( !defined( 'ABSPATH' ) ) {
	exit;
}

class CFM_Frontend_Form_Post extends CFM_Render_Form {
	private static $_instance;
	
	function __construct() {
		add_shortcode( 'fes-form', array(
			 $this,
			'add_post_shortcode' 
		) );
		// ajax requests
		add_action( 'wp_ajax_fes_submit_post', array(
			 $this,
			'submit_post' 
		) );
		add_action( 'wp_ajax_nopriv_fes_submit_post', array(
			 $this,
			'submit_post' 
		) );
	}
	
	public static function init() {
		if ( !self::$_instance ) {
			self::$_instance = new self;
		}
		return self::$_instance;
	}
	
	public function add_post_shortcode() {
		ob_start();
		$this->render_form( EDD_CFM()->fes_options->get_option( 'fes-submission-form' ) );
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}
	
	public function submit_post() {
		require_once EDD_PLUGIN_DIR . 'includes/admin/upload-functions.php';
		if ( function_exists( 'edd_set_upload_dir' ) ) {
			add_filter( 'upload_dir', 'edd_set_upload_dir' );
		}
		check_ajax_referer( 'fes-form_add' );
		@header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );
		$form_id       = isset( $_POST[ 'form_id' ] ) ? intval( $_POST[ 'form_id' ] ) : 0;
		$form_vars     = $this->get_input_fields( $form_id );
		$form_settings = get_post_meta( $form_id, 'fes-form_settings', true );
		list( $post_vars, $taxonomy_vars, $meta_vars ) = $form_vars;
		$post_author = get_current_user_id();
		$status  = 'publish';
		$postarr = array(
			'post_type' => 'download',
			'post_status' => $status,
			'post_author' => $post_author,
			'post_title' => isset( $_POST[ 'post_title' ] ) ? trim( $_POST[ 'post_title' ] ) : '',
			'post_content' => isset( $_POST[ 'post_content' ] ) ? trim( $_POST[ 'post_content' ] ) : '',
			'post_excerpt' => isset( $_POST[ 'post_excerpt' ] ) ? trim( $_POST[ 'post_excerpt' ] ) : '' 
		);
		$error = apply_filters( 'fes_add_post_validate', '' );
		if ( !empty( $error ) ) {
			$this->send_error( $error );
		}

		$postarr = apply_filters( 'fes_add_post_args', $postarr, $form_id, $form_settings, $form_vars );
		$post_id = wp_insert_post( $postarr );
		if ( $post_id ) {
			self::update_post_meta( $meta_vars, $post_id );
			// set the post form_id for later usage
			update_post_meta( $post_id, self::$config_id, $form_id );
			// find our if any images in post content and associate them
			if ( !empty( $postarr[ 'post_content' ] ) ) {
				$dom = new DOMDocument();
				$dom->loadHTML( $postarr[ 'post_content' ] );
				$images = $dom->getElementsByTagName( 'img' );
				if ( $images->length ) {
					foreach ( $images as $img ) {
						$url           = $img->getAttribute( 'src' );
						$url           = str_replace( array(
							 '"',
							"'",
							"\\" 
						), '', $url );
						$attachment_id = fes_get_attachment_id_from_url( $url );
						if ( $attachment_id ) {
							fes_associate_attachment( $attachment_id, $post_id );
						}
					}
				}
			}

			// send the response (these are options in 2.1, so let's set this array up for that)
			if ( function_exists( 'edd_set_upload_dir' ) ) {
				remove_filter( 'upload_dir', 'edd_set_upload_dir' );
			}
			$response = array(
				 'success' => true,
				'redirect_to' => get_permalink( EDD_CFM()->fes_options->get_option( 'vendor-dashboard-page' ) ),
				'message' => __( 'Success!', 'edd_fes' ),
				'is_post' => true 
			);
			$response = apply_filters( 'fes_add_post_redirect', $response, $post_id, $form_id, $form_settings );
			echo json_encode( $response );
			exit;
		}
		else {
			$this->send_error( __( 'Something went wrong. Error 1049.', 'edd_fes' ) );	
		}
	}
	
	public static function update_post_meta( $meta_vars, $post_id ) {
		// prepare the meta vars
		list( $meta_key_value, $multi_repeated, $files ) = self::prepare_meta_fields( $meta_vars );
		// set featured image if there's any
		if ( isset( $_POST[ 'fes_files' ][ 'featured_image' ] ) ) {
			$attachment_id = $_POST[ 'fes_files' ][ 'featured_image' ][ 0 ];
			fes_associate_attachment( $attachment_id, $post_id );
			set_post_thumbnail( $post_id, $attachment_id );
		}
		// save all custom fields
		foreach ( $meta_key_value as $meta_key => $meta_value ) {
			update_post_meta( $post_id, $meta_key, $meta_value );
		}
		// save any multicolumn repeatable fields
		foreach ( $multi_repeated as $repeat_key => $repeat_value ) {
			// first, delete any previous repeatable fields
			delete_post_meta( $post_id, $repeat_key );
			// now add them
			foreach ( $repeat_value as $repeat_field ) {
				add_post_meta( $post_id, $repeat_key, $repeat_field );
			}
		}
		// save any files attached
		foreach ( $files as $file_input ) {
			// delete any previous value
			delete_post_meta( $post_id, $file_input[ 'name' ] );
			foreach ( $file_input[ 'value' ] as $attachment_id ) {
				fes_associate_attachment( $attachment_id, $post_id );
				add_post_meta( $post_id, $file_input[ 'name' ], $attachment_id );
			}
		}
	}
}
new CFM_Frontend_Form_Post;