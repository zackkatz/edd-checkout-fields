<?php
if ( !defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin Form UI Builder
 */
class CFM_Admin_Form {

    private $form_data_key = 'fes-form';
    private $form_settings_key = 'fes-form_settings';

    /**
     * Add neccessary actions and filters
     *
     * @return void
     */
    function __construct() {
        add_filter( 'post_updated_messages', array($this, 'form_updated_message') );
        add_action( 'admin_footer-edit.php', array($this, 'add_form_button_style') );
        add_action( 'admin_footer-post.php', array($this, 'add_form_button_style') );
        add_action( 'admin_head', array( $this, 'menu_icon' ) );
		add_action( 'bulk_actions-edit-fes-forms', array( $this, 'remove_bulk_actions'),10,2);
		add_action( 'views_edit-fes-forms', array( $this, 'remove_bulk_actions'),10,2);
		add_action( 'months_dropdown_results', array( $this, 'remove_months'),10,3);
		add_action('admin_head',  array( $this, 'fes_remove_search_and_filter'));
        // meta boxes
        add_action( 'add_meta_boxes_fes-forms', array($this, 'add_meta_boxes') );

        // custom columns
        add_filter( 'manage_edit-fes-forms_columns', array( $this, 'admin_column' ) );
        add_action( 'manage_fes-forms_posts_custom_column', array( $this, 'admin_column_value' ), 10, 2 );

        // ajax actions for post forms
        add_action( 'wp_ajax_fes-form_dump', array( $this, 'form_dump' ) );
        add_action( 'wp_ajax_fes-form_add_el', array( $this, 'ajax_post_add_element' ) );

		add_action( 'save_post', array( $this, 'save_form_meta' ), 1, 2 ); // save the custom fields
    }
	function fes_remove_search_and_filter(){
		$screen = get_current_screen();
		if (!is_admin() || $screen->id !== 'edit-fes-forms'){
			return;
		}
		echo '<style type="text/css">
			#post-query-submit{ display: none !important; } 
			.search-box{display: none !important}
			.tablenav.top{display: none !important}
			.wp-list-table.widefat.fixed.posts { margin-top: 20px; }
			</style>';
	}
	function remove_bulk_actions($actions){
		return array();
	}
	
	function remove_months($actions, $cpt){
		if($cpt !== 'fes-forms'){
			return $actions;
		}
		return array();
	}
    /**
     * Enqueue scripts and styles for form builder
     *
     * @global string $pagenow
     * @return void
     */


    function add_form_button_style() {
        global $pagenow, $post_type;

        if ( $post_type != 'fes-forms' ) {
            return;
        }
        ?>
        <style type="text/css">
            .wrap .add-new-h2, .wrap .add-new-h2:active {
                background: #21759b;
                color: #fff;
                text-shadow: 0 1px 1px #446E81;
            }
			#post-body-content{
				display: none;
			}
           #fes-metabox-fields{
                position: fixed;
            }
		   #major-publishing-actions {
				padding: 0 !important;
				background: #FFF !important;
				border-top: none !important;
			}
        </style>
        <?php
    }
	
    function form_updated_message( $messages ) {
        $message = array(
             0 => '',
             1 => __( 'Form updated.', 'edd_fes' ),
             2 => __( 'Custom field updated.', 'edd_fes' ),
             3 => __( 'Custom field deleted.', 'edd_fes' ),
             4 => __( 'Form updated.', 'edd_fes' ),
             5 => isset($_GET['revision']) ? sprintf( __( 'Form restored to revision from %s', 'edd_fes' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
             6 => __( 'Form published.', 'edd_fes' ),
             7 => __( 'Form saved.', 'edd_fes' ),
             8 => __( 'Form submitted.', 'edd_fes' ),
             9 => '',
            10 => __( 'Form draft updated.', 'edd_fes' ),
        );

        $messages['fes-forms'] = $message;
        $messages['fes_profile'] = $message;

        return $messages;
    }

    function menu_icon() {
        ?>
        <style type="text/css">
            .icon32-posts-fes-forms{
                background: url('<?php echo admin_url( "images/icons32.png" ); ?>') no-repeat 2% 35%;
            }
        </style>
        <?php
    }

    /**
     * Columns form builder list table
     *
     * @param type $columns
     * @return string
     */
    function admin_column( $columns ) {
        $columns = array(
            'cb' => '<input type="checkbox" />',
            'title' => __( 'Form Name', 'edd_fes' ),
        );

        return $columns;
    }

    /**
     * Add meta boxes to form builders
     *
     * @return void
     */
    function add_meta_boxes() {
		global $post;

		if(get_the_ID() == EDD_CFM()->fes_options->get_option( 'fes-submission-form')){
        add_meta_box( 'fes-metabox-editor', __( 'Submission Form Editor', 'edd_fes' ), array($this, 'metabox_post_form'), 'fes-forms', 'normal', 'high' );
        add_meta_box( 'fes-metabox-fields', __( 'EDD CFM Submission Form Guide', 'edd_fes' ), array($this, 'form_elements_post'), 'fes-forms', 'side', 'core' );
		}
		if(get_the_ID() == EDD_CFM()->fes_options->get_option( 'fes-profile-form')){
        add_meta_box( 'fes-metabox-editor', __( 'Profile Form Editor', 'edd_fes' ), array($this, 'metabox_profile_form'), 'fes-forms', 'normal', 'high' );
        add_meta_box( 'fes-metabox-fields', __( 'Profile Submission Guide', 'edd_fes' ), array($this, 'form_elements_profile'), 'fes-forms', 'side', 'core' );
		}
		remove_meta_box('submitdiv', 'fes-forms', 'side');
        remove_meta_box('slugdiv', 'fes-forms', 'normal');
	}

    function publish_button() {
        global $post, $pagenow;
        ?>
		<h2><?php _e('Step 3: Save the Form','edd_fes');?></h2>
        <div class="submitbox" id="submitpost" style="float:left">
            <div id="major-publishing-actions">
                <div id="publishing-action">
                    <span class="spinner"></span>
                        <input name="original_publish" type="hidden" id="original_publish" value="<?php esc_attr_e( 'Save' ) ?>" />
                        <input name="save" type="submit" class="button button-primary button-large" id="publish" accesskey="p" value="<?php esc_attr_e( 'Save' ) ?>" />
                </div>
                <div class="fes-clear"></div>
            </div>
       </div>
        <?php
    }

     function metabox_post_form( $post ) {
        ?>
        <h1><?php _e('CFM Submission Form Editor','edd_fes');?></h1>
        <div class="tab-content">
            <div id="fes-metabox" class="group">
                <?php $this->edit_form_area(); ?>
            </div>
            <?php do_action( 'fes_post_form_tab_content' ); ?>
        </div>
        <?php
    }

    function metabox_profile_form( $post ) {
        ?>
        <h1><?php _e('CFM Profile Form Editor','edd_fes');?></h1>
        <div class="tab-content">
            <div id="fes-metabox" class="group">
                 <?php $this->edit_form_area_profile(); ?>
            </div>
             <?php do_action( 'fes_profile_form_tab_content' ); ?>
        </div>
        <?php
    }

    function form_elements_common($form = 'post') {
        $title = esc_attr( __( 'Click to add to the editor', 'edd_fes' ) );
        ?>
        <h2><?php _e( 'Step 2: Insert Custom Fields', 'edd_fes' ); ?></h2>
        <div class="fes-form-buttons">
            <button class="button" data-name="custom_text" data-type="text" title="<?php echo $title; ?>"><?php _e( 'Text', 'edd_fes' ); ?></button>
            <button class="button" data-name="custom_textarea" data-type="textarea" title="<?php echo $title; ?>"><?php _e( 'Textarea', 'edd_fes' ); ?></button>
            <button class="button" data-name="custom_select" data-type="select" title="<?php echo $title; ?>"><?php _e( 'Dropdown', 'edd_fes' ); ?></button>
            <button class="button" data-name="custom_date" data-type="date" title="<?php echo $title; ?>"><?php _e( 'Date', 'edd_fes' ); ?></button>
            <button class="button" data-name="custom_multiselect" data-type="multiselect" title="<?php echo $title; ?>"><?php _e( 'Multi Select', 'edd_fes' ); ?></button>
            <button class="button" data-name="custom_radio" data-type="radio" title="<?php echo $title; ?>"><?php _e( 'Radio', 'edd_fes' ); ?></button>
            <button class="button" data-name="custom_checkbox" data-type="checkbox" title="<?php echo $title; ?>"><?php _e( 'Checkbox', 'edd_fes' ); ?></button>
            <button class="button" data-name="custom_image" data-type="image" title="<?php echo $title; ?>"><?php _e( 'Image Upload', 'edd_fes' ); ?></button>
            <button class="button" data-name="custom_file" data-type="file" title="<?php echo $title; ?>"><?php _e( 'File Upload', 'edd_fes' ); ?></button>
            <button class="button" data-name="custom_url" data-type="url" title="<?php echo $title; ?>"><?php _e( 'URL', 'edd_fes' ); ?></button>
            <button class="button" data-name="custom_email" data-type="email" title="<?php echo $title; ?>"><?php _e( 'Email', 'edd_fes' ); ?></button>
            <button class="button" data-name="custom_repeater" data-type="repeat" title="<?php echo $title; ?>"><?php _e( 'Repeat Field', 'edd_fes' ); ?></button>
            <button class="button" data-name="custom_hidden" data-type="hidden" title="<?php echo $title; ?>"><?php _e( 'Hidden Field', 'edd_fes' ); ?></button>
            <button class="button" data-name="custom_map" data-type="map" title="<?php echo $title; ?>"><?php _e( 'Google Maps', 'edd_fes' ); ?></button>
            <?php if($form == 'post'){ ?>
			<button class="button" data-name="recaptcha" data-type="captcha" title="<?php echo $title; ?>"><?php _e( 'reCaptcha', 'edd_fes' ); ?></button>
            <button class="button" data-name="really_simple_captcha" data-type="rscaptcha" title="<?php echo $title; ?>"><?php _e( 'Really Simple Captcha', 'edd_fes' ); ?></button>
			<?php } ?>
			<button class="button" data-name="section_break" data-type="break" title="<?php echo $title; ?>"><?php _e( 'Section Break', 'edd_fes' ); ?></button>
            <button class="button" data-name="custom_html" data-type="html" title="<?php echo $title; ?>"><?php _e( 'HTML', 'edd_fes' ); ?></button>
            <button class="button" data-name="action_hook" data-type="action" title="<?php echo $title; ?>"><?php _e( 'Do Action', 'edd_fes' ); ?></button>
            <button class="button" data-name="toc" data-type="action" title="<?php echo $title; ?>"><?php _e( 'Term &amp; Condition', 'edd_fes' ); ?></button>

            <?php do_action( 'fes-form_buttons_other' ); ?>
        </div>

        <?php
    }

    /**
     * Form elements for post form builder
     *
     * @return void
     */
    function form_elements_post() {
		?>
        <div class="fes-loading hide"></div>
        <h2><?php _e( 'Step 1: Add EDD Fields', 'edd_fes' ); ?></h2>
        <div class="fes-form-buttons">
            <button class="button" data-name="post_title" data-type="post_title" title="<?php _e( 'Click to add to the editor', 'edd_fes' ); ?>"><?php _e( 'Title (required)', 'edd_fes' ); ?></button>
			<button class="button" data-name="post_content" data-type="post_content" title="<?php _e( 'Click to add to the editor', 'edd_fes' ); ?>"><?php _e( 'Description (required)', 'edd_fes' ); ?></button>
			<button class="button" data-name="featured_image" data-type="featured_image" title="<?php _e( 'Click to add to the editor', 'edd_fes' ); ?>"><?php _e( 'Featured Image', 'edd_fes' ); ?></button>
			<button class="button" data-name="download_category" data-type="download_category" title="<?php _e( 'Click to add to the editor', 'edd_fes' ); ?>"><?php _e( 'Categories', 'edd_fes' ); ?></button>
			<button class="button" data-name="download_tag" data-type="download_tag" title="<?php _e( 'Click to add to the editor', 'edd_fes' ); ?>"><?php _e( 'Tags', 'edd_fes' ); ?></button>
			<button class="button" data-name="multiple_pricing" data-type="multiple_pricing" title="<?php _e( 'Click to add to the editor', 'edd_fes' ); ?>"><?php _e( 'Prices and Files', 'edd_fes' ); ?></button>
			<button class="button" data-name="post_excerpt" data-type="post_excerpt" title="<?php _e( 'Click to add to the editor', 'edd_fes' ); ?>"><?php _e( 'Excerpt', 'edd_fes' ); ?></button>
			<?php do_action( 'fes-form_buttons_post' ); ?>
	    </div>
		<?php 
        $this->form_elements_common('post');
        $this->publish_button();
    }

    /**
     * Form elements for Profile Builder
     *
     * @return void
     */
    function form_elements_profile() {
        ?>

        <div class="fes-loading hide"></div>

        <h2><?php _e( 'Step 1: Add Common Fields', 'edd_fes' ); ?></h2>
        <div class="fes-form-buttons">
            <button class="button" data-name="user_login" data-type="text"><?php _e( 'Username', 'edd_fes' ); ?></button>
            <button class="button" data-name="password" data-type="password"><?php _e( 'Password', 'edd_fes' ); ?></button>
            <button class="button" data-name="user_email" data-type="category"><?php _e( 'E-mail', 'edd_fes' ); ?></button>
            <button class="button" data-name="first_name" data-type="textarea"><?php _e( 'First Name', 'edd_fes' ); ?></button>
            <button class="button" data-name="last_name" data-type="textarea"><?php _e( 'Last Name', 'edd_fes' ); ?></button>
            <button class="button" data-name="nickname" data-type="text"><?php _e( 'Nickname', 'edd_fes' ); ?></button>
            <button class="button" data-name="display_name" data-type="text"><?php _e( 'Display Name', 'edd_fes' ); ?></button>
            <button class="button" data-name="user_bio" data-type="textarea"><?php _e( 'Biographical Info', 'edd_fes' ); ?></button>
            <button class="button" data-name="user_url" data-type="text"><?php _e( 'Website', 'edd_fes' ); ?></button>
            <button class="button" data-name="user_avatar" data-type="avatar"><?php _e( 'Avatar', 'edd_fes' ); ?></button>
			<?php if (EDD_CFM()->vendors->is_commissions_active()){ ?>
            <button class="button" data-name="eddc_user_paypal" data-type="eddc_user_paypal"><?php _e( 'PayPal Email', 'edd_fes' ); ?></button>
            <?php } ?>
			<?php do_action( 'fes-form_buttons_user' ); ?>
        </div>

        <?php
        $this->form_elements_common('user');
        $this->publish_button();
    }

    /**
     * Saves the form settings
     *
     * @param int $post_id
     * @param object $post
     * @return int|void
     */
    function save_form_meta( $post_id, $post ) {
        if ( !isset($_POST['fes-form_editor'])) {
            return $post->ID;
        }

        if ( !wp_verify_nonce( $_POST['fes-form_editor'], plugin_basename( __FILE__ ) ) ) {
            return $post->ID;
        }

        // Is the user allowed to edit the post or page?
        if ( !current_user_can( 'edit_post', $post->ID ) ) {
            return $post->ID;
        }

        update_post_meta( $post->ID, $this->form_data_key, $_POST['fes_input'] );
    }

    /**
     * Edit form elements area for post
     *
     * @global object $post
     * @global string $pagenow
     */
    function edit_form_area() {
        global $post, $pagenow;

        $form_inputs = get_post_meta( $post->ID, $this->form_data_key, true );
        ?>

        <input type="hidden" name="fes-form_editor" id="fes-form_editor" value="<?php echo wp_create_nonce( plugin_basename( __FILE__ ) ); ?>" />

        <div style="margin-bottom: 10px">
          <button class="button fes-collapse"><?php _e( 'Toggle All Fields Open/Close', 'edd_fes' ); ?></button>
        </div>
		<?php if ( empty( $form_inputs ) ){ ?>		
        <div class="fes-updated">
            <p><?php _e( 'Let\'s make the EDD Submissions form. Start by adding the required fields on the upper right', 'edd_fes' ); ?></p>
        </div>
		<?php } ?>
        <ul id="fes-form-editor" class="fes-form-editor unstyled">

        <?php
        if ($form_inputs) {
            $count = 0;
            foreach ($form_inputs as $order => $input_field) {
                $name = ucwords( str_replace( '_', ' ', $input_field['template'] ) );

                if ( $input_field['template'] == 'taxonomy') {
                    CFM_Admin_Template_Post::$input_field['template']( $count, $name, $input_field['name'], $input_field );
                } else {
                    CFM_Admin_Template_Post::$input_field['template']( $count, $name, $input_field );
                }

                $count++;
            }
        }
        ?>
        </ul>

        <?php
    }

    /**
     * Edit form elements area for profile
     *
     * @global object $post
     * @global string $pagenow
     */
    function edit_form_area_profile() {
        global $post, $pagenow;

        $form_inputs = get_post_meta( $post->ID, $this->form_data_key, true );
        ?>

        <input type="hidden" name="fes-form_editor" id="fes-form_editor" value="<?php echo wp_create_nonce( plugin_basename( __FILE__ ) ); ?>" />

        <div style="margin-bottom: 10px">
            <button class="button fes-collapse"><?php _e( 'Toggle All', 'edd_fes' ); ?></button>
        </div>
		<?php if ( empty( $form_inputs ) ){ ?>
        <div class="fes-updated">
            <p><?php _e( 'Welcome to the EDD CFM Profile Form Editor! The fields you can add are to the right.', 'edd_fes' ); ?></p>
        </div>
		<?php } ?>
        <ul id="fes-form-editor" class="fes-form-editor unstyled">

        <?php
        if ($form_inputs) {
            $count = 0;
            foreach ($form_inputs as $order => $input_field) {
                $name = ucwords( str_replace( '_', ' ', $input_field['template'] ) );

                CFM_Admin_Template_Profile::$input_field['template']( $count, $name, $input_field );

                $count++;
            }
        }
        ?>
        </ul>

        <?php
    }

    /**
     * Ajax Callback handler for inserting fields in forms
     *
     * @return void
     */
    function ajax_post_add_element() {

        $name = $_POST['name'];
        $type = $_POST['type'];
        $field_id = $_POST['order'];

        switch ($name) {
			case 'post_title':
				 CFM_Admin_Template_Post::post_title( $field_id, __('Title','edd_fes'));
				break;
			case 'post_content':
				CFM_Admin_Template_Post::post_content($field_id, __('Body','edd_fes'));
				break;
			case 'post_excerpt':
				CFM_Admin_Template_Post::post_excerpt( $field_id, __('Excerpt','edd_fes'));			
				break;
			case 'featured_image':
				  CFM_Admin_Template_Post::featured_image( $field_id, __('Featured Image','edd_fes'));
				break;
			case 'download_category':
				CFM_Admin_Template_Post::taxonomy( $field_id, 'Category', __( 'download_category','edd_fes') );			
				break;
			case 'download_tag':
				CFM_Admin_Template_Post::taxonomy( $field_id, 'Tags', __( 'download_tag','edd_fes') );
				break;
			case 'multiple_pricing':
				CFM_Admin_Template_Post::multiple_pricing( $field_id, __( 'Prices and Files','edd_fes'));	
				break;
            case 'custom_text':
			    CFM_Admin_Template_Post::text_field( $field_id, __( 'Custom field: Text','edd_fes'));
                break;

            case 'custom_textarea':
                CFM_Admin_Template_Post::textarea_field( $field_id, __( 'Custom field: Textarea','edd_fes'));
                break;

            case 'custom_select':
                CFM_Admin_Template_Post::dropdown_field( $field_id, __( 'Custom field: Select','edd_fes'));
                break;

            case 'custom_multiselect':
                CFM_Admin_Template_Post::multiple_select( $field_id, __( 'Custom field: Multiselect','edd_fes'));
                break;

            case 'custom_radio':
                CFM_Admin_Template_Post::radio_field( $field_id, __( 'Custom field: Radio','edd_fes'));
                break;

            case 'custom_checkbox':
                CFM_Admin_Template_Post::checkbox_field( $field_id, __( 'Custom field: Checkbox','edd_fes'));
                break;

            case 'custom_image':
                CFM_Admin_Template_Post::image_upload( $field_id, __( 'Custom field: Image','edd_fes'));
                break;

            case 'custom_file':
                CFM_Admin_Template_Post::file_upload( $field_id, __( 'Custom field: File Upload','edd_fes'));
                break;

            case 'custom_url':
                CFM_Admin_Template_Post::website_url( $field_id, __( 'Custom field: URL','edd_fes'));
                break;

            case 'custom_email':
                CFM_Admin_Template_Post::email_address( $field_id, __( 'Custom field: E-Mail','edd_fes'));
                break;

            case 'custom_repeater':
                CFM_Admin_Template_Post::repeat_field( $field_id, __( 'Custom field: Repeat Field','edd_fes'));
                break;

            case 'custom_html':
                CFM_Admin_Template_Post::custom_html( $field_id, __( 'HTML','edd_fes') );
                break;

            case 'section_break':
                CFM_Admin_Template_Post::section_break( $field_id, __( 'Section Break','edd_fes') );
                break;

            case 'recaptcha':
                CFM_Admin_Template_Post::recaptcha( $field_id, __( 'reCaptcha','edd_fes') );
                break;

            case 'action_hook':
                CFM_Admin_Template_Post::action_hook( $field_id, __( 'Action Hook','edd_fes') );
                break;

            case 'really_simple_captcha':
                CFM_Admin_Template_Post::really_simple_captcha( $field_id, __( 'Really Simple Captcha','edd_fes') );
                break;

            case 'custom_date':
                CFM_Admin_Template_Post::date_field( $field_id, __( 'Custom Field: Date','edd_fes') );
                break;

            case 'custom_map':
                CFM_Admin_Template_Post::google_map( $field_id, __( 'Custom Field: Google Map','edd_fes') );
                break;

            case 'custom_hidden':
                CFM_Admin_Template_Post::custom_hidden_field( $field_id, __( 'Hidden Field','edd_fes') );
                break;

            case 'toc':
                CFM_Admin_Template_Post::toc( $field_id, 'TOC' );
                break;

            case 'user_login':
                CFM_Admin_Template_Profile::user_login( $field_id, __( 'Username', 'edd_fes' ) );
                break;

            case 'first_name':
                CFM_Admin_Template_Profile::first_name( $field_id, __( 'First Name', 'edd_fes' ) );
                break;

            case 'last_name':
                CFM_Admin_Template_Profile::last_name( $field_id, __( 'Last Name', 'edd_fes' ) );
                break;

            case 'nickname':
                CFM_Admin_Template_Profile::nickname( $field_id, __( 'Nickname', 'edd_fes' ) );
                break;

            case 'display_name':
                CFM_Admin_Template_Profile::display_name( $field_id, __( 'Display Name', 'edd_fes' ) );
                break;				
				
            case 'user_email':
                CFM_Admin_Template_Profile::user_email( $field_id, __( 'E-mail', 'edd_fes' ) );
                break;

            case 'user_url':
                CFM_Admin_Template_Profile::user_url( $field_id, __( 'Website', 'edd_fes' ) );
                break;

            case 'user_bio':
                CFM_Admin_Template_Profile::description( $field_id, __( 'Biographical Info', 'edd_fes' ) );
                break;

            case 'password':
                CFM_Admin_Template_Profile::password( $field_id, __( 'Password', 'edd_fes' ) );
                break;

            case 'user_avatar':
                CFM_Admin_Template_Profile::avatar( $field_id, __( 'Avatar', 'edd_fes' ) );
                break;

            case 'eddc_user_paypal':
                CFM_Admin_Template_Profile::eddc_user_paypal( $field_id, __( 'PayPal Email', 'edd_fes' ) );
                break;

            default:
                do_action( 'fes_admin_field_' . $name, $type, $field_id );
                break;
        }

        exit;
    }

}