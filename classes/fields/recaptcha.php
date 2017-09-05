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
		'template'        => 'recaptcha',
		'title'           => 'reCAPTCHA',
		'meta_type'       => 'payment', // 'payment' or 'user' here if is_meta()
		'public'          => "public", // denotes whether a field shows in the admin only
		'show_on_receipt' => false,
	);

	/** @var array Characteristics are things that can change from field to field of the same field type. Like the placeholder between two email fields. Stored in db. */
	public $characteristics = array(
		'name'        => 'recaptcha',
		'template'    => 'recaptcha',
		'public'      => false,
		'required'    => true,
		'label'       => '',
		'html'        => '',
		'show_in_exports' => "noexport", // denotes whether a field is in the CSV exports
	);

	public function set_title() {
		$this->supports['title'] = apply_filters( 'cfm_' . $this->name() . '_field_title', _x( 'reCAPTCHA', 'CFM Field title translation', 'edd_cfm' ) );
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
		$public_key  = edd_get_option( 'cfm-recaptcha-public-key', '' );
		$private_key = edd_get_option( 'cfm-recaptcha-private-key', '' );

		if ( $public_key == '' || $private_key == '' ) {
			return '';
		}

		$output = sprintf( '<div class="cfm-el %1s %2s %3s">', esc_attr( $this->template() ), esc_attr( $this->name() ), esc_attr( $this->css() ) );
			$output .= $this->label( $profile );
			$output .= '<div id="cfm-recaptcha"></div>';
			$output .= '<input type="hidden" name="cfm_ip" value="'. esc_attr( edd_get_ip() ) . '"/>';
		$output .= '</div>';

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

		if ( ! empty( $_POST[ 'g-recaptcha-response' ] ) ) {

			try {

				$private_key     = trim( edd_get_option( 'cfm-recaptcha-private-key', '' ) );
				$recap_challenge = trim( $values['g-recaptcha-response'] );
				$remote_ip       = trim( $values['cfm_ip'] );
				$url             = 'https://www.google.com/recaptcha/api/siteverify';

				$data     = array( 'secret' => $private_key, 'response' => $recap_challenge, 'remoteip' => $remote_ip );

				$args     = array(
					'headers' => array(
						'Content-type' => 'application/x-www-form-urlencoded',
					),
					'body' => $data,
				);

				$response = wp_safe_remote_post( $url, $data );
				if ( is_wp_error( $response ) ) {

					edd_set_error( 'invalid_recaptcha_bad_' . $this->id, __( 'Please retry the reCAPTCHA challenge', 'edd_cfm' ) );

				} else {

					$verify = json_decode( wp_remote_retrieve_body( $response ) );

					if ( $verify->success === 'false' ) {
						edd_set_error( 'invalid_recaptcha_bad_' . $this->id, __( 'Please retry the reCAPTCHA challenge', 'edd_cfm' ) );
					}

				}

			} catch ( Exception $e ) {
				edd_set_error( 'invalid_recaptcha_bad_' . $this->id, __( 'There was an error validating the reCaptcha', 'edd_cfm' ) );
			}

		} else if ( $this->required() ) {
			// if the field is required but isn't present
			edd_set_error( 'invalid_recaptcha_incomplete_' . $this->id, __( 'Please complete the reCAPTCHA challenge', 'edd_cfm' ) );
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
