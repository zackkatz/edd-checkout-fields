<?php
class CFM_Honeypot_Field extends CFM_Field {

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
		),
		'template'   => 'honeypot',
		'title'       => 'Honeypot',
	);

	/** @var array Characteristics are things that can change from field to field of the same field type. Like the placeholder between two email fields. Stored in db. */
	public $characteristics = array(
		'name'        => 'honeypot',
		'template'   => 'honeypot',
		'public'      => false,
		'required'    => false,
		'label'       => '',
		'description' => '',
		'meta_type'   => 'payment', // 'payment' or 'user' here if is_meta()
		'public'          => "public", // denotes whether a field shows in the admin only
		'show_in_exports' => "noexport", // denotes whether a field is in the CSV exports
	);


	public function set_title() {
		$title = _x( 'Honeypot', 'CFM Field title translation', 'edd_cfm' );
		$title = apply_filters( 'cfm_' . $this->name() . '_field_title', $title );
		$this->supports['title'] = $title;		
	}

	public function extending_constructor( ) {
		add_filter( 'cfm_templates_to_exclude_render_checkout_form_admin', array( $this, 'exclude_field' ), 10, 1  );
		add_filter( 'cfm_templates_to_exclude_sanitize_checkout_form_admin', array( $this, 'exclude_field' ), 10, 1  );
		add_filter( 'cfm_templates_to_exclude_validate_checkout_form_admin', array( $this, 'exclude_field' ), 10, 1  );
		add_filter( 'cfm_templates_to_exclude_save_checkout_form_admin', array( $this, 'exclude_field' ), 10, 1  );
		
		add_filter( 'cfm_templates_to_exclude_sanitize_checkout_form_frontend', array( $this, 'exclude_field' ), 10, 1  );
		add_filter( 'cfm_templates_to_exclude_validate_checkout_form_frontend', array( $this, 'exclude_field' ), 10, 1  );
		add_filter( 'cfm_templates_to_exclude_save_checkout_form_frontend', array( $this, 'exclude_field' ), 10, 1  );		
	}

	public function exclude_field( $fields ) {
		array_push( $fields, 'honeypot' );
		return $fields;
	}


	/** don't renter in admin */
	public function render_field_admin( $user_id = -2, $profile = -2 ) {
		return '';
	}

	/** Returns the Honeypot to render a field in frontend */
	public function render_field_frontend( $user_id = -2, $profile = -2 ) {
		if ( $user_id === -2 ) {
			$user_id = get_current_user_id();
		}

		$output        = '';
		$output     .= sprintf( '<p class="cfm-el %1s %2s %3s">', esc_attr( $this->template() ), esc_attr( $this->name() ), esc_attr( $this->css() ) );
		ob_start(); ?>
		<input type="hidden" name="honeypot" value=""/>
		<?php
		$output .= ob_get_clean();
		$output .= '</p>';
		return $output;
	}

	/** Returns the Honeypot to render a field for the formbuilder */
	public function render_formbuilder_field( $index = -2, $insert = false ) {
		$removable = $this->can_remove_from_formbuilder();
		ob_start(); ?>
		<li class="custom-field honeypot">
			<?php $this->legend( $this->title(), $this->get_label(), $removable ); ?>
			<?php CFM_Formbuilder_Templates::public_radio( $index, $this->characteristics, "public" ); ?>
			<?php CFM_Formbuilder_Templates::export_radio( $index, $this->characteristics, "noexport" ); ?>
			<?php CFM_Formbuilder_Templates::meta_type_radio( $index, $this->characteristics, "payment" ); ?>
			<?php CFM_Formbuilder_Templates::hidden_field( "[$index][template]", $this->template() ); ?>
			<?php CFM_Formbuilder_Templates::field_div( $index, $this->name(), $this->characteristics, $insert ); ?>
			<?php _e( 'There are no settings for this field', 'edd_cfm'); ?>
			</div>
		</li>
		<?php
		return ob_get_clean();
	}

	/** Validates field */
	public function validate( $values = array(), $payment_id = -2, $user_id = -2 ) {
		$name = $this->name();
		if ( !empty( $values[ $name ] ) ) {
			edd_set_error( 'invalid_' . $this->id, sprintf( __( 'Nice try bot, but you failed the %s.', 'edd_cfm' ), $this->get_label() ) );
		}
	}

	public function sanitize( $values = array(), $payment_id = -2, $user_id = -2 ) {
		$name = $this->name();
		if ( !empty( $values[ $name ] ) ) {
			$values[ $name ] = '1';
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
