=== Clictill ===
Contributors: carlconrad, lewebtranquille
Tags: clictill, ecommerce, pos, cart, e-commerce, inventory, point-of-sale, woocommerce
Requires at least: 4.6
Tested up to: 5.3
Requires PHP: 5.6
Stable tag: 0.5
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
WC requires at least: 3.0
WC tested up to: 3.9

Integration of Clictill for WooCommerce

== Description ==

This plug-in is designed to help you link your Clictill to your WooCommerce based web shop.

The Clictill integration for WooCommerce enables your WooCommerce store and Clictill Point of Sale account to communicate and keep inventory in sync.

[Pro only] When a new sale comes in via your Clictill Point of Sale system, stock quantities are updated within WooCommerce. When a new order arrives via WooCommerce, your Clictill Retail account reflects the same inventory numbers.

This plug-in requires MySQL version 5.6 or greater or MariaDB version 10.0 or greater.

Currently, Clictill only addresses the French market.

== Installation ==

<h4>Plug-in installation</h4>
1. Upload the plugin files to the `/wp-content/plugins/ct4woo` directory, or install the plugin through the WordPress plugins screen directly.
1. Activate the plugin through the 'Plugins' screen in WordPress.
1. Use the WooCommerce->Settings->Clictill screen to configure the plugin.

<h4>Product set up</h4>
1. Once connected to your credentials set, the plug-in will start syncing your products from Clictill to WooCommerce. New products are set with the pending status for review before publishing.

<h4>Managing the database</h4>
Desactivating the plug-in only stops the cron task.
Uninstalling the plug-in removes all products and settings from the database.

== Frequently Asked Questions ==

= Do I need a Clictill license? =

Yes. Use of this plug-in requires an active Clictill account.

= Do I need a running copy of WooCommerce? =

Yes. This plug-in allows for linking your Clictill based business to your existing WooCommerce installation.

== Screenshots ==

== Changelog ==

= 0.5 =
* First public version.

= 0.3 =
* Added tax management
* Added shipping method based triggers for synchronization

= 0.3 =
* Improved installation procedure
* Switched to InnoDB tables

= 0.2 =
* Added support for categories.
* Added field mapping.
* Added support for older database engines.
* Added logging to the WooCommerce logs.
* Added activation and deactivation hooks.

= 0.1 =
* First version.

== Upgrade Notice ==

= 0.5 =
First public version.
