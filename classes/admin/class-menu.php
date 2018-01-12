<?php
/**
 * CFM Menu
 *
 * This file deals with CFM's menu items.
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
 * CFM Menu.
 *
 * Creates all of the menu and submenu items CFM adds to the backend.
 *
 * @since 2.0.0
 * @access public
 */
class CFM_Menu {

	/**
	 * CFM Menu Actions.
	 *
	 * Runs actions required to add menus and submenus.
	 *
	 * @since 2.0.0
	 * @access public
	 * 
	 * @return void
	 */	
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'admin_menus' ), 9 );
	}

	/**
	 * CFM Menu Items.
	 *
	 * Adds the menu and submenu pages.
	 *
	 * @since 2.0.0
	 * @access public
	 * 
	 * @return void
	 */	
	public function admin_menus() {
		if ( is_network_admin() ) {
			return;
		}
		
		if ( current_user_can( 'manage_shop_settings' ) ){
			foreach ( EDD_CFM()->load_forms as $name => $class ) {
				$form = new $class( $name, 'name' );
				if ( $form->has_formbuilder() && ! empty( $form->id ) ) {
					add_submenu_page( 'edit.php?post_type=download', $form->title( true ), $form->title( true ), 'manage_shop_settings', 'post.php?post=' . $form->id . '&action=edit' );
				}
			}
		}
	}
}
