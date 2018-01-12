<?php
/**
 * CFM Update System
 *
 * This file deals with CFM's user
 * initiate upgrades
 *
 * @package CFM
 * @subpackage Install/Upgrade
 * @since 2.0.0
 *
 * @todo Split upgrade routines off into their 
 *       own files.
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) { 
	exit;
}

/**
 * CFM Upgrade Page registration.
 *
 * Register an upgrade page for CFM to 
 * use during user initiated upgrade 
 * routines.
 *
 * @since 2.0.0
 * @access public
 *
 * @return void
 */
function cfm_register_upgrades_page() {
	add_submenu_page( null, __( 'CFM Upgrades', 'edd_cfm' ), __( 'CFM Upgrades', 'edd_cfm' ), 'install_plugins', 'cfm-upgrades', 'cfm_upgrades_screen' );
}
add_action( 'admin_menu', 'cfm_register_upgrades_page', 10 );

/**
 * CFM Upgrade Page screen.
 *
 * Renders the screen shown
 * during an CFM upgrade routine.
 *
 * @since 2.0.0
 * @access public
 *
 * @return void
 */
function cfm_upgrades_screen() {
	$step   = isset( $_GET['step'] ) ? absint( $_GET['step'] ) : 1;
	$counts = count_users();
	$total  = isset( $counts['total_users'] ) ? $counts['total_users'] : 1;
	$total_steps = round( ( $total / 50 ), 0 );
	?>
	<div class="wrap">
		<h2><?php _e( 'Checkout Fields Manager - Upgrades', 'edd_cfm' ); ?></h2>
		<div id="edd-upgrade-status">
			<p><?php _e( 'The upgrade process is running, please be patient. This could take several minutes to complete.', 'edd_cfm' ); ?></p>
			<p><strong><?php printf( __( 'Step %d of approximately %d running', 'edd_cfm' ), $step, $total_steps ); ?>
		</div>
		<script type="text/javascript">
			document.location.href = "index.php?edd_action=<?php echo $_GET['edd_upgrade']; ?>&step=<?php echo absint( $_GET['step'] ); ?>";
		</script>
	</div>
<?php	
}

/**
 * CFM Show Upgrade Notice.
 *
 * Determines if the CFM install needs
 * to run an upgrade routine and if 
 * so shows an admin notice for the user
 * to run it.
 *
 * @since 2.0.0
 * @access public
 *
 * @return void
 */
function cfm_show_upgrade_notice() {
	$cfm_version = get_option( 'cfm_db_version', '1.0' );
	
	if ( version_compare( $cfm_version, '2.0', '<' ) && ! isset( $_GET['edd_upgrade'] ) ) {
		$form 		 = get_option( 'cfm-checkout-form', false );
		$fields      = get_post_meta( $form, 'cfm-form', true );
		if ( $fields && is_array( $fields ) ){
			$has_date_field = false;
			foreach ( $fields as $field ) {
				if ( ! empty( $field['template'] )  && $field['template'] === 'date' ) {
					$has_date_field = true;
					break;
				}
			}
			if ( $has_date_field ) {
				printf(
					'<div class="error"><p>' . __( 'The Checkout Fields Manager datepicker field schema needs to be updated! Click <a href="%s">here</a> to start the upgrade.', 'edd_cfm' ) . '</p></div>',
					esc_url( add_query_arg( array( 'edd_action' => 'upgrade_cfm_date_field' ), admin_url() ) )
				);
			} else {
				update_option( 'cfm_db_version', '2.0' );
			}
		} else {
			update_option( 'cfm_db_version', '2.0' );
		}
	}
}
add_action( 'admin_notices', 'cfm_show_upgrade_notice' );

/**
 * CFM 2.0 Date Field Update.
 *
 * In CFM 2.0, if there's a date field in use, we 
 * need to update the values that it saved to the new 
 * HTML5 standard.
 *
 * @since 2.0.0
 * @access public
 *
 * @return void
 */
function cfm_upgrade_cfm_date_fields() {

	$cfm_version = get_option( 'cfm_db_version', '1.0' );

	if ( version_compare( $cfm_version, '2.0', '>=' ) ) {
		return;
	}

	ignore_user_abort( true );

	if ( ! edd_is_func_disabled( 'set_time_limit' ) && ! ini_get( 'safe_mode' ) )
		set_time_limit( 0 );

	$step   = isset( $_GET['step'] ) ? absint( $_GET['step'] ) : 1;
	$offset = $step === 1 ? 0 : $step * 50; 

	$payments = new WP_Query( array( 'post_type' => 'edd_payment', 'fields' => 'ids', 'number' => 50, 'offset' => $offset ) );
	$payments = $payments->posts;
	
	if ( $payments && count( $payments ) > 0 ) {
		$form 		 = get_option( 'cfm-checkout-form', false );
		$fields          = get_post_meta( $form, 'cfm-form', true );
		$has_date_field = false;
		$date_fields    = array();
		foreach ( $fields as $field ) {
			if ( ! empty( $field['template'] )  && $field['template'] === 'date' ) {
				$has_date_field = true;
				$name = $field['name'];
				$date_fields["$name"]["format"] = !empty( $field['format'] ) ? $field['format'] : '';
				$date_fields["$name"]["time"]  = !empty( $field['time'] ) ? $field['time']: 'no';
				$date_fields["$name"]["name"]  = !empty( $field['name'] ) ? $field['name']: '';
			}
		}
		foreach( $payments as $payment => $id ) {
			foreach ( $date_fields as $dfield ) {
				$value = get_post_meta( $id, $dfield['name'], true );
				if ( $value ) {
					$format = $dfield['format'];
					$format = str_replace( 'oo', 'z', $format ); // day of the year (three digit)
					$format = str_replace( 'o' , 'z', $format ); // day of the year (no leading zeros)
					$format = str_replace( 'DD', 'l', $format ); // day name long
					$format = str_replace( 'dd', '$', $format ); // day of month (two digit)
					$format = str_replace( 'd' , 'j', $format ); // day of month (no leading zero)
					$format = str_replace( '$' , 'd', $format ); // day of month (two digit)
					$format = str_replace( 'mm', '$', $format ); // month of year (two digit)
					$format = str_replace( 'm' , 'n', $format ); // month of year (no leading zero)
					$format = str_replace( '$' , 'm', $format ); // month of year (two digit)
					$format = str_replace( 'MM', 'F', $format ); // month name long
					$format = str_replace( 'yy', 'Y', $format ); // year (four digit)
					$format = str_replace( '@' , ' ', $format ); // Unix timestamp (ms since 01/01/1970). Not supported by PHP.
					$format = str_replace( '!' , ' ', $format ); // Windows ticks (100ns since 01/01/0001). Not supported by PHP.
					$format = str_replace( "'" , ' ', $format ); // single quote
					$format .= "+";
					$value     = date_create_from_format( $format, $value );
					if ( $value !== false ) {
						if ( !empty( $dfield['time'] ) && $dfield['time'] === 'yes' ) {
							$value  = date_format( $value, "Y-m-d" ) . 'T'. date_format( $value, "H:i" );
						} else {
							$value  = date_format( $value, "Y-m-d" );
						}
						update_post_meta( $id, $name, $value );
					}
				}
			}
		}

		// Keys found so upgrade them
		$step++;
		$redirect = add_query_arg( array(
			'edd_action' => 'upgrade_cfm_date_field',
			'step'        => $step
		), admin_url( 'index.php' ) );
		wp_redirect( $redirect ); exit;

	} else {

		// No more keys found, update the DB version and exit
		update_option( 'cfm_db_version', '2.0' );
		wp_redirect( admin_url( 'edit.php?post_type=download&page=edd-settings&tab=extensions&section=cfm' ) ); exit;
	}

}
add_action( 'edd_upgrade_cfm_date_field', 'cfm_upgrade_cfm_date_fields' );
