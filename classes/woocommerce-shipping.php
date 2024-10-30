<?php
/*
@package ct4woo
@since 0.4
@internal adds methods for reading shipping information from WooCommerce
@todo 
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once 'woocommerce-api.php';

class WoocommerceShipping {

	function clictill_read_methods_from_woocommerce() {
		$request = new WoocommerceApi;
		$request->service = 'shipping_methods';
		$request->get();
		$this->response = $request->body;
	}
}