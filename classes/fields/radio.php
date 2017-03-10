<?php
class CFM_Radio_Field extends CFM_Field {

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
		'template'   => 'radio',
		'title'       => 'Radio',
	);

	/** @var array Characteristics are things that can change from field to field of the same field type. Like the placeholder between two radio fields. Stored in db. */
	public $characteristics = array(
		'name'        => '',
		'template'   => 'radio',
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
		'public'          => "public", // denotes whether a field shows in the admin only
		'show_in_exports' => "export", // denotes whether a field is in the CSV exports
	);

	public function set_title() {
		$title = _x( 'Radio', 'CFM Field title translation', 'edd_cfm' );
		$title = apply_filters( 'cfm_' . $this->name() . '_field_title', $title );
		$this->supports['title'] = $title;		
	}

	/** Returns the HTML to render a field in admin */
	public function render_field_admin( $user_id = -2, $profile = -2 ) {
		if ( $user_id === -2 ) {
			$user_id = get_current_user_id();
		}

		
		$value     = $this->get_field_value_admin( $this->payment_id, $this->user_id );
		$output        = '';
		$output     .= sprintf( '<p class="cfm-el %1s %2s %3s">', esc_attr( $this->template() ), esc_attr( $this->name() ), esc_attr( $this->css() ) );
		$output    .= $this->label( false );
		if ( !is_array( $value ) ){
			$values = array();
			array_push( $values, $value );
			$value = $values;
		}
		ob_start(); ?>
		<?php
		if ( $this->characteristics['options'] && count( $this->characteristics['options'] ) > 0 ) {
			echo '<ul class="cfm-checkbox-checklist" class="radio edd-input">';
			foreach ( $this->characteristics['options'] as $option ) { 
				echo '<li>';?>
						<input name="<?php echo esc_attr( $this->name() ); ?>" id="<?php echo esc_attr( $this->name() ); ?>" type="radio" class="radio edd-input" value="<?php echo esc_attr( $option ); ?>" <?php echo in_array( $option, $value ) ? ' checked="checked"' : ''; ?> />
						<?php _e( $option, 'edd_cfm' ); ?>
					<?php
				echo '</li>';
			}
			echo '</ul>';
		} ?>
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

		$value     = $this->get_field_value_frontend( $this->payment_id, $user_id );
		if ( ! $profile && is_integer( $user_id ) && $user_id > 0 && ! metadata_exists( 'user', $user_id, $this->name() ) ) {
			$value  = isset( $this->characteristics['selected'] ) ? $this->characteristics['selected'] : array();
		}
		
		$required  = $this->required();
		$output        = '';
		$output     .= sprintf( '<p class="cfm-el %1s %2s %3s">', esc_attr( $this->template() ), esc_attr( $this->name() ), esc_attr( $this->css() ) );
		$output    .= $this->label( ! (bool) $profile );
		if ( !is_array( $value ) ){
			$values = array();
			array_push( $values, $value );
			$value = $values;
		}
		ob_start(); ?>
		<?php
		if ( $this->characteristics['options'] && count( $this->characteristics['options'] ) > 0 ) {
			echo '<ul class="cfm-checkbox-checklist" class="radio edd-input ' . $this->required_class() . '">';
			foreach ( $this->characteristics['options'] as $option ) { 
				echo '<li>';?>
						<input name="<?php echo $this->name(); ?>" type="radio" value="<?php echo esc_attr( $option ); ?>" <?php echo in_array( $option, $value ) ? ' checked="checked"' : ''; ?> />
						<?php _e( $option, 'edd_cfm' ); ?>
					<?php
				echo '</li>';
			}
			echo '</ul>';
		} ?>
		<?php
		$output .= ob_get_clean();
		$output .= '</p>';
		return $output;
	}

	/** Returns the HTML to render a field for the formbuilder */
	public function render_formbuilder_field( $index = -2, $insert = false ) {
		$removable = $this->can_remove_from_formbuilder();
		ob_start(); ?>
		<li class="custom-field radio_field">
			<?php $this->legend( $this->title(), $this->get_label(), $removable ); ?>
			<?php CFM_Formbuilder_Templates::hidden_field( "[$index][template]", $this->template() ); ?>

			<?php CFM_Formbuilder_Templates::field_div( $index, $this->name(), $this->characteristics, $insert ); ?>
				<?php CFM_Formbuilder_Templates::public_radio( $index, $this->characteristics ); ?>
				<?php CFM_Formbuilder_Templates::export_radio( $index, $this->characteristics ); ?>
				<?php CFM_Formbuilder_Templates::meta_type_radio( $index, $this->characteristics ); ?>
				<?php CFM_Formbuilder_Templates::standard( $index, $this ); ?>
				<?php CFM_Formbuilder_Templates::css( $index, $this->characteristics ); ?>

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
}
