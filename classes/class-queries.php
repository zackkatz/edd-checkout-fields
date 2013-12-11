<?php
if ( !defined( 'ABSPATH' ) ) {
	exit;
}

class FES_Queries {
	public function get_pending_products( $user_id ) {
		global $wpdb;
		global $current_user;
		$vendor_products = array();
		$vendor_products = get_posts( array(
			 'nopaging' => true,
			'author' => $current_user->ID,
			'orderby' => 'title',
			'post_type' => 'download',
			'post_status' => 'pending',
			'order' => 'ASC' 
		) );
		if ( empty( $vendor_products ) ){
			return false;
		}
		foreach ( $vendor_products as $product ) {
			$data[] = array(
				 'ID' => $product->ID,
				'date' => $product->post_date,
				'title' => $product->post_title,
				'url' => esc_url( get_permalink( $product->ID ) ),
				'sales' => edd_get_download_sales_stats( $product->ID ) 
			);
		}
		return $data;
	}
	
	public function get_published_products( $user_id ) {
		global $wpdb;
		global $current_user;
		$vendor_products = array();
		$vendor_products = get_posts( array(
			 'nopaging' => true,
			'author' => $current_user->ID,
			'orderby' => 'title',
			'post_type' => 'download',
			'post_status' => 'publish',
			'order' => 'ASC' 
		) );
		if ( empty( $vendor_products ) ){
			return false;
		}
		foreach ( $vendor_products as $product ) {
			$data[] = array(
				 'ID' => $product->ID,
				'date' => $product->post_date,
				'title' => $product->post_title,
				'url' => esc_url( get_permalink( $product->ID ) ),
				'sales' => edd_get_download_sales_stats( $product->ID ) 
			);
		}
		return $data;
	}
}