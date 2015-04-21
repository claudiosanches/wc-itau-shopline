<?php
/**
 * Plugin Name: WooCommerce Itaú Shopline
 * Plugin URI: https://github.com/claudiosmweb/woocommerce-itau-shopline
 * Description: Itaú Shopline payment gateway for WooCommerce.
 * Author: Claudio Sanches
 * Author URI: https://claudiosmweb.com/
 * Version: 1.0.0
 * License: GPLv2 or later
 * Text Domain: woocommerce-itau-shopline
 * Domain Path: languages/
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WC_Itau_Shopline' ) ) :

/**
 * WooCommerce Itaú Shopline main class.
 */
class WC_Itau_Shopline {

	/**
	 * Plugin version.
	 *
	 * @var string
	 */
	const VERSION = '1.0.0';

	/**
	 * Instance of this class.
	 *
	 * @var object
	 */
	protected static $instance = null;

	/**
	 * Initialize the plugin actions.
	 */
	public function __construct() {
		// Load plugin text domain.
		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );

		// Checks with WooCommerce and WooCommerce is installed.
		if ( class_exists( 'WC_Payment_Gateway' ) && defined( 'WC_VERSION' ) && version_compare( WC_VERSION, '2.2', '>=' ) ) {
			$this->includes();

			// Hook to add Itaú Shopline Gateway to WooCommerce.
			add_filter( 'woocommerce_payment_gateways', array( $this, 'add_gateway' ) );
			add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'plugin_action_links' ) );
		} else {
			add_action( 'admin_notices', array( $this, 'dependencies_notices' ) );
		}
	}

	/**
	 * Return an instance of this class.
	 *
	 * @return object A single instance of this class.
	 */
	public static function get_instance() {
		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Load the plugin text domain for translation.
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain( 'woocommerce-itau-shopline', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	/**
	 * Includes.
	 */
	private function includes() {
		include_once 'includes/wc-class-itau-shopline-cripto.php';
	}

	/**
	 * Add the gateway to WooCommerce.
	 *
	 * @param  array $methods WooCommerce payment methods.
	 *
	 * @return array          Payment methods with Itaú Shopline.
	 */
	public function add_gateway( $methods ) {
		$methods[] = 'WC_Itau_Shopline_Gateway';

		return $methods;
	}

	/**
	 * Dependencies notices.
	 */
	public function dependencies_notices() {
		include_once 'includes/views/html-notice-woocommerce-missing.php';
	}

	/**
	 * Action links.
	 *
	 * @param  array $links
	 *
	 * @return array
	 */
	public function plugin_action_links( $links ) {
		$plugin_links   = array();
		$plugin_links[] = '<a href="' . esc_url( admin_url( 'admin.php?page=wc-settings&tab=checkout&section=wc_itau_shopline_gateway' ) ) . '">' . __( 'Settings', 'woocommerce-itau-shopline' ) . '</a>';

		return array_merge( $plugin_links, $links );
	}
}

add_action( 'plugins_loaded', array( 'WC_Itau_Shopline', 'get_instance' ) );

endif;
