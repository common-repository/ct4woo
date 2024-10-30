<?php
/*
@package ct4woo
@since 0.2
@internal adds brand taxonomy
@todo 
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'init', 'clictill_brand_taxonomy', 0 );
 
function clictill_brand_taxonomy() {
  $labels = array(
    'name' => _x( 'Brands', 'taxonomy general name', CLICTILL_DOMAIN ),
    'singular_name' => _x( 'Brand', 'taxonomy singular name', CLICTILL_DOMAIN ),
    'search_items' =>  __( 'Search brands', CLICTILL_DOMAIN ),
    'all_items' => __( 'All brands', CLICTILL_DOMAIN ),
    'edit_item' => __( 'Edit brand', CLICTILL_DOMAIN ),
    'update_item' => __( 'Update brand', CLICTILL_DOMAIN ),
    'add_new_item' => __( 'Add new brand', CLICTILL_DOMAIN ),
    'new_item_name' => __( 'New brand name', CLICTILL_DOMAIN ),
    'menu_name' => __( 'Brands', CLICTILL_DOMAIN ),
  );    
 
  register_taxonomy('brands',array('product'), array(
    'description' => __( 'Brands as imported from Clictill', CLICTILL_DOMAIN ),
    'hierarchical' => false,
    'labels' => $labels,
    'show_ui' => true,
    'show_admin_column' => true,
    'query_var' => true,
    'rewrite' => array( 'slug' => __( 'brand', CLICTILL_DOMAIN ) )
  ));
 
}

class ClictillBrands {

	var $data;
	var $result;
	protected $token;

	function __construct() {
		$this->token = get_option( 'clictill_settings_brands_token' );
	}

	function clictill_read_list_from_clictill() {
		$body = '{"Brands"}';
		$request = new ClictillApi;
		$request->service = 'wsServerBrand/getBrand/';
		$request->token = $this->token;
		$request->read( $body );
		$this->result = $request->result;
		$this->data = json_decode( $this->result );

		if ( WP_DEBUG === true )
			clictill_write_to_woocommerce_logs( 'Brand result: ', $this->result );

		return;
	}
}

class ClictillBrand {

	var $clictill_brand_id;
	var $clictill_brand_name;
	var $woocommerce_brand_id;
	var $data;
	var $description;
	protected $token;
	
	function __construct() {
		$this->token = get_option( 'clictill_settings_brands_token' );
	}

	function clictill_write_to_woocommerce() {	
		global $wpdb;
		$table_name = $wpdb->prefix . 'clictill_brands';
		$sql = "SELECT woocommerce_id FROM $table_name WHERE clictill_id = $this->clictill_brand_id";
		$row = $wpdb->get_row( $sql );
		$now = date( "y-m-d H:i:s" );
		$clictill_brand_name = $this->clictill_brand_name;
		if ($row) {
			wp_update_term( $row->woocommerce_id, $clictill_brand_name, 'brands' );
			$wpdb->update(
				$table_name,
				array(
					'last_sync' => $now
				),
				array(
					'woocommerce_id' => $row->woocommerce_id
				)
			);
		}
		else {
			$term = term_exists( $clictill_brand_name, 'brands' );
			if ( 0 !== $term && null !== $term ) {
				if ( WP_DEBUG === true )
					clictill_write_to_woocommerce_logs( 'Brand: '. $clictill_brand_name .' already exists.' );
			}
			else {
				$term = wp_insert_term( $clictill_brand_name, 'brands' );
				if ( WP_DEBUG === true )
					clictill_write_to_woocommerce_logs( 'Brand: '. $clictill_brand_name .' added.' );
			}
			$woocommerce_id = $term['term_id'];
			$wpdb->insert(
				$table_name,
				array(
					'clictill_id' => $this->clictill_brand_id,
					'woocommerce_id' => $woocommerce_id,
					'date_created' => $now,
					'last_sync' => $now
				)
			);
		}
	}
}