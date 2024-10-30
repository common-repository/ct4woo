<?php
/*
@package ct4woo
@since 0.3
@internal adds methods for writing sales data using the clictill API
@todo 
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once 'clictill-api.php';

class ClictillShops {

	protected $token;

	function __construct() {
		$this->token = get_option( 'clictill_settings_shops_token' );
	}

	function clictill_read_list_from_clictill() {
		$body = '{"Shops":[]}';
		$request = new ClictillApi;
		$request->service = 'wsServerShop/getShop/';
		$request->token = $this->token;
		$request->read($body);
		$this->result = $request->result;
		$this->data = json_decode($this->result);

		return;
	}
}