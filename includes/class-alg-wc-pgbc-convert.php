<?php
/**
 * Payment Gateway Currency for WooCommerce - Convert
 *
 * @version 3.4.2
 * @since   2.0.0
 *
 * @author  Algoritmika Ltd.
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'Alg_WC_PGBC_Convert_Filterable_Scripts' ) && class_exists( 'WP_Scripts' ) ) :

/**
 * Alg_WC_PGBC_Convert_Filterable_Scripts.
 *
 * @version 3.4.2
 * @since   3.4.2
 *
 * @see     https://wordpress.stackexchange.com/questions/108362/how-to-intercept-already-localized-scripts
 *
 * @todo    [now] [!!!] (fix) possible "missing scripts" issue
 * @todo    [now] [!!!] (dev) move this to another file
 */

class Alg_WC_PGBC_Convert_Filterable_Scripts extends WP_Scripts {

	/**
	 * localize.
	 *
	 * @version 3.4.2
	 * @since   3.4.2
	 */
	function localize( $handle, $object_name, $l10n ) {
		$l10n = apply_filters( 'alg_wc_pgbc_convert_filterable_scripts_l10n', $l10n, $handle, $object_name );
		return parent::localize( $handle, $object_name, $l10n );
	}

}

endif;

if ( ! class_exists( 'Alg_WC_PGBC_Convert' ) ) :

class Alg_WC_PGBC_Convert {

	/**
	 * Constructor.
	 *
	 * @version 3.4.2
	 * @since   2.0.0
	 *
	 * @todo    [next] (dev) trigger AJAX update (i.e. mini-cart) when payment gateway is changed on the checkout page
	 * @todo    [next] (dev) move *all* hooks to `init` action?
	 * @todo    [next] (feature) currency reports
	 * @todo    [next] (feature) order by admin
	 * @todo    [later] (feature) "My account > Orders": add option convert prices (i.e. instead of locking the gateway, etc.)?
	 * @todo    [maybe] (feature) "My account > Orders": add option to hide "Pay" button (`woocommerce_my_account_my_orders_actions`)?
	 */
	function __construct() {
		// Properties
		$this->do_debug      = ( 'yes' === get_option( 'alg_wc_pgbc_convert_currency_debug', 'no' ) );
		// Rates
		$this->rates         = require_once( 'class-alg-wc-pgbc-convert-rates.php' );
		// Frontend and backend info
		$this->info_frontend = require_once( 'class-alg-wc-pgbc-convert-info-frontend.php' );
		$this->info_backend  = require_once( 'class-alg-wc-pgbc-convert-info-backend.php' );
		// Price and currency conversions
		$this->prices        = require_once( 'class-alg-wc-pgbc-convert-prices.php' );
		// Hooks
		if ( 'yes' === get_option( 'alg_wc_pgbc_convert_currency_enabled', 'no' ) ) {
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
			}
			// PayPal for WooCommerce by Angell EYE
			if ( 'yes' === get_option( 'alg_wc_pgbc_convert_currency_angelleye_ppcp', 'no' ) ) {
				add_filter( 'script_loader_tag', array( $this, 'angelleye_ppcp' ), PHP_INT_MAX, 2 );
			}
			// WPML
			$this->convert_on_wpml = get_option( 'alg_wc_pgbc_convert_currency_on_wpml', array() );
			if ( ! empty( $this->convert_on_wpml ) ) {
				add_filter( 'alg_wc_pgbc_convert_currency_do_convert', array( $this, 'do_convert_wpml' ) );
			}
		}
	}

	/**
	 * angelleye_ppcp.
	 *
	 * @version 3.4.2
	 * @since   3.4.2
	 *
	 * @todo    [now] [!!!] (dev) better way?
	 * @todo    [now] [!!!] (dev) extra check: currency differs from the original shop currency?
	 */
	function angelleye_ppcp( $tag, $handle ) {
		if ( 'angelleye-paypal-checkout-sdk' === $handle && false !== ( $currency = $this->get_gateway_currency( 'angelleye_ppcp' ) ) ) {
			$tag = str_replace( 'currency=' . get_option( 'woocommerce_currency' ), 'currency=' . $currency, $tag );
		}
		return $tag;
	}

	/**
	 * ppcp_init.
	 *
	 * @version 3.4.2
	 * @since   3.4.2
	 *
	 * @todo    [now] [!!!] (dev) extra check: using "smart button" (on checkout)?
	 * @todo    [now] [!!!] (dev) extra check: currency differs from the original shop currency?
	 */
	function ppcp_init() {
		if ( false !== $this->get_gateway_currency( 'ppcp-gateway' ) ) {
			add_filter( 'alg_wc_pgbc_convert_filterable_scripts_l10n', array( $this, 'ppcp_localize' ), 10, 3 );
			$GLOBALS['wp_scripts'] = new Alg_WC_PGBC_Convert_Filterable_Scripts();
		}
	}

	/**
	 * ppcp_localize.
	 *
	 * @version 3.4.2
	 * @since   3.4.2
	 */
	function ppcp_localize( $l10n, $handle, $object_name ) {
		if ( 'ppcp-smart-button' === $handle && 'PayPalCommerceGateway' === $object_name && ! empty( $l10n['button']['url'] ) && false !== ( $currency = $this->get_gateway_currency( 'ppcp-gateway' ) ) ) {
			$l10n['button']['url'] = add_query_arg( 'currency', $currency, $l10n['button']['url'] );
		}
		return $l10n;
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
	 * @todo    [maybe] (dev) there must be a better way?
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
	 * @todo    [maybe] (dev) run this directly (i.e. instead of on `before_woocommerce_pay`)?
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
	 * @todo    [next] (feature) same for "Restrict" section
	 * @todo    [next] (dev) default to `yes`?
	 * @todo    [maybe] (dev) `if ( ! $order_id || ! $order || ! $order_gateway ) { $available_gateways = array(); }`?
	 * @todo    [maybe] (dev) make this optional: `add_filter( 'woocommerce_pay_order_button_html', '__return_empty_string', PHP_INT_MAX )`?
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
	 * @todo    [next] (dev) find a better solution?
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
	 * @todo    [next] (dev) use `wp_parse_args()`?
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
	 * recalculate_wc_subscriptions_renewal_order.
	 *
	 * @version 2.0.0
	 * @since   2.0.0
	 *
	 * @todo    [next] (dev) add `recalculate` option for the subscription itself (i.e. not only for the parent or renewal orders)
	 */
	function recalculate_wc_subscriptions_renewal_order( $renewal_order, $subscription ) {
		$data = get_post_meta( $subscription->get_parent_id(), '_alg_wc_pgbc_data', true );
		$renewal_order = $this->prices->recalculate_order( $renewal_order, $data );
		return $renewal_order;
	}

	/**
	 * save_order_pgbc_data.
	 *
	 * @version 2.0.0
	 * @since   2.0.0
	 */
	function save_order_pgbc_data( $order_id ) {
		$data = WC()->session->get( 'alg_wc_pgbc_data', array() );
		$data['version'] = alg_wc_pgbc()->version;
		update_post_meta( $order_id, '_alg_wc_pgbc_data', $data );
		$this->clear_session_data();
	}

	/**
	 * add_checkout_script.
	 *
	 * @version 1.4.0
	 * @since   1.4.0
	 *
	 * @todo    [next] (feature) add option to disable this
	 * @todo    [next] (dev) move to `class-alg-wc-pgbc-checkout.js`
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
	 * @todo    [next] (dev) Fallback #1: `if ( ! empty( $available_payment_gateways ) && in_array( $_REQUEST['payment_method'], $available_payment_gateways ) )`?
	 * @todo    [maybe] (dev) do we really need all fallbacks?
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
	 * @version 3.1.0
	 * @since   1.4.0
	 */
	function do_convert() {
		return apply_filters( 'alg_wc_pgbc_convert_currency_do_convert', (
			is_checkout() ||
			( 'yes' === get_option( 'alg_wc_pgbc_convert_currency_on_checkout', 'yes' ) && is_cart() ) ||
			( 'yes' === get_option( 'alg_wc_pgbc_convert_currency_on_ajax', 'yes' ) && is_ajax() )
		) );
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
