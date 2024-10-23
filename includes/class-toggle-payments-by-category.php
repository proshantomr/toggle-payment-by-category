<?php

defined( 'ABSPATH' ) || exit;

/**
 * Class Toggle_Payments_By_Category
 *
 * This class handles the core functionality of the Toggle Payment By Category plugin.
 * It defines constants, hooks, and registers the activation and deactivation hooks.
 *
 * @package TogglePaymentByCategory
 * @since 1.0.0
 */
class Toggle_Payments_By_Category {

	/**
	 * Plugin file path.
	 *
	 * @var string
	 */
	public string $file;

	/**
	 * Plugin version.
	 *
	 * @var string
	 */

	public string $version;

	/**
	 * Constructor for the class.
	 *
	 * Initializes the plugin by setting the file path and version, defining constants,
	 * and registering activation and deactivation hooks.
	 *
	 * @param string $file    Path to the main plugin file.
	 * @param string $version Plugin version.
	 */
	public function __construct( $file, $version = '1.0.0' ) {
		$this->file    = $file;
		$this->version = $version;
		$this->define_constants();
		$this->inithooks();

		register_activation_hook( $file, array( $this, 'activate' ) );
		register_deactivation_hook( $file, array( $this, 'deactivate' ) );
	}

	/**
	 * Defines plugin constants.
	 *
	 * This function defines constants that store the version, file path, directory path,
	 * URL, and the plugin basename. These constants are used throughout the plugin
	 * to avoid hardcoding these values in multiple places.
	 *
	 * @since 1.0.0
	 */
	public function define_constants() {
		define( 'TPBC_VERSION', $this->version );
		define( 'TPBC_FILE', $this->file );
		define( 'TPBC_PLUGIN_DIR', plugin_dir_path( $this->file ) );
		define( 'TPBC_PLUGIN_URL', plugin_dir_url( $this->file ) );
		define( 'TPBC_PLUGIN_BASENAME', plugin_basename( $this->file ) );
	}

	/**
	 * Initializes hooks for the plugin.
	 *
	 * This function registers WordPress actions and filters to initialize the plugin.
	 * - It hooks into the 'init' action to initialize plugin functionality.
	 * - It hooks into 'plugins_loaded' to load the plugin's text domain for translations.
	 * - It hooks into the 'woocommerce_available_payment_gateways' filter to customize available payment gateways during checkout.
	 *
	 * @since 1.0.0
	 */
	public function inithooks() {
		add_action( 'init', array( $this, 'init' ) );
		add_action( 'plugins_loaded', array( $this, 'load_plugin_textdomain' ) );
		add_filter( 'woocommerce_available_payment_gateways', array( $this, 'checkout_payment' ) );
	}

	/**
	 * Plugin activation hook.
	 *
	 * This function is triggered upon plugin activation. Activation logic can be added here
	 * if needed (e.g., creating database tables or setting default options).
	 *
	 * @since 1.0.0
	 */
	public function activate() {
		// Activation logic here if needed.
	}

	/**
	 * Plugin deactivation hook.
	 *
	 * This function is triggered upon plugin deactivation. Deactivation logic can be added here
	 * if needed (e.g., cleaning up settings or removing temporary data).
	 *
	 * @since 1.0.0
	 */
	public function deactivate() {
		// Deactivation logic here if needed.
	}

	/**
	 * Initializes the admin functionality.
	 *
	 * This function initializes the plugin's admin-related functionality by creating
	 * an instance of the `Toggle_Payments_By_Category_Admin` class.
	 *
	 * @since 1.0.0
	 */
	public function init() {
		new Toggle_Payments_By_Category_Admin();
	}

	/**
	 * Loads the plugin textdomain for translations.
	 *
	 * This function loads the textdomain to enable translation of the plugin. It looks
	 * for translation files in the `languages` directory.
	 *
	 * @since 1.0.0
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain( 'replace-variable-price-with-active-variation', false, basename( __DIR__ ) . '/languages/' );
	}

	/**
	 * Filters available payment gateways based on product categories in the cart.
	 *
	 * This function checks the product categories in the WooCommerce cart and compares them
	 * against the plugin's payment settings. It removes payment gateways that are restricted
	 * for the product categories present in the cart.
	 *
	 * @param array $available_gateways List of available payment gateways.
	 *
	 * @return array Modified list of available payment gateways.
	 *
	 * @since 1.0.0
	 */
	public function checkout_payment( $available_gateways ) {

		if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
			return $available_gateways;
		}

		$payment_settings = get_option( 'tpbc_payment_settings', array() );

		$cart_categories = array();

		// Gather product categories from the cart.
		foreach ( WC()->cart->get_cart() as $cart_item ) {
			$product_id         = $cart_item['product_id'];
			$product_categories = wp_get_post_terms( $product_id, 'product_cat', array( 'fields' => 'ids' ) );
			$cart_categories    = array_merge( $cart_categories, $product_categories );
		}

		$cart_categories = array_unique( $cart_categories );

		foreach ( $payment_settings as $setting ) {
			// Check if the category from settings is in the cart categories.
			// phpcs:ignore
			if ( in_array( $setting['category'], $cart_categories ) ) {
				if ( $setting['method'] && 'hide' === $setting['visibility'] ) {
					unset( $available_gateways[ $setting['method'] ] );
				}
			}
		}

		return $available_gateways;
	}
}
