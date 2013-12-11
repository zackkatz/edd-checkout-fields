<?php
if ( !defined( 'ABSPATH' ) ) {
	exit;
}

// This is based off of work by bbPress and also EDD itself.
class CFM_Menu {

	public $minimum_capability = 'manage_options';

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'admin_menus') );
		add_action( 'admin_head', array( $this, 'admin_head' ) );
		add_action( 'admin_init', array( $this, 'welcome'    ) );
	}

	public function admin_menus() {
		add_menu_page( 'About CFM', 'About CFM', $this->minimum_capability,'cfm-about', array( $this, 'about_screen' ));
		add_submenu_page( 'edit.php?post_type=download', 'Checkout Fields', 'Checkout Fields Editor', 'manage_options', 'post.php?post=' . get_option( 'edd_cfm_id') . '&action=edit');
	}

	/**
	 * Hide Individual Dashboard Pages
	 *
	 * @access public
	 * @since 1.4
	 * @return void
	 */
	public function admin_head() {
		// hide from dash menu
		remove_menu_page( 'index.php', 'cfm-about' );
		// Badge for welcome page
		$badge_url = cfm_assets_url . 'img/extensions2.jpg';
		?>
		<style type="text/css" media="screen">
		/*<![CDATA[*/
		.cfm-badge {
			padding-top: 150px;
			height: 217px;
			width: 370px;
			color: #666;
			font-weight: bold;
			font-size: 14px;
			text-align: center;
			text-shadow: 0 1px 0 rgba(255, 255, 255, 0.8);
			margin: 0 -5px;
			background: url('<?php echo $badge_url; ?>') no-repeat;
		}

		.about-wrap .cfm-badge {
			position: absolute;
			top: 0;
			right: 0;
		}

		.cfm-welcome-screenshots {
			float: right;
			margin-left: 10px!important;
		}
		/*]]>*/
		</style>
		<?php
	}

	/**
	 * Render About Screen
	 *
	 * @access public
	 * @since 1.4
	 * @return void
	 */
	public function about_screen() {
		list( $display_version ) = explode( '-', cfm_plugin_version );
		?>
		<div class="wrap about-wrap">
			<h1><?php printf( __( 'Welcome to EDD CFM %s!', 'edd_cfm' ), $display_version ); ?></h1>
			<div class="about-text"><?php printf( __( 'Thank you for updating to the latest version! <br />Easy Digital Downloads Frontend Submissions %s  <br /> is ready to make your online store faster, safer and better!', 'edd_cfm' ), $display_version ); ?></div>
			<div class="cfm-badge"></div>
			<div class="changelog">
				<h3><?php _e( 'cfm Forms', 'edd_cfm' );?></h3>

				<div class="feature-section">

					<img src="<?php echo cfm_assets_url . 'img/custom_fields.png'; ?>" class="cfm-welcome-screenshots"/>

					<h4><?php _e( 'Simple, Easy, Custom Submission Forms', 'edd_cfm' );?></h4>
					<p><?php _e( 'The most requested new feature of cfm was to make it easier to add custom fields. After months of hard work, you can now add all sorts of different fields with the push of a button. I\'ll be adding more fields to choose from in future versions based on feedback from you guys.', 'edd_cfm' );?></p>

					<h4><?php _e( 'Improved Profile Fields', 'edd_cfm' );?></h4>
					<p><?php _e( 'Now you can collect all sorts of information about your users. And without having to write a single line of code.', 'edd_cfm' );?></p>
					
					<h4><?php _e( 'Drag and Drop Re-ordering of fields', 'edd_cfm' );?></h4>
					<p><?php _e( 'To change the order of the fields, simply drag and drop! ', 'edd_cfm' );?></p>
					
					<h4><?php _e( 'Easy Integration with Thousands of Plugins', 'edd_cfm' );?></h4>
					<p><?php _e( 'Using the new form editor you can make fields that map to any post_meta. ', 'edd_cfm' );?></p>
				</div>
			</div>

			<div class="changelog">
				<h3><?php _e( 'Admin Downloads Dashboard', 'edd_cfm' );?></h3>

				<div class="feature-section">

					<img src="<?php echo cfm_assets_url . 'img/dash.png'; ?>" class="cfm-welcome-screenshots"/>

					<h4><?php _e( 'Faster Approve/Deny Workflow','edd_cfm' );?></h4>
					<p><?php _e( 'With the new admin layout, you can approve/deny a submission with the push of a button. ', 'edd_cfm' );?></p>

					<h4><?php _e( 'Quickly Scan Submissions', 'edd_cfm' );?></h4>
					<p><?php _e( 'We\ve added a color coded first column to make it easy to distinguish submissions that you need to approve.', 'edd_cfm' );?></p>


				</div>
			</div>

			<div class="changelog">
				<h3><?php _e( 'New Vendor Dashboard', 'edd_cfm' );?></h3>

				<div class="feature-section">

					<img src="<?php echo cfm_assets_url . 'img/dashfront.png'; ?>" class="cfm-welcome-screenshots"/>

					<h4><?php _e( 'Better Default UI', 'edd_cfm' );?></h4>
					<p><?php _e( 'No more unstyled menu bar as the default. Introducing the new responsive, darker themed menu bar (with a light version to come in a later version). Built for a fullwidth page template, cfm is now responsive!', 'edd_cfm' );?></p>

					<h4><?php _e( 'New Login/Register Forms', 'edd_cfm' );?></h4>
					<p><?php _e( 'cfm now uses it\'s own registration and login forms. No more using the default WordPress ones :)', 'edd_cfm' );?></p>
					
				</div>
			</div>

			<div class="changelog">
				<h3><?php _e( 'Developer features', 'edd_cfm' );?></h3>

				<div class="feature-section">

					<h4><?php _e( 'More hooks and actions','edd_cfm' );?></h4>
					<p><?php _e( 'With hundreds of new hooks and actions, cfm is now much more developer friendly. There\'s even a field in the form editor called Do Action that you can use to add custom field types.', 'edd_cfm' );?></p>

					<h4><?php _e( 'The Start of a Templating System', 'edd_cfm' ); ?></h4>
					<p><?php _e( 'In 2.0, cfm is beginning a new chapter as a more developer and theme friendly plugin. cfm now uses a template system to load views. We\'re aiming for a robust templating experience by 2.2.', 'edd_cfm' ); ?></p>

				</div>
			</div>
		</div>
		<?php
	}
		
	public function welcome() {
		global $edd_options;

		// Bail if no activation redirect
		if ( ! get_transient( '_edd_cfm_activation_redirect' ) )
			return;

		// Delete the redirect transient
		delete_transient( '_edd_cfm_activation_redirect' );

		// Bail if activating from network, or bulk
		if ( is_network_admin() || isset( $_GET['activate-multi'] ) )
			return;

		wp_safe_redirect( admin_url( 'index.php?page=cfm-about' ) ); exit;
	}
}