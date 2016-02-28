<?php
/**
 * CFM Post Types
 *
 * This file contains code that affects the 
 * CFM Forms post type.
 *
 * @package CFM
 * @subpackage Post Types
 * @since 2.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) { 
	exit;
}

/**
 * CFM Post Types.
 *
 * This file contains code that affects the 
 * CFM Forms post type.
 *
 * @since 2.0.0
 * @access public
 */
class CFM_Post_Types {

	/**
	 * CFM Post Types action/filters.
	 *
	 * Registers the actions and filters to create
	 * the CFM Forms post type and disable UI items 
	 * of it.
	 *
	 * @since 2.0.0
	 * @access public
	 * 
	 * @return void
	 */
	public function __construct() {
		add_action( 'init',  array( $this, 'register_post_types' ) );
		add_filter( "bulk_actions-edit-edd-checkout-fields", '__return_empty_array' );
		add_filter( 'disable_months_dropdown', array( $this, 'cfm_disable_months_dropdown'), 10, 2 );
	}

	/**
	 * Register CFM Forms post type.
	 *
	 * Adds the CFM Forms post type which is 
	 * used to store CFM forms.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @return void
	 */	
	public function register_post_types() {
		$capability = 'manage_shop_settings';
		register_post_type( 'edd-checkout-fields', array(
			'label' => __( 'EDD CFM Forms', 'edd_cfm' ),
			'public' => false,
			'rewrites' => false,
			'capability_type' => 'post',
			'capabilities' => array(
				'publish_posts' => 'cap_that_doesnt_exist',
				'edit_posts' => $capability,
				'edit_others_posts' => $capability,
				'delete_posts' => 'cap_that_doesnt_exist',
				'delete_others_posts' => 'cap_that_doesnt_exist',
				'read_private_posts' => 'cap_that_doesnt_exist',
				'edit_post' => $capability,
				'delete_post' => 'cap_that_doesnt_exist',
				'read_post' => $capability,
				'create_posts' => 'cap_that_doesnt_exist'
			),
			'hierarchical' => false,
			'query_var' => false,
			'supports' => array(
				'title'
			),
			'can_export'  => true,
			'show_ui'           => true,
			'show_in_menu' => false,
			'show_in_nav_menus' => false,
			'show_in_admin_bar' => false,
			'labels' => array(
				'name' => __( 'EDD CFM Forms', 'edd_cfm' ),
				'singular_name' => __( 'CFM Form', 'edd_cfm' ),
				'menu_name' => __( 'CFM Forms', 'edd_cfm' ),
				'add_new' => __( 'Add CFM Form', 'edd_cfm' ),
				'add_new_item' => __( 'Add New Form', 'edd_cfm' ),
				'edit' => __( 'Edit', 'edd_cfm' ),
				'edit_item' => '',
				'new_item' => __( 'New CFM Form', 'edd_cfm' ),
				'view' => __( 'View CFM Form', 'edd_cfm' ),
				'view_item' => __( 'View CFM Form', 'edd_cfm' ),
				'search_items' => __( 'Search CFM Forms', 'edd_cfm' ),
				'not_found' => __( 'No CFM Forms Found', 'edd_cfm' ),
				'not_found_in_trash' => __( 'No CFM Forms Found in Trash', 'edd_cfm' ),
				'parent' => __( 'Parent CFM Form', 'edd_cfm' )
			)
		) );
	}

	/**
	 * CFM Disable Month Dropdown.
	 *
	 * On the list table of the CFM
	 * Forms post type, remove the 
	 * dropdown for month created, since 
	 * that doesn't make any sense for use
	 * with CFM.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @param  bool $hide Whether to hide the dropdown for the post type.
	 * @param  string $post_type The post type.
	 * @return bool Whether to hide the dropdown for the post type.
	 */	
	public function cfm_disable_months_dropdown( $hide, $post_type ){
		if ( $post_type === 'edd-checkout-fields' ){
			return true;
		} else { 
			return $hide;
		}
	}
}
$post_types = new CFM_Post_Types();