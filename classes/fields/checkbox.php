<?php
class CFM_Checkbox_Field extends CFM_Field {

	/** @var bool For 3rd parameter of get_post/user_meta */
	public $single = true;

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
		'template'    => 'checkbox',
		'title'       => 'Checkbox',
		'export'   => true
	);

	/** @var array Characteristics are things that can change from field to field of the same field type. Like the placeholder between two checkbox fields. Stored in db. */
	public $characteristics = array(
		'name'        => '',
		'template'    => 'checkbox',
		'public'      => true,
		'required'    => false,
		'label'       => '',
		'css'         => '',
		'default'     => '',
		'size'        => '',
		'help'        => '',
		'placeholder' => '',
		'options'     => '',
		'selected'    => '',
		'meta_type'   => 'payment', // 'payment' or 'user' here if is_meta()
		'public'          => true, // denotes whether a field shows in the admin only
		'show_in_exports' => true, // denotes whether a field is in the CSV exports
	);

	public function set_title() {
		$title = _x( 'Checkbox', 'CFM Field title translation', 'edd_cfm' );
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

		$user_id   = apply_filters( 'cfm_render_checkbox_field_user_id_admin', $user_id, $this->id );
		$readonly  = apply_filters( 'cfm_render_checkbox_field_readonly_admin', $readonly, $user_id, $this->id );
		$value     = $this->get_field_value_admin( $this->payment_id, $user_id, $readonly );
		$selected  = isset( $this->characteristics['selected'] ) ? $this->characteristics['selected'] : array();
		$required  = $this->required( $readonly );

		$output        = '';
		$output     .= sprintf( '<fieldset class="cfm-el %1s %2s %3s">', $this->template(), $this->name(), $this->css() );
		$output    .= $this->label( $readonly );
		
		if ( $this->payment_id > 0 && ( $this->type !== 'post' || ( $this->type === 'post' && get_post_status( $this->payment_id ) !== 'auto-draft' ) ) ) {
			$selected = $this->get_meta( $this->payment_id, $this->name(), $this->type );
			if ( !is_array( $selected ) ){
				$selected = explode( '|', $selected );
			}
		}
		ob_start(); ?>
		<div class="cfm-fields">
			<?php
			if ( isset( $this->characteristics['options'] ) && count( $this->characteristics['options'] ) > 0 ) {
				echo '<ul class="cfm-checkbox-checklist">';
				foreach ( $this->characteristics['options'] as $option ) {
					echo '<li>';?>
						<input type="checkbox" name="<?php echo $this->name(); ?>[]" value="<?php echo esc_attr( $option ); ?>"<?php echo in_array( $option, $selected ) ? ' checked="checked"' : ''; ?> />
						<?php echo __( $option, 'edd_cfm' ); ?>
					<?php
					echo '</li>';
				}
				echo '</ul>';
			}
			?>
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

		$user_id   = apply_filters( 'cfm_render_checkbox_field_user_id_frontend', $user_id, $this->id );
		$readonly  = apply_filters( 'cfm_render_checkbox_field_readonly_frontend', $readonly, $user_id, $this->id );
		$value     = $this->get_field_value_frontend( $this->payment_id, $user_id, $readonly );
		$selected  = isset( $this->characteristics['selected'] ) ? $this->characteristics['selected'] : array();
		$required  = $this->required( $readonly );

		$output        = '';
		$output     .= sprintf( '<fieldset class="cfm-el %1s %2s %3s">', $this->template(), $this->name(), $this->css() );
		$output    .= $this->label( $readonly );

		if ( $this->payment_id > 0 ) {
			$selected = $this->get_meta( $this->payment_id, $this->name(), $this->type );
			if ( !is_array( $selected ) ){
				$selected = explode( '|', $selected );
			}
		}
		ob_start(); ?>
		<div class="cfm-fields">
			<span data-required="<?php echo $required; ?>" data-type="radio"></span>
			<?php
			if ( isset( $this->characteristics['options'] ) && count( $this->characteristics['options'] ) > 0 ) {
				echo '<ul class="cfm-checkbox-checklist">';
				foreach ( $this->characteristics['options'] as $option ) {
					echo '<li><label>';?>
						<input type="checkbox" name="<?php echo $this->name(); ?>[]" value="<?php echo esc_attr( $option ); ?>"<?php echo in_array( $option, $selected ) ? ' checked="checked"' : ''; ?> />
						<?php echo __( $option, 'edd_cfm' ); ?>
					<?php
					echo '</label></li>';
				}
				echo '</ul>';
			}
			?>
		</div>
		<?php
		$output .= ob_get_clean();
		$output .= '</fieldset>';
		return $output;
	}
	
	public function export_data( $payment_id = -2, $user_id = -2 ) {
		if ( $user_id === -2 ) {
			$user_id = get_current_user_id();
		}

		$user_id   = apply_filters( 'cfm_formatted_' . $this->template() . '_field_user_id', $user_id, $this->id );
		$value     = $this->get_field_value_frontend( $this->payment_id, $user_id );
		if ( ! is_array( $value ) ) {
			$value = explode( '|', $value );
		} else {
			$value = array_map( 'trim', $value );
		}
		$value = implode( ', ', $value );
		return $value;
	}

	/** Returns the HTML to render a field for the formbuilder */
	public function render_formbuilder_field( $index = -2, $insert = false ) {
		$removable = $this->can_remove_from_formbuilder();
		ob_start(); ?>
		<li class="custom-field checkbox_field">
			<?php $this->legend( $this->title(), $this->get_label(), $removable ); ?>
			<?php CFM_Formbuilder_Templates::hidden_field( "[$index][template]", $this->template() ); ?>

			<?php CFM_Formbuilder_Templates::field_div( $index, $this->name(), $this->characteristics, $insert ); ?>
				<?php CFM_Formbuilder_Templates::public_radio( $index, $this->characteristics, $this->form_name ); ?>
				<?php CFM_Formbuilder_Templates::standard( $index, $this ); ?>
				<div class="cfm-form-rows">
					<label><?php _e( 'Options', 'edd_cfm' ); ?></label>

					<div class="cfm-form-sub-fields">
						<?php CFM_Formbuilder_Templates::common_checkbox( $index, 'options', $this->characteristics ); ?>
					</div>
				</div>
			</div>
		</li>
		<?php
		return ob_get_clean();
	}

	public function validate( $values = array(), $payment_id = -2, $user_id = -2 ) {
		$name = $this->name();
		if ( !empty( $values[ $name ] ) ) {
			// if the value is set

		} else {
			// if the checkbox is required but isn't present
			if ( $this->required() ) {
				if ( is_array( $this->characteristics['options'] ) ) {
					edd_set_error( 'select_checkbox_option_' . $this->id, sprintf( __( 'Please select at least 1 option for %s.', 'edd_cfm' ), $this->get_label() ) );
				} else {
					edd_set_error( 'select_checkbox_' . $this->id, sprintf( __( 'Please check the checkbox for %s.', 'edd_cfm' ), $this->get_label() ) );
				}
			}
		}
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
		return apply_filters( 'cfm_sanitize_' . $this->template() . '_field', $values, $name, $payment_id, $user_id );
	}
}
