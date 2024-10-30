<?php
/*
@package ct4woo
@since 0.2
@internal manages required tables
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function clictill_create_tables() {
	require_once ( ABSPATH . 'wp-admin/includes/upgrade.php' );
	global $wpdb;
	
	$charset_collate = $wpdb->get_charset_collate();

	$table_name = $wpdb->prefix . 'clictill_customers';
	$sql = "CREATE TABLE IF NOT EXISTS $table_name (
		clictill_id INT(10) NOT NULL UNIQUE,
		woocommerce_id INT(10) NOT NULL UNIQUE,
		PRIMARY KEY  (clictill_id)
	) ENGINE=InnoDB $charset_collate;";
	dbDelta($sql);

	$table_name = $wpdb->prefix . 'clictill_products';
/*
	$sql = "CREATE TABLE IF NOT EXISTS $table_name (
		clictill_id INT(10) NOT NULL UNIQUE,
		woocommerce_id INT(10) NOT NULL UNIQUE,
		inventory INT(10) NOT NULL,
		date_created DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
		last_sync DATETIME on update CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
		PRIMARY KEY (clictill_id)
	) $charset_collate;";
*/
	$sql = "CREATE TABLE IF NOT EXISTS $table_name (
		clictill_id INT(10) NOT NULL UNIQUE,
		woocommerce_id INT(10) NOT NULL UNIQUE,
		inventory INT(10) NOT NULL,
		date_created DATETIME NOT NULL,
		last_sync DATETIME NOT NULL,
		PRIMARY KEY  (clictill_id)
	) ENGINE=InnoDB $charset_collate;";
	dbDelta($sql);

	$table_name = $wpdb->prefix . 'clictill_brands';
	$sql = "CREATE TABLE IF NOT EXISTS $table_name (
		clictill_id INT(10) NOT NULL UNIQUE,
		woocommerce_id INT(10) NOT NULL UNIQUE,
		date_created DATETIME NOT NULL,
		last_sync DATETIME NOT NULL,
		PRIMARY KEY  (clictill_id)
	) ENGINE=InnoDB $charset_collate;";
	dbDelta($sql);

	$table_name = $wpdb->prefix . 'clictill_categories';
	$sql = "CREATE TABLE IF NOT EXISTS $table_name (
		clictill_id INT(10) NOT NULL UNIQUE,
		woocommerce_id INT(10) NOT NULL UNIQUE,
		date_created DATETIME NOT NULL,
		last_sync DATETIME NOT NULL,
		PRIMARY KEY  (clictill_id)
	) ENGINE=InnoDB $charset_collate;";
	dbDelta($sql);

	$table_name = $wpdb->prefix . 'clictill_suppliers';
	$sql = "CREATE TABLE IF NOT EXISTS $table_name (
		clictill_id INT(10) NOT NULL UNIQUE,
		woocommerce_id INT(10) NOT NULL UNIQUE,
		date_created DATETIME NOT NULL,
		last_sync DATETIME NOT NULL,
		PRIMARY KEY  (clictill_id)
	) ENGINE=InnoDB $charset_collate;";
	dbDelta($sql);
}

function clictill_remove_tables() {
	global $wpdb;

	$table_name = $wpdb->prefix . 'clictill_customers';
	$sql = "DROP TABLE IF EXISTS $table_name;";
	$wpdb->query( $sql );

	$table_name = $wpdb->prefix . 'clictill_products';
	$sql = "DROP TABLE IF EXISTS $table_name;";
	$wpdb->query( $sql );

	$table_name = $wpdb->prefix . 'clictill_brands';
	$sql = "DROP TABLE IF EXISTS $table_name;";
	$wpdb->query( $sql );

	$table_name = $wpdb->prefix . 'clictill_categories';
	$sql = "DROP TABLE IF EXISTS $table_name;";
	$wpdb->query( $sql );
	
	$table_name = $wpdb->prefix . 'clictill_suppliers';
	$sql = "DROP TABLE IF EXISTS $table_name;";
	$wpdb->query( $sql );
	
	return;
}
