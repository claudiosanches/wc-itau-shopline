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
		<a class="button" href="<?php echo esc_url( $url ); ?>" target="_blank" style="display: block !important; visibility: visible !important;"><?php esc_html_e( 'Make payment', 'wc-itau-shopline' ); ?></a>
		<?php if ( $billet_only ) : ?>
			<?php esc_html_e( 'Please use the link below get your banking billet:', 'wc-itau-shopline' ); ?>
		<?php else : ?>
			<?php esc_html_e( 'Please use the link below to make your payment:', 'wc-itau-shopline' ); ?>
		<?php endif; ?>
		<br />
		<?php esc_html_e( 'After we receive the payment confirmation, your order will be processed.', 'wc-itau-shopline' ); ?>
	</span>
</div>
