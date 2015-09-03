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

<h2><?php _e( 'Payment', 'woocommerce-itau-shopline' ); ?></h2>

<p class="order_details"><?php _e( 'Please use the link below to make your payment:', 'woocommerce-itau-shopline' ); ?><br /><a class="button" href="<?php echo esc_url( $url ); ?>" target="_blank"><?php _e( 'Pay order', 'woocommerce-itau-shopline' ); ?></a><br /><?php _e( 'After we receive the payment confirmation, your order will be processed.', 'woocommerce-itau-shopline' ); ?></p>
