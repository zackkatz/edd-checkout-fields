<?php
class CFM_Repeat_Field extends CFM_Field {

	/** @var bool For 3rd parameter of get_post/user_meta */
	public $single = true;

	/** @var array Supports are things that are the same for all fields of a field type. Like whether or not a field type supports jQuery Phoenix. Stored in obj, not db. */
	public $supports = array(
		'multiple'    => false,
		'is_meta'     => true,  // in object as public (bool) $meta;
		'forms'       => array(
			'registration'     => true,
			'submission'       => true,
			'vendor-contact'   => false,
			'profile'          => true,
			'login'            => false,
		),
		'position'    => 'custom',
		'permissions' => array(
			'can_remove_from_formbuilder' => true,
			'can_change_meta_key'         => true,
			'can_add_to_formbuilder'      => true,
		),
		'template'    => 'repeat',
		'title'       => 'Repeat',
		'phoenix'      => false,
	);

	/** @var array Characteristics are things that can change from field to field of the same field type. Like the placeholder between two email fields. Stored in db. */
	public $characteristics = array(
		'name'        => '',
		'template'    => 'repeat',
		'public'      => false,
		'required'    => false,
		'label'       => '',
		'css'         => '',
		'default'     => '',
		'size'        => '',
		'help'        => '',
		'placeholder' => '',
		'multiple'    => array(),
		'columns'     => false,
		'size'     => '40',
	);

	public function set_title() {
		$title = _x( 'Repeat', 'CFM Field title translation', 'edd_cfm' );
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

		$user_id   = apply_filters( 'cfm_render_repeat_field_user_id_admin', $user_id, $this->id );
		$readonly  = apply_filters( 'cfm_render_repeat_field_readonly_admin', $readonly, $user_id, $this->id );
		$value     = $this->get_field_value_admin( $this->save_id, $user_id, $readonly );
		$add       = cfm_assets_url .'img/add.png';
		$remove    = cfm_assets_url. 'img/remove.png';
		$required  = $this->required( $readonly );
		$output        = '';
		$output     .= sprintf( '<fieldset class="cfm-el %1s %2s %3s">', $this->template(), $this->name(), $this->css() );
		$output    .= $this->label( $readonly );
		ob_start(); ?>

		<div class="cfm-fields">

			<?php if ( isset( $this->characteristics['multiple'] ) ) { ?>
				<table>
					<thead>
						<tr>
							<?php
							$num_columns = count( $this->characteristics['columns'] );
							foreach ( $this->characteristics['columns'] as $column ) { ?>
								<th><?php echo $column; ?></th>
							<?php } ?>
							<th>
								<?php _e( 'Actions', 'edd_cfm' ); ?>
							</th>
						</tr>

					</thead>
					<tbody>
						<?php
						$row_count = count( $value ) > 0 ? count( $value ) - 1 : 0;
						if ( $row_count > 0 ) {
							for ( $row = 0; $row <= $row_count; $row++ ) { ?>
								<tr data-key="<?php echo $row; ?>">
									<?php for ( $count = 0; $count < $num_columns; $count++ ) { ?>
										<td class="cfm-repeat-field">
											<input type="text" name="<?php echo $this->name() . '[' . $row . '][' . $count . ']'; ?>" value="<?php echo esc_attr( $value[ $row ][ $count ] ); ?>" size="<?php echo esc_attr( $this->size() ); ?>" data-required="<?php echo $required; ?>" data-type="text"<?php $this->required_html5( $readonly ); ?> />
										</td>
									<?php } ?>
									<td class="cfm-repeat-field">
										<img class="cfm-clone-field" alt="<?php esc_attr_e( 'Add another', 'edd_cfm' ); ?>" title="<?php esc_attr_e( 'Add another', 'edd_cfm' ); ?>" src="<?php echo $add; ?>">
										<img class="cfm-remove-field" alt="<?php esc_attr_e( 'Remove this choice', 'edd_cfm' ); ?>" title="<?php esc_attr_e( 'Remove this choice', 'edd_cfm' ); ?>" src="<?php echo $remove; ?>">
									</td>
								</tr>
								<?php
							}
						} else { ?>
							<tr data-key="<?php echo $row_count; ?>">
								<?php for ( $count = 0; $count < $num_columns; $count++ ) { ?>
									<td class="cfm-repeat-field">
										<input type="text" name="<?php echo $this->name() . '[0][' . $count . ']'; ?>" size="<?php echo esc_attr( $this->size() ) ?>"  value="<?php echo $value[0][ $count ]; ?>" data-required="<?php echo $required; ?>" data-type="text"<?php $this->required_html5( $readonly ); ?> />
									</td>
								<?php } ?>
								<td class="cfm-repeat-field">
									<img class="cfm-clone-field" alt="<?php esc_attr_e( 'Add another', 'edd_cfm' ); ?>" title="<?php esc_attr_e( 'Add another', 'edd_cfm' ); ?>" src="<?php echo $add; ?>">
									<img class="cfm-remove-field" alt="<?php esc_attr_e( 'Remove this choice', 'edd_cfm' ); ?>" title="<?php esc_attr_e( 'Remove this choice', 'edd_cfm' ); ?>" src="<?php echo $remove; ?>">
								</td>
							</tr>

						<?php } ?>

					</tbody>
				</table>
			<?php } else { ?>
				<table>
					<?php
					if ( $value && count( $value ) > 1 ) {
						foreach ( $value as $item ) { ?>
						 <tr>
							 <td class="cfm-repeat-field">
								 <input id="cfm-<?php echo $this->name(); ?>" type="text" data-required="<?php echo $required; ?>" data-type="text"<?php $this->required_html5( $readonly ); ?> name="<?php echo esc_attr( $this->name() ); ?>[]" placeholder="<?php echo esc_attr( $this->placeholder() ); ?>" value="<?php echo esc_attr( $item ) ?>" size="<?php echo esc_attr( $this->size() ) ?>" />
							 </td>
							 <td class="cfm-repeat-field">
								 <img style="cursor:pointer; margin:0 3px;" alt="add another choice" title="add another choice" class="cfm-clone-field" src="<?php echo $add; ?>">
								 <img style="cursor:pointer;" class="cfm-remove-field" alt="remove this choice" title="remove this choice" src="<?php echo $remove; ?>">
							 </td>
						 </tr>
								<?php
						} //endforeach
					} else { ?>
							 <tr>
								 <td class="cfm-repeat-field">
									 <input id="cfm-<?php echo $this->name(); ?>" type="text" data-required="<?php echo $required; ?>" data-type="text"<?php $this->required_html5( $readonly ); ?> name="<?php echo esc_attr( $this->name() ); ?>[]" placeholder="<?php echo esc_attr( $this->placeholder() ); ?>" value="<?php echo esc_attr( $this->characteristics['default'] ) ?>" size="<?php echo esc_attr( $this->size() ); ?>" />
								 </td>
								 <td class="cfm-repeat-field">
									 <img style="cursor:pointer; margin:0 3px;" alt="add another choice" title="<?php _e( 'add another choice', 'edd_cfm' ); ?>" class="cfm-clone-field" src="<?php echo $add; ?>">
									 <img style="cursor:pointer;" class="cfm-remove-field" alt="remove this choice" title="<?php _e( 'remove this choice', 'edd_cfm' ); ?>" src="<?php echo $remove; ?>">
								 </td>
							 </tr>
					<?php } ?>
				</table>
		<?php } ?>
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

		$user_id   = apply_filters( 'cfm_render_repeat_field_user_id_frontend', $user_id, $this->id );
		$readonly  = apply_filters( 'cfm_render_repeat_field_readonly_frontend', $readonly, $user_id, $this->id );
		$value     = $this->get_field_value_frontend( $this->save_id, $user_id, $readonly );
		$add       = cfm_assets_url .'img/add.png';
		$remove    = cfm_assets_url. 'img/remove.png';
		$required  = $this->required( $readonly );
		$output        = '';
		$output     .= sprintf( '<fieldset class="cfm-el %1s %2s %3s">', $this->template(), $this->name(), $this->css() );
		$output    .= $this->label( $readonly );
		ob_start(); ?>

		<div class="cfm-fields">

			<?php if ( isset( $this->characteristics['multiple'] ) ) { ?>
				<table>
					<thead>
						<tr>
							<?php
							$num_columns = count( $this->characteristics['columns'] );
							foreach ( $this->characteristics['columns'] as $column ) { ?>
								<th><?php echo $column; ?></th>
							<?php } ?>
							<th>
								<?php _e( 'Actions', 'edd_cfm' ); ?>
							</th>
						</tr>

					</thead>
					<tbody>
						<?php
						$row_count = count( $value ) > 0 ? count( $value ) - 1 : 0;
						if ( $row_count > 0 ) {
							for ( $row = 0; $row <= $row_count; $row++ ) { ?>
								<tr data-key="<?php echo $row; ?>">
									<?php for ( $count = 0; $count < $num_columns; $count++ ) { ?>
										<td class="cfm-repeat-field">
											<input type="text" name="<?php echo $this->name() . '[' . $row . '][' . $count . ']'; ?>" value="<?php echo esc_attr( $value[ $row ][ $count ] ); ?>" size="<?php echo esc_attr( $this->size() ); ?>" data-required="<?php echo $required; ?>" data-type="text"<?php $this->required_html5( $readonly ); ?> />
										</td>
									<?php } ?>
									<td class="cfm-repeat-field">
										<img class="cfm-clone-field" alt="<?php esc_attr_e( 'Add another', 'edd_cfm' ); ?>" title="<?php esc_attr_e( 'Add another', 'edd_cfm' ); ?>" src="<?php echo $add; ?>">
										<img class="cfm-remove-field" alt="<?php esc_attr_e( 'Remove this choice', 'edd_cfm' ); ?>" title="<?php esc_attr_e( 'Remove this choice', 'edd_cfm' ); ?>" src="<?php echo $remove; ?>">
									</td>
								</tr>
								<?php
							}
						} else { ?>
							<tr data-key="<?php echo $row_count; ?>">
								<?php for ( $count = 0; $count < $num_columns; $count++ ) { ?>
									<td class="cfm-repeat-field">
										<input type="text" name="<?php echo $this->name() . '[0][' . $count . ']'; ?>" size="<?php echo esc_attr( $this->size() ) ?>"  value="<?php echo $value[0][ $count ]; ?>" data-required="<?php echo $required; ?>" data-type="text"<?php $this->required_html5( $readonly ); ?> />
									</td>
								<?php } ?>
								<td class="cfm-repeat-field">
									<img class="cfm-clone-field" alt="<?php esc_attr_e( 'Add another', 'edd_cfm' ); ?>" title="<?php esc_attr_e( 'Add another', 'edd_cfm' ); ?>" src="<?php echo $add; ?>">
									<img class="cfm-remove-field" alt="<?php esc_attr_e( 'Remove this choice', 'edd_cfm' ); ?>" title="<?php esc_attr_e( 'Remove this choice', 'edd_cfm' ); ?>" src="<?php echo $remove; ?>">
								</td>
							</tr>

						<?php } ?>

					</tbody>
				</table>
			<?php } else { ?>
				<table>
					<?php
					if ( $value && count( $value ) > 1 ) {
						foreach ( $value as $item ) { ?>
						 <tr>
							 <td class="cfm-repeat-field">
								 <input id="cfm-<?php echo $this->name(); ?>" type="text" data-required="<?php echo $required; ?>" data-type="text"<?php $this->required_html5( $readonly ); ?> name="<?php echo esc_attr( $this->name() ); ?>[]" placeholder="<?php echo esc_attr( $this->placeholder() ); ?>" value="<?php echo esc_attr( $item ) ?>" size="<?php echo esc_attr( $this->size() ) ?>" />
							 </td>
							 <td class="cfm-repeat-field">
								 <img style="cursor:pointer; margin:0 3px;" alt="add another choice" title="add another choice" class="cfm-clone-field" src="<?php echo $add; ?>">
								 <img style="cursor:pointer;" class="cfm-remove-field" alt="remove this choice" title="remove this choice" src="<?php echo $remove; ?>">
							 </td>
						 </tr>
								<?php
						} //endforeach
					} else { ?>
							 <tr>
								 <td class="cfm-repeat-field">
									 <input id="cfm-<?php echo $this->name(); ?>" type="text" data-required="<?php echo $required; ?>" data-type="text"<?php $this->required_html5( $readonly ); ?> name="<?php echo esc_attr( $this->name() ); ?>[]" placeholder="<?php echo esc_attr( $this->placeholder() ); ?>" value="<?php echo esc_attr( $this->characteristics['default'] ) ?>" size="<?php echo esc_attr( $this->size() ); ?>" />
								 </td>
								 <td class="cfm-repeat-field">
									 <img style="cursor:pointer; margin:0 3px;" alt="add another choice" title="<?php _e( 'add another choice', 'edd_cfm' ); ?>" class="cfm-clone-field" src="<?php echo $add; ?>">
									 <img style="cursor:pointer;" class="cfm-remove-field" alt="remove this choice" title="<?php _e( 'remove this choice', 'edd_cfm' ); ?>" src="<?php echo $remove; ?>">
								 </td>
							 </tr>
					<?php } ?>
				</table>
		<?php } ?>
		</div>
		<?php
		$output .= ob_get_clean();
		$output .= '</fieldset>';
		return $output;
	}

	/** Returns the HTML to render a field for the formbuilder */
	public function render_formbuilder_field( $index = -2, $insert = false ) {
		$removable    = $this->can_remove_from_formbuilder();
		$tpl                = '%s[%d][%s]';
		$enable_column_name = sprintf( '%s[%d][multiple]', 'cfm_input', $index );
		$column_names       = sprintf( '%s[%d][columns]', 'cfm_input', $index );
		$has_column         = isset( $this->characteristics['columns'] ) &&  count( $this->characteristics['columns'] ) > 1 ? true : false;
		$placeholder_name   = sprintf( $tpl, 'cfm_input', $index, 'placeholder' );
		$default_name       = sprintf( $tpl, 'cfm_input', $index, 'default' );
		$size_name          = sprintf( $tpl, 'cfm_input', $index, 'size' );
		$placeholder_value  = esc_attr( $this->placeholder() );
		$default_value      = esc_attr( $this->characteristics['default'] );
		$size_value         = esc_attr( $this->size() );
		$add    = cfm_assets_url .'img/add.png';
		$remove = cfm_assets_url. 'img/remove.png';

		ob_start(); ?>
		<li class="custom-field custom_repeater">
			<?php $this->legend( $this->title(), $this->get_label(), $removable ); ?>
			<?php CFM_Formbuilder_Templates::hidden_field( "[$index][template]", $this->template() ); ?>

			<?php CFM_Formbuilder_Templates::field_div( $index, $this->name(), $this->characteristics, $insert ); ?>
				<?php CFM_Formbuilder_Templates::public_radio( $index, $this->characteristics, $this->form_name, true ); ?>
				<?php CFM_Formbuilder_Templates::standard( $index, $this ); ?>

				<div class="cfm-form-rows">
					<label><?php _e( 'Multiple Column', 'edd_cfm' ); ?></label>

					<div class="cfm-form-sub-fields">
						<label><input type="checkbox" class="multicolumn" name="<?php echo $enable_column_name ?>"<?php echo $has_column ? ' checked="checked"' : ''; ?> value="true"> Enable Multi Column</label>
					</div>
				</div>

				<div class="cfm-form-rows<?php echo $has_column ? ' cfm-hide' : ''; ?>">
					<label><?php _e( 'Placeholder text', 'edd_cfm' ); ?></label>
					<input type="text" class="smallipopInput" name="<?php echo $placeholder_name; ?>" title="text for HTML5 placeholder attribute" value="<?php echo $placeholder_value; ?>" />
				</div>

				<div class="cfm-form-rows<?php echo $has_column ? ' cfm-hide' : ''; ?>">
					<label><?php _e( 'Default value', 'edd_cfm' ); ?></label>
					<input type="text" class="smallipopInput" name="<?php echo $default_name; ?>" title="the default value this field will have" value="<?php echo $default_value; ?>" />
				</div>

				<div class="cfm-form-rows">
					<label><?php _e( 'Size', 'edd_cfm' ); ?></label>
					<input type="text" class="smallipopInput" name="<?php echo $size_name; ?>" title="Size of this input field" value="<?php echo $size_value; ?>" />
				</div>

				<div class="cfm-form-rows column-names<?php echo $has_column ? '' : ' cfm-hide'; ?>">
					<label><?php _e( 'Columns', 'edd_cfm' ); ?></label>

					<div class="cfm-form-sub-fields">
					<?php

					if ( $this->characteristics['columns'] > 0 ) {
						foreach ( $this->characteristics['columns'] as $key => $value ) { ?>
							<div>
								<input type="text" name="<?php echo $column_names; ?>[]" value="<?php echo $value; ?>">
								<img style="cursor:pointer; margin:0 3px;" alt="add another choice" title="add another choice" class="cfm-clone-field" src="<?php echo $add; ?>">
								<img style="cursor:pointer;" class="cfm-remove-field" alt="remove this choice" title="remove this choice" src="<?php echo $remove; ?>">
							</div>
							<?php
						}
					} else { ?>
						<div>
							<input type="text" name="<?php echo $column_names; ?>[]" value="">
							   <img style="cursor:pointer; margin:0 3px;" alt="add another choice" title="add another choice" class="cfm-clone-field" src="<?php echo $add; ?>">
							   <img style="cursor:pointer;" class="cfm-remove-field" alt="remove this choice" title="remove this choice" src="<?php echo $remove; ?>">
						</div>
					<?php
					} ?>
					</div>
				</div>
			</div>
		</li>

		<?php
		return ob_get_clean();
	}

	public function display_field( $user_id = -2, $single = false ) {
		if ( $user_id === -2 ) {
			$user_id = get_current_user_id();
		}
		$user_id   = apply_filters( 'cfm_display_' . $this->template() . '_field_user_id', $user_id, $this->id );
		$value     = $this->get_field_value_frontend( $this->save_id, $user_id );
		ob_start(); ?>

			<?php if ( $single ) { ?>
			<table class="cfm-display-field-table">
			<?php } ?>

				<tr class="cfm-display-field-row <?php echo $this->template(); ?>" id="<?php echo $this->name(); ?>">
					<td class="cfm-display-field-label"><?php echo $this->get_label(); ?></td>
					<td class="cfm-display-field-values">
						<?php
						echo '';
						?>
					</td>
				</tr>
			<?php if ( $single ) { ?>
			</table>
			<?php } ?>
		<?php
		return ob_get_clean();
	}

	public function formatted_data( $user_id = -2 ) {
		if ( $user_id === -2 ) {
			$user_id = get_current_user_id();
		}

		$user_id   = apply_filters( 'cfm_fomatted_' . $this->template() . '_field_user_id', $user_id, $this->id );
		$values     = $this->get_field_value_frontend( $this->save_id, $user_id );
		$output    = '';
		return $output;
	}

	public function validate( $values = array(), $save_id = -2, $user_id = -2 ) {
		$name = $this->name();
		$return_value = false;
		if ( !empty( $values[ $name ] ) && $this->required() ) {
			if ( !empty( $this->characteristics['multiple'] ) ) {
				if ( is_array( $values[ $name ] ) ){
					foreach( $values[ $name ] as $key => $index ){
						if ( !empty( $index ) && is_array( $index ) ){
							foreach( $index as $column => $value ){
								if ( empty( $values[ $name ][ $key ][ $column ] ) ){
									$return_value = __( 'Please fill out this field.', 'edd_cfm' );
									break;
								}
							}
						} else {
							$return_value = __( 'Please fill out this field.', 'edd_cfm' );
							break;
						}
					}
				} else {
					$return_value = __( 'Please fill out this field.', 'edd_cfm' );
				}
			} else {
				if ( is_array( $values[ $name ] ) ){
					foreach( $values[ $name ] as $key => $value ){
						if ( empty( $values[ $name ][ $key ] ) ){
							$return_value = __( 'Please fill out this field.', 'edd_cfm' );
							break;
						}
					}
				} else {
					$return_value = __( 'Please fill out this field.', 'edd_cfm' );
				}
			}
		} else {
			// if required but isn't present
			if ( $this->required() ) {
				$return_value = __( 'Please fill out this field.', 'edd_cfm' );
			}
		}
		return apply_filters( 'cfm_validate_' . $this->template() . '_field', $return_value, $values, $name, $save_id, $user_id );
	}

	public function sanitize( $values = array(), $save_id = -2, $user_id = -2 ) {
		$name = $this->name();
		if ( !empty( $values[ $name ] ) ) {
			if ( !empty( $this->characteristics['multiple'] )  ){
				if ( is_array( $values[ $name ] ) ){
					foreach( $values[ $name ] as $key => $index ){
						if ( !empty( $index ) && is_array( $index ) ){
							foreach( $index as $column => $value ){
								$values[ $name ][ $key ][ $column ] = sanitize_text_field( trim( $values[ $name ][ $key ][ $column ]  ) );
							}
						}
					}
				}
			} else {
				if ( is_array( $values[ $name ] ) ){
					foreach( $values[ $name ] as $key => $value ){
						$values[ $name ][ $key ] = sanitize_text_field( trim( $values[ $name ][ $key ] ) );
					}
				}
			}
		}
		return apply_filters( 'cfm_sanitize_' . $this->template() . '_field', $values, $name, $save_id, $user_id );
	}
}
