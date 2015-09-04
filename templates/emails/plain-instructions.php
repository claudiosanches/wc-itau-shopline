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

_e( 'Payment', 'itau-shopline-for-woocommerce' );

echo "\n\n";

_e( 'Please use the link below to make your payment:', 'itau-shopline-for-woocommerce' );

echo "\n";

echo esc_url( $url );

echo "\n";

_e( 'After we receive the payment confirmation, your order will be processed.', 'itau-shopline-for-woocommerce' );

echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";
