<?php
if ( !defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Post related form templates
 */
class CFM_Admin_Template_Post extends CFM_Admin_Template {
    public static function post_title( $field_id, $label, $values = array() ) {
        if(!isset($values['label']) || $values['label'] == ''){
			$values['label'] = edd_get_label_singular().' '.$label;
		}
		$values['required'] = $values && isset($values['required']) ? $values['required']  : 'yes';
        $values['label'] = $values && isset($values['label']) ? $values['label']  : '';
        $values['help'] = $values && isset($values['help'])? $values['help']  : '';
        $values['css'] = $values && isset($values['css'])?  $values['css']  : '';
		?>
        <li class="post_title">
            <?php self::legend( $label, $values, false ); ?>
            <?php self::hidden_field( "[$field_id][input_type]", 'text' ); ?>
            <?php self::hidden_field( "[$field_id][template]", 'post_title' ); ?>

            <div class="fes-form-holder">
                <?php self::common( $field_id, 'post_title', false, $values, false ); ?>
                <?php self::common_text( $field_id, $values ); ?>
            </div> <!-- .fes-form-holder -->
        </li>
        <?php
    }

    public static function post_content( $field_id, $label, $values = array() ) {
        if(!isset($values['label']) ||  $values['label'] == ''){
			$values['label'] = edd_get_label_singular().' '.$label;
		}
		$values['required'] = $values && isset($values['required']) ? $values['required']  : 'yes';
        $values['label'] = $values && isset($values['label']) ? $values['label']  : '';
        $values['help'] = $values && isset($values['help'])? $values['help']  : '';
        $values['css'] = $values && isset($values['css'])?  $values['css']  : '';
		
		
        $image_insert_name = sprintf( '%s[%d][insert_image]', self::$input_name, $field_id );
        $image_insert_value = isset( $values['insert_image'] ) ? $values['insert_image'] : 'yes';
        ?>
        <li class="post_content">
            <?php self::legend( $label, $values, false ); ?>
            <?php self::hidden_field( "[$field_id][input_type]", 'textarea' ); ?>
            <?php self::hidden_field( "[$field_id][template]", 'post_content' ); ?>

            <div class="fes-form-holder">
                <?php self::common( $field_id, 'post_content', false, $values, false ); ?>
                <?php self::common_textarea( $field_id, $values ); ?>

                <div class="fes-form-rows">
                    <label><?php _e( 'Enable Image Insertion', 'edd_fes' ); ?></label>

                    <div class="fes-form-sub-fields">
                        <label>
                            <?php self::hidden_field( "[$field_id][insert_image]", 'no' ); ?>
                            <input type="checkbox" name="<?php echo $image_insert_name ?>" value="yes"<?php checked( $image_insert_value, 'yes' ); ?> />
                            <?php _e( 'Enable image upload in post area', 'edd_fes' ); ?>
                        </label>
                    </div>
                </div> <!-- .fes-form-rows -->
            </div> <!-- .fes-form-holder -->
        </li>
        <?php
    }

    public static function post_excerpt( $field_id, $label, $values = array() ) {
        if(!isset($values['label']) || $values['label'] == ''){
			$values['label'] = edd_get_label_singular().' '.$label;
		}
		?>
        <li class="post_excerpt">
            <?php self::legend( $label, $values); ?>
            <?php self::hidden_field( "[$field_id][input_type]", 'textarea' ); ?>
            <?php self::hidden_field( "[$field_id][template]", 'post_excerpt' ); ?>

            <div class="fes-form-holder">
                <?php self::common( $field_id, 'post_excerpt', false, $values); ?>
                <?php self::common_textarea( $field_id, $values ); ?>
            </div> <!-- .fes-form-holder -->
        </li>
        <?php
    }

	public static function multiple_pricing( $field_id, $label, $values = array() ) {
        if(!isset($values['label']) || $values['label'] == ''){
			$values['label'] = edd_get_label_singular().' '.$label;
		}
		?>
        <li class="multiple_pricing">
            <?php self::legend( $label, $values); ?>
            <?php self::hidden_field( "[$field_id][input_type]", 'multiple_pricing' ); ?>
            <?php self::hidden_field( "[$field_id][template]", 'multiple_pricing' ); ?>
            <div class="fes-form-holder">
                <?php self::common( $field_id, 'multiple', false, $values ); ?>
                <?php self::prices_and_files( $field_id, $values ); ?>
			</div> <!-- .fes-form-holder -->
        </li>
        <?php
    }

    public static function featured_image( $field_id, $label, $values = array() ) {
        $max_file_name = sprintf( '%s[%d][max_size]', self::$input_name, $field_id );
        $max_file_value = $values ? $values['max_size'] : '10240';
        $help = esc_attr( __( 'Enter maximum upload size limit in KB', 'edd_fes' ) );
		 if(!isset($values['label']) || $values['label'] == ''){
			$values['label'] = edd_get_label_singular().' '.$label;
		}
        ?>
        <li class="featured_image">
            <?php self::legend( $label, $values); ?>
            <?php self::hidden_field( "[$field_id][input_type]", 'image_upload' ); ?>
            <?php self::hidden_field( "[$field_id][template]", 'featured_image' ); ?>
            <?php self::hidden_field( "[$field_id][count]", '1' ); ?>

            <div class="fes-form-holder">
                <?php self::common( $field_id, 'featured_image', false, $values ); ?>

                <div class="fes-form-rows">
                    <label><?php _e( 'Max. file size', 'edd_fes' ); ?></label>
                    <input type="text" class="smallipopInput" name="<?php echo $max_file_name; ?>" value="<?php echo $max_file_value; ?>" title="<?php echo $help; ?>">
                </div> <!-- .fes-form-rows -->
            </div> <!-- .fes-form-holder -->
        </li>
        <?php
    }

    public static function taxonomy( $field_id, $label, $taxonomy = '', $values = array() ) {
        $type_name = sprintf( '%s[%d][type]', self::$input_name, $field_id );
        $order_name = sprintf( '%s[%d][order]', self::$input_name, $field_id );
        $orderby_name = sprintf( '%s[%d][orderby]', self::$input_name, $field_id );
        $exclude_type_name = sprintf( '%s[%d][exclude_type]', self::$input_name, $field_id );
        $exclude_name = sprintf( '%s[%d][exclude]', self::$input_name, $field_id );

        $type_value = $values  && isset($values['type'])? esc_attr( $values['type'] ) : 'select';
        $order_value = $values && isset($values['order'])? esc_attr( $values['order'] ) : 'ASC';
        $orderby_value = $values && isset($values['orderby'] )? esc_attr( $values['orderby'] ) : 'name';
        $exclude_type_value = $values && isset( $values['exclude_type'] )? esc_attr( $values['exclude_type'] ) : 'exclude';
        $exclude_value = $values && isset($values['exclude'] )? esc_attr( $values['exclude'] ) : '';
        if(!isset($values['label']) || $values['label'] == ''){
			$values['label'] = edd_get_label_singular().' '.$label;
		}
        ?>
        <li class="taxonomy <?php echo $taxonomy; ?>">
            <?php self::legend( $label, $values ); ?>
            <?php self::hidden_field( "[$field_id][input_type]", 'taxonomy' ); ?>
            <?php self::hidden_field( "[$field_id][template]", 'taxonomy' ); ?>

            <div class="fes-form-holder">
                <?php self::common( $field_id, $taxonomy, false, $values ); ?>

                <div class="fes-form-rows">
                    <label><?php _e( 'Type', 'edd_fes' ); ?></label>
                    <select name="<?php echo $type_name ?>">
                        <option value="select"<?php selected( $type_value, 'select' ); ?>><?php _e( 'Dropdown', 'edd_fes' ); ?></option>
                        <option value="multiselect"<?php selected( $type_value, 'multiselect' ); ?>><?php _e( 'Multi Select', 'edd_fes' ); ?></option>
                        <option value="checkbox"<?php selected( $type_value, 'checkbox' ); ?>><?php _e( 'Checkbox', 'edd_fes' ); ?></option>
                        <option value="text"<?php selected( $type_value, 'text' ); ?>><?php _e( 'Text Input', 'edd_fes' ); ?></option>
                    </select>
                </div> <!-- .fes-form-rows -->

                <div class="fes-form-rows">
                    <label><?php _e( 'Order By', 'edd_fes' ); ?></label>
                    <select name="<?php echo $orderby_name ?>">
                        <option value="name"<?php selected( $orderby_value, 'name' ); ?>><?php _e( 'Name', 'edd_fes' ); ?></option>
                        <option value="id"<?php selected( $orderby_value, 'id' ); ?>><?php _e( 'Term ID', 'edd_fes' ); ?></option>
                        <option value="slug"<?php selected( $orderby_value, 'slug' ); ?>><?php _e( 'Slug', 'edd_fes' ); ?></option>
                        <option value="count"<?php selected( $orderby_value, 'count' ); ?>><?php _e( 'Count', 'edd_fes' ); ?></option>
                        <option value="term_group"<?php selected( $orderby_value, 'term_group' ); ?>><?php _e( 'Term Group', 'edd_fes' ); ?></option>
                    </select>
                </div> <!-- .fes-form-rows -->

                <div class="fes-form-rows">
                    <label><?php _e( 'Order', 'edd_fes' ); ?></label>
                    <select name="<?php echo $order_name ?>">
                        <option value="ASC"<?php selected( $order_value, 'ASC' ); ?>><?php _e( 'ASC', 'edd_fes' ); ?></option>
                        <option value="DESC"<?php selected( $order_value, 'DESC' ); ?>><?php _e( 'DESC', 'edd_fes' ); ?></option>
                    </select>
                </div> <!-- .fes-form-rows -->

                <div class="fes-form-rows">
                    <label><?php _e( 'Selection Type', 'edd_fes' ); ?></label>
                    <select name="<?php echo $exclude_type_name ?>">
                        <option value="exclude"<?php selected( $exclude_type_value, 'exclude' ); ?>><?php _e( 'Exclude', 'edd_fes' ); ?></option>
                        <option value="include"<?php selected( $exclude_type_value, 'include' ); ?>><?php _e( 'Include', 'edd_fes' ); ?></option>
                        <option value="child_of"<?php selected( $exclude_type_value, 'child_of' ); ?>><?php _e( 'Child of', 'edd_fes' ); ?></option>
                    </select>
                </div> <!-- .fes-form-rows -->

                <div class="fes-form-rows">
                    <label><?php _e( 'Selection terms', 'edd_fes' ); ?></label>
                    <input type="text" class="smallipopInput" name="<?php echo $exclude_name; ?>" title="<?php _e( 'Enter the term IDs as comma separated (without space) to exclude/include in the form.', 'edd_fes' ); ?>" value="<?php echo $exclude_value; ?>" />
                </div> <!-- .fes-form-rows -->

            </div> <!-- .fes-form-holder -->
        </li>
        <?php
    }

}