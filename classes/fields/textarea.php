<?php
class CFM_Textarea_Field extends CFM_Field {

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
		'template'   => 'textarea',
		'title'       => 'Textarea',
	);

	/** @var array Characteristics are things that can change from field to field of the same field type. Like the placeholder between two text fields. Stored in db. */
	public $characteristics = array(
		'name'        => '',
		'template'   => 'textarea',
		'required'    => false,
		'label'       => '',
		'css'         => '',
		'default'     => '',
		'size'        => '',
		'help'        => '',
		'placeholder' => '',
		'cols'        => '50',
		'rows'        => '8',
		'rich'        => '',
		'meta_type'   => 'payment', // 'payment' or 'user' here if is_meta()
		'public'          => "public", // denotes whether a field shows in the admin only
		'show_in_exports' => "export", // denotes whether a field is in the CSV exports
	);

	public function set_title() {
		$title = _x( 'Textarea', 'CFM Field title translation', 'edd_cfm' );
		$title = apply_filters( 'cfm_' . $this->name() . '_field_title', $title );
		$this->supports['title'] = $title;		
	}

	/** Returns the HTML to render a field in admin */
	public function render_field_admin( $user_id = -2, $profile = -2 ) {
		if ( $user_id === -2 ) {
			$user_id = get_current_user_id();
		}

		$value     = $this->get_field_value_admin( $this->payment_id, $this->user_id );
		$req_class = 'rich-editor';
		$required  = $this->required();

		$output        = '';
		$output     .= sprintf( '<p class="cfm-el %1s %2s %3s">', esc_attr( $this->template() ), esc_attr( $this->name() ), esc_attr( $this->css() ) );
		$output    .= $this->label( false );
		ob_start(); ?>
		<?php
		$rows =isset( $this->characteristics['rows'] ) ? $this->characteristics['rows'] : 8;
		$cols =isset( $this->characteristics['cols'] ) ? $this->characteristics['cols'] : 50;
		if ( isset( $this->characteristics['rich'] ) && $this->characteristics['rich'] == 'yes' ) {
			$options = array( 'editor_height' => $rows, 'quicktags' => false, 'editor_class' => $req_class, 'media_buttons' => false );
			printf( '<span class="cfm-rich-validation" data-required="%s" data-type="rich" data-id="%s"></span>', $this->characteristics['required'], $this->name() );
			wp_editor( $value, $this->name(), $options );

		} elseif ( isset( $this->characteristics['rich'] ) && $this->characteristics['rich'] == 'teeny' ) {
			$options = array( 'editor_height' => $rows, 'quicktags' => false, 'teeny' => true, 'editor_class' => $req_class, 'media_buttons' => false );
			
			printf( '<span class="cfm-rich-validation" data-required="%s" data-type="rich" data-id="%s"></span>', $this->characteristics['required'], $this->name() );
			wp_editor( $value, $this->name(), $options );
		} else {  ?>
			<textarea name="<?php echo esc_attr( $this->name() ); ?>" id="<?php echo esc_attr( $this->name() ); ?>" class="textarea edd-input" data-required="false" data-type="textarea" placeholder="<?php echo esc_attr( $this->placeholder() ); ?>" rows="<?php echo esc_attr( $rows ); ?>" cols="<?php echo esc_attr( $cols ); ?>"><?php echo esc_textarea( $value ) ?></textarea>
		<?php } ?>
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

		$value     = $this->get_field_value_frontend( $this->payment_id, $this->user_id );
		$required  = $this->required();
		$req_class = $required ? 'required' : 'rich-editor';

		$output        = '';
		$output     .= sprintf( '<p class="cfm-el %1s %2s %3s">', esc_attr( $this->template() ), esc_attr( $this->name() ), esc_attr( $this->css() ) );
		$output    .= $this->label( ! (bool) $profile );
		ob_start(); ?>
		<?php
		$rows =isset( $this->characteristics['rows'] ) ? $this->characteristics['rows'] : 8;
		$cols =isset( $this->characteristics['cols'] ) ? $this->characteristics['cols'] : 50;
		if ( isset( $this->characteristics['rich'] ) && $this->characteristics['rich'] == 'yes' ) {
			$options = array( 'editor_height' => $rows, 'quicktags' => false, 'editor_class' => $req_class );
			printf( '<span class="cfm-rich-validation" data-required="%s" data-type="rich" data-id="%s"></span>', $this->characteristics['required'], $this->name() );
			wp_editor( $value, $this->name(), $options );

		} elseif ( isset( $this->characteristics['rich'] ) && $this->characteristics['rich'] == 'teeny' ) {
			$options = array( 'editor_height' => $rows, 'quicktags' => false, 'teeny' => true, 'editor_class' => $req_class );
			printf( '<span class="cfm-rich-validation" data-required="%s" data-type="rich" data-id="%s"></span>', $this->characteristics['required'], $this->name() );
			wp_editor( $value, $this->name(), $options );
		} else {  ?>
				<textarea name="<?php echo esc_attr( $this->name() ); ?>" id="<?php echo esc_attr( $this->name() ); ?>" class="textarea edd-input <?php echo $this->required_class(); ?>" data-required="<?php echo $required; ?>" data-type="textarea"<?php $this->required_html5(); ?> placeholder="<?php echo esc_attr( $this->placeholder() ); ?>" rows="<?php echo esc_attr( $rows ); ?>" cols="<?php echo esc_attr( $cols ); ?>"><?php echo esc_textarea( $value ) ?></textarea>
		<?php } ?>
		<?php
		$output .= ob_get_clean();
		$output .= '</p>';
		return $output;
	}

	/** Returns the HTML to render a field for the formbuilder */
	public function render_formbuilder_field( $index = -2, $insert = false ) {
		$removable = $this->can_remove_from_formbuilder();
		ob_start(); ?>
		<li class="custom-field textarea_field">
			<?php $this->legend( $this->title(), $this->get_label(), $removable ); ?>
			<?php CFM_Formbuilder_Templates::hidden_field( "[$index][template]", $this->template() ); ?>

			<?php CFM_Formbuilder_Templates::field_div( $index, $this->name(), $this->characteristics, $insert ); ?>
				<?php CFM_Formbuilder_Templates::public_radio( $index, $this->characteristics ); ?>
				<?php CFM_Formbuilder_Templates::export_radio( $index, $this->characteristics ); ?>
				<?php CFM_Formbuilder_Templates::meta_type_radio( $index, $this->characteristics ); ?>
				<?php CFM_Formbuilder_Templates::standard( $index, $this ); ?>
				<?php CFM_Formbuilder_Templates::css( $index, $this->characteristics ); ?>
				<?php CFM_Formbuilder_Templates::common_textarea( $index, $this->characteristics ); ?>
			</div>
		</li>
		<?php
		return ob_get_clean();
	}

	public function sanitize( $values = array(), $payment_id = -2, $user_id = -2 ) {
		$name = $this->name();
		if ( !empty( $values[ $name ] ) ) {
			$values[ $name ] = trim( $values[ $name ] );
			$values[ $name ] = wp_kses( $values[ $name ], cfm_allowed_html_tags() );
		}
		return apply_filters( 'cfm_sanitize_' . $this->template() . '_field', $values, $name, $payment_id, $user_id );
	}
}
