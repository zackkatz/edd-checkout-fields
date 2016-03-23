<?php
class CFM_Date_Field extends CFM_Field {

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
		'template'    => 'date',
		'title'       => 'Date',
	);

	/** @var array Characteristics are things that can change from field to field of the same field type. Like the placeholder between two email fields. Stored in db. */
	public $characteristics = array(
		'name'        => '',
		'template'    => 'date',
		'required'    => false,
		'label'       => '',
		'time'        => 'no',
		'view'		  => 'day',
		'size'		  => '1',
		'min'         => '',
		'max'         => '',
		'css'         => '',
		'format'    => 'mm/dd/yy',
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
		
		$value     = $this->get_field_value_admin( $this->payment_id, $this->user_id );
		if ( $value ) {
			$value 	   = new DateTime( $value );
			$date 	   = date_format( $value, "Y-m-d" );
			$datetime  = date_format( $value, "Y-m-d" ) . 'T'. date_format( $value, "H:i" );
		} else{
			$date = '';
			$datetime = '';
		}
		$size 	   = ! empty( $this->characteristics['size'] ) ? absint( $this->characteristics['size'] ) : 1;
		$view      = ! empty( $this->characteristics['view'] ) ? $this->characteristics['view'] : "day";
		
		if ( $view === 'month' ){
			$view = 1;
		} else if( $view === 'year' ){
			$view = 0;
		} else{
			$view = 2; // day
		}

		$output        = '';
		$output     .= sprintf( '<p class="cfm-el %1s %2s %3s">', esc_attr( $this->template() ), esc_attr( $this->name() ), esc_attr( $this->css() ) );
		$output    .= $this->label( false );
		ob_start(); ?>
		<?php if ( $this->characteristics['time'] == 'yes' ) { ?>
			<input name="<?php echo esc_attr( $this->name() ); ?>"  id="<?php echo esc_attr( $this->name() ); ?>" type="datetime-local" class="datepicker show-yearbtns show-uparrow text edd-input" data-datetime-local-stepfactor="1" data-datetime-local-open-on-focus="true" data-datetime-start-view="<?php echo $view; ?>" data-required="false" data-datetime-size="<?php echo $size; ?>" data-type="text" value="<?php echo esc_attr( $datetime ); ?>"  />
		<?php } else { ?>
			<input name="<?php echo esc_attr( $this->name() ); ?>"  id="<?php echo esc_attr( $this->name() ); ?>" type="date" class="datepicker show-yearbtns show-uparrow text edd-input" data-date-start-view="<?php echo $view; ?>" data-date-size="<?php echo $size; ?>" data-type="text" data-type="text" value="<?php echo esc_attr( $date ) ?>" data-required="false" data-date-open-on-focus="true" />
		<?php }
		$output .= ob_get_clean();
		$output .= '</p>';
		return $output;
	}

	/** Returns the Date to render a field in frontend */
	public function render_field_frontend( $user_id = -2, $profile = -2 ) {
		if ( $user_id === -2 ) {
			$user_id = get_current_user_id();
		}

		$value     = $this->get_field_value_frontend( $this->payment_id, $this->user_id );
		if ( $value ) {
			$value 	   = new DateTime( $value );
			$date 	   = date_format( $value, "Y-m-d" );
			$datetime  = date_format( $value, "Y-m-d" ) . 'T'. date_format( $value, "H:i" );
		} else{
			$date = '';
			$datetime = '';
		}
		$size 	   = ! empty( $this->characteristics['size'] ) ? absint( $this->characteristics['size'] ) : 1;
		$view      = ! empty( $this->characteristics['view'] ) ? $this->characteristics['view'] : "day";
		$required  = $this->required();
		if ( $view === 'month' ){
			$view = 1;
		} else if( $view === 'year' ){
			$view = 0;
		} else{
			$view = 2; // day
		}

		$output        = '';
		$output     .= sprintf( '<p class="cfm-el %1s %2s %3s">', esc_attr( $this->template() ), esc_attr( $this->name() ), esc_attr( $this->css() ) );
		$output    .= $this->label( $profile );
		ob_start(); ?>
		<?php if ( $this->characteristics['time'] == 'yes' ) { ?>
			<input name="<?php echo esc_attr( $this->name() ); ?>"  id="<?php echo esc_attr( $this->name() ); ?>" type="datetime-local" class="datepicker show-yearbtns show-uparrow text edd-input <?php echo $this->required_class(); ?>" data-datetime-local-stepfactor="1" data-datetime-local-open-on-focus="true" data-datetime-start-view="<?php echo $view; ?>" <?php $this->required_html5(); ?> data-required="<?php echo $required; ?>" data-datetime-size="<?php echo $size; ?>" data-type="text" value="<?php echo esc_attr( $datetime ); ?>"  />
		<?php } else { ?>
			<input name="<?php echo esc_attr( $this->name() ); ?>"  id="<?php echo esc_attr( $this->name() ); ?>" type="date" class="datepicker show-yearbtns show-uparrow text edd-input <?php echo $this->required_class(); ?>" data-date-start-view="<?php echo $view; ?>" data-date-size="<?php echo $size; ?>" data-type="text" data-type="text" value="<?php echo esc_attr( $date ) ?>" <?php $this->required_html5(); ?> data-required="<?php echo $required; ?>" data-date-open-on-focus="true" />
		<?php }
		$output .= ob_get_clean();
		$output .= '</p>';
		return $output;
	}

	/** Returns the Date to render a field for the formbuilder */
	public function render_formbuilder_field( $index = -2, $insert = false ) {
		$removable    = $this->can_remove_from_formbuilder();
		$time_name    = sprintf( '%s[%d][time]', 'cfm_input', $index );
		$view_name    = sprintf( '%s[%d][view]', 'cfm_input', $index );
		$time_value   = isset( $this->characteristics['time'] ) ? $this->characteristics['time'] : 'no';
		$view         = isset( $this->characteristics['view'] ) ? $this->characteristics['view'] : 'day';
		$format_value = isset( $this->characteristics['format'] ) ? $this->characteristics['format'] : 'mm/dd/yy';
		?><li class="custom-field custom_image">
			<?php $this->legend( $this->title(), $this->get_label(), $removable ); ?>
			<?php CFM_Formbuilder_Templates::hidden_field( "[$index][template]", $this->template() ); ?>
			<?php CFM_Formbuilder_Templates::hidden_field( "[$index][format]", $format_value ); ?>
			<?php CFM_Formbuilder_Templates::field_div( $index, $this->name(), $this->characteristics, $insert ); ?>
				<?php CFM_Formbuilder_Templates::public_radio( $index, $this->characteristics ); ?>
				<?php CFM_Formbuilder_Templates::export_radio( $index, $this->characteristics ); ?>
				<?php CFM_Formbuilder_Templates::meta_type_radio( $index, $this->characteristics ); ?>
				<?php CFM_Formbuilder_Templates::standard( $index, $this ); ?>
				<?php CFM_Formbuilder_Templates::css( $index, $this->characteristics ); ?>
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
				
				<div class="cfm-form-rows">
					<label><?php _e( 'Start View', 'edd_cfm' ); ?></label>
					<div class="cfm-form-sub-fields">
						<label for="view">
							<input type="radio" id="<?php echo esc_attr( $view_name ); ?>" name="<?php echo esc_attr( $view_name ); ?>" value="day" <?php checked( "day" ===  $view ); ?> data-type="label" /><?php _e( 'Day', 'edd_cfm' ); ?> <br />
							<input type="radio" id="<?php echo esc_attr( $view_name ); ?>" name="<?php echo esc_attr( $view_name ); ?>" value="month" <?php checked( "month" === $view ); ?> data-type="label" /><?php _e( 'Month', 'edd_cfm' ); ?> <br />
							<input type="radio" id="<?php echo esc_attr( $view_name ); ?>" name="<?php echo esc_attr( $view_name ); ?>" value="year" <?php checked( "year" ===  $view ); ?> data-type="label" /><?php _e( 'Year', 'edd_cfm' ); ?> <br />
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
			$value 			 = trim( $values[ $name ] );
		}
		return apply_filters( 'cfm_sanitize_' . $this->template() . '_field', $values, $name, $payment_id, $user_id );
	}
}
