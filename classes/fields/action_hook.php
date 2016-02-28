<?php
class CFM_Action_Hook_Field extends CFM_Field {

	/** @var bool For 3rd parameter of get_post/user_meta */
	public $single = true;
	
	/** @var string Version of field */
	public $version = '1.0.0';

	/** @var array Supports are things that are the same for all fields of a field type. Like whether or not a field type supports jQuery Phoenix. Stored in obj, not db. */
	public $supports = array(
		'multiple'    => true, // You can have multiples of this field
		'is_meta'     => true,  // in object as public (bool) $meta;
		'forms'       => array( // the forms you can use this field on
			'checkout'     => true,
		),
		'position'    => 'custom', // the position on the formbuilder
		'permissions' => array(
			'can_remove_from_formbuilder' => true, // this field can be removed once inserted into the formbuilder
			'can_change_meta_key'         => true, // you can change the meta key this field saves to in the formbuilder
			'can_add_to_formbuilder'      => true, // you can add this field to a form via the formbuilder
		),
		'template'    => 'action_hook', // the type of field
		'title'       => 'Action Hook',
	);

	/** @var array Characteristics are things that can change from field to field of the same field type. Like the placeholder between two email fields. Stored in db. */
	public $characteristics = array(
		'name'        => '', // the metakey where this field saves to
		'template'    => 'action_hook',
		'public'      => false, // can you display this publicly (used by CFM_Field->display_field() )
		'required'    => false, // is it a required field (default is false)
		'css'         => '',
		'meta_type'   => 'payment', // 'payment' or 'user' here if is_meta()
		'public'          => "public", // denotes whether a field shows in the admin only
		'show_in_exports' => "export", // denotes whether a field is in the CSV exports
	);

	public function set_title() {
		$title = _x( 'Action Hook', 'CFM Field title translation', 'edd_cfm' );
		$title = apply_filters( 'cfm_' . $this->name() . '_field_title', $title );
		$this->supports['title'] = $title;		
	}

	/** Returns the Action_Hook to render a field in admin */
	public function render_field_admin( $user_id = -2, $profile = -2 ) {
		if ( $user_id === -2 ) {
			$user_id = get_current_user_id();
		}

		$output        = '';
		$output     .= sprintf( '<p class="cfm-el %1s %2s %3s">', esc_attr( $this->template() ), esc_attr( $this->name() ), esc_attr( $this->css() ) );
		ob_start();
		do_action( $this->name(), $this->form, $this->payment_id, $this->user_id, $this );
		$output .= ob_get_clean();
		$output .= '</p>';
		return $output;
	}

	/** Returns the Action_Hook to render a field in frontend */
	public function render_field_frontend( $user_id = -2, $profile = -2 ) {
		if ( $user_id === -2 ) {
			$user_id = get_current_user_id();
		}
		$output        = '';
		$output     .= sprintf( '<p class="cfm-el %1s %2s %3s">', esc_attr( $this->template() ), esc_attr( $this->name() ), esc_attr( $this->css() ) );
		ob_start();
		do_action( $this->name(), $this->form, $this->payment_id, $this->user_id, $this );
		$output .= ob_get_clean();
		$output .= '</p>';
		return $output;
	}

	/** Returns the Action_Hook to render a field for the formbuilder */
	public function render_formbuilder_field( $index = -2, $insert = false ) {
		$removable    = $this->can_remove_from_formbuilder();
		$title_name   = sprintf( '%s[%d][name]', 'cfm_input', $index );
		$title_value  = esc_attr( $this->name() );
		ob_start(); ?>
		<li class="action_hook">
			<?php $this->legend( $this->title(), $this->name(), $removable ); ?>
			<?php CFM_Formbuilder_Templates::public_radio( $index, $this->characteristics, "public" ); ?>
			<?php CFM_Formbuilder_Templates::meta_type_radio( $index, $this->characteristics, "payment" ); ?>
			<?php CFM_Formbuilder_Templates::hidden_field( "[$index][template]", $this->template() ); ?>
			<?php CFM_Formbuilder_Templates::field_div( $index, $this->name(), $this->characteristics, $insert ); ?>
			<?php CFM_Formbuilder_Templates::css( $index, $this->characteristics ); ?>
				<div class="cfm-form-rows">
					<label><?php _e( 'Hook Name', 'edd_cfm' ); ?></label>
					<div class="cfm-form-sub-fields">
						<input type="text" class="smallipopInput" title="<?php _e( 'Name of the hook', 'edd_cfm' ); ?>" name="<?php echo $title_name; ?>" value="<?php echo esc_attr( $title_value ); ?>" />

						<div class="description" style="margin-top: 8px;">
							<?php _e( "This is for developers to add dynamic elements as they want. It provides the chance to add whatever input type you want to add in this form.", 'edd_cfm' ); ?>
							<pre>
add_action('{hookname}', 'my_function_name}', 10, 4 );
// first param: Form Object
// second param: Save ID of payment if in scope, else -2
// third param: Save ID of user if in scope, else -2
// fourth param: Field Object
function my_function_name( $form, $payment_id, $user_id, $field ) {
	// Do whatever you want here
}
							</pre>
						</div>
					</div>
				</div>
			</div>
		</li>
		<?php
		return ob_get_clean();
	}

	// note in order for this to run, a hidden text field should be output in the render function with an id of the meta_key, else this won't run
	public function save_field_admin( $payment_id = -2, $user_id = -2, $value = '', $current_user_id = -2 ) {
		do_action( $this->name() . '_save_admin', $payment_id, $user_id, $value, $current_user_id, $this );
	}

	// note in order for this to run, a hidden text field should be output in the render function with an id of the meta_key, else this won't run
	public function save_field_frontend( $payment_id = -2, $user_id = -2, $value = '', $current_user_id = -2 ) {
		do_action( $this->name() . '_save_frontend', $payment_id, $user_id, $value, $current_user_id, $this );
	}	

	/** Returns formatted data of field in frontend */
	public function export_data( $payment_id = -2, $user_id = -2 ) {
		return apply_filters( 'cfm_formatted_' . $this->template() . '_field', '', $payment_id, $user_id );
	}	

	public function validate( $values = array(), $payment_id = -2, $user_id = -2 ) {
		return apply_filters( 'cfm_validate_' . $this->template() . '_field', false, $values,  $this->name(), $payment_id, $user_id );
	}

	public function sanitize( $values = array(), $payment_id = -2, $user_id = -2 ) {
		return apply_filters( 'cfm_sanitize_' . $this->template() . '_field', $values, $this->name(), $payment_id, $user_id );
	}
}
