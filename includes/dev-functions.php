<?php
/*
@package ct4woo
@since 0.4
@internal various useful functions
@todo 
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function clictill_woocommerce_delete_test_products() {
	$woocommerce = new WoocommerceApi;
	$woocommerce->service = 'products';
	$woocommerce->get();
	$response = $woocommerce->response;
	$body = json_decode($response['body']);
	foreach ($body as $product) {
		if ($product->status == 'pending') {
			$woocommerce_product_to_remove = new WoocommerceApi;
			$woocommerce_product_to_remove->service = 'products/'.$product->id;
			$woocommerce_product_to_remove->delete();
		}
	}
}