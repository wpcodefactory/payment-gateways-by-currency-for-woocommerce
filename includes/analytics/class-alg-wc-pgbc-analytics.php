<?php
/**
 * Payment Gateway Currency for WooCommerce - Analytics.
 *
 * @version 3.5.0
 * @since   3.5.0
 *
 * @author  Algoritmika Ltd.
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'Alg_WC_PGBC_Analytics' ) ) :

	class Alg_WC_PGBC_Analytics {

		/**
		 * init.
		 *
		 * @version 3.5.0
		 * @since   3.5.0
		 */
		function init(){
			$this->handle_orders_and_revenue();
		}

		/**
		 * handle_orders.
		 *
		 * @version 3.5.0
		 * @since   3.5.0
		 */
		function handle_orders_and_revenue() {
			if ( 'yes' === get_option( 'alg_wc_pgbc_convert_currency_analytics_orders_and_revenue', 'no' ) ) {
				// Orders Select.
				add_filter( 'woocommerce_analytics_clauses_select_orders_subquery', array( $this, 'handle_orders_select' ) );
				add_filter( 'woocommerce_analytics_clauses_select_orders_stats_total', array( $this, 'handle_orders_stats_total_and_interval' ) );
				add_filter( 'woocommerce_analytics_clauses_select_orders_stats_interval', array( $this, 'handle_orders_stats_total_and_interval' ) );
				// Orders Join.
				add_filter( 'woocommerce_analytics_clauses_join_orders_subquery', array( $this, 'handle_orders_join' ) );
				add_filter( 'woocommerce_analytics_clauses_join_orders_stats_total', array( $this, 'handle_orders_join' ) );
				add_filter( 'woocommerce_analytics_clauses_join_orders_stats_interval', array( $this, 'handle_orders_join' ) );
			}
		}

		/**
		 * handle_orders_select.
		 *
		 * @version 3.5.0
		 * @since   3.5.0
		 *
		 * @param $query
		 *
		 * @return mixed
		 */
		function handle_orders_select( $query ) {
			$pgbc_convert_rate_sql = $this->generate_convert_rate_sql();
			foreach ( $query as $k => $v ) {
				$query[ $k ] = str_replace( "wp_wc_order_stats.net_total", "(wp_wc_order_stats.net_total/{$pgbc_convert_rate_sql}) as net_total", $v );
			}
			$query[] = ", {$pgbc_convert_rate_sql} AS pgbc_convert_price_rate";
			return $query;
		}

		/**
		 * generate_convert_rate_sql.
		 *
		 * @version 3.5.0
		 * @since   3.5.0
		 *
		 * @return string
		 */
		function generate_convert_rate_sql() {
			return 'IFNULL(REGEXP_SUBSTR(REGEXP_SUBSTR(pgbc_pm.meta_value,\'convert_price_rate\"\;.*?\;\'),\'(?<=\:").*[^\"\;]|(?<=d\:).*[^\;]|(?<=i\:).*[^\;]\'),1)';
		}

		/**
		 * handle_orders_stats_interval.
		 *
		 * @version 3.5.0
		 * @since   3.5.0
		 *
		 * @param $query
		 *
		 * @return mixed
		 */
		function handle_orders_stats_total_and_interval( $query ) {
			$pgbc_convert_rate_sql = $this->generate_convert_rate_sql();
			foreach ( $query as $k => $v ) {
				$query[ $k ] = str_replace( array(
					"wp_wc_order_stats.total_sales",
					"wp_wc_order_stats.tax_total",
					"wp_wc_order_stats.shipping_total",
					"wp_wc_order_stats.net_total"
				), array(
					"wp_wc_order_stats.total_sales/{$pgbc_convert_rate_sql}",
					"wp_wc_order_stats.tax_total/{$pgbc_convert_rate_sql}",
					"wp_wc_order_stats.shipping_total/{$pgbc_convert_rate_sql}",
					"wp_wc_order_stats.net_total/{$pgbc_convert_rate_sql}"
				), $v );
			}
			return $query;
		}

		/**
		 * handle_orders_join.
		 *
		 * @version 3.5.0
		 * @since   3.5.0
		 *
		 * @param $clauses
		 *
		 * @return array
		 */
		function handle_orders_join( $clauses ) {
			global $wpdb;
			$clauses[] = "LEFT JOIN {$wpdb->postmeta} pgbc_pm ON {$wpdb->prefix}wc_order_stats.order_id = pgbc_pm.post_id AND pgbc_pm.meta_key = '_alg_wc_pgbc_data'";
			return $clauses;
		}

	}

endif;

return new Alg_WC_PGBC_Analytics();
