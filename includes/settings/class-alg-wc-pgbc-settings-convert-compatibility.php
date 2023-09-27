<?php
/**
 * Payment Gateway Currency for WooCommerce - Convert - Compatibility Section Settings
 *
 * @version 3.8.0
 * @since   3.8.0
 *
 * @author  Algoritmika Ltd.
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Alg_WC_PGBC_Settings_Convert_Compatibility' ) ) :

class Alg_WC_PGBC_Settings_Convert_Compatibility extends Alg_WC_PGBC_Settings_Section {

	/**
	 * Constructor.
	 *
	 * @version 3.8.0
	 * @since   3.8.0
	 */
	function __construct() {
		$this->id   = 'convert_compatibility';
		$this->desc = __( 'Compatibility', 'payment-gateways-by-currency-for-woocommerce' );
		parent::__construct();
	}

	/**
	 * get_settings.
	 *
	 * @version 3.8.0
	 * @since   3.8.0
	 *
	 * @todo    (dev) [!] `alg_wc_pgbc_convert_currency_yith_product_add_ons`: better solution for "Recommended settings: ..."
	 * @todo    (dev) `alg_wc_pgbc_convert_currency_yith_product_add_ons`: default to `yes`?
	 * @todo    (dev) `alg_wc_pgbc_convert_currency_yith_account_funds`: default to `yes`?
	 * @todo    (dev) `alg_wc_pgbc_convert_currency_angelleye_ppcp`: default to `yes`?
	 * @todo    (dev) `alg_wc_pgbc_convert_currency_ppcp`: default to `yes`?
	 * @todo    (desc) `alg_wc_pgbc_convert_currency_angelleye_ppcp`: "PayPal Complete Payments"?
	 * @todo    (desc) `alg_wc_pgbc_convert_currency_ppcp`
	 * @todo    (desc) `alg_wc_pgbc_convert_currency_paypal_show_always`: better desc?
	 * @todo    (desc) `alg_wc_pgbc_convert_currency_wc_subscriptions_renewal`: better desc?
	 */
	function get_settings() {
		return array(
			array(
				'title'    => __( 'Convert Currency', 'payment-gateways-by-currency-for-woocommerce' ) . ': ' . __( 'Compatibility Options', 'payment-gateways-by-currency-for-woocommerce' ),
				'type'     => 'title',
				'id'       => 'alg_wc_pgbc_convert_currency_compatibility_options',
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
				'title'    => __( 'WooCommerce PayPal Checkout Gateway', 'payment-gateways-by-currency-for-woocommerce' ),
				'desc'     => __( 'Enable', 'payment-gateways-by-currency-for-woocommerce' ),
				'desc_tip' => sprintf( __( 'Enables compatibility with the %s plugin, when using e.g., PayPal Checkout buttons on the single product page.', 'payment-gateways-by-currency-for-woocommerce' ),
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
				'title'    => __( 'YITH WooCommerce Account Funds Premium', 'payment-gateways-by-currency-for-woocommerce' ),
				'desc'     => __( 'Enable', 'payment-gateways-by-currency-for-woocommerce' ),
				'desc_tip' => sprintf( __( 'Enables compatibility with the %s plugin.', 'payment-gateways-by-currency-for-woocommerce' ),
						'<a target="_blank" href="' . 'https://yithemes.com/themes/plugins/yith-woocommerce-account-funds/' . '">' .
							__( 'YITH WooCommerce Account Funds Premium', 'payment-gateways-by-currency-for-woocommerce' ) . '</a>' ),
				'id'       => 'alg_wc_pgbc_convert_currency_yith_account_funds',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'title'    => __( 'YITH WooCommerce Product Add-Ons', 'payment-gateways-by-currency-for-woocommerce' ),
				'desc'     => __( 'Enable', 'payment-gateways-by-currency-for-woocommerce' ),
				'desc_tip' => sprintf( __( 'Enables compatibility with the %s plugin.', 'payment-gateways-by-currency-for-woocommerce' ),
						'<a target="_blank" href="' . 'https://wordpress.org/plugins/yith-woocommerce-product-add-ons/' . '">' .
							__( 'YITH WooCommerce Product Add-Ons', 'payment-gateways-by-currency-for-woocommerce' ) . '</a>' ) . '<br>' .
					sprintf( __( 'Recommended additional settings:<br>%s', 'payment-gateways-by-currency-for-woocommerce' ),
						'* ' . sprintf( __( 'Disable the "%s" option in the %s section.', 'payment-gateways-by-currency-for-woocommerce' ),
							__( 'Convert on AJAX', 'payment-gateways-by-currency-for-woocommerce' ),
							'<a href="' . admin_url( 'admin.php?page=wc-settings&tab=alg_wc_pgbc&section=convert_general' ) . '">' .
								__( 'General', 'payment-gateways-by-currency-for-woocommerce' ) . '</a>' ) . '<br>' .
						'* ' . sprintf( __( 'Set the "%s" option in the %s section to "%s".', 'payment-gateways-by-currency-for-woocommerce' ),
							__( 'Cache prices', 'payment-gateways-by-currency-for-woocommerce' ),
							'<a href="' . admin_url( 'admin.php?page=wc-settings&tab=alg_wc_pgbc&section=convert_advanced' ) . '">' .
								__( 'Advanced', 'payment-gateways-by-currency-for-woocommerce' ) . '</a>',
							__( 'Product ID and product changes', 'payment-gateways-by-currency-for-woocommerce' ) ) ),
				'id'       => 'alg_wc_pgbc_convert_currency_yith_product_add_ons',
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
				'type'     => 'sectionend',
				'id'       => 'alg_wc_pgbc_convert_currency_compatibility_options',
			),
		);
	}

}

endif;

return new Alg_WC_PGBC_Settings_Convert_Compatibility();
