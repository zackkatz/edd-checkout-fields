<?php
if ( !defined( 'ABSPATH' ) ) {
	exit;
}

class FES_Admin_Posting_Profile extends FES_Admin_Posting {

    function __construct() {
        // Removed in 2.0 RC2 to contemplate removal or reinclusion
		// add_action( 'personal_options_update', array($this, 'save_fields') );
        // add_action( 'edit_user_profile_update', array($this, 'save_fields') );
        // add_action( 'show_user_profile', array($this, 'render_form') );
        // add_action( 'edit_user_profile', array($this, 'render_form') );
        // add_action( 'wp_ajax_fes_delete_avatar', array($this, 'delete_avatar_ajax') );
    }

    function delete_avatar_ajax() {
        $user_id = get_current_user_id();
        $avatar = get_user_meta( $user_id, 'user_avatar', true );
        if ( $avatar ) {
            $upload_dir = wp_upload_dir();

            $full_url = str_replace( $upload_dir['baseurl'],  $upload_dir['basedir'], $avatar );

            if ( file_exists( $full_url ) ) {
                unlink( $full_url );
                delete_user_meta( $user_id, 'user_avatar' );
            }
        }

        die();
    }

    function get_role_name( $userdata ) {
        return reset( $userdata->roles );
    }

    function render_form( $form_id, $post_id = NULL, $preview = false) {
		$userdata = get_userdata( get_current_user_id() );
        $form_id = EDD_FES()->fes_options->get_option( 'fes-profile-form');
        list($post_fields, $taxonomy_fields, $custom_fields) = $this->get_input_fields( $form_id );

        if ( !$custom_fields ) {
            return;
        }
        ?>

        <input type="hidden" name="fes_cf_update" value="<?php echo wp_create_nonce( plugin_basename( __FILE__ ) ); ?>" />
        <input type="hidden" name="fes_cf_form_id" value="<?php echo $form_id; ?>" />

        <table class="form-table fes-cf-table">
            <tbody>
                <?php
                // reset -> get the first item
                if ( $avatar = reset( $this->search( $post_fields, 'name', 'avatar' ) ) ) {
                    $this->render_item_before( $avatar );
                    $this->image_upload( $avatar, $userdata->ID, 'user' );
                    $this->render_item_after( $avatar );
                }

                $this->render_items( $custom_fields, $userdata->ID, 'user', $form_id, get_post_meta( $form_id, 'fes-form', true ) );
                ?>
            </tbody>
        </table>
        <?php
        $this->scripts_styles();
    }

    function save_fields( $user_id ) {
        if ( !isset( $_POST['fes_cf_update'] ) ) {
            return;
        }

        if ( !wp_verify_nonce( $_POST['fes_cf_update'], plugin_basename( __FILE__ ) ) ) {
            return;
        }

        list($post_fields, $taxonomy_fields, $custom_fields) = self::get_input_fields( $_POST['fes_cf_form_id'] );
        EDD_FES()->frontend_form_profile->update_user_meta( $custom_fields, $user_id );
    }
}