<?php
class CFM_Multiselect_Field extends CFM_Field {

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
		'template'   => 'multiselect',
		'title'       => 'Multiselect',
	);

	/** @var array Characteristics are things that can change from field to field of the same field type. Like the placeholder between two text fields. Stored in db. */
	public $characteristics = array(
		'name'        => '',
		'template'   => 'multiselect',
		'required'    => false,
		'label'       => '',
		'css'         => '',
		'default'     => '',
		'size'        => '',
		'help'        => '',
		'placeholder' => '',
		'first'       => '- select -',
		'selected'    => '',
		'options'     => '',
		'meta_type'   => 'payment', // 'payment' or 'user' here if is_meta()
		'public'          => "public", // denotes whether a field shows in the admin only
		'show_in_exports' => "export", // denotes whether a field is in the CSV exports
	);


	public function set_title() {
		$title = _x( 'Multiselect', 'CFM Field title translation', 'edd_cfm' );
		$title = apply_filters( 'cfm_' . $this->name() . '_field_title', $title );
		$this->supports['title'] = $title;		
	}

	/** Returns the HTML to render a field in admin */
	public function render_field_admin( $user_id = -2, $profile = -2 ) {
		if ( $user_id === -2 ) {
			$user_id = get_current_user_id();
		}

		$value = $this->get_field_value_admin( $this->payment_id, $this->user_id );

		if ( ! is_array( $value ) ){
			$one = $value;
			$value = array();
			$value[0] = $one;
		}

		$output        = '';
		$output     .= sprintf( '<p class="cfm-el %1s %2s %3s">', esc_attr( $this->template() ), esc_attr( $this->name() ), esc_attr( $this->css() ) );
		$output    .= $this->label( false );
		ob_start(); ?>
		<select name="<?php echo esc_attr( $this->name() ); ?>[]" id="<?php echo esc_attr( $this->name() ); ?>" class="multiselect select edd-input" multiple="multiple" data-required="false" data-type="multiselect">
			<?php if ( !empty( $this->characteristics['first'] ) ) { ?>
				<option value=""><?php echo $this->characteristics['first']; ?></option>
			<?php } ?>
			<?php
			if ( $this->characteristics['options'] && count( $this->characteristics['options'] ) > 0 ) {
				foreach ( $this->characteristics['options'] as $option ) { 
					$is_selected = in_array( $option, $value );
					?>
				<option value="<?php echo esc_attr( $option ); ?>"<?php selected( $is_selected, true ); ?>><?php echo $option; ?></option><?php
				}
			} ?>
		</select>
		<?php
		$output .= ob_get_clean();
		$output .= '</p>';
		return $output;
	}

	/** Returns the HTML to render a field in frontend */
	public function render_field_frontend( $user_id = -2, $profile = -2 ) {
		if ( $user_id === -2 ) {
			$user_id = get_current_user_id();
		}
		
		$value     = $this->get_field_value_frontend( $this->payment_id, $this->user_id );
		if ( ! $profile && is_integer( $this->user_id ) && $this->user_id > 0 && ! metadata_exists( 'user', $this->user_id, $this->name() ) ) {
			$value  = isset( $this->characteristics['selected'] ) ? $this->characteristics['selected'] : array();
		}
		
		$required  = $this->required();

		if ( ! is_array( $value ) ){
			$one = $value;
			$value = array();
			$value[0] = $one;
		}		

		$output        = '';
		$output     .= sprintf( '<p class="cfm-el %1s %2s %3s">', esc_attr( $this->template() ), esc_attr( $this->name() ), esc_attr( $this->css() ) );
		$output    .= $this->label( ! (bool) $profile );
		ob_start(); ?>
		<select name="<?php echo esc_attr( $this->name() ); ?>[]" id="<?php echo esc_attr( $this->name() ); ?>" class="multiselect select edd-input <?php echo $this->required_class(); ?>" multiple="multiple" data-required="<?php echo $required; ?>" data-type="multiselect"<?php $this->required_html5(); ?>>
			<?php if ( !empty( $this->characteristics['first'] ) ) { ?>
				<option value=""><?php echo $this->characteristics['first']; ?></option>
			<?php } ?>
			<?php
			if ( $this->characteristics['options'] && count( $this->characteristics['options'] ) > 0 ) {
				foreach ( $this->characteristics['options'] as $option ) {
					$is_selected = in_array( $option, $value );
					?>
					<option value="<?php echo esc_attr( $option ); ?>"<?php selected( $is_selected, true ); ?>><?php echo $option; ?></option><?php
				}
			} ?>
		</select>
		<?php
		$output .= ob_get_clean();
		$output .= '</p>';
		return $output;
	}

	/** Returns the HTML to render a field for the formbuilder */
	public function render_formbuilder_field( $index = -2, $insert = false ) {
		$removable    = $this->can_remove_from_formbuilder();
		$first_name  = sprintf( '%s[%d][first]', 'cfm_input', $index );
		$first_value = $this->characteristics['first'];
		$help        = esc_attr( __( 'First element of the select dropdown. Leave this empty if you don\'t want to show this field', 'edd_cfm' ) );
		ob_start(); ?>
		<li class="custom-field multiple_multiselect">
			<?php $this->legend( $this->title(), $this->get_label(), $removable ); ?>
			<?php CFM_Formbuilder_Templates::hidden_field( "[$index][template]", $this->template() ); ?>

			<?php CFM_Formbuilder_Templates::field_div( $index, $this->name(), $this->characteristics, $insert ); ?>
				<?php CFM_Formbuilder_Templates::public_radio( $index, $this->characteristics ); ?>
				<?php CFM_Formbuilder_Templates::export_radio( $index, $this->characteristics ); ?>
				<?php CFM_Formbuilder_Templates::meta_type_radio( $index, $this->characteristics ); ?>
				<?php CFM_Formbuilder_Templates::standard( $index, $this ); ?>
				<?php CFM_Formbuilder_Templates::css( $index, $this->characteristics ); ?>

				<div class="cfm-form-rows">
					<label><?php _e( 'Select Text', 'edd_cfm' ); ?></label>
					<input type="text" class="smallipopInput" name="<?php echo $first_name; ?>" value="<?php echo $first_value; ?>" title="<?php echo $help; ?>">
				</div>

				<div class="cfm-form-rows">
					<label><?php _e( 'Options', 'edd_cfm' ); ?></label>

					<div class="cfm-form-sub-fields">
						<?php CFM_Formbuilder_Templates::radio_fields( $index, 'options', $this->characteristics ); ?>
					</div>
				</div>
			</div>
		</li>
		<?php
		return ob_get_clean();
	}

	public function sanitize( $values = array(), $payment_id = -2, $user_id = -2 ) {
		$name = $this->name();
		if ( ! empty( $values[ $name ] ) ) {
			if ( is_array( $values[ $name ] ) ) {
				foreach ( $values[ $name ] as $key => $string ) {
					$values[ $name ][ $key ] = trim( $string );
					$values[ $name ][ $key ] = sanitize_text_field( $values[ $name ][ $key ] );
				}
			} else {
				$values[ $name ] = trim( $values[ $name ] );
				$values[ $name ] = sanitize_text_field( $values[ $name ] );
			}
		}
		if ( ! empty( $values[ $name ] ) && isset( $values[ $name ][0] ) && $values[ $name ][0] === '' ){
			unset( $values [ $name ][0] );
		}
		return apply_filters( 'cfm_sanitize_' . $this->template() . '_field', $values, $name, $payment_id, $user_id );
	}
}
