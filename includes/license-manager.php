<?php
/*
@package ct4woo
@since 0.4
@internal license manager
@todo 
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function ct4wc_create_license() {
	$postURL = 'https://lewebtranquille.fr'; 
	$secretKey = '5ca480ece5c700.11682111';

	$api_params = array();
	$api_params['slm_action'] = 'slm_create_new';
	$api_params['secret_key'] = $secretKey;
	$api_params['first_name'] = get_option('clictill_woocommerce_license_first_name');
	$api_params['last_name'] = get_option('clictill_woocommerce_license_last_name');
	$api_params['email'] = get_option('clictill_woocommerce_license_email');
	$api_params['company_name'] = get_option('clictill_woocommerce_license_company');
//		$api_params['txn_id'] = 'ABC0987654321';
	$api_params['max_allowed_domains'] = '1';
	$api_params['product_ref'] = 'CT4WC';
	$api_params['date_created'] = date("Y-m-d");
//		$api_params['date_expiry'] = '2016-01-01';

	$response = wp_remote_get(
		add_query_arg(
			$api_params,
			$postURL
		),
		array(
			'timeout' => 20,
			'sslverify' => false
		)
	);
	$license_data = json_decode( wp_remote_retrieve_body( $response ) );
	add_option('clictill_woocommerce_license_key', $license_data->key );
	return($license_data->key);
}


function ct4wc_validate_license() {
	$postURL = 'https://lewebtranquille.fr'; 
	$secretKey = '5ca480ece5c782.26535040';

	$api_params = array();
	$api_params['slm_action'] = 'slm_check';
	$api_params['secret_key'] = $secretKey;
	$api_params['license_key'] = get_option('clictill_woocommerce_license_key');

	$response = wp_remote_get(
		add_query_arg(
			$api_params,
			$postURL
		),
		array(
			'timeout' => 20,
			'sslverify' => false
		)
	);
	if ( is_wp_error( $response ) )
		return false;

	$license_data = json_decode( wp_remote_retrieve_body( $response ) );
	return($license_data->status);
}

function ct4wc_license_is_active() {
	$status = ct4wc_validate_license();
	if ($status == 'active')
		return true;
	else
		return false;
}