<?php
/**
 * Payment Gateway Currency for WooCommerce - Convert Section Settings
 *
 * @version 3.4.1
 * @since   1.4.0
 *
 * @author  Algoritmika Ltd.
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Alg_WC_PGBC_Settings_Convert' ) ) :

class Alg_WC_PGBC_Settings_Convert extends Alg_WC_PGBC_Settings_Section {

	/**
	 * Constructor.
	 *
	 * @version 2.0.0
	 * @since   1.4.0
	 */
	function __construct() {
		$this->id   = '';
		$this->desc = __( 'Convert Currency', 'payment-gateways-by-currency-for-woocommerce' );
		parent::__construct();
	}

	/**
	 * get_settings.
	 *
	 * @version 3.4.1
	 * @since   1.4.0
	 *
	 * @todo    [now] (desc) Currency symbol: "... this is optional..."
	 * @todo    [later] (feature) Currency symbol: make optional (i.e. add "Set currency symbol" checkbox)?
	 * @todo    [later] (desc) `alg_wc_pgbc_convert_currency_enabled`: better desc?
	 * @todo    [maybe] (dev) merge with `Alg_WC_PGBC_Settings_Restrict::get_settings()`?
	 */
	function get_settings() {

		if (
			'' !== get_option( 'alg_wc_pgbc_convert_currency_auto_rates_plugin', '' ) ||
			'yes' === get_option( 'alg_wc_pgbc_convert_currency_rate_type_text', 'no' )
		) {
			alg_wc_pgbc()->core->convert->rates->get_gateway_rates( true, true );
		}

		$main_settings = array(
			array(
				'title'    => __( 'Convert Currency Options', 'payment-gateways-by-currency-for-woocommerce' ),
				'type'     => 'title',
				'id'       => 'alg_wc_pgbc_convert_currency_options',
			),
			array(
				'title'    => __( 'Convert currency', 'payment-gateways-by-currency-for-woocommerce' ),
				'desc'     => '<strong>' . __( 'Enable section', 'payment-gateways-by-currency-for-woocommerce' ) . '</strong>',
				'desc_tip' => __( 'Convert cart currencies and prices by the currency exchange rates.', 'payment-gateways-by-currency-for-woocommerce' ),
				'id'       => 'alg_wc_pgbc_convert_currency_enabled',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'type'     => 'sectionend',
				'id'       => 'alg_wc_pgbc_convert_currency_options',
			),
		);

		$currencies         = get_woocommerce_currencies();
		$paypal_tip         = $this->get_paypal_tip( $currencies, false );
		$gateways           = WC()->payment_gateways->payment_gateways();
		$gateways_settings  = array();
		alg_wc_pgbc()->core->convert->get_gateway_currencies( true );
		foreach ( $gateways as $key => $gateway ) {
			$gateways_currency = alg_wc_pgbc()->core->convert->get_gateway_currency( $key );
			$gateways_settings = array_merge( $gateways_settings, array(
				array(
					'title'    => ( ! empty( $gateway->method_title ) ? $gateway->method_title : ( ! empty( $gateway->title ) ? $gateway->title : $key ) ),
					'desc'     => ( ! in_array( $key, array( 'bacs', 'cheque', 'paypal', 'cod', 'ppcp-gateway' ) ) ? apply_filters( 'alg_wc_pgbc_settings', $this->get_pro_desc() ) : '' ),
					'type'     => 'title',
					'id'       => 'alg_wc_pgbc_convert_currency_' . $key,
				),
				array(
					'title'    => __( 'Convert currency', 'payment-gateways-by-currency-for-woocommerce' ),
					'desc_tip' => ( 'paypal' == $key ? $paypal_tip : '' ),
					'id'       => "alg_wc_pgbc_convert_currency[{$key}]",
					'default'  => '',
					'type'     => 'select',
					'class'    => 'chosen_select',
					'options'  => array_merge( array( '' => __( 'No changes', 'payment-gateways-by-currency-for-woocommerce' ) ), $currencies ),
					'custom_attributes' => ( ! in_array( $key, array( 'bacs', 'cheque', 'paypal', 'cod', 'ppcp-gateway' ) ) ?
						apply_filters( 'alg_wc_pgbc_settings', array( 'disabled' => 'disabled' ) ) : '' ),
				),
				array(
					'title'    => __( 'Conversion rate', 'payment-gateways-by-currency-for-woocommerce' ),
					'desc_tip' => ( false !== $gateways_currency ? get_option( 'woocommerce_currency' ) . $gateways_currency : '' ),
					'id'       => "alg_wc_pgbc_convert_rate[{$key}]",
					'default'  => '',
					'type'     => ( 'no' === get_option( 'alg_wc_pgbc_convert_currency_rate_type_text', 'no' ) ? 'number' : 'text' ),
					'custom_attributes' => ( ! in_array( $key, array( 'bacs', 'cheque', 'paypal', 'cod', 'ppcp-gateway' ) ) ?
						apply_filters( 'alg_wc_pgbc_settings', array( 'readonly' => 'readonly' ), 'price' ) : alg_wc_pgbc()->core->convert->get_rate_step_attribute() ),
				),
				array(
					'title'    => __( 'Currency symbol', 'payment-gateways-by-currency-for-woocommerce' ),
					'desc_tip' => ( false !== $gateways_currency ?
						sprintf( __( 'Leave empty for the default symbol (%s).', 'payment-gateways-by-currency-for-woocommerce' ),
							get_woocommerce_currency_symbol( $gateways_currency ) ) : '' ),
					'id'       => "alg_wc_pgbc_convert_symbol[{$key}]",
					'default'  => '',
					'type'     => 'text',
					'custom_attributes' => ( ! in_array( $key, array( 'bacs', 'cheque', 'paypal', 'cod', 'ppcp-gateway' ) ) ?
						apply_filters( 'alg_wc_pgbc_settings', array( 'readonly' => 'readonly' ) ) : '' ),
				),
				array(
					'type'     => 'sectionend',
					'id'       => 'alg_wc_pgbc_convert_currency_' . $key,
				),
			) );
		}

		return array_merge( $main_settings, $gateways_settings );
	}

}

endif;

return new Alg_WC_PGBC_Settings_Convert();
