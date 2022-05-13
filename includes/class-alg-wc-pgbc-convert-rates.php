<?php
/**
 * Payment Gateway Currency for WooCommerce - Convert - Rates Class
 *
 * @version 3.4.0
 * @since   2.0.0
 *
 * @author  Algoritmika Ltd.
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Alg_WC_PGBC_Convert_Rates' ) ) :

class Alg_WC_PGBC_Convert_Rates {

	/**
	 * Constructor.
	 *
	 * @version 3.4.0
	 * @since   2.0.0
	 *
	 * @see     https://actionscheduler.org/
	 *
	 * @todo    [now] [!!] (dev) `wp_clear_scheduled_hook`, `wp_unschedule_event`? (also run on plugin deactivation?)
	 */
	function __construct() {

		$this->action = 'alg_wc_pgbc_currency_exchange_rates_action';

		// Schedule/Unschedule action
		if ( 'yes' === get_option( 'alg_wc_pgbc_convert_currency_enabled', 'no' ) && $this->is_server_rates() ) {
			add_action( 'init', array( $this, 'schedule_action' ) );
			add_action( $this->action, array( $this, 'get_server_rates' ) );
		} else {
			add_action( 'init', array( $this, 'unschedule_action' ) );
		}

		// Plugin deactivation
		register_deactivation_hook( ALG_WC_PGBC_FILE, array( $this, 'unschedule_action' ) );

		// Clearing WP cron (for backward compatibility)
		wp_clear_scheduled_hook( 'alg_wc_pgbc_currency_exchange_rates' );
		wp_clear_scheduled_hook( 'alg_wc_pgbc_currency_exchange_rates', array( 'hourly' ) );

		// "Update all rates now" checkbox
		add_action( 'alg_wc_pgbc_settings_saved', array( $this, 'get_server_rates_manual' ) );

	}

	/**
	 * unschedule_action.
	 *
	 * @version 3.4.0
	 * @since   3.4.0
	 */
	function unschedule_action() {
		if ( function_exists( 'as_unschedule_all_actions' ) ) {
			as_unschedule_all_actions( $this->action );
		}
	}

	/**
	 * schedule_action.
	 *
	 * @version 3.4.0
	 * @since   3.4.0
	 *
	 * @todo    [later] (dev) move it somewhere else from the `init` action (`register_activation_hook` won't work because of `plugins_loaded` problem)
	 * @todo    [maybe] (dev) `admin_notices`: `check_if_wp_crons_disabled`
	 * @todo    [maybe] (dev) run on `alg_wc_pgbc_settings_saved` (same for `unschedule_action`)?
	 */
	function schedule_action() {
		if (
			function_exists( 'as_has_scheduled_action' ) &&
			( $interval_in_seconds = get_option( 'alg_wc_pgbc_convert_currency_auto_rates_interval', HOUR_IN_SECONDS ) ) &&
			false === as_has_scheduled_action( $this->action, array( $interval_in_seconds ) )
		) {
			as_unschedule_all_actions( $this->action );
			as_schedule_recurring_action( time(), $interval_in_seconds, $this->action, array( $interval_in_seconds ) );
		}
	}

	/**
	 * get_gateway_rates.
	 *
	 * @version 3.1.0
	 * @since   2.0.0
	 */
	function get_gateway_rates( $force = false, $update = false ) {
		if ( $force || ! isset( $this->gateway_rates ) ) {
			$this->gateway_rates = get_option( 'alg_wc_pgbc_convert_rate', array() );
			if ( 'woocommerce_wpml' === get_option( 'alg_wc_pgbc_convert_currency_auto_rates_plugin', '' ) ) {
				// WooCommerce Multilingual (WPML)
				if ( function_exists( 'wcml_is_multi_currency_on' ) && wcml_is_multi_currency_on() ) {
					global $woocommerce_wpml;
					$multi_currency = $woocommerce_wpml->get_multi_currency();
					foreach ( alg_wc_pgbc()->core->convert->get_gateway_currencies() as $gateway => $currency ) {
						if ( ! empty( $multi_currency->currencies[ $currency ]['rate'] ) ) {
							$this->gateway_rates[ $gateway ] = $multi_currency->currencies[ $currency ]['rate'];
						}
					}
					if ( $update ) {
						update_option( 'alg_wc_pgbc_convert_rate', $this->gateway_rates );
					}
				}
			} elseif ( 'yes' === get_option( 'alg_wc_pgbc_convert_currency_rate_type_text', 'no' ) ) {
				$this->gateway_rates = array_map( array( $this, 'ensure_is_numeric_value' ), $this->gateway_rates );
				if ( $update ) {
					update_option( 'alg_wc_pgbc_convert_rate', $this->gateway_rates );
				}
			}
		}
		return $this->gateway_rates;
	}

	/**
	 * ensure_is_numeric_value.
	 *
	 * @version 2.1.0
	 * @since   2.1.0
	 */
	function ensure_is_numeric_value( $value ) {
		$value = str_replace( ',', '.', $value );
		return ( is_numeric( $value ) ? $value : '' );
	}

	/**
	 * get_gateway_rate.
	 *
	 * @version 3.1.0
	 * @since   2.0.0
	 *
	 * @todo    [maybe] (dev) maybe return `false` on *zero* as well?
	 * @todo    [maybe] (dev) maybe return `false` on *one* as well (and if currencies are the same)?
	 */
	function get_gateway_rate( $gateway ) {
		if ( ! isset( $this->gateway_rates ) ) {
			$this->get_gateway_rates();
		}
		$rate = ( isset( $this->gateway_rates[ $gateway ] ) && '' !== $this->gateway_rates[ $gateway ] ? $this->gateway_rates[ $gateway ] : false );
		return apply_filters( 'alg_wc_pgbc_convert_currency_get_gateway_rate', $rate, $gateway, $this );
	}

	/**
	 * is_server_rates.
	 *
	 * @version 2.0.0
	 * @since   2.0.0
	 */
	function is_server_rates() {
		return ( '' != get_option( 'alg_wc_pgbc_convert_currency_auto_rates_cron', '' ) && '' === get_option( 'alg_wc_pgbc_convert_currency_auto_rates_plugin', '' ) );
	}

	/**
	 * get_server_rates_manual.
	 *
	 * @version 2.0.0
	 * @since   1.5.0
	 */
	function get_server_rates_manual() {
		if ( 'yes' === get_option( 'alg_wc_pgbc_convert_currency_auto_rates_now', 'no' ) ) {
			update_option( 'alg_wc_pgbc_convert_currency_auto_rates_now', 'no' );
			$this->get_server_rates();
			WC_Admin_Settings::add_message( __( 'Currency exchange rates updated.', 'payment-gateways-by-currency-for-woocommerce' ) );
		}
	}

	/**
	 * get_server_rates.
	 *
	 * @version 3.3.0
	 * @since   1.5.0
	 *
	 * @todo    [next] (feature) add more servers, e.g. https://free.currencyconverterapi.com/, https://exchangeratesapi.io/, https://www.exchangerate-api.com/
	 * @todo    [next] (feature) fixed offset?
	 */
	function get_server_rates() {
		if ( '' === get_option( 'alg_wc_pgbc_convert_currency_auto_rates_plugin', '' ) ) {
			$rates       = get_option( 'alg_wc_pgbc_convert_rate', array() );
			$from        = get_option( 'woocommerce_currency' );
			$server      = get_option( 'alg_wc_pgbc_convert_currency_auto_rates_server', 'ecb' );
			$func        = apply_filters( 'alg_wc_pgbc_server_rates_function', array( $this, 'get_server_rate_' . $server ) );
			$server_keys = get_option( 'alg_wc_pgbc_convert_currency_auto_rates_server_keys', array() );
			$constant    = 'ALG_WC_PGBC_API_KEY_' . strtoupper( $server );
			$server_key  = ( defined( $constant ) ? constant( $constant ) : ( isset( $server_keys[ $server ] ) ? $server_keys[ $server ] : false ) );
			$multiplier  = get_option( 'alg_wc_pgbc_convert_currency_rate_multiplier', 0 );
			$currencies  = alg_wc_pgbc()->core->convert->get_gateway_currencies();
			foreach ( $currencies as $gateway => $currency ) {
				if ( '' !== $currency ) {
					$rate = ( isset( $this->cached_server_rates[ $from ][ $currency ] ) ?
						$this->cached_server_rates[ $from ][ $currency ] : call_user_func( $func, $from, $currency, $server_key, $currencies ) );
					if ( $rate ) {
						if ( 0 != $multiplier ) {
							$rate *= $multiplier;
						}
						$rates[ $gateway ] = apply_filters( 'alg_wc_pgbc_convert_currency_rate', $rate, $from, $currency );
					}
				}
			}
			update_option( 'alg_wc_pgbc_convert_rate', $rates );
			$this->gateway_rates = $rates;
		}
	}

	/**
	 * get_server_rate_fixer.
	 *
	 * @version 3.4.0
	 * @since   2.0.0
	 *
	 * @todo    [now] [!!] (fix) `base_currency_access_restricted`
	 */
	function get_server_rate_fixer( $currency_from, $currency_to, $key, $currencies ) {
		$final_rate   = false;
		$symbols      = implode( ',', array_unique( array_filter( $currencies ) ) );
		$use_apilayer = ( 'apilayer' === get_option( 'alg_wc_pgbc_convert_currency_auto_rates_fixer_url', 'fixer' ) );
		$url          = add_query_arg( array( ( $use_apilayer ? 'apikey' : 'access_key' ) => $key, 'base' => $currency_from, 'symbols' => $symbols ),
			( $use_apilayer ? 'http://api.apilayer.com/fixer/latest' : 'http://data.fixer.io/api/latest' ) );
		if ( ! isset( $this->cached_data_fixer ) ) {
			if ( function_exists( 'curl_version' ) ) {
				$curl = curl_init( $url );
				curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true );
				$this->cached_data_fixer = curl_exec( $curl );
				curl_close( $curl );
			} elseif ( function_exists( 'file_get_contents' ) ) {
				$this->cached_data_fixer = file_get_contents( $url );
			}
			if ( isset( $this->cached_data_fixer ) ) {
				$this->cached_data_fixer = json_decode( $this->cached_data_fixer, true );
				if ( alg_wc_pgbc()->core->convert->do_debug ) {
					alg_wc_pgbc()->core->add_to_log( sprintf( __( '%s server data: %s', 'payment-gateways-by-currency-for-woocommerce' ),
						__( 'Fixer.io', 'payment-gateways-by-currency-for-woocommerce' ),
						print_r( $this->cached_data_fixer, true ) ) );
				}
				if ( JSON_ERROR_NONE != json_last_error() ) {
					unset( $this->cached_data_fixer );
					if ( alg_wc_pgbc()->core->convert->do_debug ) {
						alg_wc_pgbc()->core->add_to_log( sprintf( __( '%s server data: %s.', 'payment-gateways-by-currency-for-woocommerce' ),
						__( 'Fixer.io', 'payment-gateways-by-currency-for-woocommerce' ),
						json_last_error_msg() ) );
					}
				}
			} elseif ( alg_wc_pgbc()->core->convert->do_debug ) {
				alg_wc_pgbc()->core->add_to_log( sprintf( __( '%s server data failed.', 'payment-gateways-by-currency-for-woocommerce' ),
					__( 'Fixer.io', 'payment-gateways-by-currency-for-woocommerce' ) ) );
			}
		}
		if ( isset( $this->cached_data_fixer ) ) {
			if ( isset( $this->cached_data_fixer['rates'][ $currency_to ] ) && is_numeric( $this->cached_data_fixer['rates'][ $currency_to ] ) ) {
				$final_rate = round( $this->cached_data_fixer['rates'][ $currency_to ], 6 );
				$this->cached_server_rates[ $currency_from ][ $currency_to ] = $final_rate;
			}
		}
		return $final_rate;
	}

	/**
	 * get_server_rate_ecb.
	 *
	 * @version 3.2.0
	 * @since   1.5.0
	 *
	 * @todo    [maybe] (dev) fallback for `simplexml_load_file`?
	 */
	function get_server_rate_ecb( $currency_from, $currency_to, $key = false, $currencies = false ) {
		$final_rate = false;
		if ( ! isset( $this->cached_data_ecb ) ) {
			if ( function_exists( 'simplexml_load_file' ) ) {
				if ( alg_wc_pgbc()->core->convert->do_debug ) {
					libxml_use_internal_errors( true );
				}
				$url = 'http://www.ecb.int/stats/eurofxref/eurofxref-daily.xml';
				$this->cached_data_ecb = ( defined( 'WP_DEBUG' ) && true === WP_DEBUG ? simplexml_load_file( $url ) : @simplexml_load_file( $url ) );
			} elseif ( alg_wc_pgbc()->core->convert->do_debug ) {
				alg_wc_pgbc()->core->add_to_log( sprintf( __( '%s server data: %s does not exist.', 'payment-gateways-by-currency-for-woocommerce' ),
					__( 'European Central Bank (ECB)', 'payment-gateways-by-currency-for-woocommerce' ), '`simplexml_load_file()`' ) );
			}
			if ( isset( $this->cached_data_ecb ) ) {
				if ( false !== $this->cached_data_ecb ) {
					if ( alg_wc_pgbc()->core->convert->do_debug ) {
						alg_wc_pgbc()->core->add_to_log( sprintf( __( '%s server data: %s', 'payment-gateways-by-currency-for-woocommerce' ),
							__( 'European Central Bank (ECB)', 'payment-gateways-by-currency-for-woocommerce' ),
							print_r( $this->cached_data_ecb, true ) ) );
					}
					if ( ! isset( $this->cached_data_ecb->Cube->Cube->Cube ) ) {
						unset( $this->cached_data_ecb );
						if ( alg_wc_pgbc()->core->convert->do_debug ) {
							alg_wc_pgbc()->core->add_to_log( sprintf( __( '%s server data: wrong format.', 'payment-gateways-by-currency-for-woocommerce' ),
								__( 'European Central Bank (ECB)', 'payment-gateways-by-currency-for-woocommerce' ) ) );
						}
					}
				} elseif ( alg_wc_pgbc()->core->convert->do_debug ) {
					foreach ( libxml_get_errors() as $error ) {
						alg_wc_pgbc()->core->add_to_log( sprintf( __( '%s server data failed: %s', 'payment-gateways-by-currency-for-woocommerce' ),
							__( 'European Central Bank (ECB)', 'payment-gateways-by-currency-for-woocommerce' ), $error->message ) );
					}
				}
			}
		}
		if ( isset( $this->cached_data_ecb ) ) {
			$EUR_rate_from = ( 'EUR' === $currency_from ? 1 : null );
			$EUR_rate_to   = ( 'EUR' === $currency_to   ? 1 : null );
			foreach ( $this->cached_data_ecb->Cube->Cube->Cube as $currency_rate ) {
				$currency_rate = $currency_rate->attributes();
				if ( ! isset( $EUR_rate_from ) && $currency_from == $currency_rate->currency ) {
					$EUR_rate_from = ( float ) $currency_rate->rate;
				}
				if ( ! isset( $EUR_rate_to )   && $currency_to   == $currency_rate->currency ) {
					$EUR_rate_to   = ( float ) $currency_rate->rate;
				}
				if ( isset( $EUR_rate_from, $EUR_rate_to ) ) {
					break;
				}
			}
			if ( isset( $EUR_rate_from, $EUR_rate_to ) && 0 != $EUR_rate_from ) {
				$final_rate = round( $EUR_rate_to / $EUR_rate_from, 6 );
				$this->cached_server_rates[ $currency_from ][ $currency_to ] = $final_rate;
			}
		}
		return $final_rate;
	}

}

endif;

return new Alg_WC_PGBC_Convert_Rates();
