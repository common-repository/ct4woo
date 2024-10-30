<?php
/*
@package woo-clictill
@since 0.1
@internal adds a Clictill Web Services status tab to the WooCommerce main status screen
@todo 
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Add a custom tab to WooCommerce Status section
add_filter('woocommerce_admin_status_tabs','clictill_add_custom_admin_status_tabs', 15, 1);
function clictill_add_custom_admin_status_tabs( $tabs ) {
    $tabs['clictill'] = __( 'Clictill', CLICTILL_DOMAIN );
    return $tabs;
}

// Add the content of the custom tab to WooCommerce Status section
// ( HERE the hook is maid of 'woocommerce_admin_status_content_' + the slug of this tab )
add_action( 'woocommerce_admin_status_content_clictill', 'clictill_add_custom_admin_status_content_clictill' );
function clictill_add_custom_admin_status_content_clictill() {
	global $wpdb;
	$key_slug = 'clictill';
	$table_name = $wpdb->prefix . 'clictill_products';
	$article_list = new ClictillArticles;
	$article_list->clictill_read_list_from_clictill();
	$customer_list = new ClictillCustomer;
	$customer_list->clictill_read_from_clictill();
	$last_sync = get_transient('clictill_product_sync');
    ?>
    <h1><?php _e( 'Clictill tokens', CLICTILL_DOMAIN ); ?></h1>
	<p><?php _e( 'Article token:', CLICTILL_DOMAIN ); ?> <?php echo $article_list->data->response->info->status_ret ?></p>
	<p><?php _e( 'Client token:', CLICTILL_DOMAIN ); ?> <?php echo $customer_list->data->response->info->status_ret ?></p>
	<h1><?php _e( 'Clictill products', CLICTILL_DOMAIN ); ?></h1>
	<p><?php _e( 'Last sync:', CLICTILL_DOMAIN ); ?> <?php echo $last_sync;?></p>
	<p><?php _e( 'This table lists the Clictill products.', CLICTILL_DOMAIN ); ?></p>
<!--
	<p><?php _e( 'Response:', CLICTILL_DOMAIN ); ?> <?php echo $article_list->data->response->info->status_ret ?></p>
	<p><?php _e( 'Number of articles in the clictill database:', CLICTILL_DOMAIN ); ?> <?php echo $article_list->data->response->info->success_rows_count ?></p>
-->
	<table class="wc_status_table wc_status_table--<?php echo $key_slug; ?> widefat" cellspacing="0">
        <tbody class="<?php echo $key_slug; ?>">
            <tr class="section-name-1" >
                <th><strong><?php _e( 'Product reference', CLICTILL_DOMAIN ); ?></strong></th>
                <th><strong><?php _e( 'Clictill Product ID', CLICTILL_DOMAIN ); ?></strong></th>
                <th><strong><?php _e( 'WooCommerce Product ID', CLICTILL_DOMAIN ); ?></strong></th>
				<th><strong><?php _e( 'Inventory', CLICTILL_DOMAIN ); ?></strong></th>
				<th><strong><?php _e( 'Date created', CLICTILL_DOMAIN ); ?></strong></th>
				<th><strong><?php _e( 'Last synced', CLICTILL_DOMAIN ); ?></strong></th>
			</tr>
		</tbody>
<?php
	foreach ($article_list->data->response->data as $product) {
		$sql = "SELECT woocommerce_id, inventory, date_created, last_sync FROM $table_name WHERE clictill_id = $product->id_article";
		$rows = $wpdb->get_results( $sql );
		foreach ( $rows as $row ) {
			echo '<tr><td>'.$product->reference.'</td><td>'.$product->id_article.'</td><td><a href="/wp-admin/post.php?post='.$row->woocommerce_id.'&action=edit">'.$row->woocommerce_id.'</a></td><td>'.$row->inventory.'</td><td>'.$row->date_created.'</td><td>'.$row->last_sync.'</td></tr>';
		}
	}
?>
    </table>

    <h1><?php _e( 'Clictill customers', CLICTILL_DOMAIN ); ?></h1>
	<p><?php _e( 'This table lists the Clictill customers.', CLICTILL_DOMAIN ); ?></p>
<!--
	<p><?php _e( 'Response:', CLICTILL_DOMAIN ); ?> <?php echo $customer_list->data->response->info->status_ret ?></p>
	<p><?php _e( 'Number of customers in the clictill database:', CLICTILL_DOMAIN ); ?> <?php echo $customer_list->data->response->info->success_rows_count ?></p>
-->
	<table class="wc_status_table wc_status_table--<?php echo $key_slug; ?> widefat" cellspacing="0">
        <tbody class="<?php echo $key_slug; ?>">
            <tr class="section-name-1" >
                <th><strong><?php _e( 'Customer name', CLICTILL_DOMAIN ); ?></strong></th>
                <th><strong><?php _e( 'Customer first-name', CLICTILL_DOMAIN ); ?></strong></th>
				<th><strong><?php _e( 'Clictill Customer ID', CLICTILL_DOMAIN ); ?></strong></th>
				<th><strong><?php _e( 'WooCommerce customer ID', CLICTILL_DOMAIN ); ?></strong></th>
			</tr>
		</tbody>
<?php
	foreach ($customer_list->data->response->data as $customer_list) {
		echo '<tr><td>'.$customer_list->last_name.'</td><td>'.$customer_list->first_name.'</td><td>'.$customer_list->code_customer.'</td><td>'.$customer_list->ext_code_customer.'</td></tr>';
	}
?>
    </table>
<?php
}