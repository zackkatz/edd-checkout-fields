<?php
class CFM_HTML_Field extends CFM_Field {

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
		'template'   => 'html',
		'title'       => 'HTML',
		'show_on_receipt' => false,
	);

	/** @var array Characteristics are things that can change from field to field of the same field type. Like the placeholder between two email fields. Stored in db. */
	public $characteristics = array(
		'name'            => '',
		'template'        => 'html',
		'required'        => false,
		'label'           => '',
		'html'            => '',
		'css'             => '',
		'meta_type'       => 'payment', // 'payment' or 'user' here if is_meta()
		'public'          => "public", // denotes whether a field shows in the admin only
		'show_in_exports' => "noexport", // denotes whether a field is in the CSV exports
	);


	public function set_title() {
		$title = _x( 'HTML', 'CFM Field title translation', 'edd_cfm' );
		$title = apply_filters( 'cfm_' . $this->name() . '_field_title', $title );
		$this->supports['title'] = $title;
	}

	public function extending_constructor( ) {
		add_filter( 'cfm_templates_to_exclude_sanitize_checkout_form_admin', array( $this, 'exclude_field' ), 10, 1  );
		add_filter( 'cfm_templates_to_exclude_validate_checkout_form_admin', array( $this, 'exclude_field' ), 10, 1  );
		add_filter( 'cfm_templates_to_exclude_save_checkout_form_admin', array( $this, 'exclude_field' ), 10, 1  );

		add_filter( 'cfm_templates_to_exclude_sanitize_checkout_form_frontend', array( $this, 'exclude_field' ), 10, 1  );
		add_filter( 'cfm_templates_to_exclude_validate_checkout_form_frontend', array( $this, 'exclude_field' ), 10, 1  );
		add_filter( 'cfm_templates_to_exclude_save_checkout_form_frontend', array( $this, 'exclude_field' ), 10, 1  );
	}

	public function exclude_field( $fields ) {
		array_push( $fields, 'html' );
		return $fields;
	}

	/** Returns the HTML to render a field in admin */
	public function render_field_admin( $user_id = -2, $profile = -2 ) {
		if ( $user_id === -2 ) {
			$user_id = get_current_user_id();
		}

		$output  = '';
		$output .= sprintf( '<p class="cfm-el %1s %2s %3s">', esc_attr( $this->template() ), esc_attr( $this->name() ), esc_attr( $this->css() ) );
		$output .= $this->label( false );
		ob_start(); ?>
		<?php echo do_shortcode( $this->characteristics['html'] ); ?>
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

		$output  = '';
		$output .= sprintf( '<p class="cfm-el %1s %2s %3s">', esc_attr( $this->template() ), esc_attr( $this->name() ), esc_attr( $this->css() ) );
		$output .= $this->label( ! (bool) $profile );
		ob_start(); ?>
		<?php echo do_shortcode( $this->characteristics['html'] ); ?>
		<?php
		$output .= ob_get_clean();
		$output .= '</p>';
		return $output;
	}

	/** Returns the HTML to render a field for the formbuilder */
	public function render_formbuilder_field( $index = -2, $insert = false ) {
		$removable    = $this->can_remove_from_formbuilder();
		$title_name   = sprintf( '%s[%d][label]', 'cfm_input', $index );
		$html_name    = sprintf( '%s[%d][html]', 'cfm_input', $index );
		$title_value  = esc_attr( $this->get_label() );
		$html_value   = esc_attr( $this->characteristics['html'] );
		$name         = $this->name() ? $this->name() : 'html_' . time();
		ob_start(); ?>
		<li class="html">
			<?php $this->legend( $this->title(), $this->get_label(), $removable ); ?>
			<?php CFM_Formbuilder_Templates::field_div( $index, $this->name(), $this->characteristics, $insert ); ?>
			<?php CFM_Formbuilder_Templates::public_radio( $index, $this->characteristics ); ?>
			<?php CFM_Formbuilder_Templates::export_radio( $index, $this->characteristics, "noexport" ); ?>
			<?php CFM_Formbuilder_Templates::meta_type_radio( $index, $this->characteristics, "payment" ); ?>
			<?php CFM_Formbuilder_Templates::hidden_field( "[$index][template]", $this->template() ); ?>
			<?php CFM_Formbuilder_Templates::hidden_field( "[$index][name]", $name ); ?>
			<?php CFM_Formbuilder_Templates::css( $index, $this->characteristics ); ?>
				<div class="cfm-form-rows">
					<label><?php _e( 'Title', 'edd_cfm' ); ?></label>
					<input type="text" class="smallipopInput" title="Title of the section" name="<?php echo $title_name; ?>" value="<?php echo esc_attr( $title_value ); ?>" />
				</div>

				<div class="cfm-form-rows">
					<label><?php _e( 'HTML', 'edd_cfm' ); ?></label>
					<textarea class="smallipopInput" title="Paste your HTML code & WordPress shortcodes here" name="<?php echo $html_name; ?>" rows="10"><?php echo esc_html( $html_value ); ?></textarea>
				</div>
			</div>
		</li>
		<?php
		return ob_get_clean();
	}

	public function validate( $values = array(), $payment_id = -2, $user_id = -2 ) {
		// Nothing to validate
	}

	public function sanitize( $values = array(), $payment_id = -2, $user_id = -2 ) {
		return $values; // Nothing to sanitize
	}
}
