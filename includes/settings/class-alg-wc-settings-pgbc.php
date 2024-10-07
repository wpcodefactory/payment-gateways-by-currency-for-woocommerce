<?php
/**
 * Payment Gateway Currency for WooCommerce - Settings
 *
 * @version 3.8.0
 * @since   1.0.0
 *
 * @author  Algoritmika Ltd.
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Alg_WC_Settings_Payment_Gateways_by_Currency' ) ) :

class Alg_WC_Settings_Payment_Gateways_by_Currency extends WC_Settings_Page {

	/**
	 * Constructor.
	 *
	 * @version 3.8.0
	 * @since   1.0.0
	 */
	function __construct() {
		$this->id    = 'alg_wc_pgbc';
		$this->label = __( 'Payment Gateway Currency', 'payment-gateways-by-currency-for-woocommerce' );
		parent::__construct();
		// Sections
		require_once( 'class-alg-wc-pgbc-settings-section.php' );
		require_once( 'class-alg-wc-pgbc-settings-convert.php' );
		require_once( 'class-alg-wc-pgbc-settings-convert-general.php' );
		require_once( 'class-alg-wc-pgbc-settings-convert-info.php' );
		require_once( 'class-alg-wc-pgbc-settings-convert-info-backend.php' );
		require_once( 'class-alg-wc-pgbc-settings-convert-compatibility.php' );
		require_once( 'class-alg-wc-pgbc-settings-convert-advanced.php' );
		require_once( 'class-alg-wc-pgbc-settings-restrict.php' );
	}

	/**
	 * get_settings.
	 *
	 * @version 1.2.0
	 * @since   1.0.0
	 */
	function get_settings() {
		global $current_section;
		return array_merge( apply_filters( 'woocommerce_get_settings_' . $this->id . '_' . $current_section, array() ), array(
			array(
				'title'     => __( 'Reset Settings', 'payment-gateways-by-currency-for-woocommerce' ),
				'type'      => 'title',
				'id'        => $this->id . '_' . $current_section . '_reset_options',
			),
			array(
				'title'     => __( 'Reset section settings', 'payment-gateways-by-currency-for-woocommerce' ),
				'desc'      => '<strong>' . __( 'Reset', 'payment-gateways-by-currency-for-woocommerce' ) . '</strong>',
				'desc_tip'  => __( 'Check the box and save changes to reset.', 'payment-gateways-by-currency-for-woocommerce' ),
				'id'        => $this->id . '_' . $current_section . '_reset',
				'default'   => 'no',
				'type'      => 'checkbox',
			),
			array(
				'type'      => 'sectionend',
				'id'        => $this->id . '_' . $current_section . '_reset_options',
			),
		) );
	}

	/**
	 * maybe_reset_settings.
	 *
	 * @version 1.2.0
	 * @since   1.0.0
	 */
	function maybe_reset_settings() {
		global $current_section;
		if ( 'yes' === get_option( $this->id . '_' . $current_section . '_reset', 'no' ) ) {
			foreach ( $this->get_settings() as $value ) {
				if ( isset( $value['id'] ) ) {
					$id = explode( '[', $value['id'] );
					delete_option( $id[0] );
				}
			}
			if ( method_exists( 'WC_Admin_Settings', 'add_message' ) ) {
				WC_Admin_Settings::add_message( __( 'Your settings have been reset.', 'payment-gateways-by-currency-for-woocommerce' ) );
			} else {
				add_action( 'admin_notices', array( $this, 'admin_notice_settings_reset' ) );
			}
		}
	}

	/**
	 * admin_notice_settings_reset.
	 *
	 * @version 1.1.0
	 * @since   1.1.0
	 */
	function admin_notice_settings_reset() {
		echo '<div class="notice notice-warning is-dismissible"><p><strong>' .
			__( 'Your settings have been reset.', 'payment-gateways-by-currency-for-woocommerce' ) . '</strong></p></div>';
	}

	/**
	 * Save settings.
	 *
	 * @version 1.5.0
	 * @since   1.0.0
	 */
	function save() {
		parent::save();
		$this->maybe_reset_settings();
		do_action( 'alg_wc_pgbc_settings_saved' );
	}

}

endif;

return new Alg_WC_Settings_Payment_Gateways_by_Currency();
