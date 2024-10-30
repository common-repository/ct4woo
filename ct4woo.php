<?php
/*
Plugin Name: Clictill
Plugin URI: https://carlconrad.net/wordpress/plugins/
Description: This plug-in is designed to help you link your Clictill to your WooCommerce based web shop.
Version: 0.5
Author: Le Web tranquille
Author URI: https://lewebtranquille.fr/
Text Domain: ct4woo
Domain Path: /lang/

@package ct4woo
@internal  Main plug-in file
@since 0.1
@todo synchronize categories
@todo assign names
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

global $wpdb;

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/dev-functions.php';
require_once __DIR__ . '/classes/clictill-article.php';
require_once __DIR__ . '/classes/clictill-category.php';
if ( get_option( 'clictill_settings_manage_brands' ) == 'yes')
	require_once __DIR__ . '/taxonomies/brand.php';
if ( get_option( 'clictill_settings_manage_suppliers' ) == 'yes')
	require_once __DIR__ . '/taxonomies/supplier.php';
require_once __DIR__ . '/classes/clictill-customer.php';
require_once __DIR__ . '/classes/clictill-order.php';
require_once __DIR__ . '/classes/clictill-receipt.php';
require_once __DIR__ . '/includes/license-manager.php';

if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
	if ( is_admin() ){
//		add_action( 'admin_menu', 'ct4wc_license_options_page' );
		require_once __DIR__ . '/admin/woocommerce-settings-tab.php';
		require_once __DIR__ . '/admin/woocommerce-status-tab.php';
	}
	if (defined( 'CTWC_MANAGE_ORDERS' ) ) {
		add_action( 'woocommerce_order_status_pending' , 'clictill_order_pending', 10, 1);
		add_action( 'woocommerce_order_status_failed' , 'clictill_order_failed', 10, 1);
		add_action( 'woocommerce_order_status_on-hold' , 'clictill_order_on_hold', 10, 1);
		add_action( 'woocommerce_order_status_processing' , 'clictill_order_processing', 10, 1);
		add_action( 'woocommerce_order_status_completed' , 'clictill_order_completed', 10, 1);
		add_action( 'woocommerce_order_status_refunded' , 'clictill_order_refunded', 10, 1);
		add_action( 'woocommerce_order_status_cancelled' , 'clictill_order_cancelled', 10, 1);
//	add_action( 'woocommerce_payment_complete', 'clictill_order_completed', 10, 1 );
	}
}

/*-----------------------------------------------------*/

register_activation_hook( __FILE__, 'clictill_activate' );

register_deactivation_hook( __FILE__, 'clictill_deactivate' );

register_uninstall_hook(__FILE__, 'clictill_uninstall');

add_action( 'upgrader_process_complete', 'clictill_upgrade',10, 2);

/*-----------------------------------------------------*/

add_action('plugins_loaded', 'clictill_load_textdomain');
function clictill_load_textdomain() {
	load_plugin_textdomain( CLICTILL_DOMAIN, false, CLICTILL_LANG_DIR );
}

/*-----------------------------------------------------*/

add_filter( 'plugin_action_links', 'clictill_plugin_add_settings_link', 10, 5 );
function clictill_plugin_add_settings_link( $actions, $plugin_file ) {
	static $plugin;

	if ( !isset( $plugin ) )
		$plugin = plugin_basename( __FILE__ );
	if ( $plugin == $plugin_file ) {
		$settings = array('settings' => '<a href="'.admin_url( 'admin.php?page=wc-settings&tab=clictill' ).'">'. __( 'Settings' ) .'</a>');
		$actions = array_merge( $settings, $actions );
	}
	return $actions;
}

/*-----------------------------------------------------*/

function clictill_cronjob_activate() {
	if( !wp_next_scheduled( 'clictill_cronjob' ) ) {
	   wp_schedule_event( time(), 'clictill_cron_every_hour', 'clictill_cronjob' );
	}
}

add_action ('clictill_cronjob', 'clictill_cronjob_function');
function clictill_cronjob_function() {
//	if ( !ct4wc_license_is_active() ) die();
	$now = get_transient( 'clictill_product_sync' );
	$new = date("y-m-d H:i:s");
	if ( get_option( 'clictill_settings_manage_categories' ) == 'yes' ) {
		$category_list = new ClictillCategories;
		$category_list->clictill_read_list_from_clictill();
		foreach ($category_list->data->response->data as $category_item) {
			$category = new ClictillCategory;
			$category->clictill_category_id = $category_item->id_category;
			$category->clictill_category_name = $category_item->name_category;
			$category->clictill_write_to_woocommerce();
		}
	}
	if ( get_option( 'clictill_settings_manage_brands' ) == 'yes') {
		$brand_list = new ClictillBrands;
		$brand_list->clictill_read_list_from_clictill();
		foreach ($brand_list->data->response->data as $brand_item) {
			$brand = new ClictillBrand;
			$brand->clictill_brand_id = $brand_item->id_brand;
			$brand->clictill_brand_name = $brand_item->name_brand;
			$brand->clictill_write_to_woocommerce();
		}
	}
	if ( get_option( 'clictill_settings_manage_suppliers' ) == 'yes') {
		$supplier_list = new ClictillSuppliers;
		$supplier_list->clictill_read_list_from_clictill();
		foreach ( $supplier_list->data->response->data as $supplier_item ) {
			$supplier = new ClictillSupplier;
			$supplier->clictill_supplier_id = $supplier_item->id_supplier;
			$supplier->clictill_supplier_name = $supplier_item->name;
			$supplier->clictill_write_to_woocommerce();
		}
	}
	$article_list = new ClictillArticles;
	$article_list->clictill_read_list_from_clictill($now);
	foreach ($article_list->data->response->data as $product) {
		$article = new ClictillArticle;
		$article->clictill_article_id = $product->id_article;
		$article->clictill_read_from_clictill();
		$article->clictill_write_to_woocommerce();
	}
	set_transient( 'clictill_product_sync', $new, DAY_IN_SECONDS );
}

/*-----------------------------------------------------*/

function clictill_order_pending( $order_id ) {
	$customer = new ClictillCustomer;
	$customer->clictill_read_customer_id_from_woocommerce_order( $order_id );
	$order = new ClictillOrder;
	$order->so_ext_code = $order_id;
	$order->clictill_customer_id = $customer->clictill_customer_id;
	$order->woocommerce_order_id = $order_id;
	$order->clictill_write_order_to_clictill('pending');
}

function clictill_order_failed( $order_id ) {
	$message = sprintf( __( 'Order failed: order %s entered failed status.', CLICTILL_DOMAIN ), $order_id );
	clictill_write_to_woocommerce_logs( $message );
}

function clictill_order_on_hold( $order_id ) {
	$customer = new ClictillCustomer;
	$customer->clictill_read_customer_id_from_woocommerce_order( $order_id );
	$order = new ClictillOrder;
	$order->so_ext_code = $order_id;
	$order->clictill_customer_id = $customer->clictill_customer_id;
	$order->woocommerce_order_id = $order_id;
	$order->clictill_write_order_to_clictill('on-hold');
}

function clictill_order_processing( $order_id ) {
	$customer = new ClictillCustomer;
	$customer->clictill_read_customer_id_from_woocommerce_order( $order_id );
	$order = new ClictillOrder;
	$order->so_ext_code = $order_id;
	$order->clictill_customer_id = $customer->clictill_customer_id;
	$order->woocommerce_order_id = $order_id;
	$order->clictill_write_order_to_clictill('processing');
}

function clictill_order_completed( $order_id ) {
	$customer = new ClictillCustomer;
	$customer->clictill_read_customer_id_from_woocommerce_order( $order_id );
	$order = new ClictillOrder;
	$order->so_ext_code = $order_id;
	$order->clictill_customer_id = $customer->clictill_customer_id;
	$order->woocommerce_order_id = $order_id;
	$order->clictill_write_order_to_clictill( 'completed' );
	$receipt = new ClictillReceipt;
	$receipt->so_ext_code = $order_id;
	$receipt->clictill_customer_id = $customer->clictill_customer_id;
	$receipt->woocommerce_order_id = $order_id;
	$receipt->clictill_write_receipt_to_clictill();
}

function clictill_order_refunded( $order_id ) {
	$message = sprintf( __( 'Order refunded: order %s entered refunded status.', CLICTILL_DOMAIN ), $order_id );
	clictill_write_to_woocommerce_logs( $message );
}

function clictill_order_cancelled( $order_id ) {
	$message = sprintf( __( 'Order cancelled: order %s entered cancelled status.', CLICTILL_DOMAIN ), $order_id );
	clictill_write_to_woocommerce_logs( $message );
}

/*-----------------------------------------------------*/

function clictill_activate() {
	require_once __DIR__ . '/init/tables.php';
	clictill_create_tables();
	if( !wp_next_scheduled( 'clictill_cronjob' ) ) {
	   wp_schedule_event( time(), 'clictill_cron_every_hour', 'clictill_cronjob' );
	}
	return;
}

function clictill_upgrade() {
	return;
}

function clictill_deactivate() {
	wp_clear_scheduled_hook( 'clictill_cronjob' );
	delete_transient( 'clictill_product_sync' );
//	clictill_woocommerce_delete_test_products();
	require_once __DIR__ . '/init/tables.php';
	clictill_remove_tables();
	return;
}

function clictill_uninstall() {
	clictill_deactivate();
	clictill_delete_clictill_settings();
//	clictill_delete_ct4wc_license();
	return;
}
