<?php
/**
 * Payment Gateway Currency for WooCommerce - Convert - Backend Info Section Settings
 *
 * @version 3.2.0
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
	 * @todo    [next] (dev) rename to "Admin" (and `Alg_WC_PGBC_Convert_Info_Backend` to `Alg_WC_PGBC_Convert_Admin`)?
	 */
	function __construct() {
		$this->id   = 'convert_info_backend';
		$this->desc = __( 'Admin', 'payment-gateways-by-currency-for-woocommerce' );
		parent::__construct();
	}

	/**
	 * get_settings.
	 *
	 * @version 3.2.0
	 * @since   3.0.0
	 *
	 * @todo    [later] (desc) `alg_wc_pgbc_convert_currency_order_meta_box`: better desc?
	 * @todo    [later] (desc) `alg_wc_pgbc_convert_currency_admin_symbol`: better desc?
	 * @todo    [later] (desc) `alg_wc_pgbc_convert_currency_admin_order_total`: better desc
	 * @todo    [maybe] (dev) rename "Recalculate with new rate" button (e.g. to "Recalculate by gateway")?
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
				'desc_tip' => __( 'This will display used conversion rate etc. in meta box on admin edit order page.', 'payment-gateways-by-currency-for-woocommerce' ) . '<br>' .
					__( 'Please note that this will work only for orders converted with our plugin since v2.0.0 (03/06/2021).', 'payment-gateways-by-currency-for-woocommerce' ),
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
		return $settings;
	}

}

endif;

return new Alg_WC_PGBC_Settings_Convert_Info_Backend();
