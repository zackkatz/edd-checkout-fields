<?php
class CFM_Text_Field extends CFM_Field {

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
		'template'   => 'text',
		'title'       => 'Text',
		'export'     => true,
	);

	/** @var array Characteristics are things that can change from field to field of the same field type. Like the placeholder between two text fields. Stored in db. */
	public $characteristics = array(
		'name'        => '',
		'template'   => 'text',
		'required'    => false,
		'label'       => '',
		'css'         => '',
		'default'     => '',
		'size'        => '',
		'help'        => '',
		'placeholder' => '',
		'meta_type'   => 'payment', // 'payment' or 'user' here if is_meta()
		'public'          => true, // denotes whether a field shows in the admin only
		'show_in_exports' => true, // denotes whether a field is in the CSV exports
	);

	public function set_title() {
		$title = _x( 'Text', 'CFM Field title translation', 'edd_cfm' );
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

		$user_id   = apply_filters( 'cfm_render_text_field_user_id_admin', $user_id, $this->id );
		$readonly  = apply_filters( 'cfm_render_text_field_readonly_admin', $readonly, $user_id, $this->id );
		$value     = $this->get_field_value_admin( $this->save_id, $user_id, $readonly );

		$output        = '';
		$output     .= sprintf( '<fieldset class="cfm-el %1s %2s %3s">', $this->template(), $this->name(), $this->css() );
		$output    .= $this->label( $readonly );
		ob_start(); ?>
		<div class="cfm-fields">
		   <input class="textfield<?php echo $this->required_class( $readonly ); ?>" id="<?php echo $this->name(); ?>" type="text" data-required="false" data-type="text" name="<?php echo esc_attr( $this->name() ); ?>" placeholder="<?php echo esc_attr( $this->placeholder() ); ?>" value="<?php echo esc_attr( $value ) ?>" size="<?php echo esc_attr( $this->size() ); ?>" <?php echo $readonly ? 'disabled' : ''; ?> />
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

		$user_id   = apply_filters( 'cfm_render_text_field_user_id_frontend', $user_id, $this->id );
		$readonly  = apply_filters( 'cfm_render_text_field_readonly_frontend', $readonly, $user_id, $this->id );
		$value     = $this->get_field_value_frontend( $this->save_id, $user_id, $readonly );
		$required  = $this->required( $readonly );

		$output        = '';
		$output     .= sprintf( '<fieldset class="cfm-el %1s %2s %3s">', $this->template(), $this->name(), $this->css() );
		$output    .= $this->label( $readonly );
		ob_start(); ?>
		<div class="cfm-fields">
		   <input class="textfield<?php echo $this->required_class( $readonly ); ?>" id="<?php echo $this->name(); ?>" type="text" data-required="<?php echo $required; ?>" data-type="text"<?php $this->required_html5( $readonly ); ?> name="<?php echo esc_attr( $this->name() ); ?>" placeholder="<?php echo esc_attr( $this->placeholder() ); ?>" value="<?php echo esc_attr( $value ) ?>" size="<?php echo esc_attr( $this->size() ); ?>" <?php echo $readonly ? 'disabled' : ''; ?> />
		</div>
		<?php
		$output .= ob_get_clean();
		$output .= '</fieldset>';
		return $output;
	}

	/** Returns the HTML to render a field for the formbuilder */
	public function render_formbuilder_field( $index = -2, $insert = false ) {
		$removable = $this->can_remove_from_formbuilder();
		ob_start(); ?>
		<li class="custom-field text_field">
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
}
