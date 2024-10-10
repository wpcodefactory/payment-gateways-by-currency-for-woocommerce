<?php
/**
 * Payment Gateway Currency for WooCommerce - Convert
 *
 * @version 4.0.1
 * @since   2.0.0
 *
 * @author  Algoritmika Ltd.
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Alg_WC_PGBC_Convert' ) ) :

class Alg_WC_PGBC_Convert {

	/**
	 * do_debug.
	 *
	 * @version 3.8.1
	 * @since   3.8.1
	 */
	public $do_debug;

	/**
	 * rates.
	 *
	 * @version 3.8.1
	 * @since   3.8.1
	 */
	public $rates;

	/**
	 * info_frontend.
	 *
	 * @version 3.8.1
	 * @since   3.8.1
	 */
	public $info_frontend;

	/**
	 * info_backend.
	 *
	 * @version 3.8.1
	 * @since   3.8.1
	 */
	public $info_backend;

	/**
	 * prices.
	 *
	 * @version 3.8.1
	 * @since   3.8.1
	 */
	public $prices;

	/**
	 * convert_on_wpml.
	 *
	 * @version 3.8.1
	 * @since   3.8.1
	 */
	public $convert_on_wpml;

	/**
	 * options.
	 *
	 * @version 3.8.1
	 * @since   3.8.1
	 */
	public $options;

	/**
	 * gateway_currencies.
	 *
	 * @version 3.8.1
	 * @since   3.8.1
	 */
	public $gateway_currencies;

	/**
	 * gateway_currency_symbols.
	 *
	 * @version 3.8.1
	 * @since   3.8.1
	 */
	public $gateway_currency_symbols;

	/**
	 * gateway_currency_nums_decimals.
	 *
	 * @version 3.9.0
	 * @since   3.9.0
	 */
	public $gateway_currency_nums_decimals;

	/**
	 * last_known_current_gateway.
	 *
	 * @version 3.8.1
	 * @since   3.8.1
	 */
	public $last_known_current_gateway;

	/**
	 * Constructor.
	 *
	 * @version 4.0.1
	 * @since   2.0.0
	 *
	 * @todo    (dev) YITH WooCommerce Product Add-Ons: use `yith_wapo_addon_prices_on_cart` filter?
	 * @todo    (dev) trigger AJAX update (i.e., mini-cart) when payment gateway is changed on the checkout page
	 * @todo    (dev) move *all* hooks to `init` action?
	 * @todo    (feature) currency reports
	 * @todo    (feature) order by admin
	 * @todo    (feature) "My account > Orders": add option convert prices (i.e., instead of locking the gateway, etc.)?
	 * @todo    (feature) "My account > Orders": add option to hide "Pay" button (`woocommerce_my_account_my_orders_actions`)?
	 */
	function __construct() {

		// Properties
		$this->do_debug = ( 'yes' === get_option( 'alg_wc_pgbc_convert_currency_debug', 'no' ) );

		// Rates
		$this->rates = require_once( 'class-alg-wc-pgbc-convert-rates.php' );

		// Frontend and backend info
		$this->info_frontend = require_once( 'class-alg-wc-pgbc-convert-info-frontend.php' );
		$this->info_backend  = require_once( 'class-alg-wc-pgbc-convert-info-backend.php' );

		// Price and currency conversions
		$this->prices = require_once( 'class-alg-wc-pgbc-convert-prices.php' );

		// Hooks
		if ( 'yes' === get_option( 'alg_wc_pgbc_convert_currency_enabled', 'no' ) ) {

			// Filterable scripts
			add_action( 'init', array( $this, 'maybe_load_filterable_scripts' ), 0 );

			// Checkout script
			if ( 'no' !== get_option( 'alg_wc_pgbc_convert_currency_on_checkout', 'yes' ) ) {
				add_action( 'wp_footer', array( $this, 'add_checkout_script' ) );
			}

			// Collecting data in order meta
			add_action( 'woocommerce_checkout_order_processed', array( $this, 'save_order_pgbc_data' ), PHP_INT_MAX );

			// PayPal supported currencies
			if ( 'yes' === get_option( 'alg_wc_pgbc_convert_currency_paypal_show_always', 'yes' ) ) {
				add_filter( 'woocommerce_paypal_supported_currencies', array( $this, 'extend_supported_currencies' ), PHP_INT_MAX );
			}

			// PayFast supported currencies
			if ( 'yes' === get_option( 'alg_wc_pgbc_convert_currency_payfast_show_always', 'yes' ) ) {
				add_filter( 'woocommerce_gateway_payfast_available_currencies', array( $this, 'extend_supported_currencies' ), PHP_INT_MAX );
			}

			// WooCommerce Subscriptions - Renewals
			if ( 'yes' === get_option( 'alg_wc_pgbc_convert_currency_wc_subscriptions_renewal', 'no' ) ) {
				add_filter( 'wcs_renewal_order_created', array( $this, 'recalculate_wc_subscriptions_renewal_order' ), PHP_INT_MAX, 2 );
			}

			// Fix mini cart
			if ( 'yes' === get_option( 'alg_wc_pgbc_convert_currency_recalculate_cart', 'yes' ) ) {
				add_action( 'woocommerce_cart_loaded_from_session', array( $this, 'recalculate_cart' ), PHP_INT_MAX );
			}

			// "My account > Orders > Pay": Lock gateway
			if ( 'yes' === get_option( 'alg_wc_pgbc_convert_currency_order_pay_lock_gateway', 'yes' ) ) {
				add_action( 'before_woocommerce_pay', array( $this, 'order_pay_lock_gateway_hook' ) );
			}

			// Add `bdi` tag to `wp_kses_allowed_html`
			if ( 'yes' === get_option( 'alg_wc_pgbc_convert_currency_add_bdi', 'no' ) ) {
				add_filter( 'wp_kses_allowed_html', array( $this, 'add_bdi_tag_to_wp_kses_allowed_html' ), PHP_INT_MAX, 2 );
			}

			// WooCommerce PayPal Express
			if ( 'yes' === get_option( 'alg_wc_pgbc_convert_currency_ppec_paypal', 'yes' ) ) {
				add_filter( 'woocommerce_paypal_express_checkout_sdk_script_args', array( $this, 'woocommerce_paypal_express' ), PHP_INT_MAX );
			}

			// WooCommerce PayPal Payments
			if ( 'yes' === get_option( 'alg_wc_pgbc_convert_currency_ppcp', 'no' ) ) {
				add_action( 'init', array( $this, 'ppcp_init' ) );
				add_filter( 'ppcp_request_args', array( $this, 'ppcp_request_args' ), PHP_INT_MAX );
			}

			// PayPal for WooCommerce by Angell EYE
			if ( 'yes' === get_option( 'alg_wc_pgbc_convert_currency_angelleye_ppcp', 'no' ) ) {
				add_filter( 'script_loader_tag', array( $this, 'angelleye_ppcp' ), PHP_INT_MAX, 2 );
				add_action( 'init', array( $this, 'angelleye_ppcp_init' ) );
			}

			// YITH WooCommerce Account Funds Premium
			if ( 'yes' === get_option( 'alg_wc_pgbc_convert_currency_yith_account_funds', 'no' ) ) {
				add_filter( 'yith_show_available_funds', array( $this, 'yith_account_funds' ), PHP_INT_MAX );
			}

			// YITH WooCommerce Product Add-Ons
			if ( 'yes' === get_option( 'alg_wc_pgbc_convert_currency_yith_product_add_ons', 'no' ) ) {
				add_filter( 'yith_wapo_get_addon_price',      array( $this->prices, 'convert_price' ) );
				add_filter( 'yith_wapo_get_addon_sale_price', array( $this->prices, 'convert_price' ) );
			}

			// WPML
			$this->convert_on_wpml = get_option( 'alg_wc_pgbc_convert_currency_on_wpml', array() );
			if ( ! empty( $this->convert_on_wpml ) ) {
				add_filter( 'alg_wc_pgbc_convert_currency_do_convert', array( $this, 'do_convert_wpml' ) );
			}

			// Check single product page
			if ( 'yes' === get_option( 'alg_wc_pgbc_convert_currency_check_single_product', 'no' ) ) {
				add_filter( 'alg_wc_pgbc_convert_currency_do_convert', array( $this, 'do_convert_check_single_product' ), 11 );
			}

			// Check shop currency
			if ( 'yes' === get_option( 'alg_wc_pgbc_convert_currency_check_shop_currency', 'no' ) ) {
				add_filter( 'alg_wc_pgbc_convert_currency_do_convert', array( $this, 'do_convert_check_shop_currency' ), 11 );
			}

			// WooCommerce Analytics
			require_once( 'analytics/class-alg-wc-pgbc-analytics.php' );
			$analytics = new Alg_WC_PGBC_Analytics();
			$analytics->init();

		}
	}

	/**
	 * maybe_load_filterable_scripts.
	 *
	 * @version 3.9.1
	 * @since   3.8.1
	 */
	function maybe_load_filterable_scripts() {

		// Ensure we do this only once
		remove_action( 'init', array( $this, 'maybe_load_filterable_scripts' ), 0 );

		// Check gateway options
		if (
			( 'no' === get_option( 'alg_wc_pgbc_convert_currency_ppcp',           'no' ) ) &&
			( 'no' === get_option( 'alg_wc_pgbc_convert_currency_angelleye_ppcp', 'no' ) )
		) {
			return;
		}

		// "Gravity Forms" plugin
		if ( isset( $_GET['page'] ) && 'gf_edit_forms' === $_GET['page'] ) {
			return;
		}

		// `Alg_WC_PGBC_Convert_Filterable_Scripts`
		require_once( 'class-alg-wc-pgbc-convert-filterable-scripts.php' );
		$GLOBALS['wp_scripts'] = new Alg_WC_PGBC_Convert_Filterable_Scripts();

	}

	/**
	 * get_order_data.
	 *
	 * @version 3.6.1
	 * @since   3.6.0
	 *
	 * @todo    (dev) `is_a( $order, 'WC_Order' )` || `is_callable( array( $order, 'get_meta' ) )`?
	 * @todo    (dev) `get_post_meta()` as a fallback?
	 */
	function get_order_data( $order ) {
		return ( $order ? $order->get_meta( '_alg_wc_pgbc_data' ) : '' );
	}

	/**
	 * set_order_data.
	 *
	 * @version 3.6.1
	 * @since   3.6.0
	 *
	 * @todo    (test) `$order->save()`
	 * @todo    (dev) `is_a( $order, 'WC_Order' )` || `is_callable( array( $order, 'update_meta_data' ) )`?
	 * @todo    (dev) `update_post_meta()` as a fallback?
	 */
	function set_order_data( $order, $data ) {
		if ( $order ) {
			$order->update_meta_data( '_alg_wc_pgbc_data', $data );
			return $order->save();
		}
		return false;
	}

	/**
	 * yith_account_funds.
	 *
	 * @version 3.4.3
	 * @since   3.4.3
	 */
	function yith_account_funds( $funds ) {
		if ( $this->do_convert() && ( $current_gateway = $this->get_current_gateway() ) ) {
			if ( false !== ( $rate = $this->rates->get_gateway_rate( $current_gateway ) ) ) {
				$funds *= $rate;
			}
		}
		return $funds;
	}

	/**
	 * angelleye_ppcp.
	 *
	 * @version 3.4.2
	 * @since   3.4.2
	 *
	 * @todo    (dev) better way?
	 * @todo    (dev) extra check: currency differs from the original shop currency?
	 */
	function angelleye_ppcp( $tag, $handle ) {
		if ( 'angelleye-paypal-checkout-sdk' === $handle && false !== ( $currency = $this->get_gateway_currency( 'angelleye_ppcp' ) ) ) {
			$tag = str_replace( 'currency=' . get_option( 'woocommerce_currency' ), 'currency=' . $currency, $tag );
		}
		return $tag;
	}

	/**
	 * angelleye_ppcp_init.
	 *
	 * @version 3.9.1
	 * @since   3.8.1
	 */
	function angelleye_ppcp_init() {
		if ( false !== $this->get_gateway_currency( 'angelleye_ppcp' ) ) {
			add_filter( 'alg_wc_pgbc_convert_filterable_scripts_l10n', array( $this, 'angelleye_ppcp_localize' ), 10, 3 );
		}
	}

	/**
	 * angelleye_ppcp_localize.
	 *
	 * @version 3.8.1
	 * @since   3.8.1
	 */
	function angelleye_ppcp_localize( $l10n, $handle, $object_name ) {
		if (
			'angelleye-paypal-checkout-sdk' === $handle &&
			'angelleye_ppcp_manager' === $object_name &&
			false !== ( $currency = $this->get_gateway_currency( 'angelleye_ppcp' ) )
		) {
			if ( ! empty( $l10n['paypal_sdk_url'] ) ) {
				$l10n['paypal_sdk_url'] = add_query_arg( 'currency', $currency, $l10n['paypal_sdk_url'] );
			}
		}
		return $l10n;
	}

	/**
	 * ppcp_init.
	 *
	 * @version 3.9.1
	 * @since   3.4.2
	 *
	 * @todo    (dev) extra check: using "smart button" (on checkout)?
	 * @todo    (dev) extra check: currency differs from the original shop currency?
	 */
	function ppcp_init() {
		if ( false !== $this->get_gateway_currency( 'ppcp-gateway' ) ) {
			add_filter( 'alg_wc_pgbc_convert_filterable_scripts_l10n', array( $this, 'ppcp_localize' ), 10, 3 );
		}
	}

	/**
	 * ppcp_localize.
	 *
	 * @version 3.7.4
	 * @since   3.4.2
	 */
	function ppcp_localize( $l10n, $handle, $object_name ) {
		if (
			'ppcp-smart-button' === $handle &&
			'PayPalCommerceGateway' === $object_name &&
			false !== ( $currency = $this->get_gateway_currency( 'ppcp-gateway' ) )
		) {
			if ( ! empty( $l10n['button']['url'] ) ) {
				$l10n['button']['url'] = add_query_arg( 'currency', $currency, $l10n['button']['url'] );
			}
			if ( ! empty( $l10n['url'] ) ) {
				$l10n['url'] = add_query_arg( 'currency', $currency, $l10n['url'] );
			}
			if ( ! empty( $l10n['url_params']['currency'] ) ) {
				$l10n['url_params']['currency'] = $currency;
			}
			if ( ! empty( $l10n['currency'] ) ) {
				$l10n['currency'] = $currency;
			}
		}
		return $l10n;
	}

	/**
	 * ppcp_request_args.
	 *
	 * @version 4.0.1
	 * @since   4.0.1
	 */
	function ppcp_request_args( $args ) {
		if (
			isset( $args['body'] ) &&
			false !== ( $currency = $this->get_gateway_currency( 'ppcp-gateway' ) )
		) {
			$body = json_decode( $args['body'] );
			if (
				isset( $body->purchase_units ) &&
				is_array( $body->purchase_units )
			) {
				foreach ( $body->purchase_units as &$purchase_unit ) {
					// Amount
					if ( isset( $purchase_unit->amount->currency_code ) ) {
						$purchase_unit->amount->currency_code = $currency;
					}
					if ( isset( $purchase_unit->amount->breakdown->item_total->currency_code ) ) {
						$purchase_unit->amount->breakdown->item_total->currency_code = $currency;
					}
					if ( isset( $purchase_unit->amount->breakdown->shipping->currency_code ) ) {
						$purchase_unit->amount->breakdown->shipping->currency_code = $currency;
					}
					if ( isset( $purchase_unit->amount->breakdown->tax_total->currency_code ) ) {
						$purchase_unit->amount->breakdown->tax_total->currency_code = $currency;
					}
					// Items
					if (
						isset( $purchase_unit->items ) &&
						is_array( $purchase_unit->items )
					) {
						foreach ( $purchase_unit->items as &$item ) {
							if ( isset( $item->unit_amount->currency_code ) ) {
								$item->unit_amount->currency_code = $currency;
							}
						}
					}
				}
			}
			$args['body'] = json_encode( $body );
		}
		return $args;
	}

	/**
	 * woocommerce_paypal_express.
	 *
	 * @version 3.0.1
	 * @since   3.0.1
	 */
	function woocommerce_paypal_express( $script_args ) {
		if ( false !== ( $currency = $this->get_gateway_currency( 'ppec_paypal' ) ) ) {
			$script_args['currency'] = $currency;
		}
		return $script_args;
	}

	/**
	 * add_bdi_tag_to_wp_kses_allowed_html.
	 *
	 * @version 3.0.0
	 * @since   3.0.0
	 *
	 * @see     https://github.com/woocommerce/woocommerce/blob/5.5.1/templates/cart/cart-totals.php#L80
	 * @see     https://github.com/woocommerce/woocommerce/blob/5.5.1/includes/wc-cart-functions.php#L295
	 * @see     https://developer.wordpress.org/reference/functions/wp_kses_allowed_html/
	 *
	 * @todo    (dev) there must be a better way?
	 */
	function add_bdi_tag_to_wp_kses_allowed_html( $tags, $context ) {
		if ( 'post' === $context && ! isset( $tags['bdi'] ) ) {
			$tags['bdi'] = array();
		}
		return $tags;
	}

	/**
	 * order_pay_lock_gateway_hook.
	 *
	 * @version 2.1.0
	 * @since   2.1.0
	 *
	 * @todo    (dev) run this directly (i.e., instead of on `before_woocommerce_pay`)?
	 */
	function order_pay_lock_gateway_hook() {
		add_filter( 'woocommerce_available_payment_gateways', array( $this, 'order_pay_lock_gateway' ), PHP_INT_MAX );
	}

	/**
	 * order_pay_lock_gateway.
	 *
	 * @version 2.1.0
	 * @since   2.1.0
	 *
	 * @todo    (feature) same for "Restrict" section
	 * @todo    (dev) default to `yes`?
	 * @todo    (dev) `if ( ! $order_id || ! $order || ! $order_gateway ) { $available_gateways = array(); }`?
	 * @todo    (dev) make this optional: `add_filter( 'woocommerce_pay_order_button_html', '__return_empty_string', PHP_INT_MAX )`?
	 */
	function order_pay_lock_gateway( $available_gateways ) {
		if ( is_checkout_pay_page() ) {
			global $wp;
			if (
				( $order_id      = absint( $wp->query_vars['order-pay'] ) ) && // `is_checkout_pay_page()` makes sure that `! empty( $wp->query_vars['order-pay'] )`
				( $order         = wc_get_order( $order_id ) ) &&
				( $order_gateway = $order->get_payment_method() )
			) {
				$available_gateways = ( isset( $available_gateways[ $order_gateway ] ) ? array( $available_gateways[ $order_gateway ] ) : array() );
			}
			if ( empty( $available_gateways ) ) {
				add_filter( 'woocommerce_pay_order_button_html', '__return_empty_string', PHP_INT_MAX );
			}
		}
		return $available_gateways;
	}

	/**
	 * get_rate_step_attribute.
	 *
	 * @version 2.1.0
	 * @since   2.1.0
	 */
	function get_rate_step_attribute() {
		return ( 'no' === get_option( 'alg_wc_pgbc_convert_currency_rate_type_text', 'no' ) ? array( 'step' => $this->get_rate_step() ) : array() );
	}

	/**
	 * get_rate_step.
	 *
	 * @version 2.1.0
	 * @since   2.1.0
	 */
	function get_rate_step() {
		return str_replace( ',', '.', get_option( 'alg_wc_pgbc_convert_currency_rate_step', '0.000001' ) );
	}

	/**
	 * recalculate_cart.
	 *
	 * @version 3.0.0
	 * @since   2.0.0
	 *
	 * @todo    (dev) find a better solution?
	 */
	function recalculate_cart( $cart ) {
		WC()->session->set( 'cart_totals', null );
	}

	/**
	 * get_options.
	 *
	 * @version 2.0.0
	 * @since   2.0.0
	 *
	 * @todo    (dev) use `wp_parse_args()`?
	 */
	function get_options( $force = false ) {
		if ( $force || ! isset( $this->options ) ) {
			$this->options = get_option( 'alg_wc_pgbc_convert_currency_advanced', array() );
			$this->options = array(
				'shipping'                 => ( ! isset( $this->options['shipping'] )                 || 'yes' === $this->options['shipping'] ),
				'shipping_free_min_amount' => ( ! isset( $this->options['shipping_free_min_amount'] ) || 'yes' === $this->options['shipping_free_min_amount'] ),
				'coupon'                   => ( ! isset( $this->options['coupon'] )                   || 'yes' === $this->options['coupon'] ),
				'coupon_min_amount'        => ( ! isset( $this->options['coupon_min_amount'] )        || 'yes' === $this->options['coupon_min_amount'] ),
				'coupon_max_amount'        => ( ! isset( $this->options['coupon_max_amount'] )        || 'yes' === $this->options['coupon_max_amount'] ),
				'cart_fee'                 => ( ! isset( $this->options['cart_fee'] )                 || 'yes' === $this->options['cart_fee'] ),
			);
		}
		return $this->options;
	}

	/**
	 * get_option.
	 *
	 * @version 2.0.0
	 * @since   2.0.0
	 */
	function get_option( $option, $default = false ) {
		if ( ! isset( $this->options ) ) {
			$this->get_options();
		}
		return ( isset( $this->options[ $option ] ) ? $this->options[ $option ] : $default );
	}

	/**
	 * get_gateway_currencies.
	 *
	 * @version 2.0.0
	 * @since   2.0.0
	 */
	function get_gateway_currencies( $force = false ) {
		if ( $force || ! isset( $this->gateway_currencies ) ) {
			$this->gateway_currencies = get_option( 'alg_wc_pgbc_convert_currency', array() );
		}
		return $this->gateway_currencies;
	}

	/**
	 * get_gateway_currency.
	 *
	 * @version 2.0.0
	 * @since   2.0.0
	 */
	function get_gateway_currency( $gateway ) {
		if ( ! isset( $this->gateway_currencies ) ) {
			$this->get_gateway_currencies();
		}
		return ( isset( $this->gateway_currencies[ $gateway ] ) && '' !== $this->gateway_currencies[ $gateway ] ? $this->gateway_currencies[ $gateway ] : false );
	}

	/**
	 * get_gateway_currency_symbols.
	 *
	 * @version 2.0.0
	 * @since   2.0.0
	 */
	function get_gateway_currency_symbols( $force = false ) {
		if ( $force || ! isset( $this->gateway_currency_symbols ) ) {
			$this->gateway_currency_symbols = get_option( 'alg_wc_pgbc_convert_symbol', array() );
		}
		return $this->gateway_currency_symbols;
	}

	/**
	 * get_gateway_currency_symbol.
	 *
	 * @version 2.0.0
	 * @since   2.0.0
	 */
	function get_gateway_currency_symbol( $gateway ) {
		if ( ! isset( $this->gateway_currency_symbols ) ) {
			$this->get_gateway_currency_symbols();
		}
		return ( isset( $this->gateway_currency_symbols[ $gateway ] ) && '' !== $this->gateway_currency_symbols[ $gateway ] ? $this->gateway_currency_symbols[ $gateway ] : false );
	}

	/**
	 * get_gateway_currency_nums_decimals.
	 *
	 * @version 3.9.0
	 * @since   3.9.0
	 *
	 * @todo    (dev) remove this and use `get_option( 'alg_wc_pgbc_convert_num_decimals', array() )` directly (same for other similar methods)
	 */
	function get_gateway_currency_nums_decimals( $force = false ) {
		if ( $force || ! isset( $this->gateway_currency_nums_decimals ) ) {
			$this->gateway_currency_nums_decimals = get_option( 'alg_wc_pgbc_convert_num_decimals', array() );
		}
		return $this->gateway_currency_nums_decimals;
	}

	/**
	 * get_gateway_currency_num_decimals.
	 *
	 * @version 3.9.0
	 * @since   3.9.0
	 */
	function get_gateway_currency_num_decimals( $gateway ) {
		if ( ! isset( $this->gateway_currency_nums_decimals ) ) {
			$this->get_gateway_currency_nums_decimals();
		}
		return ( isset( $this->gateway_currency_nums_decimals[ $gateway ] ) && '' !== $this->gateway_currency_nums_decimals[ $gateway ] ? $this->gateway_currency_nums_decimals[ $gateway ] : false );
	}

	/**
	 * recalculate_wc_subscriptions_renewal_order.
	 *
	 * @version 3.6.0
	 * @since   2.0.0
	 *
	 * @todo    (test) `wc_get_order( $subscription->get_parent_id() )`
	 * @todo    (dev) add `recalculate` option for the subscription itself (i.e., not only for the parent or renewal orders)
	 */
	function recalculate_wc_subscriptions_renewal_order( $renewal_order, $subscription ) {
		$data          = $this->get_order_data( wc_get_order( $subscription->get_parent_id() ) );
		$renewal_order = $this->prices->recalculate_order( $renewal_order, $data );
		return $renewal_order;
	}

	/**
	 * save_order_pgbc_data.
	 *
	 * @version 3.6.0
	 * @since   2.0.0
	 */
	function save_order_pgbc_data( $order_id ) {
		$data = WC()->session->get( 'alg_wc_pgbc_data', array() );
		$data['version'] = alg_wc_pgbc()->version;
		$this->set_order_data( wc_get_order( $order_id ), $data );
		$this->clear_session_data();
	}

	/**
	 * add_checkout_script.
	 *
	 * @version 1.4.0
	 * @since   1.4.0
	 *
	 * @todo    (feature) add option to disable this
	 * @todo    (dev) move to `class-alg-wc-pgbc-checkout.js`
	 */
	function add_checkout_script( $core ) {
		if ( is_checkout() ) {
			?><script>
				jQuery( document ).ready( function() {
					jQuery( 'body' ).on( 'change', 'input[name="payment_method"]', function() {
						jQuery( 'body' ).trigger( 'update_checkout' );
					} );
				} );
			</script><?php
		}
	}

	/**
	 * get_current_gateway.
	 *
	 * @version 3.0.1
	 * @since   1.4.0
	 *
	 * @todo    (dev) Fallback #1: `if ( ! empty( $available_payment_gateways ) && in_array( $_REQUEST['payment_method'], $available_payment_gateways ) )`?
	 * @todo    (dev) do we really need all fallbacks?
	 */
	function get_current_gateway() {

		// Get it from session
		if ( function_exists( 'WC' ) && isset( WC()->session->chosen_payment_method ) && '' != ( $current_gateway = WC()->session->chosen_payment_method ) ) {
			$this->last_known_current_gateway = $current_gateway;
			return $current_gateway;
		}

		// WooCommerce PayPal Express
		if ( isset( $_GET['wc-ajax'] ) && in_array( wc_clean( $_GET['wc-ajax'] ), array( 'wc_ppec_start_checkout', 'wc_ppec_generate_cart' ) ) ) {
			$this->last_known_current_gateway = 'ppec_paypal';
			return 'ppec_paypal';
		}

		// Fallbacks
		$fallbacks = get_option( 'alg_wc_pgbc_convert_currency_current_gateway_fallbacks', array( 'payment_method' ) );

		// Fallback #1: `$_REQUEST['payment_method']`
		if ( in_array( 'payment_method', $fallbacks ) && ! empty( $_REQUEST['payment_method'] ) ) {
			$current_gateway = wc_clean( wp_unslash( $_REQUEST['payment_method'] ) );
			$this->last_known_current_gateway = $current_gateway;
			return $current_gateway;
		}

		// Fallback #2: First available gateway
		if ( in_array( 'first_available', $fallbacks ) ) {
			if ( function_exists( 'WC' ) && isset( WC()->payment_gateways ) && is_callable( array( WC()->payment_gateways, 'get_available_payment_gateways' ) ) ) {
				$available_payment_gateways = WC()->payment_gateways->get_available_payment_gateways();
				$available_payment_gateways = ( is_array( $available_payment_gateways ) ? array_keys( $available_payment_gateways ) : false );
				if ( ! empty( $available_payment_gateways ) ) {
					return $available_payment_gateways[0];
				}
			}
		}

		// Fallback #3: Last known current gateway
		if ( in_array( 'last_known', $fallbacks ) && isset( $this->last_known_current_gateway ) ) {
			return $this->last_known_current_gateway;
		}

		// Fallback #4: Default gateway
		if ( in_array( 'default', $fallbacks ) ) {
			return get_option( 'woocommerce_default_gateway', '' );
		}

		// Nothing found
		return false;

	}

	/**
	 * do_convert.
	 *
	 * @version 3.8.0
	 * @since   1.4.0
	 */
	function do_convert() {
		return apply_filters( 'alg_wc_pgbc_convert_currency_do_convert', (

			// Checkout
			is_checkout() ||

			// Cart
			(
				'yes' === get_option( 'alg_wc_pgbc_convert_currency_on_checkout', 'yes' ) &&
				is_cart()
			) ||

			// AJAX
			(
				'yes' === get_option( 'alg_wc_pgbc_convert_currency_on_ajax', 'yes' ) &&
				is_ajax()
			) ||

			// "WooCommerce PayPal Payments" plugin
			(
				'yes' === get_option( 'alg_wc_pgbc_convert_currency_ppcp', 'no' ) &&
				isset( $_REQUEST['wc-ajax'] ) && 'ppc-create-order' === $_REQUEST['wc-ajax']
			)

		) );
	}

	/**
	 * do_convert_check_shop_currency.
	 *
	 * @version 3.9.3
	 * @since   3.9.3
	 */
	function do_convert_check_shop_currency( $do_convert ) {
		if ( $do_convert ) {

			$had_filter = remove_filter( 'woocommerce_currency', array( $this->prices, 'convert_currency' ), PHP_INT_MAX );
			$woocommerce_currency = get_woocommerce_currency();
			if ( $had_filter ) {
				add_filter( 'woocommerce_currency', array( $this->prices, 'convert_currency' ), PHP_INT_MAX );
			}

			if ( $woocommerce_currency !== get_option( 'woocommerce_currency' ) ) {
				return false;
			}

		}
		return $do_convert;
	}

	/**
	 * do_convert_check_single_product.
	 *
	 * @version 3.9.2
	 * @since   3.9.2
	 *
	 * @todo    (dev) code refactoring: `return ( $do_convert && ! is_product() );`?
	 */
	function do_convert_check_single_product( $do_convert ) {
		return ( $do_convert ? ! is_product() : $do_convert );
	}

	/**
	 * do_convert_wpml.
	 *
	 * @version 3.1.0
	 * @since   3.1.0
	 */
	function do_convert_wpml( $do_convert ) {
		return ( defined( 'ICL_LANGUAGE_CODE' ) && ! in_array( ICL_LANGUAGE_CODE, $this->convert_on_wpml ) ? false : $do_convert );
	}

	/**
	 * extend_supported_currencies.
	 *
	 * @version 3.3.1
	 * @since   1.4.0
	 */
	function extend_supported_currencies( $supported_currencies ) {
		$currency = get_woocommerce_currency();
		if ( ! in_array( $currency, $supported_currencies ) ) {
			$supported_currencies[] = $currency;
		}
		return $supported_currencies;
	}

	/**
	 * add_to_session_data.
	 *
	 * Saving data in session for later use in `$this->save_order_pgbc_data()` and in `$this->info_frontend->general_info_from_session()`.
	 *
	 * @version 3.0.0
	 * @since   2.0.0
	 */
	function add_to_session_data( $data ) {
		if ( WC()->session ) {
			if ( ! WC()->session->has_session() && 'yes' === get_option( 'alg_wc_pgbc_convert_currency_set_session_cookie', 'yes' ) ) {
				WC()->session->set_customer_session_cookie( true );
			}
			$data['timestamp'] = time();
			WC()->session->set( 'alg_wc_pgbc_data', array_merge( WC()->session->get( 'alg_wc_pgbc_data', array() ), $data ) );
		}
	}

	/**
	 * clear_session_data.
	 *
	 * @version 2.0.0
	 * @since   2.0.0
	 */
	function clear_session_data() {
		WC()->session->set( 'alg_wc_pgbc_data', array() );
	}

}

endif;

return new Alg_WC_PGBC_Convert();
