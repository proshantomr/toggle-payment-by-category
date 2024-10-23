<?php
/**
 * Plugin Name:       Toggle Payment By Category
 * Plugin URI:        https://woocopilot.com/plugins/toggle-payment-by-category/
 * Description:       "Toggle Payment by Category" is a plugin that allows administrators to enable or disable specific payment methods based on product categories in an online store. It provides greater flexibility by ensuring that certain payment options are available or restricted for different product types, enhancing the checkout experience for both the store owner and customers.
 * Version:           1.0.0
 * Requires at least: 6.5
 * Requires PHP:      7.2
 * Author:            WooCopilot
 * Author URI:        https://woocopilot.com/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       toggle-payment-by-category
 * Domain Path:       /languages
 *
 * @package           TogglePaymentByCategory
 */

defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/includes/class-admin-toggle-payments-by-category.php';
require_once __DIR__ . '/includes/class-toggle-payments-by-category.php';

/**
 * Initializing plugin.
 *
 * @since 1.0.0
 * @return object Plugin object.
 */
function toggle_payments_by_category() {
	return new Toggle_Payments_By_Category( __FILE__, '1.0.0' );
}

toggle_payments_by_category();
