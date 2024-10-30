<?php
/*
@package ct4woo
@since 0.1
@internal adds methods for reading and writing customer data using the clictill API
@todo 
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once 'clictill-api.php';
require_once 'woocommerce-api.php';

class ClictillCustomers {

	var $data;
	var $result;
	protected $token;

	function __construct() {
		$this->token = get_option( 'clictill_settings_client_token' );
	}

	function clictill_read_list_from_clictill($since = false) {
		if (! $since) {
			$since = date('y-m-d H:i:s', strtotime('-5 minutes'));
		}
		$data = array();
		$data['Customers'] = array(array('modified_date' => array(array('operator' => '>','date' => $since, 'format' => 'y-m-d H:i:s'))));
		$body = json_encode($data);
		$body = '{"Customers":[{"modified_date":[{ "operator":">" ,"date":"'. $since .'" ,"format":"y-m-d H:i:s" } ]} ]}';
		$request = new ClictillApi;
		$request->service = 'wsServerCustomer/getCustomer/';
		$request->token = $this->token;
		$request->read($body);
		$this->result = $request->result;
		$this->data = json_decode($this->result);

		return;
	}
}

class ClictillCustomer {

	var $last_name;
	var $first_name;
	var $company_name;
	var $num_vat;
	var $num_siret;
	var $address1;
	var $address2;
	var $address3;
	var $state_customer;
	var $zip_code;
	var $city;
	var $country_iso_code;
	var $country_name;
	var $phone;
	var $mobile_phone;
	var $email;
	var $comment_customer;
	var $clictill_customer_id;
	var $woocommerce_customer_id;
	var $data;
	var $result;
	protected $token;

	function __construct() {
		$this->token = get_option( 'clictill_settings_client_token' );
	}

	function clictill_read_customer_id_from_woocommerce_order($order_id) {
// https://woocommerce.github.io/woocommerce-rest-api-docs/#retrieve-an-order
		global $wpdb;
		$table_name = $wpdb->prefix . 'clictill_customers';
		$woocommerce = new WoocommerceApi;
		$woocommerce->service = 'orders/'.$order_id;
		$woocommerce->get();
		$response = $woocommerce->response;
		$order = json_decode( $response['body'] );
		$this->woocommerce_customer_id = $order->customer_id;
		// Check if existing clictill customer
		$sql = "SELECT clictill_id FROM $table_name WHERE woocommerce_id = $this->woocommerce_customer_id";
		$row = $wpdb->get_row( $sql );
		if ($row) {
			$this->clictill_customer_id = $row->clictill_id;
		}
		else {
			$request = new ClictillApi;
			$request->service = 'wsServerCustomer/setCustomer/';
			$request->token = $this->token;
/*
			$body = '{"Customers":[{
				"last_name":"'. $order->billing->last_name .'",
				"first_name":"'. $order->billing->first_name .'",
				"email":"'. $order->billing->email .'",
				"company_name":"'. $order->billing->company .'",
				"address1":"'. $order->billing->address_1 .'",
				"address2":"'. $order->billing->address_2 .'",
				"city":"'. $order->billing->city .'",
				"state_customer":"'. $order->billing->state .'",
				"zip_code":"'. $order->billing->postcode .'",
				"phone":"'. $order->billing->phone .'",
				"has_a_web_account":"1",
				"ext_code_customer":"'.$this->woocommerce_customer_id.'"
			}]}';
*/
			$body_array = array();
			$body_array['Customers'] = array(array());
			$body_array['Customers']['0']['last_name'] = "" . $order->billing->last_name . "";
			$body_array['Customers']['0']['first_name'] = "" . $order->billing->first_name . "";
			$body_array['Customers']['0']['email'] = "" . $order->billing->email . "";
			$body_array['Customers']['0']['company_name'] = "" . $order->billing->company_name . "";
			$body_array['Customers']['0']['address1'] = "" . $order->billing->address_1 . "";
			$body_array['Customers']['0']['address2'] = "" . $order->billing->address_2 . "";
			$body_array['Customers']['0']['city'] = "" . $order->billing->city . "";
			$body_array['Customers']['0']['state_customer'] = "" . $order->billing->state . "";
			$body_array['Customers']['0']['zip_code'] = "" . $order->billing->postcode . "";
			$body_array['Customers']['0']['phone'] = "" . $order->billing->phone . "";
			$body_array['Customers']['0']['has_a_web_account'] = "1";
			$body_array['Customers']['0']['ext_code_customer'] = "" . $this->woocommerce_customer_id . "";
			$body_json = json_encode($body_array);
			if ( WP_DEBUG === true )
				clictill_write_to_woocommerce_logs( 'Customer: '. $body_json );
			$request->write($body_json);
			$this->result = $request->result;
			$this->data = json_decode($this->result);
			// Read from clictill and get the clictill ID
			$request = new ClictillApi;
/*
			$body = '{"Customers":[{
				"ext_code_customer":"'.$this->woocommerce_customer_id.'"
			}]}';
*/
			$body_array = array();
			$body_array['Customers'] = array(array());
			$body_array['Customers']['0']['ext_code_customer'] = "" . $this->woocommerce_customer_id . "";
			$body_json = json_encode($body_array);
			$request->service = 'wsServerCustomer/getCustomer/';
			$request->token = $this->token;
			$request->read($body_json);
			$this->result = $request->result;
			$this->data = json_decode($this->result);

			foreach ($this->data->response->data as $customer) {
				$this->clictill_customer_id = $customer->code_customer;
			}
			$wpdb->insert(
				$table_name,
				array(
					'clictill_id' => $this->clictill_customer_id,
					'woocommerce_id' => $this->woocommerce_customer_id
				)
			);
		}
	}

	function clictill_read_from_clictill() {
		$request = new ClictillApi;
		$request->service = 'wsServerCustomer/getCustomer/';
		$request->token = $this->token;
		$request->read();
		$this->result = $request->result;
		$this->data = json_decode($this->result);
		return;
	}

	function clictill_write_to_clictill() {
		$request = new ClictillApi;
		$request->service = 'wsServerCustomer/setCustomer';
		$request->content = json_encode(
			array(
				'last_name' => $this->last_name,
				'first_name' => $this->first_name
			)
		);
		$request->token = $this->token;
		$request->read();
	}

	function clictill_write_to_woocommerce() {
		$request = new WoocommerceApi;
	}
}