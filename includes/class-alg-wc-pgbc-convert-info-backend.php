<?php
/**
 * Payment Gateway Currency for WooCommerce - Convert - Info Backend Class
 *
 * @version 3.9.0
 * @since   3.0.0
 *
 * @author  Algoritmika Ltd.
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Alg_WC_PGBC_Convert_Info_Backend' ) ) :

class Alg_WC_PGBC_Convert_Info_Backend {

	/**
	 * Constructor.
	 *
	 * @version 3.9.0
	 * @since   3.0.0
	 */
	function __construct() {

		if ( 'yes' === get_option( 'alg_wc_pgbc_convert_currency_enabled', 'no' ) ) {

			if ( is_admin() ) {

				// Order meta box
				if ( 'yes' === get_option( 'alg_wc_pgbc_convert_currency_order_meta_box', 'yes' ) ) {
					add_action( 'add_meta_boxes', array( $this, 'add_order_pgbc_data_meta_box' ), 10, 2 );
					add_action( 'admin_init',     array( $this, 'recalculate_order_action' ) );
					add_action( 'admin_notices',  array( $this, 'order_recalculated_notice' ) );
				}

				// Currency symbol in admin
				if ( 'yes' === get_option( 'alg_wc_pgbc_convert_currency_admin_symbol', 'no' ) ) {
					add_filter( 'woocommerce_currency_symbol', array( $this, 'convert_currency_symbol_in_admin' ), PHP_INT_MAX, 2 );
				}

				// Order formatted total
				if ( 'yes' === get_option( 'alg_wc_pgbc_convert_currency_admin_order_total', 'no' ) ) {
					add_filter( 'woocommerce_get_formatted_order_total', array( $this, 'get_formatted_order_total' ), PHP_INT_MAX, 2 );
				}

				// Admin order list column
				if ( 'yes' === get_option( 'alg_wc_pgbc_convert_currency_admin_orders_list_total', 'no' ) ) {
					add_filter( 'manage_edit-shop_order_columns',        array( $this, 'add_orders_list_column_total' ) );
					add_action( 'manage_shop_order_posts_custom_column', array( $this, 'render_orders_list_column_total' ), 10, 2 );
				}

				// Number of decimals in admin
				if ( 'yes' === get_option( 'alg_wc_pgbc_convert_currency_admin_num_decimals', 'no' ) ) {
					add_filter( 'wc_get_price_decimals', array( $this, 'convert_currency_decimals' ), PHP_INT_MAX );
				}

			}

		}

	}

	/**
	 * convert_currency_decimals.
	 *
	 * @version 3.9.0
	 * @since   3.9.0
	 *
	 * @todo    (dev) `wc_get_order()`: `get_the_ID()`?
	 * @todo    (dev) limit this to *orders* only?
	 */
	function convert_currency_decimals( $decimals ) {

		// Get order
		remove_filter( 'wc_get_price_decimals', array( $this, 'convert_currency_decimals' ), PHP_INT_MAX );
		$order = wc_get_order();
		add_filter( 'wc_get_price_decimals', array( $this, 'convert_currency_decimals' ), PHP_INT_MAX );

		// Gateway currency num decimals
		if ( $order && ( $current_gateway = $order->get_payment_method() ) ) {
			if ( false !== ( $currency_decimals = alg_wc_pgbc()->core->convert->get_gateway_currency_num_decimals( $current_gateway ) ) ) {
				return $currency_decimals;
			}
		}

		// No changes
		return $decimals;

	}

	/**
	 * add_orders_list_column_total.
	 *
	 * @version 3.7.0
	 * @since   3.7.0
	 */
	function add_orders_list_column_total( $columns ) {
		$columns['alg_wc_pgbc_order_total'] = esc_html__( 'Original total', 'payment-gateways-by-currency-for-woocommerce' );
		return $columns;
	}

	/**
	 * render_orders_list_column_total.
	 *
	 * @version 3.7.0
	 * @since   3.7.0
	 *
	 * @todo    (dev) override the standard "Total" column instead?
	 */
	function render_orders_list_column_total( $column, $post_id ) {
		if (
			'alg_wc_pgbc_order_total' === $column &&
			( $order = wc_get_order( $post_id ) ) &&
			( $data = alg_wc_pgbc()->core->convert->get_order_data( $order ) ) &&
			! empty( $data['convert_price_rate'] ) &&
			isset( $data['shop_currency'] )
		) {
			echo wc_price( ( $order->get_total() / $data['convert_price_rate'] ), array( 'currency' => $data['shop_currency'] ) );
		}
	}

	/**
	 * get_formatted_order_total.
	 *
	 * @version 3.7.0
	 * @since   2.0.0
	 *
	 * @todo    (feature) add more placeholders?
	 */
	function get_formatted_order_total( $formatted_total, $order ) {
		$template     = get_option( 'alg_wc_pgbc_convert_currency_admin_order_total_format', '%order_total% %currency%' );
		$data         = alg_wc_pgbc()->core->convert->get_order_data( $order );
		$placeholders = array(
			'%order_total%'          => $formatted_total,
			'%currency%'             => $order->get_currency(),
			'%currency_symbol%'      => get_woocommerce_currency_symbol( $order->get_currency() ),
			'%convert_price_rate%'   => ( $data && isset( $data['convert_price_rate'] ) ? $data['convert_price_rate'] : '' ),
			'%order_total_original%' => ( $data && ! empty( $data['convert_price_rate'] ) && isset( $data['shop_currency'] ) ?
				wc_price( ( $order->get_total() / $data['convert_price_rate'] ), array( 'currency' => $data['shop_currency'] ) ) : $formatted_total ),
		);
		return str_replace( array_keys( $placeholders ), $placeholders, $template );
	}

	/**
	 * convert_currency_symbol_in_admin.
	 *
	 * @version 2.0.0
	 * @since   2.0.0
	 *
	 * @todo    (dev) limit this to *orders* only?
	 */
	function convert_currency_symbol_in_admin( $_currency_symbol, $_currency ) {
		foreach ( alg_wc_pgbc()->core->convert->get_gateway_currencies() as $gateway => $currency ) {
			if ( '' !== $currency && $currency === $_currency ) {
				if ( false !== ( $currency_symbol = alg_wc_pgbc()->core->convert->get_gateway_currency_symbol( $gateway ) ) ) {
					return $currency_symbol;
				}
			}
		}
		return $_currency_symbol;
	}

	/**
	 * recalculate_order_action.
	 *
	 * @version 3.2.0
	 * @since   2.0.0
	 *
	 * @todo    (dev) `alg_wc_pgbc_convert_order_id`: notice
	 * @todo    (dev) better notices (i.e., errors)
	 */
	function recalculate_order_action() {

		if ( ! empty( $_GET['alg_wc_pgbc_recalculate_order_id'] ) ) {
			if ( ! isset( $_REQUEST['_wpnonce_alg_wc_pgbc'] ) || ! wp_verify_nonce( $_REQUEST['_wpnonce_alg_wc_pgbc'], 'recalculate' ) ) {
				wp_die( __( 'Nonce verification failed. Please try again.', 'payment-gateways-by-currency-for-woocommerce' ) );
			}
			if ( current_user_can( 'manage_woocommerce' ) && ( $order_id = intval( $_GET['alg_wc_pgbc_recalculate_order_id'] ) ) && ( $order = wc_get_order( $order_id ) ) ) {
				alg_wc_pgbc()->core->convert->prices->recalculate_order( $order );
			}
			wp_safe_redirect( remove_query_arg( array( 'alg_wc_pgbc_recalculate_order_id', '_wpnonce_alg_wc_pgbc' ), add_query_arg( 'alg_wc_pgbc_order_recalculated', true ) ) );
			exit;
		}

		if ( ! empty( $_GET['alg_wc_pgbc_convert_order_id'] ) ) {
			if ( ! isset( $_REQUEST['_wpnonce_alg_wc_pgbc'] ) || ! wp_verify_nonce( $_REQUEST['_wpnonce_alg_wc_pgbc'], 'convert' ) ) {
				wp_die( __( 'Nonce verification failed. Please try again.', 'payment-gateways-by-currency-for-woocommerce' ) );
			}
			if ( current_user_can( 'manage_woocommerce' ) && ( $order_id = intval( $_GET['alg_wc_pgbc_convert_order_id'] ) ) && ( $order = wc_get_order( $order_id ) ) ) {
				alg_wc_pgbc()->core->convert->prices->convert_order( $order );
			}
			wp_safe_redirect( remove_query_arg( array( 'alg_wc_pgbc_convert_order_id', '_wpnonce_alg_wc_pgbc' ), add_query_arg( 'alg_wc_pgbc_order_recalculated', true ) ) );
			exit;
		}

	}

	/**
	 * order_recalculated_notice.
	 *
	 * @version 2.0.0
	 * @since   2.0.0
	 */
	function order_recalculated_notice() {
		if ( isset( $_REQUEST['alg_wc_pgbc_order_recalculated'] ) ) {
			echo '<div class="notice notice-success is-dismissible"><p>' . __( 'Order recalculated.', 'payment-gateways-by-currency-for-woocommerce' ) . '</p></div>';
		}
	}

	/**
	 * add_order_pgbc_data_meta_box.
	 *
	 * @version 3.6.0
	 * @since   2.0.0
	 *
	 * @todo    (dev) rethink `alg_wc_pgbc_convert_currency_order_meta_box_convert`
	 */
	function add_order_pgbc_data_meta_box( $post_type, $post ) {
		$data = alg_wc_pgbc()->core->convert->get_order_data( wc_get_order( $post->ID ) );
		if ( ! empty( $data['convert_price_rate'] ) || 'yes' === get_option( 'alg_wc_pgbc_convert_currency_order_meta_box_convert', 'no' ) ) {
			add_meta_box(
				'alg-wc-pgbc-meta-box',
				__( 'Currency Conversion', 'payment-gateways-by-currency-for-woocommerce' ),
				array( $this, 'create_order_pgbc_data_meta_box' ),
				'shop_order',
				'side',
				'default',
				$data
			);
		}
	}

	/**
	 * create_order_pgbc_data_meta_box.
	 *
	 * @version 3.7.0
	 * @since   2.0.0
	 *
	 * @todo    (dev) `alg_wc_pgbc_convert_currency_order_meta_box_convert`: `( empty( $data['convert_price_gateway'] ) || $data['convert_price_gateway'] != $order->get_payment_method() ) &&`
	 * @todo    (dev) rethink `alg_wc_pgbc_convert_currency_wc_subscriptions_renewal`, e.g., check if order has a subscription?
	 * @todo    (feature) add option to manually change/set the "Used currency rate"?
	 * @todo    (desc) better desc?
	 */
	function create_order_pgbc_data_meta_box( $post, $callback_args ) {

		$html  = '';
		$data  = $callback_args['args'];
		$order = wc_get_order( $post->ID );

		if ( ! empty( $data['convert_price_rate'] ) ) {

			// Data table
			$pair = ( isset( $data['shop_currency'], $data['convert_price_currency'] ) ? $data['shop_currency'] . $data['convert_price_currency'] : '' );

			$html .= '<table class="widefat striped"><tbody>';

			$html .= '<tr>' .
					'<th>' . sprintf( __( 'Used %s rate', 'payment-gateways-by-currency-for-woocommerce' ), $pair ) . '</th>' .
					'<td>' . '<code>' . $data['convert_price_rate'] . '</code>' . '<td>' .
				'</tr>';

			if ( isset( $data['shop_currency'] ) ) {
				$html .= '<tr>' .
						'<th>' . __( 'Original total', 'payment-gateways-by-currency-for-woocommerce' ) . '</th>' .
						'<td>' . '<code>' . wc_price( ( $order->get_total() / $data['convert_price_rate'] ), array( 'currency' => $data['shop_currency'] ) ) . '</code>' . '<td>' .
					'</tr>';
			}

			if ( isset( $data['convert_options'] ) && is_array( $data['convert_options'] ) ) {
				foreach ( $data['convert_options'] as $key => $value ) {
					switch ( $key ) {
						case 'shipping':
							$label = __( 'shipping', 'payment-gateways-by-currency-for-woocommerce' );
							break;
						case 'coupon':
							$label = __( 'coupons', 'payment-gateways-by-currency-for-woocommerce' );
							break;
						case 'cart_fee':
							$label = __( 'fees', 'payment-gateways-by-currency-for-woocommerce' );
							break;
						default:
							$label = false;
					}
					if ( $label ) {
						$html .= '<tr>' .
							'<th>' . sprintf( __( 'Convert %s', 'payment-gateways-by-currency-for-woocommerce' ), $label ) . '</th>' .
							'<td>' . ( true === $value ? __( 'Yes', 'payment-gateways-by-currency-for-woocommerce' ) : __( 'No', 'payment-gateways-by-currency-for-woocommerce' ) ) . '<td>' .
						'</tr>';
					}
				}
			}

			$html .= '<tr>' .
					'<th>' . sprintf( __( 'Current %s rate', 'payment-gateways-by-currency-for-woocommerce' ), $pair ) . '</th>' .
					'<td>' . '<code>' . ( isset( $data['convert_price_gateway'] ) && false !== ( $rate = alg_wc_pgbc()->core->convert->rates->get_gateway_rate( $data['convert_price_gateway'] ) ) ?
						$rate : __( 'N/A', 'payment-gateways-by-currency-for-woocommerce' ) ) . '</code>' . '<td>' .
				'</tr>';

			$html .= '</tbody></table>';

			if (
				'yes' === get_option( 'alg_wc_pgbc_convert_currency_order_meta_box_recalculate', 'no' ) &&
				'no' === get_option( 'alg_wc_pgbc_convert_currency_wc_subscriptions_renewal', 'no' )
			) {

				// "Recalculate order" button
				$html .= '<p>' .
						'<a' .
							' href="' . add_query_arg( array( 'alg_wc_pgbc_recalculate_order_id' => $post->ID, '_wpnonce_alg_wc_pgbc' => wp_create_nonce( 'recalculate' ) ) ) . '"' .
							' class="button"' .
							' onclick="return confirm(\'' . __( 'There is no undo for this action. Are you sure?', 'payment-gateways-by-currency-for-woocommerce' ) . '\');"' .
						'>' . __( 'Recalculate with new rate', 'payment-gateways-by-currency-for-woocommerce' ) . '</a>' .
					'</p>';

			}

		} elseif (
			'yes' === get_option( 'alg_wc_pgbc_convert_currency_order_meta_box_convert', 'no' ) &&
			$order && ( $payment_method = $order->get_payment_method() ) &&
			false !== alg_wc_pgbc()->core->convert->rates->get_gateway_rate( $payment_method ) &&
			$order->get_currency() != ( $currency = alg_wc_pgbc()->core->convert->get_gateway_currency( $payment_method ) )
		) {

			// "Convert order" button
			$html .= '<p>' .
					'<a' .
						' href="' . add_query_arg( array( 'alg_wc_pgbc_convert_order_id' => $post->ID, '_wpnonce_alg_wc_pgbc' => wp_create_nonce( 'convert' ) ) ) . '"' .
						' class="button"' .
						' onclick="return confirm(\'' . __( 'There is no undo for this action. Are you sure?', 'payment-gateways-by-currency-for-woocommerce' ) . '\');"' .
					'>' . sprintf( __( 'Convert order to %s', 'payment-gateways-by-currency-for-woocommerce' ), $currency ) . '</a>' .
				'</p>';

		} else {

			// No data
			$html .= '<p><em>' . __( 'No data.', 'payment-gateways-by-currency-for-woocommerce' ) . '</em></p>';

		}

		echo $html;

	}

}

endif;

return new Alg_WC_PGBC_Convert_Info_Backend();
