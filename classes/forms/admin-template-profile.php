<?php
if ( !defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Profile related form templates
 *
 */
class FES_Admin_Template_Profile extends FES_Admin_Template {

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
}