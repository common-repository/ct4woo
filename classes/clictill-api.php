<?php
/*
@package ct4woo
@since 0.1
@internal adds methods for reading and writing the clictill API
@todo Write error to journal
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class ClictillApi {

	var $data;
	var $result;
	var $service;
	var $token;
	
	function read($body = false) {
		$client_webservice_url = CLICTILL_PATH . '/wsRest/' . $this->service;
		$request = wp_remote_post( $client_webservice_url, array(
			'method' => 'POST',
			'timeout' => 45,
			'redirection' => 5,
			'httpversion' => '1.0',
			'blocking' => true,
			'headers' => array('token' => $this->token, 'Content-type' => 'application/json'),
			'body' => $body
			)
		);
		$this->result = wp_remote_retrieve_body($request);
		$this->data = json_decode($this->result);
		if (!isset ($this->data->response->info->error_rows_count) || $this->data->response->info->error_rows_count == '0') {
		}
		else {
			clictill_write_to_woocommerce_logs('webService ('. $this->service .'): '.$this->data->response->info->webService .' [NOK]');
			clictill_write_to_woocommerce_logs('body: '. $body);
			clictill_write_to_woocommerce_logs('error_rows_count: '.$this->data->response->info->error_rows_count);
			clictill_write_to_woocommerce_logs('success_rows_count: '.$this->data->response->info->success_rows_count);
			clictill_write_to_woocommerce_logs('status_ret: '.$this->data->response->info->status_ret);
		}
		return;
	}

	function write($body = false) {
		$client_webservice_url = CLICTILL_PATH . '/wsRest/' . $this->service;
		$request = wp_remote_post( $client_webservice_url, array(
			'method' => 'POST',
			'timeout' => 45,
			'redirection' => 5,
			'httpversion' => '1.0',
			'blocking' => true,
			'headers' => array('token' => $this->token, 'Content-type' => 'application/json'),
			'body' => $body
			)
		);
		$this->result = wp_remote_retrieve_body($request);
		if (!$this->data->response->info->error_rows_count || $this->data->response->info->error_rows_count == '0') {
//			clictill_write_to_woocommerce_logs('webService ('. $this->service .'): '.$this->data->response->info->webService .' [OK]');
		}
		else {
			clictill_write_to_woocommerce_logs('webService ('. $this->service .'): '.$this->data->response->info->webService .' [NOK]');
			clictill_write_to_woocommerce_logs('body: '. $body);
			clictill_write_to_woocommerce_logs('error_rows_count: '.$this->data->response->info->error_rows_count);
			clictill_write_to_woocommerce_logs('success_rows_count: '.$this->data->response->info->success_rows_count);
			clictill_write_to_woocommerce_logs('status_ret: '.$this->data->response->info->status_ret);
//			clictill_write_to_woocommerce_logs('messages: '.$this->data->response->info->messages);
		}
		return;
	}
}