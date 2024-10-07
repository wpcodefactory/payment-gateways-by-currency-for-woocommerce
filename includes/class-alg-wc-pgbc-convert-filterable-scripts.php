<?php
/**
 * Payment Gateway Currency for WooCommerce - Convert - Filterable Scripts Class
 *
 * @version 3.7.4
 * @since   3.4.2
 *
 * @author  Algoritmika Ltd.
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Alg_WC_PGBC_Convert_Filterable_Scripts' ) && class_exists( 'WP_Scripts' ) ) :

class Alg_WC_PGBC_Convert_Filterable_Scripts extends WP_Scripts {

	/**
	 * localize.
	 *
	 * @version 3.4.2
	 * @since   3.4.2
	 *
	 * @see     https://wordpress.stackexchange.com/questions/108362/how-to-intercept-already-localized-scripts
	 *
	 * @todo    (fix) possible "missing scripts" issue
	 */
	function localize( $handle, $object_name, $l10n ) {
		$l10n = apply_filters( 'alg_wc_pgbc_convert_filterable_scripts_l10n', $l10n, $handle, $object_name );
		return parent::localize( $handle, $object_name, $l10n );
	}

}

endif;
