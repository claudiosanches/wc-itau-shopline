<?php
/**
 * Payment instructions.
 *
 * @author  Claudio Sanches
 * @package WC_Itau_Shopline/Templates
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

?>

<div class="woocommerce-message">
	<span>
		<a class="button" href="<?php echo esc_url( $url ); ?>" target="_blank" style="display: block !important; visibility: visible !important;"><?php _e( 'Make payment', 'wc-itau-shopline' ); ?></a>
		<?php _e( 'Please click in the following button to make your payment.', 'wc-itau-shopline' ); ?><br />
		<?php _e( 'After we receive the payment confirmation, your order will be processed.', 'wc-itau-shopline' ); ?>
	</span>
</div>
