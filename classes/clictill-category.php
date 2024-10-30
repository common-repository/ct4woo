<?php
/*
@package ct4woo
@since 0.2
@internal adds methods for listing, reading and writing category data using the clictill and the WooCommerce APIs
@todo 
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once 'clictill-api.php';
require_once 'woocommerce-api.php';

class ClictillCategories {

	var $data;
	var $result;
	protected $token;

	function __construct() {
		$this->token = get_option( 'clictill_settings_category_token' );
	}

	function clictill_read_list_from_clictill() {
		$body = '{"Categories"}';
		$request = new ClictillApi;
		$request->service = 'wsServerCategory/getCategory/';
		$request->token = $this->token;
		$request->read($body);
		$this->result = $request->result;
		$this->data = json_decode($this->result);

		return;
	}
}

class ClictillCategory {

	var $clictill_category_id;
	var $clictill_category_name;
	var $woocommerce_category_id;
	var $data;
	var $description;
	var $ext_code_category;
	protected $token;
	
	function __construct() {
		$this->token = get_option( 'clictill_settings_category_token' );
	}

	function clictill_write_to_woocommerce() {
		global $wpdb;
		$table_name = $wpdb->prefix . 'clictill_categories';
		$sql = "SELECT woocommerce_id FROM $table_name WHERE clictill_id = $this->clictill_category_id";
		$row = $wpdb->get_row( $sql );
		$woocommerce = new WoocommerceApi;
		$woocommerce_data = array();
		$woocommerce_data['name'] = $this->clictill_category_name;
		$woocommerce->data = json_encode($woocommerce_data);
		$now = date("y-m-d H:i:s");
		if ($row) {
			$woocommerce_id = $row->woocommerce_id;
			$woocommerce->service = 'products/categories/'.$woocommerce_id;
			$woocommerce->put();
			$wpdb->update(
				$table_name,
				array(
					'last_sync' => $now
				),
				array(
					'woocommerce_id' => $woocommerce_id
				)
			);
		}
		else {
			$woocommerce->service = 'products/categories';
			$woocommerce->post();
			if ( WP_DEBUG === true )
				clictill_write_to_woocommerce_logs( 'Category update: '. $woocommerce->data );
			$response = $woocommerce->response;
			$body = json_decode( $response['body'] );
			if ( isset( $body->data->resource_id) ) {
				$wpdb->insert(
					$table_name,
					array(
						'clictill_id' => $this->clictill_category_id,
						'woocommerce_id' => $body->data->resource_id,
						'date_created' => $now,
						'last_sync' => $now
					)
				);
				if ( WP_DEBUG === true )
					clictill_write_to_woocommerce_logs('Category update: '. $this->clictill_category_id .' / '. $body->data->resource_id .'.');
			}
		}
	}
}