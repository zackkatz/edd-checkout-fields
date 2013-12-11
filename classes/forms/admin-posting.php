<?php
if ( !defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin side posting handler
 *
 * Builds custom fields UI for post add/edit screen
 * and handles value saving.
 *
 */
class FES_Admin_Posting extends FES_Render_Form {

    function __construct() {
        add_action( 'add_meta_boxes', array($this, 'add_meta_boxes') );
        add_action( 'save_post', array($this, 'save_meta'), 1, 2 ); // save the custom fields
    }

    function add_meta_boxes() {
		 add_meta_box( 'fes-custom-fields', __( 'FES Custom Fields', 'edd_fes' ), array($this, 'render_form'), 'download', 'normal', 'high' );
    }

    function render_form($form_id, $post_id = NULL, $preview = false) {
        global $post;

        $form_id = EDD_FES()->fes_options->get_option( 'fes-submission-form');
        $form_settings = get_post_meta( $form_id, 'fes-form_settings', true );

        list($post_fields, $taxonomy_fields, $custom_fields) = $this->get_input_fields( $form_id );

        if ( empty( $custom_fields ) ) {
            _e( 'No custom fields found.', 'edd_fes' );
            return;
        }
        ?>

        <input type="hidden" name="fes_cf_update" value="<?php echo wp_create_nonce( plugin_basename( __FILE__ ) ); ?>" />
        <input type="hidden" name="fes_cf_form_id" value="<?php echo $form_id; ?>" />

        <table class="form-table fes-cf-table">
            <tbody>
                <?php
                $this->render_items( $custom_fields, $post->ID, 'post', $form_id, $form_settings );
                ?>
            </tbody>
        </table>
        <?php
        $this->scripts_styles();
    }

    /**
     * Prints form input label
     *
     * @param string $attr
     */
    function label( $attr, $post_id = 0) {
        ?>
        <?php echo $attr['label'] . $this->required_mark( $attr ); ?>
        <?php
    }

    function render_item_before( $form_field, $post_id = 0 ) {
        echo '<tr>';
        echo '<th><strong>';
        $this->label( $form_field );
        echo '</strong></th>';
        echo '<td>';
    }

    function render_item_after( $attr, $post_id = 0) {
        echo '</td>';
        echo '</tr>';
    }

    function scripts_styles() {
        ?>
        <script type="text/javascript">
            jQuery(function($){
                var fes = {
                    init: function() {
                        $('.fes-cf-table').on('click', 'img.fes-clone-field', this.cloneField);
                        $('.fes-cf-table').on('click', 'img.fes-remove-field', this.removeField);
                        $('.fes-cf-table').on('click', 'a.fes-delete-avatar', this.deleteAvatar);
                    },
                    cloneField: function(e) {
                        e.preventDefault();

                        var $div = $(this).closest('tr');
                        var $clone = $div.clone();
                        // console.log($clone);

                        //clear the inputs
                        $clone.find('input').val('');
                        $clone.find(':checked').attr('checked', '');
                        $div.after($clone);
                    },

                    removeField: function() {
                        //check if it's the only item
                        var $parent = $(this).closest('tr');
                        var items = $parent.siblings().andSelf().length;

                        if( items > 1 ) {
                            $parent.remove();
                        }
                    },

                    deleteAvatar: function(e) {
                        e.preventDefault();

                        var data = {
                            action: 'fes_delete_avatar',
                            _wpnonce: '<?php echo wp_create_nonce( 'fes_nonce' ); ?>'
                        };

                        if ( confirm( $(this).data('confirm') ) ) {
                            $.post(ajaxurl, data, function() {
                                window.location.reload();
                            });
                        }
                    }
                };

                fes.init();
            });

        </script>
        <style type="text/css">
            ul.fes-attachment-list li {
                display: inline-block;
                border: 1px solid #dfdfdf;
                padding: 5px;
                -webkit-border-radius: 5px;
                -moz-border-radius: 5px;
                border-radius: 5px;
                margin-right: 5px;
            }
            ul.fes-attachment-list li a.attachment-delete {
                text-decoration: none;
                padding: 3px 12px;
                border: 1px solid #C47272;
                color: #ffffff;
                text-shadow: 0 -1px 0 rgba(0, 0, 0, 0.25);
                -webkit-border-radius: 3px;
                -moz-border-radius: 3px;
                border-radius: 3px;
                background-color: #da4f49;
                background-image: -moz-linear-gradient(top, #ee5f5b, #bd362f);
                background-image: -webkit-gradient(linear, 0 0, 0 100%, from(#ee5f5b), to(#bd362f));
                background-image: -webkit-linear-gradient(top, #ee5f5b, #bd362f);
                background-image: -o-linear-gradient(top, #ee5f5b, #bd362f);
                background-image: linear-gradient(to bottom, #ee5f5b, #bd362f);
                background-repeat: repeat-x;
                filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#ffee5f5b', endColorstr='#ffbd362f', GradientType=0);
                border-color: #bd362f #bd362f #802420;
                border-color: rgba(0, 0, 0, 0.1) rgba(0, 0, 0, 0.1) rgba(0, 0, 0, 0.25);
                *background-color: #bd362f;
                filter: progid:DXImageTransform.Microsoft.gradient(enabled=false);
            }
            ul.fes-attachment-list li a.attachment-delete:hover,
            ul.fes-attachment-list li a.attachment-delete:active {
                color: #ffffff;
                background-color: #bd362f;
                *background-color: #a9302a;
            }

            .fes-cf-table table th,
            .fes-cf-table table td{
                padding-left: 0 !important;
            }

            .fes-cf-table .required { color: red;}
            .fes-cf-table textarea { width: 400px; }

        </style>
        <?php
    }

    // Save the Metabox Data
    function save_meta( $post_id, $post ) {
        if ( !isset( $_POST['fes_cf_update'] ) ) {
            return;
        }

        if ( !wp_verify_nonce( $_POST['fes_cf_update'], plugin_basename( __FILE__ ) ) ) {
            return;
        }

        // Is the user allowed to edit the post or page?
        if ( !current_user_can( 'edit_post', $post->ID ) )
            return $post->ID;

        list( $post_vars, $tax_vars, $meta_vars ) = self::get_input_fields( $_POST['fes_cf_form_id'] );

        EDD_FES()->frontend_form_post->update_post_meta( $meta_vars, $post->ID );
    }

}