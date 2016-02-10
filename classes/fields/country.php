<?php
class CFM_Country_Field extends CFM_Field {

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
		'template'   => 'country',
		'title'      => 'Country',
	);

	/** @var array Characteristics are things that can change from field to field of the same field type. Like the placeholder between two country fields. Stored in db. */
	public $characteristics = array(
		'name'        => '',
		'template'   => 'country',
		'required'    => false,
		'label'       => '',
		'css'         => '',
		'default'     => '',
		'size'        => '',
		'help'        => '',
		'placeholder' => '',
		'options'     => array(),
		'first'       => ' - select -',
		'meta_type'   => 'payment', // 'payment' or 'user' here if is_meta()
		'public'          => "public", // denotes whether a field shows in the admin only
		'show_in_exports' => "export", // denotes whether a field is in the CSV exports
	);


	public function set_title() {
		$title = _x( 'Country', 'CFM Field title translation', 'edd_cfm' );
		$title = apply_filters( 'cfm_' . $this->name() . '_field_title', $title );
		$this->supports['title'] = $title;		
	}

	/** Returns the HTML to render a field in admin */
	public function render_field_admin( $user_id = -2, $profile = -2 ) {
		if ( $user_id === -2 ) {
			$user_id = get_current_user_id();
		}

		$value     = $this->get_field_value_admin( $this->payment_id, $this->user_id );
		if ( is_array( $value ) ){
			$value = $value[0];
		}
		$output        = '';
		$output     .= sprintf( '<p class="cfm-el %1s %2s %3s">', esc_attr( $this->template() ), esc_attr( $this->name() ), esc_attr( $this->css() ) );
		$output    .= $this->label( false );
		ob_start(); ?>
		<select name="<?php echo esc_attr( $this->name() ); ?>[]"  id="<?php echo esc_attr( $this->name() ); ?>" data-required="false" data-type="select" class="select edd-input">
			<?php if ( !empty( $this->characteristics['first'] ) ) { ?>
				<option value=""><?php echo $this->characteristics['first']; ?></option>
				<?php }
				if ( $this->characteristics['options'] && count( $this->characteristics['options'] ) > 0 ) {
					foreach ( $this->characteristics['options'] as $option ) {
						$current_select = selected( $value, $option, false ); ?>
						<option value="<?php echo esc_attr( $option ); ?>"<?php echo $current_select; ?>><?php echo $option; ?></option><?php
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

		$value     = $this->get_field_value_frontend(  $this->payment_id, $this->user_id );
		$required  = $this->required();

		if ( ! $profile && is_integer( $this->user_id ) && $this->user_id > 0 && ! metadata_exists( 'user', $this->user_id, $this->name() ) ) {
			$value = $this->characteristics['selected'];
		}
		if ( is_array( $value ) ){
			$value = $value[0];
		}
		$output        = '';
		$output     .= sprintf( '<p class="cfm-el %1s %2s %3s">', esc_attr( $this->template() ), esc_attr( $this->name() ), esc_attr( $this->css() ) );
		$output    .= $this->label( ! (bool) $profile );
		ob_start(); ?>
		<select name="<?php echo esc_attr( $this->name() ); ?>[]"  id="<?php echo esc_attr( $this->name() ); ?>" data-required="<?php echo $required; ?>" data-type="select"<?php $this->required_html5(); ?> class="select edd-input <?php echo $this->required_class(); ?>">
			<?php if ( !empty( $this->characteristics['first'] ) ) { ?>
				<option value=""><?php echo $this->characteristics['first']; ?></option>
			<?php }
				if ( $this->characteristics['options'] && count( $this->characteristics['options'] ) > 0 ) {
					foreach ( $this->characteristics['options'] as $option ) {
						$current_select = selected( $value, $option, false ); ?>
						<option value="<?php echo esc_attr( $option ); ?>"<?php echo $current_select; ?>><?php echo $option; ?></option><?php
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
		$removable = $this->can_remove_from_formbuilder();
		$first_name = sprintf( '%s[%d][first]', 'cfm_input', $index );
		$first_value = $this->characteristics['first'];
		$values['options'] = empty( $this->characteristics['options'] ) ? edd_get_country_list() : $this->characteristics['options'];
		$values['label']   = $this->get_label() ? __( 'Vendor Country', 'edd_cfm' ) : $this->get_label();
		$values['name']    = $this->name();
		$help = esc_attr( __( 'First element of the select dropdown. Leave this empty if you don\'t want to show this field', 'edd_cfm' ) );
		ob_start(); ?>
		<li class="custom-field country">
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
					<label><?php _e( 'Countries', 'edd_cfm' ); ?></label>

					<div class="cfm-form-sub-fields">
						<?php CFM_Formbuilder_Templates::radio_fields( $index, 'options', $values ); ?>
					</div>
				</div>
			</div>
		</li>
		<?php
		return ob_get_clean();
	}

	public function sanitize( $values = array(), $payment_id = -2, $user_id = -2 ) {
		$name = $this->name();
		if ( !empty( $values[ $name ][0] ) ) {
			$values[ $name ] = trim( $values[ $name ][0] );
			$values[ $name ] = sanitize_text_field( $values[ $name ] );
		} else if ( isset( $values[ $name ][0] ) ){
			$values[ $name ] = '';
		}
		return apply_filters( 'cfm_sanitize_' . $this->template() . '_field', $values, $name, $payment_id, $user_id );
	}
}
