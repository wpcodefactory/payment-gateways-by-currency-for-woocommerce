<?php
/**
 * Payment Gateway Currency for WooCommerce - Restrict Section Settings
 *
 * @version 3.4.1
 * @since   1.0.0
 *
 * @author  Algoritmika Ltd.
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'Alg_WC_PGBC_Settings_Restrict' ) ) :

class Alg_WC_PGBC_Settings_Restrict extends Alg_WC_PGBC_Settings_Section {

	/**
	 * Constructor.
	 *
	 * @version 2.0.0
	 * @since   1.0.0
	 */
	function __construct() {
		$this->id   = 'restrict';
		$this->desc = __( 'Restrict Currency', 'payment-gateways-by-currency-for-woocommerce' );
		parent::__construct();
	}

	/**
	 * get_settings.
	 *
	 * @version 3.4.1
	 * @since   1.0.0
	 *
	 * @todo    [maybe] (dev) rename mislabeled options?
	 */
	function get_settings() {

		$main_settings = array(
			array(
				'title'    => __( 'Restrict Currency Options', 'payment-gateways-by-currency-for-woocommerce' ),
				'type'     => 'title',
				'id'       => 'alg_wc_pgbc_restrict_currency_options',
			),
			array(
				'title'    => __( 'Restrict currency', 'payment-gateways-by-currency-for-woocommerce' ),
				'desc'     => '<strong>' . __( 'Enable section', 'payment-gateways-by-currency-for-woocommerce' ) . '</strong>',
				'desc_tip' => __( 'Set allowed currencies for payment gateways to show up.', 'payment-gateways-by-currency-for-woocommerce' ),
				'id'       => 'alg_wc_payment_gateways_by_currency_plugin_enabled', // mislabeled, should be `alg_wc_pgbc_restrict_currency_enabled`
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'type'     => 'sectionend',
				'id'       => 'alg_wc_pgbc_restrict_currency_options',
			),
		);

		$currencies        = get_woocommerce_currencies();
		$gateways          = WC()->payment_gateways->payment_gateways();
		$paypal_tip        = $this->get_paypal_tip( $currencies );
		$gateways_settings = array();
		foreach ( $gateways as $key => $gateway ) {
			$gateways_settings = array_merge( $gateways_settings, array(
				array(
					'title'    => ( ! empty( $gateway->method_title ) ? $gateway->method_title : ( ! empty( $gateway->title ) ? $gateway->title : $key ) ),
					'desc'     => ( ! in_array( $key, array( 'bacs', 'cheque', 'paypal', 'cod', 'ppcp-gateway' ) ) ? apply_filters( 'alg_wc_pgbc_settings', $this->get_pro_desc() ) : '' ),
					'type'     => 'title',
					'id'       => 'alg_wc_pgbc_restrict_currency_' . $key,
				),
				array(
					'title'    => __( 'Allowed currencies', 'payment-gateways-by-currency-for-woocommerce' ),
					'desc_tip' => __( 'Payment gateway will be available ONLY for selected currencies.', 'payment-gateways-by-currency-for-woocommerce' ) . ' ' .
						__( 'If set empty - option is ignored.', 'payment-gateways-by-currency-for-woocommerce' ) . ( 'paypal' == $key ? $paypal_tip : '' ),
					'id'       => "alg_wc_payment_gateways_by_currency_incl[{$key}]", // mislabeled, should be `alg_wc_pgbc_restrict_currency_incl`
					'default'  => array(),
					'type'     => 'multiselect',
					'class'    => 'chosen_select',
					'css'      => 'width:100%',
					'options'  => $currencies,
					'custom_attributes' => array_merge( array( 'data-placeholder' => __( 'Select currencies...', 'payment-gateways-by-currency-for-woocommerce' ) ),
						( ! in_array( $key, array( 'bacs', 'cheque', 'paypal', 'cod', 'ppcp-gateway' ) ) ? apply_filters( 'alg_wc_pgbc_settings', array( 'disabled' => 'disabled' ), 'array' ) : array() ) ),
				),
				array(
					'title'    => __( 'Denied currencies', 'payment-gateways-by-currency-for-woocommerce' ),
					'desc_tip' => __( 'Payment gateway will be NOT available for selected currencies.', 'payment-gateways-by-currency-for-woocommerce' ) . ' ' .
						__( 'If set empty - option is ignored.', 'payment-gateways-by-currency-for-woocommerce' ),
					'id'       => "alg_wc_payment_gateways_by_currency_excl[{$key}]", // mislabeled, should be `alg_wc_pgbc_restrict_currency_excl`
					'default'  => array(),
					'type'     => 'multiselect',
					'class'    => 'chosen_select',
					'css'      => 'width:100%',
					'options'  => $currencies,
					'custom_attributes' => array_merge( array( 'data-placeholder' => __( 'Select currencies...', 'payment-gateways-by-currency-for-woocommerce' ) ),
						( ! in_array( $key, array( 'bacs', 'cheque', 'paypal', 'cod', 'ppcp-gateway' ) ) ? apply_filters( 'alg_wc_pgbc_settings', array( 'disabled' => 'disabled' ), 'array' ) : array() ) ),
				),
				array(
					'type'     => 'sectionend',
					'id'       => 'alg_wc_pgbc_restrict_currency_' . $key,
				),
			) );
		}

		return array_merge( $main_settings, $gateways_settings );
	}

}

endif;

return new Alg_WC_PGBC_Settings_Restrict();
