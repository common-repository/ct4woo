<?php
/*
@package ct4woo
@since 0.1
@internal adds methods for writing sales data using the clictill API
@todo 
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once 'clictill-api.php';
require_once 'woocommerce-api.php';

class ClictillOrder {

	protected $token;

	function __construct() {
		$this->token = get_option( 'clictill_settings_order_token' );
		$this->price_code = get_option( 'clictill_settings_price_code' );
	}

	function clictill_write_order_to_clictill($status) {
		switch($status) {
			case('pending'):
				$so_status = 0;
				break;
			case('on-hold'):
				$so_status = 1	;
				break;
			case('processing'):
				$so_status = 2;
				break;
			case('completed'):
				$so_status = 4;
				break;
		}
		$woocommerce = new WoocommerceApi;
		$woocommerce->service = 'orders/'.$this->woocommerce_order_id;
		$woocommerce->get();
		$woocommerce_response = $woocommerce->response;
		$order = json_decode( $woocommerce_response['body'] );
		$this->woocommerce_customer_id = $order->customer_id;

		$request = new ClictillApi;
		$request->service = 'wsServerSo/getSo/';
		$request->token = $this->token;
		$body_array = array();
		$body_array['Sos'] = array(array());
		$body_array['Sos']['0']['so_numbers'] = array(array());
		$body_array['Sos']['0']['so_numbers']['0']['so_number'] = "" . $order->id . "";
		$body_json = json_encode($body_array);

		$request->read( $body_json );
		$result = $request->result;
		$clictill_response = json_decode( $result );
		$success_rows_count = $clictill_response->response->info->success_rows_count;
		$body_array = array();
		$body_array['fagActiveLogs'] = true;
		
		if ( $success_rows_count == 0 ) {
			$item_line_number = 0;
			$items_array = array(array());
			foreach ($order->line_items as $item) {
				$total_wt_tax = $item->total + $item->total_tax;

				$items_array[$item_line_number]['so_item_line_number'] = "" . $item_line_number + 1 . "";
				$items_array[$item_line_number]['so_item_reference'] = "" . $item->name . "";
	/*
				$items_array[$item_line_number]['so_item_article_code_ext'] = "";
				$items_array[$item_line_number]['so_item_serial_number'] = "";
				$items_array[$item_line_number]['so_item_lot_number'] = "";
				$items_array[$item_line_number]['so_item_ecotax'] = "";
	*/
				$items_array[$item_line_number]['so_item_qty_ordered'] = "" . $item->quantity . "";
	/*
				$items_array[$item_line_number]['so_item_barcode'] = "";
				$items_array[$item_line_number]['so_item_disc_type'] = "";
				$items_array[$item_line_number]['so_item_disc_amt'] = "";
				$items_array[$item_line_number]['so_item_total_disc_wo_tax'] = "";
				$items_array[$item_line_number]['so_item_total_disc_wt_tax'] = "";
	*/
				$items_array[$item_line_number]['so_item_brut_price_wo_tax'] = "" . $item->total . "";
				$items_array[$item_line_number]['so_item_brut_price_wt_tax'] = "" . $total_wt_tax . "";
				$items_array[$item_line_number]['so_item_net_price_wo_tax'] = "" . $item->total . "";
				$items_array[$item_line_number]['so_item_net_price_wt_tax'] = "" . $total_wt_tax . "";
				$items_array[$item_line_number]['so_item_total_brut_wo_tax'] = "" . $item->total . "";
				$items_array[$item_line_number]['so_item_total_brut_wt_tax'] = "" . $total_wt_tax . "";
				$items_array[$item_line_number]['so_item_total_net_wo_tax'] = "" . $item->total . "";
				$items_array[$item_line_number]['so_item_total_net_wt_tax'] = "" . $total_wt_tax . "";
				$items_array[$item_line_number]['so_item_total_discount_value_spread_wt_tax'] = "";
				$items_array[$item_line_number]['so_item_total_tax_brut'] = "" . $item->total_tax . "";
				$items_array[$item_line_number]['so_item_total_tax_net_net'] = "" . $item->total_tax . "";
				$items_array[$item_line_number]['so_item_subtotal_discount_spread_wo_tax'] = "";
				$items_array[$item_line_number]['so_item_total_discount_value_spread_wt_tax'] = "";
				$items_array[$item_line_number]['so_item_subtotal_discount_value'] = "";
				$items_array[$item_line_number]['so_item_total_net_net_wo_tax'] = "" . $item->total . "";
				$items_array[$item_line_number]['so_item_total_net_net_wt_tax'] = "" . $total_wt_tax . "";
				$items_array[$item_line_number]['so_item_fixed_price'] = "";
				$items_array[$item_line_number]['so_item_is_professional'] = "1";
				$items_array[$item_line_number]['so_item_is_taxable'] = "1";
				$items_array[$item_line_number]['so_item_is_package'] = "";
				$items_array[$item_line_number]['so_item_tax_code'] = "Normale";
				$items_array[$item_line_number]['so_item_tax_code_ext'] = "";
				$items_array[$item_line_number]['so_item_code_price_code'] = "" . $this->price_code . "";
				$items_array[$item_line_number]['so_item_code_price_code_ext'] = "";
				$items_array[$item_line_number]['so_item_tax_area_code'] = "France";
				$items_array[$item_line_number]['so_item_tax_area_code_ext'] = "";
				$items_array[$item_line_number]['so_item_discount_code'] = "";
				$items_array[$item_line_number]['so_item_discount_code_ext'] = "";
				$items_array[$item_line_number]['so_item_global_discount_spread_wt_tax'] = "";
				$items_array[$item_line_number]['so_item_global_discount_spread_wo_tax'] = "";

				$item_line_number++;
			}

			$body_array['saleOrders'] = array(array());
			$body_array['saleOrders']['0']['so_number'] = "$order->id";
			$body_array['saleOrders']['0']['so_ext_code'] = "$order->id";
			$body_array['saleOrders']['0']['so_status'] = "$so_status";
			$body_array['saleOrders']['0']['so_created_date'] = "". date('Y-m-d H:i:s', strtotime($order->date_created)) ."";
			$body_array['saleOrders']['0']['so_cust_code'] = "$this->clictill_customer_id";
			$body_array['saleOrders']['0']['so_cust_code_ext'] = "$order->customer_id";
			$body_array['saleOrders']['0']['so_shipto_code'] = "$this->clictill_customer_id";
			$body_array['saleOrders']['0']['so_shipto_ext_code'] = "$order->customer_id";
			$body_array['saleOrders']['0']['so_user_createdby_nickname'] = "". get_option( 'clictill_settings_user' ) ."";
			$body_array['saleOrders']['0']['so_createdby_store_code'] = "". get_option( 'clictill_settings_shop' ) ."";
			$body_array['saleOrders']['0']['so_processat_store_code'] = "". get_option( 'clictill_settings_shop' ) ."";
			$body_array['saleOrders']['0']['so_items'] = $items_array;
		}
		else {
			$body_array['saleOrders'] = array(array());
			$body_array['saleOrders']['0']['so_number'] = "$order->id";
			$body_array['saleOrders']['0']['so_status'] = "$so_status";
			$body_array['saleOrders']['0']['so_items'] = array(array());
		}
		$body_json = json_encode($body_array);
		if ( WP_DEBUG === true )
			clictill_write_to_woocommerce_logs( 'Order' . $this->woocommerce_order_id . $body_json );

		$request = new ClictillApi;
		$request->service = 'wsServerSo/setSo/';
		$request->token = $this->token;
		$request->write($body_json);
		$this->result = $request->result;
	}
}