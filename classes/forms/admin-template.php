<?php
if ( !defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * FES Form builder template
 */
class FES_Admin_Template {

    static $input_name = 'fes_input';

    /**
     * Legend of a form item
     *
     * @param string $title
     * @param array $values
     */
    public static function legend( $title = 'Field Name', $values = array(), $removeable = true ) {
        $field_label = $values ? ': <strong>' . $values['label'] . '</strong>' : '';
        ?>
        <div class="fes-legend" title="<?php _e( 'Click and Drag to rearrange', 'edd_fes'); ?>">
            <div class="fes-label"><?php echo $title . $field_label; ?></div>
            <div class="fes-actions">
				<?php if ($removeable){ ?>
                <a href="#" class="fes-remove"><?php _e( 'Remove', 'edd_fes' ); ?></a>
				<?php } ?>
                <a href="#" class="fes-toggle"><?php _e( 'Toggle', 'edd_fes' ); ?></a>
            </div>
        </div> <!-- .fes-legend -->
        <?php
    }

    /**
     * Common Fields for a input field
     *
     * Contains required, label, meta_key, help text, css class name
     *
     * @param int $id field order
     * @param mixed $field_name_value
     * @param bool $custom_field if it a custom field or not
     * @param array $values saved value
     */
    public static function common( $id, $field_name_value = '', $custom_field = true, $values = array(), $reqtoggle = true ) {
        $tpl = '%s[%d][%s]';
        $required_name = sprintf( $tpl, self::$input_name, $id, 'required' );
        $field_name = sprintf( $tpl, self::$input_name, $id, 'name' );
        $label_name = sprintf( $tpl, self::$input_name, $id, 'label' );
        $is_meta_name = sprintf( $tpl, self::$input_name, $id, 'is_meta' );
        $help_name = sprintf( $tpl, self::$input_name, $id, 'help' );
        $css_name = sprintf( $tpl, self::$input_name, $id, 'css' );
		
        $required = $values && isset($values['required']) ? esc_attr( $values['required'] ) : 'yes';
        $label_value = $values && isset($values['label']) ? esc_attr( $values['label'] ) : '';
        $help_value = $values && isset($values['help'])? esc_textarea( $values['help'] ) : '';
        $css_value = $values && isset($values['css'])? esc_attr( $values['css'] ) : '';

        if ($custom_field && $values) {
            $field_name_value = $values['name'];
        }
		do_action('edd_fes_add_field_to_common_form_element', $tpl, self::$input_name, $id, $values);
        ?>

        <div class="fes-form-rows required-field">
            <label><?php _e( 'Required', 'edd_fes' ); ?></label>

            <?php //self::hidden_field($order_name, ''); ?>
            <div class="fes-form-sub-fields">
                <label><input type="radio" name="<?php echo $required_name; ?>" value="yes"<?php checked( $required, 'yes' ); ?>> <?php _e( 'Yes', 'edd_fes' ); ?> </label>
				<?php if($reqtoggle){ ?>
				<label><input type="radio" name="<?php echo $required_name; ?>" value="no"<?php checked( $required, 'no' ); ?>> <?php _e( 'No', 'edd_fes' ); ?> </label>
				<?php } ?>
			</div>
        </div> <!-- .fes-form-rows -->

        <div class="fes-form-rows">
            <label><?php _e( 'Field Label', 'edd_fes' ); ?></label>
            <input type="text" data-type="label" name="<?php echo $label_name; ?>" value="<?php echo $label_value; ?>" class="smallipopInput" title="<?php _e( 'Enter a title of this field', 'edd_fes' ); ?>">
        </div> <!-- .fes-form-rows -->

        <?php if ( $custom_field ) { ?>
            <div class="fes-form-rows">
                <label><?php _e( 'Meta Key', 'edd_fes' ); ?></label>
                <input type="text" name="<?php echo $field_name; ?>" value="<?php echo $field_name_value; ?>" class="smallipopInput" title="<?php _e( 'Name of the meta key this field will save to', 'edd_fes' ); ?>">
                <input type="hidden" name="<?php echo $is_meta_name; ?>" value="yes">
            </div> <!-- .fes-form-rows -->
        <?php } else { ?>

            <input type="hidden" name="<?php echo $field_name; ?>" value="<?php echo $field_name_value; ?>">
            <input type="hidden" name="<?php echo $is_meta_name; ?>" value="no">

        <?php } ?>

        <div class="fes-form-rows">
            <label><?php _e( 'Help text', 'edd_fes' ); ?></label>
            <textarea name="<?php echo $help_name; ?>" class="smallipopInput" title="<?php _e( 'Give the user some information about this field', 'edd_fes' ); ?>"><?php echo $help_value; ?></textarea>
        </div> <!-- .fes-form-rows -->

        <div class="fes-form-rows">
            <label><?php _e( 'CSS Class Name', 'edd_fes' ); ?></label>
            <input type="text" name="<?php echo $css_name; ?>" value="<?php echo $css_value; ?>" class="smallipopInput" title="<?php _e( 'Add a CSS class name for this field', 'edd_fes' ); ?>">
        </div> <!-- .fes-form-rows -->

        <?php
    }

    /**
     * Common fields for a text area
     *
     * @param int $id
     * @param array $values
     */
    public static function common_text( $id, $values = array() ) {
        $tpl = '%s[%d][%s]';
        $placeholder_name = sprintf( $tpl, self::$input_name, $id, 'placeholder' );
        $default_name = sprintf( $tpl, self::$input_name, $id, 'default' );
        $size_name = sprintf( $tpl, self::$input_name, $id, 'size' );

        $placeholder_value = $values && isset($values['placeholder'])? esc_attr( $values['placeholder'] ) : '';
        $default_value = $values && isset($values['default']) ? esc_attr( $values['default'] ) : '';
        $size_value = $values && isset($values['size']) ? esc_attr( $values['size'] ) : '40';

        ?>
        <div class="fes-form-rows">
            <label><?php _e( 'Placeholder text', 'edd_fes' ); ?></label>
            <input type="text" class="smallipopInput" name="<?php echo $placeholder_name; ?>" title="<?php esc_attr_e( 'Text for HTML5 placeholder attribute', 'edd_fes' ); ?>" value="<?php echo $placeholder_value; ?>" />
        </div> <!-- .fes-form-rows -->

        <div class="fes-form-rows">
            <label><?php _e( 'Default value', 'edd_fes' ); ?></label>
            <input type="text" class="smallipopInput" name="<?php echo $default_name; ?>" title="<?php esc_attr_e( 'The default value this field will have', 'edd_fes' ); ?>" value="<?php echo $default_value; ?>" />
        </div> <!-- .fes-form-rows -->

        <div class="fes-form-rows">
            <label><?php _e( 'Size', 'edd_fes' ); ?></label>
            <input type="text" class="smallipopInput" name="<?php echo $size_name; ?>" title="<?php esc_attr_e( 'Size of this input field', 'edd_fes' ); ?>" value="<?php echo $size_value; ?>" />
        </div> <!-- .fes-form-rows -->
        <?php
    }

    /**
     * Common fields for a textarea
     *
     * @param int $id
     * @param array $values
     */
    public static function common_textarea( $id, $values = array() ) {
        $tpl = '%s[%d][%s]';
        $rows_name = sprintf( $tpl, self::$input_name, $id, 'rows' );
        $cols_name = sprintf( $tpl, self::$input_name, $id, 'cols' );
        $rich_name = sprintf( $tpl, self::$input_name, $id, 'rich' );
        $placeholder_name = sprintf( $tpl, self::$input_name, $id, 'placeholder' );
        $default_name = sprintf( $tpl, self::$input_name, $id, 'default' );

        $rows_value = $values && isset( $values['rows'] )? esc_attr( $values['rows'] ) : '5';
        $cols_value = $values && isset( $values['cols'] )? esc_attr( $values['cols'] ) : '25';
        $rich_value = $values && isset( $values['rich'] )? esc_attr( $values['rich'] ) : 'no';
        $placeholder_value = $values && isset( $values['placeholder'] )? esc_attr( $values['placeholder'] ) : '';
        $default_value = $values && isset( $values['default'] )? esc_attr( $values['default'] ) : '';

        ?>
        <div class="fes-form-rows">
            <label><?php _e( 'Rows', 'edd_fes' ); ?></label>
            <input type="text" class="smallipopInput" name="<?php echo $rows_name; ?>" title="Number of rows in textarea" value="<?php echo $rows_value; ?>" />
        </div> <!-- .fes-form-rows -->

        <div class="fes-form-rows">
            <label><?php _e( 'Columns', 'edd_fes' ); ?></label>
            <input type="text" class="smallipopInput" name="<?php echo $cols_name; ?>" title="Number of columns in textarea" value="<?php echo $cols_value; ?>" />
        </div> <!-- .fes-form-rows -->

        <div class="fes-form-rows">
            <label><?php _e( 'Placeholder text', 'edd_fes' ); ?></label>
            <input type="text" class="smallipopInput" name="<?php echo $placeholder_name; ?>" title="text for HTML5 placeholder attribute" value="<?php echo $placeholder_value; ?>" />
        </div> <!-- .fes-form-rows -->

        <div class="fes-form-rows">
            <label><?php _e( 'Default value', 'edd_fes' ); ?></label>
            <input type="text" class="smallipopInput" name="<?php echo $default_name; ?>" title="the default value this field will have" value="<?php echo $default_value; ?>" />
        </div> <!-- .fes-form-rows -->

        <div class="fes-form-rows">
            <label><?php _e( 'Textarea', 'edd_fes' ); ?></label>

            <div class="fes-form-sub-fields">
                <label><input type="radio" name="<?php echo $rich_name; ?>" value="no"<?php checked( $rich_value, 'no' ); ?>> <?php _e( 'Normal', 'edd_fes' ); ?></label>
                <label><input type="radio" name="<?php echo $rich_name; ?>" value="yes"<?php checked( $rich_value, 'yes' ); ?>> <?php _e( 'Rich textarea', 'edd_fes' ); ?></label>
                <label><input type="radio" name="<?php echo $rich_name; ?>" value="teeny"<?php checked( $rich_value, 'teeny' ); ?>> <?php _e( 'Teeny Rich textarea', 'edd_fes' ); ?></label>
            </div>
        </div> <!-- .fes-form-rows -->
        <?php
    }

    /**
     * Hidden field helper function
     *
     * @param string $name
     * @param string $value
     */
    public static function hidden_field( $name, $value = '' ) {
        printf( '<input type="hidden" name="%s" value="%s" />', self::$input_name . $name, $value );
    }

    /**
     * Displays a radio custom field
     *
     * @param int $field_id
     * @param string $name
     * @param array $values
     */
    function radio_fields( $field_id, $name, $values = array() ) {
        $selected_name = sprintf( '%s[%d][selected]', self::$input_name, $field_id );
        $input_name = sprintf( '%s[%d][%s]', self::$input_name, $field_id, $name );

        $selected_value = ( $values && isset( $values['selected'] ) ) ? $values['selected'] : '';

        if ( $values && $values['options'] > 0 ) {
            foreach ($values['options'] as $key => $value) {
                ?>
                <div>
                    <input type="radio" name="<?php echo $selected_name ?>" value="<?php echo $value; ?>" <?php checked( $selected_value, $value ); ?>>
                    <input type="text" name="<?php echo $input_name; ?>[]" value="<?php echo $value; ?>">

                    <?php self::remove_button(); ?>
                </div>
                <?php
            }
        } else {
        ?>
            <div>
                <input type="radio" name="<?php echo $selected_name ?>">
                <input type="text" name="<?php echo $input_name; ?>[]" value="">

                <?php self::remove_button(); ?>
            </div>
        <?php
        }
    }

    /**
     * Displays a checkbox custom field
     *
     * @param int $field_id
     * @param string $name
     * @param array $values
     */
    function common_checkbox( $field_id, $name, $values = array() ) {
        $selected_name = sprintf( '%s[%d][selected]', self::$input_name, $field_id );
        $input_name = sprintf( '%s[%d][%s]', self::$input_name, $field_id, $name );

        $selected_value = ( $values && isset( $values['selected'] ) ) ? $values['selected'] : array();

        if ( $values && $values['options'] > 0 ) {
            foreach ($values['options'] as $key => $value) {
                ?>
                <div>
                    <input type="checkbox" name="<?php echo $selected_name ?>[]" value="<?php echo $value; ?>"<?php echo in_array($value, $selected_value) ? ' checked="checked"' : ''; ?> />
                    <input type="text" name="<?php echo $input_name; ?>[]" value="<?php echo $value; ?>">

                    <?php self::remove_button(); ?>
                </div>
                <?php
            }
        } else {
        ?>
            <div>
                <input type="checkbox" name="<?php echo $selected_name ?>[]">
                <input type="text" name="<?php echo $input_name; ?>[]" value="">

                <?php self::remove_button(); ?>
            </div>
        <?php
        }
    }

    /**
     * Add/remove buttons for repeatable fields
     *
     * @return void
     */
    public static function remove_button() {
        $add = fes_assets_url .'img/add.png';
        $remove = fes_assets_url. 'img/remove.png';
        ?>
        <img style="cursor:pointer; margin:0 3px;" alt="add another choice" title="add another choice" class="fes-clone-field" src="<?php echo $add; ?>">
        <img style="cursor:pointer;" class="fes-remove-field" alt="remove this choice" title="remove this choice" src="<?php echo $remove; ?>">
        <?php
    }

    public static function get_buffered($func, $field_id, $label) {
        ob_start();

        self::$func( $field_id, $label );

        return ob_get_clean();
    }

    public static function text_field( $field_id, $label, $values = array(), $removeable = true, $reqtoggle = true ) {
        ?>
        <li class="custom-field text_field">
            <?php self::legend( $label, $values, $removeable ); ?>
            <?php self::hidden_field( "[$field_id][input_type]", 'text' ); ?>
            <?php self::hidden_field( "[$field_id][template]", 'text_field' ); ?>

            <div class="fes-form-holder">
                <?php self::common( $field_id, '', true, $values, $reqtoggle ); ?>
                <?php self::common_text( $field_id, $values ); ?>
            </div> <!-- .fes-form-holder -->
        </li>
        <?php
    }

    public static function textarea_field( $field_id, $label, $values = array(), $removeable = true, $reqtoggle = true  ) {
        ?>
        <li class="custom-field textarea_field">
            <?php self::legend( $label, $values, $removeable ); ?>
            <?php self::hidden_field( "[$field_id][input_type]", 'textarea' ); ?>
            <?php self::hidden_field( "[$field_id][template]", 'textarea_field' ); ?>

            <div class="fes-form-holder">
                <?php self::common( $field_id, '', true, $values, $reqtoggle ); ?>
                <?php self::common_textarea( $field_id, $values ); ?>
            </div> <!-- .fes-form-holder -->
        </li>
        <?php
    }

    public static function radio_field( $field_id, $label, $values = array(), $removeable = true, $reqtoggle = true  ) {
        ?>
        <li class="custom-field radio_field">
            <?php self::legend( $label, $values, $removeable ); ?>
            <?php self::hidden_field( "[$field_id][input_type]", 'radio' ); ?>
            <?php self::hidden_field( "[$field_id][template]", 'radio_field' ); ?>

            <div class="fes-form-holder">
                <?php self::common( $field_id, '', true, $values, $reqtoggle ); ?>

                <div class="fes-form-rows">
                    <label><?php _e( 'Options', 'edd_fes' ); ?></label>

                    <div class="fes-form-sub-fields">
                        <?php self::radio_fields( $field_id, 'options', $values ); ?>
                    </div> <!-- .fes-form-sub-fields -->
                </div> <!-- .fes-form-rows -->
            </div> <!-- .fes-form-holder -->
        </li>
        <?php
    }

    public static function checkbox_field( $field_id, $label, $values = array(), $removeable = true, $reqtoggle = true  ) {
        ?>
        <li class="custom-field checkbox_field">
            <?php self::legend( $label, $values, $removeable ); ?>
            <?php self::hidden_field( "[$field_id][input_type]", 'checkbox' ); ?>
            <?php self::hidden_field( "[$field_id][template]", 'checkbox_field' ); ?>

            <div class="fes-form-holder">
                <?php self::common( $field_id, '', true, $values, $reqtoggle ); ?>

                <div class="fes-form-rows">
                    <label><?php _e( 'Options', 'edd_fes' ); ?></label>

                    <div class="fes-form-sub-fields">
                        <?php self::common_checkbox( $field_id, 'options', $values ); ?>
                    </div> <!-- .fes-form-sub-fields -->
                </div> <!-- .fes-form-rows -->
            </div> <!-- .fes-form-holder -->
        </li>
        <?php
    }

    public static function dropdown_field( $field_id, $label, $values = array(), $removeable = true, $reqtoggle = true  ) {
        $first_name = sprintf('%s[%d][first]', self::$input_name, $field_id);
        $first_value = $values ? $values['first'] : ' - select -';
        $help = esc_attr( __( 'First element of the select dropdown. Leave this empty if you don\'t want to show this field', 'edd_fes' ) );
        ?>
        <li class="custom-field dropdown_field">
            <?php self::legend( $label, $values, $removeable ); ?>
            <?php self::hidden_field( "[$field_id][input_type]", 'select' ); ?>
            <?php self::hidden_field( "[$field_id][template]", 'dropdown_field' ); ?>

            <div class="fes-form-holder">
                <?php self::common( $field_id, '', true, $values, $reqtoggle ); ?>

                <div class="fes-form-rows">
                    <label><?php _e( 'Select Text', 'edd_fes' ); ?></label>
                    <input type="text" class="smallipopInput" name="<?php echo $first_name; ?>" value="<?php echo $first_value; ?>" title="<?php echo $help; ?>">
                </div> <!-- .fes-form-rows -->

                <div class="fes-form-rows">
                    <label><?php _e( 'Options', 'edd_fes' ); ?></label>

                    <div class="fes-form-sub-fields">
                        <?php self::radio_fields( $field_id, 'options', $values ); ?>
                    </div> <!-- .fes-form-sub-fields -->
                </div> <!-- .fes-form-rows -->
            </div> <!-- .fes-form-holder -->
        </li>
        <?php
    }

    public static function multiple_select( $field_id, $label, $values = array(), $removeable = true, $reqtoggle = true  ) {
        $first_name = sprintf('%s[%d][first]', self::$input_name, $field_id);
        $first_value = $values ? $values['first'] : ' - select -';
        $help = esc_attr( __( 'First element of the select dropdown. Leave this empty if you don\'t want to show this field', 'edd_fes' ) );
        ?>
        <li class="custom-field multiple_select">
            <?php self::legend( $label, $values, $removeable ); ?>
            <?php self::hidden_field( "[$field_id][input_type]", 'multiselect' ); ?>
            <?php self::hidden_field( "[$field_id][template]", 'multiple_select' ); ?>

            <div class="fes-form-holder">
                <?php self::common( $field_id, '', true, $values, $reqtoggle ); ?>

                <div class="fes-form-rows">
                    <label><?php _e( 'Select Text', 'edd_fes' ); ?></label>
                    <input type="text" class="smallipopInput" name="<?php echo $first_name; ?>" value="<?php echo $first_value; ?>" title="<?php echo $help; ?>">
                </div> <!-- .fes-form-rows -->

                <div class="fes-form-rows">
                    <label><?php _e( 'Options', 'edd_fes' ); ?></label>

                    <div class="fes-form-sub-fields">
                        <?php self::radio_fields( $field_id, 'options', $values ); ?>
                    </div> <!-- .fes-form-sub-fields -->
                </div> <!-- .fes-form-rows -->
            </div> <!-- .fes-form-holder -->
        </li>
        <?php
    }

    public static function image_upload( $field_id, $label, $values = array(), $removeable = true, $reqtoggle = true  ) {
        $max_size_name = sprintf('%s[%d][max_size]', self::$input_name, $field_id);
        $max_files_name = sprintf('%s[%d][count]', self::$input_name, $field_id);

        $max_size_value = $values ? $values['max_size'] : '1024';
        $max_files_value = $values ? $values['count'] : '1';

        $help = esc_attr( __( 'Enter maximum upload size limit in KB', 'edd_fes' ) );
        $count = esc_attr( __( 'Number of images can be uploaded', 'edd_fes' ) );
        ?>
        <li class="custom-field image_upload">
            <?php self::legend( $label, $values, $removeable ); ?>
            <?php self::hidden_field( "[$field_id][input_type]", 'image_upload' ); ?>
            <?php self::hidden_field( "[$field_id][template]", 'image_upload' ); ?>

            <div class="fes-form-holder">
                <?php self::common( $field_id, '', true, $values, $reqtoggle ); ?>

                <div class="fes-form-rows">
                    <label><?php _e( 'Max. file size', 'edd_fes' ); ?></label>
                    <input type="text" class="smallipopInput" name="<?php echo $max_size_name; ?>" value="<?php echo $max_size_value; ?>" title="<?php echo $help; ?>">
                </div> <!-- .fes-form-rows -->

                <div class="fes-form-rows">
                    <label><?php _e( 'Max. files', 'edd_fes' ); ?></label>
                    <input type="text" class="smallipopInput" name="<?php echo $max_files_name; ?>" value="<?php echo $max_files_value; ?>" title="<?php echo $count; ?>">
                </div> <!-- .fes-form-rows -->
            </div> <!-- .fes-form-holder -->
        </li>
        <?php
    }

     public static function prices_and_files( $id, $values = array() ) {
        $tpl = '%s[%d][%s]';
		$single_name = sprintf( $tpl, self::$input_name, $id, 'single' );
        $prices_name = sprintf( $tpl, self::$input_name, $id, 'prices' );
        $files_name = sprintf( $tpl, self::$input_name, $id, 'files' );
        $single = $values && isset($values['single']) ? esc_attr( $values['single'] ) : 'no';
        $prices = $values && isset($values['prices']) ? esc_attr( $values['prices'] ) : 'yes';
        $files = $values && isset($values['files']) ? esc_attr( $values['files'] ) : 'yes';
        ?>

        <div class="fes-form-rows required-field">
            <label><?php _e( 'Single Price/Upload', 'edd_fes' ); ?></label>

            <?php //self::hidden_field($order_name, ''); ?>
            <div class="fes-form-sub-fields">
                <label><input type="radio" name="<?php echo $single_name; ?>" value="yes"<?php checked( $single, 'yes' ); ?>> <?php _e( 'Yes', 'edd_fes' ); ?> </label>
				<label><input type="radio" name="<?php echo $single_name; ?>" value="no"<?php checked( $single, 'no' ); ?>> <?php _e( 'No', 'edd_fes' ); ?> </label>
			</div>
        </div> <!-- .fes-form-rows -->

        <div class="fes-form-rows required-field">
            <label><?php _e( 'Allow Vendors to Set Prices', 'edd_fes' ); ?></label>

            <?php //self::hidden_field($order_name, ''); ?>
            <div class="fes-form-sub-fields">
                <label><input type="radio" name="<?php echo $prices_name; ?>" value="yes"<?php checked( $prices, 'yes' ); ?>> <?php _e( 'Yes', 'edd_fes' ); ?> </label>
				<label><input type="radio" name="<?php echo $prices_name; ?>" value="no"<?php checked( $prices, 'no' ); ?>> <?php _e( 'No', 'edd_fes' ); ?> </label>
			</div>
        </div> <!-- .fes-form-rows -->

        <div class="fes-form-rows required-field">
            <label><?php _e( 'Allow Vendors to Upload Downloads', 'edd_fes' ); ?></label>

            <?php //self::hidden_field($order_name, ''); ?>
            <div class="fes-form-sub-fields">
                <label><input type="radio" name="<?php echo $files_name; ?>" value="yes"<?php checked( $files, 'yes' ); ?>> <?php _e( 'Yes', 'edd_fes' ); ?> </label>
				<label><input type="radio" name="<?php echo $files_name; ?>" value="no"<?php checked( $files, 'no' ); ?>> <?php _e( 'No', 'edd_fes' ); ?> </label>
			</div>
        </div> <!-- .fes-form-rows -->
        <?php
    }
	
	public static function file_upload( $field_id, $label, $values = array(), $removeable = true, $reqtoggle = true  ) {
        $max_size_name = sprintf('%s[%d][max_size]', self::$input_name, $field_id);
        $max_files_name = sprintf('%s[%d][count]', self::$input_name, $field_id);
        $extensions_name = sprintf('%s[%d][extension][]', self::$input_name, $field_id);

        $max_size_value = $values ? $values['max_size'] : '1024';
        $max_files_value = $values ? $values['count'] : '1';
        $extensions_value = $values ? $values['extension'] : array('images', 'audio', 'video', 'pdf', 'office', 'zip', 'exe', 'csv');

        $extensions = fes_allowed_extensions();

        $help = esc_attr( __( 'Enter maximum upload size limit in KB', 'edd_fes' ) );
        $count = esc_attr( __( 'Number of images can be uploaded', 'edd_fes' ) );
        ?>
        <li class="custom-field custom_image">
            <?php self::legend( $label, $values, $removeable ); ?>
            <?php self::hidden_field( "[$field_id][input_type]", 'file_upload' ); ?>
            <?php self::hidden_field( "[$field_id][template]", 'file_upload' ); ?>

            <div class="fes-form-holder">
                <?php self::common( $field_id, '', true, $values, $reqtoggle ); ?>

                <div class="fes-form-rows">
                    <label><?php _e( 'Max. file size', 'edd_fes' ); ?></label>
                    <input type="text" class="smallipopInput" name="<?php echo $max_size_name; ?>" value="<?php echo $max_size_value; ?>" title="<?php echo $help; ?>">
                </div> <!-- .fes-form-rows -->

                <div class="fes-form-rows">
                    <label><?php _e( 'Max. files', 'edd_fes' ); ?></label>
                    <input type="text" class="smallipopInput" name="<?php echo $max_files_name; ?>" value="<?php echo $max_files_value; ?>" title="<?php echo $count; ?>">
                </div> <!-- .fes-form-rows -->

                <div class="fes-form-rows">
                    <label><?php _e( 'Allowed Files', 'edd_fes' ); ?></label>

                    <div class="fes-form-sub-fields">
                        <?php foreach ($extensions as $key => $value) {
                            ?>
                            <label>
                                <input type="checkbox" name="<?php echo $extensions_name; ?>" value="<?php echo $key; ?>"<?php echo in_array($key, $extensions_value) ? ' checked="checked"' : ''; ?>>
                                <?php printf('%s (%s)', $value['label'], str_replace( ',', ', ', $value['ext'] ) ) ?>
                            </label> <br />
                        <?php } ?>
                    </div>
                </div> <!-- .fes-form-rows -->
            </div> <!-- .fes-form-holder -->
        </li>
        <?php
    }

    public static function website_url( $field_id, $label, $values = array(), $removeable = true, $reqtoggle = true  ) {
        ?>
        <li class="custom-field website_url">
            <?php self::legend( $label, $values, $removeable ); ?>
            <?php self::hidden_field( "[$field_id][input_type]", 'url' ); ?>
            <?php self::hidden_field( "[$field_id][template]", 'website_url' ); ?>

            <div class="fes-form-holder">
                <?php self::common( $field_id, '', true, $values, $reqtoggle ); ?>
                <?php self::common_text( $field_id, $values ); ?>
            </div> <!-- .fes-form-holder -->
        </li>
        <?php
    }

    public static function email_address( $field_id, $label, $values = array(), $removeable = true, $reqtoggle = true  ) {
        ?>
        <li class="custom-field eamil_address">
            <?php self::legend( $label, $values, $removeable ); ?>
            <?php self::hidden_field( "[$field_id][input_type]", 'email' ); ?>
            <?php self::hidden_field( "[$field_id][template]", 'email_address' ); ?>

            <div class="fes-form-holder">
                <?php self::common( $field_id, '', true, $values, $reqtoggle ); ?>
                <?php self::common_text( $field_id, $values ); ?>
            </div> <!-- .fes-form-holder -->
        </li>
        <?php
    }

    public static function repeat_field( $field_id, $label, $values = array(), $removeable = true, $reqtoggle = true  ) {
        $tpl = '%s[%d][%s]';

        $enable_column_name = sprintf( '%s[%d][multiple]', self::$input_name, $field_id );
        $column_names = sprintf( '%s[%d][columns]', self::$input_name, $field_id );
        $has_column = ( $values && isset( $values['multiple'] ) ) ? true : false;

        $placeholder_name = sprintf( $tpl, self::$input_name, $field_id, 'placeholder' );
        $default_name = sprintf( $tpl, self::$input_name, $field_id, 'default' );
        $size_name = sprintf( $tpl, self::$input_name, $field_id, 'size' );

        $placeholder_value = $values ? esc_attr( $values['placeholder'] ) : '';
        $default_value = $values ? esc_attr( $values['default'] ) : '';
        $size_value = $values ? esc_attr( $values['size'] ) : '40';

        ?>
        <li class="custom-field custom_repeater">
            <?php self::legend( $label, $values, $removeable ); ?>
            <?php self::hidden_field( "[$field_id][input_type]", 'repeat' ); ?>
            <?php self::hidden_field( "[$field_id][template]", 'repeat_field' ); ?>

            <div class="fes-form-holder">
                <?php self::common( $field_id, '', true, $values, $reqtoggle ); ?>

                <div class="fes-form-rows">
                    <label><?php _e( 'Multiple Column', 'edd_fes' ); ?></label>

                    <div class="fes-form-sub-fields">
                        <label><input type="checkbox" class="multicolumn" name="<?php echo $enable_column_name ?>"<?php echo $has_column ? ' checked="checked"' : ''; ?> value="true"> Enable Multi Column</label>
                    </div>
                </div>

                <div class="fes-form-rows<?php echo $has_column ? ' fes-hide' : ''; ?>">
                    <label><?php _e( 'Placeholder text', 'edd_fes' ); ?></label>
                    <input type="text" class="smallipopInput" name="<?php echo $placeholder_name; ?>" title="text for HTML5 placeholder attribute" value="<?php echo $placeholder_value; ?>" />
                </div> <!-- .fes-form-rows -->

                <div class="fes-form-rows<?php echo $has_column ? ' fes-hide' : ''; ?>">
                    <label><?php _e( 'Default value', 'edd_fes' ); ?></label>
                    <input type="text" class="smallipopInput" name="<?php echo $default_name; ?>" title="the default value this field will have" value="<?php echo $default_value; ?>" />
                </div> <!-- .fes-form-rows -->

                <div class="fes-form-rows">
                    <label><?php _e( 'Size', 'edd_fes' ); ?></label>
                    <input type="text" class="smallipopInput" name="<?php echo $size_name; ?>" title="Size of this input field" value="<?php echo $size_value; ?>" />
                </div> <!-- .fes-form-rows -->

                <div class="fes-form-rows column-names<?php echo $has_column ? '' : ' fes-hide'; ?>">
                    <label><?php _e( 'Columns', 'edd_fes' ); ?></label>

                    <div class="fes-form-sub-fields">
                    <?php

                        if ( $values && $values['columns'] > 0 ) {
                            foreach ($values['columns'] as $key => $value) {
                                ?>
                                <div>
                                    <input type="text" name="<?php echo $column_names; ?>[]" value="<?php echo $value; ?>">

                                    <?php self::remove_button(); ?>
                                </div>
                                <?php
                            }
                        } else {
                        ?>
                            <div>
                                <input type="text" name="<?php echo $column_names; ?>[]" value="">

                                <?php self::remove_button(); ?>
                            </div>
                        <?php
                        }
                    ?>
                    </div>
                </div> <!-- .fes-form-rows -->
            </div> <!-- .fes-form-holder -->
        </li>
        <?php
    }

    public static function custom_html( $field_id, $label, $values = array(), $removeable = true, $reqtoggle = true  ) {
        $title_name = sprintf( '%s[%d][label]', self::$input_name, $field_id );
        $html_name = sprintf( '%s[%d][html]', self::$input_name, $field_id );

        $title_value = $values ? esc_attr( $values['label'] ) : '';
        $html_value = $values ? esc_attr( $values['html'] ) : '';
        ?>
        <li class="custom-field custom_html">
            <?php self::legend( $label, $values, $removeable ); ?>
            <?php self::hidden_field( "[$field_id][input_type]", 'html' ); ?>
            <?php self::hidden_field( "[$field_id][template]", 'custom_html' ); ?>

            <div class="fes-form-holder">
                <div class="fes-form-rows">
                    <label><?php _e( 'Title', 'edd_fes' ); ?></label>
                    <input type="text" class="smallipopInput" title="Title of the section" name="<?php echo $title_name; ?>" value="<?php echo esc_attr( $title_value ); ?>" />
                </div> <!-- .fes-form-rows -->

                <div class="fes-form-rows">
                    <label><?php _e( 'HTML Codes', 'edd_fes' ); ?></label>
                    <textarea class="smallipopInput" title="Paste your HTML codes, WordPress shortcodes will also work here" name="<?php echo $html_name; ?>" rows="10"><?php echo esc_html( $html_value ); ?></textarea>
                </div>
            </div> <!-- .fes-form-holder -->
        </li>
        <?php
    }

    public static function custom_hidden_field( $field_id, $label, $values = array(), $removeable = true, $reqtoggle = true  ) {
        $meta_name = sprintf( '%s[%d][name]', self::$input_name, $field_id );
        $value_name = sprintf( '%s[%d][meta_value]', self::$input_name, $field_id );
        $is_meta_name = sprintf( '%s[%d][is_meta]', self::$input_name, $field_id );
        $label_name = sprintf( '%s[%d][label]', self::$input_name, $field_id );

        $meta_value = $values ? esc_attr( $values['name'] ) : '';
        $value_value = $values ? esc_attr( $values['meta_value'] ) : '';
        ?>
        <li class="custom-field custom_hidden_field">
            <?php self::legend( $label, $values, $removeable ); ?>
            <?php self::hidden_field( "[$field_id][input_type]", 'hidden' ); ?>
            <?php self::hidden_field( "[$field_id][template]", 'custom_hidden_field' ); ?>

            <div class="fes-form-holder">
                <div class="fes-form-rows">
                    <label><?php _e( 'Meta Key', 'edd_fes' ); ?></label>
                    <input type="text" name="<?php echo $meta_name; ?>" value="<?php echo $meta_value; ?>" class="smallipopInput" title="<?php _e( 'Name of the meta key this field will save to', 'edd_fes' ); ?>">
                    <input type="hidden" name="<?php echo $is_meta_name; ?>" value="yes">
                    <input type="hidden" name="<?php echo $label_name; ?>" value="">
                </div> <!-- .fes-form-rows -->

                <div class="fes-form-rows">
                    <label><?php _e( 'Meta Value', 'edd_fes' ); ?></label>
                    <input type="text" class="smallipopInput" title="<?php esc_attr_e( 'Enter the meta value', 'edd_fes' ); ?>" name="<?php echo $value_name; ?>" value="<?php echo $value_value; ?>">
                </div>
            </div> <!-- .fes-form-holder -->
        </li>
        <?php
    }

    public static function section_break( $field_id, $label, $values = array(), $removeable = true, $reqtoggle = true  ) {
        $title_name = sprintf( '%s[%d][label]', self::$input_name, $field_id );
        $description_name = sprintf( '%s[%d][description]', self::$input_name, $field_id );

        $title_value = $values ? esc_attr( $values['label'] ) : '';
        $description_value = $values ? esc_attr( $values['description'] ) : '';
        ?>
        <li class="custom-field custom_html">
            <?php self::legend( $label, $values, $removeable ); ?>
            <?php self::hidden_field( "[$field_id][input_type]", 'section_break' ); ?>
            <?php self::hidden_field( "[$field_id][template]", 'section_break' ); ?>

            <div class="fes-form-holder">
                <div class="fes-form-rows">
                    <label><?php _e( 'Title', 'edd_fes' ); ?></label>
                    <input type="text" class="smallipopInput" title="Title of the section" name="<?php echo $title_name; ?>" value="<?php echo esc_attr( $title_value ); ?>" />
                </div> <!-- .fes-form-rows -->

                <div class="fes-form-rows">
                    <label><?php _e( 'Description', 'edd_fes' ); ?></label>
                    <textarea class="smallipopInput" title="Some details text about the section" name="<?php echo $description_name; ?>" rows="3"><?php echo esc_html( $description_value ); ?></textarea>
                </div> <!-- .fes-form-rows -->
            </div> <!-- .fes-form-holder -->
        </li>
        <?php
    }

    public static function recaptcha( $field_id, $label, $values = array(), $removeable = true, $reqtoggle = true  ) {
        $title_name = sprintf( '%s[%d][label]', self::$input_name, $field_id );
        $html_name = sprintf( '%s[%d][html]', self::$input_name, $field_id );

        $title_value = $values ? esc_attr( $values['label'] ) : '';
        $html_value = $values ? esc_attr( $values['html'] ) : '';
        ?>
        <li class="custom-field custom_html">
            <?php self::legend( $label, $values, $removeable ); ?>
            <?php self::hidden_field( "[$field_id][input_type]", 'recaptcha' ); ?>
            <?php self::hidden_field( "[$field_id][template]", 'recaptcha' ); ?>

            <div class="fes-form-holder">
                <div class="fes-form-rows">
                    <label><?php _e( 'Title', 'edd_fes' ); ?></label>

                    <div class="fes-form-sub-fields">
                        <input type="text" class="smallipopInput" title="Title of the section" name="<?php echo $title_name; ?>" value="<?php echo esc_attr( $title_value ); ?>" />

                        <div class="description" style="margin-top: 8px;">
                            <?php printf( __( "Insert your public key and private key in <a href='%s'>plugin settings</a>. <a href='https://www.google.com/recaptcha/' target='_blank'>Register</a> first if you don't have any keys." ), admin_url( 'admin.php?page=edd_fes' ) ); ?>
                        </div>
                    </div> <!-- .fes-form-rows -->
                </div>
            </div> <!-- .fes-form-holder -->
        </li>
        <?php
    }

    public static function really_simple_captcha( $field_id, $label, $values = array(), $removeable = true, $reqtoggle = true  ) {
        $title_name = sprintf( '%s[%d][label]', self::$input_name, $field_id );
        $html_name = sprintf( '%s[%d][html]', self::$input_name, $field_id );

        $title_value = $values ? esc_attr( $values['label'] ) : '';
        $html_value = $values ? esc_attr( $values['html'] ) : '';
        ?>
        <li class="custom-field custom_html">
            <?php self::legend( $label, $values, $removeable ); ?>
            <?php self::hidden_field( "[$field_id][input_type]", 'really_simple_captcha' ); ?>
            <?php self::hidden_field( "[$field_id][template]", 'really_simple_captcha' ); ?>

            <div class="fes-form-holder">
                <div class="fes-form-rows">
                    <label><?php _e( 'Title', 'edd_fes' ); ?></label>

                    <div class="fes-form-sub-fields">
                        <input type="text" class="smallipopInput" title="Title of the section" name="<?php echo $title_name; ?>" value="<?php echo esc_attr( $title_value ); ?>" />

                        <div class="description" style="margin-top: 8px;">
                            <?php printf( __( "Depends on <a href='http://wordpress.org/extend/plugins/really-simple-captcha/' target='_blank'>Really Simple Captcha</a> Plugin. Install it first." )  ); ?>
                        </div>
                    </div> <!-- .fes-form-rows -->
                </div>
            </div> <!-- .fes-form-holder -->
        </li>
        <?php
    }

    public static function action_hook( $field_id, $label, $values = array(), $removeable = true, $reqtoggle = true  ) {
        $title_name = sprintf( '%s[%d][label]', self::$input_name, $field_id );
        $title_value = $values ? esc_attr( $values['label'] ) : '';
        ?>
        <li class="custom-field custom_html">
            <?php self::legend( $label, $values, $removeable ); ?>
            <?php self::hidden_field( "[$field_id][input_type]", 'action_hook' ); ?>
            <?php self::hidden_field( "[$field_id][template]", 'action_hook' ); ?>

            <div class="fes-form-holder">
                <div class="fes-form-rows">
                    <label><?php _e( 'Hook Name', 'edd_fes' ); ?></label>

                    <div class="fes-form-sub-fields">
                        <input type="text" class="smallipopInput" title="<?php _e( 'Name of the hook', 'edd_fes' ); ?>" name="<?php echo $title_name; ?>" value="<?php echo esc_attr( $title_value ); ?>" />

                        <div class="description" style="margin-top: 8px;">
                            <?php _e( "An option for developers to add dynamic elements they want. It provides the chance to add whatever input type you want to add in this form.", 'edd_fes' ); ?>
                            <?php _e( 'This way, you can bind your own functions to render the form to this action hook. You\'ll be given 3 parameters to play with: $form_id, $post_id, $form_settings.', 'edd_fes' ); ?>
<pre>
add_action('HOOK_NAME', 'your_function_name', 10, 3 );
function your_function_name( $form_id, $post_id, $form_settings ) {
    // do what ever you want
}
</pre>
                        </div>
                    </div> <!-- .fes-form-rows -->
                </div>
            </div> <!-- .fes-form-holder -->
        </li>
        <?php
    }

    public static function date_field( $field_id, $label, $values = array(), $removeable = true, $reqtoggle = true  ) {
        $format_name = sprintf('%s[%d][format]', self::$input_name, $field_id);
        $time_name = sprintf('%s[%d][time]', self::$input_name, $field_id);

        $format_value = $values ? $values['format'] : 'dd/mm/yy';
        $time_value = $values ? $values['time'] : 'no';

        $help = esc_attr( __( 'The date format', 'edd_fes' ) );
        ?>
        <li class="custom-field custom_image">
            <?php self::legend( $label, $values, $removeable ); ?>
            <?php self::hidden_field( "[$field_id][input_type]", 'date' ); ?>
            <?php self::hidden_field( "[$field_id][template]", 'date_field' ); ?>

            <div class="fes-form-holder">
                <?php self::common( $field_id, '', true, $values, $reqtoggle ); ?>

                <div class="fes-form-rows">
                    <label><?php _e( 'Date Format', 'edd_fes' ); ?></label>
                    <input type="text" class="smallipopInput" name="<?php echo $format_name; ?>" value="<?php echo $format_value; ?>" title="<?php echo $help; ?>">
                </div> <!-- .fes-form-rows -->

                <div class="fes-form-rows">
                    <label><?php _e( 'Time', 'edd_fes' ); ?></label>

                    <div class="fes-form-sub-fields">
                        <label>
                            <?php self::hidden_field( "[$field_id][time]", 'no' ); ?>
                            <input type="checkbox" name="<?php echo $time_name ?>" value="yes"<?php checked( $time_value, 'yes' ); ?> />
                            <?php _e( 'Enable time input', 'edd_fes' ); ?>
                        </label>
                    </div>
                </div> <!-- .fes-form-rows -->
            </div> <!-- .fes-form-holder -->
        </li>
        <?php
    }

    public static function google_map( $field_id, $label, $values = array(), $removeable = true, $reqtoggle = true  ) {
        $zoom_name = sprintf('%s[%d][zoom]', self::$input_name, $field_id);
        $address_name = sprintf('%s[%d][address]', self::$input_name, $field_id);
        $default_pos_name = sprintf('%s[%d][default_pos]', self::$input_name, $field_id);
        $show_lat_name = sprintf('%s[%d][show_lat]', self::$input_name, $field_id);

        $zoom_value = $values ? $values['zoom'] : '12';
        $address_value = $values ? $values['address'] : 'yes';
        $show_lat_value = $values ? $values['show_lat'] : 'no';
        $default_pos_value = $values ? $values['default_pos'] : '40.7143528,-74.0059731';

        $zoom_help = esc_attr( __( 'Set the map zoom level', 'edd_fes' ) );
        $pos_help = esc_attr( __( 'Enter default latitude and longitude to center the map', 'edd_fes' ) );
        ?>
        <li class="custom-field custom_image">
            <?php self::legend( $label, $values, $removeable ); ?>
            <?php self::hidden_field( "[$field_id][input_type]", 'map' ); ?>
            <?php self::hidden_field( "[$field_id][template]", 'google_map' ); ?>

            <div class="fes-form-holder">
                <?php self::common( $field_id, '', true, $values, $reqtoggle ); ?>

                <div class="fes-form-rows">
                    <label><?php _e( 'Zoom Level', 'edd_fes' ); ?></label>
                    <input type="text" class="smallipopInput" name="<?php echo $zoom_name; ?>" value="<?php echo $zoom_value; ?>" title="<?php echo $zoom_help; ?>">
                </div> <!-- .fes-form-rows -->

                <div class="fes-form-rows">
                    <label><?php _e( 'Default Co-ordinate', 'edd_fes' ); ?></label>
                    <input type="text" class="smallipopInput" name="<?php echo $default_pos_name; ?>" value="<?php echo $default_pos_value; ?>" title="<?php echo $pos_help; ?>">
                </div> <!-- .fes-form-rows -->

                <div class="fes-form-rows">
                    <label><?php _e( 'Address Button', 'edd_fes' ); ?></label>

                    <div class="fes-form-sub-fields">
                        <label>
                            <?php self::hidden_field( "[$field_id][address]", 'no' ); ?>
                            <input type="checkbox" name="<?php echo $address_name ?>" value="yes"<?php checked( $address_value, 'yes' ); ?> />
                            <?php _e( 'Show address find button', 'edd_fes' ); ?>
                        </label>
                    </div>
                </div> <!-- .fes-form-rows -->

                <div class="fes-form-rows">
                    <label><?php _e( 'Show Latitude/Longitude', 'edd_fes' ); ?></label>

                    <div class="fes-form-sub-fields">
                        <label>
                            <?php self::hidden_field( "[$field_id][show_lat]", 'no' ); ?>
                            <input type="checkbox" name="<?php echo $show_lat_name ?>" value="yes"<?php checked( $show_lat_value, 'yes' ); ?> />
                            <?php _e( 'Show latitude and longitude input box value', 'edd_fes' ); ?>
                        </label>
                    </div>
                </div> <!-- .fes-form-rows -->
            </div> <!-- .fes-form-holder -->
        </li>
        <?php
    }

    public static function toc( $field_id, $label, $values = array(), $removeable = true, $reqtoggle = true  ) {
        $title_name = sprintf( '%s[%d][label]', self::$input_name, $field_id );
        $description_name = sprintf( '%s[%d][description]', self::$input_name, $field_id );

        $title_value = $values ? esc_attr( $values['label'] ) : '';
        $description_value = $values ? esc_attr( $values['description'] ) : '';
        ?>
        <li class="custom-field custom_html">
            <?php self::legend( $label, $values, $removeable ); ?>
            <?php self::hidden_field( "[$field_id][input_type]", 'toc' ); ?>
            <?php self::hidden_field( "[$field_id][template]", 'toc' ); ?>

            <div class="fes-form-holder">
                <div class="fes-form-rows">
                    <label><?php _e( 'Label', 'edd_fes' ); ?></label>
                    <input type="text" name="<?php echo $title_name; ?>" value="<?php echo esc_attr( $title_value ); ?>" />
                </div> <!-- .fes-form-rows -->

                <div class="fes-form-rows">
                    <label><?php _e( 'Terms & Conditions', 'edd_fes' ); ?></label>
                    <textarea class="smallipopInput" title="<?php _e( 'Insert terms and condtions here.', 'edd_fes'); ?>" name="<?php echo $description_name; ?>" rows="3"><?php echo esc_html( $description_value ); ?></textarea>
                </div> <!-- .fes-form-rows -->
            </div> <!-- .fes-form-holder -->
        </li>
        <?php
    }

}