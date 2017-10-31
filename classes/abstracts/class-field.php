<?php
/**
 * Checkout Fields Manager Abstract Field Object.
 *
 * @package EDD_CFM
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * CFM_Field Class.
 */
class CFM_Field {

	/**
	 * Field ID.
	 *
	 * @var strin
	 */
	public $id = null;

	/**
	 * Value of the field.
	 *
	 * @var bool|mixed
	 */
	public $value = null;

	/**
	 * ID of the form that the field appears on.
	 *
	 * @var int
	 */
	public $form = null;

	/**
	 * Form name.
	 *
	 * @var string
	 */
	public $form_name = null;

	/**
	 * Payment ID.
	 *
	 * @var int
	 */
	public $payment_id = null;

	/**
	 * User ID.
	 *
	 * @var int
	 */
	public $user_id = null;

	/**
	 * True for post/usermeta. False for inherit.
	 *
	 * Use true if you want to save a field somewhere custom, and then hook into save_field.
	 *
	 * @var bool
	 */
	public $meta = true;

	/**
	 * For 3rd parameter of get_post/user_meta
	 *
	 * @var bool
	 */
	public $single = false;

	/**
	 * Supports are things that are the same for all fields of a field type. Like whether or not a field type supports
	 * jQuery Phoenix. Stored in obj, not db.
	 *
	 * @var array
	 */
	public $supports = array(
		'multiple'        => true,
		'forms'           => array( 'checkout' => true, ),
		'position'        => 'custom',
		// where the button to add this appears on the formbuilder. Top = "custom", bottom = "extension". Extensions should register on extension
		'permissions'     => array(
			'can_remove_from_formbuilder' => true,
			'can_change_meta_key'         => true,
			'can_add_to_formbuilder'      => true,
		),
		'template'        => 'text',
		'title'           => 'Text',
		'show_on_receipt' => true,
		// denotes whether a field is shown on the purchase confirmation page
	);

	/**
	 * Characteristics are things that can change from field to field of the same field type. Like the placeholder
	 * between two text fields. Stored in db.
	 *
	 * @var array
	 */
	public $characteristics = array(
		'name'              => '',
		'template'          => 'text',
		'required'          => false,
		'label'             => '',
		'css'               => '',
		'default'           => '',
		'size'              => '',
		'help'              => '',
		'placeholder'       => '',
		'meta_type'         => 'payment', // 'payment' or 'user' here if is_meta()
		'public'            => true, // denotes whether a field shows in the admin only
		'show_in_exports'   => 'export', // denotes whether a field is in the CSV exports
		'conditional_logic' => array(),
	);

	/** From here down, parameters for functions as they relate to the field object are:
	 * Function    | Object   | Explanation
	 * $field      | $name    | Usually this is the same as the meta_key for saving. This is the name of a field.
	 * Unique to each field.
	 * $form       | $form    | $form is the int id of the form post that the field appears on
	 * $payment_id | $type    | $type is the type of form the field is being used on (post, user, custom)
	 * $user_id    | $user_id | Corresponds to the ID of the object the field's value is saved to. See $payment_id's
	 * parameter comment
	 */
	public function __construct( $field = '', $form = 'notset', $payment_id = -2, $user_id = -2 ) {
		if ( is_array( $field ) ) {
			$this->id              = isset( $field['name'] ) ? $field['name'] : $field;
			$this->characteristics = $field;
			if ( $form != 'notset' ) {
				$this->form      = $form;
				$this->form_name = get_post_meta( $form, 'cfm-form-name', true );
			}
			$this->meta       = $this->is_meta();
			$this->payment_id = $payment_id;
			$this->user_id    = $user_id;
			$this->value      = $this->get_field_value();
		} else if ( is_string( $field ) && strlen( $field ) > 0 ) {
			$this->id = $field;
			if ( $form !== 'notset' ) {
				$this->form            = $form;
				$this->form_name       = get_post_meta( $form, 'cfm-form-name', true );
				$this->characteristics = $this->pull_characteristics( $field, $form );
				$this->meta            = $this->is_meta();
			}

			$this->payment_id = $payment_id;
			$this->user_id    = $user_id;
			$this->value      = $this->get_field_value();
		} else {
			$this->id = $field;
			if ( $form != 'notset' ) {
				$this->form      = $form;
				$this->form_name = get_post_meta( $form, 'cfm-form-name', true );
			}
			$this->payment_id = $payment_id;
			$this->user_id    = $user_id;
			$this->value      = $this->get_field_value();
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
			$this->id   = $id;
			$this->form = $form;
		}

		$value  = null;
		$fields = get_post_meta( $form, 'cfm-form', true );

		if ( ! $fields ) {
			$fields = array();
		}

		$found = false;
		foreach ( $fields as $field ) {

			if ( isset( $field['name'] ) && $field['name'] == $this->id ) {
				$value = $field;
				$found = true;
			}

		}

		if ( ! $found ) {
			$value = $this->characteristics;
		}

		$value                 = apply_filters( 'cfm_pull_field_characteristics', $value, $this );
		$this->characteristics = $value;

		return $value;
	}

	public function save_characteristics( $id = false, $form = false, $characteristics = array() ) {
		if ( $id && $form ) {
			$this->id   = $id;
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
		if ( $payment_id === -2 ) {
			$payment_id = $this->payment_id;
		}

		if ( $user_id === -2 ) {
			if ( $payment_id !== -2 ) {
				$payment = new EDD_Payment( $payment_id );
				$user_id = $payment->__get( 'user_id' );
			} else {
				$user_id = $this->user_id;
			}
		}

		$value = $this->get_field_value_frontend( $payment_id, $user_id );
		if ( ! empty( $value ) && is_array( $value ) ) {
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
			if ( $meta_type === 'user' ) {
				$value = update_user_meta( $user_id, $this->id, $value );
			} else {
				// payment meta
				$value = update_post_meta( $payment_id, $this->id, $value );
			}
		} else {
			$user = get_userdata( $user_id );
			if ( $user && isset( $this->id ) ) {
				$arr              = array();
				$arr['ID']        = $user_id;
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
			if ( $meta_type === 'user' ) {
				$value = update_user_meta( $user_id, $this->id, $value );
			} else {
				// payment meta
				$value = update_post_meta( $payment_id, $this->id, $value );
			}
		} else {
			$user = get_userdata( $user_id );
			if ( $user && isset( $this->id ) ) {
				$arr              = array();
				$arr['ID']        = $user_id;
				$arr[ $this->id ] = $value;
				wp_update_user( $arr );
			}
		}

		$this->value = $value;
		do_action( 'cfm_save_field_after_save_frontend', $this, $payment_id, $user_id, $value, $current_user_id );
	}

	/** Gets field value */
	public function get_field_value( $payment_id = -2, $user_id = -2 ) {
		if ( cfm_is_admin() ) {
			$value = $this->get_field_value_admin( $payment_id, $user_id );
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

		if ( ( $this->is_meta() && $this->meta_type() === 'payment' && $payment_id === -2 ) || ( $this->is_meta() && $this->meta_type() === 'user' && $user_id === -2 ) || ( ! $this->is_meta() && $user_id === -2 ) ) {
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
			if ( $meta_type === 'user' ) {
				$value = get_user_meta( $user_id, $this->id, $this->single );
			} else {
				// payment meta
				$value = get_post_meta( $payment_id, $this->id, $this->single );
			}
		} else {
			$user = get_userdata( $user_id );
			if ( $user && isset( $this->id ) ) {
				$param = $this->id;
				$value = $user->$param;
			}
		}
		$value = apply_filters( 'cfm_get_field_value_return_value_admin', $value, $this, $payment_id, $user_id );

		return $value;
	}

	/** Gets field value for frontend */
	public function get_field_value_frontend( $payment_id = -2, $user_id = -2 ) {
		if ( $user_id === -2 || $user_id < 1 ) {
			$user_id = get_current_user_id();
		}

		if ( ( $this->is_meta() && $this->meta_type() === 'payment' && $payment_id === -2 ) || ( $this->is_meta() && $this->meta_type() === 'user' && $user_id === -2 ) || ( ! $this->is_meta() && $user_id === -2 ) ) {
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
			if ( $meta_type === 'user' ) {
				$value = get_user_meta( $user_id, $this->id, $this->single );
			} else {
				// payment meta
				$value = get_post_meta( $payment_id, $this->id, $this->single );
			}
		} else {
			$user = get_userdata( $user_id );
			if ( $user && isset( $this->id ) ) {
				$param = $this->id;
				$value = $user->$param;
			}
		}
		$value = apply_filters( 'cfm_get_field_value_return_value_frontend', $value, $this, $payment_id, $user_id );

		return $value;
	}

	/** Returns the HTML to render a field for the formbuilder */
	public function render_formbuilder_field( $index = -2, $insert = false ) {
		// defined in the extending fields
	}


	/** Validates field */
	public function validate( $values = array(), $payment_id = -2, $user_id = -2 ) {
		$name = $this->name();
		if ( ! empty( $values[ $name ] ) ) {
			// if the value is set
			// no specific validation
		} else {
			// if the field is required but isn't present
			if ( $this->required() && $this->is_field_displayed() )  {
				edd_set_error( 'invalid_' . $this->id, sprintf( __( 'Please enter a value for %s.', 'edd_cfm' ), $this->get_label() ) );
			}
		}
	}

	/** Sanitizes field value */
	public function sanitize( $values = array(), $payment_id = -2, $user_id = -2 ) {
		$name = $this->name();
		if ( ! empty( $values[ $name ] ) ) {
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
		if ( $this->required() ) {
			return apply_filters( 'cfm_required_class', ' required' );
		}
	}

	public function get_label() {
		return isset( $this->characteristics['label'] ) ? $this->characteristics['label'] : '';
	}

	public function label( $show_help = -2 ) {
		if ( $show_help === -2 ) {
			$show_help = false;
		}
		$show_help = ( bool ) $show_help;
		$name      = $this->name();
		$label     = $this->get_label();
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
		return isset( $this->characteristics['help'] ) ? $this->characteristics['help'] : '';
	}

	public function name() {
		return $this->characteristics['name'];
	}

	public function id() {
		return str_replace( '_', '-', $this->name() );
	}

	public function placeholder() {
		return isset( $this->characteristics['placeholder'] ) ? $this->characteristics['placeholder'] : '';
	}

	public function size() {
		return isset( $this->characteristics['size'] ) ? $this->characteristics['size'] : '';
	}

	public function template() {
		return $this->characteristics['template'];
	}

	public function set_title() {
		$title                   = _x( 'Text', 'CFM Field title translation', 'edd_cfm' );
		$title                   = apply_filters( 'cfm_' . $this->name() . '_field_title', $title );
		$this->supports['title'] = $title;
	}

	public function title() {
		$title = ! empty( $this->supports['title'] ) ? $this->supports['title'] : 'Text';

		return $title;
	}

	public function css() {
		return isset( $this->characteristics['css'] ) ? $this->characteristics['css'] : '';
	}

	public function conditional_logic() {
		return isset( $this->characteristics['conditional_logic'] ) ? $this->characteristics['conditional_logic'] : array();
	}

	public function can_remove_from_formbuilder() {
		return isset( $this->supports['permissions']['can_remove_from_formbuilder'] ) ? $this->supports['permissions']['can_remove_from_formbuilder'] : true;
	}

	public function is_meta() {
		if ( ( isset( $this->supports['is_meta'] ) && (bool) $this->supports['is_meta'] ) || ( ! isset( $this->supports['is_meta'] ) && isset( $this->characteristics['is_meta'] ) && (bool) $this->characteristics['is_meta'] ) ) {
			return true;
		} else {
			return false;
		}
	}

	public function meta_type() {
		if ( ( isset( $this->supports['is_meta'] ) && (bool) $this->supports['is_meta'] ) || ( ! isset( $this->supports['is_meta'] ) && isset( $this->characteristics['is_meta'] ) && (bool) $this->characteristics['is_meta'] ) ) {
			if ( isset( $this->characteristics['meta_type'] ) ) {
				if ( $this->characteristics['meta_type'] === 'user' ) {
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
		if ( ! empty ( $this->characteristics['public'] ) && $this->characteristics['public'] === "public" ) {
			return true;
		} else if ( ! empty ( $this->characteristics['public'] ) && $this->characteristics['public'] === "admin" ) {
			return false;
		} else {
			return true;
		}
	}

	public function legend( $title = 'Field Type', $label = '', $removable = true ) {
		$legend = '';
		$title  = $title;
		if ( $title === $label || $label === '' ) {
			$legend = '<strong>' . $title . '</strong>';
		} else {
			$legend = '<strong>' . $title . '</strong>: ' . $label;
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

	public function extending_constructor() {
		// used by extending fields who need it
	}

	public function can_export() {
		return ( isset( $this->characteristics['show_in_exports'] ) && $this->characteristics['show_in_exports'] === 'export' ) ? true : false;
	}

	/**
	 * Generates the markup to apply conditional logic to all fields.
	 *
	 * @access protected
	 * @since  2.2
	 *
	 * @param int $index
	 *
	 * @return string HTML markup.
	 */
	protected function display_conditional_logic_fields( $index ) {
		$toggle_name  = sprintf( '%s[%d][conditional_logic][conditional_logic_enabled]', 'cfm_input', $index );
		$toggle_value = esc_attr( $this->characteristics['conditional_logic']['conditional_logic_enabled'] );
		$action_value = esc_attr( $this->characteristics['conditional_logic']['conditional_logic_action'] );
		$type_value   = esc_attr( $this->characteristics['conditional_logic']['conditional_logic_type'] );
		$rules        = $this->characteristics['conditional_logic']['rules'];
		$checked      = 'on' == $toggle_value ? 'checked = "checked"' : '';
		$is_hidden    = empty( $checked ) ? 'hidden' : '';
		ob_start();
		?>
		<div class="cfm-form-rows">
			<label><?php _e( 'Conditional Logic', 'edd_cfm' ); ?></label>
			<div class="cfm-form-sub-fields">
				<div class="cfm-toggle-conditional-logic">
					<input <?php echo $checked; ?> class="cfm-toggle-conditional-logic-toggle" type="checkbox" name="<?php echo $toggle_name; ?>" id="<?php echo $toggle_name; ?>" />
					<label for="<?php echo $toggle_name; ?>"><?php _e( 'Enable Conditional Logic', 'edd_cfm' ); ?></label>
				</div>

				<div class="cfm-conditional-logic <?php echo $is_hidden; ?>">
					<p>
						<?php
						echo EDD()->html->select( array(
							'options'          => apply_filters( 'edd_cfm_conditional_logic_actions', array(
								'show' => __( 'Show', 'edd_cfm' ),
								'hide' => __( 'Hide', 'edd_cfm' ),
							) ),
							'name'             => 'cfm_input[' . $index . '][conditional_logic][conditional_logic_action]',
							'class'            => 'cfm-conditional-logic-action',
							'selected'         => $action_value,
							'show_option_all'  => false,
							'show_option_none' => false,
						) );

						_e( 'this field if', 'edd_cfm' );

						echo EDD()->html->select( array(
							'options'          => apply_filters( 'edd_cfm_conditional_logic_types', array(
								'any' => __( 'any', 'edd_cfm' ),
								'all' => __( 'all', 'edd_cfm' ),
							) ),
							'name'             => 'cfm_input[' . $index . '][conditional_logic][conditional_logic_type]',
							'class'            => 'cfm-conditional-logic-type',
							'selected'         => $type_value,
							'show_option_all'  => false,
							'show_option_none' => false,
						) );

						_e( 'of the following match:', 'edd_cfm' );
						?>
					</p>

					<div class="cfm-conditional-logic-conditions">
						<?php if ( is_array( $rules ) ) {
							foreach ( $rules as $key => $rule ) {
								$is_input_hidden = 'in_cart' == $rule['conditional_logic_rule'] || 'not_in_cart' == $rule['conditional_logic_rule'] || 'user_role' == $rule['conditional_logic_rule'] ? true : false;
								$is_product_dropdown_hidden = 'in_cart' == $rule['conditional_logic_rule'] || 'not_in_cart' == $rule['conditional_logic_rule'] ? '' : 'hidden';
								$is_user_dropdown_hidden = 'user_role' == $rule['conditional_logic_rule'] ? '' : 'hidden';
								?>
								<div class="cfm-conditional-logic-condition cfm-conditional-logic-repeatable-row" data-key="<?php echo $key; ?>">
									<?php
									echo $this->display_conditional_logic_rules( $index, $key, $rule );
									echo $this->display_conditional_logic_operators( $index, $key, $rule );
									?>

									<input type="text" value="<?php echo $rule['conditional_logic_value']; ?>" class="cfm-conditional-logic-value <?php echo $is_input_hidden ? 'hidden' : ''; ?>" name="cfm_input[<?php echo $index ?>][conditional_logic][rules][<?php echo $key ?>][conditional_logic_value]" placeholder="<?php _e( 'Enter a value', 'edd_cfm' ); ?>" />

									<?php
									echo EDD()->html->product_dropdown( array(
										'name'     => 'cfm_input[' . $index . '][conditional_logic][rules][' . $key . '][conditional_logic_products][]',
										'selected' => isset( $rule['conditional_logic_products'] ) ? $rule['conditional_logic_products'] : false,
										'class'    => 'cfm-conditional-logic-product-dropdown ' . $is_product_dropdown_hidden,
										'multiple' => true,
										'chosen'   => true,
									) );

									$wp_roles = wp_roles()->roles;
									$roles = array();

									foreach ( $wp_roles as $k => $role ) {
										$roles[ $k ] = translate_user_role( $role['name'] );
									}

									echo EDD()->html->select( array(
										'name'             => 'cfm_input[' . $index . '][conditional_logic][rules][' . $key . '][conditional_logic_user_role][]',
										'class'            => 'cfm-conditional-logic-user-role-dropdown ' . $is_user_dropdown_hidden,
										'multiple'         => true,
										'chosen'           => true,
										'options'          => $roles,
										'placeholder'      => __( 'Choose one or more user roles', 'edd_cfm' ),
										'show_option_all'  => false,
										'show_option_none' => false,
										'selected'         => isset( $rule['conditional_logic_user_role'] ) ? $rule['conditional_logic_user_role'] : false,
									) );
									?>

									<span class="cfm-conditional-logic-repeatable-row-actions"><a href="#" class="edd-delete"><?php _e( 'Remove', 'edd_cfm' ); ?></a></span>
								</div>
							<?php }
						} else { ?>
							<div class="cfm-conditional-logic-condition cfm-conditional-logic-repeatable-row" data-key="0">
								<?php
								echo $this->display_conditional_logic_rules( $index );
								echo $this->display_conditional_logic_operators( $index );
								?>

								<input type="text" class="cfm-conditional-logic-value" name="cfm_input[<?php echo $index ?>][conditional_logic][rules][0][conditional_logic_value]" placeholder="<?php _e( 'Enter a value', 'edd_cfm' ); ?>" />

								<?php
								echo EDD()->html->product_dropdown( array(
									'name'     => 'cfm_input[' . $index . '][conditional_logic][rules][0][conditional_logic_products][]',
									'class'    => 'cfm-conditional-logic-product-dropdown hidden',
									'multiple' => true,
									'chosen'   => true,
								) );

								$wp_roles = wp_roles()->roles;
								$roles = array();

								foreach ( $wp_roles as $key => $role ) {
									$roles[ $key ] = translate_user_role( $role['name'] );
								}

								echo EDD()->html->select( array(
									'name'        => 'cfm_input[' . $index . '][conditional_logic][rules][0][conditional_logic_user_role][]',
									'class'       => 'cfm-conditional-logic-user-role-dropdown hidden',
									'multiple'    => true,
									'chosen'      => true,
									'options'     => $roles,
									'placeholder' => __( 'Choose one or more user roles', 'edd_cfm' ),
								) );

								?>

								<span class="cfm-conditional-logic-repeatable-row-actions"><a href="#" class="edd-delete"><?php _e( 'Remove', 'edd_cfm' ); ?></a></span>
							</div>
						<?php } ?>
						<p>
							<a class="button-secondary cfm-conditional-logic-add-repeatable" style="margin: 6px 0 10px;"><?php _e( 'Add New Rule', 'edd_cfm' ); ?></a>
						</p>
					</div>
				</div>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Display conditional logic rules.
	 *
	 * @access private
	 * @since  2.2
	 *
	 * @param int   $index Formbuilder index.
	 * @param int   $key   Rule key.
	 * @param array $rule  Rule meta.
	 *
	 * @return string $output Conditional logic rules.
	 */
	private function display_conditional_logic_rules( $index, $key = 0, $rule = array() ) {
		$rules = apply_filters( 'edd_cfm_conditional_logic_rules', array(
			'cart_num_items' => __( 'Number of items in cart', 'edd_cfm' ),
			'cart_amount'    => __( 'Cart amount', 'edd_cfm' ),
			'in_cart'        => sprintf( __( '%s in cart', 'edd_cfm' ), edd_get_label_plural() ),
			'not_in_cart'    => sprintf( __( '%s not in cart', 'edd_cfm' ), edd_get_label_plural() ),
			'user_role'      => __( 'User role', 'edd_cfm' ),
		) );

		$args = array(
			'options'          => $rules,
			'class'            => 'cfm-conditional-logic-rule',
			'name'             => 'cfm_input[' . $index . '][conditional_logic][rules][' . $key . '][conditional_logic_rule]',
			'show_option_all'  => false,
			'show_option_none' => false,
		);

		if ( isset( $rule['conditional_logic_rule'] ) ) {
			$args['selected'] = $rule['conditional_logic_rule'];
		}

		$output = EDD()->html->select( $args );

		return $output;
	}

	/**
	 * Display conditional logic operators.
	 *
	 * @access private
	 * @since  2.2
	 *
	 * @param int   $index Formbuilder index.
	 * @param int   $key   Rule key.
	 * @param array $rule  Rule meta.
	 *
	 * @return string $output Conditional logic operators.
	 */
	private function display_conditional_logic_operators( $index, $key = 0, $rule = array() ) {
		$is_dropdown_hidden = 'in_cart' == $rule['conditional_logic_rule'] || 'not_in_cart' == $rule['conditional_logic_rule'] || 'user_role' == $rule['conditional_logic_rule'] ? 'hidden' : '';

		$operators = apply_filters( 'edd_cfm_conditional_logic_operators', array(
			'is'           => __( 'is', 'edd_cfm' ),
			'is_not'       => __( 'is not', 'edd_cfm' ),
			'greater_than' => __( 'greater than', 'edd_cfm' ),
			'less_than'    => __( 'less than', 'edd_cfm' ),
		) );

		$args = array(
			'options'          => $operators,
			'class'            => 'cfm-conditional-logic-operator ' . $is_dropdown_hidden,
			'name'             => 'cfm_input[' . $index . '][conditional_logic][rules][' . $key . '][conditional_logic_operator]',
			'show_option_all'  => false,
			'show_option_none' => false,
		);

		if ( isset( $rule['conditional_logic_operator'] ) ) {
			$args['selected'] = $rule['conditional_logic_operator'];
		}

		$output = EDD()->html->select( $args );

		return $output;
	}

	/**
	 * Evaluate the conditional logic and check if the conditions are satisfied.
	 *
	 * @access public
	 * @since  2.2
	 *
	 * @return bool Conditions satisfied or not.
	 */
	public function evaluate_conditional_logic() {
		$action = false;

		if ( ! isset( $this->characteristics['conditional_logic'] ) ) {
			return true;
		}

		$toggle_value = esc_attr( $this->characteristics['conditional_logic']['conditional_logic_enabled'] );

		if ( empty( $toggle_value ) || 'on' !== $toggle_value ) {
			return true;
		}

		$matches = 0;

		$logic = $this->characteristics['conditional_logic'];

		if ( is_array( $logic['rules'] ) ) {
			foreach ( $logic['rules'] as $key => $rule ) {
				if ( 'in_cart' == $rule['conditional_logic_rule'] || 'not_in_cart' == $rule['conditional_logic_rule'] ) {
					if ( $this->is_match( $rule['conditional_logic_rule'], $rule['conditional_logic_operator'], $rule['conditional_logic_products'] ) ) {
						$matches++;
					}
				} elseif ( 'user_role' == $rule['conditional_logic_rule'] ) {
					if ( $this->is_match( $rule['conditional_logic_rule'], $rule['conditional_logic_operator'], $rule['conditional_logic_user_role'] ) ) {
						$matches++;
					}
				} else {
					if ( $this->is_match( $rule['conditional_logic_rule'], $rule['conditional_logic_operator'], $rule['conditional_logic_value'] ) ) {
						$matches++;
					}
				}
			}
		}

		if ( ( 'all' == $logic['conditional_logic_type'] && $matches == sizeof( $logic['rules'] ) || ( 'any' == $logic['conditional_logic_type'] && $matches > 0 ) ) ) {
			$action = true;
		}

		return $action;
	}

	/**
	 * Check if the logic evaluates to true.
	 *
	 * @access private
	 * @since  2.2
	 *
	 * @param string $rule     Rule.
	 * @param string $operator Logical operator.
	 * @param string $value    Value to compare against.
	 *
	 * @return boolean Whether the logical test evaluated to true or not.
	 */
	private function is_match( $rule = '', $operator = '', $value = '' ) {
		if ( empty( $rule ) || empty( $operator ) || ! isset( $value ) ) {
			return false;
		}

		$data = $this->find_source_data( $rule );

		if ( 'in_cart' == $rule || 'not_in_cart' == $rule ) {
			$data = $value;
			$data = array_map( 'absint', $data );
			asort( $data );
			$data = array_filter( array_values( $data ) );

			$validated = false;

			foreach ( $data as $download_id ) {
				if ( empty( $download_id ) ) {
					continue;
				}

				if ( 'in_cart' == $rule && edd_item_in_cart( $download_id ) ) {
					$validated = true;
				}

				if ( 'not_in_cart' == $rule && ! edd_item_in_cart( $download_id ) ) {
					$validated = true;
				}
			}

			return $validated;
		}

		if ( 'user_role' == $rule ) {
			return in_array( $data, $value );
		}

		switch ( $operator ) {
			case 'is' :
				return $value == $data;
				break;

			case 'is_not' :
				return $value != $data;
				break;

			case 'greater_than' :
				return $data > $value;
				break;

			case 'less_than' :
				return $data < $value;
				break;
		}

		return false;
	}

	/**
	 * Generate source data to compare against depending on the rule.
	 *
	 * @access private
	 * @since  2.2
	 *
	 * @param string $rule Rule.
	 *
	 * @return mixed string|array|null Source data to be used in logical test.
	 */
	private function find_source_data( $rule = '' ) {
		if ( empty( $rule ) ) {
			return '';
		}

		$data = null;

		switch ( $rule ) {
			case 'cart_amount' :
				$data = edd_get_cart_total();
				break;

			case 'user_role' :
				$user = wp_get_current_user();

				if ( $roles = $user->roles ) {
					$data = $roles[0];
				}
				break;

			case 'cart_num_items' :
				$data = edd_get_cart_quantity();
				break;
		}

		/**
		 * Allow the data to be filtered for other rules to be added by extensions.
		 *
		 * @since 2.2
		 *
		 * @param array  $data Data to be passed back to the conditional logic test.
		 * @param string $rule Name of the rule.
		 * @param int    $id   Field ID.
		 * @param int    $form Form ID.
		 */
		$data = apply_filters( 'edd_cfm_conditional_logic_source_data', $data, $rule, $this->id, $this->form );

		return $data;
	}

	/**
	 * Is conditional logic enabled?
	 *
	 * @access public
	 * @since 2.2
	 *
	 * @return bool
	 */
	public function is_conditional_logic_enabled() {
		return isset( $this->characteristics['conditional_logic'] ) && 'on' == $this->characteristics['conditional_logic']['conditional_logic_enabled'];
	}

	/**
	 * Get the action for the conditional logic (show/hide).
	 *
	 * @access public
	 * @since  2.2
	 *
	 * @return string $action Show/hide.
	 */
	public function get_conditional_logic_action() {
		$action = 'show';

		if ( isset( $this->characteristics['conditional_logic'] ) && 'on' == $this->characteristics['conditional_logic']['conditional_logic_enabled'] ) {
			$action = $this->characteristics['conditional_logic']['conditional_logic_action'];
		}

		/**
		 * Filter the conditional logic action (show/hide)
		 *
		 * @since 2.2
		 *
		 * @param string $action Show/hide.
		 * @param int    $id     Field ID.
		 * @param int    $form   Form ID.
		 */
		return apply_filters( 'edd_cfm_conditional_logic_action', $action, $this->id, $this->form );
	}

	/**
	 * Checks if the field has been displayed or not.
	 *
	 * @access public
	 * @since  2.2
	 *
	 * @return bool
	 */
	public function is_field_displayed() {
		return $this->evaluate_conditional_logic() && 'show' == $this->get_conditional_logic_action();
	}
}