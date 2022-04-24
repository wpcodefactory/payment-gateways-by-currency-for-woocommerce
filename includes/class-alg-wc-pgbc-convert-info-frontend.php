<?php
/**
 * Payment Gateway Currency for WooCommerce - Convert - Info Frontend Class
 *
 * @version 3.3.0
 * @since   2.0.0
 *
 * @author  Algoritmika Ltd.
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'Alg_WC_PGBC_Convert_Info_Frontend' ) ) :

class Alg_WC_PGBC_Convert_Info_Frontend {

	/**
	 * Constructor.
	 *
	 * @version 3.0.0
	 * @since   2.0.0
	 *
	 * @todo    [next] (dev) subtotal hash + no ajax?
	 * @todo    [next] [!] (dev) `apply_filters( 'woocommerce_cart_hash', $hash, $cart_session )`?
	 * @todo    [next] (dev) recheck if we need to fix: ajax + empty cart (in mini-cart)?
	 * @todo    [next] [!] (dev) maybe use `wc_price` filter instead?
	 * @todo    [next] (dev) maybe use `woocommerce_cart_totals_before_order_total` instead of `woocommerce_after_cart_totals` (`woocommerce_cart_totals_after_order_total`?)?
	 * @todo    [maybe] (dev) code refactoring?
	 * @todo    [maybe] (dev) rename `%price%` and `%unconverted_price%`?
	 * @todo    [next] [!] (recheck) order details (e.g. on thank you page and in emails)
	 * @todo    [next] [!] (recheck) more position, e.g. `woocommerce_cart_totals_order_total_html`, `wcs_cart_totals_order_total_html`, shipping, mini-cart, etc.
	 */
	function __construct() {
		$this->positions = require_once( 'class-alg-wc-pgbc-convert-info-frontend-positions.php' );
		if ( 'yes' === get_option( 'alg_wc_pgbc_convert_currency_enabled', 'no' ) ) {
			if ( 'yes' === get_option( 'alg_wc_pgbc_convert_currency_show_info', 'no' ) ) {
				// Positions hooks
				$hooks = get_option( 'alg_wc_pgbc_convert_currency_info_hooks', $this->positions->get_default() );
				foreach ( $hooks as $hook ) {
					$position_props = $this->positions->get_props( $hook );
					if ( 'session' === $position_props['data_source'] && 'no' === get_option( 'alg_wc_pgbc_convert_currency_on_checkout', 'yes' ) ) {
						continue;
					}
					$func            = ( isset( $position_props['func'] )            ? $position_props['func']            : 'add_action' );
					$tag             = ( isset( $position_props['tag'] )             ? $position_props['tag']             : $hook );
					$function_to_add = ( isset( $position_props['function_to_add'] ) ? $position_props['function_to_add'] : 'general_info_from_' . $position_props['data_source'] );
					$priority        = ( isset( $position_props['priority'] )        ? $position_props['priority']        : PHP_INT_MAX );
					$accepted_args   = ( isset( $position_props['accepted_args'] )   ? $position_props['accepted_args']   : 0 );
					$func( $tag, array( $this, $function_to_add ), $priority, $accepted_args );
				}
				// Shortcodes
				add_shortcode( 'alg_wc_pgbc_product_price_table', array( $this, 'product_price_table' ) );
				// Compatibility: "WooCommerce Dynamic Pricing & Discounts" by RightPress
				if ( 'yes' === get_option( 'alg_wc_pgbc_convert_currency_info_compatibility_rp_wcdpd', 'no' ) ) {
					add_filter( 'rightpress_product_price_cart_item_display_price_enabled', '__return_false', PHP_INT_MAX );
				}
			}
		}
	}

	/**
	 * product_price_table.
	 *
	 * @version 3.3.0
	 * @since   3.0.0
	 *
	 * @todo    [next] (dev) move to a separate file
	 * @todo    [next] [!] (dev) variable products (i.e. price ranges)
	 * @todo    [next] [!] (dev) variations (i.e. different hook - probably `woocommerce_available_variation` (check my "EAN" or "Wholesale pricing" plugins))
	 */
	function product_price_table( $atts, $content = '' ) {
		$default_atts = array(
			'product_id' => 0,
			'row'        => '<tr><th>%gateway_title%</th><td>%product_price%</td></tr>',
			'before'     => '<table><tbody>' .
				'<tr><th>' . __( 'Gateway', 'payment-gateways-by-currency-for-woocommerce' ) . '</th><th>' . __( 'Price', 'payment-gateways-by-currency-for-woocommerce' ) . '</th></tr>',
			'after'      => '</tbody></table>',
		);
		$atts = shortcode_atts( $default_atts, $atts, 'alg_wc_pgbc_product_price_table' );
		if ( ! empty( $atts['product_id'] ) ) {
			$product = wc_get_product( $atts['product_id'] );
		} else {
			global $product;
		}
		if ( $product && is_a( $product, 'WC_Product' ) && ( $price = $product->get_price() ) && ! empty( $price ) ) {
			$price = alg_wc_pgbc()->core->convert->prices->prepare_price( $price );
			$rows  = array();
			foreach ( WC()->payment_gateways->get_available_payment_gateways() as $gateway => $gateway_data ) {
				if ( false !== ( $rate = alg_wc_pgbc()->core->convert->rates->get_gateway_rate( $gateway ) ) ) {
					$currency = alg_wc_pgbc()->core->convert->get_gateway_currency( $gateway );
					$placeholders = array(
						'%gateway_title%'          => $gateway_data->get_title(),
						'%gateway_admin_title%'    => $gateway_data->get_method_title(),
						'%gateway_rate%'           => $rate,
						'%gateway_currency%'       => $currency,
						'%shop_currency%'          => get_option( 'woocommerce_currency' ),
						'%product_original_price%' => $this->wc_price( $price ),
						'%product_price%'          => $this->wc_price( ( $price * $rate ), array( 'currency' => $currency ) ),
					);
					$rows[] = str_replace( array_keys( $placeholders ), $placeholders, $atts['row'] );
				}
			}
			if ( ! empty( $rows ) ) {
				return $atts['before'] . implode( '', $rows ) . $atts['after'];
			}
		}
	}

	/**
	 * output_shortcode.
	 *
	 * @version 3.0.0
	 * @since   3.0.0
	 */
	function output_shortcode() {
		echo do_shortcode( $this->get_template() );
	}

	/**
	 * order_discount.
	 *
	 * @version 3.0.0
	 * @since   3.0.0
	 *
	 * @see     https://github.com/woocommerce/woocommerce/blob/5.5.1/includes/abstracts/abstract-wc-order.php#L2130
	 * @see     https://github.com/woocommerce/woocommerce/blob/5.5.1/includes/abstracts/abstract-wc-order.php#L2040
	 * @see     https://github.com/woocommerce/woocommerce/blob/5.5.1/includes/abstracts/abstract-wc-order.php#L2012
	 */
	function order_discount( $total_rows, $order, $tax_display ) {
		$placeholders = $this->get_placeholders( get_post_meta( $order->get_id(), '_alg_wc_pgbc_data', true ) );
		if ( empty( $placeholders['%convert_rate%'] ) || ! isset( $placeholders['%shop_currency%'] ) ) {
			return $total_rows;
		}
		if ( isset( $total_rows['discount'] ) ) {
			if ( $order->get_total_discount() > 0 ) {
				$ex_tax = ( 'excl' === ( $tax_display ? $tax_display : get_option( 'woocommerce_tax_display_cart' ) ) && 'excl' === get_option( 'woocommerce_tax_display_cart' ) );
				$total_rows['discount'] = array(
					'label' => __( 'Discount:', 'woocommerce' ),
					'value' => '-' . apply_filters( 'woocommerce_order_discount_to_display', $this->get_value( $order->get_total_discount( $ex_tax ), $placeholders, 'order_discount' ), $order ),
				);
			}
		}
		return $total_rows;
	}

	/**
	 * order_shipping.
	 *
	 * @version 3.0.0
	 * @since   3.0.0
	 *
	 * @see     https://github.com/woocommerce/woocommerce/blob/5.5.1/includes/abstracts/abstract-wc-order.php#L1970
	 */
	function order_shipping( $shipping, $order, $tax_display ) {
		$placeholders = $this->get_placeholders( get_post_meta( $order->get_id(), '_alg_wc_pgbc_data', true ) );
		if ( empty( $placeholders['%convert_rate%'] ) || ! isset( $placeholders['%shop_currency%'] ) ) {
			return $shipping;
		}
		if ( 0 < abs( (float) $order->get_shipping_total() ) ) {
			if ( 'excl' === $tax_display ) {
				// Show shipping excluding tax.
				$shipping = $this->get_value( $order->get_shipping_total(), $placeholders );
				if ( (float) $order->get_shipping_tax() > 0 && $order->get_prices_include_tax() ) {
					$shipping .= apply_filters( 'woocommerce_order_shipping_to_display_tax_label', '&nbsp;<small class="tax_label">' . WC()->countries->ex_tax_or_vat() . '</small>', $order, $tax_display );
				}
			} else {
				// Show shipping including tax.
				$shipping = $this->get_value( ( $order->get_shipping_total() + $order->get_shipping_tax() ), $placeholders );
				if ( (float) $order->get_shipping_tax() > 0 && ! $order->get_prices_include_tax() ) {
					$shipping .= apply_filters( 'woocommerce_order_shipping_to_display_tax_label', '&nbsp;<small class="tax_label">' . WC()->countries->inc_tax_or_vat() . '</small>', $order, $tax_display );
				}
			}
			/* translators: %s: method */
			$shipping .= apply_filters( 'woocommerce_order_shipping_to_display_shipped_via', '&nbsp;<small class="shipped_via">' . sprintf( __( 'via %s', 'woocommerce' ), $order->get_shipping_method() ) . '</small>', $order );
		}
		return $shipping;
	}

	/**
	 * order_taxes.
	 *
	 * @version 3.0.0
	 * @since   3.0.0
	 *
	 * @see     https://github.com/woocommerce/woocommerce/blob/5.5.1/includes/abstracts/abstract-wc-order.php#L2130
	 * @see     https://github.com/woocommerce/woocommerce/blob/5.5.1/includes/abstracts/abstract-wc-order.php#L2092
	 */
	function order_taxes( $total_rows, $order, $tax_display ) {
		$placeholders = $this->get_placeholders( get_post_meta( $order->get_id(), '_alg_wc_pgbc_data', true ) );
		if ( empty( $placeholders['%convert_rate%'] ) || ! isset( $placeholders['%shop_currency%'] ) ) {
			return $total_rows;
		}
		// Tax for tax exclusive prices.
		if ( 'excl' === $tax_display && wc_tax_enabled() ) {
			if ( 'itemized' === get_option( 'woocommerce_tax_total_display' ) ) {
				foreach ( $order->get_tax_totals() as $code => $tax ) {
					if ( isset( $total_rows[ sanitize_title( $code ) ] ) ) {
						$total_rows[ sanitize_title( $code ) ] = array(
							'label' => $tax->label . ':',
							'value' => $this->get_value( $tax->amount, $placeholders, 'order_taxes' ),
						);
					}
				}
			} else {
				if ( isset( $total_rows['tax'] ) ) {
					$total_rows['tax'] = array(
						'label' => WC()->countries->tax_or_vat() . ':',
						'value' => $this->get_value( $order->get_total_tax(), $placeholders, 'order_taxes' ),
					);
				}
			}
		}
		return $total_rows;
	}

	/**
	 * order_fees.
	 *
	 * @version 3.0.0
	 * @since   3.0.0
	 *
	 * @see     https://github.com/woocommerce/woocommerce/blob/5.5.1/includes/abstracts/abstract-wc-order.php#L2130
	 * @see     https://github.com/woocommerce/woocommerce/blob/5.5.1/includes/abstracts/abstract-wc-order.php#L2070
	 */
	function order_fees( $total_rows, $order, $tax_display ) {
		$placeholders = $this->get_placeholders( get_post_meta( $order->get_id(), '_alg_wc_pgbc_data', true ) );
		if ( empty( $placeholders['%convert_rate%'] ) || ! isset( $placeholders['%shop_currency%'] ) ) {
			return $total_rows;
		}
		$fees = $order->get_fees();
		if ( $fees ) {
			foreach ( $fees as $id => $fee ) {
				if ( apply_filters( 'woocommerce_get_order_item_totals_excl_free_fees', empty( $fee['line_total'] ) && empty( $fee['line_tax'] ), $id ) ) {
					continue;
				}
				if ( isset( $total_rows[ 'fee_' . $fee->get_id() ] ) ) {
					$total_rows[ 'fee_' . $fee->get_id() ] = array(
						'label' => $fee->get_name() . ':',
						'value' => $this->get_value( ( 'excl' === $tax_display ? $fee->get_total() : $fee->get_total() + $fee->get_total_tax() ), $placeholders, 'order_fees' ),
					);
				}
			}
		}
		return $total_rows;
	}

	/**
	 * order_line_subtotal.
	 *
	 * @version 3.0.0
	 * @since   3.0.0
	 *
	 * @see     https://github.com/woocommerce/woocommerce/blob/5.5.1/includes/abstracts/abstract-wc-order.php#L1884
	 */
	function order_line_subtotal( $value, $item, $order ) {
		$placeholders = $this->get_placeholders( get_post_meta( $order->get_id(), '_alg_wc_pgbc_data', true ) );
		if ( empty( $placeholders['%convert_rate%'] ) || ! isset( $placeholders['%shop_currency%'] ) ) {
			return $value;
		}
		$inc_tax           = ! ( 'excl' === get_option( 'woocommerce_tax_display_cart' ) );
		$subtotal          = $order->get_line_subtotal( $item, $inc_tax );
		$unconverted_price = $this->get_unconverted_price_html( $subtotal, $placeholders );
		return $this->get_output( array_merge( $placeholders, array( '%price%' => $value, '%unconverted_price%' => $unconverted_price ) ) );
	}

	/**
	 * order_total.
	 *
	 * @version 3.0.0
	 * @since   3.0.0
	 *
	 * @see     https://github.com/woocommerce/woocommerce/blob/5.5.1/includes/abstracts/abstract-wc-order.php#L1909
	 */
	function order_total( $value, $order ) {
		$placeholders = $this->get_placeholders( get_post_meta( $order->get_id(), '_alg_wc_pgbc_data', true ) );
		if ( empty( $placeholders['%convert_rate%'] ) || ! isset( $placeholders['%shop_currency%'] ) ) {
			return $value;
		}
		$unconverted_price = $this->get_unconverted_price_html( $order->get_total(), $placeholders );
		return $this->get_output( array_merge( $placeholders, array( '%price%' => $value, '%unconverted_price%' => $unconverted_price ) ) );
	}

	/**
	 * order_subtotal.
	 *
	 * @version 3.0.0
	 * @since   3.0.0
	 *
	 * @see     https://github.com/woocommerce/woocommerce/blob/5.5.1/includes/abstracts/abstract-wc-order.php#L1921
	 */
	function order_subtotal( $value, $compound, $order ) {
		$placeholders = $this->get_placeholders( get_post_meta( $order->get_id(), '_alg_wc_pgbc_data', true ) );
		if ( empty( $placeholders['%convert_rate%'] ) || ! isset( $placeholders['%shop_currency%'] ) ) {
			return $value;
		}
		$unconverted_price = $this->get_unconverted_price_html( $this->extract_float( $value ), $placeholders );
		return $this->get_output( array_merge( $placeholders, array( '%price%' => $value, '%unconverted_price%' => $unconverted_price ) ) );
	}

	/**
	 * cart_fees.
	 *
	 * @version 3.0.0
	 * @since   3.0.0
	 *
	 * @see     https://github.com/woocommerce/woocommerce/blob/5.5.1/includes/wc-cart-functions.php#L340
	 *
	 * @todo    [next] (dev) maybe it's better/safer to recalculate `$value` again (same in other `cart` functions)?
	 */
	function cart_fees( $value, $fee ) {
		$placeholders = $this->get_placeholders( WC()->session->get( 'alg_wc_pgbc_data', array() ) );
		if ( empty( $placeholders['%convert_rate%'] ) || ! isset( $placeholders['%shop_currency%'] ) ) {
			return $value;
		}
		$cart_totals_fee   = ( WC()->cart->display_prices_including_tax() ? ( $fee->total + $fee->tax ) : $fee->total );
		$unconverted_price = $this->get_unconverted_price_html( $cart_totals_fee, $placeholders );
		return $this->get_output( array_merge( $placeholders, array( '%price%' => $value, '%unconverted_price%' => $unconverted_price ) ) );
	}

	/**
	 * cart_coupons.
	 *
	 * @version 3.0.0
	 * @since   3.0.0
	 *
	 * @see     https://github.com/woocommerce/woocommerce/blob/5.5.1/includes/wc-cart-functions.php#L278
	 */
	function cart_coupons( $value, $coupon ) {
		$placeholders = $this->get_placeholders( WC()->session->get( 'alg_wc_pgbc_data', array() ) );
		if ( empty( $placeholders['%convert_rate%'] ) || ! isset( $placeholders['%shop_currency%'] ) ) {
			return $value;
		}
		if ( is_string( $coupon ) ) {
			$coupon = new WC_Coupon( $coupon );
		}
		$amount = WC()->cart->get_coupon_discount_amount( $coupon->get_code(), WC()->cart->display_cart_ex_tax );
		if ( $coupon->get_free_shipping() && empty( $amount ) ) {
			return $value;
		}
		$unconverted_price = '-' . $this->get_unconverted_price_html( $amount, $placeholders );
		return $this->get_output( array_merge( $placeholders, array( '%price%' => $value, '%unconverted_price%' => $unconverted_price ) ) );
	}

	/**
	 * cart_taxes_itemized.
	 *
	 * @version 3.3.0
	 * @since   3.0.0
	 *
	 * @see     https://github.com/woocommerce/woocommerce/blob/5.5.1/includes/class-wc-cart.php#L861
	 */
	function cart_taxes_itemized( $value, $cart ) {
		$placeholders = $this->get_placeholders( WC()->session->get( 'alg_wc_pgbc_data', array() ), 'cart_taxes' );
		if ( empty( $placeholders['%convert_rate%'] ) || ! isset( $placeholders['%shop_currency%'] ) ) {
			return $value;
		}
		foreach ( $value as &$tax ) {
			$price                 = $tax->formatted_amount;
			$unconverted_price     = $this->wc_price( $tax->amount / $placeholders['%convert_rate%'], array( 'currency' => $placeholders['%shop_currency%'] ) );
			$tax->formatted_amount = $this->get_output( array_merge( $placeholders, array( '%price%' => $price, '%unconverted_price%' => $unconverted_price ) ), 'cart_taxes' );
		}
		return $value;
	}

	/**
	 * cart_taxes_non_itemized.
	 *
	 * @version 3.3.0
	 * @since   3.0.0
	 *
	 * @see     https://github.com/woocommerce/woocommerce/blob/5.5.1/includes/wc-cart-functions.php#L246
	 */
	function cart_taxes_non_itemized( $value ) {
		$placeholders = $this->get_placeholders( WC()->session->get( 'alg_wc_pgbc_data', array() ), 'cart_taxes' );
		if ( empty( $placeholders['%convert_rate%'] ) || ! isset( $placeholders['%shop_currency%'] ) ) {
			return $value;
		}
		$taxes = $this->wc_price( WC()->cart->get_taxes_total() / $placeholders['%convert_rate%'], array( 'currency' => $placeholders['%shop_currency%'] ) );
		return $this->get_output( array_merge( $placeholders, array( '%price%' => $value, '%unconverted_price%' => $taxes ) ), 'cart_taxes' );
	}

	/**
	 * cart_shipping.
	 *
	 * @version 3.3.0
	 * @since   3.0.0
	 *
	 * @see     https://github.com/woocommerce/woocommerce/blob/5.5.1/includes/wc-cart-functions.php#L352
	 *
	 * @todo    [next] (dev) `$_label` to `$value` (everywhere)?
	 */
	function cart_shipping( $_label, $method ) {
		$placeholders = $this->get_placeholders( WC()->session->get( 'alg_wc_pgbc_data', array() ) );
		if ( empty( $placeholders['%convert_rate%'] ) || ! isset( $placeholders['%shop_currency%'] ) ) {
			return $_label;
		}
		$label     = $method->get_label();
		$has_cost  = 0 < $method->cost;
		$hide_cost = ! $has_cost && in_array( $method->get_method_id(), array( 'free_shipping', 'local_pickup' ), true );
		if ( $has_cost && ! $hide_cost ) {
			if ( WC()->cart->display_prices_including_tax() ) {
				$shipping           = $method->cost + $method->get_shipping_tax();
				$price              = $this->wc_price( $shipping );
				$unconverted_price  = $this->get_unconverted_price_html( $shipping, $placeholders );
				$label             .= ': ' . $this->get_output( array_merge( $placeholders, array( '%price%' => $price, '%unconverted_price%' => $unconverted_price ) ) );
				if ( $method->get_shipping_tax() > 0 && ! wc_prices_include_tax() ) {
					$label .= ' <small class="tax_label">' . WC()->countries->inc_tax_or_vat() . '</small>';
				}
			} else {
				$shipping           = $method->cost;
				$price              = $this->wc_price( $shipping );
				$unconverted_price  = $this->get_unconverted_price_html( $shipping, $placeholders );
				$label             .= ': ' . $this->get_output( array_merge( $placeholders, array( '%price%' => $price, '%unconverted_price%' => $unconverted_price ) ) );
				if ( $method->get_shipping_tax() > 0 && wc_prices_include_tax() ) {
					$label .= ' <small class="tax_label">' . WC()->countries->ex_tax_or_vat() . '</small>';
				}
			}
			return $label;
		} else {
			return $_label;
		}
	}

	/**
	 * cart_total.
	 *
	 * @version 3.3.0
	 * @since   3.0.0
	 *
	 * @see     https://github.com/woocommerce/woocommerce/blob/5.5.1/includes/class-wc-cart.php#L289
	 *
	 * @todo    [next] [!] (dev) recheck `tax_label` everywhere
	 */
	function cart_total( $_cart_total ) {
		$placeholders = $this->get_placeholders( WC()->session->get( 'alg_wc_pgbc_data', array() ) );
		if ( empty( $placeholders['%convert_rate%'] ) || ! isset( $placeholders['%shop_currency%'] ) ) {
			return $_cart_total;
		}
		$cart_total = $this->wc_price( WC()->cart->get_total( 'edit' ) / $placeholders['%convert_rate%'], array( 'currency' => $placeholders['%shop_currency%'] ) );
		return $this->get_output( array_merge( $placeholders, array( '%price%' => $_cart_total, '%unconverted_price%' => $cart_total ) ) );
	}

	/**
	 * cart_subtotal.
	 *
	 * @version 3.0.0
	 * @since   3.0.0
	 *
	 * @see     https://github.com/woocommerce/woocommerce/blob/5.5.1/includes/class-wc-cart.php#L1961
	 */
	function cart_subtotal( $_cart_subtotal, $compound, $cart ) {
		$placeholders = $this->get_placeholders( WC()->session->get( 'alg_wc_pgbc_data', array() ) );
		if ( empty( $placeholders['%convert_rate%'] ) || ! isset( $placeholders['%shop_currency%'] ) ) {
			return $_cart_subtotal;
		}
		if ( $compound ) {
			$cart_subtotal = $this->get_value( ( $cart->get_cart_contents_total() + $cart->get_shipping_total() + $cart->get_taxes_total( false, false ) ), $placeholders );
		} elseif ( $cart->display_prices_including_tax() ) {
			$cart_subtotal = $this->get_value( ( $cart->get_subtotal() + $cart->get_subtotal_tax() ), $placeholders );
			if ( $cart->get_subtotal_tax() > 0 && ! wc_prices_include_tax() ) {
				$cart_subtotal .= ' <small class="tax_label">' . WC()->countries->inc_tax_or_vat() . '</small>';
			}
		} else {
			$cart_subtotal = $this->get_value( $cart->get_subtotal(), $placeholders );
			if ( $cart->get_subtotal_tax() > 0 && wc_prices_include_tax() ) {
				$cart_subtotal .= ' <small class="tax_label">' . WC()->countries->ex_tax_or_vat() . '</small>';
			}
		}
		return $cart_subtotal;
	}

	/**
	 * cart_product_subtotal.
	 *
	 * @version 3.0.0
	 * @since   3.0.0
	 *
	 * @see     https://github.com/woocommerce/woocommerce/blob/5.5.1/includes/class-wc-cart.php#L2011
	 */
	function cart_product_subtotal( $_product_subtotal, $product, $quantity, $cart ) {
		$placeholders = $this->get_placeholders( WC()->session->get( 'alg_wc_pgbc_data', array() ) );
		if ( empty( $placeholders['%convert_rate%'] ) || ! isset( $placeholders['%shop_currency%'] ) ) {
			return $_product_subtotal;
		}
		if ( $product->is_taxable() ) {
			if ( $cart->display_prices_including_tax() ) {
				$product_subtotal = $this->get_value( wc_get_price_including_tax( $product, array( 'qty' => $quantity ) ), $placeholders );
				if ( ! wc_prices_include_tax() && $cart->get_subtotal_tax() > 0 ) {
					$product_subtotal .= ' <small class="tax_label">' . WC()->countries->inc_tax_or_vat() . '</small>';
				}
			} else {
				$product_subtotal = $this->get_value( wc_get_price_excluding_tax( $product, array( 'qty' => $quantity ) ), $placeholders );
				if ( wc_prices_include_tax() && $cart->get_subtotal_tax() > 0 ) {
					$product_subtotal .= ' <small class="tax_label">' . WC()->countries->ex_tax_or_vat() . '</small>';
				}
			}
		} else {
			$product_subtotal = $this->get_value( ( $product->get_price() * $quantity ), $placeholders );
		}
		return $product_subtotal;
	}

	/**
	 * cart_product_price.
	 *
	 * @version 3.3.0
	 * @since   3.0.0
	 *
	 * @see     https://github.com/woocommerce/woocommerce/blob/5.5.1/includes/class-wc-cart.php#L1991
	 */
	function cart_product_price( $_product_price, $product ) {
		$placeholders = $this->get_placeholders( WC()->session->get( 'alg_wc_pgbc_data', array() ) );
		if ( empty( $placeholders['%convert_rate%'] ) || ! isset( $placeholders['%shop_currency%'] ) ) {
			return $_product_price;
		}
		if ( WC()->cart->display_prices_including_tax() ) {
			$product_price = wc_get_price_including_tax( $product );
		} else {
			$product_price = wc_get_price_excluding_tax( $product );
		}
		$product_price = $product_price / $placeholders['%convert_rate%'];
		$product_price = $this->wc_price( $product_price, array( 'currency' => $placeholders['%shop_currency%'] ) );
		return $this->get_output( array_merge( $placeholders, array( '%price%' => $_product_price, '%unconverted_price%' => $product_price ) ) );
	}

	/**
	 * general_info_from_order.
	 *
	 * @version 2.0.0
	 * @since   2.0.0
	 */
	function general_info_from_order( $order ) {
		$this->general_info( get_post_meta( $order->get_id(), '_alg_wc_pgbc_data', true ) );
	}

	/**
	 * general_info_from_session.
	 *
	 * @version 2.0.0
	 * @since   2.0.0
	 */
	function general_info_from_session() {
		$this->general_info( WC()->session->get( 'alg_wc_pgbc_data', array() ) );
	}

	/**
	 * general_info.
	 *
	 * @version 3.0.0
	 * @since   2.0.0
	 */
	function general_info( $data ) {
		$placeholders = $this->get_placeholders( $data );
		if ( ! empty( $placeholders ) ) {
			echo $this->get_output( $placeholders );
		}
	}

	/**
	 * is_mini_cart.
	 *
	 * @version 3.0.0
	 * @since   3.0.0
	 *
	 * @todo    [maybe] (dev) replace `! is_checkout()` with: `isset( $_REQUEST['wc-ajax'] ) && 'update_order_review'     !== $_REQUEST['wc-ajax']`?
	 * @todo    [maybe] (dev) replace `! is_checkout()` with: `isset( $_REQUEST['wc-ajax'] ) && 'get_refreshed_fragments' === $_REQUEST['wc-ajax']`?
	 */
	function is_mini_cart() {
		return ( is_ajax() && ! is_checkout() );
	}

	/**
	 * is_scope.
	 *
	 * @version 3.0.0
	 * @since   3.0.0
	 */
	function is_scope( $scope ) {
		return ( 'is_mini_cart' === $scope ? $this->is_mini_cart() : $scope() );
	}

	/**
	 * is_scope_position.
	 *
	 * @version 3.0.0
	 * @since   3.0.0
	 *
	 * @return  bool|string `false` if not in scope; otherwise string value of the `scope`
	 *
	 * @todo    [next] (dev) better function name?
	 */
	function is_scope_position( $positions, $position = false ) {
		$_position = ( $position ? $position : current_filter() );
		foreach ( $positions as $scope => $_positions ) {
			if ( in_array( $_position, $_positions ) && $this->is_scope( $scope ) ) {
				return $scope;
			}
		}
		return false;
	}

	/**
	 * get_placeholders.
	 *
	 * @version 3.1.0
	 * @since   2.0.0
	 *
	 * @todo    [next] [!] (feature) "Order total" **backend** position (i.e. in admin orders list) (maybe in "Convert Currency: Admin Options > Order total in admin" option?)
	 * @todo    [next] [!] (dev) `do_convert`: for `session` only?
	 * @todo    [maybe] (dev) `exceptions`: maybe remove it, and use different positions instead, i.e. "Cart product price: Cart" - "Cart product price: Checkout" - "Cart product price: AJAX", etc.?
	 * @todo    [next] (feature) `exceptions`: `is_order()`, `is_email()`?
	 * @todo    [next] (dev) `exceptions`: to a separate function?
	 * @todo    [next] (dev) `required_placeholders`: Check required placeholders: `if ( ! empty( $required_placeholders ) ) { $intersection = array_intersect_key( $required_placeholders, $placeholders ); if ( count( $intersection ) != count( $required_placeholders ) ) { return array(); } }`
	 * @todo    [next] (dev) `$data = false` (then take it from session)
	 * @todo    [next] (feature) better placeholders, e.g. `convert_price_gateway_title` (i.e. in addition to `convert_price_gateway`)
	 * @todo    [next] (feature) more placeholders, e.g. `currency_symbol`
	 */
	function get_placeholders( $data, $position = false ) {
		// Check "convert on"
		if ( ! alg_wc_pgbc()->core->convert->do_convert() ) {
			return array();
		}
		// Check position exceptions
		if ( false !== $this->is_scope_position( get_option( 'alg_wc_pgbc_convert_currency_info_hooks_exceptions', array() ), $position ) ) {
			return array();
		}
		// Get placeholders
		$placeholders = array();
		if (
			isset( $data['convert_price_rate'], $data['convert_price_currency'], $data['shop_currency'] ) &&
			( 1 != $data['convert_price_rate'] || $data['convert_price_currency'] != $data['shop_currency'] )
		) {
			foreach ( $data as $key => $value ) {
				$placeholders[ "%{$key}%" ] = ( ! is_array( $value ) ? $value : implode( ', ', $value ) );
			}
			// Aliases
			$placeholders['%convert_rate%']     = $data['convert_price_rate'];
			$placeholders['%convert_currency%'] = $data['convert_price_currency'];
		}
		// Return placeholders
		return $placeholders;
	}

	/**
	 * get_output.
	 *
	 * @version 3.0.0
	 * @since   3.0.0
	 */
	function get_output( $placeholders, $position = false ) {
		$position     = ( $position ? $position : current_filter() );
		$placeholders = apply_filters( 'alg_wc_pgbc_convert_currency_info_get_output_placeholders', $placeholders, $position, $this );
		return str_replace( array_keys( $placeholders ), $placeholders, $this->get_template( $position ) );
	}

	/**
	 * add_extra_template_position_scope.
	 *
	 * @version 3.0.0
	 * @since   3.0.0
	 *
	 * @todo    [next] (dev) check if this should be used anywhere else?
	 */
	function add_extra_template_position_scope( $position ) {
		return ( false !== ( $scope = $this->is_scope_position( get_option( 'alg_wc_pgbc_convert_currency_info_hooks_extra_template', array() ), $position ) ) ?
			$position . '_' . $scope : $position );
	}

	/**
	 * get_template.
	 *
	 * @version 3.0.0
	 * @since   2.0.0
	 */
	function get_template( $position = false ) {
		$_position  = ( $position ? $position : current_filter() );
		$_templates = get_option( 'alg_wc_pgbc_convert_currency_info_hooks_custom_templates', array() );
		if ( ! in_array( $_position, $_templates ) ) {
			return $this->positions->get_default_template( $_position );
		}
		$templates  = get_option( 'alg_wc_pgbc_convert_currency_info_template', array() );
		$_position  = $this->add_extra_template_position_scope( $_position );
		return ( isset( $templates[ $_position ] ) ? $templates[ $_position ] : $this->positions->get_default_template( $_position ) );
	}

	/**
	 * extract_float.
	 *
	 * @version 3.0.0
	 * @since   3.0.0
	 *
	 * @see     https://www.php.net/manual/en/function.floatval.php#114486
	 *
	 * @todo    [next] [!] (dev) is it safe to use? if yes, then use everywhere?
	 */
	function extract_float( $value ) {
		// Prepare: remove `<span>`, `<bdi>`, etc. tags, and remove currency symbol
		$value     = strip_tags( $value );
		$value     = str_replace( get_woocommerce_currency_symbols(), '', $value );
		// Find sep
		$dot_pos   = strrpos( $value, '.' );
		$comma_pos = strrpos( $value, ',' );
		$sep       = ( ( $dot_pos && ( $dot_pos > $comma_pos ) ) ? $dot_pos : ( ( $comma_pos && ( $comma_pos > $dot_pos ) ) ? $comma_pos : false ) );
		// Final result
		$pattern   = '/[^0-9]/';
		if ( ! $sep ) {
			$value    = preg_replace( $pattern, '', $value );
		} else {
			$int_part = preg_replace( $pattern, '', substr( $value, 0, $sep ) );
			$dec_part = preg_replace( $pattern, '', substr( $value, ( $sep + 1 ) ) );
			$value    = sprintf( '%s.%s', $int_part, $dec_part );
		}
		return floatval( $value );
	}

	/**
	 * get_unconverted_price_html.
	 *
	 * @version 3.3.0
	 * @since   3.0.0
	 *
	 * @todo    [next] (dev) merge it with `extract_float()`? || use this everywhere
	 */
	function get_unconverted_price_html( $value, $data ) {
		return $this->wc_price( $value / $data['%convert_rate%'], array( 'currency' => $data['%shop_currency%'] ) );
	}

	/**
	 * get_value.
	 *
	 * @version 3.3.0
	 * @since   3.0.0
	 *
	 * @todo    [next] (dev) better function naming
	 * @todo    [next] (dev) use this everywhere
	 */
	function get_value( $value, $placeholders, $position = false ) {
		return $this->get_output( array_merge( $placeholders, array(
				'%price%'             => $this->wc_price( $value ),
				'%unconverted_price%' => $this->get_unconverted_price_html( $value, $placeholders ) ) ),
			$position );
	}

	/**
	 * wc_price.
	 *
	 * @version 3.3.0
	 * @since   3.3.0
	 */
	function wc_price( $price, $args = array() ) {
		$do_woocs = false;
		if ( class_exists( 'WOOCS' ) ) {
			global $WOOCS;
			if ( $WOOCS ) {
				if ( 9999 === has_filter( 'woocommerce_currency_symbol', array( $WOOCS, 'woocommerce_currency_symbol' ) ) ) {
					$do_woocs = remove_filter( 'woocommerce_currency_symbol', array( $WOOCS, 'woocommerce_currency_symbol' ), 9999 );
				}
			}
		}
		$price = wc_price( $price, $args );
		if ( $do_woocs ) {
			add_filter( 'woocommerce_currency_symbol', array( $WOOCS, 'woocommerce_currency_symbol' ), 9999 );
		}
		return $price;
	}

}

endif;

return new Alg_WC_PGBC_Convert_Info_Frontend();
