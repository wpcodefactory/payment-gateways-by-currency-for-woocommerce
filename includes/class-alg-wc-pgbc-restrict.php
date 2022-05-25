<?php
/**
 * Payment Gateway Currency for WooCommerce - Restrict
 *
 * @version 3.4.1
 * @since   2.0.0
 *
 * @author  Algoritmika Ltd.
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'Alg_WC_PGBC_Restrict' ) ) :

class Alg_WC_PGBC_Restrict {

	/**
	 * Constructor.
	 *
	 * @version 2.0.0
	 * @since   2.0.0
	 */
	function __construct() {
		if ( 'yes' === get_option( 'alg_wc_payment_gateways_by_currency_plugin_enabled', 'no' ) ) {
			add_filter( 'woocommerce_available_payment_gateways', array( $this, 'restrict_payment_gateways' ), PHP_INT_MAX );
		}
	}

	/**
	 * restrict_payment_gateways.
	 *
	 * @version 3.4.1
	 * @since   1.0.0
	 */
	function restrict_payment_gateways( $available_gateways ) {
		$current_currency = get_woocommerce_currency();
		$incl_currencies  = get_option( 'alg_wc_payment_gateways_by_currency_incl', array() );
		$excl_currencies  = get_option( 'alg_wc_payment_gateways_by_currency_excl', array() );
		foreach ( $available_gateways as $key => $gateway ) {
			if ( ! apply_filters( 'alg_wc_pgbc_pre_check', ( in_array( $key, array( 'bacs', 'cheque', 'paypal', 'cod', 'ppcp-gateway' ) ) ) ) ) {
				continue;
			}
			if (
				( ! empty( $incl_currencies[ $key ] ) && ! in_array( $current_currency, $incl_currencies[ $key ] ) ) ||
				( ! empty( $excl_currencies[ $key ] ) &&   in_array( $current_currency, $excl_currencies[ $key ] ) )
			) {
				unset( $available_gateways[ $key ] );
			}
		}
		return $available_gateways;
	}

}

endif;

return new Alg_WC_PGBC_Restrict();
