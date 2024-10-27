=== Payment Gateway Currency for WooCommerce ===
Contributors: wpcodefactory, algoritmika, anbinder, karzin, omardabbas, kousikmukherjeeli
Tags: woocommerce, payment gateway, payment method, currency, multi-currency
Requires at least: 4.4
Tested up to: 6.6
Stable tag: 4.1.0
License: GNU General Public License v3.0
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Manage currencies for WooCommerce payment gateways. Beautifully.

== Description ==

**Payment Gateway Currency for WooCommerce** plugin lets you manage currencies for WooCommerce payment gateways. The plugin can work in two modes:

* **Convert currencies** - convert cart currencies and prices by the currency exchange rates.
* **Restrict currencies** - simple mode that lets you set allowed currencies for payment gateways to show up.

### &#9989; Convert Currencies ###

This mode will convert cart currencies and prices by the currency exchange rates based on the selected payment gateway. For example, you can set the order total to be converted to euros (EUR) for the "Direct bank transfer" gateway, and use US dollars (USD) for all your remaining payment gateways.

* Currency exchange rates can be set manually, or updated automatically from the selected server or plugin, for example, from the "European Central Bank (ECB)" server, or from the "WooCommerce Multilingual (WPML)" plugin.
* Shipping, coupons and cart fees conversions are optional.
* Prices can be converted right away on the cart and checkout pages, or only on the "thank you" page and in the final order.
* Optionally show currently used currency conversion rates, converted and unconverted prices on frontend and in emails to your customers.
* WooCommerce Analytics compatible.
* And more...

### &#9989; Restrict Currencies ###

This mode lets you set allowed currencies for WooCommerce payment gateways. For example, you can set the "Check payments" gateway to accept US dollars (USD) or euros (EUR) only, so this gateway will be shown on frontend checkout only for the selected currencies. For each payment gateway you can set "Allowed currencies" (i.e., payment gateway will be available ONLY for selected currencies) or "Denied currencies" (i.e., payment gateway will be NOT available for selected currencies) lists. For example, this is useful if you are using some additional currency switcher plugin.

### &#129309; Compatibility ###

* [WooCommerce PayPal Payments](https://wordpress.org/plugins/woocommerce-paypal-payments/) plugin.
* [WooCommerce PayPal Checkout Gateway](https://wordpress.org/plugins/woocommerce-gateway-paypal-express-checkout/) plugin.
* [PayPal for WooCommerce by Angell EYE](https://www.angelleye.com/product/woocommerce-paypal-plugin/) plugin.
* [PayFast](https://wordpress.org/plugins/woocommerce-payfast-gateway/) plugin.
* [YITH WooCommerce Account Funds Premium](https://yithemes.com/themes/plugins/yith-woocommerce-account-funds/) plugin.
* [YITH WooCommerce Product Add-Ons](https://wordpress.org/plugins/yith-woocommerce-product-add-ons/) plugin.
* [WooCommerce Multilingual (WPML)](https://wordpress.org/plugins/woocommerce-multilingual/) plugin.
* [WOOCS – Currency Switcher for WooCommerce](https://wordpress.org/plugins/woocommerce-currency-switcher/) plugin.
* [WooCommerce Subscriptions](https://woocommerce.com/products/woocommerce-subscriptions/) plugin.
* And more...

### &#127942; Premium Version ###

The **free version** allows setting currencies for all standard WooCommerce payment gateways, i.e.:

* Direct bank transfer,
* Check payments,
* Cash on delivery (COD),
* PayPal (including [WooCommerce PayPal Payments](https://wordpress.org/plugins/woocommerce-paypal-payments/)).

With the [Pro version](https://wpfactory.com/item/payment-gateways-by-currency-for-woocommerce/) you can set currencies for **any payment gateway**.

### &#128472; Feedback ###

* We are open to your suggestions and feedback. Thank you for using or trying out one of our plugins!
* [Visit plugin site](https://wpfactory.com/item/payment-gateways-by-currency-for-woocommerce/).

### &#8505; More ###

* The plugin is **"High-Performance Order Storage (HPOS)"** compatible.

== Installation ==

1. Upload the entire plugin folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the "Plugins" menu in WordPress.
3. Start by visiting plugin settings at "WooCommerce > Settings > Payment Gateway Currency".

== Screenshots ==

1. Restrict currencies.
2. Convert currencies.

== Changelog ==

= 4.1.0 - 27/10/2024 =
* Dev - Plugin settings moved to the "WPFactory" menu.
* Dev - "Recommendations" added.
* Dev - Key Manager - Library updated.
* Dev - Code refactoring.

= 4.0.1 - 10/10/2024 =
* Dev - WooCommerce PayPal Payments - Compatibility issue fixed.

= 4.0.0 - 07/10/2024 =
* Dev - "Key Manager" added.
* Dev - Code refactoring.
* WC tested up to: 9.3.

= 3.9.3 - 28/07/2024 =
* Dev - Advanced - "Check shop currency" option added (defaults to `no`).
* Tested up to: 6.6.
* WC tested up to: 9.1.

= 3.9.2 - 30/05/2024 =
* Dev - Advanced - "Check single product page" option added (defaults to `no`).
* WC tested up to: 8.9.
* WooCommerce added to the "Requires Plugins" (plugin header).

= 3.9.1 - 01/05/2024 =
* Dev - WooCommerce Multilingual (WPML) compatibility issue fixed.
* WC tested up to: 8.8.

= 3.9.0 - 05/04/2024 =
* Dev - Convert Currency - "Number of decimals" options added.
* Dev - Convert Currency - Admin - "Number of decimals in admin" option added (defaults to `no`).
* Dev - PHP 8.2 compatibility - "Creation of dynamic property is deprecated" notice fixed.
* Tested up to: 6.5.
* WC tested up to: 8.7.
* Readme.txt - Tags updated.

= 3.8.2 - 30/11/2023 =
* Dev - "Gravity Forms" plugin compatibility issue fixed (see `alg_wc_pgbc_convert_filterable_scripts_l10n`).
* Tested up to: 6.4.
* WC tested up to: 8.3.

= 3.8.1 - 18/10/2023 =
* Dev - Convert Currency - Compatibility - PayPal for WooCommerce by Angell EYE - Algorithm improved (`alg_wc_pgbc_convert_filterable_scripts_l10n` callback added).
* Dev - Convert Currency - Declaring `Alg_WC_PGBC_Convert` class properties.
* WC tested up to: 8.2.

= 3.8.0 - 27/09/2023 =
* Dev - Convert Currency - Advanced - Cache product ID - "Product ID and product changes" option added.
* Dev - Convert Currency - Compatibility - YITH WooCommerce Product Add-Ons - Algorithm improved (using `yith_wapo_get_addon_price` and `yith_wapo_get_addon_sale_price` filters instead of the `yith_wapo_addon_prices_on_cart` filter).
* Dev - Convert Currency - Compatibility - YITH WooCommerce Product Add-Ons - Algorithm improved (see `Alg_WC_PGBC_Convert_Prices::convert_price()`).
* Dev - Convert Currency - Compatibility - WooCommerce PayPal Payments - Algorithm improved (see `Alg_WC_PGBC_Convert::do_convert()`).
* Dev - Convert Currency - Admin settings rearranged - "Compatibility" settings section added (options moved from the "Advanced" section).
* Dev - Convert Currency - Admin settings descriptions updated.

= 3.7.6 - 26/09/2023 =
* WC tested up to: 8.1.
* Plugin icon, banner updated.

= 3.7.5 - 12/09/2023 =
* Dev - Convert Currency - Advanced - "YITH WooCommerce Product Add-Ons" option added (defaults to `no`).

= 3.7.4 - 06/09/2023 =
* Dev - Convert Currency - Advanced - WooCommerce PayPal Payments - Algorithm improved: now modifying `$l10n['url_params']['currency']` and `$l10n['currency']` as well.
* Dev - Convert Currency - Code refactoring.

= 3.7.3 - 21/08/2023 =
* Fix - Declaring HPOS compatibility for the free plugin version, even if the Pro version is activated.
* Dev - Convert Currency - Advanced - WooCommerce PayPal Payments - Algorithm improved: now modifying `$l10n['url']` as well (in addition to `$l10n['button']['url']`).
* Tested up to: 6.3.
* WC tested up to: 8.0.

= 3.7.2 - 18/06/2023 =
* WC tested up to: 7.8.

= 3.7.1 - 30/05/2023 =
* Fix - Convert Currency - Admin - WooCommerce Analytics - Refunded orders are not getting recalculated.

= 3.7.0 - 17/05/2023 =
* Dev - Convert Currency - Admin - "Orders list > Original total" - option added (defaults to `no`).
* Dev - Convert Currency - Admin - Order total in admin - New placeholders added: `%currency_symbol%`, `%convert_price_rate%`, `%order_total_original%`.
* Dev - Convert Currency - Admin - Order page - "Original total" row added.
* Dev - Convert Currency - Admin - Settings descriptions updated.

= 3.6.1 - 15/05/2023 =
* Fix - Possible "Call to a member function get_meta() on bool" error fixed.

= 3.6.0 - 12/05/2023 =
* Fix - Convert Currency - Admin - WooCommerce Analytics - Some database tables are using the prefix hardcoded.
* Dev - "High-Performance Order Storage (HPOS)" compatibility.
* Tested up to: 6.2.
* WC tested up to: 7.7.

= 3.5.0 - 19/09/2022 =
* Dev - Convert Currency - Admin - WooCommerce Analytics - "Orders and Revenue > Recalculate values from the orders and revenue tabs based on the conversion rate" option added (defaults to `no`).
* WC tested up to: 6.9.

= 3.4.3 - 24/08/2022 =
* Dev - Convert Currency - Advanced - "YITH WooCommerce Account Funds Premium" option added (defaults to `no`).
* WC tested up to: 6.8.

= 3.4.2 - 31/07/2022 =
* Dev - Convert Currency - Advanced - "WooCommerce PayPal Payments" option added (defaults to `no`).
* Dev - Convert Currency - Advanced - "PayPal for WooCommerce by Angell EYE" option added (defaults to `no`).
* WC tested up to: 6.7.

= 3.4.1 - 25/05/2022 =
* Dev - "WooCommerce PayPal Payments" gateway moved to the free version.
* Dev - Deploy script added.
* Tested up to: 6.0.

= 3.4.0 - 13/05/2022 =
* Dev - Convert Currency - Automatic Currency Exchange Rates - Update periodically from server:
    * "WP-Cron" replaced with "Action Scheduler".
    * "Interval (in seconds)" option added.
    * Fixer.io - "URL" option added.
    * Admin settings rearranged.
* WC tested up to: 6.5.

= 3.3.1 - 13/04/2022 =
* Dev - Convert Currency - Advanced - "Always show PayFast" option added (defaults to `yes`).

= 3.3.0 - 05/04/2022 =
* Dev - Convert Currency - Automatic Currency Exchange Rates - "Multiplier" option added.
* Dev - Convert Currency - "WOOCS – Currency Switcher for WooCommerce" plugin compatibility added.
* Dev - Convert Currency - Shortcodes - `[alg_wc_pgbc_product_price_table]` - Preparing price now (i.e., un-converting WPML, etc. conversions).
* Dev - Convert Currency - Developers - `alg_wc_pgbc_convert_currency_rate` filter added.
* Dev - Convert Currency - Developers - `alg_wc_pgbc_do_convert_shipping_package_rate` filter added.
* Dev - Convert Currency - General - Convert on languages - Description updated (Polylang plugin included).
* Tested up to: 5.9.
* WC tested up to: 6.3.

= 3.2.0 - 26/11/2021 =
* Dev - Convert Currency - Rates - ECB - More data added to the log, in case if any errors occur.
* Dev - Convert Currency - Admin - Order page - "Add convert button" option added.
* Dev - Convert Currency - Shipping - Now converting the shipping price even if the "Conversion rate" option is set to 1. This fixes the issue with WPML `unconvert_price_amount()` function.
* Dev - Code refactoring.
* WC tested up to: 5.9.

= 3.1.0 - 28/10/2021 =
* Fix - Convert Currency - WooCommerce Multilingual (WPML) - Checking if plugin's "Multi-currency" module is enabled as well.
* Dev - Convert Currency - General - Convert on - "Convert on WPML languages" option added.
* Dev - Convert Currency - Developers - `alg_wc_pgbc_convert_currency_do_convert` filter added.
* Dev - Convert Currency - Developers - `alg_wc_pgbc_convert_currency_get_gateway_rate` filter added.
* Dev - Convert Currency - Developers - `alg_wc_pgbc_convert_currency_get_shop_currency` filter added.
* WC tested up to: 5.8.

= 3.0.2 - 10/09/2021 =
* Dev - Convert Currency - Advanced - Cache prices - "Cache product ID" option added (defaults to `Product ID`).

= 3.0.1 - 25/08/2021 =
* Dev - Convert Currency - Advanced - "WooCommerce PayPal Checkout Gateway" option added (defaults to `yes`).
* Dev - Backward PHP compatibility added (tested with PHP v7.2.4).
* WC tested up to: 5.6.

= 3.0.0 - 15/08/2021 =
* Dev - Convert Currency - General - "Convert on checkout" option renamed to "Convert on". "Checkout only" value added.
* Dev - Convert Currency - General - Convert on - "Convert on AJAX" option added (defaults to `yes`).
* Dev - Convert Currency - Info - "Cart product price", "Cart product subtotal", "Cart subtotal", "Cart total", "Cart totals: Shipping", "Cart totals: Taxes", "Cart totals: Coupons", "Cart totals: Fees", "Order total", "Order subtotal", "Order totals: Discount", "Order totals: Shipping", "Order totals: Fees", "Order totals: Taxes", "Order product subtotal" positions added.
* Dev - Convert Currency - Info - `%price%`, `%unconverted_price%` placeholders added.
* Dev - Convert Currency - Info - "Single product summary" position added.
* Dev - Convert Currency - Info - `[alg_wc_pgbc_product_price_table]` shortcode added.
* Dev - Convert Currency - Info - "Templates" option added.
* Dev - Convert Currency - Info - "Extra templates" options added.
* Dev - Convert Currency - Info - "Exceptions" options added.
* Dev - Convert Currency - Info - "WooCommerce Dynamic Pricing & Discounts" compatibility option added.
* Dev - Convert Currency - Info - Positions renamed, e.g., "Cart order totals" to "Cart totals: After order total", etc.
* Dev - Convert Currency - Info - Positions - Defaults to all available positions now.
* Dev - Convert Currency - Advanced - "Cache prices" option added (defaults to `yes`).
* Dev - Convert Currency - Advanced - "Fix RTL currencies" option added (defaults to `no`).
* Dev - Convert Currency - Advanced - Recalculate cart - Updated, now setting session's `cart_totals` to null instead of recalculating cart directly.
* Dev - Convert Currency - Advanced - Recalculate cart - Defaults to `yes` now.
* Dev - Convert Currency - Advanced - Force session start - Defaults to `yes` now.
* Dev - Convert Currency - Advanced - Lock gateway on order payment - Defaults to `yes` now.
* Dev - Admin settings restyled. "Convert Currency: Options" section split into "General", "Info", "Admin", "Advanced".
* Dev - Admin settings descriptions updated.
* Dev - Developers - `alg_wc_pgbc_convert_currency_info_get_output_placeholders` filter added.
* Dev - Code refactoring.
* WC tested up to: 5.5.
* Tested up to: 5.8.

= 2.1.0 - 29/06/2021 =
* Dev - Convert Currency - Advanced - "Lock gateway on order payment" option added.
* Dev - Convert Currency - Advanced - "Rate step" options added.
* Dev - Code refactoring.
* WC tested up to: 5.4.

= 2.0.0 - 03/06/2021 =
* Dev - Convert Currency - "WooCommerce Multilingual (WPML)" plugin compatibility added.
* Dev - Convert Currency - Automatic Currency Exchange Rates - "Get from plugin" option added. New plugin added: "WooCommerce Multilingual (WPML)".
* Dev - Convert Currency - Automatic Currency Exchange Rates - "Server" options added. New server added: "Fixer.io".
* Dev - Convert Currency - Automatic Currency Exchange Rates - Unscheduling cron even if "Convert" section is disabled.
* Dev - Convert Currency - General - Convert - "Free shipping min amount" option added (defaults to `yes`).
* Dev - Convert Currency - General - Convert - Shipping price - Shipping cost conversion in subscription fixed (for the "WooCommerce Subscriptions" plugin).
* Dev - Convert Currency - General - Convert - Cart fees - Default value changed to `yes`.
* Dev - Convert Currency - General - "Convert on checkout" option added (defaults to `yes`).
* Dev - Convert Currency - Info - "Frontend info" options added.
* Dev - Convert Currency - Info - "Admin Info" options added. It displays saved conversion rate, etc., and optionally adds "Recalculate with new rate" button to the meta box.
* Dev - Convert Currency - Info - "Currency symbol in admin" option added (defaults to `no`).
* Dev - Convert Currency - Info - "Order total in admin" options added (defaults to `no`).
* Dev - Convert Currency - Advanced - "Always show PayPal" option added (defaults to `yes`).
* Dev - Convert Currency - Advanced - "Recalculate cart" option added (defaults to `no`).
* Dev - Convert Currency - Advanced - "WooCommerce Subscriptions > Recalculate renewal orders" option added (for the "WooCommerce Subscriptions" plugin).
* Dev - Convert Currency - Advanced - "Debug" option added (defaults to `no`).
* Dev - Convert Currency - Advanced - "Current gateway fallbacks" option added.
* Dev - Convert Currency - Advanced - "Force session start" option added (defaults to `no`).
* Dev - Convert Currency - Saving used conversion rate, etc. in order meta now.
* Dev - Convert Currency - Loading conversion hooks on `init` action now.
* Dev - Convert Currency - Admin settings split into "Convert Currency" and "Convert Currency: Options" sections.
* Dev - Convert Currency - Admin settings descriptions updated.
* Dev - Restrict currency - Enable section - Default value changed to `no`.
* Dev - Initializing plugin on `plugins_loaded` action now.
* Dev - Admin settings section order changed ("Convert Currency" section is listed first now).
* Dev - Code refactoring.
* WC tested up to: 5.3.

= 1.5.0 - 05/04/2021 =
* Dev - Convert Currency - "Automatic Currency Exchange Rates" options added.
* Dev - Code refactoring.
* WC tested up to: 5.1.
* Tested up to: 5.7.

= 1.4.1 - 01/03/2021 =
* Dev - Settings - Now using gateway's `method_title` instead of `title`. This fixes the issue with some gateways (e.g., "iyzico WooCommerce") not displaying title in plugin settings properly.
* WC tested up to: 5.0.

= 1.4.0 - 24/12/2020 =
* Dev - "Convert Currency" section added.
* Dev - "General" section renamed to "Restrict Currency".
* Dev - Localization - `load_plugin_textdomain` moved to the `init` action.
* Dev - Code refactoring.
* Dev - Admin settings descriptions updated.
* Plugin renamed (was "Payment Gateways by Currency for WooCommerce").
* Tested up to: 5.6.
* WC tested up to: 4.8.

= 1.3.0 - 22/07/2020 =
* Dev - All four standard WooCommerce payment gateways (Direct bank transfer, Check payments, Cash on delivery, PayPal) added to the free version.
* Dev - PayPal allowed currencies list updated (`INR` added).
* Dev - Code refactoring.
* Tested up to: 5.4.
* WC tested up to: 4.3.

= 1.2.0 - 27/03/2020 =
* Fix - "Reset settings" message fixed.
* Dev - Admin settings descriptions updated.
* Dev - Code refactoring.
* POT file uploaded.
* Tested up to: 5.3.
* WC tested up to: 4.0.

= 1.1.0 - 10/07/2019 =
* Dev - Code refactoring.
* Dev - "Your settings have been reset" admin notice added.
* Plugin URI updated.
* WC tested up to: 3.6.
* Tested up to: 5.2.

= 1.0.0 - 27/04/2018 =
* Initial Release.

== Upgrade Notice ==

= 1.0.0 =
This is the first release of the plugin.
