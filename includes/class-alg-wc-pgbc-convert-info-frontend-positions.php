<?php
/**
 * Payment Gateway Currency for WooCommerce - Convert - Info Frontend Positions Class
 *
 * @version 3.0.0
 * @since   3.0.0
 *
 * @author  Algoritmika Ltd.
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'Alg_WC_PGBC_Convert_Info_Frontend_Positions' ) ) :

class Alg_WC_PGBC_Convert_Info_Frontend_Positions {

	/**
	 * Constructor.
	 *
	 * @version 3.0.0
	 * @since   3.0.0
	 */
	function __construct() {
		return true;
	}

	/**
	 * get_all.
	 *
	 * @version 3.0.0
	 * @since   2.0.0
	 */
	function get_all() {
		return array(
			'woocommerce_cart_product_price'                      => __( 'Cart product price', 'payment-gateways-by-currency-for-woocommerce' ),
			'woocommerce_cart_product_subtotal'                   => __( 'Cart product subtotal', 'payment-gateways-by-currency-for-woocommerce' ),
			'woocommerce_cart_subtotal'                           => __( 'Cart subtotal', 'payment-gateways-by-currency-for-woocommerce' ),
			'woocommerce_coupon_discount_amount_html'             => __( 'Cart totals: Coupons', 'payment-gateways-by-currency-for-woocommerce' ),
			'woocommerce_cart_shipping_method_full_label'         => __( 'Cart totals: Shipping', 'payment-gateways-by-currency-for-woocommerce' ),
			'woocommerce_cart_totals_fee_html'                    => __( 'Cart totals: Fees', 'payment-gateways-by-currency-for-woocommerce' ),
			'cart_taxes'                                          => __( 'Cart totals: Taxes', 'payment-gateways-by-currency-for-woocommerce' ),
			'woocommerce_cart_total'                              => __( 'Cart total', 'payment-gateways-by-currency-for-woocommerce' ),
			'woocommerce_cart_totals_after_order_total'           => __( 'Cart totals: After order total', 'payment-gateways-by-currency-for-woocommerce' ),
			'woocommerce_review_order_after_order_total'          => __( 'Checkout: After order total', 'payment-gateways-by-currency-for-woocommerce' ),
			'woocommerce_order_formatted_line_subtotal'           => __( 'Order product subtotal', 'payment-gateways-by-currency-for-woocommerce' ),
			'woocommerce_order_details_after_order_table_items'   => __( 'Order: Before subtotal', 'payment-gateways-by-currency-for-woocommerce' ),
			'woocommerce_order_subtotal_to_display'               => __( 'Order subtotal', 'payment-gateways-by-currency-for-woocommerce' ),
			'order_discount'                                      => __( 'Order totals: Discount', 'payment-gateways-by-currency-for-woocommerce' ),
			'woocommerce_order_shipping_to_display'               => __( 'Order totals: Shipping', 'payment-gateways-by-currency-for-woocommerce' ),
			'order_fees'                                          => __( 'Order totals: Fees', 'payment-gateways-by-currency-for-woocommerce' ),
			'order_taxes'                                         => __( 'Order totals: Taxes', 'payment-gateways-by-currency-for-woocommerce' ),
			'woocommerce_get_formatted_order_total'               => __( 'Order total', 'payment-gateways-by-currency-for-woocommerce' ),
			'woocommerce_email_after_order_table'                 => __( 'Email: After order details', 'payment-gateways-by-currency-for-woocommerce' ),
			'woocommerce_single_product_summary'                  => __( 'Single product summary', 'payment-gateways-by-currency-for-woocommerce' ),
		);
	}

	/**
	 * get_default_template.
	 *
	 * @version 3.0.0
	 * @since   2.0.0
	 *
	 * @todo    [next] (dev) change `%price% <small>(%unconverted_price%)</small>` to e.g. `%price%<br><small>%unconverted_price%</small>`, etc.? no need to add `bdi` then?
	 * @todo    [next] (dev) remove `default:`?
	 * @todo    [next] (dev) move to `get_position_props()`?
	 */
	function get_default_template( $position ) {
		switch ( $position ) {
			case 'woocommerce_cart_product_price':
			case 'woocommerce_cart_product_subtotal':
			case 'woocommerce_cart_subtotal':
			case 'woocommerce_cart_total':
			case 'cart_taxes':
			case 'woocommerce_coupon_discount_amount_html':
			case 'woocommerce_cart_totals_fee_html':
			case 'woocommerce_order_subtotal_to_display':
			case 'order_discount':
			case 'woocommerce_order_shipping_to_display':
			case 'order_fees':
			case 'order_taxes':
			case 'woocommerce_get_formatted_order_total':
			case 'woocommerce_order_formatted_line_subtotal':
			case 'woocommerce_cart_shipping_method_full_label':
				return '%price% <small>(%unconverted_price%)</small>';
			case 'woocommerce_email_after_order_table':
				return '<p><strong>%shop_currency%%convert_currency%:</strong> %convert_rate%</p>';
			case 'woocommerce_single_product_summary':
				return '[alg_wc_pgbc_product_price_table]';
			default:
				return '<tr><th>%shop_currency%%convert_currency%</th><td>%convert_rate%</td></tr>';
		}
	}

	/**
	 * get_props.
	 *
	 * @version 3.0.0
	 * @since   2.0.0
	 *
	 * @todo    [next] (dev) remove `default:`?
	 * @todo    [next] (dev) add `function_to_add` everywhere?
	 * @todo    [next] (dev) do we really need `func` (we could always use `add_filter()`)
	 */
	function get_props( $position ) {
		switch ( $position ) {
			case 'woocommerce_cart_product_price':
				return array(
					'data_source'     => 'session',
					'func'            => 'add_filter',
					'function_to_add' => 'cart_product_price',
					'accepted_args'   => 2,
				);
			case 'woocommerce_cart_product_subtotal':
				return array(
					'data_source'     => 'session',
					'func'            => 'add_filter',
					'function_to_add' => 'cart_product_subtotal',
					'accepted_args'   => 4,
				);
			case 'woocommerce_cart_subtotal':
				return array(
					'data_source'     => 'session',
					'func'            => 'add_filter',
					'function_to_add' => 'cart_subtotal',
					'accepted_args'   => 3,
				);
			case 'woocommerce_cart_total':
				return array(
					'data_source'     => 'session',
					'func'            => 'add_filter',
					'function_to_add' => 'cart_total',
					'accepted_args'   => 2,
				);
			case 'woocommerce_cart_shipping_method_full_label':
				return array(
					'data_source'     => 'session',
					'func'            => 'add_filter',
					'function_to_add' => 'cart_shipping',
					'accepted_args'   => 2,
				);
			case 'cart_taxes':
				$is_itemized = ( 'itemized' === get_option( 'woocommerce_tax_total_display' ) );
				return array(
					'data_source'     => 'session',
					'func'            => 'add_filter',
					'function_to_add' => ( $is_itemized ? 'cart_taxes_itemized'         : 'cart_taxes_non_itemized' ),
					'accepted_args'   => ( $is_itemized ? 2                             : 1 ),
					'tag'             => ( $is_itemized ? 'woocommerce_cart_tax_totals' : 'woocommerce_cart_totals_taxes_total_html' ),
				);
			case 'woocommerce_coupon_discount_amount_html':
				return array(
					'data_source'     => 'session',
					'func'            => 'add_filter',
					'function_to_add' => 'cart_coupons',
					'accepted_args'   => 2,
				);
			case 'woocommerce_cart_totals_fee_html':
				return array(
					'data_source'     => 'session',
					'func'            => 'add_filter',
					'function_to_add' => 'cart_fees',
					'accepted_args'   => 2,
				);
			case 'woocommerce_order_subtotal_to_display':
				return array(
					'data_source'     => 'order',
					'func'            => 'add_filter',
					'function_to_add' => 'order_subtotal',
					'accepted_args'   => 3,
				);
			case 'order_discount':
				return array(
					'data_source'     => 'order',
					'func'            => 'add_filter',
					'function_to_add' => 'order_discount',
					'accepted_args'   => 3,
					'tag'             => 'woocommerce_get_order_item_totals',
				);
			case 'woocommerce_order_shipping_to_display':
				return array(
					'data_source'     => 'order',
					'func'            => 'add_filter',
					'function_to_add' => 'order_shipping',
					'accepted_args'   => 3,
				);
			case 'order_fees':
				return array(
					'data_source'     => 'order',
					'func'            => 'add_filter',
					'function_to_add' => 'order_fees',
					'accepted_args'   => 3,
					'tag'             => 'woocommerce_get_order_item_totals',
				);
			case 'order_taxes':
				return array(
					'data_source'     => 'order',
					'func'            => 'add_filter',
					'function_to_add' => 'order_taxes',
					'accepted_args'   => 3,
					'tag'             => 'woocommerce_get_order_item_totals',
				);
			case 'woocommerce_get_formatted_order_total':
				return array(
					'data_source'     => 'order',
					'func'            => 'add_filter',
					'function_to_add' => 'order_total',
					'accepted_args'   => 2,
				);
			case 'woocommerce_order_formatted_line_subtotal':
				return array(
					'data_source'     => 'order',
					'func'            => 'add_filter',
					'function_to_add' => 'order_line_subtotal',
					'accepted_args'   => 3,
				);
			case 'woocommerce_order_details_after_order_table_items':
			case 'woocommerce_email_after_order_table':
				return array(
					'data_source'     => 'order',
					'accepted_args'   => 1,
				);
			case 'woocommerce_single_product_summary':
				return array(
					'data_source'     => 'none',
					'func'            => 'add_action',
					'function_to_add' => 'output_shortcode',
					'accepted_args'   => 0,
					'priority'        => 11,
				);
			default:
				return array(
					'data_source'     => 'session',
				);
		}
	}

	/**
	 * get_default.
	 *
	 * @version 3.0.0
	 * @since   2.0.0
	 *
	 * @todo    [next] (dev) is this a good idea - maybe remove `woocommerce_single_product_summary` at least?
	 */
	function get_default() {
		return array_keys( $this->get_all() );
	}

	/**
	 * get_placeholders_desc.
	 *
	 * @version 3.0.0
	 * @since   3.0.0
	 */
	function get_placeholders_desc( $position ) {
		// Shortcodes
		if (
			in_array( $position, array(
				'woocommerce_single_product_summary',
			) )
		) {
			$shortcodes = array( '[alg_wc_pgbc_product_price_table]' );
			return sprintf( __( 'Shortcodes: %s.', 'payment-gateways-by-currency-for-woocommerce' ), '<code>' . implode( '</code>, <code>', $shortcodes ) . '</code>' );
		}
		// Placeholders
		$placeholders = array( '%convert_rate%', '%convert_currency%', '%shop_currency%' );
		if ( in_array( $position, array(
				'woocommerce_cart_product_price',
				'woocommerce_cart_product_subtotal',
				'woocommerce_cart_subtotal',
				'woocommerce_cart_total',
				'woocommerce_cart_shipping_method_full_label',
				'cart_taxes',
				'woocommerce_coupon_discount_amount_html',
				'woocommerce_cart_totals_fee_html',
				'woocommerce_order_formatted_line_subtotal',
				'woocommerce_order_subtotal_to_display',
				'order_discount',
				'woocommerce_order_shipping_to_display',
				'order_fees',
				'order_taxes',
				'woocommerce_get_formatted_order_total',
			) )
		) {
			$placeholders = array_merge( array( '%price%', '%unconverted_price%' ), $placeholders );
		}
		return sprintf( __( 'Placeholders: %s.', 'payment-gateways-by-currency-for-woocommerce' ), '<code>' . implode( '</code>, <code>', $placeholders ) . '</code>' );
	}

}

endif;

return new Alg_WC_PGBC_Convert_Info_Frontend_Positions();
