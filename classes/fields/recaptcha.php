<?php
class CFM_Recaptcha_Field extends CFM_Field {

	/** @var bool For 3rd parameter of get_post/user_meta */
	public $single = true;

	/** @var string Version of field */
	public $version = '1.0.0';

	/** @var array Supports are things that are the same for all fields of a field type. Like whether or not a field type supports jQuery Phoenix. Stored in obj, not db. */
	public $supports = array(
		'multiple'    => false,
		'is_meta'     => true,  // in object as public (bool) $meta;
		'forms'       => array(
			'checkout'     => true,
		),
		'position'    => 'custom',
		'permissions' => array(
			'can_remove_from_formbuilder' => true,
			'can_change_meta_key'         => false,
			'can_add_to_formbuilder'      => true,
			'field_always_required'       => true,
		),
		'template'   => 'recaptcha',
		'title'       => 'reCAPTCHA',
		'meta_type'   => 'payment', // 'payment' or 'user' here if is_meta()
		'public'          => "public", // denotes whether a field shows in the admin only
		'show_in_exports' => "noexport", // denotes whether a field is in the CSV exports
	);

	/** @var array Characteristics are things that can change from field to field of the same field type. Like the placeholder between two email fields. Stored in db. */
	public $characteristics = array(
		'name'        => 'recaptcha',
		'template'   => 'recaptcha',
		'public'      => false,
		'required'    => true,
		'label'       => '',
		'html'     => '',
	);

	public function set_title() {
		$title = _x( 'reCAPTCHA', 'CFM Field title translation', 'edd_cfm' );
		$title = apply_filters( 'cfm_' . $this->name() . '_field_title', $title );
		$this->supports['title'] = $title;		
	}

	public function extending_constructor( ) {
		add_filter( 'cfm_templates_to_exclude_render_checkout_form_admin', array( $this, 'exclude_field_admin' ), 10, 1  );
		add_filter( 'cfm_templates_to_exclude_sanitize_checkout_form_admin', array( $this, 'exclude_field_admin' ), 10, 1  );
		add_filter( 'cfm_templates_to_exclude_validate_checkout_form_admin', array( $this, 'exclude_field_admin' ), 10, 1  );
		add_filter( 'cfm_templates_to_exclude_save_checkout_form_admin', array( $this, 'exclude_field_admin' ), 10, 1  );

		add_filter( 'cfm_templates_to_exclude_render_checkout_form_frontend', array( $this, 'exclude_field_frontend' ), 10, 1  );
		add_filter( 'cfm_templates_to_exclude_sanitize_checkout_form_frontend', array( $this, 'exclude_field_frontend' ), 10, 1  );
		add_filter( 'cfm_templates_to_exclude_validate_checkout_form_frontend', array( $this, 'exclude_field_frontend' ), 10, 1  );
		add_filter( 'cfm_templates_to_exclude_save_checkout_form_frontend', array( $this, 'exclude_field_frontend' ), 10, 1  );		
	}

	public function exclude_field_admin( $fields ) {
		array_push( $fields, 'recaptcha' );
		return $fields;
	}

	public function exclude_field_frontend( $fields ) {
		$public_key = edd_get_option( 'cfm-recaptcha-public-key', '' );
		$private_key = edd_get_option( 'cfm-recaptcha-private-key', '' );
		if ( $public_key == '' || $private_key == '' ) {
			array_push( $fields, 'recaptcha' );
		}
		return $fields;
	}

	/** Returns the Recaptcha to render a field in admin */
	public function render_field_admin( $user_id = -2, $profile = -2 ) {
		// we don't render reCAPTCHA in the backend
		return '';
	}

	/** Returns the Recaptcha to render a field in frontend */
	public function render_field_frontend( $user_id = -2, $profile = -2 ) {
		$public_key      = edd_get_option( 'cfm-recaptcha-public-key', '' );
		$private_key     = edd_get_option( 'cfm-recaptcha-private-key', '' );
		$theme           = apply_filters( 'cfm_render_recaptcha_field_frontend_theme', 'light' ); // The color theme of the widget. Either dark or light
		$type       	 = apply_filters( 'cfm_render_recaptcha_field_frontend_type', 'image' ); // The type of CAPTCHA to serve. Either audio or image
		$size       	 = apply_filters( 'cfm_render_recaptcha_field_frontend_size', 'normal' ); // The size of the widget. Either compact  or normal
		if ( $public_key == '' || $private_key == '' ) {
			return '';
		}

		$output        = '';
		$output     .= sprintf( '<p class="cfm-el %1s %2s %3s">', esc_attr( $this->template() ), esc_attr( $this->name() ), esc_attr( $this->css() ) );
		$output        .= $this->label( $profile );
		$prefix   = is_ssl() ? "https" : "http";
		$url      = $prefix . '://www.google.com/recaptcha/api.js';
		ob_start(); ?>

		<?php wp_enqueue_script( 'recaptcha', $url ); ?>
		<div class="g-recaptcha" data-sitekey="<?php echo $public_key; ?>" data-theme="<?php echo $theme; ?>" data-type="<?php echo $type; ?>" data-size="<?php echo $size; ?>"></div>
		<noscript>
			<div style="width: 302px; height: 422px;">
				<div style="width: 302px; height: 422px; position: relative;">
					<div style="width: 302px; height: 422px; position: absolute;">
					<iframe src="https://www.google.com/recaptcha/api/fallback?k=<?php echo $public_key; ?>"
						frameborder="0" scrolling="no"
						style="width: 302px; height:422px; border-style: none;">
					</iframe>
					 </div>
					 <div style="width: 300px; height: 60px; border-style: none;
						bottom: 12px; left: 25px; margin: 0px; padding: 0px; right: 25px;
						background: #f9f9f9; border: 1px solid #c1c1c1; border-radius: 3px;">
					<textarea id="g-recaptcha-response" name="g-recaptcha-response"
						class="g-recaptcha-response"
						style="width: 250px; height: 40px; border: 1px solid #c1c1c1;
						margin: 10px 25px; padding: 0px; resize: none;" >
					</textarea>
					 </div>
				</div>
			</div>
		</noscript>
		<?php
		$output .= ob_get_clean();
		$output .= '</p>';
		return $output;
	}

	/** Returns the Recaptcha to render a field for the formbuilder */
	public function render_formbuilder_field( $index = -2, $insert = false ) {
		$removable    = $this->can_remove_from_formbuilder();
?>
		<li class="recaptcha">
			<?php $this->legend( $this->title(), $this->get_label(), $removable ); ?>
			<?php CFM_Formbuilder_Templates::hidden_field( "[$index][template]", $this->template() ); ?>

			<?php CFM_Formbuilder_Templates::field_div( $index, $this->name(), $this->characteristics, $insert ); ?>
				<div class="cfm-form-rows">
					<label><b><?php _e( 'Important:', 'edd_cfm' ); ?></b></label>

					<div class="cfm-form-sub-fields">

						<div class="description" style="margin-top: 8px;">
							<?php _e( "In order for reCAPTCHA to work you must insert your site key and private key in the EDD settings panel. <a href='https://www.google.com/recaptcha/admin#list' target='_blank'>Create a key</a> first if you don't have any keys.", 'edd_cfm' ); ?>
						</div>
					</div>
				</div>
				<?php CFM_Formbuilder_Templates::public_radio( $index, $this->characteristics, "public" ); ?>
				<?php CFM_Formbuilder_Templates::export_radio( $index, $this->characteristics, "noexport" ); ?>
				<?php CFM_Formbuilder_Templates::meta_type_radio( $index, $this->characteristics, "payment" ); ?>
				<?php CFM_Formbuilder_Templates::standard( $index, $this ); ?>
				<?php CFM_Formbuilder_Templates::css( $index, $this->characteristics ); ?>
			</div>
		</li>
		<?php
		return ob_get_clean();
	}

	/** Validates field */
	public function validate( $values = array(), $payment_id = -2, $user_id = -2 ) {
		$name = $this->name();
		$return_value = false;

		if ( !empty( $values[ $name ] ) ) {
			$recap_challenge = isset( $values[ 'g-recaptcha-response' ] ) ? $values[ 'g-recaptcha-response' ] : '';
			$private_key     = edd_get_option( 'cfm-recaptcha-private-key', '' );
			try {
				$prefix   = is_ssl() ? "https" : "http";
				$url      = $prefix . '://www.google.com/recaptcha/api/siteverify';
				$data     = array( 'secret' => $private_key, 'response' => $recap_challenge, 'remoteip' => $_SERVER['REMOTE_ADDR'] );
				$options  = array( 'http' => array( 'header' => "Content-type: application/x-www-form-urlencoded\r\n", 'method' => 'POST', 'content' => http_build_query( $data ) ) );
				$context  = stream_context_create( $options );
				$result   = file_get_contents( $url, false, $context );
				if ( json_decode( $result )->success == false ) {
					edd_set_error( 'invalid_recaptcha_bad_' . $this->id, __( 'Please retry the reCAPTCHA challenge', 'edd_cfm' ) );
				}
			}
			catch ( Exception $e ) {
				edd_set_error( 'invalid_recaptcha_bad_' . $this->id, __( 'Please retry the reCAPTCHA challenge', 'edd_cfm' ) );
			}
		} else {
			// if the field is required but isn't present
			if ( $this->required() ) {
				edd_set_error( 'invalid_recaptcha_incomplete_' . $this->id, __( 'Please complete the reCAPTCHA challenge', 'edd_cfm' ) );
			}
		}
	}

	public function sanitize( $values = array(), $payment_id = -2, $user_id = -2 ) {
		$name = $this->name();
		if ( !empty( $values[ 'g-recaptcha-response' ] ) ) {
			$values[ $name ] = trim( $values[ 'g-recaptcha-response' ] );
			$values[ $name ] = sanitize_text_field( $values[ $name ] );
		}
		return apply_filters( 'cfm_sanitize_' . $this->template() . '_field', $values, $name, $payment_id, $user_id );
	}

	public function get_field_value_admin( $payment_id = -2, $user_id = -2 ) {
		return ''; // don't get field value
	}

	public function get_field_value_frontend( $payment_id = -2, $user_id = -2 ) {
		return ''; // don't get field value
	}

	public function save_field_admin( $payment_id = -2, $user_id = -2, $value = array(), $current_user_id = -2 ) {
		// don't save field value
	}

	public function save_field_frontend( $payment_id = -2, $user_id = -2, $value = array(), $current_user_id = -2 ) {
		// don't save field value
	}
}
