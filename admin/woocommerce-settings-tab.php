<?php
/*
@package woo-clictill
@since 0.1
@internal adds a Clictill Web Services settings tab to the WooCommerce main settings screen
@documentation https://docs.woocommerce.com/document/adding-a-section-to-a-settings-tab/
@todo Add a read this first section
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once __DIR__ . '/../classes/woocommerce-shipping.php';

add_filter( 'woocommerce_settings_tabs_array', 'clictill_add_settings_tab', 50 );
function clictill_add_settings_tab( $tabs ) {
	$tabs['clictill'] = __( 'Clictill', CLICTILL_DOMAIN );
	return $tabs;
}

/*
add_filter( 'woocommerce_get_sections_clictill', 'clictill_add_section', 40 );
function clictill_add_section( $sections ) {
	$sections['startup_instructions'] = __( 'Installation instructions', CLICTILL_DOMAIN );
	return $sections;
}
*/

add_action( 'woocommerce_settings_tabs_clictill', 'clictill_settings_tab' );
function clictill_settings_tab() {
    woocommerce_admin_fields( clictill_get_clictill_settings() );
}

add_action( 'woocommerce_update_options_clictill', 'clictill_update_clictill_settings' );
function clictill_update_clictill_settings() {
    woocommerce_update_options( clictill_get_clictill_settings() );
}

function clictill_get_clictill_settings() {
	$clictill_default_css = 'min-width:150px;';

	$clictill_woocommerce_api_section = array();
	$clictill_woocommerce_api_section['name'] = __('WooCommerce API', CLICTILL_DOMAIN);
	$clictill_woocommerce_api_section['type'] = 'title';
	$clictill_woocommerce_api_section['desc'] = __('The following WooCommerce keys are to be generated on the <a href="/wp-admin/admin.php?page=wc-settings&tab=advanced&section=keys">API REST page</a>. Make sure to grant Read/Write access rights.', CLICTILL_DOMAIN);
	$clictill_woocommerce_api_section['id'] = 'clictill_woocommerce_api_section';
	
	$clictill_woocommerce_consumer_key = array();
	$clictill_woocommerce_consumer_key['name'] = __('WooCommerce API consumer key', CLICTILL_DOMAIN);
	$clictill_woocommerce_consumer_key['type'] = 'text';
	$clictill_woocommerce_consumer_key['css'] = $clictill_default_css;
	$clictill_woocommerce_consumer_key['desc'] = __('WooCommerce API consumer key', CLICTILL_DOMAIN);
	$clictill_woocommerce_consumer_key['id'] = 'clictill_woocommerce_consumer_key';
	
	$clictill_woocommerce_consumer_secret = array();
	$clictill_woocommerce_consumer_secret['name'] = __('WooCommerce API consumer secret', CLICTILL_DOMAIN);
	$clictill_woocommerce_consumer_secret['type'] = 'text';
	$clictill_woocommerce_consumer_secret['css'] = $clictill_default_css;
	$clictill_woocommerce_consumer_secret['desc'] = __('WooCommerce API consumer secret', CLICTILL_DOMAIN);
	$clictill_woocommerce_consumer_secret['id'] = 'clictill_woocommerce_consumer_secret';
	
	$clictill_woocommerce_api_section_end = array();
	$clictill_woocommerce_api_section_end['type'] = 'sectionend';
	$clictill_woocommerce_api_section_end['id'] = 'clictill_woocommerce_api_section_end';

/*
	$startup_instructions = '<div style="background:white;padding:20px;border-radius: 25px;">';
	$startup_instructions .= '<h1>'. __('Installation instructions', CLICTILL_DOMAIN) .'</h1>';
	$startup_instructions .= '<p>'. __('Please follow these steps to properly install the Clictill to WooCommerce plug-in.', CLICTILL_DOMAIN) .'</p>';
	$startup_instructions .= '<ol>';
	$startup_instructions .= '<li>'. __('Create and register the required ClicTill tokens.', CLICTILL_DOMAIN) .'</li>';
	$startup_instructions .= '<li>'. __('Create and register the required WooCommerce tokens.', CLICTILL_DOMAIN) .'</li>';
	$startup_instructions .= '<li>'. __('Save the settings.', CLICTILL_DOMAIN) .'</li>';
	$startup_instructions .= '<li>'. __('Check all tokens are correct.', CLICTILL_DOMAIN) .'</li>';
	$startup_instructions .= '<li>'. __('Register the required user from the drop down list.', CLICTILL_DOMAIN) .'</li>';
	$startup_instructions .= '<li>'. __('Define the required VAT mappings.', CLICTILL_DOMAIN) .'</li>';
	$startup_instructions .= '<li>'. __('Save the settings.', CLICTILL_DOMAIN) .'</li>';
	$startup_instructions .= '<li>'. __('Register the required shop from the drop down list.', CLICTILL_DOMAIN) .'</li>';
	$startup_instructions .= '<li>'. __('Save the settings.', CLICTILL_DOMAIN) .'</li>';
	$startup_instructions .= '</ol>';
	$startup_instructions .= '<p>'. __('Please <a href="/wp-admin/admin.php?page=wc-status&tab=logs">check the logs</a> if the plug-in does not seems to operate correctly.', CLICTILL_DOMAIN) .'</p>';
	$startup_instructions .= '</div>';

	if ( !get_option( 'clictill_hide_installation_instructions' ) || get_option( 'clictill_hide_installation_instructions' ) == 'no' )
		echo $startup_instructions;
*/

	$settings = array();

	$settings['woocommerce_api_section'] = $clictill_woocommerce_api_section;
	$settings['clictill_woocommerce_consumer_key'] = $clictill_woocommerce_consumer_key;
	$settings['clictill_woocommerce_consumer_secret'] = $clictill_woocommerce_consumer_secret;
	$settings['woocommerce_api_section_end'] = $clictill_woocommerce_api_section_end;

/*
	$clictill_hide_installation_instructions = array();
	$clictill_hide_installation_instructions['type'] = 'checkbox';
	$clictill_hide_installation_instructions['desc'] = __('Hide installation instructions', CLICTILL_DOMAIN);
	$clictill_hide_installation_instructions['id'] = 'clictill_hide_installation_instructions';
	$settings['clictill_hide_installation_instructions'] = $clictill_hide_installation_instructions;
*/

	if ( get_option( 'clictill_woocommerce_consumer_key' ) ) {
		if ( get_option( 'clictill_settings_users_token' ) ) {
			require_once __DIR__ . '/../classes/clictill-user.php';
			$clictill_users = new ClictillUsers;
			$clictill_users->clictill_read_list_from_clictill();
			$clictill_user_options = array();
			$clictill_user_options['none'] = __('None', CLICTILL_DOMAIN );
	//		$clictill_user_options = array('none' => __('None', CLICTILL_DOMAIN), 'TARDY' => 'TARDY');
			foreach ($clictill_users->data->response->data as $clictill_user) {
				$clictill_user_options[$clictill_user->nickname] = $clictill_user->nickname;
			}
			$user_description = __('Vendor code as found on <a href="https://clic-till.com/paramApp/users">this page</a>.', CLICTILL_DOMAIN);
			if ( $clictill_user_nickname = get_option( 'clictill_settings_user' ) ) {
				require_once __DIR__ . '/../classes/clictill-user.php';
				$clictill_user = new ClictillUser;
				$clictill_user->clictill_user_nickname = $clictill_user_nickname;
				$clictill_user->clictill_read_from_clictill();
			}
			if ( get_option( 'clictill_settings_shops_token' ) ) {
				require_once __DIR__ . '/../classes/clictill-shop.php';
				$clictill_users = new ClictillShops;
				$clictill_shop_options = array('none' => __('None', CLICTILL_DOMAIN), 'LYO' => 'LYO');
	//			$clictill_shop_options = array('none' => __('None', CLICTILL_DOMAIN), 'LYO' => 'LYO', 'Inovaport' => 'Inovaport');
				$clictill_shop_description = __('Shop', CLICTILL_DOMAIN);
			}
			else {
				$clictill_shop_options = array('none' => __('None', CLICTILL_DOMAIN));
				$clictill_shop_description = __('Displaying the list of shops requires entering the shops token.', CLICTILL_DOMAIN);
			}
		}
		else {
			$clictill_user_options = array('none' => __('None', CLICTILL_DOMAIN));
	//		$clictill_user_options = array('none' => __('None', CLICTILL_DOMAIN), 'TARDY' => 'TARDY');
			$user_description = __('Displaying the list of users requires entering the users token.', CLICTILL_DOMAIN);
			$clictill_shop_options = array('none' => __('None', CLICTILL_DOMAIN));
	//		$clictill_shop_options = array('none' => __('None', CLICTILL_DOMAIN), 'LYO' => 'LYO', 'Inovaport' => 'Inovaport');
			$clictill_shop_description = __('Displaying the list of shops requires entering the shops token.', CLICTILL_DOMAIN);
		}

		if (defined( 'CTWC_MANAGE_ORDERS' ) ) {
			$clictill_clictill_account_section_title = array();
			$clictill_clictill_account_section_title['name'] = __('Clictill user and shop', CLICTILL_DOMAIN);
			$clictill_clictill_account_section_title['type'] = 'title';
			$clictill_clictill_account_section_title['desc'] = __('The following accounts are needed in order to synchronize orders.', CLICTILL_DOMAIN);
			$clictill_clictill_account_section_title['id'] = 'clictill_clictill_account_section_title';
			$settings['clictill_account_section'] = $clictill_clictill_account_section_title;

			$clictill_user_token_menu = array();
			$clictill_user_token_menu['name'] = __('Users token', CLICTILL_DOMAIN);
			$clictill_user_token_menu['type'] = 'text';
			$clictill_user_token_menu['css'] = $clictill_default_css;
			$clictill_user_token_menu['desc'] = __('Users token', CLICTILL_DOMAIN);
			$clictill_user_token_menu['id'] = 'clictill_settings_users_token';

			$clictill_user_menu = array();
			$clictill_user_menu['name'] = __('User', CLICTILL_DOMAIN);
			$clictill_user_menu['type'] = 'select';
			$clictill_user_menu['options'] = $clictill_user_options;
			$clictill_user_menu['css'] = $clictill_default_css;
			$clictill_user_menu['desc'] = $user_description;
			$clictill_user_menu['id'] = 'clictill_settings_user';

			$clictill_shop_token_menu = array();
			$clictill_shop_token_menu['name'] = __('Shops token', CLICTILL_DOMAIN);
			$clictill_shop_token_menu['type'] = 'text';
			$clictill_shop_token_menu['css'] = $clictill_default_css;
			$clictill_shop_token_menu['desc'] = __('Shops token', CLICTILL_DOMAIN);
			$clictill_shop_token_menu['id'] = 'clictill_settings_shops_token';

			$clictill_shop_menu = array();
			$clictill_shop_menu['name'] = __('Shop', CLICTILL_DOMAIN);
			$clictill_shop_menu['type'] = 'select';
			$clictill_shop_menu['options'] = $clictill_shop_options;
			$clictill_shop_menu['css'] = $clictill_default_css;
			$clictill_shop_menu['desc'] = $clictill_shop_description;
			$clictill_shop_menu['id'] = 'clictill_settings_shop';

			$clictill_settings_price_code = array();
			$clictill_settings_price_code['name'] = __('Price code', CLICTILL_DOMAIN);
			$clictill_settings_price_code['type'] = 'text';
			$clictill_settings_price_code['css'] = $clictill_default_css;
			$clictill_settings_price_code['default'] = 'STANDARD';
			$clictill_settings_price_code['desc'] = __('Price code (use STANDARD if no specific price code is defined)', CLICTILL_DOMAIN);
			$clictill_settings_price_code['id'] = 'clictill_settings_price_code';

			$clictill_account_section_end = array();
			$clictill_account_section_end['type'] = 'sectionend';
			$clictill_account_section_end['id'] = 'clictill_account_section_end';
		}			
		
		$clictill_api_section = array();
		$clictill_api_section['name'] = __('Clictill Web Services', CLICTILL_DOMAIN);
		$clictill_api_section['type'] = 'title';
		$clictill_api_section['desc'] = __('The following web services are available and need to be activated by entering the required token. Check and/or regenerate the tokens here: <a href="https://clic-till.com/paramApp/webservice_tokens/index" target="_blank">https://clic-till.com/paramApp/webservice_tokens/index</a>.', CLICTILL_DOMAIN);
		$clictill_api_section['id'] = 'clictill_clictill_api_section_title';
		
		$clictill_settings_client_token = array();
		$clictill_settings_client_token['name'] = __('Client token', CLICTILL_DOMAIN);
		$clictill_settings_client_token['type'] = 'text';
		$clictill_settings_client_token['css'] = $clictill_default_css;
		$clictill_settings_client_token['desc'] = __('Client token', CLICTILL_DOMAIN);
		$clictill_settings_client_token['id'] = 'clictill_settings_client_token';

		$clictill_settings_article_token = array();
		$clictill_settings_article_token['name'] = __('Article token', CLICTILL_DOMAIN);
		$clictill_settings_article_token['type'] = 'text';
		$clictill_settings_article_token['css'] = $clictill_default_css;
		$clictill_settings_article_token['desc'] = __('Article token', CLICTILL_DOMAIN);
		$clictill_settings_article_token['id'] = 'clictill_settings_article_token';
		
		if (defined( 'CTWC_MANAGE_ORDERS' ) ) {
			$clictill_settings_manage_stock = array();
			$clictill_settings_manage_stock['name'] = __('Manage stock', CLICTILL_DOMAIN);
			$clictill_settings_manage_stock['type'] = 'checkbox';
			$clictill_settings_manage_stock['desc'] = __('Synchronize stock information', CLICTILL_DOMAIN);
			$clictill_settings_manage_stock['id'] = 'clictill_settings_manage_stock';
			
			$clictill_settings_order_token = array();
			$clictill_settings_order_token['name'] = __('Order token', CLICTILL_DOMAIN);
			$clictill_settings_order_token['type'] = 'text';
			$clictill_settings_order_token['css'] = $clictill_default_css;
			$clictill_settings_order_token['desc'] = __('Order token', CLICTILL_DOMAIN);
			$clictill_settings_order_token['id'] = 'clictill_settings_order_token';
			
			$clictill_settings_receipt_token = array();
			$clictill_settings_receipt_token['name'] = __('Receipt token', CLICTILL_DOMAIN);
			$clictill_settings_receipt_token['type'] = 'text';
			$clictill_settings_receipt_token['css'] = $clictill_default_css;
			$clictill_settings_receipt_token['desc'] = __('Receipt token', CLICTILL_DOMAIN);
			$clictill_settings_receipt_token['id'] = 'clictill_settings_receipt_token';
		}
		
		$clictill_settings_family_token = array();
		$clictill_settings_family_token['name'] = __('Family token', CLICTILL_DOMAIN);
		$clictill_settings_family_token['type'] = 'text';
		$clictill_settings_family_token['css'] = $clictill_default_css;
		$clictill_settings_family_token['desc'] = __('Family token', CLICTILL_DOMAIN);
		$clictill_settings_family_token['id'] = 'clictill_settings_family_token';

		$clictill_settings_taxcodes_token = array();
		$clictill_settings_taxcodes_token['name'] = __('Tax codes token', CLICTILL_DOMAIN);
		$clictill_settings_taxcodes_token['type'] = 'text';
		$clictill_settings_taxcodes_token['css'] = $clictill_default_css;
		$clictill_settings_taxcodes_token['desc'] = __('Tax codes token', CLICTILL_DOMAIN);
		$clictill_settings_taxcodes_token['id'] = 'clictill_settings_taxcodes_token';

		$clictill_settings_manage_categories = array();
		$clictill_settings_manage_categories['name'] = __('Manage categories', CLICTILL_DOMAIN);
		$clictill_settings_manage_categories['type'] = 'checkbox';
		$clictill_settings_manage_categories['desc'] = __('Synchronize category information', CLICTILL_DOMAIN);
		$clictill_settings_manage_categories['id'] = 'clictill_settings_manage_categories';
		
		$clictill_settings_category_token = array();
		$clictill_settings_category_token['name'] = __('Categories token', CLICTILL_DOMAIN);
		$clictill_settings_category_token['type'] = 'text';
		$clictill_settings_category_token['css'] =  $clictill_default_css;
		$clictill_settings_category_token['desc'] = __('Categories token', CLICTILL_DOMAIN);
		$clictill_settings_category_token['id'] = 'clictill_settings_category_token';

		$clictill_settings_manage_suppliers = array();
		$clictill_settings_manage_suppliers['name'] = __('Manage suppliers', CLICTILL_DOMAIN);
		$clictill_settings_manage_suppliers['type'] = 'checkbox';
		$clictill_settings_manage_suppliers['desc'] = __('Synchronize supplier information', CLICTILL_DOMAIN);
		$clictill_settings_manage_suppliers['id'] = 'clictill_settings_manage_suppliers';

		$clictill_settings_suppliers_token = array();
		$clictill_settings_suppliers_token['name'] = __('Suppliers token', CLICTILL_DOMAIN);
		$clictill_settings_suppliers_token['type'] = 'text';
		$clictill_settings_suppliers_token['css'] = $clictill_default_css;
		$clictill_settings_suppliers_token['desc'] = __('Suppliers token', CLICTILL_DOMAIN);
		$clictill_settings_suppliers_token['id'] = 'clictill_settings_suppliers_token';

		$clictill_settings_manage_brands = array();
		$clictill_settings_manage_brands['name'] = __('Manage brands', CLICTILL_DOMAIN);
		$clictill_settings_manage_brands['type'] = 'checkbox';
		$clictill_settings_manage_brands['desc'] = __('Synchronize brand information', CLICTILL_DOMAIN);
		$clictill_settings_manage_brands['id'] = 'clictill_settings_manage_brands';

		$clictill_settings_brands_token = array();
		$clictill_settings_brands_token['name'] = __('Brands token', CLICTILL_DOMAIN);
		$clictill_settings_brands_token['type'] = 'text';
		$clictill_settings_brands_token['css'] = $clictill_default_css;
		$clictill_settings_brands_token['desc'] = __('Brands token', CLICTILL_DOMAIN);
		$clictill_settings_brands_token['id'] = 'clictill_settings_brands_token';
		
		$clictill_clictill_api_section_end = array();
		$clictill_clictill_api_section_end['type'] = 'sectionend';
		$clictill_clictill_api_section_end['id'] = 'clictill_clictill_api_section_end';
		
		$clictill_field_mapping_section = array();
		$clictill_field_mapping_section['name'] = __('Field mapping', CLICTILL_DOMAIN);
		$clictill_field_mapping_section['type'] = 'title';
		$clictill_field_mapping_section['desc'] = __('The following field mapping links some clictill fields to some WooCommerce fields. These fields are only synchronized on the initial run.', CLICTILL_DOMAIN);
		$clictill_field_mapping_section['id'] = 'clictill_field_mapping_section';
		
		$clictill_woocommerce_name_field_mapping_options = array();
		$clictill_woocommerce_name_field_mapping_options['none'] = __('None', CLICTILL_DOMAIN);
		$clictill_woocommerce_name_field_mapping_options['description1'] = __('clictill description 1', CLICTILL_DOMAIN);
		$clictill_woocommerce_name_field_mapping_options['description2'] = __('clictill description 2', CLICTILL_DOMAIN);
		$clictill_woocommerce_name_field_mapping_options['description3'] = __('clictill description 3', CLICTILL_DOMAIN);
		
		$clictill_woocommerce_name_field_mapping = array();
		$clictill_woocommerce_name_field_mapping['name'] = __('WooCommerce product name', CLICTILL_DOMAIN);
		$clictill_woocommerce_name_field_mapping['type'] = 'select';
		$clictill_woocommerce_name_field_mapping['options'] = $clictill_woocommerce_name_field_mapping_options;
		$clictill_woocommerce_name_field_mapping['css'] = $clictill_default_css;
		$clictill_woocommerce_name_field_mapping['desc'] = __('WooCommerce product name', CLICTILL_DOMAIN);
		$clictill_woocommerce_name_field_mapping['id'] = 'clictill_woocommerce_name_field_mapping';
		
		$clictill_woocommerce_description_field_mapping = array();
		$clictill_woocommerce_description_field_mapping['name'] = __('WooCommerce product description', CLICTILL_DOMAIN);
		$clictill_woocommerce_description_field_mapping['type'] = 'select';
		$clictill_woocommerce_description_field_mapping['options'] = $clictill_woocommerce_name_field_mapping_options;
		$clictill_woocommerce_description_field_mapping['css'] = $clictill_default_css;
		$clictill_woocommerce_description_field_mapping['desc'] = __('WooCommerce product description', CLICTILL_DOMAIN);
		$clictill_woocommerce_description_field_mapping['id'] = 'clictill_woocommerce_description_field_mapping';
		
		$clictill_woocommerce_short_description_field_mapping = array();
		$clictill_woocommerce_short_description_field_mapping['name'] = __('WooCommerce product short description', CLICTILL_DOMAIN);
		$clictill_woocommerce_short_description_field_mapping['type'] = 'select';
		$clictill_woocommerce_short_description_field_mapping['options'] = $clictill_woocommerce_name_field_mapping_options;
		$clictill_woocommerce_short_description_field_mapping['css'] = $clictill_default_css;
		$clictill_woocommerce_short_description_field_mapping['desc'] = __('WooCommerce product short description', CLICTILL_DOMAIN);
		$clictill_woocommerce_short_description_field_mapping['id'] = 'clictill_woocommerce_short_description_field_mapping';

		$clictill_field_mapping_section_end = array();
		$clictill_field_mapping_section_end['type'] = 'sectionend';
		$clictill_field_mapping_section_end['id'] = 'clictill_field_mapping_section_end';
		
		if ( get_option( 'clictill_settings_taxcodes_token' ) ) {
			$clictill_vat_mapping_section = array();
			$clictill_vat_mapping_section['name'] = __('VAT mapping', CLICTILL_DOMAIN);
			$clictill_vat_mapping_section['type'] = 'title';
			$clictill_vat_mapping_section['desc'] = __('The following VAT mapping links some clictill fields to some WooCommerce fields. These fields are only synchronized on the initial run.', CLICTILL_DOMAIN);
			$clictill_vat_mapping_section['id'] = 'clictill_vat_mapping_section';
			
		// Get WooCommerce VAT information and create the options
			require_once __DIR__ . '/../classes/woocommerce-taxes.php';
			$clictill_woocommece_taxes = new WoocommerceTaxes;
			$clictill_woocommece_taxes->clictill_read_list_from_woocommerce();
			$clictill_woocommerce_vat_mapping_options[0] = __('None', CLICTILL_DOMAIN);
			foreach ($clictill_woocommece_taxes->response as $woocommerce_tax) {
				$clictill_woocommerce_vat_mapping_options[$woocommerce_tax->id] = sprintf( __('%s (Rate: %s %%)', CLICTILL_DOMAIN), $woocommerce_tax->name, $woocommerce_tax->rate);
			}
			
		// Get ClicTill VAT information and create the loop
			require_once __DIR__ . '/../classes/clictill-taxes.php';
			$clictill_clictill_taxes = new ClictillTaxes;
			$clictill_clictill_taxes->clictill_read_list_from_clictill();
			foreach ($clictill_clictill_taxes->data->response->data as $clictill_tax) {
				$i = $clictill_tax->id_tax_code;
				$clictill_woocommerce_vat_mapping[$i] = array();
				$clictill_woocommerce_vat_mapping[$i]['name'] = sprintf( __('%s (Rate: %s %%)', CLICTILL_DOMAIN), $clictill_tax->tax_code_name, $clictill_tax->rate);
				$clictill_woocommerce_vat_mapping[$i]['type'] = 'select';
				$clictill_woocommerce_vat_mapping[$i]['options'] = $clictill_woocommerce_vat_mapping_options;
				$clictill_woocommerce_vat_mapping[$i]['id'] = 'clictill_woocommerce_vat_mapping_'.$i;
			}
			
			$clictill_vat_mapping_section_end = array();
			$clictill_vat_mapping_section_end['type'] = 'sectionend';
			$clictill_vat_mapping_section_end['id'] = 'clictill_vat_mapping_section_end';
		}

		if (defined( 'CTWC_MANAGE_ORDERS' ) ) {
			$clictill_shipping_mapping_section = array();
			$clictill_shipping_mapping_section['name'] = __('Shipping mapping', CLICTILL_DOMAIN);
			$clictill_shipping_mapping_section['type'] = 'title';
			$clictill_shipping_mapping_section['desc'] = __('The following shipping mapping links some Clictill fields to some WooCommerce fields. These fields are only synchronized on the initial run.', CLICTILL_DOMAIN);
			$clictill_shipping_mapping_section['id'] = 'clictill_shipping_mapping_section';
			
		// Get WooCommerce shipping information and create the options
			$clictill_woocommerce_shipping_mapping_options['none'] = __('None (0 %)', CLICTILL_DOMAIN);
			$clictill_woocommerce_shipping_mapping_options['shipping1'] = __('Shipping 1 (5 %)', CLICTILL_DOMAIN);
			$clictill_woocommerce_shipping_mapping_options['shipping2'] = __('Shipping 2 (20 %)', CLICTILL_DOMAIN);
			$clictill_woocommerce_shipping_mapping_options['shipping3'] = __('Shipping 3 (33 %)', CLICTILL_DOMAIN);

		// Get ClicTill shipping information and create the loop
			$clictill_shipping_methods_list = new WoocommerceShipping;
			$clictill_shipping_methods_list->clictill_read_methods_from_woocommerce();
			$clictill_woocommerce_shipping_method = array();
			$clictill_shipping_method_number = 0;
			foreach ($clictill_shipping_methods_list->response as $clictill_shipping_method) {
				$i = $clictill_shipping_method_number;
				$clictill_woocommerce_shipping_mapping[$i] = array();
				$clictill_woocommerce_shipping_mapping[$i]['name'] = $clictill_shipping_method->title;
				$clictill_woocommerce_shipping_mapping[$i]['type'] = 'select';
				$clictill_woocommerce_shipping_mapping[$i]['options'] = $clictill_woocommerce_shipping_mapping_options;
				$clictill_woocommerce_shipping_mapping[$i]['id'] = 'clictill_woocommerce_shipping_mapping_'.$i;
				$i++;
			}
			$clictill_shipping_method_number = $i;
			
			$clictill_shipping_mapping_section_end = array();
			$clictill_shipping_mapping_section_end['type'] = 'sectionend';
			$clictill_shipping_mapping_section_end['id'] = 'clictill_shipping_mapping_section_end';

		//	Get shipping methods
			$clictill_shipping_methods_list = new WoocommerceShipping;
			$clictill_shipping_methods_list->clictill_read_methods_from_woocommerce();
			$clictill_woocommerce_shipping_method = array();
			$clictill_shipping_method_number = 0;
			foreach ($clictill_shipping_methods_list->response as $clictill_shipping_method) {
				$clictill_woocommerce_shipping_method[$clictill_shipping_method_number] = array();
				$clictill_woocommerce_shipping_method[$clictill_shipping_method_number]['name'] = __('WooCommerce order shipping method', CLICTILL_DOMAIN);
				$clictill_woocommerce_shipping_method[$clictill_shipping_method_number]['type'] = 'select';
				$clictill_woocommerce_shipping_method[$clictill_shipping_method_number]['options'] = array('shop' => __('Shop', CLICTILL_DOMAIN), 'chronopost' => __('Chronopost', CLICTILL_DOMAIN));
				$clictill_woocommerce_shipping_method[$clictill_shipping_method_number]['css'] = $clictill_default_css;
				$clictill_woocommerce_shipping_method[$clictill_shipping_method_number]['desc'] = __('WooCommerce order shipping method', CLICTILL_DOMAIN);
				$clictill_woocommerce_shipping_method[$clictill_shipping_method_number]['id'] = 'clictill_woocommerce_shipping_method_'.$clictill_shipping_method_number;
				$clictill_shipping_method_number++;
			}

			$clictill_woocommerce_trigger = array();
			$clictill_woocommerce_trigger['name'] = __('WooCommerce order status trigger', CLICTILL_DOMAIN);
			$clictill_woocommerce_trigger['type'] = 'select';
			$clictill_woocommerce_trigger['options'] = array('onhold' => __('On hold', CLICTILL_DOMAIN), 'processing' => __('Processing', CLICTILL_DOMAIN), 'completed' => __('Completed', CLICTILL_DOMAIN));
			$clictill_woocommerce_trigger['css'] = $clictill_default_css;
			$clictill_woocommerce_trigger['desc'] = __('WooCommerce order status trigger', CLICTILL_DOMAIN);
			$clictill_woocommerce_trigger['id'] = 'clictill_woocommerce_trigger';
			
			$clictill_clictill_status = array();
			$clictill_clictill_status['name'] = __('Clictill order status', CLICTILL_DOMAIN);
			$clictill_clictill_status['type'] = 'select';
			$clictill_clictill_status['options'] = array();
			$clictill_clictill_status['options']['description_1'] = __('clictill description 1', CLICTILL_DOMAIN);
			$clictill_clictill_status['options']['description_2'] = __('clictill description 2', CLICTILL_DOMAIN);
			$clictill_clictill_status['options']['description_3'] = __('clictill description 3', CLICTILL_DOMAIN);
			$clictill_clictill_status['css'] = $clictill_default_css;
			$clictill_clictill_status['desc'] = __('Clictill order status', CLICTILL_DOMAIN);
			$clictill_clictill_status['id'] = 'clictill_clictill_status';
			
			$clictill_behaviour_section_end = array();
			$clictill_behaviour_section_end['type'] = 'sectionend';
			$clictill_behaviour_section_end['id'] = 'clictill_behaviour_section_end';
		}

		$clictill_woocommerce_products_per_batch_options = array();
		$clictill_woocommerce_products_per_batch_options['none'] =  __('None', CLICTILL_DOMAIN);
		$clictill_woocommerce_products_per_batch_options['10'] = '10';
		$clictill_woocommerce_products_per_batch_options['20'] = '20';
		$clictill_woocommerce_products_per_batch_options['50'] = '50';

		$clictill_woocommerce_products_per_batch = array();
		$clictill_woocommerce_products_per_batch['name'] = __('WooCommerce number of products per batch', CLICTILL_DOMAIN);
		$clictill_woocommerce_products_per_batch['type'] = 'select';
		$clictill_woocommerce_products_per_batch['options'] = $clictill_woocommerce_products_per_batch_options;
		$clictill_woocommerce_products_per_batch['css'] = $clictill_default_css;
		$clictill_woocommerce_products_per_batch['desc'] = __('Reduce this value in case of WooCommerce timeouts', CLICTILL_DOMAIN);
		$clictill_woocommerce_products_per_batch['id'] = 'clictill_woocommerce_products_per_batch';
		
		$clictill_behaviour_section = array();
		$clictill_behaviour_section['name'] = __('Behaviour', CLICTILL_DOMAIN);
		$clictill_behaviour_section['type'] = 'title';
		$clictill_behaviour_section['desc'] = __('The following field mapping links some WooCommerce events to some Clictill statuses.', CLICTILL_DOMAIN);
		$clictill_behaviour_section['id'] = 'clictill_behaviour_section';

		if (defined( 'CTWC_MANAGE_ORDERS' ) ) {
			$settings['users'] = $clictill_user_token_menu;
		// If token
			$settings['user_list'] = $clictill_user_menu;
			$settings['shops'] = $clictill_shop_token_menu;
		// If token
			$settings['shop_list'] = $clictill_shop_menu;
			$settings['price_code'] = $clictill_settings_price_code;
			$settings['clictill_account_section_end'] = $clictill_account_section_end;
		}

		$settings['clictill_api_section'] = $clictill_api_section;
		$settings['client'] = $clictill_settings_client_token;
		$settings['article'] = $clictill_settings_article_token;

		if (defined( 'CTWC_MANAGE_ORDERS' ) ) {
			$settings['manage_stock'] = $clictill_settings_manage_stock;
			$settings['order'] = $clictill_settings_order_token;
			$settings['receipt'] = $clictill_settings_receipt_token;
		}
/*
		$settings['family'] = $clictill_settings_family_token;
	*/
		$settings['taxcodes'] = $clictill_settings_taxcodes_token;
		$settings['manage_categories'] = $clictill_settings_manage_categories;
		$settings['category'] = $clictill_settings_category_token;
		$settings['manage_suppliers'] = $clictill_settings_manage_suppliers;
		$settings['suppliers'] = $clictill_settings_suppliers_token;
		$settings['manage_brands'] = $clictill_settings_manage_brands;
		$settings['brands'] = $clictill_settings_brands_token;
		$settings['clictill_api_section_end'] = $clictill_clictill_api_section_end;

		$settings['clictill_field_mapping_section'] = $clictill_field_mapping_section;
		$settings['clictill_woocommerce_name_field_mapping'] = $clictill_woocommerce_name_field_mapping;
		$settings['clictill_woocommerce_description_field_mapping'] = $clictill_woocommerce_description_field_mapping;
		$settings['clictill_woocommerce_short_description_field_mapping'] = $clictill_woocommerce_short_description_field_mapping;
		$settings['clictill_field_mapping_section_end'] = $clictill_field_mapping_section_end;

		if ( get_option( 'clictill_settings_taxcodes_token' ) ) {
			$settings['clictill_vat_mapping_section'] = $clictill_vat_mapping_section;
			foreach ($clictill_clictill_taxes->data->response->data as $clictill_tax) {
				$i = $clictill_tax->id_tax_code;
				$settings['clictill_woocommerce_vat_mapping_'.$i] = $clictill_woocommerce_vat_mapping[$i];
			}
			$settings['clictill_vat_mapping_section_end'] = $clictill_vat_mapping_section_end;
		}

		if ( defined( 'CTWC_MANAGE_ORDERS' ) ) {
			$settings['clictill_shipping_mapping_section'] = $clictill_shipping_mapping_section;
			for ($i = 0; $i <= $clictill_shipping_method_number; $i++) {
				$settings['clictill_woocommerce_shipping_mapping_'.$i] = $clictill_woocommerce_shipping_mapping[$i];
			}
			$settings['clictill_shipping_mapping_section_end'] = $clictill_shipping_mapping_section_end;

		}
/*
		$settings['clictill_behaviour_section'] = $clictill_behaviour_section;
		$settings['clictill_woocommerce_trigger'] = $clictill_woocommerce_trigger;
		$settings['clictill_clictill_status'] = $clictill_clictill_status;
		$settings['clictill_behaviour_section_end'] = $clictill_behaviour_section_end;
*/
	}
	else {
		echo 'NOK WC';
	}


	return apply_filters( 'clictill_settings', $settings );
}

function clictill_delete_clictill_settings() {
	delete_option( 'clictill_woocommerce_consumer_key' );
	delete_option( 'clictill_woocommerce_consumer_secret' );
	delete_option( 'clictill_settings_users_token' );
	delete_option( 'clictill_settings_user' );
	delete_option( 'clictill_settings_shops_token' );
	delete_option( 'clictill_settings_shop' );
	delete_option( 'clictill_settings_price_code' );
	delete_option( 'clictill_settings_client_token' );
	delete_option( 'clictill_settings_article_token' );
	delete_option( 'clictill_settings_manage_stock' );
	delete_option( 'clictill_settings_order_token' );
	delete_option( 'clictill_settings_receipt_token' );
	delete_option( 'clictill_settings_family_token' );
	delete_option( 'clictill_settings_taxcodes_token' );
	delete_option( 'clictill_settings_manage_categories' );
	delete_option( 'clictill_settings_category_token' );
	delete_option( 'clictill_settings_manage_suppliers' );
	delete_option( 'clictill_settings_suppliers_token' );
	delete_option( 'clictill_settings_manage_brands' );
	delete_option( 'clictill_settings_brands_token' );
	delete_option( 'clictill_woocommerce_name_field_mapping' );
	delete_option( 'clictill_woocommerce_description_field_mapping' );
	delete_option( 'clictill_woocommerce_short_description_field_mapping' );
	delete_option( 'clictill_woocommerce_products_per_batch' );
	delete_option( 'clictill_woocommerce_vat_mapping_options' );
	delete_option( 'clictill_woocommerce_shipping_method' );
	delete_option( 'clictill_woocommerce_trigger' );
	delete_option( 'clictill_clictill_status' );
}