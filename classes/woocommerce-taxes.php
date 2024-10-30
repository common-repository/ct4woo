<?php
/*
@package ct4woo
@since 0.4
@internal adds methods for reading tax information from WooCommerce
@todo 
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once 'woocommerce-api.php';

class WoocommerceTaxes {

	function clictill_read_list_from_woocommerce() {
		$request = new WoocommerceApi;
		$request->service = 'taxes';
		$request->get();
		$this->response = $request->body;
	}
}