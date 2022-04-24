<?php
/**
 * Payment Gateway Currency for WooCommerce - Convert - Info Backend Class
 *
 * @version 3.2.0
 * @since   3.0.0
 *
 * @author  Algoritmika Ltd.
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'Alg_WC_PGBC_Convert_Info_Backend' ) ) :

class Alg_WC_PGBC_Convert_Info_Backend {

	/**
	 * Constructor.
	 *
	 * @version 3.0.0
	 * @since   3.0.0
	 *
	 * @todo    [later] (feature) admin: show original (i.e. unconverted) order total in "Orders" list column
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
			}
		}
	}

	/**
	 * get_formatted_order_total.
	 *
	 * @version 2.0.0
	 * @since   2.0.0
	 *
	 * @todo    [next] (feature) add more placeholders, e.g. `%currency_symbol%`
	 */
	function get_formatted_order_total( $formatted_total, $order ) {
		$template     = get_option( 'alg_wc_pgbc_convert_currency_admin_order_total_format', '%order_total% %currency%' );
		$placeholders = array(
			'%order_total%' => $formatted_total,
			'%currency%'    => $order->get_currency(),
		);
		return str_replace( array_keys( $placeholders ), $placeholders, $template );
	}

	/**
	 * convert_currency_symbol_in_admin.
	 *
	 * @version 2.0.0
	 * @since   2.0.0
	 *
	 * @todo    [next] (dev) maybe limit this to *orders* only?
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
	 * @todo    [now] (dev) `alg_wc_pgbc_convert_order_id`: notice
	 * @todo    [next] (dev) better notices (i.e. errors)
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
	 * @version 3.2.0
	 * @since   2.0.0
	 *
	 * @todo    [now] [!] (dev) rethink `alg_wc_pgbc_convert_currency_order_meta_box_convert`
	 */
	function add_order_pgbc_data_meta_box( $post_type, $post ) {
		$data = get_post_meta( $post->ID, '_alg_wc_pgbc_data', true );
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
	 * @version 3.2.0
	 * @since   2.0.0
	 *
	 * @todo    [now] (dev) `alg_wc_pgbc_convert_currency_order_meta_box_convert`: `( empty( $data['convert_price_gateway'] ) || $data['convert_price_gateway'] != $order->get_payment_method() ) &&`
	 * @todo    [next] (dev) rethink `alg_wc_pgbc_convert_currency_wc_subscriptions_renewal`, e.g. check if order has a subscription?
	 * @todo    [later] (feature) add option to manually change/set the "Used currency rate"?
	 * @todo    [maybe] (desc) better desc?
	 */
	function create_order_pgbc_data_meta_box( $post, $callback_args ) {
		$html = '';
		$data = $callback_args['args'];
		if ( ! empty( $data['convert_price_rate'] ) ) {
			// Data table
			$pair = ( isset( $data['shop_currency'], $data['convert_price_currency'] ) ? $data['shop_currency'] . $data['convert_price_currency'] : '' );
			$html .= '<table class="widefat striped"><tbody>' .
					'<tr>' .
						'<th>' . sprintf( __( 'Used %s rate', 'payment-gateways-by-currency-for-woocommerce' ), $pair ) . '</th>' .
						'<td>' . '<code>' . $data['convert_price_rate'] . '</code>' . '<td>' .
					'</tr>';
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
					'</tr>' .
				'</tbody></table>';
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
			// "Convert order" button
			'yes' === get_option( 'alg_wc_pgbc_convert_currency_order_meta_box_convert', 'no' ) &&
			( $order = wc_get_order( $post->ID ) ) && ( $payment_method = $order->get_payment_method() ) &&
			false !== alg_wc_pgbc()->core->convert->rates->get_gateway_rate( $payment_method ) &&
			$order->get_currency() != ( $currency = alg_wc_pgbc()->core->convert->get_gateway_currency( $payment_method ) )
		) {
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
