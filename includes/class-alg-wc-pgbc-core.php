<?php
/**
 * Payment Gateway Currency for WooCommerce - Core Class
 *
 * @version 3.9.0
 * @since   1.0.0
 *
 * @author  Algoritmika Ltd.
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Alg_WC_PGBC_Core' ) ) :

class Alg_WC_PGBC_Core {

	/**
	 * restrict.
	 *
	 * @version 3.9.0
	 * @since   3.9.0
	 */
	public $restrict;

	/**
	 * convert.
	 *
	 * @version 3.9.0
	 * @since   3.9.0
	 */
	public $convert;

	/**
	 * Constructor.
	 *
	 * @version 2.0.0
	 * @since   1.0.0
	 *
	 * @todo    (dev) move this to the `Alg_WC_PGBC` class?
	 * @todo    (dev) add more data to debug
	 */
	function __construct() {

		// Restrict (i.e., allowed/denied) currencies
		$this->restrict = require_once( 'class-alg-wc-pgbc-restrict.php' );

		// Convert price & currency
		$this->convert  = require_once( 'class-alg-wc-pgbc-convert.php' );

	}

	/**
	 * add_to_log.
	 *
	 * @version 2.0.0
	 * @since   2.0.0
	 */
	function add_to_log( $message ) {
		if ( function_exists( 'wc_get_logger' ) && ( $log = wc_get_logger() ) ) {
			$log->log( 'info', $message, array( 'source' => 'alg-wc-payment-gateway-currency' ) );
		}
	}

}

endif;

return new Alg_WC_PGBC_Core();
