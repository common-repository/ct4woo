<?php
/*
@package ct4woo
@since 0.2
@internal adds supplier taxonomy
@todo 
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'init', 'clictill_supplier_taxonomy', 0 );
 
function clictill_supplier_taxonomy() {
  $labels = array(
    'name' => _x( 'Suppliers', 'taxonomy general name', CLICTILL_DOMAIN ),
    'singular_name' => _x( 'Supplier', 'taxonomy singular name', CLICTILL_DOMAIN ),
    'search_items' =>  __( 'Search suppliers', CLICTILL_DOMAIN ),
    'all_items' => __( 'All suppliers', CLICTILL_DOMAIN ),
    'edit_item' => __( 'Edit supplier', CLICTILL_DOMAIN ),
    'update_item' => __( 'Update supplier', CLICTILL_DOMAIN ),
    'add_new_item' => __( 'Add new supplier', CLICTILL_DOMAIN ),
    'new_item_name' => __( 'New supplier name', CLICTILL_DOMAIN ),
    'menu_name' => __( 'Suppliers', CLICTILL_DOMAIN ),
  );    
 
  register_taxonomy('suppliers',array('product'), array(
    'description' => __( 'Suppliers as imported from Clictill', CLICTILL_DOMAIN ),
	'hierarchical' => false,
    'labels' => $labels,
    'show_ui' => true,
    'show_admin_column' => true,
    'query_var' => true,
    'rewrite' => array( 'slug' => __( 'supplier', CLICTILL_DOMAIN ) )
  ));
 
}

class ClictillSuppliers {

	var $data;
	var $result;
	protected $token;

	function __construct() {
		$this->token = get_option( 'clictill_settings_suppliers_token' );
	}

	function clictill_read_list_from_clictill() {
		$body = '{"Suppliers"}';
		$request = new ClictillApi;
		$request->service = 'wsServerSupplier/getSupplier/';
		$request->token = $this->token;
		$request->read( $body );
		$this->result = $request->result;
		$this->data = json_decode( $this->result );
		
		if ( WP_DEBUG === true )
			clictill_write_to_woocommerce_logs( 'Supplier result: ', $this->result );

		return;
	}
}

class ClictillSupplier {

	var $clictill_supplier_id;
	var $clictill_supplier_name;
	var $woocommerce_supplier_id;
	var $data;
	var $description;
	protected $token;
	
	function __construct() {
		$this->token = get_option( 'clictill_settings_suppliers_token' );
	}

	function clictill_write_to_woocommerce() {
		global $wpdb;
		$table_name = $wpdb->prefix . 'clictill_suppliers';
		$sql = "SELECT woocommerce_id FROM $table_name WHERE clictill_id = $this->clictill_supplier_id";
		$row = $wpdb->get_row( $sql );
		$now = date( "y-m-d H:i:s" );
		$clictill_supplier_name = $this->clictill_supplier_name;
		if ($row) {
			wp_update_term( $row->woocommerce_id, $clictill_supplier_name, 'suppliers' );
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
			$term = term_exists( $clictill_supplier_name, 'suppliers' );
			if ( 0 !== $term && null !== $term ) {
				if ( WP_DEBUG === true )
					clictill_write_to_woocommerce_logs( 'Supplier: '. $clictill_supplier_name .' already exists.' );
			}
			else {
				$term = wp_insert_term( $clictill_supplier_name, 'suppliers' );
				if ( WP_DEBUG === true )
					clictill_write_to_woocommerce_logs( 'Supplier: '. $clictill_supplier_name .' added.' );
			}
			$woocommerce_id = $term['term_id']; 
			$wpdb->insert(
				$table_name,
				array(
					'clictill_id' => $this->clictill_supplier_id,
					'woocommerce_id' => $woocommerce_id,
					'date_created' => $now,
					'last_sync' => $now
				)
			);
		}
	}
	
	function clictill_update_external_code() {
		$request = new ClictillApi;
		$request->service = 'wsServerSupplier/setSupplier/';
		$request->token = $this->token;
		$body_array = array();
		$body_array['fagActiveLogs'] = true;
		$body_array['Suppliers'] = array(array());
		$body_array['Suppliers']['0']['code_supplier'] = "";
		$body_array['Suppliers']['0']['ext_code_supplier'] = "";
		$body_json = json_encode($body_array);
		if ( WP_DEBUG === true )
			clictill_write_to_woocommerce_logs( 'Supplier: '. $body_json );
		$request->write( $body_json );
		$this->result = $request->result;
	}
}