<?php
/**
 * Payment Gateway Currency for WooCommerce - Convert - Backend Info Section Settings
 *
 * @version 3.9.0
 * @since   3.0.0
 *
 * @author  Algoritmika Ltd.
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Alg_WC_PGBC_Settings_Convert_Info_Backend' ) ) :

class Alg_WC_PGBC_Settings_Convert_Info_Backend extends Alg_WC_PGBC_Settings_Section {

	/**
	 * Constructor.
	 *
	 * @version 3.0.0
	 * @since   3.0.0
	 *
	 * @todo    (dev) rename to "Admin" (and `Alg_WC_PGBC_Convert_Info_Backend` to `Alg_WC_PGBC_Convert_Admin`)?
	 */
	function __construct() {
		$this->id   = 'convert_info_backend';
		$this->desc = __( 'Admin', 'payment-gateways-by-currency-for-woocommerce' );
		parent::__construct();
	}

	/**
	 * get_settings.
	 *
	 * @version 3.9.0
	 * @since   3.0.0
	 *
	 * @todo    (dev) `alg_wc_pgbc_convert_currency_admin_num_decimals`: default to `yes`?
	 * @todo    (desc) `alg_wc_pgbc_convert_currency_order_meta_box`: better desc?
	 * @todo    (desc) `alg_wc_pgbc_convert_currency_admin_symbol`: better desc?
	 * @todo    (desc) `alg_wc_pgbc_convert_currency_admin_order_total`: better desc
	 * @todo    (dev) rename "Recalculate with new rate" button (e.g., to "Recalculate by gateway")?
	 */
	function get_settings() {

		$settings = array(
			array(
				'title'    => __( 'Convert Currency', 'payment-gateways-by-currency-for-woocommerce' ) . ': ' . __( 'Admin Options', 'payment-gateways-by-currency-for-woocommerce' ),
				'type'     => 'title',
				'id'       => 'alg_wc_pgbc_convert_currency_info_backend_options',
			),
			array(
				'title'    => __( 'Order page', 'payment-gateways-by-currency-for-woocommerce' ),
				'desc'     => __( 'Enable', 'payment-gateways-by-currency-for-woocommerce' ),
				'desc_tip' => __( 'This will display used conversion rate etc. in meta box on admin edit order page.', 'payment-gateways-by-currency-for-woocommerce' ),
				'id'       => 'alg_wc_pgbc_convert_currency_order_meta_box',
				'default'  => 'yes',
				'type'     => 'checkbox',
				'checkboxgroup' => 'start',
			),
			array(
				'desc'     => __( 'Add recalculate button', 'payment-gateways-by-currency-for-woocommerce' ),
				'desc_tip' => __( 'This will add "Recalculate with new rate" button to the meta box.', 'payment-gateways-by-currency-for-woocommerce' ) . ' ' .
					sprintf( __( '"%s" option must be enabled.', 'payment-gateways-by-currency-for-woocommerce' ), __( 'Order page', 'payment-gateways-by-currency-for-woocommerce' ) ),
				'id'       => 'alg_wc_pgbc_convert_currency_order_meta_box_recalculate',
				'default'  => 'no',
				'type'     => 'checkbox',
				'checkboxgroup' => '',
			),
			array(
				'desc'     => __( 'Add convert button', 'payment-gateways-by-currency-for-woocommerce' ),
				'desc_tip' => __( 'This will add "Convert order" button to the meta box.', 'payment-gateways-by-currency-for-woocommerce' ) . ' ' .
					sprintf( __( '"%s" option must be enabled.', 'payment-gateways-by-currency-for-woocommerce' ), __( 'Order page', 'payment-gateways-by-currency-for-woocommerce' ) ),
				'id'       => 'alg_wc_pgbc_convert_currency_order_meta_box_convert',
				'default'  => 'no',
				'type'     => 'checkbox',
				'checkboxgroup' => 'end',
			),
			array(
				'title'    => __( 'Orders list', 'payment-gateways-by-currency-for-woocommerce' ),
				'desc'     => __( 'Original total', 'payment-gateways-by-currency-for-woocommerce' ),
				'desc_tip' => __( 'This will add original (i.e., unconverted) order total column to the "Orders" list.', 'payment-gateways-by-currency-for-woocommerce' ),
				'id'       => 'alg_wc_pgbc_convert_currency_admin_orders_list_total',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'title'    => __( 'Number of decimals in admin', 'payment-gateways-by-currency-for-woocommerce' ),
				'desc'     => __( 'Enable', 'payment-gateways-by-currency-for-woocommerce' ),
				'desc_tip' => __( 'Apply the number of decimals in admin as well.', 'payment-gateways-by-currency-for-woocommerce' ),
				'id'       => 'alg_wc_pgbc_convert_currency_admin_num_decimals',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'title'    => __( 'Currency symbol in admin', 'payment-gateways-by-currency-for-woocommerce' ),
				'desc'     => __( 'Enable', 'payment-gateways-by-currency-for-woocommerce' ),
				'desc_tip' => __( 'Replace currency symbol in admin as well.', 'payment-gateways-by-currency-for-woocommerce' ),
				'id'       => 'alg_wc_pgbc_convert_currency_admin_symbol',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'title'    => __( 'Order total in admin', 'payment-gateways-by-currency-for-woocommerce' ),
				'desc_tip' => __( 'Custom admin order total format.', 'payment-gateways-by-currency-for-woocommerce' ),
				'desc'     => __( 'Enable', 'payment-gateways-by-currency-for-woocommerce' ),
				'id'       => 'alg_wc_pgbc_convert_currency_admin_order_total',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'desc'     => sprintf( __( 'Placeholders: %s.', 'payment-gateways-by-currency-for-woocommerce' ), '<code>' . implode( '</code>, <code>', array(
						'%order_total%',
						'%currency%',
						'%currency_symbol%',
						'%convert_price_rate%',
						'%order_total_original%',
					) ) . '</code>' ),
				'id'       => 'alg_wc_pgbc_convert_currency_admin_order_total_format',
				'default'  => '%order_total% %currency%',
				'type'     => 'text',
			),
			array(
				'type'     => 'sectionend',
				'id'       => 'alg_wc_pgbc_convert_currency_info_backend_options',
			),
		);

		$analytics = array(
			array(
				'title'    => __( 'WooCommerce Analytics', 'payment-gateways-by-currency-for-woocommerce' ),
				'desc'     => sprintf( __( 'Options regarding the %s.', 'payment-gateways-by-currency-for-woocommerce' ),
						'<a href="' . admin_url( 'admin.php?page=wc-admin&path=/analytics/overview' ) . '">' .
							__( 'WooCommerce Analytics', 'payment-gateways-by-currency-for-woocommerce' ) .
						'</a>'
					) . ' ' .
					sprintf( __( 'If you can\'t see the values refreshed or have issues with the analytics page, please try to <a href="%s">clear analytics cache</a>.', 'payment-gateways-by-currency-for-woocommerce' ),
						admin_url( 'admin.php?page=wc-status&tab=tools' ) ),
				'type'     => 'title',
				'id'       => 'alg_wc_pgbc_convert_currency_analytics_options',
			),
			array(
				'title'    => __( 'Orders and Revenue', 'payment-gateways-by-currency-for-woocommerce' ),
				'desc'     => __( 'Recalculate values from the orders and revenue tabs based on the conversion rate', 'payment-gateways-by-currency-for-woocommerce' ),
				'type'     => 'checkbox',
				'checkbox' => 'no',
				'id'       => 'alg_wc_pgbc_convert_currency_analytics_orders_and_revenue',
			),
			array(
				'type'     => 'sectionend',
				'id'       => 'alg_wc_pgbc_convert_currency_analytics_options',
			),
		);

		return array_merge( $settings, $analytics );
	}

}

endif;

return new Alg_WC_PGBC_Settings_Convert_Info_Backend();
