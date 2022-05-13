<?php
/**
 * Payment Gateway Currency for WooCommerce - Convert - General Section Settings
 *
 * @version 3.4.0
 * @since   2.0.0
 *
 * @author  Algoritmika Ltd.
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'Alg_WC_PGBC_Settings_Convert_General' ) ) :

class Alg_WC_PGBC_Settings_Convert_General extends Alg_WC_PGBC_Settings_Section {

	/**
	 * Constructor.
	 *
	 * @version 3.0.0
	 * @since   2.0.0
	 *
	 * @todo    [next] (dev) split into "General" and "Automatic Rates" sections?
	 */
	function __construct() {
		$this->id   = 'convert_general';
		$this->desc = __( 'General', 'payment-gateways-by-currency-for-woocommerce' );
		parent::__construct();
	}

	/**
	 * get_settings.
	 *
	 * @version 3.4.0
	 * @since   2.0.0
	 *
	 * @todo    [now] [!!] (desc) `alg_wc_pgbc_convert_currency_auto_rates_now`: remove `title`?
	 * @todo    [now] (desc) `alg_wc_pgbc_convert_currency_rate_multiplier`
	 * @todo    [later] (dev) `alg_wc_pgbc_convert_currency_auto_rates_server_keys`: remove "Save/Update password" browser box (`autocomplete`?)
	 * @todo    [later] (desc) `alg_wc_pgbc_convert_currency_auto_rates_options`: better desc?
	 * @todo    [later] (desc) `alg_wc_pgbc_convert_currency_auto_rates_server`: better desc?
	 * @todo    [later] (desc) `alg_wc_pgbc_convert_currency_on_checkout`: better desc?
	 * @todo    [maybe] (dev) rename `alg_wc_pgbc_convert_currency_advanced` to `alg_wc_pgbc_convert_currency_conversion_options`?
	 * @todo    [maybe] (desc) `alg_wc_pgbc_convert_currency_on_checkout`: `checkout_only`: add description, e.g. "May cause issues with mini-cart..." (when Info + "Recalculate cart" disabled?)?
	 */
	function get_settings() {

		$general_settings = array(
			array(
				'title'    => __( 'Convert Currency', 'payment-gateways-by-currency-for-woocommerce' ) . ': ' . __( 'General Options', 'payment-gateways-by-currency-for-woocommerce' ),
				'type'     => 'title',
				'id'       => 'alg_wc_pgbc_convert_currency_general_options',
			),
			array(
				'title'    => __( 'Convert', 'payment-gateways-by-currency-for-woocommerce' ),
				'desc'     => __( 'Shipping price', 'payment-gateways-by-currency-for-woocommerce' ),
				'id'       => 'alg_wc_pgbc_convert_currency_advanced[shipping]',
				'default'  => 'yes',
				'type'     => 'checkbox',
				'checkboxgroup' => 'start',
			),
			array(
				'desc'     => __( 'Free shipping min amount', 'payment-gateways-by-currency-for-woocommerce' ),
				'id'       => 'alg_wc_pgbc_convert_currency_advanced[shipping_free_min_amount]',
				'default'  => 'yes',
				'type'     => 'checkbox',
				'checkboxgroup' => '',
			),
			array(
				'desc'     => __( 'Coupon amounts ("Fixed cart/product discount" coupons only)', 'payment-gateways-by-currency-for-woocommerce' ),
				'id'       => 'alg_wc_pgbc_convert_currency_advanced[coupon]',
				'default'  => 'yes',
				'type'     => 'checkbox',
				'checkboxgroup' => '',
			),
			array(
				'desc'     => __( 'Coupon minimum spend', 'payment-gateways-by-currency-for-woocommerce' ),
				'id'       => 'alg_wc_pgbc_convert_currency_advanced[coupon_min_amount]',
				'default'  => 'yes',
				'type'     => 'checkbox',
				'checkboxgroup' => '',
			),
			array(
				'desc'     => __( 'Coupon maximum spend', 'payment-gateways-by-currency-for-woocommerce' ),
				'id'       => 'alg_wc_pgbc_convert_currency_advanced[coupon_max_amount]',
				'default'  => 'yes',
				'type'     => 'checkbox',
				'checkboxgroup' => '',
			),
			array(
				'desc'     => __( 'Cart fees', 'payment-gateways-by-currency-for-woocommerce' ),
				'id'       => 'alg_wc_pgbc_convert_currency_advanced[cart_fee]',
				'default'  => 'yes',
				'type'     => 'checkbox',
				'checkboxgroup' => 'end',
			),
			array(
				'title'    => __( 'Convert on', 'payment-gateways-by-currency-for-woocommerce' ),
				'id'       => 'alg_wc_pgbc_convert_currency_on_checkout', // mislabelled, should be e.g. `alg_wc_pgbc_convert_currency_scope`
				'default'  => 'yes',
				'type'     => 'select',
				'class'    => 'chosen_select',
				'options'  => array(
					'yes'           => __( 'Cart and checkout', 'payment-gateways-by-currency-for-woocommerce' ),
					'checkout_only' => __( 'Checkout only', 'payment-gateways-by-currency-for-woocommerce' ),
					'no'            => __( 'After checkout (i.e. on "thank you" page and in final order)', 'payment-gateways-by-currency-for-woocommerce' ),
				),
			),
			array(
				'desc'     => __( 'Convert on AJAX', 'payment-gateways-by-currency-for-woocommerce' ),
				'desc_tip' => __( 'This will convert prices or currencies on AJAX e.g. in mini-cart.', 'payment-gateways-by-currency-for-woocommerce' ),
				'id'       => 'alg_wc_pgbc_convert_currency_on_ajax',
				'default'  => 'yes',
				'type'     => 'checkbox',
			),
		);
		if ( function_exists( 'icl_get_languages' ) ) {
			$general_settings = array_merge( $general_settings, array(
				array(
					'desc'     => __( 'Convert on languages', 'payment-gateways-by-currency-for-woocommerce' ),
					'desc_tip' => __( 'This will convert prices or currencies on selected WPML/Polylang languages only.', 'payment-gateways-by-currency-for-woocommerce' ) . ' ' .
						__( 'Leave empty to convert on all languages.', 'payment-gateways-by-currency-for-woocommerce' ),
					'id'       => 'alg_wc_pgbc_convert_currency_on_wpml',
					'default'  => array(),
					'type'     => 'multiselect',
					'class'    => 'chosen_select',
					'options'  => wp_list_pluck( icl_get_languages(), 'native_name' ),
				),
			) );
		}
		$general_settings = array_merge( $general_settings, array(
			array(
				'type'     => 'sectionend',
				'id'       => 'alg_wc_pgbc_convert_currency_general_options',
			),
		) );

		$next_scheduled      = ( alg_wc_pgbc()->core->convert->rates->is_server_rates() ?
			as_next_scheduled_action( alg_wc_pgbc()->core->convert->rates->action, array( get_option( 'alg_wc_pgbc_convert_currency_auto_rates_interval', HOUR_IN_SECONDS ) ) ) : false );
		$server              = get_option( 'alg_wc_pgbc_convert_currency_auto_rates_server', 'ecb' );
		$server_key_constant = 'ALG_WC_PGBC_API_KEY_' . strtoupper( $server );
		$servers             = array(
			'ecb'   => __( 'European Central Bank (ECB)', 'payment-gateways-by-currency-for-woocommerce' ),
			'fixer' => __( 'Fixer.io', 'payment-gateways-by-currency-for-woocommerce' ),
		);
		$server_links        = array(
			'fixer' => 'https://fixer.io/',
		);
		$auto_rates_settings = array(
			array(
				'title'    => __( 'Automatic Currency Exchange Rates Options', 'payment-gateways-by-currency-for-woocommerce' ),
				'desc'     => __( 'This section allows you to automatically update currency exchange rates from the selected server or plugin.', 'payment-gateways-by-currency-for-woocommerce' ),
				'type'     => 'title',
				'id'       => 'alg_wc_pgbc_convert_currency_auto_rates_options',
			),
			array(
				'title'    => __( 'Get from plugin', 'payment-gateways-by-currency-for-woocommerce' ),
				'desc_tip' => sprintf( __( 'This will override the "%s" option.', 'payment-gateways-by-currency-for-woocommerce' ),
					__( 'Update periodically from server', 'payment-gateways-by-currency-for-woocommerce' ) ),
				'id'       => 'alg_wc_pgbc_convert_currency_auto_rates_plugin',
				'default'  => '',
				'type'     => 'select',
				'class'    => 'chosen_select',
				'options'  => array(
					''                 => __( 'Disabled', 'payment-gateways-by-currency-for-woocommerce' ),
					'woocommerce_wpml' => __( 'WooCommerce Multilingual (WPML) > Multi-currency', 'payment-gateways-by-currency-for-woocommerce' ) .
						( function_exists( 'wcml_is_multi_currency_on' ) && wcml_is_multi_currency_on() ? '' : ' [' . __( 'inactive', 'payment-gateways-by-currency-for-woocommerce' ) . ']' ),
				),
			),
			array(
				'title'    => __( 'Update periodically from server', 'payment-gateways-by-currency-for-woocommerce' ),
				'desc_tip' => sprintf( __( 'This will be ignored if "%s" option is enabled.', 'payment-gateways-by-currency-for-woocommerce' ),
					__( 'Get from plugin', 'payment-gateways-by-currency-for-woocommerce' ) ),
				'desc'     => ( $next_scheduled ? sprintf( __( 'Next update is <a href="%s" target="_blank" title="%s">scheduled</a> on %s.', 'payment-gateways-by-currency-for-woocommerce' ),
					admin_url( 'admin.php?page=wc-status&tab=action-scheduler' ),
					sprintf( __( 'Action name: %s', 'payment-gateways-by-currency-for-woocommerce' ), alg_wc_pgbc()->core->convert->rates->action ),
					'<code>' . date_i18n( 'Y-m-d H:i:s', $next_scheduled + ( int ) ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS ) ) . '</code>' ) : '' ),
				'id'       => 'alg_wc_pgbc_convert_currency_auto_rates_cron',
				'default'  => '',
				'type'     => 'select',
				'class'    => 'chosen_select',
				'options'  => array(
					''           => __( 'Disabled', 'payment-gateways-by-currency-for-woocommerce' ),
					'hourly'     => __( 'Enabled', 'payment-gateways-by-currency-for-woocommerce' ), // mislabelled, should be `yes`
				),
			),
			array(
				'desc'     => __( 'Interval (in seconds)', 'payment-gateways-by-currency-for-woocommerce' ),
				'id'       => 'alg_wc_pgbc_convert_currency_auto_rates_interval',
				'default'  => HOUR_IN_SECONDS,
				'type'     => 'number',
				'custom_attributes' => array( 'min' => 60 ),
			),
			array(
				'desc'     => __( 'Server', 'payment-gateways-by-currency-for-woocommerce' ) . ( ! defined( $server_key_constant ) ? '' :
					'<br>' . sprintf( __( 'You have defined your key as %s constant in %s file.', 'payment-gateways-by-currency-for-woocommerce' ),
						'<code>' . $server_key_constant . '</code>', '<code>' . 'wp-config.php' . '</code>' ) ),
				'id'       => 'alg_wc_pgbc_convert_currency_auto_rates_server',
				'default'  => 'ecb',
				'type'     => 'select',
				'class'    => 'chosen_select',
				'options'  => $servers,
			),
		);
		if ( in_array( $server, array( 'fixer' ) ) && ! defined( $server_key_constant ) ) {
			$auto_rates_settings = array_merge( $auto_rates_settings, array(
				array(
					'desc'     => __( 'Server key', 'payment-gateways-by-currency-for-woocommerce' ) .
						' [' . ( isset( $servers[ $server ] ) ? $servers[ $server ] : $server ) . ']' .
						( isset( $server_links[ $server ] ) ? '. ' . sprintf( __( 'Get your free API key at %s.', 'payment-gateways-by-currency-for-woocommerce' ),
							'<a href="' . $server_links[ $server ] . '" target="_blank">' . $server_links[ $server ] . '</a>' ) : '' ) . '<br>' .
						sprintf( __( 'You can also define your key as %s constant in %s file.', 'payment-gateways-by-currency-for-woocommerce' ),
							'<code>' . $server_key_constant . '</code>', '<code>' . 'wp-config.php' . '</code>' ),
					'id'       => "alg_wc_pgbc_convert_currency_auto_rates_server_keys[{$server}]",
					'default'  => '',
					'type'     => 'password',
					'custom_attributes' => array( 'autocomplete' => 'new-password' ),
				),
			) );
		}
		if ( in_array( $server, array( 'fixer' ) ) ) {
			$auto_rates_settings = array_merge( $auto_rates_settings, array(
				array(
					'desc'     => __( 'URL', 'payment-gateways-by-currency-for-woocommerce' ),
					'id'       => 'alg_wc_pgbc_convert_currency_auto_rates_fixer_url',
					'default'  => 'fixer',
					'type'     => 'select',
					'class'    => 'chosen_select',
					'options'  => array(
						'fixer'    => 'http://data.fixer.io/api/latest',
						'apilayer' => 'http://api.apilayer.com/fixer/latest',
					),
				),
			) );
		}
		$auto_rates_settings = array_merge( $auto_rates_settings, array(
			array(
				'desc'     => __( 'Multiplier', 'payment-gateways-by-currency-for-woocommerce' ),
				'desc_tip' => __( 'Ignored if zero.', 'payment-gateways-by-currency-for-woocommerce' ),
				'id'       => 'alg_wc_pgbc_convert_currency_rate_multiplier',
				'default'  => 0,
				'type'     => 'number',
				'custom_attributes' => array( 'min' => 0, 'step' => 0.000001 ),
			),
			array(
				'title'    => __( 'Update now from server', 'payment-gateways-by-currency-for-woocommerce' ),
				'desc'     => __( 'Update all rates now', 'payment-gateways-by-currency-for-woocommerce' ),
				'desc_tip' => __( 'Check the box and save changes to update.', 'payment-gateways-by-currency-for-woocommerce' ),
				'id'       => 'alg_wc_pgbc_convert_currency_auto_rates_now',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'type'     => 'sectionend',
				'id'       => 'alg_wc_pgbc_convert_currency_auto_rates_options',
			),
		) );

		return array_merge( $general_settings, $auto_rates_settings );
	}

}

endif;

return new Alg_WC_PGBC_Settings_Convert_General();
