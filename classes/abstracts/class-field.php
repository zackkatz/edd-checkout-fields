<?php
class CFM_Field {

	/** @var string The field ID. */
	public $id = null;

	/** @var unknown Value of the field */
	public $value = null;

	/** @var int The form id the field appears on. */
	public $form = null;

	/** @var string The form's name. */
	public $form_name = null;

	/** @var int The id of the object the field value is saved to. This is the payment ID. */
	public $payment_id = null;

	/** @var int The id of the object the field value is saved to. This is the user ID. */
	public $user_id = null;

	/** @var bool True for post/usermeta. False for inherit. Use true if you want to save a field somewhere custom, and then hook into save_field */
	public $meta = true;

	/** @var bool For 3rd parameter of get_post/user_meta */
	public $single = false;

	/** @var array Supports are things that are the same for all fields of a field type. Like whether or not a field type supports jQuery Phoenix. Stored in obj, not db. */
	public $supports = array(
		'multiple'    => true,
		'forms'       => array( // forms this field supports
			'checkout'     => true,
		),
		'position'    => 'custom', // where the button to add this appears on the formbuilder. Top = "custom", bottom = "extension". Extensions should register on extension
		'permissions' => array(
			'can_remove_from_formbuilder' => true,
			'can_change_meta_key'         => true,
			'can_add_to_formbuilder'      => true,
		),
		'template'    => 'text',
		'title'       => 'Text'
	);

	/** @var array Characteristics are things that can change from field to field of the same field type. Like the placeholder between two text fields. Stored in db. */
	public $characteristics = array(
		'name'        => '',
		'template'    => 'text',
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

	/** From here down, parameters for functions as they relate to the field object are:
	 Function    | Object   | Explanation
	 $field      | $name    | Usually this is the same as the meta_key for saving. This is the name of a field. Unique to each field.
	 $form       | $form    | $form is the int id of the form post that the field appears on
	 $payment_id | $type    | $type is the type of form the field is being used on (post, user, custom)
	 $user_id    | $user_id | Corresponds to the ID of the object the field's value is saved to. See $payment_id's parameter comment
	 */
	public function __construct( $field = '', $form = 'notset', $payment_id = -2, $user_id = -2 ) {
		if ( is_array( $field ) ) {
			$this->id                 = isset( $field['name'] ) ? $field['name'] : $field;
			$this->characteristics    = $field;
			if ( $form != 'notset' ) {
				$this->form = $form;
				$this->form_name = get_post_meta( $form, 'cfm-form-name', true );
			}
			$this->meta = $this->is_meta();
			$this->payment_id = $payment_id;
			$this->user_id = $user_id;
			$this->value = $this->get_field_value();
		} else if ( is_string( $field ) && strlen( $field ) > 0 ) {
			$this->id   = $field;
			if ( $form !== 'notset' ) {
				$this->form = $form;
				$this->form_name = get_post_meta( $form, 'cfm-form-name', true );
				$this->characteristics = $this->pull_characteristics( $field, $form );
				$this->meta = $this->is_meta();
			}

			$this->payment_id = $payment_id;
			$this->user_id = $user_id;
			$this->value = $this->get_field_value();
		} else {
			$this->id   = $field;
			if ( $form != 'notset' ) {
				$this->form = $form;
				$this->form_name = get_post_meta( $form, 'cfm-form-name', true );
			}
			$this->payment_id = $payment_id;
			$this->user_id = $user_id;
			$this->value = $this->get_field_value();
		}
		$this->set_title();
		$this->extending_constructor();
	}

	public function get_id() {
		return $this->id;
	}

	public function set_id( $value ) {
		$this->id = $value;
	}

	/** get_value pulls the value from the obj. It does not touch the db */
	public function get_value() {
		return $this->get_field_value();
	}

	/** set_value sets the value of the object. It does not save the value to the db */
	public function set_value( $value ) {
		$this->value = $value;
	}

	/** Aliases save_field */
	public function save_value( $value, $field, $form, $id ) {
		$this->save_field( $value, $field, $form, $id );
	}

	public function get_form() {
		return $this->form;
	}

	public function set_form( $form ) {
		$this->form = $form;
	}

	public function get_payment_id() {
		return $this->payment_id;
	}

	public function set_payment_id( $payment_id ) {
		$this->payment_id = $payment_id;
	}

	public function get_user_id() {
		return $this->user_id;
	}

	public function set_user_id( $user_id ) {
		$this->user_id = $user_id;
	}

	public function set_meta( $meta ) {
		$this->meta = $meta;
	}

	public function get_supports() {
		return $this->supports;
	}

	public function set_supports( $supports ) {
		$this->supports = $supports;
	}

	public function add_supports( $supports ) {
		$this->supports = array_merge( $this->supports, $supports );
	}

	/** Gets characteristics from obj. Does not touch the db */
	public function get_characteristics() {
		return $this->characteristics;
	}

	/** Sets obj characteristics. Does not touch the db */
	public function set_characteristics( $characteristics ) {
		$this->characteristics = $characteristics;
	}

	/** Pulls the characteristics from the db, and sets the object value equal to that. Different than get_characteristics */
	public function pull_characteristics( $id = false, $form = false ) {
		if ( $id && $form ) {
			$this->id = $id;
			$this->form = $form;
		}
		$value;
		$fields = get_post_meta( $form, 'cfm-form', true );
		if ( !$fields ) {
			$fields = array();
		}
		$found = false;
		foreach ( $fields as $field ) {
			if ( isset( $field['name'] ) && $field['name'] == $this->id ) {
				$value = $field;
				$found = true;
			}
		}

		if ( !$found ) {
			$value = $this->characteristics;
		}

		$value = apply_filters( 'cfm_pull_field_characteristics', $value, $this );
		$this->characteristics = $value;
		return $value;
	}

	public function save_characteristics( $id = false, $form = false, $characteristics = array() ) {
		if ( $id && $form ) {
			$this->id = $id;
			$this->form = $form;
		}
		$fields = get_post_meta( $this->form, 'cfm-form', true );
		foreach ( $fields as $field ) {
			if ( $field['name'] == $this->id ) {
				$field = $characteristics;
			}
		}
		update_post_meta( $this->form, 'cfm-form', $fields );
		$this->characteristics = $characteristics;
	}

	/** Returns the HTML to render a field */
	public function render_field( $current_user_id = -2, $profile = false ) {
		$output = '';
		if ( cfm_is_admin() ) {
			$output .= $this->render_field_admin( $current_user_id, $profile );
		} else {
			$output .= $this->render_field_frontend( $current_user_id, $profile );
		}

		return $output;
	}

	/** Returns the HTML to render a field in admin */
	public function render_field_admin( $current_user_id = -2, $profile = -2 ) {
		// defined in the extending fields
		return '';
	}

	/** Returns the HTML to render a field in frontend */
	public function render_field_frontend( $current_user_id = -2, $profile = -2 ) {
		// defined in the extending fields
		return '';
	}

	public function export_data( $payment_id = -2, $user_id = -2 ) {
		if ( $payment_id === -2 ){
			$payment_id = $this->payment_id;
		}

		if ( $user_id === -2 ){
			if ( $payment_id !== -2 ){
				$payment = new EDD_Payment( $payment_id );
				$user_id = $payment->__get('user_id');
			} else {
				$user_id = $this->user_id;
			}
		}

		$value = $this->get_field_value_frontend( $payment_id, $user_id );
		if ( ! empty( $value ) && is_array( $value ) ){
			$value = implode( ", ", $value );
		}
		return $value;
	}

	/** Saves field by extracting value from array of values (for all fields of a form) */
	public function save_field_values( $payment_id = -2, $user_id = -2, $values = array(), $current_user_id = -2 ) {
		if ( $current_user_id === -2 ) {
			$current_user_id = get_current_user_id();
		}

		if ( $payment_id == -2 ) {
			$payment_id = $this->payment_id;
		}

		if ( $user_id == -2 ) {
			$user_id = $this->user_id;
		}

		do_action( 'cfm_save_field_values_before', $payment_id, $user_id, $values, $current_user_id );

		if ( isset( $values[ $this->name() ] ) ) {
			$this->save_field( $payment_id, $user_id, $values[ $this->name() ], $current_user_id );
		} else {
			$this->save_field( $payment_id, $user_id, '', $current_user_id );
		}
		do_action( 'cfm_save_field_values_after', $payment_id, $user_id, $values, $current_user_id );
	}

	/** Saves field */
	public function save_field( $payment_id = -2, $user_id = -2, $value = '', $current_user_id = -2 ) {
		if ( cfm_is_admin() ) {
			$this->save_field_admin( $payment_id, $user_id, $value, $current_user_id );
		} else {
			$this->save_field_frontend( $payment_id, $user_id, $value, $current_user_id );
		}
	}

	/** Saves field in admin */
	public function save_field_admin( $payment_id = -2, $user_id = -2, $value = '', $current_user_id = -2 ) {
		if ( $current_user_id === -2 ) {
			$current_user_id = get_current_user_id();
		}

		if ( $payment_id == -2 ) {
			$payment_id = $this->payment_id;
		}

		if ( $user_id == -2 ) {
			$user_id = $this->user_id;
		}

		do_action( 'cfm_save_field_before_save_admin', $this, $payment_id, $user_id, $value, $current_user_id );
		if ( (bool) $this->meta ) {
			$meta_type = $this->meta_type();
			if ( $meta_type === 'user' ){
				$value = update_user_meta( $user_id, $this->id, $value );
			} else {
				// payment meta
				$value = update_post_meta( $payment_id, $this->id, $value );
			}
		} else {
			$user  = get_userdata( $user_id );
			if ( $user && isset( $this->id ) ) {
				$arr = array();
				$arr['ID'] = $user_id;
				$arr[ $this->id ] = $value;
				wp_update_user( $arr );
			}
		}

		$this->value = $value;
		do_action( 'cfm_save_field_after_save_admin', $this, $payment_id, $user_id, $value, $current_user_id );
	}

	/** Saves field in frontend */
	public function save_field_frontend( $payment_id = -2, $user_id = -2, $value = '', $current_user_id = -2 ) {
		if ( $current_user_id === -2 ) {
			$current_user_id = get_current_user_id();
		}

		if ( $payment_id == -2 ) {
			$payment_id = $this->payment_id;
		}

		if ( $user_id == -2 ) {
			$user_id = $this->user_id;
		}

		do_action( 'cfm_save_field_before_save_frontend', $this, $payment_id, $user_id, $value, $current_user_id );

		if ( (bool) $this->meta ) {
			$meta_type = $this->meta_type();
			if ( $meta_type === 'user' ){
				$value = update_user_meta( $user_id, $this->id, $value );
			} else {
				// payment meta
				$value = update_post_meta( $payment_id, $this->id, $value );
			}
		} else {
			$user  = get_userdata( $user_id );
			if ( $user && isset( $this->id ) ) {
				$arr = array();
				$arr['ID'] = $user_id;
				$arr[ $this->id ] = $value;
				wp_update_user( $arr );
			}
		}

		$this->value = $value;
		do_action( 'cfm_save_field_after_save_frontend', $this, $payment_id, $user_id, $value, $current_user_id );
	}

	/** Gets field value */
	public function get_field_value( $payment_id = -2, $user_id = -2 ) {
		$value;
		if ( cfm_is_admin() ) {
			$value = $this->get_field_value_admin( $payment_id, $user_id  );
		} else {
			$value = $this->get_field_value_frontend( $payment_id, $user_id );
		}
		return $value;
	}

	/** Gets field value for admin */
	public function get_field_value_admin( $payment_id = -2, $user_id = -2 ) {
		if ( $user_id === -2 ) {
			$user_id = get_current_user_id();
		}

		if ( ( $this->is_meta() && $this->meta_type() === 'payment' && $payment_id === -2 ) ||
			 ( $this->is_meta() && $this->meta_type() === 'user' && $user_id === -2 ) ||
			 ( !$this->is_meta() && $user_id === -2 ) ){
			// if the place we are saving to doesn't have a save_id we are on a draft and therefore don't have a value
			// if there's a default lets use that
			if ( isset( $this->characteristics ) && isset( $this->characteristics ) && isset( $this->characteristics['default'] ) ) {
				$value = $this->characteristics['default'];
			}
			$value = apply_filters( 'cfm_get_field_value_early_value_admin', null, $this, $payment_id, $user_id );
			return $value;
		}

		$value = false;

		if ( (bool) $this->meta ) {
				$meta_type = $this->meta_type();
				if ( $meta_type === 'user' ){
					$value = get_user_meta( $user_id, $this->id, $this->single );
				} else {
					// payment meta
					$value = get_post_meta( $payment_id, $this->id, $this->single );
				}
		} else {
			$user  = get_userdata( $user_id );
			if ( $user && isset( $this->id ) ) {
				$param = $this->id;
				$value = $user->$param;
			}
		}
		$value = apply_filters( 'cfm_get_field_value_return_value_admin', $value, $this, $payment_id, $user_id  );
		return $value;
	}

	/** Gets field value for frontend */
	public function get_field_value_frontend( $payment_id = -2, $user_id = -2 ) {
		if ( $user_id === -2 ) {
			$user_id = get_current_user_id();
		}

		if ( ( $this->is_meta() && $this->meta_type() === 'payment' && $payment_id === -2 ) ||
			 ( $this->is_meta() && $this->meta_type() === 'user' && $user_id === -2 ) ||
			 ( !$this->is_meta() && $user_id === -2 ) ){
			// if the place we are saving to doesn't have a save_id we are on a draft and therefore don't have a value
			// if there's a default lets use that
			if ( isset( $this->characteristics ) && isset( $this->characteristics ) && isset( $this->characteristics['default'] ) ) {
				$value = $this->characteristics['default'];
			}
			$value = apply_filters( 'cfm_get_field_value_early_value_frontend', null, $this, $payment_id, $user_id );
			return $value;
		}

		$value = false;

		if ( (bool) $this->meta ) {
				$meta_type = $this->meta_type();
				if ( $meta_type === 'user' ){
					$value = get_user_meta( $user_id, $this->id, $this->single );
				} else {
					// payment meta
					$value = get_post_meta( $payment_id, $this->id, $this->single );
				}
		} else {
			$user  = get_userdata( $user_id );
			if ( $user && isset( $this->id ) ) {
				$param = $this->id;
				$value = $user->$param;
			}
		}
		$value = apply_filters( 'cfm_get_field_value_return_value_frontend', $value, $this, $payment_id, $user_id  );
		return $value;
	}

	/** Returns the HTML to render a field for the formbuilder */
	public function render_formbuilder_field( $index = -2, $insert = false ) {
		// defined in the extending fields
	}


	/** Validates field */
	public function validate( $values = array(), $payment_id = -2, $user_id = -2 ) {
		$name = $this->name();
		if ( !empty( $values[ $name ] ) ) {
			// if the value is set
			// no specific validation
		} else {
			// if the field is required but isn't present
			if ( $this->required() ) {
				edd_set_error( 'invalid_' . $this->id, sprintf( __( 'Please enter a value for %s.', 'edd_cfm' ), $this->get_label() ) );
			}
		}
	}

	/** Sanitizes field value */
	public function sanitize( $values = array(), $payment_id = -2, $user_id = -2 ) {
		$name = $this->name();
		if ( !empty( $values[ $name ] ) ) {
			$values[ $name ] = trim( $values[ $name ] );
			$values[ $name ] = sanitize_text_field( $values[ $name ] );
		}
		return apply_filters( 'cfm_sanitize_' . $this->template() . '_field', $values, $name, $payment_id, $user_id );
	}

	public function required_mark() {
		if ( $this->required() ) {
			return apply_filters( 'cfm_required_mark', '<span class="edd-required-indicator">*</span>' );
		}
	}

	public function required_html5() {
		if ( $this->required() ) {
			return apply_filters( 'cfm_required_html5', ' required="required"' );
		}
	}

	public function required_class() {
		if ( $this->required()) {
			return apply_filters( 'cfm_required_class', ' required' );
		}
	}

	public function get_label( ) {
		return isset( $this->characteristics['label'] ) ?  $this->characteristics['label'] : '';
	}

	public function label( $show_help = -2 ) {
		if ( $show_help === -2 ){
			$show_help = false;
		}
		$show_help = ( bool ) $show_help;
		$name  = $this->name();
		$label = $this->get_label();
		ob_start(); ?>
		<label class="edd-label" for="<?php echo isset( $name ) ? $name : 'cls'; ?>"><?php echo $label . $this->required_mark(); ?></label>
		<?php if ( $show_help && $this->help() ) { ?>
			<span class="edd-description"><?php echo $this->help(); ?></span>
		<?php } ?>
		<?php
		return ob_get_clean();
	}

	public function required() {
		$required = false;

		$required = isset( $this->characteristics['required'] ) ? $this->characteristics['required'] : 'no';
		if ( $required === 'no' ) {
			$required = false;
		}
		$required = (bool) $required;
		$required = apply_filters( 'cfm_' . $this->name() . '_field_required', $required, $this );
		return $required;
	}

	public function help() {
		return isset( $this->characteristics['help'] ) ?  $this->characteristics['help'] : '';
	}

	public function name() {
		return $this->characteristics['name'];
	}

	public function id() {
		return str_replace( '_' , '-', $this->name() );
	}

	public function placeholder() {
		return isset( $this->characteristics['placeholder'] ) ?  $this->characteristics['placeholder'] : '';
	}

	public function size() {
		return isset( $this->characteristics['size'] ) ?  $this->characteristics['size'] : '';
	}

	public function template() {
		return $this->characteristics['template'];
	}

	public function set_title() {
		$title = _x( 'Text', 'CFM Field title translation', 'edd_cfm' );
		$title = apply_filters( 'cfm_' . $this->name() . '_field_title', $title );
		$this->supports['title'] = $title;
	}

	public function title() {
		$title = !empty( $this->supports[ 'title' ] ) ? $this->supports[ 'title' ] : 'Text';
		return $title;
	}

	public function css() {
		return isset( $this->characteristics['css'] ) ?  $this->characteristics['css'] : '';
	}

	public function can_remove_from_formbuilder() {
		return isset( $this->supports['permissions']['can_remove_from_formbuilder'] ) ? $this->supports['permissions']['can_remove_from_formbuilder'] : true;
	}

	public function is_meta() {
		if ( ( isset( $this->supports['is_meta'] ) && (bool) $this->supports['is_meta'] ) || (  ! isset( $this->supports['is_meta'] ) && isset( $this->characteristics['is_meta'] ) && (bool) $this->characteristics['is_meta'] ) ) {
			return true;
		} else {
			return false;
		}
	}

	public function meta_type() {
		if ( ( isset( $this->supports['is_meta'] ) && (bool) $this->supports['is_meta'] ) || (  ! isset( $this->supports['is_meta'] ) && isset( $this->characteristics['is_meta'] ) && (bool) $this->characteristics['is_meta'] ) ) {
			if ( isset( $this->characteristics['meta_type'] ) ){
				if ( $this->characteristics['meta_type'] === 'user' ){
					return 'user';
				} else {
					return 'payment';
				}
			}
		} else {
			return false;
		}
	}

	public function is_public() {
		if ( !empty ( $this->characteristics['public'] ) && $this->characteristics['public'] === "public" ){
			return true;
		} else if ( !empty ( $this->characteristics['public'] ) && $this->characteristics['public'] === "admin" ){
			return false;
		} else {
			return true;
		}
	}

	public function legend( $title = 'Field Type', $label = '', $removable = true ) {
		$legend      = '';
		$title       = $title;
		if ( $title === $label || $label === '' ) {
			$legend = '<strong>' . $title  . '</strong>';
		} else {
			$legend = '<strong>' . $title . '</strong>: '. $label;
		}

?>
		<div class="cfm-legend" title="<?php _e( 'Click and Drag to rearrange', 'edd_cfm' ); ?>">
			<div class="cfm-label"><?php echo $legend; ?></div>
			<div class="cfm-actions">
				<?php if ( $removable ) { ?>
				<a href="#" class="cfm-remove"><?php _e( 'Remove', 'edd_cfm' ); ?></a>
				<?php } ?>
				<a href="#" class="cfm-toggle"><?php _e( 'Toggle', 'edd_cfm' ); ?></a>
			</div>
		</div> <!-- .cfm-legend -->
		<?php
	}

	public function extending_constructor( ) {
		// used by extending fields who need it
	}

	public function can_export(){
		return ( isset( $this->characteristics['show_in_exports'] ) &&  $this->characteristics['show_in_exports'] === 'export' ) ?  true : false;
	}

}
