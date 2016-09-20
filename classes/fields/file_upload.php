<?php
class CFM_File_Upload_Field extends CFM_Field {

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
		'template'    => 'file_upload',
		'title'       => 'File Upload',
		'phoenix'    => false,
	);

	/** @var array Characteristics are things that can change from field to field of the same field type. Like the placeholder between two file_upload fields. Stored in db. */
	public $characteristics = array(
		'name'        => '',
		'template'    => 'file_upload',
		'required'    => false,
		'label'       => '',
		'default'     => '',
		'size'        => '',
		'help'        => '',
		'placeholder' => '',
		'count'       => '1',
		'css'         => '',
		'meta_type'   => 'payment', // 'payment' or 'user' here if is_meta()
		'public'          => "public", // denotes whether a field shows in the admin only
		'show_in_exports' => "export", // denotes whether a field is in the CSV exports
	);


	public function set_title() {
		$title = _x( 'File Upload', 'CFM Field title translation', 'edd_cfm' );
		$title = apply_filters( 'cfm_' . $this->name() . '_field_title', $title );
		$this->supports['title'] = $title;
	}

	/** Returns the HTML to render a field in admin */
	public function render_field_admin( $user_id = -2, $profile = -2 ) {
		if ( $user_id === -2 ) {
			$user_id = get_current_user_id();
		}

		$value     = $this->get_field_value_admin( $this->payment_id, $this->user_id );

		$uploaded_items = $value;
		if ( ! is_array( $uploaded_items ) || empty( $uploaded_items ) ) {
			$uploaded_items = array( 0 => '' );
		}

		$max_files = 0;
		if ( absint( $this->characteristics['count'] ) > 0 ) {
			$max_files = absint( $this->characteristics['count'] );
		}

		$output        = '';
		$output     .= sprintf( '<p class="cfm-el %1s %2s %3s">', esc_attr( $this->template() ), esc_attr( $this->name() ), esc_attr( $this->css() ) );
		$output    .= $this->label( false );
		ob_start(); ?>
			<div class="cfm-fields">
				 <table class="<?php echo sanitize_key( $this->name() ); ?>">
					<thead>
						<tr>
							<td class="cfm-file-column" colspan="2"><?php _e( 'File URL', 'edd_cfm' ); ?></td>
							<td class="cfm-download-file"> <?php _e( 'Download File', 'edd_cfm' ); ?> </td>
							<?php if ( $max_files > 1 || $max_files === 0 ) { ?>
							<td class="cfm-remove-column">&nbsp;</td>
							<?php } ?>
						 </tr>
					</thead>
					<tbody class="cfm-variations-list-<?php echo sanitize_key( $this->name() ); ?>">
							<input type="hidden" id="cfm-upload-max-files-<?php echo sanitize_key( $this->name() ); ?>" value="<?php echo $max_files; ?>" />
							<?php
							foreach ( $uploaded_items as $index => $attach_id ) {
								$show_download_link = false;

								if ( is_numeric( $attach_id ) ) {
									$download = wp_get_attachment_url( $attach_id );
								} else {
									$download = apply_filters( 'cfm_file_download_url', $attach_id );
								}

								if ( $download ) {
									$show_download_link = true;
								}

								?>
								<tr class="cfm-single-variation">
									 <td class="cfm-url-row">
												<input type="text" class="cfm-file-value" placeholder="<?php _e( "http://", 'edd_cfm' ); ?>" name="<?php echo $this->name(); ?>[<?php echo esc_attr( $index ); ?>]" value="<?php echo esc_attr( $download ); ?>" />
									 </td>
									 <td class="cfm-url-choose-row" width="1%">
												<a href="#" class="edd-submit button upload_file_button" data-choose="<?php _e( 'Choose file', 'edd_cfm' ); ?>" data-update="<?php _e( 'Insert file URL', 'edd_cfm' ); ?>">
												<?php echo str_replace( ' ', '&nbsp;', __( 'Choose file', 'edd_cfm' ) ); ?></a>
									 </td>
									 <td class="cfm-download-file">
									 		<?php if ( $show_download_link ) { ?>
												<?php printf( '<a href="%s">%s</a>', $download, __( 'Download File', 'edd_cfm' ) ); ?>
											<?php } else { ?>
												<?php _e( 'File is not available locally.', 'edd_cfm' ); ?>
											<?php } ?>
									 </td>
									 <?php if ( $max_files > 1 || $max_files === 0 ) { ?>
									 <td width="1%" class="cfm-delete-row">
												<a href="#" class="edd-cfm-delete delete">
												<?php _e( '&times;', 'edd_cfm' ); ?></a>
									 </td>
									<?php } ?>
								</tr>
							<?php } ?>
					</tbody>
					<?php if ( $max_files > 1 || $max_files === 0 ) : ?>
					<tfoot>
						<tr>
							<th colspan="5">
								<a href="#" class="edd-submit button insert-file-row" id="<?php echo sanitize_key( $this->name() ); ?>"><?php _e( 'Add File', 'edd_cfm' ); ?></a>
							</th>
						</tr>
					</tfoot>
					<?php endif; ?>
				</table>
		 	</div> <!-- .cfm-fields -->
		<script type="text/javascript">
		jQuery(document).ready(function($){
			wp.media.controller.Library.prototype.defaults.contentUserSetting=false;
		});
		</script>
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

		$uploaded_items = $value;
		if ( ! is_array( $uploaded_items ) || empty( $uploaded_items ) ) {
			$uploaded_items = array( 0 => '' );
		}

		$max_files = 0;
		if ( absint( $this->characteristics['count'] ) > 0 ) {
			$max_files = absint( $this->characteristics['count'] );
		}
		$output        = '';
		$output     .= sprintf( '<p class="cfm-el %1s %2s %3s">', esc_attr( $this->template() ), esc_attr( $this->name() ), esc_attr( $this->css() ) );
		$output    .= $this->label( true );
		ob_start(); ?>
			<div class="cfm-fields">
				 <table class="<?php echo sanitize_key( $this->name() ); ?>">
					<thead>
						<tr>
							<th class="cfm-file-column" colspan="2"><?php _e( 'File URL', 'edd_cfm' ); ?></th>
							<?php if ( $max_files > 1 || $max_files === 0 ) { ?>
									 <th class="cfm-remove-column">&nbsp;</th>
							<?php } ?>
						 </tr>
					</thead>
					<tbody class="cfm-variations-list-<?php echo sanitize_key( $this->name() ); ?>">
							 <input type="hidden" id="cfm-upload-max-files-<?php echo sanitize_key( $this->name() ); ?>" value="<?php echo $max_files; ?>" />
							<?php
							foreach ( $uploaded_items as $index => $attach_id ) {
								if ( is_numeric( $attach_id ) ){
									$download = wp_get_attachment_url( $attach_id );
								} else {
									$download = $attach_id;
								} ?>
								<tr class="cfm-single-variation">
									 <td class="cfm-url-row">
												<input type="text" data-formid="<?php echo $this->form;?>" data-fieldname="<?php echo $this->name();?>" class="cfm-file-value" placeholder="<?php _e( "http://", 'edd_cfm' ); ?>" name="<?php echo $this->name(); ?>[<?php echo esc_attr( $index ); ?>]" value="<?php echo esc_attr( $download ); ?>" />
									 </td>
									 <td class="cfm-url-choose-row">
												<a href="#" class="edd-submit button upload_file_button" data-choose="<?php _e( 'Choose file', 'edd_cfm' ); ?>" data-update="<?php _e( 'Insert file URL', 'edd_cfm' ); ?>">
												<?php echo str_replace( ' ', '&nbsp;', __( 'Choose file', 'edd_cfm' ) ); ?></a>
									 </td>
									 <?php if ( $max_files > 1 || $max_files === 0 ) { ?>
									 <td width="1%" class="cfm-delete-row">
												<a href="#" class="edd-cfm-delete delete">
												<?php _e( '&times;', 'edd_cfm' ); ?></a>
									 </td>
									<?php } ?>
								</tr>
							<?php } ?>
							<tr class="add_new" style="display:none !important;" id="<?php echo sanitize_key( $this->name() ); ?>"></tr>
					</tbody>
					<?php if ( $max_files > 1 || $max_files === 0 ) : ?>
					<tfoot>
						<tr>
							<th colspan="5">
								<a href="#" class="edd-submit button insert-file-row" id="<?php echo sanitize_key( $this->name() ); ?>"><?php _e( 'Add File', 'edd_cfm' ); ?></a>
							</th>
						</tr>
					</tfoot>
					<?php endif; ?>
				</table>
		 	</div> <!-- .cfm-fields -->
		<script type="text/javascript">
		jQuery(document).ready(function($){
			wp.media.controller.Library.prototype.defaults.contentUserSetting=false;
		});
		</script>
		<?php
		$output .= ob_get_clean();
		$output .= '</p>';
		return $output;
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
			foreach( $value as $key => $file ){
				if ( is_numeric( $file ) ){
					$value[ $key ] = wp_get_attachment_url( $file );
				} else {
					$value[ $key ] = $file;
				}
			}
			$value = implode( ", ", $value );
		} else {
			if ( is_numeric( $value ) ){
				$value = wp_get_attachment_url( $value );
			} else {
				// $value is already url (amazon s3)
			}
		}
		return $value;
	}

	/** Returns the HTML to render a field for the formbuilder */
	public function render_formbuilder_field( $index = -2, $insert = false ) {
		$removable    = $this->can_remove_from_formbuilder();
        $max_size_name = sprintf('%s[%d][max_size]', 'cfm_input', $index);
        $max_files_name = sprintf('%s[%d][count]', 'cfm_input', $index);
        $extensions_name = sprintf('%s[%d][extension][]', 'cfm_input', $index);
        $help = esc_attr( __( 'Enter maximum upload size limit in KB', 'edd_cfm' ) );
        $count = esc_attr( __( 'Number of files which can be uploaded', 'edd_cfm' ) );
		$extensions_value = isset( $this->characteristics['extension'] ) ? $this->characteristics['extension'] : false;
		$extensions = cfm_allowed_extensions();
		$max_size_value = isset( $this->characteristics['max_size'] ) ? $this->characteristics['max_size'] : false;
		$max_files_value = isset( $this->characteristics['count'] ) ? $this->characteristics['count'] : false;
		ob_start();
        ?>
        <li class="custom-field file_upload">
			<?php $this->legend( $this->title(), $this->get_label(), $removable ); ?>
			<?php CFM_Formbuilder_Templates::hidden_field( "[$index][template]", $this->template() ); ?>
			<?php CFM_Formbuilder_Templates::field_div( $index, $this->name(), $this->characteristics, $insert ); ?>
				<?php CFM_Formbuilder_Templates::public_radio( $index, $this->characteristics ); ?>
				<?php CFM_Formbuilder_Templates::export_radio( $index, $this->characteristics ); ?>
				<?php CFM_Formbuilder_Templates::meta_type_radio( $index, $this->characteristics ); ?>
				<?php CFM_Formbuilder_Templates::standard( $index, $this ); ?>
				<?php CFM_Formbuilder_Templates::css( $index, $this->characteristics ); ?>

                <div class="cfm-form-rows">
                    <label><?php _e( 'Max. file size', 'edd_cfm' ); ?></label>
                    <input type="text" class="smallipopInput" name="<?php echo $max_size_name; ?>" value="<?php echo $max_size_value; ?>" title="<?php echo $help; ?>">
                </div> <!-- .edd-checkout-fields-rows -->

                <div class="cfm-form-rows">
                    <label><?php _e( 'Max. files', 'edd_cfm' ); ?></label>
                    <input type="text" class="smallipopInput" name="<?php echo $max_files_name; ?>" value="<?php echo $max_files_value; ?>" title="<?php echo $count; ?>">
                </div> <!-- .edd-checkout-fields-rows -->

                <div class="edd-checkout-fields-rows">
                    <label><?php _e( 'Allowed Files', 'edd_cfm' ); ?></label>

                    <div class="edd-checkout-fields-sub-fields">
                        <?php foreach ($extensions as $key => $value) {
                            ?>
                            <label>
                                <input type="checkbox" name="<?php echo $extensions_name; ?>" value="<?php echo $key; ?>"<?php echo $extensions_value && is_array( $extensions_value ) && in_array($key, $extensions_value) ? ' checked="checked"' : ''; ?>>
                                <?php printf('%s (%s)', $value['label'], str_replace( ',', ', ', $value['ext'] ) ) ?>
                            </label> <br />
                        <?php } ?>
                    </div>
                </div> <!-- .edd-checkout-fields-rows -->
            </div> <!-- .edd-checkout-fields-holder -->
        </li>
		<?php
		return ob_get_clean();
	}

	public function validate( $values = array(), $payment_id = -2, $user_id = -2 ) {
		$name = $this->name();
		$return_value = false;
		if ( $this->required() ) {
			if ( !empty( $values[ $name ] ) ) {
				if ( is_array( $values[ $name ] ) ){
					foreach( $values[ $name ] as $key => $file  ){
						if ( filter_var( $file, FILTER_VALIDATE_URL ) === false ) {
							// if that's not a url
							$valid = apply_filters( 'cfm_validate_filter_url_' . $this->template() . '_field', false, $file, $payment_id, $user_id );
							if ( ! $valid ) {
								edd_set_error( 'invalid_' . $this->id, sprintf( __( 'Please enter a valid URL for %s.', 'edd_cfm' ), $this->get_label() ) );
								break;
							}
						}
					}
				} else {
					edd_set_error( 'invalid_' . $this->id, sprintf( __( 'Please enter a value for %s.', 'edd_cfm' ), $this->get_label() ) );
				}
			} else {
				edd_set_error( 'invalid_' . $this->id, sprintf( __( 'Please enter a value for %s.', 'edd_cfm' ), $this->get_label() ) );
			}
		}
	}

	public function sanitize( $values = array(), $payment_id = -2, $user_id = -2 ) {
		$name = $this->name();
		if ( ! empty( $values[ $name ] ) ) {
			if ( is_array( $values[ $name ] ) ){
				foreach( $values[ $name ] as $key => $option  ){
					$values[ $name ][ $key ] = filter_var( trim( $values[ $name ][ $key ] ), FILTER_SANITIZE_URL );
				}
			}
		}
		return apply_filters( 'cfm_sanitize_' . $this->template() . '_field', $values, $name, $payment_id, $user_id );
	}

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

		$meta_type = $this->meta_type();
		if ( $meta_type === 'user' ){
			delete_user_meta( $user_id, $this->id );
			$ids = array();
			foreach ( $value as $file => $url ) {
				if ( empty ( $url ) ) {
					continue;
				}
				$attachment_id = cfm_get_attachment_id_from_url( $url );
				$attachment_id = apply_filters( 'cfm_save_field_admin_file_upload_field_attachment_id', $attachment_id, $url, $meta_type, $payment_id, $user_id, $value, $current_user_id );
				$ids[] = $attachment_id;
			}
			update_user_meta( $user_id, $this->id, $ids );
		} else {
			// payment meta
			delete_post_meta( $payment_id, $this->id );
			$ids = array();
			foreach ( $value as $file => $url ) {
				if ( empty ( $url ) ) {
					continue;
				}
				$attachment_id = cfm_get_attachment_id_from_url( $url );
				$attachment_id = apply_filters( 'cfm_save_field_admin_file_upload_field_attachment_id', $attachment_id, $url, $meta_type, $payment_id, $user_id, $value, $current_user_id );
				$ids[] = $attachment_id;
			}
			update_post_meta( $payment_id, $this->id, $ids );
		}

		$this->value = $value;
		do_action( 'cfm_save_field_after_save_admin', $this, $payment_id, $user_id, $value, $current_user_id );
	}

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

		$meta_type = $this->meta_type();
		if ( $meta_type === 'user' ){
			delete_user_meta( $user_id, $this->id );
			$ids = array();
			foreach ( $value as $file => $url ) {
				if ( empty ( $url ) ) {
					continue;
				}
				$attachment_id = cfm_get_attachment_id_from_url( $url );
				$attachment_id = apply_filters( 'cfm_save_field_frontend_file_upload_field_attachment_id', $attachment_id, $url, $meta_type, $payment_id, $user_id, $value, $current_user_id );
				$ids[] = $attachment_id;
			}
			update_user_meta( $user_id, $this->id, $ids );
		} else {
			// payment meta
			delete_post_meta( $payment_id, $this->id );
			$ids = array();
			foreach ( $value as $file => $url ) {
				if ( empty ( $url ) ) {
					continue;
				}
				$attachment_id = cfm_get_attachment_id_from_url( $url );
				$attachment_id = apply_filters( 'cfm_save_field_frontend_file_upload_field_attachment_id', $attachment_id, $url, $meta_type, $payment_id, $user_id, $value, $current_user_id );
				$ids[] = $attachment_id;
			}
			update_post_meta( $payment_id, $this->id, $ids );
		}

		$this->value = $value;
		do_action( 'cfm_save_field_after_save_frontend', $this, $payment_id, $user_id, $value, $current_user_id );
	}
}
