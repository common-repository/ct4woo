<?php
/*
@package ct4woo
@since 0.1
@internal adds methods for reading and writing the woocommerce API
@todo test write method
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WoocommerceApi {
	
	var $data;
	var $api_url;
	var $response;
	protected $headers;
	
	function __construct() {
		$client_id = get_option( 'clictill_woocommerce_consumer_key' );
		$client_secret = get_option( 'clictill_woocommerce_consumer_secret' );
		$basicauth = 'Basic ' . base64_encode( $client_id . ':' . $client_secret );
		$this->headers = array( 
			'Authorization' => $basicauth,
			'Content-type' => 'application/json'
		);
		$this->api_url = get_option( 'siteurl' ) . '/wp-json/wc/v3/';
	}
	
	function delete() {
		$payload = array(
			'method' => 'DELETE',
			'timeout' => 30,
			'redirection' => 5,
			'httpversion' => '1.0',
			'blocking' => true,
			'headers' => $this->headers,
			'cookies' => array()
		);
		$response = wp_remote_request($this->api_url . $this->service, $payload);
		if ( is_array( $response ) ) {
			$this->body = json_decode( $response['body'] );
			if ( isset( $this->body->data->status ) )
				if ( WP_DEBUG === true )
					clictill_write_to_woocommerce_logs('WooCommerce: DELETE response: '.$this->body->code.', status: '.$this->body->data->status);
			$this->response = $response;
		}
		else {
			$error_message = $response->get_error_message();
			if ( WP_DEBUG === true )
				clictill_write_to_woocommerce_logs('WooCommerce: Something went wrong: '.$error_message);
		}
	}

	function get() {
		$payload = array(
			'method' => 'GET',
			'timeout' => 30,
			'redirection' => 5,
			'httpversion' => '1.0',
			'blocking' => true,
			'headers' => $this->headers,
			'body' => $this->data,
			'cookies' => array()
		);
		$response = wp_remote_get($this->api_url . $this->service, $payload);
		if ( is_array( $response ) ) {
			$this->body = json_decode( $response['body'] );
			if ( isset( $this->body->data->status ) )
				if ( WP_DEBUG === true )
					clictill_write_to_woocommerce_logs('WooCommerce: GET response: '.$this->body->code.', status: '.$this->body->data->status);
			$this->response = $response;
		}
		else {
			$error_message = $response->get_error_message();
			if ( WP_DEBUG === true )
				clictill_write_to_woocommerce_logs('WooCommerce: Something went wrong: '.$error_message);
		}
	}

	function post() {
		$payload = array(
			'method' => 'POST',
			'timeout' => 30,
			'redirection' => 5,
			'httpversion' => '1.0',
			'blocking' => true,
			'headers' => $this->headers,
			'body' => $this->data,
			'cookies' => array()
		);
		$response = wp_remote_post($this->api_url . $this->service, $payload);
		if ( is_array( $response ) ) {
			$this->body = json_decode( $response['body'] );
			if ( isset( $this->body->data->status ) )
				if ( WP_DEBUG === true )
					clictill_write_to_woocommerce_logs('WooCommerce: POST response: '.$this->body->code.', status: '.$this->body->data->status);
			$this->response = $response;
		}
		else {
			$error_message = $response->get_error_message();
			if ( WP_DEBUG === true )
				clictill_write_to_woocommerce_logs('WooCommerce: Something went wrong: '.$error_message);
		}
	}

	function put() {
		$payload = array(
			'method' => 'PUT',
			'timeout' => 30,
			'redirection' => 5,
			'httpversion' => '1.0',
			'blocking' => true,
			'headers' => $this->headers,
			'body' => $this->data,
			'cookies' => array()
		);
		$response = wp_remote_post($this->api_url . $this->service, $payload);
		if ( is_array( $response ) ) {
			$this->body = json_decode( $response['body'] );
			if ( isset( $this->body->data->status ) )
				if ( WP_DEBUG === true )
					clictill_write_to_woocommerce_logs('WooCommerce: PUT response: '.$this->body->code.', status: '.$this->body->data->status);
			$this->response = $response;
		}
		else {
			$error_message = $response->get_error_message();
			if ( WP_DEBUG === true )
				clictill_write_to_woocommerce_logs('WooCommerce: Something went wrong: '.$error_message);
		}
	}
}