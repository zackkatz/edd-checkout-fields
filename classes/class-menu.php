<?php
if ( !defined( 'ABSPATH' ) ) {
	exit;
}

// This is based off of work by bbPress and also EDD itself.
class CFM_Menu {

	public $minimum_capability = 'manage_options';

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'admin_menus'), 9 );
		add_action( 'admin_head', array( $this, 'admin_head' ) );
		add_action( 'admin_init', array( $this, 'welcome'    ) );
		add_action( 'edd_sale_notification', array( $this, 'email_body'    ),10,2 );
		add_action( 'edd_purchase_receipt', array( $this, 'email_body'    ),10,2 );
		add_filter( 'edd_export_csv_cols_payments', array($this, 'columns') );
		add_filter( 'edd_export_get_data', array($this, 'data') );
	}
	public function columns( $cols ){
		$submission = array('text','textarea','date','url','email');
		$submission_meta = array();

		$form_id = get_option( 'edd_cfm_id' );
		if ( $form_id ){
			list($post_fields, $taxonomy_fields, $custom_fields) = EDD_CFM()->render_form->get_input_fields( $form_id );
			foreach($custom_fields as $field){
				if ( in_array( $field['input_type'], $submission ) ){
					$cols[] = $field['name'];
				}
			}
		}
		return $cols;
	}

	public function data( $data ){
		$submission = array('text','textarea','date','url','email');
		$submission_meta = array();
		$post_id = $data['id'];
		$form_id = get_option( 'edd_cfm_id' );
		if ( $form_id ){
			list($post_fields, $taxonomy_fields, $custom_fields) = EDD_CFM()->render_form->get_input_fields( $form_id );
			foreach($custom_fields as $field){
				if ( in_array( $field['input_type'], $submission ) ){
					$name = "$field['name']";
					$data["$n"] = EDD_CFM()->menu->get_post_meta($meta, $post_id);
				}
			}
		}
		return $data;	
	}

	public function email_body( $message, $post_id ){
		$submission = array('text','textarea','date','url','email');
		$submission_meta = array();

		$form_id = get_option( 'edd_cfm_id' );
		if ( $form_id ){
			list($post_fields, $taxonomy_fields, $custom_fields) = EDD_CFM()->render_form->get_input_fields( $form_id );
			foreach($custom_fields as $field){
				if ( in_array( $field['input_type'], $submission ) ){
					array_push($submission_meta, $field['name']);
				}
			}
		}

		foreach($submission_meta as $meta ){
			$message = str_replace('{'.$meta.'}', EDD_CFM()->menu->get_post_meta($meta, $post_id), $message );
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

	public function admin_menus() {
		add_dashboard_page( 'About CFM', 'About CFM', $this->minimum_capability,'cfm-about', array( $this, 'about_screen' ));
		add_submenu_page( 'edit.php?post_type=download', 'Checkout Fields', 'Checkout Fields', 'manage_options', 'post.php?post=' . get_option( 'edd_cfm_id') . '&action=edit');
	}

	/**
	 * Hide Individual Dashboard Pages
	 *
	 * @access public
	 * @since 1.4
	 * @return void
	 */
	public function admin_head() {
		global $submenu;
		remove_submenu_page( 'index.php', 'cfm-about' );
		?>
		<style type="text/css" media="screen">
		/*<![CDATA[*/
		.about-wrap h1 {
			margin-right: 0px;
		}
		/*]]>*/
		</style>
		<?php
	}

	/**
	 * Render About Screen
	 *
	 * @access public
	 * @since 1.4
	 * @return void
	 */
	public function about_screen() {
		list( $display_version ) = explode( '-', cfm_plugin_version );
		?>
		<div class="wrap about-wrap">
			<h1><?php printf( __( 'Welcome to EDD Checkout Fields Manager %s!', 'edd_cfm' ), $display_version ); ?></h1>
			<div class="about-text"><?php _e( 'Thank you for updating to the latest version!', 'edd_cfm' ); ?></div>
			<div class="changelog">
				<h3><?php _e( 'What\'s New:', 'edd_cfm' );?></h3>

				<div class="feature-section">
					<h4><?php _e( '15 Field Types', 'edd_cfm' );?></h4>
					<p><?php _e( 'With 15 field types, you can make whatever you want on the checkout page come to reality in a couple clicks.', 'edd_cfm' );?></p>

					<h4><?php _e( 'File Upload', 'edd_cfm' );?></h4>
					<p><?php _e( 'With the file upload field, having users upload files during checkout is quick and simple', 'edd_cfm' );?></p>
					
					<h4><?php _e( 'Drag and Drop Re-Ordering of fields', 'edd_cfm' );?></h4>
					<p><?php _e( 'To change the order of the fields on the checkout page, simply drag and drop! ', 'edd_cfm' );?></p>
				</div>
			</div>
		</div>
		<?php
	}
		
	public function welcome() {
		global $edd_options;

		// Bail if no activation redirect
		if ( ! get_transient( '_edd_cfm_activation_redirect' ) )
			return;

		// Delete the redirect transient
		delete_transient( '_edd_cfm_activation_redirect' );

		// Bail if activating from network, or bulk
		if ( is_network_admin() || isset( $_GET['activate-multi'] ) )
			return;

		wp_safe_redirect( admin_url( 'index.php?page=cfm-about' ) ); exit;
	}
}