<?php
/**
 * Payment Gateway Currency for WooCommerce - Convert - Advanced Section Settings
 *
 * @version 3.4.2
 * @since   3.0.0
 *
 * @author  Algoritmika Ltd.
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Alg_WC_PGBC_Settings_Convert_Advanced' ) ) :

class Alg_WC_PGBC_Settings_Convert_Advanced extends Alg_WC_PGBC_Settings_Section {

	/**
	 * Constructor.
	 *
	 * @version 3.0.0
	 * @since   3.0.0
	 */
	function __construct() {
		$this->id   = 'convert_advanced';
		$this->desc = __( 'Advanced', 'payment-gateways-by-currency-for-woocommerce' );
		parent::__construct();
	}

	/**
	 * get_settings.
	 *
	 * @version 3.4.2
	 * @since   3.0.0
	 *
	 * @todo    [now] [!!!] (dev) `alg_wc_pgbc_convert_currency_angelleye_ppcp`: default to `yes`?
	 * @todo    [now] [!!!] (dev) `alg_wc_pgbc_convert_currency_ppcp`: default to `yes`?
	 * @todo    [now] [!!!] (desc) `alg_wc_pgbc_convert_currency_angelleye_ppcp`: "PayPal Complete Payments"?
	 * @todo    [now] [!!!] (desc) `alg_wc_pgbc_convert_currency_ppcp`
	 * @todo    [now] [!!] (desc) Always show PayFast, etc.: to a separate subsection (e.g. "Compatibility")
	 * @todo    [now] (desc) double conversion + AJAX
	 * @todo    [next] (desc) `alg_wc_pgbc_convert_currency_order_pay_lock_gateway`: better desc
	 * @todo    [later] (desc) `alg_wc_pgbc_convert_currency_paypal_show_always`: better desc?
	 * @todo    [later] (desc) `alg_wc_pgbc_convert_currency_recalculate_cart`: better desc?
	 * @todo    [later] (desc) `alg_wc_pgbc_convert_currency_wc_subscriptions_renewal`: better desc?
	 * @todo    [next] (desc) `alg_wc_pgbc_convert_currency_current_gateway_fallbacks`: better desc?
	 * @todo    [next] (desc) `alg_wc_pgbc_convert_currency_set_session_cookie`: better desc?
	 */
	function get_settings() {
		$settings = array(
			array(
				'title'    => __( 'Convert Currency', 'payment-gateways-by-currency-for-woocommerce' ) . ': ' . __( 'Advanced Options', 'payment-gateways-by-currency-for-woocommerce' ),
				'type'     => 'title',
				'id'       => 'alg_wc_pgbc_convert_currency_advanced_options',
			),
			array(
				'title'    => __( 'Lock gateway on order payment', 'payment-gateways-by-currency-for-woocommerce' ),
				'desc'     => __( 'Enable', 'payment-gateways-by-currency-for-woocommerce' ),
				'desc_tip' => __( 'Locks payment gateway in "My account > Orders > Pay".', 'payment-gateways-by-currency-for-woocommerce' ) . ' ' .
					__( 'I.e. customer won\'t be able to choose gateway different from what he selected on the checkout page.', 'payment-gateways-by-currency-for-woocommerce' ) . ' ' .
					__( 'Otherwise, if customer selects different gateway, converted prices and currency may not match the settings.', 'payment-gateways-by-currency-for-woocommerce' ),
				'id'       => 'alg_wc_pgbc_convert_currency_order_pay_lock_gateway',
				'default'  => 'yes',
				'type'     => 'checkbox',
			),
			array(
				'title'    => __( 'Always show PayPal', 'payment-gateways-by-currency-for-woocommerce' ),
				'desc'     => __( 'Enable', 'payment-gateways-by-currency-for-woocommerce' ),
				'desc_tip' => sprintf( __( 'Show %s gateway for all currencies.', 'payment-gateways-by-currency-for-woocommerce' ),
					'PayPal' ),
				'id'       => 'alg_wc_pgbc_convert_currency_paypal_show_always',
				'default'  => 'yes',
				'type'     => 'checkbox',
			),
			array(
				'title'    => __( 'Always show PayFast', 'payment-gateways-by-currency-for-woocommerce' ),
				'desc'     => __( 'Enable', 'payment-gateways-by-currency-for-woocommerce' ),
				'desc_tip' => sprintf( __( 'Show %s gateway for all currencies.', 'payment-gateways-by-currency-for-woocommerce' ),
					'<a href="https://wordpress.org/plugins/woocommerce-payfast-gateway/" target="_blank">PayFast</a>' ),
				'id'       => 'alg_wc_pgbc_convert_currency_payfast_show_always',
				'default'  => 'yes',
				'type'     => 'checkbox',
			),
			array(
				'title'    => __( 'Recalculate cart', 'payment-gateways-by-currency-for-woocommerce' ),
				'desc'     => __( 'Enable', 'payment-gateways-by-currency-for-woocommerce' ),
				'desc_tip' => __( 'Enable this if you are having issues with wrong amount and currency in mini-cart.', 'payment-gateways-by-currency-for-woocommerce' ),
				'id'       => 'alg_wc_pgbc_convert_currency_recalculate_cart',
				'default'  => 'yes',
				'type'     => 'checkbox',
			),
			array(
				'title'    => __( 'WooCommerce PayPal Checkout Gateway', 'payment-gateways-by-currency-for-woocommerce' ),
				'desc'     => __( 'Enable', 'payment-gateways-by-currency-for-woocommerce' ),
				'desc_tip' => sprintf( __( 'Enables compatibility with the %s plugin, when using e.g. PayPal Checkout buttons on the single product page.', 'payment-gateways-by-currency-for-woocommerce' ),
						'<a target="_blank" href="' . 'https://wordpress.org/plugins/woocommerce-gateway-paypal-express-checkout/' . '">' .
							__( 'WooCommerce PayPal Checkout Gateway', 'payment-gateways-by-currency-for-woocommerce' ) . '</a>' ) . '<br>' .
					sprintf( __( 'Please note that "%s" option in %s section must be enabled as well.', 'payment-gateways-by-currency-for-woocommerce' ),
						__( 'Convert on AJAX', 'payment-gateways-by-currency-for-woocommerce' ),
						'<a href="' . admin_url( 'admin.php?page=wc-settings&tab=alg_wc_pgbc&section=convert_general' ) . '">' .
							__( 'General', 'payment-gateways-by-currency-for-woocommerce' ) . '</a>' ),
				'id'       => 'alg_wc_pgbc_convert_currency_ppec_paypal',
				'default'  => 'yes',
				'type'     => 'checkbox',
			),
			array(
				'title'    => __( 'WooCommerce PayPal Payments', 'payment-gateways-by-currency-for-woocommerce' ),
				'desc'     => __( 'Enable', 'payment-gateways-by-currency-for-woocommerce' ),
				'desc_tip' => sprintf( __( 'Enables compatibility with the %s plugin.', 'payment-gateways-by-currency-for-woocommerce' ),
						'<a target="_blank" href="' . 'https://wordpress.org/plugins/woocommerce-paypal-payments/' . '">' .
							__( 'WooCommerce PayPal Payments', 'payment-gateways-by-currency-for-woocommerce' ) . '</a>' ),
				'id'       => 'alg_wc_pgbc_convert_currency_ppcp',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'title'    => __( 'PayPal for WooCommerce by Angell EYE', 'payment-gateways-by-currency-for-woocommerce' ),
				'desc'     => __( 'Enable', 'payment-gateways-by-currency-for-woocommerce' ),
				'desc_tip' => sprintf( __( 'Enables compatibility with the %s plugin.', 'payment-gateways-by-currency-for-woocommerce' ),
						'<a target="_blank" href="' . 'https://www.angelleye.com/product/woocommerce-paypal-plugin/' . '">' .
							__( 'PayPal for WooCommerce by Angell EYE', 'payment-gateways-by-currency-for-woocommerce' ) . '</a>' ),
				'id'       => 'alg_wc_pgbc_convert_currency_angelleye_ppcp',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'title'    => __( 'WooCommerce Subscriptions', 'payment-gateways-by-currency-for-woocommerce' ),
				'desc'     => __( 'Recalculate renewal orders', 'payment-gateways-by-currency-for-woocommerce' ),
				'desc_tip' => sprintf( __( 'When %s renewal order is created, recalculate it according to the current currency exchange rates.', 'payment-gateways-by-currency-for-woocommerce' ),
						'<a target="_blank" href="https://woocommerce.com/products/woocommerce-subscriptions/">' .
							__( 'WooCommerce Subscriptions', 'payment-gateways-by-currency-for-woocommerce' ) . '</a>' ) . '<br>' .
					__( 'Please note that this will work only if renewal\'s <strong>parent order</strong> was initially converted with our plugin since v2.0.0 (03/06/2021).', 'payment-gateways-by-currency-for-woocommerce' ) . '<br>' .
					__( 'This will remove "Recalculate with new rate" from order meta box.', 'payment-gateways-by-currency-for-woocommerce' ),
				'id'       => 'alg_wc_pgbc_convert_currency_wc_subscriptions_renewal',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'title'    => __( 'Current gateway fallbacks', 'payment-gateways-by-currency-for-woocommerce' ),
				'desc_tip' => __( 'Fallbacks to be used, when it\'s not possible to get current payment gateway from the session.', 'payment-gateways-by-currency-for-woocommerce' ),
				'id'       => 'alg_wc_pgbc_convert_currency_current_gateway_fallbacks',
				'default'  => array( 'payment_method' ),
				'type'     => 'multiselect',
				'class'    => 'chosen_select',
				'options'  => array(
					'payment_method'  => __( 'HTTP request `payment_method` variable', 'payment-gateways-by-currency-for-woocommerce' ),
					'first_available' => __( 'First available payment gateway', 'payment-gateways-by-currency-for-woocommerce' ),
					'last_known'      => __( 'Last known payment gateway', 'payment-gateways-by-currency-for-woocommerce' ),
					'default'         => __( 'Default payment gateway', 'payment-gateways-by-currency-for-woocommerce' ),
				),
			),
			array(
				'title'    => __( 'Force session start', 'payment-gateways-by-currency-for-woocommerce' ),
				'desc_tip' => __( 'Enable this if conversion info is not being saved for the guests (i.e. not logged users).', 'payment-gateways-by-currency-for-woocommerce' ),
				'desc'     => __( 'Enable', 'payment-gateways-by-currency-for-woocommerce' ),
				'id'       => 'alg_wc_pgbc_convert_currency_set_session_cookie',
				'default'  => 'yes',
				'type'     => 'checkbox',
			),
			array(
				'title'    => __( 'Cache prices', 'payment-gateways-by-currency-for-woocommerce' ),
				'desc_tip' => __( 'Enable this if you are experiencing issues with price conversions, e.g. prices are converted twice.', 'payment-gateways-by-currency-for-woocommerce' ),
				'desc'     => __( 'Enable', 'payment-gateways-by-currency-for-woocommerce' ),
				'id'       => 'alg_wc_pgbc_convert_currency_cache_prices',
				'default'  => 'yes',
				'type'     => 'checkbox',
			),
			array(
				'desc'     => __( 'Cache product ID', 'payment-gateways-by-currency-for-woocommerce' ),
				'desc_tip' => sprintf( __( 'Change this, if you are experiencing issues with price conversions, even after enabling the "%s" option.', 'payment-gateways-by-currency-for-woocommerce' ),
					__( 'Cache prices', 'payment-gateways-by-currency-for-woocommerce' ) ),
				'id'       => 'alg_wc_pgbc_convert_currency_cache_product_id',
				'default'  => 'product_id',
				'type'     => 'select',
				'class'    => 'chosen_select',
				'options'  => array(
					'product_id'          => __( 'Product ID', 'payment-gateways-by-currency-for-woocommerce' ),
					'product_id_and_data' => __( 'Product ID and data', 'payment-gateways-by-currency-for-woocommerce' ),
				),
			),
			array(
				'title'    => __( 'Fix RTL currencies', 'payment-gateways-by-currency-for-woocommerce' ),
				'desc_tip' => __( 'Enable this if you are experiencing issues with right-to-left (RTL) currency symbols not displaying correctly.', 'payment-gateways-by-currency-for-woocommerce' ),
				'desc'     => __( 'Enable', 'payment-gateways-by-currency-for-woocommerce' ),
				'id'       => 'alg_wc_pgbc_convert_currency_add_bdi',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'title'    => __( 'Rate step', 'payment-gateways-by-currency-for-woocommerce' ),
				'id'       => 'alg_wc_pgbc_convert_currency_rate_step',
				'default'  => '0.000001',
				'type'     => 'text',
			),
			array(
				'desc'     => __( 'Enter as text', 'payment-gateways-by-currency-for-woocommerce' ),
				'desc_tip' => __( 'Enter rate as text instead of as number.', 'payment-gateways-by-currency-for-woocommerce' ) . ' ' .
					sprintf( __( '"%s" option will be ignored.', 'payment-gateways-by-currency-for-woocommerce' ), __( 'Rate step', 'payment-gateways-by-currency-for-woocommerce' ) ),
				'id'       => 'alg_wc_pgbc_convert_currency_rate_type_text',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'title'    => __( 'Debug', 'payment-gateways-by-currency-for-woocommerce' ),
				'desc'     => __( 'Enable', 'payment-gateways-by-currency-for-woocommerce' ),
				'desc_tip' => sprintf( __( 'This will add %s log to %s.', 'payment-gateways-by-currency-for-woocommerce' ),
					'<code>' . 'alg-wc-payment-gateway-currency' . '</code>',
					'<a href="' . admin_url( 'admin.php?page=wc-status&tab=logs' ) . '">' .
						__( 'WooCommerce > Status > Logs', 'payment-gateways-by-currency-for-woocommerce' ) . '</a>' ),
				'id'       => 'alg_wc_pgbc_convert_currency_debug',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'type'     => 'sectionend',
				'id'       => 'alg_wc_pgbc_convert_currency_advanced_options',
			),
		);
		return $settings;
	}

}

endif;

return new Alg_WC_PGBC_Settings_Convert_Advanced();
