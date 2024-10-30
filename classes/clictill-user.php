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

class ClictillUsers {

	protected $token;

	function __construct() {
		$this->token = get_option( 'clictill_settings_users_token' );
	}

	function clictill_read_list_from_clictill() {
		$body = '{"Users":[]}';
		$request = new ClictillApi;
		$request->service = 'wsServerUserAccount/getUser/';
		$request->token = $this->token;
		$request->read($body);
		$this->result = $request->result;
		$this->data = json_decode($this->result);

		return;
	}
}

class ClictillUser {

	protected $token;

	function __construct() {
		$this->token = get_option( 'clictill_settings_users_token' );
	}

	function clictill_read_from_clictill() {
		$request = new ClictillApi;
		$request->service = 'wsServerUserAccount/getUser/';
		$request->token = $this->token;
		$body = '{"Users":{"nicknames":["'.$this->clictill_user_nickname.'"]}}';
		$request->read($body);
		$this->result = $request->result;
		$this->data = json_decode($this->result);

		return;
	}
}