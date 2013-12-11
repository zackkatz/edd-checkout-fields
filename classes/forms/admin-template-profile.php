<?php
if ( !defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Profile related form templates
 *
 */
class FES_Admin_Template_Profile extends FES_Admin_Template {

    public static function user_login( $field_id, $label, $values = array() ) {
		if(!isset($values['label']) || $values['label'] == ''){
			$values['label'] = $label;
		}
        ?>
        <li class="user_login">
            <?php self::legend( $label, $values ); ?>
            <?php self::hidden_field( "[$field_id][input_type]", 'text' ); ?>
            <?php self::hidden_field( "[$field_id][template]", 'user_login' ); ?>

            <div class="fes-form-holder">
                <?php self::common( $field_id, 'user_login', false, $values ); ?>
                <?php self::common_text( $field_id, $values ); ?>
            </div> <!-- .fes-form-holder -->
        </li>
        <?php
    }

    public static function first_name( $field_id, $label, $values = array() ) {
	    if(!isset($values['label']) || $values['label'] == ''){
			$values['label'] = $label;
		}
        ?>
        <li class="first_name">
            <?php self::legend( $label, $values ); ?>
            <?php self::hidden_field( "[$field_id][input_type]", 'text' ); ?>
            <?php self::hidden_field( "[$field_id][template]", 'first_name' ); ?>

            <div class="fes-form-holder">
                <?php self::common( $field_id, 'first_name', false, $values ); ?>
                <?php self::common_text( $field_id, $values ); ?>
            </div> <!-- .fes-form-holder -->
        </li>
        <?php
    }

    public static function last_name( $field_id, $label, $values = array() ) {
		if(!isset($values['label']) || $values['label'] == ''){
			$values['label'] = $label;
		}
        ?>
        <li class="last_name">
            <?php self::legend( $label, $values ); ?>
            <?php self::hidden_field( "[$field_id][input_type]", 'text' ); ?>
            <?php self::hidden_field( "[$field_id][template]", 'last_name' ); ?>

            <div class="fes-form-holder">
                <?php self::common( $field_id, 'last_name', false, $values ); ?>
                <?php self::common_text( $field_id, $values ); ?>
            </div> <!-- .fes-form-holder -->
        </li>
        <?php
    }

    public static function nickname( $field_id, $label, $values = array() ) {
		if(!isset($values['label']) || $values['label'] == ''){
			$values['label'] = $label;
		}
        ?>
        <li class="nickname">
            <?php self::legend( $label, $values ); ?>
            <?php self::hidden_field( "[$field_id][input_type]", 'text' ); ?>
            <?php self::hidden_field( "[$field_id][template]", 'nickname' ); ?>

            <div class="fes-form-holder">
                <?php self::common( $field_id, 'nickname', false, $values ); ?>
                <?php self::common_text( $field_id, $values ); ?>
            </div> <!-- .fes-form-holder -->
        </li>
        <?php
    }

    public static function display_name( $field_id, $label, $values = array() ) {
		if(!isset($values['label']) || $values['label'] == ''){
			$values['label'] = $label;
		}
        ?>
        <li class="display_name">
            <?php self::legend( $label, $values ); ?>
            <?php self::hidden_field( "[$field_id][input_type]", 'text' ); ?>
            <?php self::hidden_field( "[$field_id][template]", 'display_name' ); ?>

            <div class="fes-form-holder">
                <?php self::common( $field_id, 'display_name', false, $values ); ?>
                <?php self::common_text( $field_id, $values ); ?>
            </div> <!-- .fes-form-holder -->
        </li>
        <?php
    }	
	
    public static function user_email( $field_id, $label, $values = array() ) {
		if(!isset($values['label']) || $values['label'] == ''){
			$values['label'] = $label;
		}
        ?>
        <li class="user_email">
            <?php self::legend( $label, $values ); ?>
            <?php self::hidden_field( "[$field_id][input_type]", 'email' ); ?>
            <?php self::hidden_field( "[$field_id][template]", 'user_email' ); ?>

            <div class="fes-form-holder">
                <?php self::common( $field_id, 'user_email', false, $values ); ?>
                <?php self::common_text( $field_id, $values ); ?>
            </div> <!-- .fes-form-holder -->
        </li>
        <?php
    }

    public static function user_url( $field_id, $label, $values = array() ) {
		if(!isset($values['label']) || $values['label'] == ''){
			$values['label'] = $label;
		}
        ?>
        <li class="user_url">
            <?php self::legend( $label, $values ); ?>
            <?php self::hidden_field( "[$field_id][input_type]", 'url' ); ?>
            <?php self::hidden_field( "[$field_id][template]", 'user_url' ); ?>

            <div class="fes-form-holder">
                <?php self::common( $field_id, 'user_url', false, $values ); ?>
                <?php self::common_text( $field_id, $values ); ?>
            </div> <!-- .fes-form-holder -->
        </li>
        <?php
    }

    public static function description( $field_id, $label, $values = array() ) {	    
		if(!isset($values['label']) || $values['label'] == ''){
			$values['label'] = $label;
		}
        ?>
        <li class="user_bio">
            <?php self::legend( $label, $values ); ?>
            <?php self::hidden_field( "[$field_id][input_type]", 'textarea' ); ?>
            <?php self::hidden_field( "[$field_id][template]", 'description' ); ?>

            <div class="fes-form-holder">
                <?php self::common( $field_id, 'description', false, $values ); ?>
                <?php self::common_textarea( $field_id, $values ); ?>
            </div> <!-- .fes-form-holder -->
        </li>
        <?php
    }

    public static function password( $field_id, $label, $values = array() ) {
		if(!isset($values['label']) || $values['label'] == ''){
			$values['label'] = $label;
		}
        $min_length_name = sprintf( '%s[%d][min_length]', self::$input_name, $field_id );
        $pass_repeat_name = sprintf( '%s[%d][repeat_pass]', self::$input_name, $field_id );
        $pass_strength_name = sprintf( '%s[%d][pass_strength]', self::$input_name, $field_id );
        $re_pass_label = sprintf( '%s[%d][re_pass_label]', self::$input_name, $field_id );

        $min_length_value = isset( $values['min_length'] ) ? $values['min_length'] : '6';
        $pass_repeat_value = isset( $values['repeat_pass'] ) ? $values['repeat_pass'] : 'yes';
        $pass_strength_value = isset( $values['pass_strength'] ) ? $values['pass_strength'] : 'no';
        $re_pass_label_value = isset( $values['re_pass_label'] ) ? $values['re_pass_label'] : __( 'Confirm Password', 'edd_fes' );
        ?>
        <li class="password">
            <?php self::legend( $label, $values ); ?>
            <?php self::hidden_field( "[$field_id][input_type]", 'password' ); ?>
            <?php self::hidden_field( "[$field_id][template]", 'password' ); ?>

            <div class="fes-form-holder">
                <?php self::common( $field_id, 'password', false, $values ); ?>
                <?php self::common_text( $field_id, $values ); ?>

                <div class="fes-form-rows">
                    <label><?php _e( 'Minimum password length', 'edd_fes' ); ?></label>

                    <input type="text" name="<?php echo $min_length_name ?>" value="<?php echo esc_attr( $min_length_value ); ?>" />
                </div> <!-- .fes-form-rows -->

                <div class="fes-form-rows">
                    <label><?php _e( 'Password Re-type', 'edd_fes' ); ?></label>

                    <div class="fes-form-sub-fields">
                        <label>
                            <?php self::hidden_field( "[$field_id][repeat_pass]", 'no' ); ?>
                            <input class="retype-pass" type="checkbox" name="<?php echo $pass_repeat_name ?>" value="yes"<?php checked( $pass_repeat_value, 'yes' ); ?> />
                            <?php _e( 'Require Password repeat', 'edd_fes' ); ?>
                        </label>
                    </div>
                </div> <!-- .fes-form-rows -->

                <div class="fes-form-rows<?php echo $pass_repeat_value != 'yes' ? ' fes-hide' : ''; ?>">
                    <label><?php _e( 'Re-type password label', 'edd_fes' ); ?></label>

                    <input type="text" name="<?php echo $re_pass_label ?>" value="<?php echo esc_attr( $re_pass_label_value ); ?>" />
                </div> <!-- .fes-form-rows -->

                <div class="fes-form-rows<?php echo $pass_repeat_value != 'yes' ? ' fes-hide' : ''; ?>">
                    <label><?php _e( 'Password Strength Meter', 'edd_fes' ); ?></label>

                    <div class="fes-form-sub-fields">
                        <label>
                            <?php self::hidden_field( "[$field_id][pass_strength]", 'no' ); ?>
                            <input type="checkbox" name="<?php echo $pass_strength_name ?>" value="yes"<?php checked( $pass_strength_value, 'yes' ); ?> />
                            <?php _e( 'Show password strength meter', 'edd_fes' ); ?>
                        </label>
                    </div>
                </div> <!-- .fes-form-rows -->

            </div> <!-- .fes-form-holder -->
        </li>
        <?php
    }

    public static function avatar( $field_id, $label, $values = array() ) {
		if(!isset($values['label']) || $values['label'] == ''){
			$values['label'] = $label;
		}
        $max_file_name = sprintf( '%s[%d][max_size]', self::$input_name, $field_id );
        $max_file_value = $values && isset( $values['max_size'] ) ? $values['max_size'] : '1024';
        $help = esc_attr( __( 'Enter maximum upload size limit in KB', 'edd_fes' ) );
        ?>
        <li class="user_avatar">
            <?php self::legend( $label, $values ); ?>
            <?php self::hidden_field( "[$field_id][input_type]", 'image_upload' ); ?>
            <?php self::hidden_field( "[$field_id][template]", 'avatar' ); ?>
            <?php self::hidden_field( "[$field_id][count]", '1' ); ?>

            <div class="fes-form-holder">
                <?php self::common( $field_id, 'avatar', false, $values ); ?>

                <div class="fes-form-rows">
                    <label><?php _e( 'Max. file size', 'edd_fes' ); ?></label>
                    <input type="text" class="smallipopInput" name="<?php echo $max_file_name; ?>" value="<?php echo $max_file_value; ?>" title="<?php echo $help; ?>">
                </div> <!-- .fes-form-rows -->
            </div> <!-- .fes-form-holder -->
        </li>
        <?php
    }
	
    public static function eddc_user_paypal( $field_id, $label, $values = array() ) {
		if(!isset($values['label']) || $values['label'] == ''){
			$values['label'] = $label;
		}
		$values['is_meta'] = true;
		$values['name'] = 'eddc_user_paypal';
        ?>
        <li class="eddc_user_paypal">
            <?php self::legend( $label, $values ); ?>
            <?php self::hidden_field( "[$field_id][input_type]", 'email' ); ?>
            <?php self::hidden_field( "[$field_id][template]", 'eddc_user_paypal' ); ?>

            <div class="fes-form-holder">
                <?php self::common( $field_id, 'eddc_user_paypal', true, $values ); ?>
                <?php self::common_text( $field_id, $values ); ?>
            </div> <!-- .fes-form-holder -->
        </li>
        <?php
    }

}