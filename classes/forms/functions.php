<?php
function fes_allowed_extensions() {
    $extensions = array(
        'images' => array('ext' => 'jpg,jpeg,gif,png,bmp', 'label' => __( 'Images', 'edd_fes' )),
        'audio' => array('ext' => 'mp3,wav,ogg,wma,mka,m4a,ra,mid,midi', 'label' => __( 'Audio', 'edd_fes' )),
        'video' => array('ext' => 'avi,divx,flv,mov,ogv,mkv,mp4,m4v,divx,mpg,mpeg,mpe', 'label' => __( 'Videos', 'edd_fes' )),
        'pdf' => array('ext' => 'pdf', 'label' => __( 'PDF', 'edd_fes' )),
        'office' => array('ext' => 'doc,ppt,pps,xls,mdb,docx,xlsx,pptx,odt,odp,ods,odg,odc,odb,odf,rtf,txt', 'label' => __( 'Office Documents', 'edd_fes' )),
        'zip' => array('ext' => 'zip,gz,gzip,rar,7z', 'label' => __( 'Zip Archives' )),
        'exe' => array('ext' => 'exe', 'label' => __( 'Executable Files', 'edd_fes' )),
        'csv' => array('ext' => 'csv', 'label' => __( 'CSV', 'edd_fes' ))
    );

    return apply_filters( 'fes_allowed_extensions', $extensions );
}

/**
 * Associate attachemnt to a post
 *
 * @since 2.0
 *
 * @param type $attachment_id
 * @param type $post_id
 */
function fes_associate_attachment( $attachment_id, $post_id ) {
    wp_update_post( array(
        'ID' => $attachment_id,
        'post_parent' => $post_id
    ) );
}


/**
 * Show custom fields in post content area (beta)
 *
 * @global object $post
 * @param string $content
 * @return string
 */
function fes_show_custom_fields( $content ) {
    global $post;

    $show_custom = false;//EDD_FES()->fes_options->get_option( 'edd_fes_show_custom_meta');
	if($post->post_type != 'download'){
		return $content;
	}
    if (!$show_custom) {
        return $content;
    }

    $form_id = EDD_FES()->fes_options->get_option( 'fes-submission-form');

    $html = '<ul class="fes_customs">';

    $form_vars = get_post_meta( $form_id, 'edd-checkout-fields', true );
    $meta = array();

    if ( $form_vars ) {
        foreach ($form_vars as $attr) {
            if ( isset( $attr['is_meta'] ) && $attr['is_meta'] == 'yes' ) {
                $meta[] = $attr;
            }
        }

        if ( !$meta ) {
            return $content;
        }

        foreach ($meta as $attr) {
            $field_value = get_post_meta( $post->ID, $attr['name'] );

            if ( $attr['input_type'] == 'hidden' ) {
                continue;
            }

            if ( $attr['input_type'] == 'image_upload' || $attr['input_type'] == 'file_upload' ) {
                $image_html = '<li><label>' . $attr['label'] . ':</label> ';

                if ( $field_value ) {
                    foreach ($field_value as $attachment_id) {

                        if ( $attr['input_type'] == 'image_upload' ) {
                            $thumb = wp_get_attachment_image( $attachment_id, 'thumbnail' );
                        } else {
                            $thumb = get_post_field( 'post_title', $attachment_id );
                        }

                        $full_size = wp_get_attachment_url( $attachment_id );
                        $image_html .= sprintf( '<a href="%s">%s</a> ', $full_size, $thumb );
                    }
                }

                $html .= $image_html . '</li>';

            } elseif ( $attr['input_type'] == 'map' ) {
                ob_start();
                fes_shortcode_map_post($attr['name'], $post->ID);
                $html .= ob_get_clean();
            } else {

                $value = get_post_meta( $post->ID, $attr['name'] );
                $html .= sprintf( '<li><label>%s</label>: %s</li>', $attr['label'], make_clickable( implode( ', ', $value ) ) );
            }
        }
    }

    $html .= '</ul>';

    return $content . $html;
}

add_filter( 'the_content', 'fes_show_custom_fields' );

/**
 * Map display shortcode
 *
 * @param string $meta_key
 * @param int $post_id
 * @param array $args
 */
function fes_shortcode_map( $location, $post_id = null, $args = array(), $meta_key = '' ) {
    global $post;

    // compatibility
    if ( $post_id ) {
        fes_shortcode_map_post( $location, $post_id, $args );
        return;
    }

    $default = array('width' => 450, 'height' => 250, 'zoom' => 12);
    $args = wp_parse_args( $args, $default );

    list( $def_lat, $def_long ) = explode( ',', $location );
    $def_lat = $def_lat ? $def_lat : 0;
    $def_long = $def_long ? $def_long : 0;
    ?>

    <div class="google-map" style="margin: 10px 0; height: <?php echo $args['height']; ?>px; width: <?php echo $args['width']; ?>px;" id="fes-map-<?php echo $meta_key . $post->ID; ?>"></div>

    <script type="text/javascript">
        jQuery(function($){
            var curpoint = new google.maps.LatLng(<?php echo $def_lat; ?>, <?php echo $def_long; ?>);

            var gmap = new google.maps.Map( $('#fes-map-<?php echo $meta_key . $post->ID; ?>')[0], {
                center: curpoint,
                zoom: <?php echo $args['zoom']; ?>,
                mapTypeId: window.google.maps.MapTypeId.ROADMAP
            });

            var marker = new window.google.maps.Marker({
                position: curpoint,
                map: gmap,
                draggable: true
            });

        });
    </script>
    <?php
}

/**
 * Map shortcode for users
 *
 * @param string $meta_key
 * @param int $user_id
 * @param array $args
 */
function fes_shortcode_map_user( $meta_key, $user_id = null, $args = array() ) {
    $location = get_user_meta( $user_id, $meta_key, true );
    fes_shortcode_map( $location, null, $args, $meta_key );
}

/**
 * Map shortcode post posts
 *
 * @global object $post
 * @param string $meta_key
 * @param int $post_id
 * @param array $args
 */
function fes_shortcode_map_post( $meta_key, $post_id = null, $args = array() ) {
    global $post;

    if ( !$post_id ) {
        $post_id = $post->ID;
    }

    $location = get_post_meta( $post_id, $meta_key, true );
    fes_shortcode_map( $location, null, $args, $meta_key );
}

function fes_meta_shortcode( $atts ) {
    global $post;

    extract( shortcode_atts( array(
        'name' => '',
        'type' => 'normal',
        'size' => 'thumbnail',
        'height' => 250,
        'width' => 450,
        'zoom' => 12
    ), $atts ) );

    if ( empty( $name ) ) {
        return;
    }

    if ( $type == 'image' || $type == 'file' ) {
        $images = get_post_meta( $post->ID, $name );

        if ( $images ) {
            $html = '';
            foreach ($images as $attachment_id) {

                if ( $type == 'image' ) {
                    $thumb = wp_get_attachment_image( $attachment_id, $size );
                } else {
                    $thumb = get_post_field( 'post_title', $attachment_id );
                }

                $full_size = wp_get_attachment_url( $attachment_id );
                $html .= sprintf( '<a href="%s">%s</a> ', $full_size, $thumb );
            }

            return $html;
        }

    } elseif ( $type == 'map' ) {
        ob_start();
        fes_shortcode_map( $name, $post->ID, array('width' => $width, 'height' => $height, 'zoom' => $zoom ) );
        return ob_get_clean();

    } elseif ( $type == 'repeat' ) {
        return implode( '; ', get_post_meta( $post->ID, $name ) );
    } else {
        return implode( ', ', get_post_meta( $post->ID, $name ) );
    }
}

add_shortcode( 'fes-meta', 'fes_meta_shortcode' );


/**
 * Get attachment ID from a URL
 *
 * @since 2.1.8
 *
 * @link http://philipnewcomer.net/2012/11/get-the-attachment-id-from-an-image-url-in-wordpress/ Original Implementation
 *
 * @global type $wpdb
 * @param type $attachment_url
 * @return type
 */
function fes_get_attachment_id_from_url( $attachment_url = '' ) {

    global $wpdb;
    $attachment_id = false;

    // If there is no url, return.
    if ( '' == $attachment_url )
        return;

    // Get the upload directory paths
    $upload_dir_paths = wp_upload_dir();

    // Make sure the upload path base directory exists in the attachment URL, to verify that we're working with a media library image
    if ( false !== strpos( $attachment_url, $upload_dir_paths['baseurl'] ) ) {

        // If this is the URL of an auto-generated thumbnail, get the URL of the original image
        $attachment_url = preg_replace( '/-\d+x\d+(?=\.(jpg|jpeg|png|gif)$)/i', '', $attachment_url );

        // Remove the upload path base directory from the attachment URL
        $attachment_url = str_replace( $upload_dir_paths['baseurl'] . '/', '', $attachment_url );

        // Finally, run a custom database query to get the attachment ID from the modified attachment URL
        $attachment_id = $wpdb->get_var( $wpdb->prepare( "SELECT wposts.ID FROM $wpdb->posts wposts, $wpdb->postmeta wpostmeta WHERE wposts.ID = wpostmeta.post_id AND wpostmeta.meta_key = '_wp_attached_file' AND wpostmeta.meta_value = '%s' AND wposts.post_type = 'attachment'", $attachment_url ) );
    }

    return $attachment_id;
}