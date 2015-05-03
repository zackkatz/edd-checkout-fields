<?php
if ( !defined( 'ABSPATH' ) ) {
	exit;
}
class CFM_Emails {

	public function __construct() {
		add_action( 'edd_sale_notification', array( $this, 'email_body' ), 10,2 );
		add_action( 'edd_purchase_receipt', array( $this, 'email_body' ), 10,2 );
	}

	public function email_body( $message, $post_id ){
		$form_id = get_option( 'edd_cfm_id' );
		if ( $form_id ){
			list( $post_fields, $taxonomy_fields, $custom_fields ) = EDD_CFM()->render_form->get_input_fields( $form_id );
			foreach($custom_fields as $meta ){
				$type = 'normal';
				if ( $meta['input_type'] == 'file_upload' ){
					$type = 'file';
				} else if ( $meta['input_type'] == 'image' ){
					$type = 'image';
				} else if ( $meta['input_type'] == 'repeat' ){
					$type = 'repeat';
				}
				$message = str_replace('{'.$meta['name'].'}', EDD_CFM()->emails->get_post_meta($meta['name'], $post_id, $type ), $message );
			}
		}
		return $message;
	}

	public function get_post_meta( $name, $post_id, $type = 'normal' ){
        if ( empty( $name ) || empty( $post_id ) ) {
            return;
        }

        $post = get_post( $post_id );

        if ( $type == 'image' || $type == 'file' ) {
            $images = get_post_meta( $post->ID, $name );
            if ( $images ) {
                $html = '';
                if ( isset( $images[0] ) && is_array( $images[0] ) ){
                    $images = $images[0];
                }
                foreach ($images as $attachment_id ) {
                    if ( $type == 'image' ) {
                        $thumb = wp_get_attachment_image( $attachment_id );
                    } else {
                        $thumb = get_post_field( 'post_title', $attachment_id );
                    }

                    $full_size = wp_get_attachment_url( $attachment_id );
                    $html .= sprintf( '<a href="%s">%s</a> ', $full_size, $thumb );
                }
                return $html;
            }
        } elseif ( $type == 'repeat' ) {
            return implode( '; ', get_post_meta( $post->ID, $name ) );
        } else {
            return implode( ', ', get_post_meta( $post->ID, $name ) );
        }
    }
}