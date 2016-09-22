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

esc_html_e( 'Payment', 'wc-itau-shopline' );

echo "\n\n";

if ( $billet_only ) {
	esc_html_e( 'Please use the link below get your banking billet:', 'wc-itau-shopline' );
} else {
	esc_html_e( 'Please use the link below to make your payment:', 'wc-itau-shopline' );
}

echo "\n";

echo esc_url( $url );

echo "\n";

esc_html_e( 'After we receive the payment confirmation, your order will be processed.', 'wc-itau-shopline' );

echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";
