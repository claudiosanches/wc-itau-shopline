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
		add_filter( 'cron_schedules', array( $this, 'custom_schedule' ) );
		add_action( 'wcitaushoplinesounder', array( $this, 'sounder' ) );
	}

	/**
	 * Create custom schendule.
	 *
	 * @param  array $schedules
	 *
	 * @return array
	 */
	public function custom_schedule( $schedules ) {
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
		wp_schedule_event( time(), 'itau_shopline', 'wcitaushoplinesounder' );
	}

	/**
	 * Clear scheduled event.
	 */
	public static function clear_scheduled_event() {
		wp_clear_scheduled_hook( 'wcitaushoplinesounder' );
	}

	/**
	 * Sounder.
	 */
	public static function sounder() {
		global $wpdb;

		$orders = $wpdb->get_results( "
			SELECT posts.ID
			FROM $wpdb->posts AS posts
			LEFT JOIN $wpdb->postmeta AS postmeta ON postmeta.post_id = posts.ID
			WHERE postmeta.meta_key = '_payment_method'
			AND postmeta.meta_value = 'itau-shopline'
			AND posts.post_status = 'wc-on-hold'
		 " );

		foreach ( $orders as $_order ) {
			self::process_order_status( $_order->ID );
		}
	}

	/**
	 * Process order status.
	 *
	 * @param  int $order_id
	 */
	protected static function process_order_status( $order_id ) {
		$order_id = intval( $order_id );
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

		$processed_status = $api->process_order_status( $order_id );
	}
}
