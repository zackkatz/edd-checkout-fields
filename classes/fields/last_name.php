<?php
class CFM_Last_Name_Field extends CFM_Field {

	/** @var bool For 3rd parameter of get_post/user_meta */
	public $single = true;

	/** @var string Version of field */
	public $version = '1.0.0';

	/** @var array Supports are things that are the same for all fields of a field type. Like whether or not a field type supports jQuery Phoenix. Stored in obj, not db. */
	public $supports = array(
		'multiple'    => false,
		'is_meta'     => false,  // in object as public (bool) $meta;
		'forms'       => array(
			'checkout'     => true
		),
		'position'    => 'custom',
		'permissions' => array(
			'can_remove_from_formbuilder' => true,
			'can_change_meta_key'         => false,
			'can_add_to_formbuilder'      => true
		),
		'template'  => 'last_name',
		'title'     => 'Last Name',
	);

	/** @var array Characteristics are things that can change from field to field of the same field type. Like the placeholder between two email fields. Stored in db. */
	public $characteristics = array(
		'name'             => 'last_name',
		'template'         => 'last_name',
		'public'           => true,
		'required'         => true,
		'label'            => '',
		'css'             => '',
		'show_placeholder' => false,
		'default'          => false,
		'size'             => '40',
		'public'          => "public", // denotes whether a field shows in the admin only
		'show_in_exports' => "noexport", // denotes whether a field is in the CSV exports
	);

	public function extending_constructor() {
		add_filter( 'cfm_templates_to_exclude_render_checkout_form_admin', array( $this, 'conditional_render' ),10, 2 );
		add_filter( 'cfm_templates_to_exclude_validate_checkout_form_frontend', array( $this, 'exclude' ),10, 2 );
		add_filter( 'cfm_templates_to_exclude_save_checkout_form_frontend', array( $this, 'exclude' ),10, 2 );

		add_filter( 'cfm_templates_to_exclude_render_checkout_form_admin', array( $this, 'exclude' ),10, 2 );
		add_filter( 'cfm_templates_to_exclude_validate_checkout_form_admin', array( $this, 'exclude' ),10, 2 );
		add_filter( 'cfm_templates_to_exclude_save_checkout_form_admin', array( $this, 'exclude' ),10, 2 );
	}

	public function exclude( $templates, $profile ) {
		array_push( $templates, $this->template() );
		return $templates;
	}

	public function conditional_render( $templates, $profile ) {
		if ( $profile !== true ){

		} else {
			array_push( $templates, $this->template() );
		}
		return $templates;
	}

	public function set_title() {
		$title = _x( 'Last Name', 'CFM Field title translation', 'edd_cfm' );
		$title = apply_filters( 'cfm_' . $this->name() . '_field_title', $title );
		$this->supports['title'] = $title;
	}

	/** Returns the First_Name to render a field in admin */
	public function render_field_admin( $user_id = -2, $profile = -2 ) {
		return ''; // EDD does all rendering on the amdin
	}

	/** Returns the First_Name to render a field in frontend */
	public function render_field_frontend( $user_id = -2, $profile = -2 ) {
		if ( $user_id === -2 ) {
			$user_id = get_current_user_id();
		}

		if ( $profile === true ) {
			return '';
		}
		global $current_user;

		$value     = is_user_logged_in() ? $current_user->user_lastname : '';
		$required  = $this->required();
		$output    = '';
		$output    .= '<p id="edd-last-name-wrap">';
		$output    .= $this->label( ! (bool) $profile );
		ob_start(); ?>
		<input name="<?php echo esc_attr( $this->name() ); ?>" id="<?php echo esc_attr( $this->name() ); ?>" class="text edd-input <?php echo $this->required_class(); ?>" type="text" data-required="<?php echo $required; ?>" data-type="text"<?php $this->required_html5(); ?> placeholder="<?php echo esc_attr( $this->placeholder() ); ?>" value="<?php echo esc_attr( $value ) ?>" size="<?php echo esc_attr( $this->size() ) ?>" />
		<?php
		$output .= ob_get_clean();
		$output .= '</p>';
		return $output;
	}

	/** Returns the HTML to render a field for the formbuilder */
	public function render_formbuilder_field( $index = -2, $insert = false ) {
		$removable = $this->can_remove_from_formbuilder();
		ob_start(); ?>
		<li class="last_name">
			<?php $this->legend( $this->title(), $this->get_label(), $removable ); ?>
			<?php CFM_Formbuilder_Templates::hidden_field( "[$index][template]", $this->template() ); ?>

			<?php CFM_Formbuilder_Templates::field_div( $index, $this->name(), $this->characteristics, $insert ); ?>
				<?php CFM_Formbuilder_Templates::public_radio( $index, $this->characteristics, "public" ); ?>
				<?php CFM_Formbuilder_Templates::export_radio( $index, $this->characteristics, "noexport" ); ?>
				<?php CFM_Formbuilder_Templates::standard( $index, $this ); ?>
				<?php CFM_Formbuilder_Templates::css( $index, $this->characteristics ); ?>
				<?php CFM_Formbuilder_Templates::common_text( $index, $this->characteristics ); ?>
			</div>
		</li>
		<?php
		return ob_get_clean();
	}

	public function validate( $values = array(), $payment_id = -2, $user_id = -2 ) {
		// Do nothing. EDD takes care of validation + santization + saving this field
	}

	public function sanitize( $values = array(), $payment_id = -2, $user_id = -2 ) {
		return $values; // Do nothing. EDD takes care of validation + santization + saving this field
	}
}
