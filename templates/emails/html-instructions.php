<?php
/**
 * HTML email instructions.
 *
 * @author  Claudio Sanches
 * @package WC_Itau_Shopline/Templates
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>

<h2><?php _e( 'Payment', 'itau-shopline-for-woocommerce' ); ?></h2>

<p class="order_details"><?php _e( 'Please use the link below to make your payment:', 'itau-shopline-for-woocommerce' ); ?><br /><a class="button" href="<?php echo esc_url( $url ); ?>" target="_blank"><?php _e( 'Pay order', 'itau-shopline-for-woocommerce' ); ?></a><br /><?php _e( 'After we receive the payment confirmation, your order will be processed.', 'itau-shopline-for-woocommerce' ); ?></p>
