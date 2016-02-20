<?php
class CFM_Toc_Field extends CFM_Field {

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
			'can_change_meta_key'         => true,
			'can_add_to_formbuilder'      => true,
		),
		'template'   => 'toc',
		'title'       => 'Terms & Cond.',
	);

	/** @var array Characteristics are things that can change from field to field of the same field type. Like the placeholder between two email fields. Stored in db. */
	public $characteristics = array(
		'name'        => 'cfm_accept_toc',
		'template'   => 'toc',
		'required'    => true,
		'label'       => '',
		'css'         => '',
		'description' => '',
		'meta_type'   => 'payment', // 'payment' or 'user' here if is_meta()
		'public'          => "public", // denotes whether a field shows in the admin only
		'show_in_exports' => "noexport", // denotes whether a field is in the CSV exports
	);

	public function set_title() {
		$title = _x( 'Terms & Cond.', 'CFM Field title translation', 'edd_cfm' );
		$title = apply_filters( 'cfm_' . $this->name() . '_field_title', $title );
		$this->supports['title'] = $title;
	}

	public function extending_constructor( ) {
		add_filter( 'cfm_templates_to_exclude_render_checkout_form_admin', array( $this, 'exclude_field_admin' ), 10, 1  );
		add_filter( 'cfm_templates_to_exclude_sanitize_checkout_form_admin', array( $this, 'exclude_field_admin' ), 10, 1  );
		add_filter( 'cfm_templates_to_exclude_validate_checkout_form_admin', array( $this, 'exclude_field_admin' ), 10, 1  );
		add_filter( 'cfm_templates_to_exclude_save_checkout_form_admin', array( $this, 'exclude_field_admin' ), 10, 1  );
	}

	public function exclude_field_admin( $fields ) {
		array_push( $fields, 'toc' );
		return $fields;
	}	

	/** Returns the Toc to render a field in admin */
	public function render_field_admin( $user_id = -2, $profile = -2 ) {
		return '';
	}

	/** Returns the Toc to render a field in frontend */
	public function render_field_frontend( $user_id = -2, $profile = -2 ) {
		if ( $user_id === -2 ) {
			$user_id = get_current_user_id();
		}

		$value = $this->get_field_value_admin( $this->payment_id, $this->user_id );

		if ( $value ) {
			return '';
		}
		$output        = '';
		$output     .= sprintf( '<p class="cfm-el %1s %2s %3s">', esc_attr( $this->template() ), esc_attr( $this->name() ), esc_attr( $this->css() ) );
		//$output    .= $this->label( ! (bool) $profile );
		ob_start(); ?>
		<span data-required="yes" data-type="radio"></span>
		<?php echo $this->characteristics['description'] ?>
		<label>
			<input type="checkbox" name="cfm_accept_toc" required="required" /> <?php echo $this->get_label() ?>
		</label>
		<?php
		$output .= ob_get_clean();
		$output .= '</p>';
		return $output;
	}

	/** Returns the Toc to render a field for the formbuilder */
	public function render_formbuilder_field( $index = -2, $insert = false ) {
		$removable    = $this->can_remove_from_formbuilder();
		$title_name        = sprintf( '%s[%d][label]', 'cfm_input', $index );
		$description_name  = sprintf( '%s[%d][description]', 'cfm_input', $index );
		$css_name          = sprintf( '%s[%d][css]', 'cfm_input', $index );
		$title_value       = esc_attr( $this->get_label() );
		$description_value = esc_attr( $this->characteristics['description'] );
		$css_value         = esc_attr( $this->css() ); ?>
		<li class="toc">
			<?php $this->legend( $this->title(), $this->get_label(), $removable ); ?>
			<?php CFM_Formbuilder_Templates::public_radio( $index, $this->characteristics, $this->form_name, true ); ?>
			<?php CFM_Formbuilder_Templates::hidden_field( "[$index][template]", $this->template() ); ?>
			<?php CFM_Formbuilder_Templates::hidden_field( "[$index][name]", 'cfm_accept_toc' ); ?>

			<?php CFM_Formbuilder_Templates::field_div( $index, $this->name(), $this->characteristics, $insert ); ?>
				<div class="cfm-form-rows">
					<label><?php _e( 'Terms & Conditions', 'edd_cfm' ); ?></label>
					<textarea class="smallipopInput" title="<?php _e( 'Insert terms and condtions here.', 'edd_cfm' ); ?>" name="<?php echo $description_name; ?>" rows="3"><?php echo esc_html( $description_value ); ?></textarea>
				</div>
				<div class="cfm-form-rows">
					<label><?php _e( 'Agreement Checkbox Label', 'edd_cfm' ); ?></label>
					<input type="text" name="<?php echo $title_name; ?>" value="<?php echo esc_attr( $title_value ); ?>" />
				</div>
				<?php CFM_Formbuilder_Templates::css( $index, $this->characteristics ); ?>
			</div>
		</li>
		<?php
		return ob_get_clean();
	}

	public function validate( $values = array(), $payment_id = -2, $user_id = -2 ) {
		if ( $user_id === -2 ) {
			$user_id = get_current_user_id();
		}
		$name = $this->name();
		if ( !empty( $values[ $name ] ) ) {
			// if the value is set
			// no specific validation
		} else {
			$value = $this->get_field_value_frontend( $this->payment_id, $this->user_id );
			if ( !$value && empty( $values[ $name ] ) ) {
				edd_set_error( 'invalid_' . $this->id, sprintf( __( 'Please agree to %s.', 'edd_cfm' ), $this->get_label() ) );
			}
		}
	}

	public function sanitize( $values = array(), $payment_id = -2, $user_id = -2 ) {
		$name = $this->name();
		if ( ! empty( $values[ $name ] ) ) {
			$values[ $name ] = 'accepted';
		}
		return apply_filters( 'cfm_sanitize_' . $this->template() . '_field', $values, $name, $payment_id, $user_id );
	}
}
