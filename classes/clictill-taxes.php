<?php
/*
@package ct4woo
@since 0.4
@internal adds methods for reading tax information using the clictill API
@todo 
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once 'clictill-api.php';

class ClictillTaxes {

	protected $token;

	function __construct() {
		$this->token = get_option( 'clictill_settings_taxcodes_token' );
	}

	function clictill_read_list_from_clictill() {
		$body = '{"TaxCodes":[]}';
		$request = new ClictillApi;
		$request->service = 'wsServerTaxCode/getTaxCode/';
		$request->token = $this->token;
		$request->read($body);
		$this->result = $request->result;
		$this->data = json_decode($this->result);

		return;
	}
}