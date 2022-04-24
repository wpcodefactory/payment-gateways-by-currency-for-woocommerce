<?php
/**
 * Payment Gateway Currency for WooCommerce - Core Class
 *
 * @version 2.0.0
 * @since   1.0.0
 *
 * @author  Algoritmika Ltd.
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'Alg_WC_PGBC_Core' ) ) :

class Alg_WC_PGBC_Core {

	/**
	 * Constructor.
	 *
	 * @version 2.0.0
	 * @since   1.0.0
	 *
	 * @todo    [next] (dev) move this to the `Alg_WC_PGBC` class?
	 * @todo    [next] (dev) add more data to debug
	 */
	function __construct() {
		// Restrict (i.e. allowed/denied) currencies
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
