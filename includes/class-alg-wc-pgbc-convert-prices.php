<?php
/**
 * Payment Gateway Currency for WooCommerce - Convert - Prices Class
 *
 * @version 3.3.0
 * @since   2.0.0
 *
 * @author  Algoritmika Ltd.
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'Alg_WC_PGBC_Convert_Prices' ) ) :

class Alg_WC_PGBC_Convert_Prices {

	/**
	 * Constructor.
	 *
	 * @version 3.0.2
	 * @since   2.0.0
	 *
	 * @todo    [now] (feature) rounding (however, it will cause issues with our "Info" section)?
	 */
	function __construct() {
		if ( 'yes' === get_option( 'alg_wc_pgbc_convert_currency_enabled', 'no' ) ) {
			$this->do_cache_prices  = ( 'yes' === get_option( 'alg_wc_pgbc_convert_currency_cache_prices', 'yes' ) );
			$this->cache_product_id = get_option( 'alg_wc_pgbc_convert_currency_cache_product_id', 'product_id' );
			$hook = ( 'no' !== get_option( 'alg_wc_pgbc_convert_currency_on_checkout', 'yes' ) ? 'init' : 'woocommerce_checkout_process' );
			add_action( $hook, array( $this, 'add_hooks' ) );
		}
	}

	/**
	 * get_converter.
	 *
	 * @version 2.0.0
	 * @since   2.0.0
	 */
	function get_converter() {
		if ( ! isset( $this->converter ) ) {
			$this->converter = alg_wc_pgbc()->core->convert;
		}
		return $this->converter;
	}

	/**
	 * add_hooks.
	 *
	 * @version 2.0.0
	 * @since   2.0.0
	 *
	 * @todo    [next] (dev) `woocommerce_cart_loaded_from_session`, `woocommerce_before_calculate_totals`?
	 * @todo    [maybe] (dev) Advanced: hooks on `init`: make it optional?
	 */
	function add_hooks() {
		// Product price
		add_filter( 'woocommerce_product_get_price',                       array( $this, 'convert_price' ), PHP_INT_MAX, 2 );
		add_filter( 'woocommerce_product_variation_get_price',             array( $this, 'convert_price' ), PHP_INT_MAX, 2 );
		// Shipping price
		if ( $this->get_converter()->get_option( 'shipping' ) ) {
			add_filter( 'woocommerce_package_rates',                       array( $this, 'convert_shipping_price' ), PHP_INT_MAX, 2 );
		}
		if ( $this->get_converter()->get_option( 'shipping_free_min_amount' ) ) {
			add_filter( 'woocommerce_shipping_free_shipping_is_available', array( $this, 'convert_shipping_free_min_amount' ), PHP_INT_MAX, 3 );
		}
		// Coupons
		if ( $this->get_converter()->get_option( 'coupon' ) ) {
			add_filter( 'woocommerce_coupon_get_amount',                   array( $this, 'convert_coupon_amount' ), PHP_INT_MAX, 2 );
		}
		if ( $this->get_converter()->get_option( 'coupon_min_amount' ) ) {
			add_filter( 'woocommerce_coupon_get_minimum_amount',           array( $this, 'convert_price' ), PHP_INT_MAX );
		}
		if ( $this->get_converter()->get_option( 'coupon_max_amount' ) ) {
			add_filter( 'woocommerce_coupon_get_maximum_amount',           array( $this, 'convert_price' ), PHP_INT_MAX );
		}
		// Cart fees
		if ( $this->get_converter()->get_option( 'cart_fee' ) ) {
			add_action( 'woocommerce_cart_calculate_fees',                 array( $this, 'convert_cart_fees' ), PHP_INT_MAX );
		}
		// Currency code & symbol
		add_filter( 'woocommerce_currency',                                array( $this, 'convert_currency' ), PHP_INT_MAX );
		add_filter( 'woocommerce_currency_symbol',                         array( $this, 'convert_currency_symbol' ), PHP_INT_MAX, 2 );
	}

	/**
	 * prepare_price.
	 *
	 * @version 3.3.0
	 * @since   2.0.0
	 *
	 * @todo    [now] [!] (dev) WCOOS: `$precision = $WOOCS->get_currency_price_num_decimals( $WOOCS->current_currency, $WOOCS->price_num_decimals ); $price = number_format( $price, $precision, $WOOCS->decimal_sep, '' );`?
	 * @todo    [next] (dev) "WPML" + "Frontend info"?
	 * @todo    [now] [!] (fix) rounding issue with WPML
	 * @todo    [next] (dev) do it via filter, e.g. `alg_wc_pgbc_(un)convert_price`?
	 */
	function prepare_price( $price ) {
		// WooCommerce Multilingual (WPML)
		if ( function_exists( 'wcml_is_multi_currency_on' ) && wcml_is_multi_currency_on() ) {
			global $woocommerce_wpml;
			$multi_currency = $woocommerce_wpml->get_multi_currency();
			return $multi_currency->prices->unconvert_price_amount( $price );
		}
		// WOOCS – Currency Switcher for WooCommerce
		if ( class_exists( 'WOOCS' ) ) {
			global $WOOCS;
			if ( $WOOCS ) {
				$currencies = $WOOCS->get_currencies();
				if ( ! empty( $currencies[ $WOOCS->current_currency ]['rate'] ) ) {
					return floatval( $price ) / floatval( $currencies[ $WOOCS->current_currency ]['rate'] );
				}
			}
		}
		return $price;
	}

	/**
	 * convert_order.
	 *
	 * @version 3.2.0
	 * @since   3.2.0
	 *
	 * @todo    [now] (dev) add "Checks"
	 * @todo    [now] (dev) move `$order->set_currency` to `calculate_order()`?
	 * @todo    [now] (dev) note: `sprintf( __( 'Order converted to %s.', 'payment-gateways-by-currency-for-woocommerce' ), $data['convert_price_currency'] )`
	 * @todo    [now] (dev) apply `alg_wc_pgbc_convert_currency_get_shop_currency`?
	 */
	function convert_order( $order ) {
		// Get data
		$data = array(
			'convert_price_currency' => $this->get_converter()->get_gateway_currency( $order->get_payment_method() ),
			'convert_price_gateway'  => $order->get_payment_method(),
			'shop_currency'          => get_option( 'woocommerce_currency' ),
			'convert_price_rate'     => $this->get_converter()->rates->get_gateway_rate( $order->get_payment_method() ),
			'convert_options'        => $this->get_converter()->get_options(),
		);
		// Change order currency
		$order->set_currency( $data['convert_price_currency'] );
		// Calculate order
		$this->calculate_order( $order, $data['convert_options'], 1, $data['convert_price_rate'] );
		// Save new data in order meta
		update_post_meta( $order->get_id(), '_alg_wc_pgbc_data', $data );
		// Return recalculated order
		return $order;
	}

	/**
	 * recalculate_order.
	 *
	 * @version 3.2.0
	 * @since   2.0.0
	 *
	 * @todo    [now] (dev) remove `$gateway  = $data['convert_price_gateway'];`?
	 * @todo    [next] (dev) check if we need to add more types to `get_items()`, e.g. `tax`, `discount`, etc.?
	 * @todo    [next] (dev) recheck if `get_subtotal()` and `get_total()` are really all we need?
	 * @todo    [next] (dev) check if gateway in order hasn't changed?
	 * @todo    [next] (dev) do we always need to `save()`, i.e. for `$renewal_order` as well?
	 * @todo    [later] (dev) better order note?
	 * @todo    [maybe] (feature) bulk recalculate?
	 */
	function recalculate_order( $order, $data = false ) {
		// Get data
		if ( false === $data ) {
			$data = get_post_meta( $order->get_id(), '_alg_wc_pgbc_data', true );
		}
		// Checks
		if (
			'' === $data ||
			! isset( $data['convert_price_currency'], $data['convert_price_gateway'], $data['shop_currency'] ) ||
			empty( $data['convert_price_rate'] ) || false === ( $new_rate = $this->get_converter()->rates->get_gateway_rate( $data['convert_price_gateway'] ) ) ||
			$data['convert_price_currency'] !== $this->get_converter()->get_gateway_currency( $data['convert_price_gateway'] ) ||
			$data['shop_currency'] !== get_option( 'woocommerce_currency' )
		) {
			return $order;
		}
		$gateway  = $data['convert_price_gateway'];
		$old_rate = $data['convert_price_rate'];
		if ( $new_rate == $old_rate ) {
			// Rates haven't changed - no need to recalculate the order
			update_post_meta( $order->get_id(), '_alg_wc_pgbc_data', $data );
			return $order;
		}
		// Calculate order
		$this->calculate_order( $order, $data['convert_options'], $old_rate, $new_rate );
		// Save new data in order meta
		$data['convert_price_rate'] = $new_rate;
		update_post_meta( $order->get_id(), '_alg_wc_pgbc_data', $data );
		// Return recalculated order
		return $order;
	}

	/**
	 * calculate_order.
	 *
	 * @version 3.2.0
	 * @since   3.2.0
	 */
	function calculate_order( $order, $convert_options, $old_rate, $new_rate ) {
		// Get item types
		$item_types = array_keys( array_filter( array(
			'line_item' => true,
			'shipping'  => $convert_options['shipping'],
			'coupon'    => $convert_options['coupon'],
			'fee'       => $convert_options['cart_fee'],
		) ) );
		// Recalculate items
		foreach ( $order->get_items( $item_types ) as $item ) {
			$is_changed = false;
			if ( is_callable( array( $item, 'get_subtotal' ) ) ) {
				$item->set_subtotal( $item->get_subtotal() / $old_rate * $new_rate );
				$is_changed = true;
			}
			if ( is_callable( array( $item, 'get_total' ) ) ) {
				$item->set_total(    $item->get_total()    / $old_rate * $new_rate );
				$is_changed = true;
			}
			if ( $is_changed ) {
				$item->calculate_taxes();
				$item->save();
			}
		}
		// Calculate totals and save order
		$order->calculate_totals();
		$order->add_order_note( sprintf( __( 'Order recalculated. Old rate: %s. New rate: %s.', 'payment-gateways-by-currency-for-woocommerce' ), $old_rate, $new_rate ) );
		$order->save();
	}

	/**
	 * convert_currency.
	 *
	 * @version 3.1.0
	 * @since   1.4.0
	 */
	function convert_currency( $_currency ) {
		if ( $this->get_converter()->do_convert() && ( $current_gateway = $this->get_converter()->get_current_gateway() ) ) {
			if ( false !== ( $currency = $this->get_converter()->get_gateway_currency( $current_gateway ) ) ) {
				return $currency;
			}
		}
		return $_currency;
	}

	/**
	 * convert_currency_symbol.
	 *
	 * @version 3.1.0
	 * @since   1.4.0
	 */
	function convert_currency_symbol( $_currency_symbol, $_currency ) {
		if ( $this->get_converter()->do_convert() && ( $current_gateway = $this->get_converter()->get_current_gateway() ) ) {
			if ( false !== ( $currency_symbol = $this->get_converter()->get_gateway_currency_symbol( $current_gateway ) ) ) {
				if ( $_currency === $this->get_converter()->get_gateway_currency( $current_gateway ) ) {
					return $currency_symbol;
				}
			}
		}
		return $_currency_symbol;
	}

	/**
	 * convert_shipping_free_min_amount.
	 *
	 * @version 2.0.0
	 * @since   2.0.0
	 */
	function convert_shipping_free_min_amount( $is_available, $package, $shipping_method ) {
		if ( ( $converted_min_amount = $this->convert_price( $shipping_method->min_amount ) ) !== $shipping_method->min_amount ) {
			$shipping_method->min_amount = $converted_min_amount;
			remove_filter( 'woocommerce_shipping_free_shipping_is_available', array( $this, 'convert_shipping_free_min_amount' ), PHP_INT_MAX, 3 );
			$is_available = $shipping_method->is_available( $package );
			add_filter( 'woocommerce_shipping_free_shipping_is_available',    array( $this, 'convert_shipping_free_min_amount' ), PHP_INT_MAX, 3 );
		}
		return $is_available;
	}

	/**
	 * convert_shipping_package_rates.
	 *
	 * @version 3.3.0
	 * @since   1.4.0
	 */
	function convert_shipping_package_rates( $package_rates, $multiplier ) {
		$modified_package_rates = array();
		foreach ( $package_rates as $id => $package_rate ) {
			if (
				! empty( $package_rate->cost ) &&
				empty( $package_rate->alg_wc_pgbc_converted ) &&
				apply_filters( 'alg_wc_pgbc_do_convert_shipping_package_rate', true, $package_rate->get_method_id() )
			) {
				$package_rate->alg_wc_pgbc_converted = true;
				$package_rate->cost = $this->prepare_price( $package_rate->cost ) * $multiplier;
				if ( ! empty( $package_rate->taxes ) ) {
					$rate_taxes = $package_rate->taxes;
					foreach ( $rate_taxes as &$tax ) {
						if ( $tax ) {
							$tax = $this->prepare_price( $tax ) * $multiplier;
						}
					}
					$package_rate->taxes = $rate_taxes;
				}
			}
			$modified_package_rates[ $id ] = $package_rate;
		}
		return $modified_package_rates;
	}

	/**
	 * convert_shipping_price.
	 *
	 * @version 3.1.0
	 * @since   1.4.0
	 */
	function convert_shipping_price( $package_rates, $package ) {
		if ( $this->get_converter()->do_convert() && ( $current_gateway = $this->get_converter()->get_current_gateway() ) ) {
			if ( false !== ( $rate = $this->get_converter()->rates->get_gateway_rate( $current_gateway ) ) ) {
				return $this->convert_shipping_package_rates( $package_rates, $rate );
			}
		}
		return $package_rates;
	}

	/**
	 * convert_coupon_amount.
	 *
	 * @version 1.4.0
	 * @since   1.4.0
	 */
	function convert_coupon_amount( $amount, $coupon ) {
		return ( $coupon->is_type( array( 'fixed_cart', 'fixed_product' ) ) ? $this->convert_price( $amount ) : $amount );
	}

	/**
	 * convert_cart_fees.
	 *
	 * @version 3.2.0
	 * @since   1.4.0
	 *
	 * @todo    [maybe] (dev) find an alternative way of implementing this?
	 */
	function convert_cart_fees( $cart ) {
		if ( $cart && $this->get_converter()->do_convert() && ( $current_gateway = $this->get_converter()->get_current_gateway() ) ) {
			if ( false !== ( $rate = $this->get_converter()->rates->get_gateway_rate( $current_gateway ) ) ) {
				$fees = array();
				foreach ( $cart->fees_api()->get_fees() as $fee ) {
					$fees[] = array(
						'name'      => $fee->name,
						'amount'    => $this->prepare_price( $fee->amount ) * $rate,
						'taxable'   => $fee->taxable,
						'tax_class' => $fee->tax_class,
					);
				}
				$cart->fees_api()->set_fees( $fees );
			}
		}
	}

	/**
	 * get_cache_product_id.
	 *
	 * @version 3.0.2
	 * @since   3.0.2
	 *
	 * @todo    [next] [!] (dev) `cache`: `$cache_product_id`: `$product->get_id()` and `$product->get_data()` may be not enough, e.g. when some "add-ons" plugin is used (maybe try using `$product->get_changes()`)?
	 */
	function get_cache_product_id( $product ) {
		switch ( $this->cache_product_id ) {
			case 'product_id_and_data':
				return sprintf( '%s-%s', $product->get_id(), base64_encode( http_build_query( $product->get_data(), ',', ',' ) ) );
			default: // 'product_id'
				return $product->get_id();
		}
	}

	/**
	 * convert_price.
	 *
	 * @version 3.2.0
	 * @since   1.4.0
	 *
	 * @todo    [next] (dev) `cache`: shipping, fees, etc.?
	 * @todo    [maybe] (dev) `cache`: make always enabled, i.e. remove option?
	 * @todo    [maybe] (dev) `session`: maybe we need to save all rates *separately*, i.e. in `convert_shipping_price()`, `convert_coupon_amount()` and `convert_cart_fees()`?
	 */
	function convert_price( $price, $product = false ) {
		if ( $price && $this->get_converter()->do_convert() && ( $current_gateway = $this->get_converter()->get_current_gateway() ) ) {
			if ( false !== ( $rate = $this->get_converter()->rates->get_gateway_rate( $current_gateway ) ) ) {
				$do_cache = ( $this->do_cache_prices && $product && is_a( $product, 'WC_Product' ) );
				if ( $do_cache ) {
					$cache_product_id = $this->get_cache_product_id( $product );
					if ( isset( $this->convert_price_cache[ $current_gateway ][ $cache_product_id ] ) ) {
						return $this->convert_price_cache[ $current_gateway ][ $cache_product_id ];
					}
				}
				$price = $this->prepare_price( $price ) * $rate;
				$this->get_converter()->add_to_session_data( array(
					'convert_price_rate'     => $rate,
					'convert_price_currency' => $this->get_converter()->get_gateway_currency( $current_gateway ),
					'convert_price_gateway'  => $current_gateway,
					'convert_options'        => $this->get_converter()->get_options(),
					'shop_currency'          => apply_filters( 'alg_wc_pgbc_convert_currency_get_shop_currency', get_option( 'woocommerce_currency' ) ),
				) );
				if ( $do_cache ) {
					$this->convert_price_cache[ $current_gateway ][ $cache_product_id ] = $price;
				}
			} else {
				$this->get_converter()->clear_session_data();
			}
		}
		return $price;
	}

}

endif;

return new Alg_WC_PGBC_Convert_Prices();
