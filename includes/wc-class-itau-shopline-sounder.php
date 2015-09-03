<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WooCommerce Itau Shopline Sounder class.
 *
 * @class   WC_Itau_Shopline_Sounder
 * @version 1.0.0
 * @author  Claudio Sanches
 */
class WC_Itau_Shopline_Sounder {

	/**
	 * Initialize sounder actions
	 */
	public function __construct() {
		add_filter( 'cron_schedules', array( __CLASS__, 'sounder_schedule' ) );
		add_action( 'wc_itau_shopline_sounder', array( $this, 'sounder' ) );
	}

	/**
	 * Create sounder schendule.
	 *
	 * @param  array $schedules
	 *
	 * @return array
	 */
	public static function sounder_schedule( $schedules ) {
		$schedules['itau_shopline'] = array(
			'interval' => 10800,
			'display'  => __( 'Itau Shoptine - Every 3 hours', 'woocommerce-itau-shopline' )
		);

		return $schedules;
	}

	/**
	 * Schedule event.
	 */
	public static function schedule_event() {
		wp_schedule_event( current_time( 'timestamp' ), 'itau_shopline', 'wc_itau_shopline_sounder' );
	}

	/**
	 * Clear scheduled event.
	 */
	public static function clear_scheduled_event() {
		wp_clear_scheduled_hook( 'wc_itau_shopline_sounder' );
	}

	/**
	 * Get API instance.
	 *
	 * @return WC_Itau_Shopline_API
	 */
	protected static function get_api_instance() {
		$settings = get_option( 'woocommerce_itau-shopline_settings', array() );
		$api      = new WC_Itau_Shopline_API(
			$settings['website_code'],
			$settings['encryption_key'],
			$settings['title'],
			$settings['days_to_pay'],
			$settings['note_line1'],
			$settings['note_line2'],
			$settings['note_line3'],
			$settings['debug']
		);

		return $api;
	}

	/**
	 * Sounder.
	 */
	public static function sounder() {
		global $wpdb;

		$api    = self::get_api_instance();
		$orders = $wpdb->get_results( "
			SELECT posts.ID
			FROM $wpdb->posts AS posts
			LEFT JOIN $wpdb->postmeta AS postmeta ON postmeta.post_id = posts.ID
			WHERE postmeta.meta_key = '_payment_method'
			AND postmeta.meta_value = 'itau-shopline'
			AND posts.post_status = 'wc-on-hold'
		 " );

		// Process the order status for on-hold orders.
		foreach ( $orders as $_order ) {
			$api->process_order_status( intval( $_order->ID ) );
		}
	}
}

new WC_Itau_Shopline_Sounder();
