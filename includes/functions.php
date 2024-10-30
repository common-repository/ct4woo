<?php
/*
@package ct4woo
@since 0.1
@internal various useful functions
@todo 
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function clictill_get_clictill_customer_id($woocommerce_customer_id) {
	global $wpdb;
	$table_name = $wpdb->prefix . 'clictill_customers';
	$sql = "SELECT clictill_id FROM $table_name WHERE woocommerce_id = $woocommerce_customer_id";
	$row = $wpdb->get_row( $sql );
	if ($row)
		return($row->clictill_id);
	else
		return(false);
}

function clictill_write_to_woocommerce_logs($message) {
	$logger = new WC_Logger();
	$logger->add( 'clictill-for-woocommerce', $message );
	return;
}