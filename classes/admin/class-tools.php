<?php
/**
 * CFM Tools
 *
 * This file deals with instantiating
 * all of CFM's tools.
 *
 * @package CFM
 * @subpackage Tools
 * @since 2.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) { 
	exit;
}

/**
 * CFM Tools.
 *
 * Deals with adding CFM tools on the
 * Tools submenu page for CFM.
 *
 * @since 2.0.0
 * @access public
 */
class CFM_Tools {

	/**
	 * CFM Tools Actions.
	 *
	 * Runs actions required to show 
	 * and run the CFM tools.
	 *
	 * @since 2.0.0
	 * @access public
	 * 
	 * @return void
	 */		
	public function __construct() {
		add_filter( 'edd_tools_tabs', array( $this, 'add_cfm_tab' ), 10, 1 );
		add_action( 'edd_tools_tab_cfm', array( $this, 'cfm_tab' ) );
		add_action( 'edd_export_cfm_form', array( $this,'export' ) );
		add_action( 'edd_import_cfm_form', array( $this,'import' ) );
		add_action( 'edd_reset_cfm_form', array( $this,'reset' ) );
		add_action( 'edd_rerun_cfm_form', array( $this,'rerun' ) );
	}
	
	/**
	 * CFM Tools Tab.
	 *
	 * Adds tool tab to the EDD tools page.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @param  array $tabs Array of tabs on the EDD tools page.
	 * @return array Array of tabs on the EDD tools page.
	 */		
	public function add_cfm_tab( $tabs ){
		if( current_user_can( 'manage_shop_settings' ) ) {
			$tabs['cfm'] = __( 'Checkout Fields Manager', 'edd_cfm' );
		}
		return $tabs;
	}
	

	/**
	 * CFM Tools Tab page contents.
	 *
	 * Renders the tools for CFM.
	 *
	 * @since  2.0.0
	 * @access public
	 * 
	 * @return void
	 */
	public function cfm_tab() {
		if( ! current_user_can( 'manage_shop_settings' ) ) {
			return;
		} ?>
		<div class="postbox">
			<h3><span><?php _e( 'Reset Checkout Form', 'edd_cfm' ); ?></span></h3>
			<div class="inside">
				<p><?php _e( 'You can use this to reset the Checkout Fields Manager Form', 'edd_cfm' ); ?></p>
				<form method="post" action="<?php echo admin_url( 'edit.php?post_type=download&page=edd-tools&tab=cfm' ); ?>">
					<p><input type="hidden" name="edd_action" value="reset_cfm_form" /></p>
					<p>
						<?php wp_nonce_field( 'edd_reset_cfm_form_nonce', 'edd_reset_cfm_form_nonce' ); ?>
						<?php submit_button( __( 'Reset', 'edd_cfm' ), 'secondary', 'submit', false ); ?>
					</p>
				</form>
			</div><!-- .inside -->
		</div><!-- .postbox -->
		<div class="postbox">
			<h3><span><?php _e( 'Export Checkout Fields', 'edd_cfm' ); ?></span></h3>
			<div class="inside">
				<p><?php _e( 'Export the checkout fields for this site as a .json file. This allows you to easily import the configuration into another site.', 'edd_cfm' ); ?></p>
				<form method="post" action="<?php echo admin_url( 'edit.php?post_type=download&page=edd-tools&tab=cfm' ); ?>">
					<p><input type="hidden" name="edd_action" value="export_cfm_form" /></p>
					<p>
						<?php wp_nonce_field( 'edd_export_cfm_form_nonce', 'edd_export_cfm_form_nonce' ); ?>
						<?php submit_button( __( 'Export', 'edd_cfm' ), 'secondary', 'submit', false ); ?>
					</p>
				</form>
			</div><!-- .inside -->
		</div><!-- .postbox -->
		<div class="postbox">
			<h3><span><?php _e( 'Import Checkout Fields', 'edd_cfm' ); ?></span></h3>
			<div class="inside">
				<p><?php _e( 'Import the checkout fields from a .json file. This file can be obtained by exporting the checkout fields on another site using the form above.', 'edd_cfm' ); ?></p>
				<form method="post" enctype="multipart/form-data" action="<?php echo admin_url( 'edit.php?post_type=download&page=edd-tools&tab=cfm' ); ?>">
					<p>
						<input type="file" name="import_file"/>
					</p>
					<p>
						<input type="hidden" name="edd_action" value="import_cfm_form" />
						<?php wp_nonce_field( 'edd_import_cfm_form_nonce', 'edd_import_cfm_form_nonce' ); ?>
						<?php submit_button( __( 'Import', 'edd_cfm' ), 'secondary', 'submit', false ); ?>
					</p>
				</form>
			</div><!-- .inside -->
		</div><!-- .postbox -->
		<div class="postbox">
			<h3><span><?php _e( 'Rerun 2.0 Upgrade Routine', 'edd_cfm' ); ?></span></h3>
			<div class="inside">
				<p><?php _e( 'In a rare instance, you might need to run the CFM 1.x to 2.0 upgrade routine', 'edd_cfm' ); ?></p>
				<form method="post" action="<?php echo admin_url( 'edit.php?post_type=download&page=edd-tools&tab=cfm' ); ?>">
					<p><input type="hidden" name="edd_action" value="rerun_cfm_form" /></p>
					<p>
						<?php wp_nonce_field( 'edd_rerun_cfm_form_nonce', 'edd_rerun_cfm_form_nonce' ); ?>
						<?php submit_button( __( 'Rerun Routine', 'edd_cfm' ), 'secondary', 'submit', false ); ?>
					</p>
				</form>
			</div><!-- .inside -->
		</div><!-- .postbox -->
		<?php
	}
	
	/**
	 * CFM Checkout Form Reset.
	 * 
	 * Resets the checkout form to it's default fields.
	 *
	 * @since  2.0.0
	 * @access public
	 * 
	 * @return void
	 */
	public function reset( ) {
		if( empty( $_POST['edd_reset_cfm_form_nonce'] ) ) {
			return;
		}
		if( ! wp_verify_nonce( $_POST['edd_reset_cfm_form_nonce'], 'edd_reset_cfm_form_nonce' ) ) {
			return;
		}
		if( ! current_user_can( 'manage_shop_settings' ) ) {
			return;
		}
		$forms = get_posts( array( 'post_type' => 'edd-checkout-fields', 'fields' => 'ids', 'posts_per_page' => -1 ) );
		if ( $forms && !empty( $forms ) ) {
			foreach ( $forms as $form => $id ){
				wp_delete_post( $id, true );
			}
		}

		$page_data = array(
			'post_status' => 'publish',
			'post_type' => 'edd-checkout-fields',
			'post_author' => get_current_user_id(),
			'post_title' => __( 'Checkout Fields', 'edd_cfm' )
		);

		$page_id   = wp_insert_post( $page_data );
		cfm_save_initial_checkout_form( $page_id );
		wp_safe_redirect( admin_url( 'edit.php?post_type=download&page=edd-tools&tab=cfm&edd-message=fields-reset' ) ); exit;
	}

	/**
	 * CFM Checkout Form Rerun Upgrade Routine.
	 * 
	 * In a rare instance, you might need to run the CFM 1.x to 2.0 upgrade routine.
	 *
	 * @since  2.0.2
	 * @access public
	 * 
	 * @return void
	 */
	public function rerun( ) {
		if( empty( $_POST['edd_rerun_cfm_form_nonce'] ) ) {
			return;
		}
		if( ! wp_verify_nonce( $_POST['edd_rerun_cfm_form_nonce'], 'edd_rerun_cfm_form_nonce' ) ) {
			return;
		}
		if( ! current_user_can( 'manage_shop_settings' ) ) {
			return;
		}
		update_option( 'cfm_current_version', '1.0' );
		update_option( 'cfm_db_version', '1.0' );
		wp_safe_redirect( admin_url( 'edit.php?post_type=download&page=edd-tools&tab=cfm&edd-message=fields-rerun' ) ); exit;
	}	

	/**
	 * CFM Checkout Form Export.
	 * 
	 * Process a settings export that generates a .json file of the checkout form.
	 *
	 * @since  2.0.0
	 * @access public
	 * 
	 * @return void
	 */
	public function export() {
		if( empty( $_POST['edd_export_cfm_form_nonce'] ) ) {
			return;
		}
		if( ! wp_verify_nonce( $_POST['edd_export_cfm_form_nonce'], 'edd_export_cfm_form_nonce' ) ) {
			return;
		}
		if( ! current_user_can( 'manage_shop_settings' ) ) {
			return;
		}

		$form 	     = get_option( 'cfm-checkout-form', false );
		$fields      = get_post_meta( $form, 'cfm-form', true );
		ignore_user_abort( true );
		if ( ! edd_is_func_disabled( 'set_time_limit' ) && ! ini_get( 'safe_mode' ) ) {
			set_time_limit( 0 );
		}
		nocache_headers();
		header( 'Content-Type: application/json; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=cfm-checkout-fields-export-' . date( 'm-d-Y' ) . '.json' );
		header( "Expires: 0" );
		echo json_encode( $fields );
		exit;
	}

	/**
	 * CFM Checkout Form Import.
	 * 
	 * Process checkout fields import from a json file.
	 *
	 * @since  2.0.0
	 * @access public
	 * 
	 * @return void
	 */
	function import() {
		if( empty( $_POST['edd_import_cfm_form_nonce'] ) ) {
			return;
		}
		if( ! wp_verify_nonce( $_POST['edd_import_cfm_form_nonce'], 'edd_import_cfm_form_nonce' ) ) {
			return;
		}
		if( ! current_user_can( 'manage_shop_settings' ) ) {
			return;
		}
		if( edd_get_file_extension( $_FILES['import_file']['name'] ) != 'json' ) {
			wp_die( __( 'Please upload a valid .json file', 'edd_cfm' ), __( 'Error', 'edd_cfm' ), array( 'response' => 400 ) );
		}
		$import_file = $_FILES['import_file']['tmp_name'];
		if( empty( $import_file ) ) {
			wp_die( __( 'Please upload a file to import', 'edd_cfm' ), __( 'Error', 'edd_cfm' ), array( 'response' => 400 ) );
		}
		// Retrieve the fields from the file and convert the json object to an array
		$fields = edd_object_to_array( json_decode( file_get_contents( $import_file ) ) );
		$post_id = get_option( 'cfm-checkout-form', false );
		if ( ! $post_id ) {
			$page_data = array(
				'post_status' => 'publish',
				'post_type' => 'edd-checkout-fields',
				'post_author' => get_current_user_id(),
				'post_title' => __( 'Checkout Fields', 'edd_cfm' )
			);

			$post_id   = wp_insert_post( $page_data );
			cfm_save_initial_checkout_form( $post_id );
		}
		update_post_meta( $post_id, 'cfm-form', $fields );
		
		wp_safe_redirect( admin_url( 'edit.php?post_type=download&page=edd-tools&tab=cfm&edd-message=fields-imported' ) ); exit;
	}
	
	/**
	 * CFM tools panel
	 *
	 * Shows the tools panel which contains CFM-specific tools including the
	 * built-in import/export system.
	 *
	 * @since 2.0.0
	 * @access public
	 * 
	 * @return void
	 */
	function cfm_tools_page() {
		wp_enqueue_style( 'dashboard' );
		wp_enqueue_script( 'dashboard' );
		$active_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'forms'; ?>
		<div class="wrap">
			<?php screen_icon(); ?>
			<h2 class="nav-tab-wrapper">
				<?php
		foreach ( $this->cfm_get_tools_tabs() as $tab_id => $tab_name ) {
			$tab_url = add_query_arg( array( 'tab' => $tab_id ) );
			$tab_url = remove_query_arg( array( 'edd-message' ), $tab_url );

			$active = $active_tab == $tab_id ? ' nav-tab-active' : '';
			echo '<a href="' . esc_url( $tab_url ) . '" title="' . esc_attr( $tab_name ) . '" class="nav-tab' . $active . '">' . esc_html( $tab_name ) . '</a>';
		} ?>
			</h2>
			<div class="metabox-holder">
				<?php do_action( 'cfm_tools_tab_' . $active_tab ); ?>
			</div><!-- .metabox-holder -->
		</div><!-- .wrap -->
	<?php
	}
}
