<?php
/**
 * Admin options screen.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<h3><?php echo $this->method_title; ?></h3>

<?php
	if ( ! $this->using_supported_currency() && ! class_exists( 'woocommerce_wpml' ) ) {
		include 'html-notice-currency-not-supported.php';
	}
?>

<?php echo wpautop( $this->method_description ); ?>

<?php if ( apply_filters( 'wc_itau_shopline_help_message', true ) ) : ?>
	<div class="updated woocommerce-message">
		<p><?php printf( __( 'Help us keep the %s plugin free making a %s or rate %s on %s. Thank you in advance!', 'itau-shopline-for-woocommerce' ), '<strong>' . __( 'Itau Shopline for WooCommerce', 'itau-shopline-for-woocommerce' ) . '</strong>', '<a href="http://claudiosmweb.com/doacoes/">' . __( 'donation', 'itau-shopline-for-woocommerce' ) . '</a>', '<a href="https://wordpress.org/support/view/plugin-reviews/itau-shopline-for-woocommerce?filter=5#postform" target="_blank">&#9733;&#9733;&#9733;&#9733;&#9733;</a>', '<a href="https://wordpress.org/support/view/plugin-reviews/itau-shopline-for-woocommerce?filter=5#postform" target="_blank">' . __( 'WordPress.org', 'itau-shopline-for-woocommerce' ) . '</a>' ); ?></p>
	</div>
<?php endif; ?>

<table class="form-table">
	<?php $this->generate_settings_html(); ?>
</table>
