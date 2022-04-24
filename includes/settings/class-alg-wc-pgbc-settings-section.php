<?php
/**
 * Payment Gateway Currency for WooCommerce - Section Settings
 *
 * @version 2.0.0
 * @since   1.0.0
 *
 * @author  Algoritmika Ltd.
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'Alg_WC_PGBC_Settings_Section' ) ) :

class Alg_WC_PGBC_Settings_Section {

	/**
	 * Constructor.
	 *
	 * @version 2.0.0
	 * @since   1.0.0
	 */
	function __construct() {
		add_filter( 'woocommerce_get_sections_alg_wc_pgbc',              array( $this, 'settings_section' ) );
		add_filter( 'woocommerce_get_settings_alg_wc_pgbc_' . $this->id, array( $this, 'get_settings' ), PHP_INT_MAX );
	}

	/**
	 * settings_section.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function settings_section( $sections ) {
		$sections[ $this->id ] = $this->desc;
		return $sections;
	}

	/**
	 * get_paypal_tip.
	 *
	 * @version 1.4.0
	 * @since   1.0.0
	 *
	 * @see     https://woocommerce.github.io/code-reference/classes/WC-Gateway-Paypal.html#method_is_valid_for_use
	 *
	 * @todo    [next] (dev) filter allowed currencies instead of showing `$paypal_tip`
	 */
	function get_paypal_tip( $currencies, $do_add_br = true ) {
		$paypal_allowed_currencies = array( 'AUD', 'BRL', 'CAD', 'MXN', 'NZD', 'HKD', 'SGD', 'USD', 'EUR', 'JPY', 'TRY', 'NOK', 'CZK', 'DKK', 'HUF', 'ILS', 'MYR', 'PHP', 'PLN', 'SEK', 'CHF', 'TWD', 'THB', 'GBP', 'RMB', 'RUB', 'INR' );
		$paypal_allowed_currencies_and_names = array();
		foreach ( $paypal_allowed_currencies as $paypal_allowed_currency ) {
			if ( isset( $currencies[ $paypal_allowed_currency ] ) ) {
				$paypal_allowed_currencies_and_names[] = $currencies[ $paypal_allowed_currency ];
			}
		}
		return ( ! empty( $paypal_allowed_currencies_and_names ) ?
			sprintf( ( $do_add_br ? '<br><br>' : '' ) . __( 'From your available currencies, PayPal allows only these: %s.', 'payment-gateways-by-currency-for-woocommerce' ), '<br>' .
				implode( ', ', $paypal_allowed_currencies_and_names ) ) : '' );
	}

	/**
	 * get_pro_desc.
	 *
	 * @version 1.4.0
	 * @since   1.4.0
	 */
	function get_pro_desc() {
		return sprintf( 'You will need %s plugin to set options for this payment gateway.',
			'<a target="_blank" href="https://wpfactory.com/item/payment-gateways-by-currency-for-woocommerce/">' . 'Payment Gateway Currency for WooCommerce Pro' . '</a>' );
	}

}

endif;
