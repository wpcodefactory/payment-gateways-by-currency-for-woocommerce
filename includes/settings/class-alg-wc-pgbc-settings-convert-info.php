<?php
/**
 * Payment Gateway Currency for WooCommerce - Convert - Frontend Info Section Settings
 *
 * @version 3.0.1
 * @since   3.0.0
 *
 * @author  Algoritmika Ltd.
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Alg_WC_PGBC_Settings_Convert_Info' ) ) :

class Alg_WC_PGBC_Settings_Convert_Info extends Alg_WC_PGBC_Settings_Section {

	/**
	 * Constructor.
	 *
	 * @version 3.0.0
	 * @since   3.0.0
	 *
	 * @todo    [next] (dev) rename file and class (i.e. to `class-alg-wc-pgbc-settings-convert-info-frontend.php` and `Alg_WC_PGBC_Settings_Convert_Info_Frontend`)?
	 */
	function __construct() {
		$this->id   = 'convert_info_frontend';
		$this->desc = __( 'Info', 'payment-gateways-by-currency-for-woocommerce' );
		parent::__construct();
		add_action( 'admin_footer', array( $this, 'add_script' ) );
	}

	/**
	 * add_script.
	 *
	 * @version 3.0.0
	 * @since   3.0.0
	 *
	 * @todo    [next] (dev) move this to a separate js file
	 */
	function add_script() {
		?><script>
			jQuery( document ).ready( function() {
				jQuery( '.alg-wc-pgbc-select-all' ).click( function( event ) {
					event.preventDefault();
					jQuery( this ).closest( 'td' ).find( 'select.chosen_select' ).select2( 'destroy' ).find( 'option' ).prop( 'selected', 'selected' ).end().select2();
					return false;
				} );
				jQuery( '.alg-wc-pgbc-deselect-all' ).click( function( event ) {
					event.preventDefault();
					jQuery( this ).closest( 'td' ).find( 'select.chosen_select' ).val( '' ).change();
					return false;
				} );
			} );
		</script><?php
	}

	/**
	 * get_settings.
	 *
	 * @version 3.0.1
	 * @since   3.0.0
	 *
	 * @todo    [next] (dev) add `$select_all_buttons` to all `multiselect`?
	 * @todo    [next] (dev) add `width:100%;` to all `multiselect`?
	 * @todo    [next] (desc) `alg_wc_pgbc_convert_currency_info_compatibility_rp_wcdpd`
	 * @todo    [maybe] (dev) reduce the number of options/hooks/etc.?
	 * @todo    [later] (desc) `alg_wc_pgbc_convert_currency_info_hooks_extra_templates_options`: better desc
	 * @todo    [later] (desc) `alg_wc_pgbc_convert_currency_info_frontend_templates_options`: better desc
	 * @todo    [later] (desc) `alg_wc_pgbc_convert_currency_info_frontend_exceptions_options`: better desc
	 * @todo    [next] (desc) `alg_wc_pgbc_convert_currency_info_hooks`: better desc_tip?
	 * @todo    [next] (dev) `alg_wc_pgbc_convert_currency_info_hooks`: add "Add all cart positions", "Add all order positions", etc. buttons
	 * @todo    [later] (desc) `alg_wc_pgbc_convert_currency_show_info`: better desc?
	 */
	function get_settings() {

		// Prepare data
		$info_hooks_all         = alg_wc_pgbc()->core->convert->info_frontend->positions->get_all();
		$select_all_buttons     = '<a href="#" class="button alg-wc-pgbc-select-all">' . __( 'Select all', 'payment-gateways-by-currency-for-woocommerce' ) . '</a>' . ' ' .
			'<a href="#" class="button alg-wc-pgbc-deselect-all">' . __( 'Deselect all', 'payment-gateways-by-currency-for-woocommerce' ) . '</a>';
		$extra_templates        = array(
			'is_cart'      => __( 'Cart', 'payment-gateways-by-currency-for-woocommerce' ),
			'is_mini_cart' => __( 'AJAX', 'payment-gateways-by-currency-for-woocommerce' ),
		);
		$exceptions             = array(
			'is_cart'      => __( 'Cart', 'payment-gateways-by-currency-for-woocommerce' ),
			'is_checkout'  => __( 'Checkout', 'payment-gateways-by-currency-for-woocommerce' ),
			'is_mini_cart' => __( 'AJAX', 'payment-gateways-by-currency-for-woocommerce' ),
		);
		$info_hooks             = get_option( 'alg_wc_pgbc_convert_currency_info_hooks', alg_wc_pgbc()->core->convert->info_frontend->positions->get_default() );
		$info_hooks_options     = array_combine( $info_hooks, array_intersect_key( $info_hooks_all, array_flip( $info_hooks ) ) );
		$custom_templates       = get_option( 'alg_wc_pgbc_convert_currency_info_hooks_custom_templates', array() );
		$extra_template_hooks   = get_option( 'alg_wc_pgbc_convert_currency_info_hooks_extra_template', array() );
		$extra_template_options = array_combine( $custom_templates, array_intersect_key( $info_hooks_all, array_flip( $custom_templates ) ) );

		// General Settings
		$general_settings = array(
			array(
				'title'    => __( 'Convert Currency', 'payment-gateways-by-currency-for-woocommerce' ) . ': ' . __( 'Frontend Info Options', 'payment-gateways-by-currency-for-woocommerce' ),
				'type'     => 'title',
				'id'       => 'alg_wc_pgbc_convert_currency_info_frontend_options',
			),
			array(
				'title'    => __( 'Frontend info', 'payment-gateways-by-currency-for-woocommerce' ),
				'desc'     => '<strong>' . __( 'Enable', 'payment-gateways-by-currency-for-woocommerce' ) . '</strong>',
				'desc_tip' => __( 'This will display used conversion rate, etc. in cart, checkout, order details, etc. on frontend and in emails.', 'payment-gateways-by-currency-for-woocommerce' ),
				'id'       => 'alg_wc_pgbc_convert_currency_show_info',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'title'    => __( 'Positions', 'payment-gateways-by-currency-for-woocommerce' ),
				'desc'     => $select_all_buttons,
				'desc_tip' => sprintf( __( 'This is affected by the "%s" option.', 'payment-gateways-by-currency-for-woocommerce' ),
						__( 'General', 'payment-gateways-by-currency-for-woocommerce' ) . ' > ' . __( 'Convert on', 'payment-gateways-by-currency-for-woocommerce' ) ) . '<br><br>' .
					sprintf( __( 'Save changes after you add positions here - new options will be added to the "%s" and "%s" options below.', 'payment-gateways-by-currency-for-woocommerce' ),
						__( 'Templates', 'payment-gateways-by-currency-for-woocommerce' ), __( 'Exceptions', 'payment-gateways-by-currency-for-woocommerce' ) ),
				'id'       => 'alg_wc_pgbc_convert_currency_info_hooks',
				'default'  => alg_wc_pgbc()->core->convert->info_frontend->positions->get_default(),
				'type'     => 'multiselect',
				'class'    => 'chosen_select',
				'options'  => $info_hooks_all,
				'css'      => 'width:100%;',
			),
		);
		if ( ! empty( $info_hooks_options ) ) {
			$general_settings = array_merge( $general_settings, array(
				array(
					'title'    => __( 'Templates', 'payment-gateways-by-currency-for-woocommerce' ),
					'desc'     => $select_all_buttons,
					'desc_tip' => __( 'Select positions for which you want to set custom templates.', 'payment-gateways-by-currency-for-woocommerce' ) . '<br><br>' .
						sprintf( __( 'Save changes after you add positions here - new options will be added to the "%s" section and "%s" option below.', 'payment-gateways-by-currency-for-woocommerce' ),
							__( 'Custom Templates', 'payment-gateways-by-currency-for-woocommerce' ), __( 'Extra templates', 'payment-gateways-by-currency-for-woocommerce' ) ),
					'id'       => 'alg_wc_pgbc_convert_currency_info_hooks_custom_templates',
					'default'  => array(),
					'type'     => 'multiselect',
					'class'    => 'chosen_select',
					'options'  => $info_hooks_options,
				),
			) );
		}
		if ( ! empty( $extra_template_options ) ) {
			foreach ( $extra_templates as $scope => $title ) {
				$general_settings = array_merge( $general_settings, array(
					array(
						'title'    => ( 'is_cart' === $scope ? __( 'Extra templates', 'payment-gateways-by-currency-for-woocommerce' ) : '' ),
						'desc_tip' => sprintf( __( 'Some positions (e.g. "%s") will be shown on multiple pages. Here you can set different templates for selected pages. Please note that all positions are listed here, even those that are not displayed on the selected page.', 'payment-gateways-by-currency-for-woocommerce' ),
								__( 'Cart product subtotal', 'payment-gateways-by-currency-for-woocommerce' ) ) . '<br><br>' .
							sprintf( __( 'Save changes after you add positions here - new settings fields will be added to the "%s" section below.', 'payment-gateways-by-currency-for-woocommerce' ),
								__( 'Custom Templates', 'payment-gateways-by-currency-for-woocommerce' ) ),
						'desc'     => $title . ( 'is_mini_cart' === $scope ? ' (' . __( 'e.g. mini-cart', 'payment-gateways-by-currency-for-woocommerce' ) . ')' : '' ),
						'id'       => "alg_wc_pgbc_convert_currency_info_hooks_extra_template[{$scope}]",
						'default'  => array(),
						'type'     => 'multiselect',
						'class'    => 'chosen_select',
						'options'  => $extra_template_options,
					),
				) );
			}
		}
		if ( ! empty( $info_hooks_options ) ) {
			foreach ( $exceptions as $scope => $title ) {
				$general_settings = array_merge( $general_settings, array(
					array(
						'title'    => ( 'is_cart' === $scope ? __( 'Exceptions', 'payment-gateways-by-currency-for-woocommerce' ) : '' ),
						'desc_tip' => sprintf( __( 'Some positions (e.g. "%s") will be shown on multiple pages. Here you can exclude selected positions from selected pages. Please note that all positions are listed here, even those that are not displayed on the selected page.', 'payment-gateways-by-currency-for-woocommerce' ),
							__( 'Cart product subtotal', 'payment-gateways-by-currency-for-woocommerce' ) ),
						'desc'     => $title . ( 'is_mini_cart' === $scope ? ' (' . __( 'e.g. mini-cart', 'payment-gateways-by-currency-for-woocommerce' ) . ')' : '' ),
						'id'       => "alg_wc_pgbc_convert_currency_info_hooks_exceptions[{$scope}]",
						'default'  => array(),
						'type'     => 'multiselect',
						'class'    => 'chosen_select',
						'options'  => $info_hooks_options,
					),
				) );
			}
		}
		$general_settings = array_merge( $general_settings, array(
			array(
				'title'    => __( 'WooCommerce Dynamic Pricing & Discounts', 'payment-gateways-by-currency-for-woocommerce' ),
				'desc'     => __( 'Enable', 'payment-gateways-by-currency-for-woocommerce' ),
				'desc_tip' => sprintf( __( 'Compatibility option for the %s (by %s) plugin.', 'payment-gateways-by-currency-for-woocommerce' ),
						'<a href="https://codecanyon.net/item/woocommerce-dynamic-pricing-discounts/7119279" target="_blank">' .
							__( 'WooCommerce Dynamic Pricing & Discounts', 'payment-gateways-by-currency-for-woocommerce' ) .
						'</a>',
						__( 'RightPress', 'payment-gateways-by-currency-for-woocommerce' ) ) . ' ' .
					sprintf( __( 'Disables "%s" display by the plugin.', 'payment-gateways-by-currency-for-woocommerce' ),
						__( 'Cart product price', 'payment-gateways-by-currency-for-woocommerce' ) ),
				'id'       => 'alg_wc_pgbc_convert_currency_info_compatibility_rp_wcdpd',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'type'     => 'sectionend',
				'id'       => 'alg_wc_pgbc_convert_currency_info_frontend_options',
			),
		) );

		// Templates
		$templates_settings = array(
			array(
				'title'    => __( 'Custom Templates', 'payment-gateways-by-currency-for-woocommerce' ),
				'desc'     => sprintf( __( 'Select some "%s" (and optionally "%s") and save changes - new settings fields will be added here.', 'payment-gateways-by-currency-for-woocommerce' ),
						__( 'Templates', 'payment-gateways-by-currency-for-woocommerce' ),
						__( 'Extra templates', 'payment-gateways-by-currency-for-woocommerce' )
					),
				'type'     => 'title',
				'id'       => 'alg_wc_pgbc_convert_currency_info_frontend_templates_options',
			),
		);
		foreach ( $custom_templates as $info_hook ) {
			$templates_settings = array_merge( $templates_settings, array(
				array(
					'title'    => ( isset( $info_hooks_all[ $info_hook ] ) ? $info_hooks_all[ $info_hook ] : $info_hook ),
					'desc'     => alg_wc_pgbc()->core->convert->info_frontend->positions->get_placeholders_desc( $info_hook ),
					'id'       => "alg_wc_pgbc_convert_currency_info_template[{$info_hook}]",
					'default'  => alg_wc_pgbc()->core->convert->info_frontend->positions->get_default_template( $info_hook ),
					'type'     => 'textarea',
					'css'      => 'width:100%;',
				),
			) );
			foreach ( $extra_template_hooks as $scope => $scope_hooks ) {
				if ( in_array( $info_hook, $scope_hooks ) ) {
					$templates_settings = array_merge( $templates_settings, array(
						array(
							'title'    => ( isset( $info_hooks_all[ $info_hook ] ) ? $info_hooks_all[ $info_hook ] : $info_hook ) .
								' [' . ( isset( $extra_templates[ $scope ] ) ? $extra_templates[ $scope ] : $scope ) . ']',
							'desc'     => alg_wc_pgbc()->core->convert->info_frontend->positions->get_placeholders_desc( $info_hook ),
							'id'       => "alg_wc_pgbc_convert_currency_info_template[{$info_hook}_{$scope}]",
							'default'  => alg_wc_pgbc()->core->convert->info_frontend->positions->get_default_template( $info_hook ),
							'type'     => 'textarea',
							'css'      => 'width:100%;',
						),
					) );
				}
			}
		}
		$templates_settings = array_merge( $templates_settings, array(
			array(
				'type'     => 'sectionend',
				'id'       => 'alg_wc_pgbc_convert_currency_info_frontend_templates_options',
			),
		) );

		return array_merge( $general_settings, $templates_settings );
	}

}

endif;

return new Alg_WC_PGBC_Settings_Convert_Info();
