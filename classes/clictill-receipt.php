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

class ClictillReceipt {

	protected $token;

	function __construct() {
		$this->token = get_option( 'clictill_settings_receipt_token' );
		$this->price_code = get_option( 'clictill_settings_price_code' );
	}

	function clictill_write_receipt_to_clictill() {
		$woocommerce = new WoocommerceApi;
		$woocommerce->service = 'orders/'.$this->woocommerce_order_id;
		$woocommerce->get();
		$response = $woocommerce->response;
		$order = json_decode( $response['body'] );
		$this->woocommerce_customer_id = $order->customer_id;

		$message = sprintf( __( 'Receipt %s. For customer CT/%s, WC/%s', CLICTILL_DOMAIN ), $this->so_ext_code, $this->clictill_customer_id, $this->woocommerce_customer_id);
		if ( WP_DEBUG === true )
			clictill_write_to_woocommerce_logs( 'Product update: '. $message );
	
		$tender_array = array(array());

		$articles_line_number = 0;
		$articles_array = array(array());
		foreach ( $order->line_items as $item ) {
			$total_wt_tax = $item->total + $item->total_tax;

			$articles_array[$articles_line_number]['line_number'] = "" . $articles_line_number + 1 . "";
			$articles_array[$articles_line_number]['reference'] = "" . $item->name . "";
/*
			$articles_array[$articles_line_number]['article_code_ext'] = "";
			$articles_array[$articles_line_number]['serial_number'] = "";
			$articles_array[$articles_line_number]['lot_number'] = "";
			$articles_array[$articles_line_number]['ecotax'] = "";
*/
			$articles_array[$articles_line_number]['quantity'] = "" . $item->quantity . "";
/*
			$articles_array[$articles_line_number]['barcode'] = "";
			$articles_array[$articles_line_number]['disc_type'] = "";
			$articles_array[$articles_line_number]['disc_amt'] = "";
			$articles_array[$articles_line_number]['total_disc_wo_tax'] = "";
			$articles_array[$articles_line_number]['total_disc_wt_tax'] = "";
*/
			$articles_array[$articles_line_number]['brut_price_wo_tax'] = "" . $item->total . "";
			$articles_array[$articles_line_number]['brut_price_wt_tax'] = "" . $total_wt_tax . "";
			$articles_array[$articles_line_number]['net_price_wo_tax'] = "" . $item->total . "";
			$articles_array[$articles_line_number]['net_price_wt_tax'] = "" . $total_wt_tax . "";
			$articles_array[$articles_line_number]['total_brut_wo_tax'] = "" . $item->total . "";
			$articles_array[$articles_line_number]['total_brut_wt_tax'] = "" . $total_wt_tax . "";
			$articles_array[$articles_line_number]['total_net_wo_tax'] = "" . $item->total . "";
			$articles_array[$articles_line_number]['total_net_wt_tax'] = "" . $total_wt_tax . "";
			$articles_array[$articles_line_number]['total_discount_value_spread_wt_tax'] = "";
			$articles_array[$articles_line_number]['total_tax_brut'] = "" . $item->total_tax . "";
			$articles_array[$articles_line_number]['total_tax_net_net'] = "" . $item->total_tax . "";
			$articles_array[$articles_line_number]['subtotal_discount_spread_wo_tax'] = "";
			$articles_array[$articles_line_number]['total_discount_value_spread_wt_tax'] = "";
			$articles_array[$articles_line_number]['subtotal_discount_value'] = "";
			$articles_array[$articles_line_number]['total_net_net_wo_tax'] = "" . $item->total . "";
			$articles_array[$articles_line_number]['total_net_net_wt_tax'] = "" . $total_wt_tax . "";
			$articles_array[$articles_line_number]['fixed_price'] = "";
			$articles_array[$articles_line_number]['is_professional'] = "1";
			$articles_array[$articles_line_number]['is_taxable'] = "1";
			$articles_array[$articles_line_number]['is_package'] = "";
			$articles_array[$articles_line_number]['code_tax'] = "Normale";
			$articles_array[$articles_line_number]['ext_code_tax'] = "";
			$articles_array[$articles_line_number]['code_price'] = "" . $this->price_code . "";
			$articles_array[$articles_line_number]['ext_code_price'] = "";
			$articles_array[$articles_line_number]['tax_area'] = "France";
			$articles_array[$articles_line_number]['tax_area_code_ext'] = "";
			$articles_array[$articles_line_number]['discount_code'] = "";
			$articles_array[$articles_line_number]['ext_discount_code'] = "";
			$articles_array[$articles_line_number]['global_discount_spread_wt_tax'] = "";
			$articles_array[$articles_line_number]['global_discount_spread_wo_tax'] = "";

			$articles_line_number++;
		}

		$body_array = array();
		$body_array['fagActiveLogs'] = true;
		$body_array['Receipts'] = array(array());
		$body_array['Receipts']['0']['receipt_number'] = "$order->id";
		$body_array['Receipts']['0']['status'] = "1";
		$body_array['Receipts']['0']['nickname'] = "". get_option( 'clictill_settings_user' ) ."";
		$body_array['Receipts']['0']['so_number'] = "$order->id";
		$body_array['Receipts']['0']['created_date'] = "". date('Y-m-d H:i:s', strtotime($order->date_created)) ."";
		$body_array['Receipts']['0']['modified_date'] = "". date('Y-m-d H:i:s', strtotime($order->date_created)) ."";
		$body_array['Receipts']['0']['shop_code'] = "". get_option( 'clictill_settings_shop' ) ."";

		$body_array['Receipts']['0']['so_ext_code'] = "$order->id";
		$body_array['Receipts']['0']['cust_code'] = "$this->clictill_customer_id";
		$body_array['Receipts']['0']['cust_code_ext'] = "$order->customer_id";
		$body_array['Receipts']['0']['nickname_user_account_created'] = "". get_option( 'clictill_settings_user' ) ."";
		$body_array['Receipts']['0']['shop_code'] = "". get_option( 'clictill_settings_shop' ) ."";
//		$body_array['Receipts']['0']['receipt_tender'] = $tender_array;
		$body_array['Receipts']['0']['receipt_articles'] = $articles_array;
		$body_json = json_encode( $body_array );

		if ( WP_DEBUG === true )
			clictill_write_to_woocommerce_logs( 'Receipt'.$this->woocommerce_order_id . $body_json);

		$request = new ClictillApi;
		$request->service = 'wsServerReceipt/setReceipt/';
		$request->token = $this->token;
		$request->write( $body_json );
		$this->result = $request->result;
	}
}