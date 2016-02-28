<?php
class CFM_Hidden_Field extends CFM_Field {

	/** @var bool For 3rd parameter of get_post/user_meta */
	public $single = true;

	/** @var string Version of field */
	public $version = '1.0.0';

	/** @var array Supports are things that are the same for all fields of a field type. Like whether or not a field type supports jQuery Phoenix. Stored in obj, not db. */
	public $supports = array(
		'multiple'    => true,
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
		'template'   => 'hidden',
		'title'       => 'Hidden',
	);

	/** @var array Characteristics are things that can change from field to field of the same field type. Like the placeholder between two email fields. Stored in db. */
	public $characteristics = array(
		'name'        => '',
		'template'   => 'hidden',
		'public'      => false,
		'required'    => false,
		'label'       => '',
		'meta_value'  => '',
		'meta_type'   => 'payment', // 'payment' or 'user' here if is_meta()
		'public'          => "public", // denotes whether a field shows in the admin only
		'show_in_exports' => "noexport", // denotes whether a field is in the CSV exports
	);


	public function set_title() {
		$title = _x( 'Hidden', 'CFM Field title translation', 'edd_cfm' );
		$title = apply_filters( 'cfm_' . $this->name() . '_field_title', $title );
		$this->supports['title'] = $title;		
	}

	/** Returns the Hidden to render a field in admin */
	public function render_field_admin( $user_id = -2, $profile = -2 ) {
		if ( $user_id === -2 ) {
			$user_id = get_current_user_id();
		}

		$output        = '';
		$output     .= sprintf( '<p class="cfm-el %1s %2s %3s">', esc_attr( $this->template() ), esc_attr( $this->name() ), esc_attr( $this->css() ) );
		ob_start(); ?>
		<?php
		$name       = $this->name();
		$meta_value = $this->characteristics['meta_value'];
		printf( '<input type="hidden" name="%s" value="%s">', esc_attr( $name ), esc_attr( $meta_value ) );
		echo "\r\n"; ?>
		<?php
		$output .= ob_get_clean();
		$output .= '</p>';
		return $output;
	}

	/** Returns the Hidden to render a field in frontend */
	public function render_field_frontend( $user_id = -2, $profile = -2 ) {
		if ( $user_id === -2 ) {
			$user_id = get_current_user_id();
		}

		$output        = '';
		$output     .= sprintf( '<p class="cfm-el %1s %2s %3s">', esc_attr( $this->template() ), esc_attr( $this->name() ), esc_attr( $this->css() ) );
		ob_start(); ?>
		<?php
		$name       = $this->name();
		$meta_value = $this->characteristics['meta_value'];
		printf( '<input type="hidden" name="%s" value="%s">', esc_attr( $name ), esc_attr( $meta_value ) );
		echo "\r\n"; ?>
		<?php
		$output .= ob_get_clean();
		$output .= '</p>';
		return $output;
	}

	/** Returns the Hidden to render a field for the formbuilder */
	public function render_formbuilder_field( $index = -2, $insert = false ) {
		$removable    = $this->can_remove_from_formbuilder();
		$meta_name    = sprintf( '%s[%d][name]', 'cfm_input', $index );
		$value_name   = sprintf( '%s[%d][meta_value]', 'cfm_input', $index );
		$label_name   = sprintf( '%s[%d][label]', 'cfm_input', $index );
		$meta_value   = esc_attr( $this->name() );
		$value_value  = esc_attr( $this->characteristics['meta_value'] );
		ob_start(); ?>
		<li class="custom-field custom_hidden_field">
			<?php $this->legend( $this->title(), $this->get_label(), $removable ); ?>
			<?php CFM_Formbuilder_Templates::public_radio( $index, $this->characteristics ); ?>
			<?php CFM_Formbuilder_Templates::export_radio( $index, $this->characteristics, "noexport" ); ?>
			<?php CFM_Formbuilder_Templates::meta_type_radio( $index, $this->characteristics, "payment" ); ?>
			<?php CFM_Formbuilder_Templates::hidden_field( "[$index][template]", $this->template() ); ?>

			<?php CFM_Formbuilder_Templates::field_div( $index, $this->name(), $this->characteristics, $insert ); ?>
				<div class="cfm-form-rows">
					<label><?php _e( 'Meta Key', 'edd_cfm' ); ?></label>
					<input type="text" name="<?php echo $meta_name; ?>" value="<?php echo $meta_value; ?>" class="smallipopInput" title="<?php _e( 'Name of the meta key this field will save to', 'edd_cfm' ); ?>">
					<input type="hidden" name="<?php echo $label_name; ?>" value="">
				</div>

				<div class="cfm-form-rows">
					<label><?php _e( 'Meta Value', 'edd_cfm' ); ?></label>
					<input type="text" class="smallipopInput" title="<?php esc_attr_e( 'Enter the meta value', 'edd_cfm' ); ?>" name="<?php echo $value_name; ?>" value="<?php echo $value_value; ?>">
				</div>
			</div>
		</li>
		<?php
		return ob_get_clean();
	}

	public function sanitize( $values = array(), $payment_id = -2, $user_id = -2 ) {
		$name = $this->name();
		$values[ $name ] = isset( $this->characteristics['meta_value'] ) ? $this->characteristics['meta_value'] : '';
		return apply_filters( 'cfm_sanitize_' . $this->template() . '_field', $values, $name, $payment_id, $user_id );
	}
}
