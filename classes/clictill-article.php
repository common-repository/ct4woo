<?php
/*
@package ct4woo
@since 0.1
@internal adds methods for listing, reading and writing article data using the clictill and the WooCommerce APIs
@todo Add price synchronization
@todo Add stock management when available
@todo how to manage products removed from clictill?
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once 'clictill-api.php';
require_once 'woocommerce-api.php';

class ClictillArticles {

	var $data;
	var $result;
	protected $token;

	function __construct() {
		$this->token = get_option( 'clictill_settings_article_token' );
	}

	function clictill_read_list_from_clictill( $since = false ) {
		$request = new ClictillApi;
		$request->service = 'wsServerArticle/getArticle/';
		$request->token = $this->token;
		if ($since) {
			$data = array();
			$data['Articles'] = array( array(
				'modified_date' => array( array(
					'operator' => '>',
					'date' => $since,
					'format' => 'y-m-d H:i:s'
					) )
				) );
			$body = json_encode( $data );
			$request->read( $body );
		}
		else {
			$request->read();
		}
		$this->result = $request->result;
		$this->data = json_decode( $this->result );

		return;
	}
}

class ClictillArticle {

	var $clictill_article_id;
	var $woocommerce_article_id;
	var $data;
	var $description;
	var $short_description;
	var $ext_code_article;
	var $name;
	var $regular_price;
	var $result;
	var $use_stock;
	protected $token;
	
	function __construct() {
		$this->token = get_option( 'clictill_settings_article_token' );
		$this->price_code = get_option( 'clictill_settings_price_code' );
	}

	function clictill_read_from_clictill() {
		$request = new ClictillApi;
		$request->service = 'wsServerArticle/getArticle/';
		$request->token = $this->token;
		$body = '{"Articles":[{"id_articles":[{"id_article":"'.$this->clictill_article_id.'"}]}]}';
		$request->read( $body );
		$this->result = $request->result;
		$this->data = json_decode($this->result);

		foreach ( $this->data->response->data as $article ) {
			$this->ext_code_article = $article->ext_code_article;
			$this->clictill_brand_id = $article->id_brand;
			$this->clictill_category_id = $article->id_category;
			$this->clictill_supplier_id = $article->id_supplier;
			if ( get_option( 'clictill_settings_manage_stock' ) == 'yes') {
				$this->use_stock = $article->use_stock;
			}
			foreach ( $article->description as $description ) {
				$this->description1 = $description->description1;
				$this->description2 = $description->description2;
				$this->description3 = $description->description3;
			}
			foreach ( $article->prices as $price ) {
				$this->regular_price = $price->price_wt_tax;
			}
		}
		return;
	}

	function clictill_write_to_woocommerce() {
		global $wpdb;
		$clictill_category_id = $this->clictill_category_id;
		if ( $clictill_category_id != '' ) {
			$table_name = $wpdb->prefix . 'clictill_categories';
			$sql = "SELECT woocommerce_id FROM $table_name WHERE clictill_id = $clictill_category_id";
			$category_row = $wpdb->get_row( $sql );
		}
		$table_name = $wpdb->prefix . 'clictill_products';
		$clictill_article_id = $this->clictill_article_id;
		$sql = "SELECT woocommerce_id FROM $table_name WHERE clictill_id = $clictill_article_id";
		$row = $wpdb->get_row( $sql );
		$woocommerce = new WoocommerceApi;
		$now = date("y-m-d H:i:s");
		if ( get_option( 'clictill_settings_manage_stock' ) == 'yes') {
			$manage_stock = true;
			$stock_quantity = null;
		} 
		else {
			$manage_stock = false;
			$stock_quantity = null;
		}
		if ($row) {
			$woocommerce_id = $row->woocommerce_id;
			$woocommerce_data = array();
			$woocommerce_data['regular_price'] = $this->regular_price;
			$woocommerce_data['sku'] = $this->ext_code_article;
			$woocommerce_data['manage_stock'] = $manage_stock;
			$woocommerce_data['stock_quantity'] = $stock_quantity;
			if ( isset ( $category_row ) )
				$woocommerce_data['categories'] = array(array('id' => (int)$category_row->woocommerce_id ) );
			$woocommerce->data = json_encode($woocommerce_data);
			if ( WP_DEBUG === true )
				clictill_write_to_woocommerce_logs( 'Product update: '. $woocommerce->data );
			$woocommerce->service = 'products/'.$woocommerce_id;
			$woocommerce->put();
			$response = $woocommerce->response;
			$wpdb->update(
				$table_name,
				array(
					'last_sync' => $now
				),
				array(
					'woocommerce_id' => $woocommerce_id
				)
			);
			if ( $this->clictill_brand_id ) {
				$table_name = $wpdb->prefix . 'clictill_brands';
				$sql = "SELECT woocommerce_id FROM $table_name WHERE clictill_id = $this->clictill_brand_id";
				$brand_row = $wpdb->get_row( $sql );
				wp_set_post_terms( $woocommerce_id, array ( (int)$brand_row->woocommerce_id ), 'brands' );
			}
			if ( $this->clictill_supplier_id ) {
				$table_name = $wpdb->prefix . 'clictill_suppliers';
				$sql = "SELECT woocommerce_id FROM $table_name WHERE clictill_id = $this->clictill_supplier_id";
				$supplier_row = $wpdb->get_row( $sql );
				wp_set_post_terms( $woocommerce_id, array ( (int)$supplier_row->woocommerce_id ), 'suppliers' );
			}
		}
		else {
			$woocommerce_data = array();
			if ( $name_source = get_option( 'clictill_woocommerce_name_field_mapping' ) ) {
				switch ($name_source) {
					case 'description1' :
						$woocommerce_data['name'] = $this->description1;
						break;
					case 'description2' :
						$woocommerce_data['name'] = $this->description2;
						break;
					case 'description3' :
						$woocommerce_data['name'] = $this->description3;
						break;
					}
			}
			$woocommerce_data['type'] = 'simple';
			if ( $description_source = get_option( 'clictill_woocommerce_description_field_mapping' ) ) {
				switch ($description_source) {
					case 'description1' :
						$woocommerce_data['description'] = $this->description1;
						break;
					case 'description2' :
						$woocommerce_data['description'] = $this->description2;
						break;
					case 'description3' :
						$woocommerce_data['description'] = $this->description3;
						break;
					}
			}
			if ( $short_description_source = get_option( 'clictill_woocommerce_short_description_field_mapping' ) ) {
				switch ($short_description_source) {
					case 'description1' :
						$woocommerce_data['short_description'] = $this->description1;
						break;
					case 'description2' :
						$woocommerce_data['short_description'] = $this->description2;
						break;
					case 'description3' :
						$woocommerce_data['short_description'] = $this->description3;
						break;
					}
			}
			$woocommerce_data['regular_price'] = $this->regular_price;
			$woocommerce_data['sku'] = $this->ext_code_article;
			$woocommerce_data['status'] = 'pending';
			$woocommerce_data['manage_stock'] = $manage_stock;
			$woocommerce_data['stock_quantity'] = $stock_quantity;
			if ( isset( $category_row ) )
				$woocommerce_data['categories'] = array( array( 'id' => (int)$category_row->woocommerce_id ) );
			$woocommerce->data = json_encode( $woocommerce_data );
			if ( WP_DEBUG === true )
				clictill_write_to_woocommerce_logs( 'Product create: '. $woocommerce->data );
			$woocommerce->service = 'products';
			$woocommerce->post();
			$body = $woocommerce->body;
			if ( isset( $body->id ) ) {
				$wpdb->insert(
					$table_name,
					array(
						'clictill_id' => $clictill_article_id,
						'woocommerce_id' => $body->id,
						'date_created' => $now,
						'last_sync' => $now
					)
				);
				if ( isset( $this->clictill_brand_id ) ) {
					$table_name = $wpdb->prefix . 'clictill_brands';
					$sql = "SELECT woocommerce_id FROM $table_name WHERE clictill_id = $this->clictill_brand_id";
					$brand_row = $wpdb->get_row( $sql );
					wp_set_post_terms( $body->id, array ( (int)$brand_row->woocommerce_id ), 'brands' );
				}
				if ( isset( $this->clictill_supplier_id ) ) {
					$table_name = $wpdb->prefix . 'clictill_suppliers';
					$sql = "SELECT woocommerce_id FROM $table_name WHERE clictill_id = $this->clictill_supplier_id";
					$supplier_row = $wpdb->get_row( $sql );
					wp_set_post_terms( $body->id, array ( (int)$supplier_row->woocommerce_id ), 'suppliers' );
				}
			}
			else {
			}
		}
	}
}