<?php
class CFM_Date_Field extends CFM_Field {

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
		'template'    => 'date',
		'title'       => 'Date',
	);

	/** @var array Characteristics are things that can change from field to field of the same field type. Like the placeholder between two email fields. Stored in db. */
	public $characteristics = array(
		'name'        => '',
		'template'    => 'date',
		'required'    => false,
		'label'       => '',
		'format'    => 'mm/dd/yy',
		'time'        => 'no',
		'meta_type'   => 'payment', // 'payment' or 'user' here if is_meta()
		'public'          => "public", // denotes whether a field shows in the admin only
		'show_in_exports' => "export", // denotes whether a field is in the CSV exports
	);


	public function set_title() {
		$title = _x( 'Date', 'CFM Field title translation', 'edd_cfm' );
		$title = apply_filters( 'cfm_' . $this->name() . '_field_title', $title );
		$this->supports['title'] = $title;		
	}

	/** Returns the Date to render a field in admin */
	public function render_field_admin( $user_id = -2, $profile = -2 ) {
		if ( $user_id === -2 ) {
			$user_id = get_current_user_id();
		}
		
		$value     = $this->get_field_value_frontend( $this->payment_id, $this->user_id );
		$output     = '';
		$output     .= sprintf( '<fieldset class="cfm-el %1s %2s %3s">', $this->template(), $this->name(), $this->css() );
		$output    .= $this->label();
		ob_start(); ?>
		<div class="cfm-fields">
			<input id="<?php echo $this->name(); ?>" type="text" class="datepicker" data-required="false" data-type="text" name="<?php echo esc_attr( $this->name() ); ?>" value="<?php echo esc_attr( $value ) ?>" size="30" />
		</div>
		<script type="text/javascript">
			jQuery(function($) {
			<?php if ( $this->characteristics['time'] == 'yes' ) { ?>
				$("#<?php echo $this->name(); ?>").datetimepicker({ dateFormat: '<?php echo $this->characteristics['format']; ?>' });
			<?php } else { ?>
				$("#<?php echo $this->name(); ?>").datepicker({ dateFormat: '<?php echo $this->characteristics['format']; ?>' });
			<?php } ?>
			});
		</script>
		<?php
		$output .= ob_get_clean();
		$output .= '</fieldset>';
		return $output;
	}

	/** Returns the Date to render a field in frontend */
	public function render_field_frontend( $user_id = -2, $profile = -2 ) {
		if ( $user_id === -2 ) {
			$user_id = get_current_user_id();
		}

		$value     = $this->get_field_value_frontend( $this->payment_id, $this->user_id );
		$required  = $this->required();
		$output        = '';
		$output     .= sprintf( '<fieldset class="cfm-el %1s %2s %3s">', $this->template(), $this->name(), $this->css() );
		$output    .= $this->label();
		ob_start(); ?>
		<div class="cfm-fields">
			<input id="<?php echo $this->name(); ?>" type="text" class="datepicker" data-required="<?php echo $required; ?>" data-type="text"<?php $this->required_html5(); ?> name="<?php echo esc_attr( $this->name() ); ?>" value="<?php echo esc_attr( $value ) ?>" size="30" />
		</div>
		<script type="text/javascript">
			jQuery(function($) {
			<?php if ( $this->characteristics['time'] == 'yes' ) { ?>
				$("#<?php echo $this->name(); ?>").datetimepicker({ dateFormat: '<?php echo $this->characteristics['format']; ?>' });
			<?php } else { ?>
				$("#<?php echo $this->name(); ?>").datepicker({ dateFormat: '<?php echo $this->characteristics['format']; ?>' });
			<?php } ?>
			});
		</script>
		<?php
		$output .= ob_get_clean();
		$output .= '</fieldset>';
		return $output;
	}

	/** Returns the Date to render a field for the formbuilder */
	public function render_formbuilder_field( $index = -2, $insert = false ) {
		$removable    = $this->can_remove_from_formbuilder();
		$format_name  = sprintf( '%s[%d][format]', 'cfm_input', $index );
		$time_name    = sprintf( '%s[%d][time]', 'cfm_input', $index );
		$format_value = $this->characteristics['format'];
		$time_value   = $this->characteristics['time'];
		$help         = esc_attr( __( 'The date format', 'edd_cfm' ) ); ?>
		<li class="custom-field custom_image">
			<?php $this->legend( $this->title(), $this->get_label(), $removable ); ?>
			<?php CFM_Formbuilder_Templates::hidden_field( "[$index][template]", $this->template() ); ?>

			<?php CFM_Formbuilder_Templates::field_div( $index, $this->name(), $this->characteristics, $insert ); ?>
				<?php CFM_Formbuilder_Templates::public_radio( $index, $this->characteristics ); ?>
				<?php CFM_Formbuilder_Templates::export_radio( $index, $this->characteristics ); ?>
				<?php CFM_Formbuilder_Templates::meta_type_radio( $index, $this->characteristics ); ?>
				<?php CFM_Formbuilder_Templates::standard( $index, $this ); ?>

				<div class="cfm-form-rows">
					<label><?php _e( 'Date Format', 'edd_cfm' ); ?></label>
					<input type="text" class="smallipopInput" name="<?php echo $format_name; ?>" value="<?php echo $format_value; ?>" title="<?php echo $help; ?>">
				</div>

				<div class="cfm-form-rows">
					<label><?php _e( 'Time', 'edd_cfm' ); ?></label>

					<div class="cfm-form-sub-fields">
						<label>
							<?php CFM_Formbuilder_Templates::hidden_field( "[$index][time]", 'no' ); ?>
							<input type="checkbox" name="<?php echo $time_name ?>" value="yes"<?php checked( $time_value, 'yes' ); ?> />
							<?php _e( 'Enable time input', 'edd_cfm' ); ?>
						</label>
					</div>
				</div>
			</div>
		</li>

		<?php
		return ob_get_clean();
	}

	public function sanitize( $values = array(), $payment_id = -2, $user_id = -2 ) {
		$name = $this->name();
		if ( !empty( $values[ $name ] ) ) {
			$values[ $name ] = trim( $values[ $name ] );
		}
		return apply_filters( 'cfm_sanitize_' . $this->template() . '_field', $values, $name, $payment_id, $user_id );
	}
}
