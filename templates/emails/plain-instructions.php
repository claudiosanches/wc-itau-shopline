<?php
/**
 * Plain email instructions.
 *
 * @author  Claudio Sanches
 * @package WC_Itau_Shopline/Templates
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

_e( 'Payment', 'woocommerce-itau-shopline' );

echo "\n\n";

_e( 'Please use the link below to make your payment:', 'woocommerce-itau-shopline' );

echo "\n";

echo esc_url( $url );

echo "\n";

_e( 'After we receive the payment confirmation, your order will be processed.', 'woocommerce-itau-shopline' );

echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";
