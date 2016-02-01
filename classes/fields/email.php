<?php
class CFM_Email_Field extends CFM_Field {

	/** @var bool For 3rd parameter of get_post/user_meta */
	public $single = true;

	/** @var array Supports are things that are the same for all fields of a field type. Like whether or not a field type supports jQuery Phoenix. Stored in obj, not db. */
	public $supports = array(
		'multiple'    => true,
		'is_meta'     => true,  // in object as public (bool) $meta;
		'forms'       => array(
			'registration'     => true,
			'submission'       => true,
			'vendor-contact'   => true,
			'profile'          => true,
			'login'            => false,
		),
		'position'    => 'custom',
		'permissions' => array(
			'can_remove_from_formbuilder' => true,
			'can_change_meta_key'         => true,
			'can_add_to_formbuilder'      => true,
		),
		'template'   => 'email',
		'title'       => 'Email',
		'phoenix'   => true,
	);

	/** @var array Characteristics are things that can change from field to field of the same field type. Like the placeholder between two email fields. Stored in db. */
	public $characteristics = array(
		'name'        => '',
		'template'    => 'email',
		'public'      => true,
		'required'    => false,
		'label'       => '',
		'css'         => '',
		'default'     => '',
		'size'        => '',
		'help'        => '',
		'placeholder' => '',
	);


	public function set_title() {
		$title = _x( 'Email', 'CFM Field title translation', 'edd_cfm' );
		$title = apply_filters( 'cfm_' . $this->name() . '_field_title', $title );
		$this->supports['title'] = $title;		
	}

	/** Returns the HTML to render a field in admin */
	public function render_field_admin( $user_id = -2, $readonly = -2 ) {
		if ( $user_id === -2 ) {
			$user_id = get_current_user_id();
		}

		if ( $readonly === -2 ) {
			$readonly = $this->readonly;
		}

		$user_id   = apply_filters( 'cfm_render_email_field_user_id_admin', $user_id, $this->id );
		$readonly  = apply_filters( 'cfm_render_email_field_readonly_admin', $readonly, $user_id, $this->id );
		$value     = $this->get_field_value_admin( $this->save_id, $user_id, $readonly );

		$output        = '';
		$output     .= sprintf( '<fieldset class="cfm-el %1s %2s %3s">', $this->template(), $this->name(), $this->css() );
		$output    .= $this->label( $readonly );
		ob_start(); ?>
		<div class="cfm-fields">
			<input id="<?php echo $this->name(); ?>" type="email" class="email" data-required="false" data-type="text" name="<?php echo esc_attr( $this->name() ); ?>" placeholder="<?php echo esc_attr( $this->placeholder() ); ?>" value="<?php echo esc_attr( $value ) ?>" size="<?php echo esc_attr( $this->size() ) ?>" />
		</div>
		<?php
		$output .= ob_get_clean();
		$output .= '</fieldset>';
		return $output;
	}

	/** Returns the HTML to render a field in frontend */
	public function render_field_frontend( $user_id = -2, $readonly = -2 ) {
		if ( $user_id === -2 ) {
			$user_id = get_current_user_id();
		}

		if ( $readonly === -2 ) {
			$readonly = $this->readonly;
		}

		$user_id   = apply_filters( 'cfm_render_email_field_user_id_frontend', $user_id, $this->id );
		$readonly  = apply_filters( 'cfm_render_email_field_readonly_frontend', $readonly, $user_id, $this->id );
		$value     = $this->get_field_value_frontend( $this->save_id, $user_id, $readonly );
		$required  = $this->required( $readonly );
		
		$output        = '';
		$output     .= sprintf( '<fieldset class="cfm-el %1s %2s %3s">', $this->template(), $this->name(), $this->css() );
		$output    .= $this->label( $readonly );
		ob_start(); ?>
		<?php do_action( 'edd_purchase_form_before_email' ); ?>
		<div class="cfm-fields">
			<input id="<?php echo $this->name(); ?>" type="email" class="email" data-required="<?php echo $required; ?>" data-type="text"<?php $this->required_html5( $readonly ); ?> name="<?php echo esc_attr( $this->name() ); ?>" placeholder="<?php echo esc_attr( $this->placeholder() ); ?>" value="<?php echo esc_attr( $value ) ?>" size="<?php echo esc_attr( $this->size() ) ?>" />
		</div>
		<?php do_action( 'edd_purchase_form_after_email' ); ?>
		<?php
		$output .= ob_get_clean();
		$output .= '</fieldset>';
		return $output;
	}
	
	/** Returns the HTML to render a field for the formbuilder */
	public function render_formbuilder_field( $index = -2, $insert = false ) {
		$removable = $this->can_remove_from_formbuilder();
		ob_start(); ?>
		<li class="custom-field email_address">
			<?php $this->legend( $this->title(), $this->get_label(), $removable ); ?>
			<?php CFM_Formbuilder_Templates::hidden_field( "[$index][template]", $this->template() ); ?>

			<?php CFM_Formbuilder_Templates::field_div( $index, $this->name(), $this->characteristics, $insert ); ?>
				<?php CFM_Formbuilder_Templates::public_radio( $index, $this->characteristics, $this->form_name ); ?>
				<?php CFM_Formbuilder_Templates::standard( $index, $this ); ?>
				<?php CFM_Formbuilder_Templates::common_text( $index, $this->characteristics ); ?>
			</div>
		</li>
		<?php
		return ob_get_clean();
	}

	public function validate( $values = array(), $save_id = -2, $user_id = -2 ) {
		$name = $this->name();
		$return_value = false;
		if ( !empty( $values[ $name ] ) ) {
			// if the value is set
			if ( filter_var( $values[ $name ], FILTER_VALIDATE_EMAIL ) === false ) {
				// if that's not a email address
				$return_value = __( 'Please enter a valid email address', 'edd_cfm' );
			}
		} else {
			// if the url is required but isn't present
			if ( $this->required() ) {
				$return_value = __( 'Please fill out this field.', 'edd_cfm' );
			}
		}
		return apply_filters( 'cfm_validate_' . $this->template() . '_field', $return_value, $values, $name, $save_id, $user_id );
	}

	public function sanitize( $values = array(), $save_id = -2, $user_id = -2 ) {
		$name = $this->name();
		if ( !empty( $values[ $name ] ) ) {
			$values[ $name ] = trim( $values[ $name ] );
			$values[ $name ] = filter_var( $values[ $name ], FILTER_SANITIZE_EMAIL );
			$values[ $name ] = sanitize_email( $values[ $name ] );
		}
		return apply_filters( 'cfm_sanitize_' . $this->template() . '_field', $values, $name, $save_id, $user_id );
	}
}
