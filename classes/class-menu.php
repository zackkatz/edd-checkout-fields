<?php
if ( !defined( 'ABSPATH' ) ) {
	exit;
}

// This is based off of work by bbPress and also EDD itself.
class FES_Menu {

	public $minimum_capability = 'manage_options';

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'admin_menus') );
		add_action( 'admin_head', array( $this, 'admin_head' ) );
		add_action( 'admin_init', array( $this, 'welcome'    ) );
	}

	public function admin_menus() {
		// About Page
		add_menu_page(
			__( 'EDD FES', 'edd_fes' ),
			__( 'EDD FES', 'edd_fes' ),
			$this->minimum_capability,
			'fes-about',
			array( $this, 'about_screen' ),'','25.01'
		);
		add_submenu_page( 'fes-about','About FES', 'About FES', $this->minimum_capability,'fes-about', array( $this, 'about_screen' ));
		add_submenu_page( 'fes-about', 'Submission Form Editor', 'Submission Form Editor', 'manage_options', 'post.php?post=' . EDD_FES()->fes_options->get_option( 'fes-submission-form') . '&action=edit');
		add_submenu_page( 'fes-about', 'Profile Form Editor', 'Profile Form Editor', 'manage_options', 'post.php?post=' . EDD_FES()->fes_options->get_option( 'fes-profile-form') . '&action=edit');
		add_dashboard_page(
			__( 'EDD FES Documentation', 'edd_fes' ),
			__( 'EDD FES Documentation', 'edd_fes' ),
			$this->minimum_capability,
			'fes-documentation',
			array( $this, 'documentation_screen' )
		);
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
		remove_submenu_page( 'index.php', 'fes-documentation' );
		// Badge for welcome page
		$badge_url = fes_assets_url . 'img/extensions2.jpg';
		?>
		<style type="text/css" media="screen">
		/*<![CDATA[*/
		.fes-badge {
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

		.about-wrap .fes-badge {
			position: absolute;
			top: 0;
			right: 0;
		}

		.fes-welcome-screenshots {
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
		list( $display_version ) = explode( '-', fes_plugin_version );
		?>
		<div class="wrap about-wrap">
			<h1><?php printf( __( 'Welcome to EDD FES %s!', 'edd_fes' ), $display_version ); ?></h1>
			<div class="about-text"><?php printf( __( 'Thank you for updating to the latest version! <br />Easy Digital Downloads Frontend Submissions %s  <br /> is ready to make your online store faster, safer and better!', 'edd_fes' ), $display_version ); ?></div>
			<div class="fes-badge"></div>

			<h2 class="nav-tab-wrapper">
				<a class="nav-tab nav-tab-active" href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'fes-about' ), 'index.php' ) ) ); ?>">
					<?php _e( "What's New", 'edd_fes' ); ?>
				</a><a class="nav-tab" href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'fes-documentation' ), 'index.php' ) ) ); ?>">
					<?php _e( 'Documentation', 'edd_fes' ); ?>
				</a>
			</h2>

			<div class="changelog">
				<h3><?php _e( 'FES Forms', 'edd_fes' );?></h3>

				<div class="feature-section">

					<img src="<?php echo fes_assets_url . 'img/custom_fields.png'; ?>" class="fes-welcome-screenshots"/>

					<h4><?php _e( 'Simple, Easy, Custom Submission Forms', 'edd_fes' );?></h4>
					<p><?php _e( 'The most requested new feature of FES was to make it easier to add custom fields. After months of hard work, you can now add all sorts of different fields with the push of a button. I\'ll be adding more fields to choose from in future versions based on feedback from you guys.', 'edd_fes' );?></p>

					<h4><?php _e( 'Improved Profile Fields', 'edd_fes' );?></h4>
					<p><?php _e( 'Now you can collect all sorts of information about your users. And without having to write a single line of code.', 'edd_fes' );?></p>
					
					<h4><?php _e( 'Drag and Drop Re-ordering of fields', 'edd_fes' );?></h4>
					<p><?php _e( 'To change the order of the fields, simply drag and drop! ', 'edd_fes' );?></p>
					
					<h4><?php _e( 'Easy Integration with Thousands of Plugins', 'edd_fes' );?></h4>
					<p><?php _e( 'Using the new form editor you can make fields that map to any post_meta. ', 'edd_fes' );?></p>
				</div>
			</div>

			<div class="changelog">
				<h3><?php _e( 'Admin Downloads Dashboard', 'edd_fes' );?></h3>

				<div class="feature-section">

					<img src="<?php echo fes_assets_url . 'img/dash.png'; ?>" class="fes-welcome-screenshots"/>

					<h4><?php _e( 'Faster Approve/Deny Workflow','edd_fes' );?></h4>
					<p><?php _e( 'With the new admin layout, you can approve/deny a submission with the push of a button. ', 'edd_fes' );?></p>

					<h4><?php _e( 'Quickly Scan Submissions', 'edd_fes' );?></h4>
					<p><?php _e( 'We\ve added a color coded first column to make it easy to distinguish submissions that you need to approve.', 'edd_fes' );?></p>


				</div>
			</div>

			<div class="changelog">
				<h3><?php _e( 'New Vendor Dashboard', 'edd_fes' );?></h3>

				<div class="feature-section">

					<img src="<?php echo fes_assets_url . 'img/dashfront.png'; ?>" class="fes-welcome-screenshots"/>

					<h4><?php _e( 'Better Default UI', 'edd_fes' );?></h4>
					<p><?php _e( 'No more unstyled menu bar as the default. Introducing the new responsive, darker themed menu bar (with a light version to come in a later version). Built for a fullwidth page template, FES is now responsive!', 'edd_fes' );?></p>

					<h4><?php _e( 'New Login/Register Forms', 'edd_fes' );?></h4>
					<p><?php _e( 'FES now uses it\'s own registration and login forms. No more using the default WordPress ones :)', 'edd_fes' );?></p>
					
				</div>
			</div>

			<div class="changelog">
				<h3><?php _e( 'Developer features', 'edd_fes' );?></h3>

				<div class="feature-section">

					<h4><?php _e( 'More hooks and actions','edd_fes' );?></h4>
					<p><?php _e( 'With hundreds of new hooks and actions, FES is now much more developer friendly. There\'s even a field in the form editor called Do Action that you can use to add custom field types.', 'edd_fes' );?></p>

					<h4><?php _e( 'The Start of a Templating System', 'edd_fes' ); ?></h4>
					<p><?php _e( 'In 2.0, FES is beginning a new chapter as a more developer and theme friendly plugin. FES now uses a template system to load views. We\'re aiming for a robust templating experience by 2.2.', 'edd_fes' ); ?></p>

				</div>
			</div>
		</div>
		<?php
	}
	
public function documentation_screen() {
		list( $display_version ) = explode( '-', fes_plugin_version );
		?>
		<div class="wrap about-wrap">
			<h1><?php printf( __( 'Documentation for EDD FES %s', 'edd_fes' ), $display_version ); ?></h1>
			<div class="about-text"><?php printf( __( 'New for FES %s is our brand new documentation!', 'edd_fes' ), $display_version ); ?></div>
			<div class="fes-badge"></div>

			<h2 class="nav-tab-wrapper">
				<a class="nav-tab" href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'fes-about' ), 'index.php' ) ) ); ?>">
					<?php _e( "What's New", 'edd_fes' ); ?>
				</a><a class="nav-tab nav-tab-active" href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'fes-documentation' ), 'index.php' ) ) ); ?>">
					<?php _e( 'Documentation', 'edd_fes' ); ?>
				</a>
			</h2>

			<div class="changelog">
				<h3><?php _e( 'Announcing eddfes.com:', 'edd_fes' );?></h3>

				<div class="feature-section">

					<h4><?php _e( 'Dynamic Search Knowledgebase', 'edd_fes' );?></h4>
					<p><?php _e( 'On our website, you can now use our live search to quickly find guides, tutorials, and even videos on FES.', 'edd_fes' );?></p>

					<h4><?php _e( 'Ticket Support Remains Via EDD\'s Site', 'edd_fes' );?></h4>
					<p><?php _e( 'While we now have a new website for our documentation and development updates, support for EDD FES will continue to only be done via tickets at easydigitaldownloads.com/support/', 'edd_fes' );?></p>

				</div>
			</div>

			<div class="changelog">
				<h3><?php _e( 'Highlighted New KB Articles for 2.0', 'edd_fes' );?></h3>

				<div class="feature-section">

					<h4><?php _e( 'How to setup EDD FES','edd_fes' );?></h4>
					<p><?php _e( 'A quick 5 minute video showing how to setup EDD FES', 'edd_fes' );?></p>

					<h4><?php _e( 'Building a powerhouse: Unleashing the power of the new FES Forms feature', 'edd_fes' );?></h4>
					<p><?php _e( 'How to take advantage of FES to match your exact needs', 'edd_fes' );?></p>


				</div>
			</div>

			<div class="changelog">
				<h3><?php _e( 'Development Updates and Open Office Hours:', 'edd_fes' );?></h3>

				<div class="feature-section">

					<h4><?php _e( 'The New Timeline for FES Development', 'edd_fes' );?></h4>
					<p><?php _e( 'See it at eddfes.com/timeline', 'edd_fes' );?></p>

					<h4><?php _e( 'Open Office Hours', 'edd_fes' );?></h4>
					<p><?php _e( 'Talk directly with me during select meetings every month!', 'edd_fes' );?></p>
					<p><?php _e( 'For more information, see live.eddfes.com/events', 'edd_fes' );?></p>

				</div>
			</div>
		</div>
		<?php
	}
		
	public function welcome() {
		global $edd_options;

		// Bail if no activation redirect
		if ( ! get_transient( '_edd_fes_activation_redirect' ) )
			return;

		// Delete the redirect transient
		delete_transient( '_edd_fes_activation_redirect' );

		// Bail if activating from network, or bulk
		if ( is_network_admin() || isset( $_GET['activate-multi'] ) )
			return;

		wp_safe_redirect( admin_url( 'index.php?page=fes-about' ) ); exit;
	}
}