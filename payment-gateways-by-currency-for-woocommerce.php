<?php
/*
Plugin Name: Payment Gateway Currency for WooCommerce
Plugin URI: https://wpfactory.com/item/payment-gateways-by-currency-for-woocommerce/
Description: Manage currencies for WooCommerce payment gateways. Beautifully.
Version: 3.4.2
Author: WPFactory
Author URI: https://wpfactory.com
Text Domain: payment-gateways-by-currency-for-woocommerce
Domain Path: /langs
WC tested up to: 6.7
*/

defined( 'ABSPATH' ) || exit;

if ( 'payment-gateways-by-currency-for-woocommerce.php' === basename( __FILE__ ) ) {
	/**
	 * Check if Pro plugin version is activated.
	 *
	 * @version 2.1.0
	 * @since   2.1.0
	 */
	$plugin = 'payment-gateways-by-currency-for-woocommerce-pro/payment-gateways-by-currency-for-woocommerce-pro.php';
	if (
		in_array( $plugin, (array) get_option( 'active_plugins', array() ), true ) ||
		( is_multisite() && array_key_exists( $plugin, (array) get_site_option( 'active_sitewide_plugins', array() ) ) )
	) {
		return;
	}
}

defined( 'ALG_WC_PGBC_VERSION' ) || define( 'ALG_WC_PGBC_VERSION', '3.4.2' );

defined( 'ALG_WC_PGBC_FILE' ) || define( 'ALG_WC_PGBC_FILE', __FILE__ );

require_once( 'includes/class-alg-wc-pgbc.php' );

if ( ! function_exists( 'alg_wc_pgbc' ) ) {
	/**
	 * Returns the main instance of Alg_WC_PGBC to prevent the need to use globals.
	 *
	 * @version 2.0.0
	 * @since   1.0.0
	 */
	function alg_wc_pgbc() {
		return Alg_WC_PGBC::instance();
	}
}

add_action( 'plugins_loaded', 'alg_wc_pgbc' );
