<?php

/**
 * Handles form generaton and posting for add/edit post in frontend
 */
class CFM_Render_Form {

    static $meta_key = 'fes-form';
    static $separator = '| ';
    static $config_id = '_fes-form_id';

    /**
     * Send json error message
     *
     * @param string $error
     */
    function send_error( $error ) {
        echo json_encode( array(
            'success' => false,
            'error' => $error
        ) );

        die();
    }

    /**
     * Search on multi dimentional array
     *
     * @param array $array
     * @param string $key name of key
     * @param string $value the value to search
     * @return array
     */
    function search( $array, $key, $value ) {
        $results = array();

        if ( is_array( $array ) ) {
            if ( isset( $array[$key] ) && $array[$key] == $value )
                $results[] = $array;

            foreach ($array as $subarray)
                $results = array_merge( $results, $this->search( $subarray, $key, $value ) );
        }

        return $results;
    }

    /**
     * Really simple captcha validation
     *
     * @return void
     */
    function validate_rs_captcha() {
        $rs_captcha_input = isset( $_POST['rs_captcha'] ) ? $_POST['rs_captcha'] : '';
        $rs_captcha_file = isset( $_POST['rs_captcha_val'] ) ? $_POST['rs_captcha_val'] : '';

        if ( class_exists( 'ReallySimpleCaptcha' ) ) {
            $captcha_instance = new ReallySimpleCaptcha();

            if ( !$captcha_instance->check( $rs_captcha_file, $rs_captcha_input ) ) {

                $this->send_error( __( 'Really Simple Captcha validation failed', 'edd_fes' ) );
            } else {
                // validation success, remove the files
                $captcha_instance->remove( $rs_captcha_file );
            }
        }
    }

    /**
     * reCaptcha Validation
     *
     * @return void
     */
    function validate_re_captcha() {
        $recap_challenge = isset( $_POST['recaptcha_challenge_field'] ) ? $_POST['recaptcha_challenge_field'] : '';
        $recap_response = isset( $_POST['recaptcha_response_field'] ) ? $_POST['recaptcha_response_field'] : '';
        $private_key = EDD_CFM()->fes_options->get_option( 'recaptcha_private');

        $resp = recaptcha_check_answer( $private_key, $_SERVER["REMOTE_ADDR"], $recap_challenge, $recap_response );

        if ( !$resp->is_valid ) {
            $this->send_error( __( 'reCAPTCHA validation failed', 'edd_fes' ) );
        }
    }

    /**
     * Guess a suitable username for registration based on email address
     * @param string $email email address
     * @return string username
     */
    function guess_username( $email ) {
        // username from email address
        $username = sanitize_user( substr( $email, 0, strpos( $email, '@' ) ) );

        if ( !username_exists( $username ) ) {
            return $username;
        }

        // try to add some random number in username
        // and may be we got our username
        $username .= rand( 1, 199 );
        if ( !username_exists( $username ) ) {
            return $username;
        }
    }

    /**
     * Get input meta fields separated as post vars, taxonomy and meta vars
     *
     * @param int $form_id form id
     * @return array
     */
    public static function get_input_fields( $form_id ) {
        $form_vars = get_post_meta( $form_id, self::$meta_key, true );

        $ignore_lists = array('section_break', 'html');
        $post_vars = $meta_vars = $taxonomy_vars = array();
		if($form_vars == null){
			return array(array(),array(),array());
		}
        foreach ($form_vars as $key => $value) {

            // ignore section break and HTML input type
            if ( in_array( $value['input_type'], $ignore_lists ) ) {
                continue;
            }

            //separate the post and custom fields
            if ( isset( $value['is_meta'] ) && $value['is_meta'] == 'yes' ) {
                $meta_vars[] = $value;
                continue;
            }

            if ( $value['input_type'] == 'taxonomy' ) {

                // don't add "category"
                if ( $value['name'] == 'category' ) {
                    continue;
                }

                $taxonomy_vars[] = $value;
            } else {
                $post_vars[] = $value;
            }
        }

        return array($post_vars, $taxonomy_vars, $meta_vars);
    }

    public static function prepare_meta_fields( $meta_vars ) {
        // loop through custom fields
        // skip files, put in a key => value paired array for later executation
        // process repeatable fields separately
        // if the input is array type, implode with separator in a field

        $files = array();
        $meta_key_value = array();
        $multi_repeated = array(); //multi repeated fields will in sotre duplicated meta key

        foreach ($meta_vars as $key => $value) {

            // put files in a separate array, we'll process it later
            if ( ($value['input_type'] == 'file_upload') || ($value['input_type'] == 'image_upload') ) {
                $files[] = array(
                    'name' => $value['name'],
                    'value' => isset( $_POST['fes_files'][$value['name']] ) ? $_POST['fes_files'][$value['name']] : array()
                );

                // process repeatable fiels
            } elseif ( $value['input_type'] == 'repeat' ) {

                // if it is a multi column repeat field
                if ( isset( $value['multiple'] ) ) {

                    // if there's any items in the array, process it
                    if ( $_POST[$value['name']] ) {

                        $ref_arr = array();
                        $cols = count( $value['columns'] );
                        $first = array_shift( array_values( $_POST[$value['name']] ) ); //first element
                        $rows = count( $first );

                        // loop through columns
                        for ($i = 0; $i < $rows; $i++) {

                            // loop through the rows and store in a temp array
                            $temp = array();
                            for ($j = 0; $j < $cols; $j++) {

                                $temp[] = $_POST[$value['name']][$j][$i];
                            }

                            // store all fields in a row with self::$separator separated
                            $ref_arr[] = implode( self::$separator, $temp );
                        }

                        // now, if we found anything in $ref_arr, store to $multi_repeated
                        if ( $ref_arr ) {
                            $multi_repeated[$value['name']] = array_slice( $ref_arr, 0, $rows );
                        }
                    }
                } else {
                    $meta_key_value[$value['name']] = implode( self::$separator, $_POST[$value['name']] );
                }

                // process other fields
            } else {

                // if it's an array, implode with this->separator
                if ( is_array( $_POST[$value['name']] ) ) {
                    $meta_key_value[$value['name']] = implode( self::$separator, $_POST[$value['name']] );
                } else {
                    $meta_key_value[$value['name']] = trim( $_POST[$value['name']] );
                }
            }
        } //end foreach

        return array($meta_key_value, $multi_repeated, $files);
    }

    function guest_fields( $form_settings ) {
        ?>
        <li class="el-name">
            <div class="fes-label">
                <label><?php echo $form_settings['name_label']; ?> <span class="required">*</span></label>
            </div>

            <div class="fes-fields">
                <input type="text" required="required" data-required="yes" data-type="text" name="guest_name" value="" size="40">
            </div>
        </li>

        <li class="el-email">
            <div class="fes-label">
                <label><?php echo $form_settings['email_label']; ?> <span class="required">*</span></label>
            </div>

            <div class="fes-fields">
                <input type="email" required="required" data-required="yes" data-type="email" name="guest_email" value="" size="40">
            </div>
        </li>
        <?php
    }

    /**
     * Handles the add post shortcode
     *
     * @param $atts
     */
    function render_form( $form_id, $post_id = NULL, $preview = false ) {

    	global $user_ID;

        $form_vars = get_post_meta( $form_id, self::$meta_key, true );
        $form_settings = get_post_meta( $form_id, 'fes-form_settings', true );

        if ( EDD_CFM()->vendors->is_pending( $user_ID ) ) {
            echo '<div class="fes-vendor-pending fes-info">';
            	echo __( 'Your vendor application is pending. Once approved you will be able to submit products.', 'edd_fes' );
            echo '</div>';
            return;
        }

        if ( $form_vars ) {
            ?>

            <?php if ( !$preview ) { ?>
                <form class="fes-form-add" action="" method="post">
                <?php } ?>

                <div class="fes-form">

                    <?php
                    if ( !is_user_logged_in() && $form_settings['guest_post'] == 'true' && $form_settings['guest_details'] == 'true' ) {
                        $this->guest_fields( $form_settings );
                    }

                    if ( !$post_id ) {
                        do_action( 'fes_add_post_form_top', $form_id, $form_settings );
                    } else {
                        do_action( 'fes_edit_post_form_top', $form_id, $post_id, $form_settings );
                    }

                    $this->render_items( $form_vars, $post_id, 'post', $form_id, $form_settings );
                    $this->submit_button( $form_id, $form_settings, $post_id );

                    if ( !$post_id ) {
                        do_action( 'fes_add_post_form_bottom', $form_id, $form_settings );
                    } else {
                        do_action( 'fes_edit_post_form_bottom', $form_id, $post_id, $form_settings );
                    }
                    ?>

                </div>

                <?php if ( !$preview ) { ?>
                </form>
            <?php } ?>

            <?php
        } //endif
    }

    function render_item_before( $form_field, $post_id ) {
        $label_exclude = array('section_break', 'html', 'action_hook', 'toc');
        $el_name = !empty( $form_field['name'] ) ? $form_field['name'] : '';
        $class_name = !empty( $form_field['css'] ) ? ' ' . $form_field['css'] : '';

        printf( '<fieldset class="fes-el %s%s">', $el_name, $class_name );

        if ( isset( $form_field['input_type'] ) && !in_array( $form_field['input_type'], $label_exclude ) ) {
            $this->label( $form_field, $post_id );
        }
    }

    function render_item_after( $form_field ) {
        echo '</fieldset>';
    }

    /**
     * Render form items
     *
     * @param array $form_vars
     * @param int|null $post_id
     * @param string $type type of the form. post or user
     */
    function render_items( $form_vars, $post_id, $type = 'post', $form_id, $form_settings ) {
        $edit_ignore = array('recaptcha', 'really_simple_captcha');
        $hidden_fields = array();

        foreach ($form_vars as $key => $form_field) {

            // don't show captcha in edit page
            if ( $post_id && in_array( $form_field['input_type'], $edit_ignore ) ) {
                continue;
            }

            // igonre the hidden fields
            if ( $form_field['input_type'] == 'hidden' ) {
                $hidden_fields[] = $form_field;
                continue;
            }

            $this->render_item_before( $form_field, $post_id );

            switch ($form_field['input_type']) {
                case 'text':
                    $this->text( $form_field, $post_id, $type );
                    break;

                case 'textarea':
                    $this->textarea( $form_field, $post_id, $type );
                    break;

                case 'image_upload':
                    $this->image_upload( $form_field, $post_id, $type );
                    break;

                case 'select':
                    $this->select( $form_field, false, $post_id, $type );
                    break;

                case 'multiselect':
                    $this->select( $form_field, true, $post_id, $type );
                    break;

                case 'radio':
                    $this->radio( $form_field, $post_id, $type );
                    break;

                case 'checkbox':
                    $this->checkbox( $form_field, $post_id, $type );
                    break;

                case 'file_upload':
                    $this->file_upload( $form_field, $post_id, $type );
                    break;

                case 'url':
                    $this->url( $form_field, $post_id, $type );
                    break;

                case 'email':
                    $this->email( $form_field, $post_id, $type );
                    break;

                case 'repeat':
                    $this->repeat( $form_field, $post_id, $type );
                    break;

                case 'section_break':
                    $this->section_break( $form_field, $post_id );
                    break;

                case 'html':
                    $this->html( $form_field );
                    break;

                case 'action_hook':
                    $this->action_hook( $form_field, $form_id, $post_id, $form_settings );
                    break;

                case 'date':
                    $this->date( $form_field, $post_id, $type );
                    break;

                case 'map':
                    $this->map( $form_field, $post_id, $type );
                    break;

                case 'toc':
                    $this->toc( $form_field, $post_id );
                    break;
            }

            $this->render_item_after( $form_field );
        } //end foreach

        if( $hidden_fields ) {
            foreach($hidden_fields as $field) {
                printf( '<input type="hidden" name="%s" value="%s">', esc_attr( $field['name'] ), esc_attr( $field['meta_value'] ) );
                echo "\r\n";
            }
        }
    }

    function submit_button( $form_id, $form_settings, $post_id ) {
        $form_settings['update_text']= __( 'Update', 'edd_fes' );
		$form_settings['submit_text']= __( 'Submit', 'edd_fes' );
		?>
        <fieldset class="fes-submit">
            <div class="fes-label">
                &nbsp;
            </div>

            <?php wp_nonce_field( 'fes-form_add' ); ?>
            <input type="hidden" name="form_id" value="<?php echo $form_id; ?>">
            <input type="hidden" name="page_id" value="<?php echo get_post() ? get_the_ID() : '0'; ?>">
            <input type="hidden" name="action" value="fes_submit_post">

            <?php
            if ( $post_id ) {
                $cur_post = get_post( $post_id );
                ?>
                <input type="hidden" name="post_id" value="<?php echo $post_id; ?>">
                <input type="hidden" name="post_date" value="<?php echo esc_attr( $cur_post->post_date ); ?>">
                <input type="hidden" name="comment_status" value="<?php echo esc_attr( $cur_post->comment_status ); ?>">
                <input type="hidden" name="post_author" value="<?php echo esc_attr( $cur_post->post_author ); ?>">
                <input type="submit" class="button" name="submit" value="<?php echo $form_settings['update_text']; ?>" />
            <?php } else { ?>
                <input type="submit" class="button" name="submit" value="<?php echo $form_settings['submit_text']; ?>" />
                <input type="hidden" name="fes-form_status" value="new">
            <?php } ?>

            <?php 
			// for 2.1/2 ;)
			if ( isset( $form_settings['draft_post'] ) && $form_settings['draft_post'] == 'true' ) { ?>
                <a href="#" class="btn" id="fes-post-draft"><?php _e( 'Save Draft', 'edd_fes' ); ?></a>
            <?php } ?>
        </fieldset>
        <?php
    }

    /**
     * Prints required field asterisk
     *
     * @param array $attr
     * @return string
     */
    function required_mark( $attr ) {
        if ( isset( $attr['required'] ) && $attr['required'] == 'yes' ) {
            return ' <span class="required">*</span>';
        }
    }

    /**
     * Prints HTML5 required attribute
     *
     * @param array $attr
     * @return string
     */
    function required_html5( $attr ) {
        if ( isset( $attr['required'] ) && $attr['required'] == 'yes' ) {
            echo ' required="required"';
        }
    }

    /**
     * Print required class name
     *
     * @param array $attr
     * @return string
     */
    function required_class( $attr ) {
        return;
        if ( isset( $attr['required'] ) && $attr['required'] == 'yes' ) {
            echo ' required';
        }
    }

    /**
     * Prints form input label
     *
     * @param string $attr
     */
    function label( $attr, $post_id = 0 ) {
        ?>
        <div class="fes-label">
            <label for="fes-<?php echo isset( $attr['name'] ) ? $attr['name'] : 'cls'; ?>"><?php echo $attr['label'] . $this->required_mark( $attr ); ?></label>
        </div>
        <?php
    }

    /**
     * Check if its a meta field
     *
     * @param array $attr
     * @return boolean
     */
    function is_meta( $attr ) {
        if ( isset( $attr['is_meta'] ) && $attr['is_meta'] == 'yes' ) {
            return true;
        }

        return false;
    }

    /**
     * Get a meta value
     *
     * @param int $object_id user_ID or post_ID
     * @param string $meta_key
     * @param string $type post or user
     * @param bool $single
     * @return string
     */
    function get_meta( $object_id, $meta_key, $type = 'post', $single = true ) {
        if ( !$object_id ) {
            return '';
        }

        if ( $type == 'post' ) {
            return get_post_meta( $object_id, $meta_key, $single );
        }

        return get_user_meta( $object_id, $meta_key, $single );
    }

    function get_user_data( $user_id, $field ) {
        return get_user_by( 'id', $user_id )->$field;
    }

    /**
     * Prints a text field
     *
     * @param array $attr
     * @param int|null $post_id
     */
    function text( $attr, $post_id, $type = 'post' ) {
        // checking for user profile username
        $username = false;
        $taxonomy = false;

        if ( $post_id ) {

            if ( $this->is_meta( $attr ) ) {
                $value = $this->get_meta( $post_id, $attr['name'], $type );
            } else {
                // applicable for post tags
                if ( $type == 'post' && $attr['name'] == 'tags' ) {
                    $post_tags = wp_get_post_tags( $post_id );
                    $tagsarray = array();
                    foreach ($post_tags as $tag) {
                        $tagsarray[] = $tag->name;
                    }

                    $value = implode( ', ', $tagsarray );
                    $taxonomy = true;
                } elseif ( $type == 'post' ) {
                    $value = get_post_field( $attr['name'], $post_id );
                } elseif ( $type == 'user' ) {
                    $value = get_user_by( 'id', $post_id )->$attr['name'];

                    if ( $attr['name'] == 'user_login' ) {
                        $username = true;
                    }
                }
            }
        } else {
            $value = $attr['default'];

            if ( $type == 'post' && $attr['name'] == 'tags' ) {
                $taxonomy = true;
            }
        }
        ?>

        <div class="fes-fields">
            <input class="textfield<?php echo $this->required_class( $attr ); ?>" id="<?php echo $attr['name']; ?>" type="text" data-required="<?php echo $attr['required'] ?>" data-type="text"<?php $this->required_html5( $attr ); ?> name="<?php echo esc_attr( $attr['name'] ); ?>" placeholder="<?php echo esc_attr( $attr['placeholder'] ); ?>" value="<?php echo esc_attr( $value ) ?>" size="<?php echo esc_attr( $attr['size'] ) ?>" <?php echo $username ? 'disabled' : ''; ?> />
            <span class="fes-help"><?php echo $attr['help']; ?></span>

            <?php if ( $taxonomy ) { ?>
            <script type="text/javascript">
                jQuery(function($) {
                    $('fieldset.tags input[name=tags]').suggest( ajaxurl + '?action=ajax-tag-search&tax=post_tag', { delay: 500, minchars: 2, multiple: true, multipleSep: ', ' } );
                });
            </script>
            <?php } ?>
        </div>

        <?php
    }

    /**
     * Prints a textarea field
     *
     * @param array $attr
     * @param int|null $post_id
     */
    function textarea( $attr, $post_id, $type ) {
        $req_class = ( isset( $attr['required'] ) && $attr['required'] == 'yes' ) ? 'required' : 'rich-editor';

        if ( $post_id ) {
            if ( $this->is_meta( $attr ) ) {
                $value = $this->get_meta( $post_id, $attr['name'], $type, true );
            } else {

                if ( $type == 'post' ) {
                    $value = get_post_field( $attr['name'], $post_id );
                } else {
                    $value = $this->get_user_data( $post_id, $attr['name'] );
                }
            }
        } else {
            $value = $attr['default'];
        }
        ?>

        <div class="fes-fields">

            <?php if ( isset( $attr['insert_image'] ) && $attr['insert_image'] == 'yes' ) { ?>
                <div id="fes-insert-image-container">
                    <a class="fes-button" id="fes-insert-image" href="#">
                        <span class="fes-media-icon"></span>
                        <?php _e( 'Insert Photo', 'edd_fes' ); ?>
                    </a>
                </div>
            <?php } ?>

            <?php

            $rich = isset( $attr['rich'] ) ? $attr['rich'] : '';

            if ( $rich == 'yes' ) {

                printf( '<span class="fes-rich-validation" data-required="%s" data-type="rich" data-id="%s"></span>', $attr['required'], $attr['name'] );
                wp_editor( $value, $attr['name'], array('editor_height' => $attr['rows'], 'quicktags' => false, 'media_buttons' => false, 'editor_class' => $req_class) );

            } elseif( $rich == 'teeny' ) {

                printf( '<span class="fes-rich-validation" data-required="%s" data-type="rich" data-id="%s"></span>', $attr['required'], $attr['name'] );
                wp_editor( $value, $attr['name'], array('editor_height' => $attr['rows'], 'quicktags' => false, 'media_buttons' => false, 'teeny' => true, 'editor_class' => $req_class) );
            } else {
                ?>
                <textarea class="textareafield<?php echo $this->required_class( $attr ); ?>" id="<?php echo $attr['name']; ?>" name="<?php echo $attr['name']; ?>" data-required="<?php echo $attr['required'] ?>" data-type="textarea"<?php $this->required_html5( $attr ); ?> placeholder="<?php echo esc_attr( $attr['placeholder'] ); ?>" rows="<?php echo $attr['rows']; ?>" cols="<?php echo $attr['cols']; ?>"><?php echo esc_textarea( $value ) ?></textarea>
            <?php } ?>
            <span class="fes-help"><?php echo $attr['help']; ?></span>
        </div>

        <?php
    }

    /**
     * Prints a file upload field
     *
     * @param array $attr
     * @param int|null $post_id
     */
    function file_upload( $attr, $post_id, $type ) {



        $allowed_ext = '';
        $extensions = fes_allowed_extensions();
        if ( is_array( $attr['extension'] ) ) {
            foreach ($attr['extension'] as $ext) {
                $allowed_ext .= $extensions[$ext]['ext'] . ',';
            }
        } else {
            $allowed_ext = '*';
        }

        $uploaded_items = $post_id ? $this->get_meta( $post_id, $attr['name'], $type, false ) : array();
        ?>

        <div class="fes-fields">
            <div id="fes-<?php echo $attr['name']; ?>-upload-container">
                <div class="fes-attachment-upload-filelist">
                    <a id="fes-<?php echo $attr['name']; ?>-pickfiles" class="button file-selector" href="#"><?php _e( 'Select File(s)', 'edd_fes' ); ?></a>

                    <?php printf( '<span class="fes-file-validation" data-required="%s" data-type="file"></span>', $attr['required'] ); ?>

                    <ul class="fes-attachment-list thumbnails">
                        <?php
                        if ( $uploaded_items ) {
                            foreach ($uploaded_items as $attach_id) {
                                echo EDD_CFM()->upload->attach_html( $attach_id, $attr['name'] );

                                if ( is_admin() ) {
                                    printf( '<a href="%s">%s</a>', wp_get_attachment_url( $attach_id ), __( 'Download File', 'edd_fes' ) );
                                }
                            }
                        }
                        ?>
                    </ul>
                </div>
            </div><!-- .container -->

            <span class="fes-help"><?php echo $attr['help']; ?></span>

        </div> <!-- .fes-fields -->

        <script type="text/javascript">
            jQuery(function($) {
                new CFM_Uploader('fes-<?php echo $attr['name']; ?>-pickfiles', 'fes-<?php echo $attr['name']; ?>-upload-container', <?php echo $attr['count']; ?>, '<?php echo $attr['name']; ?>', '<?php echo $allowed_ext; ?>', <?php echo $attr['max_size'] ?>);
            });
        </script>
        <?php
    }

    /**
     * Prints a image upload field
     *
     * @param array $attr
     * @param int|null $post_id
     */
    function image_upload( $attr, $post_id, $type ) {

        $has_featured_image = false;
        $has_images = false;
        $has_avatar = false;

        if ( $post_id ) {
            if ( $this->is_meta( $attr ) ) {
                $images = $this->get_meta( $post_id, $attr['name'], $type, false );
                $has_images = true;
            } else {

                if ( $type == 'post' ) {
                    // it's a featured image then
                    $thumb_id = get_post_thumbnail_id( $post_id );

                    if ( $thumb_id ) {
                        $has_featured_image = true;
                        $featured_image = EDD_CFM()->upload->attach_html( $thumb_id );
                    }
                } else {
                    // it must be a user avatar
                    $has_avatar = true;
                    $featured_image = get_avatar( $post_id );
                }
            }
        }
        ?>

        <div class="fes-fields">
            <div id="fes-<?php echo $attr['name']; ?>-upload-container">
                <div class="fes-attachment-upload-filelist">
                    <a id="fes-<?php echo $attr['name']; ?>-pickfiles" class="button file-selector" href="#"><?php _e( 'Select Image', 'edd_fes' ); ?></a>

                    <?php
                    $required = isset( $attr['required'] ) ? $attr['required'] : '';
                    printf( '<span class="fes-file-validation" data-required="%s" data-type="file"></span>', $required ); ?>

                    <ul class="fes-attachment-list thumbnails">
                        <?php
                        if ( $has_featured_image ) {
                            echo $featured_image;
                        }

                        if ( $has_avatar ) {
                            $avatar = get_user_meta( $post_id, 'user_avatar', true );
                            if ( $avatar ) {
                                echo $featured_image;
                                printf( '<br><a href="#" data-confirm="%s" class="fes-button button fes-delete-avatar">%s</a>', __( 'Are you sure?', 'edd_fes' ), __( 'Delete', 'edd_fes' ) );
                            }
                        }

                        if ( $has_images ) {
                            foreach ($images as $attach_id) {
                                echo EDD_CFM()->upload->attach_html( $attach_id, $attr['name'] );
                            }
                        }
                        ?>
                    </ul>
                </div>
            </div><!-- .container -->

            <span class="fes-help"><?php echo $attr['help']; ?></span>

        </div> <!-- .fes-fields -->

        <script type="text/javascript">
            jQuery(function($) {
                new CFM_Uploader('fes-<?php echo $attr['name']; ?>-pickfiles', 'fes-<?php echo $attr['name']; ?>-upload-container', <?php echo $attr['count']; ?>, '<?php echo $attr['name']; ?>', 'jpg,jpeg,gif,png,bmp', <?php echo $attr['max_size'] ?>);
            });
        </script>
        <?php
    }

    /**
     * Prints a select or multiselect field
     *
     * @param array $attr
     * @param bool $multiselect
     * @param int|null $post_id
     */
    function select( $attr, $multiselect = false, $post_id, $type ) {
        if ( $post_id ) {
            $selected = $this->get_meta( $post_id, $attr['name'], $type );
            $selected = $multiselect ? explode( self::$separator, $selected ) : $selected;
        } else {
            $selected = isset( $attr['selected'] ) ? $attr['selected'] : '';
            $selected = $multiselect ? ( is_array( $selected ) ? $selected : array() ) : $selected;
        }

        $multi = $multiselect ? ' multiple="multiple"' : '';
        $data_type = $multiselect ? 'multiselect' : 'select';
        $css = $multiselect ? ' class="multiselect"' : '';
        ?>

        <div class="fes-fields">

            <select<?php echo $css; ?> name="<?php echo $attr['name'] ?>[]"<?php echo $multi; ?> data-required="<?php echo $attr['required'] ?>" data-type="<?php echo $data_type; ?>"<?php $this->required_html5( $attr ); ?>>

                <?php if ( !empty( $attr['first'] ) ) { ?>
                    <option value=""><?php echo $attr['first']; ?></option>
                <?php } ?>

                <?php
                if ( $attr['options'] && count( $attr['options'] ) > 0 ) {
                    foreach ($attr['options'] as $option) {
                        $current_select = $multiselect ? selected( in_array( $option, $selected ), true, false ) : selected( $selected, $option, false );
                        ?>
                        <option value="<?php echo esc_attr( $option ); ?>"<?php echo $current_select; ?>><?php echo $option; ?></option>
                        <?php
                    }
                }
                ?>
            </select>
            <span class="fes-help"><?php echo $attr['help']; ?></span>
        </div>
        <?php
    }

    /**
     * Prints a radio field
     *
     * @param array $attr
     * @param int|null $post_id
     */
    function radio( $attr, $post_id, $type ) {
        $selected = isset( $attr['selected'] ) ? $attr['selected'] : '';

        if ( $post_id ) {
            $selected = $this->get_meta( $post_id, $attr['name'], $type, true );
        }
        ?>

        <div class="fes-fields">

            <span data-required="<?php echo $attr['required'] ?>" data-type="radio"></span>

            <?php
            if ( $attr['options'] && count( $attr['options'] ) > 0 ) {
                foreach ($attr['options'] as $option) {
                    ?>

                    <label>
                        <input name="<?php echo $attr['name']; ?>" type="radio" value="<?php echo esc_attr( $option ); ?>"<?php checked( $selected, $option ); ?> />
                        <?php echo $option; ?>
                    </label>
                    <?php
                }
            }
            ?>

            <span class="fes-help"><?php echo $attr['help']; ?></span>
        </div>

        <?php
    }

    /**
     * Prints a checkbox field
     *
     * @param array $attr
     * @param int|null $post_id
     */
    function checkbox( $attr, $post_id, $type ) {
        $selected = isset( $attr['selected'] ) ? $attr['selected'] : array();

        if ( $post_id ) {
            $selected = explode( self::$separator, $this->get_meta( $post_id, $attr['name'], $type, true ) );
        }
        ?>

        <div class="fes-fields">
            <span data-required="<?php echo $attr['required'] ?>" data-type="radio"></span>

            <?php
            if ( $attr['options'] && count( $attr['options'] ) > 0 ) {
                foreach ($attr['options'] as $option) {
                    ?>

                    <label>
                        <input type="checkbox" name="<?php echo $attr['name']; ?>[]" value="<?php echo esc_attr( $option ); ?>"<?php echo in_array( $option, $selected ) ? ' checked="checked"' : ''; ?> />
                        <?php echo $option; ?>
                    </label>
                    <?php
                }
            }
            ?>

            <div class="fes-fields">
                <span class="fes-help"><?php echo $attr['help']; ?></span>
            </div>

        </div>

        <?php
    }

    /**
     * Prints a url field
     *
     * @param array $attr
     * @param int|null $post_id
     */
    function url( $attr, $post_id, $type ) {

        if ( $post_id ) {
            if ( $this->is_meta( $attr ) ) {
                $value = $this->get_meta( $post_id, $attr['name'], $type, true );
            } else {
                //must be user profile url
                $value = $this->get_user_data( $post_id, $attr['name'] );
            }
        } else {
            $value = $attr['default'];
        }
        ?>

        <div class="fes-fields">
            <input id="fes-<?php echo $attr['name']; ?>" type="url" class="url" data-required="<?php echo $attr['required'] ?>" data-type="text"<?php $this->required_html5( $attr ); ?> name="<?php echo esc_attr( $attr['name'] ); ?>" placeholder="<?php echo esc_attr( $attr['placeholder'] ); ?>" value="<?php echo esc_attr( $value ) ?>" size="<?php echo esc_attr( $attr['size'] ) ?>" />
            <span class="fes-help"><?php echo $attr['help']; ?></span>
        </div>

        <?php
    }

    /**
     * Prints a email field
     *
     * @param array $attr
     * @param int|null $post_id
     */
    function email( $attr, $post_id, $type = 'post' ) {
        if ( $post_id ) {
            if ( $this->is_meta( $attr ) ) {
                $value = $this->get_meta( $post_id, $attr['name'], $type, true );
            } else {
				$value = $this->get_user_data( $post_id, $attr['name'] );
            }
        } else {
            $value = $attr['default'];
        }
        ?>

        <div class="fes-fields">
            <input id="fes-<?php echo $attr['name']; ?>" type="email" class="email" data-required="<?php echo $attr['required'] ?>" data-type="text"<?php $this->required_html5( $attr ); ?> name="<?php echo esc_attr( $attr['name'] ); ?>" placeholder="<?php echo esc_attr( $attr['placeholder'] ); ?>" value="<?php echo esc_attr( $value ) ?>" size="<?php echo esc_attr( $attr['size'] ) ?>" />
            <span class="fes-help"><?php echo $attr['help']; ?></span>
        </div>

        <?php
    }
    /**
     * Prints a repeatable field
     *
     * @param array $attr
     * @param int|null $post_id
     */
    function repeat( $attr, $post_id, $type ) {
        $add = fes_assets_url .'img/add.png';
        $remove = fes_assets_url. 'img/remove.png';
        ?>

        <div class="fes-fields">

            <?php if ( isset( $attr['multiple'] ) ) { ?>
                <table>
                    <thead>
                        <tr>
                            <?php
                            $num_columns = count( $attr['columns'] );
                            foreach ($attr['columns'] as $column) {
                                ?>
                                <th>
                                    <?php echo $column; ?>
                                </th>
                            <?php } ?>

                            <th style="visibility: hidden;">
                                Actions
                            </th>
                        </tr>

                    </thead>
                    <tbody>

                        <?php
                        $items = $post_id ? $this->get_meta( $post_id, $attr['name'], $type, false ) : array();

                        if ( $items ) {
                            foreach ($items as $item_val) {
                                $column_vals = explode( self::$separator, $item_val );
                                ?>

                                <tr>
                                    <?php for ($count = 0; $count < $num_columns; $count++) { ?>
                                        <td>
                                            <input type="text" name="<?php echo $attr['name'] . '[' . $count . ']'; ?>[]" value="<?php echo esc_attr( $column_vals[$count] ); ?>" size="<?php echo esc_attr( $attr['size'] ) ?>" data-required="<?php echo $attr['required'] ?>" data-type="text"<?php $this->required_html5( $attr ); ?> />
                                        </td>
                                    <?php } ?>
                                    <td>
                                        <img class="fes-clone-field" alt="<?php esc_attr_e( 'Add another', 'edd_fes' ); ?>" title="<?php esc_attr_e( 'Add another', 'edd_fes' ); ?>" src="<?php echo $add; ?>">
                                        <img class="fes-remove-field" alt="<?php esc_attr_e( 'Remove this choice', 'edd_fes' ); ?>" title="<?php esc_attr_e( 'Remove this choice', 'edd_fes' ); ?>" src="<?php echo $remove; ?>">
                                    </td>
                                </tr>

                            <?php } //endforeach   ?>

                        <?php } else { ?>

                            <tr>
                                <?php for ($count = 0; $count < $num_columns; $count++) { ?>
                                    <td>
                                        <input type="text" name="<?php echo $attr['name'] . '[' . $count . ']'; ?>[]" size="<?php echo esc_attr( $attr['size'] ) ?>" data-required="<?php echo $attr['required'] ?>" data-type="text"<?php $this->required_html5( $attr ); ?> />
                                    </td>
                                <?php } ?>
                                <td>
                                    <img class="fes-clone-field" alt="<?php esc_attr_e( 'Add another', 'edd_fes' ); ?>" title="<?php esc_attr_e( 'Add another', 'edd_fes' ); ?>" src="<?php echo $add; ?>">
                                    <img class="fes-remove-field" alt="<?php esc_attr_e( 'Remove this choice', 'edd_fes' ); ?>" title="<?php esc_attr_e( 'Remove this choice', 'edd_fes' ); ?>" src="<?php echo $remove; ?>">
                                </td>
                            </tr>

                        <?php } ?>

                    </tbody>
                </table>

            <?php } else { ?>


                <table>
                    <?php
                    $items = $post_id ? explode( self::$separator, $this->get_meta( $post_id, $attr['name'], $type, true ) ) : array();

                    if ( $items ) {
                        foreach ($items as $item) {
                            ?>
                            <tr>
                                <td>
                                    <input id="fes-<?php echo $attr['name']; ?>" type="text" data-required="<?php echo $attr['required'] ?>" data-type="text"<?php $this->required_html5( $attr ); ?> name="<?php echo esc_attr( $attr['name'] ); ?>[]" placeholder="<?php echo esc_attr( $attr['placeholder'] ); ?>" value="<?php echo esc_attr( $item ) ?>" size="<?php echo esc_attr( $attr['size'] ) ?>" />
                                </td>
                                <td>
                                    <img style="cursor:pointer; margin:0 3px;" alt="add another choice" title="add another choice" class="fes-clone-field" src="<?php echo $add; ?>">
                                    <img style="cursor:pointer;" class="fes-remove-field" alt="remove this choice" title="remove this choice" src="<?php echo $remove; ?>">
                                </td>
                            </tr>
                        <?php } //endforeach    ?>
                    <?php } else { ?>

                        <tr>
                            <td>
                                <input id="fes-<?php echo $attr['name']; ?>" type="text" data-required="<?php echo $attr['required'] ?>" data-type="text"<?php $this->required_html5( $attr ); ?> name="<?php echo esc_attr( $attr['name'] ); ?>[]" placeholder="<?php echo esc_attr( $attr['placeholder'] ); ?>" value="<?php echo esc_attr( $attr['default'] ) ?>" size="<?php echo esc_attr( $attr['size'] ) ?>" />
                            </td>
                            <td>
                                <img style="cursor:pointer; margin:0 3px;" alt="add another choice" title="add another choice" class="fes-clone-field" src="<?php echo $add; ?>">
                                <img style="cursor:pointer;" class="fes-remove-field" alt="remove this choice" title="remove this choice" src="<?php echo $remove; ?>">
                            </td>
                        </tr>

                    <?php } ?>

                </table>
            <?php } ?>
            <span class="fes-help"><?php echo $attr['help']; ?></span>
        </div>

        <?php
    }

    /**
     * Prints a HTML field
     *
     * @param array $attr
     */
    function html( $attr ) {
        ?>
        <div class="fes-fields">
            <?php echo do_shortcode( $attr['html'] ); ?>
        </div>
        <?php
    }

    /**
     * Prints a HTML field
     *
     * @param array $attr
     */
    function toc( $attr, $post_id ) {

        if ( $post_id ) {
            return;
        }
        ?>
        <div class="fes-label">
            &nbsp;
        </div>

        <div class="fes-fields">
            <span data-required="yes" data-type="radio"></span>

            <textarea rows="10" cols="40" disabled="disabled" name="toc"><?php echo $attr['description']; ?></textarea>
            <label>
                <input type="checkbox" name="fes_accept_toc" required="required" /> <?php echo $attr['label']; ?>
            </label>
        </div>
        <?php
    }

 
    /**
     * Prints a section break
     *
     * @param array $attr
     * @param int|null $post_id
     */
    function section_break( $attr ) {
        ?>
        <div class="fes-section-wrap">
            <h2 class="fes-section-title"><?php echo $attr['label']; ?></h2>
            <div class="fes-section-details"><?php echo $attr['description']; ?></div>
        </div>
        <?php
    }

    /**
     * Prints a action hook
     *
     * @param array $attr
     * @param int $form_id
     * @param int|null $post_id
     * @param array $form_settings
     */
    function action_hook( $attr, $form_id, $post_id, $form_settings ) {

        if ( !empty( $attr['label'] ) ) {
            do_action( $attr['label'], $form_id, $post_id, $form_settings );
        }
    }

    /**
     * Prints a date field
     *
     * @param array $attr
     * @param int|null $post_id
     */
    function date( $attr, $post_id, $type ) {

        $value = $post_id ? $this->get_meta( $post_id, $attr['name'], $type, true ) : '';
        ?>

        <div class="fes-fields">
            <input id="fes-date-<?php echo $attr['name']; ?>" type="text" class="datepicker" data-required="<?php echo $attr['required'] ?>" data-type="text"<?php $this->required_html5( $attr ); ?> name="<?php echo esc_attr( $attr['name'] ); ?>" value="<?php echo esc_attr( $value ) ?>" size="30" />
            <span class="fes-help"><?php echo $attr['help']; ?></span>
        </div>
        <script type="text/javascript">
            jQuery(function($) {
        <?php if ( $attr['time'] == 'yes' ) { ?>
                                $("#fes-date-<?php echo $attr['name']; ?>").datetimepicker({ dateFormat: '<?php echo $attr["format"]; ?>' });
        <?php } else { ?>
                                $("#fes-date-<?php echo $attr['name']; ?>").datepicker({ dateFormat: '<?php echo $attr["format"]; ?>' });
        <?php } ?>
            });
        </script>

        <?php
    }

    /**
     * Prints a map field
     *
     * @param array $attr
     * @param int|null $post_id
     */
    function map( $attr, $post_id, $type ) {

        $value = $post_id ? $this->get_meta( $post_id, $attr['name'], $type, true ) : '';
        $type = $attr['show_lat'] == 'yes' ? 'text' : 'hidden';

        if ( $post_id ) {
            list( $def_lat, $def_long ) = explode( ',', $value );
        } else {
            list( $def_lat, $def_long ) = explode( ',', $attr['default_pos'] );
        }
        ?>

        <div class="fes-fields">
            <input id="fes-map-lat-<?php echo $attr['name']; ?>" type="<?php echo $type; ?>" data-required="<?php echo $attr['required'] ?>" data-type="text"<?php $this->required_html5( $attr ); ?> name="<?php echo esc_attr( $attr['name'] ); ?>" value="<?php echo esc_attr( $value ) ?>" size="30" />

            <?php if ( $attr['address'] == 'yes' ) { ?>
                <input id="fes-map-add-<?php echo $attr['name']; ?>" type="text" value="" name="find-address" placeholder="<?php _e( 'Type an address to find', 'edd_fes' ); ?>" size="30" />
                <button class="fes-button button" id="fes-map-btn-<?php echo $attr['name']; ?>"><?php _e( 'Find Address', 'edd_fes' ); ?></button>
            <?php } ?>

            <div class="google-map" style="margin: 10px 0; height: 250px; width: 450px;" id="fes-map-<?php echo $attr['name']; ?>"></div>
            <span class="fes-help"><?php echo $attr['help']; ?></span>
        </div>
        <script type="text/javascript">

            (function($) {
                $(function() {
                    var def_zoomval = <?php echo $attr['zoom']; ?>;
                    var def_longval = <?php echo $def_long ? $def_long : 0; ?>;
                    var def_latval = <?php echo $def_lat ? $def_lat : 0; ?>;
                    var curpoint = new google.maps.LatLng(def_latval, def_longval),
                        geocoder   = new window.google.maps.Geocoder(),
                        $map_area = $('#fes-map-<?php echo $attr['name']; ?>'),
                        $input_area = $( '#fes-map-lat-<?php echo $attr['name']; ?>' ),
                        $input_add = $( '#fes-map-add-<?php echo $attr['name']; ?>' ),
                        $find_btn = $( '#fes-map-btn-<?php echo $attr['name']; ?>' );

                    autoCompleteAddress();

                    $find_btn.on('click', function(e) {
                        e.preventDefault();

                        geocodeAddress( $input_add.val() );
                    });

                    var gmap = new google.maps.Map( $map_area[0], {
                        center: curpoint,
                        zoom: def_zoomval,
                        mapTypeId: window.google.maps.MapTypeId.ROADMAP
                    });

                    var marker = new window.google.maps.Marker({
                        position: curpoint,
                        map: gmap,
                        draggable: true
                    });

                    window.google.maps.event.addListener( gmap, 'click', function ( event ) {
                        marker.setPosition( event.latLng );
                        updatePositionInput( event.latLng );
                    } );

                    window.google.maps.event.addListener( marker, 'drag', function ( event ) {
                        updatePositionInput(event.latLng );
                    } );

                    function updatePositionInput( latLng ) {
                        $input_area.val( latLng.lat() + ',' + latLng.lng() );
                    }

                    function updatePositionMarker() {
                        var coord = $input_area.val(),
                            pos, zoom;

                        if ( coord ) {
                            pos = coord.split( ',' );
                            marker.setPosition( new window.google.maps.LatLng( pos[0], pos[1] ) );

                            zoom = pos.length > 2 ? parseInt( pos[2], 10 ) : 12;

                            gmap.setCenter( marker.position );
                            gmap.setZoom( zoom );
                        }
                    }

                    function geocodeAddress( address ) {
                        geocoder.geocode( {'address': address}, function ( results, status ) {
                            if ( status == window.google.maps.GeocoderStatus.OK ) {
                                updatePositionInput( results[0].geometry.location );
                                marker.setPosition( results[0].geometry.location );
                                gmap.setCenter( marker.position );
                                gmap.setZoom( 15 );
                            }
                        } );
                    }

                    function autoCompleteAddress(){
                        if (!$input_add) return null;

                        $input_add.autocomplete({
                            source: function(request, response) {
                                // TODO: add 'region' option, to help bias geocoder.
                                geocoder.geocode( {'address': request.term }, function(results, status) {
                                    response(jQuery.map(results, function(item) {
                                        return {
                                            label     : item.formatted_address,
                                            value     : item.formatted_address,
                                            latitude  : item.geometry.location.lat(),
                                            longitude : item.geometry.location.lng()
                                        };
                                    }));
                                });
                            },
                            select: function(event, ui) {

                                $input_area.val(ui.item.latitude + ',' + ui.item.longitude );

                                var location = new window.google.maps.LatLng(ui.item.latitude, ui.item.longitude);

                                gmap.setCenter(location);
                                // Drop the Marker
                                setTimeout( function(){
                                    marker.setValues({
                                        position    : location,
                                        animation   : window.google.maps.Animation.DROP
                                    });
                                }, 1500);
                            }
                        });
                    }

                });
            })(jQuery);
        </script>

        <?php
    }

}
new CFM_Render_Form();