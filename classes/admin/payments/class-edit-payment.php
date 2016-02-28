<?php
/**
 * Edit Payment Screen
 *
 * Runs actions and filters on the edit payment 
 * screen.
 *
 * @package CFM
 * @subpackage Administration
 * @since 2.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) { 
	exit;
}

/**
 * CFM Edit Payment
 *
 * Enhances the admin edit payment screen
 * as well as adds CFM's custom field metabox
 *
 * @since 2.0.0
 * @access public
 */
class CFM_Edit_Payment {

	/**
	 * Registers all actions and filters for the edit payment screen.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @return void
	 */
	function __construct() {
		add_action( 'edd_view_order_details_billing_after', array( $this, 'render_form' ), 10, 1 ); 
		add_action( 'save_post', array( $this, 'save_meta' ), 11, 2 );
	}

	/**
	 * Payment custom fields metabox HTML.
	 * 
	 * Makes the form for the custom fields metabox
	 * CFM adds to the edit payment screen.
	 *
	 * @since 2.0.0
	 *
	 * @param int $payment_id The payment ID.
	 * @return void
	 */
	public function render_form( $payment_id ) {
		// if the current user can't edit this payment, abort.
		if ( ! current_user_can( 'edit_shop_payments' ) ) {
			return '';
		}		

		// attempt to get the form id of the checkout form
		$form_id = get_option( 'cfm-checkout-form', false );

		// if we can't find the checkout form, echo an error
		if ( !$form_id ) {
			return _e( 'Checkout form not set!' , 'edd_cfm' );
		}

		$form = new CFM_Checkout_Form( $form_id, 'id', $payment_id );
		ob_start();
		?>
		<div id="cfm-checkout-fields" class="postbox">
			<h3 class="hndle">
				<span><?php _e( 'Custom Fields', 'edd_cfm' ); ?></span>
			</h3>
			<div class="inside edd-clearfix cfm-form">
			<?php
				// let's output the CFM Form
				echo $form->render_form_admin( get_current_user_id() );
			?>
			</div>
		</div>
		<?php
		$contents = ob_get_clean();
		echo $contents;
	}

	/**
	 * Save custom fields on Edit Payment screen.
	 * 
	 * Saves the custom fields CFM outputs in it's custom
	 * metabox on the edit payment screen in the admin.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @uses  CFM_Forms::save_form() Provides the HTML for the form.
	 *
	 * @param  int $post_id The post id of the payment.
	 * @param  WP_Post $post A post object of the currently edited payment.
	 * @return void
	 */
	public function save_meta( $post_id, $post ) {
		// if we're not on a payment post item, exit immediately.
		if ( isset( $post->post_type ) && $post->post_type !== 'edd_payment' ) {
			return;
		}

		/* if the save_post action has been called by WordPress doing an autosave
		 * or if save_post has been called a bulk edit call, exit immediately */
		if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) || isset( $_REQUEST['bulk_edit'] ) ) {
			return;
		}

		// if the current user can't edit this payment, abort.
		if ( ! current_user_can( 'edit_shop_payments', $post_id ) ) {
			return;
		}

		$form_id = get_option( 'cfm-checkout-form', false );

		// Make the CFM Form object.
		$form = new CFM_Checkout_Form( $form_id, 'id', $post_id );
		
		// Save the CFM Form
		$form->save_form_admin( $_POST, get_current_user_id() );
	}
}