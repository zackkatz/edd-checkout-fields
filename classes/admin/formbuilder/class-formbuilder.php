<?php
/**
 * CFM Formbuilder
 *
 * Creates the formbuilder display and
 * also contains the save routine.
 *
 * @package CFM
 * @subpackage Formbuilder
 * @since 2.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) { 
	exit;
}

/**
 * Formbuilder Updated Message.
 * 
 * Shows the updated message when the formbuilder is saved.
 *
 * @since 2.0.0
 * @access public
 * 
 * @param array $messages Messages by update response code.
 * @return array Messages by update response code.
 */
function cfm_forms_form_updated_message( $messages ) {
	$message = array(
		0  => '',
		1  => __( 'Checkout form updated.', 'edd_cfm' ) ,
		2  => __( 'Checkout form updated.', 'edd_cfm' ) ,
		3  => __( 'Checkout form updated.', 'edd_cfm' ) ,
		4  => __( 'Checkout form updated.', 'edd_cfm' ) ,
		5  => isset( $_GET['revision'] ) ? sprintf( __( 'Checkout form restored to revision from %s', 'edd_cfm' ) , wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
		6  => __( 'Checkout form updated.', 'edd_cfm' ) ,
		7  => __( 'Checkout form updated.', 'edd_cfm' ) ,
		8  => __( 'Checkout form updated.', 'edd_cfm' ) ,
		9  => __( 'Checkout form updated.', 'edd_cfm' ) ,
		10 => __( 'Checkout form updated.', 'edd_cfm' )
	);
	return $messages;
}
add_filter( 'post_updated_messages', 'cfm_forms_form_updated_message' );

/**
 * Formbuilder Metaboxes.
 * 
 * Registers the metaboxes being shown on the formbuilder.
 *
 * @since 2.0.0
 * @access public
 * 
 * @return void
 */
function cfm_forms_add_meta_boxes() {
	$id = get_the_ID();
	if ( empty( $id ) ){
		return;
	}
	add_meta_box( 'cfm-metabox-editor', __( 'Form Editor', 'edd_cfm' ), 'cfm_formbuilder_fields_metabox', 'edd-checkout-fields', 'normal', 'high' );
	add_meta_box( 'cfm-metabox-save'  , __( 'Save', 'edd_cfm' ), 'cfm_formbuilder_save', 'edd-checkout-fields', 'side', 'core' );
	$fname = '';
	foreach ( EDD_CFM()->load_forms as $name => $class ) {
		$form = new $class( $name, 'name' );
		if ( $form->id == $id ) {
			$fname = $form->title();
			break;
		}
	}
	$metabox_title = sprintf( __( 'Add %s Form Fields', 'edd_cfm' ), $fname );
	add_meta_box( 'cfm-metabox-fields-custom', __( 'Add Custom Fields', 'edd_cfm' ), 'cfm_formbuilder_sidebar_custom', 'edd-checkout-fields', 'side'  , 'core' );
	add_meta_box( 'cfm-metabox-fields-extension', __( 'Add Extension Created Fields', 'edd_cfm' ), 'cfm_formbuilder_sidebar_extension', 'edd-checkout-fields', 'side', 'core' );
	remove_meta_box( 'submitdiv', 'edd-checkout-fields', 'side' );
	remove_meta_box( 'slugdiv', 'edd-checkout-fields', 'normal' );
}
add_action( 'add_meta_boxes_edd-checkout-fields', 'cfm_forms_add_meta_boxes' );

/**
 * Formbuilder Fields Metabox
 * 
 * The content of the fields metabox in the formbuilder (contains 
 * all of the fields currently on a form).
 *
 * @since 2.0.0
 * @access public
 *
 * @global $pagenow Current page being viewed in admin.
 * @global $post Current post being viewed in admin.
 *
 * @param WP_Post $post Current post we're on (not used).
 * @return void
 */
function cfm_formbuilder_fields_metabox( $post ) {
	global $post, $pagenow;

	$id = get_the_ID();
	if ( empty( $id ) ){
		return;
	}

	$fname = '';
	$lowerfname = '';
	foreach ( EDD_CFM()->load_forms as $name => $class ) {
		$form = new $class( $name, 'name' );
		if ( $form->id == $id ) {
			$fname      = $form->title();
			$lowerfname = strtolower( $fname );
			break;
		}
	}

	$title = sprintf( __( '%s Form', 'edd_cfm' ), $fname ); ?>
	<h1><?php echo $title; ?></h1>
	<div class="tab-content">
		<div id="cfm-metabox" class="group">
		<?php
		$form = sprintf( __( 'Your %s form has no fields', 'edd_cfm' ), $lowerfname );
		$form_inputs = get_post_meta( $post->ID, 'cfm-form', true ) ;
		$form_inputs = isset( $form_inputs ) ? $form_inputs : array(); ?>

		<input type="hidden" name="cfm-formbuilder-fields" id="cfm-formbuilder-fields" value="<?php echo wp_create_nonce( "cfm-formbuilder-fields" ); ?>" />
		<div style="margin-bottom: 10px">
			<button class="button cfm-collapse"><?php _e( 'Toggle All Fields Open/Close', 'edd_cfm' ); ?></button>
		</div>

		<?php
		if ( empty( $form_inputs ) ) { ?>
			<div class="cfm-updated">
			  <p><?php echo $form; ?></p>
			</div>
			<?php } ?>

			<ul id="cfm-formbuilder-fields" class="cfm-formbuilder-fields unstyled">
				<?php
				if ( $form_inputs ) {
					$count = 0;
					foreach ( $form_inputs as $order => $input_field ) {
						if ( cfm_is_key( $input_field['template'], EDD_CFM()->load_fields ) ) {
							$class = EDD_CFM()->load_fields[ $input_field['template'] ];
							$name  = isset( $input_field['name'] ) ? trim( $input_field['name'] ) : '';
							$class = new $class( $name, $id );
							echo $class->render_formbuilder_field( $count, false );
						} else {
							_cfm_deprecated_function( 'Inserting a custom field without using CFM Fields API', '2.0' );

							/**
							 * Show the formbuilder field of a pre-2.0 field.
							 *
							 * For fields made prior to the introduction of the
							 * CFM field class, this action allows for those 
							 * extensions to output their formbuilder field.
							 *
							 * @since 1.0.0
							 *
							 * @deprecated 2.0.0
							 * @see  CFM_Field
							 * 
							 * @param int $count The order of the field in the form.
							 * @param array $input_field The field's stored characteristics.
							 */								
							do_action( 'cfm_admin_field_' . $input_field['template'], $count, $input_field );
						}
						$count++;
					}
				} ?>
			</ul>
		</div>
	</div>
	<?php
}

/**
 * Formbuilder Save Metabox
 * 
 * Creates the metabox used by CFM to save
 * the formbuilder.
 *
 * @since 2.0.0
 *
 * @return void
 */
function cfm_formbuilder_save() {
	$id = get_the_ID();
	foreach ( EDD_CFM()->load_forms as $name => $class ) {
		$form = new $class( $name, 'name' );
		if ( $form->id == $id ) {
		?>
			<div class="submitbox" id="submitpost">
				<div id="minor-publishing">
					<div id="minor-publishing-actions">
						<center>
							<input name="original_publish" type="hidden" id="original_publish" value="<?php esc_attr_e( 'Save' ) ?>" />
							<input name="save" type="submit" class="button button-primary button-large" id="publish" accesskey="p" value="<?php esc_attr_e( 'Save' ) ?>" />
							<span class="spinner"></span>
						</center>
					</div>
					<div class="cfm-clear"></div>
				</div>
			</div>
		<?php
		}
	}
}

/**
 * Formbuilder Custom Fields Metabox
 * 
 * Creates the metabox used by CFM to allow
 * for the selection of custom fields. These fields
 * are generic in nature like url or textbox.
 *
 * @since 2.0.0
 * @access public
 *
 * @todo  Simplify this and the other field
 *        metabox functions.
 * 
 * @return void
 */
function cfm_formbuilder_sidebar_custom() { ?>
  <div class="cfm-form-buttons">
  <?php
	$id = get_the_ID();
	if ( empty( $id ) ){
		return;
	}

	$fkey  = '';
	$fname = '';
	foreach ( EDD_CFM()->load_forms as $name => $class ) {
		$form = new $class( $name, 'name' );
		if ( $form->id == $id ) {
			$fname = $form->title();
			$fkey  = $form->name;
			break;
		}
	}

	$order = array();
	$order['Custom Fields'] = array();
	/* foreach fields as field
	 * 		does field support this form? (index "forms" in supports )
	 * 			if yes output button. Name from defaults, label from defaults.
	 */
	foreach ( EDD_CFM()->load_fields as $fid => $field ) {
		$class = EDD_CFM()->load_fields[ $fid ];
		$fieldo = new $class;
		if ( isset( $fieldo->supports['position'] ) && isset( $fieldo->supports['forms'][ $fkey ] ) && $fieldo->supports['forms'][ $fkey ] ) {
			if ( $fieldo->supports['position'] == 'custom' ) {
				$order['Custom Fields'][] = $fieldo;
			}
		}
	}

	$title = esc_attr( __( 'Click to add to the editor', 'edd_cfm' ) );
	usort( $order['Custom Fields'], 'cfm_field_sort');
	foreach ( $order as $type => $index ) {
		if ( count( $index )  >= 1 ) {
			foreach ( $index as $fielde ) {
				echo '<button class="cfm-button button" data-formid="' . get_the_ID() . '" data-name="'.$fielde->supports['template'].'" data-type="'.$fielde->supports['template'].'" title="' . $title . '">'. __( $fielde->supports['title'] , 'edd_cfm' ) .'</button>';
			}
		}
		else {
			echo __( 'There are no custom fields for this form', 'edd_cfm' );
		}
	} ?>
  </div>
  <script>
	(function($) {
			$('#menu-posts-download').addClass( 'wp-has-current-submenu wp-menu-open' );
		})( jQuery );
	</script>
  <?php
}

/**
 * Formbuilder Extension Fields Metabox.
 * 
 * Creates the metabox used by CFM to allow
 * for the selection of extension fields. These fields
 * are added by extensions like commissions and also
 * from pre-2.0 fields.
 *
 * @since 2.0.0
 * @access public
 *
 * @todo  Simplify this and the other 2 field
 *        metabox functions.
 * 
 * @return void
 */
function cfm_formbuilder_sidebar_extension() { ?>
  <div class="cfm-form-buttons">
	  <?php
		$id = get_the_ID();
		if ( empty( $id ) ){
			return;
		}

		$fkey  = '';
		$fname = '';
		foreach ( EDD_CFM()->load_forms as $name => $class ) {
			$form = new $class( $name, 'name' );
			if ( $form->id == $id ) {
				$fname = $form->title();
				$fkey  = $form->name;
				break;
			}
		}

		$order = array();
		$order['Extension Created Fields'] = array();
		/* foreach fields as field
		 * 		does field support this form? (index "forms" in supports )
		 * 			if yes output button. Name from defaults, label from defaults.
		 */		
		foreach ( EDD_CFM()->load_fields as $fid => $field ) {
			$class = EDD_CFM()->load_fields[ $fid ];
			$fieldo = new $class;
			if ( isset( $fieldo->supports['position'] ) && isset( $fieldo->supports['forms'][ $fkey ] ) && $fieldo->supports['forms'][ $fkey ] ) {
				if ( $fieldo->supports['position'] == 'extension' ) {
					$order['Extension Created Fields'][] = $fieldo;
				}
			}
		}

		$title = esc_attr( __( 'Click to add to the editor', 'edd_cfm' ) );
		usort( $order['Extension Created Fields'], 'cfm_field_sort');
		foreach ( $order as $type => $index ) {
			if ( count( $index )  >= 1 ) {
				foreach ( $index as $fielde ) {
					echo '<button class="cfm-button button" data-formid="' . get_the_ID() . '" data-name="'.$fielde->supports['template'].'" data-type="'.$fielde->supports['template'].'" title="' . $title . '">'. __( $fielde->supports['title'] , 'edd_cfm' ) .'</button>';
				}
			}
			else {
				if ( !has_action( 'cfm_custom_post_button' ) ) {
					echo __( 'You do not have any extensions installed that have a CFM custom field', 'edd_cfm' );
				}
			}
		}
		
		/**
		 * Output pre-CFM 2.0 field buttons for default checkout form.
		 *
		 * This outputs buttons for fields 
		 * that were made prior to the introduction
		 * of the CFM_Fields API for the default checkout form form.
		 *
		 * @since 1.0.0
		 *
		 * @deprecated 2.0.0
		 * @see  CFM_Field
		 *
		 * @param string $title Text for the tooltip to add field.
		 */
		do_action( 'cfm_custom_post_button', $title );
		?>
	</div>
	<?php
}

/**
 * Formbuilder Extension Fields Metabox.
 * 
 * Saves the CFM formbuilder.
 *
 * @since 2.0.0
 * @access public
 *
 * @param  int $post_id Int ID of the current form.
 * @param  WP_Post $post Post object for the current form.
 * @return void
 */
function cfm_forms_save_form( $post_id, $post ) {
	if ( empty( $post_id ) || $post_id < 1 || !is_object( $post ) || $post->post_type !== 'edd-checkout-fields' ) {
		return;
	}

	if ( isset( $_POST['cfm-formbuilder-fields'] ) && wp_verify_nonce( $_POST['cfm-formbuilder-fields'], 'cfm-formbuilder-fields' ) ) {
		$values = $_POST['cfm_input'];
		foreach ( EDD_CFM()->load_forms as $name => $class ) {
			$form = new $class( $name, 'name' );
			if ( $form->id == $post->ID ) {
				$return = false;
				$return = $form->save_formbuilder_fields( $post->ID, $values );
				if ( $return ) {
					return $return;
				}
				break;
			}
		}
	}
}
add_action( 'save_post', 'cfm_forms_save_form', 1, 2 );

/**
 * Add field to formbuilder.
 * 
 * Runs the ajax call that adds a field to
 * the current formbuilder.
 *
 * @since 2.0.0
 * @access public
 *
 * @return void
 */
function cfm_forms_ajax_post_add_field() {
	if ( !isset( $_POST['action'] ) || $_POST['action'] !== 'cfm_formbuilder' ) {
		exit;
	}
	$name     = isset( $_POST['name'] )  ? $_POST['name'] : '' ;
	$field_id = isset( $_POST['order'] ) ? $_POST['order'] : 0 ;
	$id       = isset( $_POST['id'] )    ? $_POST['id'] : 0 ;

	if ( cfm_is_key( $name, EDD_CFM()->load_fields ) ) {
		$class = EDD_CFM()->load_fields[ $name ];
		$class = new $class( '', $id );
		echo $class->render_formbuilder_field( $field_id, true );
	} else {
		/**
		 * Output pre-CFM 2.0 field in formbuilder.
		 *
		 * Adds a pre-CFM 2.0 field to the formbuilder.
		 *
		 * @since 2.0.0
		 *
		 * @deprecated 2.0.0
		 * @see  CFM_Field
		 *
		 * @param int $field_id Order of field in the formbuilder.
		 */		
		do_action( 'cfm_admin_field_' . $name, $field_id );
	}
	exit;
}
add_action( 'wp_ajax_cfm_formbuilder', 'cfm_forms_ajax_post_add_field' );

/**
 * Sort CFM Field buttons.
 * 
 * This function takes in 2 fields at a time
 * and using strcmp sorts the fields by their 
 * title.
 *
 * @since 2.0.0
 * @access public
 *
 * @param  CFM_Field $a First CFM field.
 * @param  CFM_Field $b Second CFM field.
 *
 * @return int The order of the fields (see PHP manual
 *                 for strcmp).
 */
function cfm_field_sort( $a,$b ) {
	return strnatcasecmp($a->supports['title'],$b->supports['title']);
}