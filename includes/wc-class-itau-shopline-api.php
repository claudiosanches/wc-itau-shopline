<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WooCommerce Itau Shopline API class.
 *
 * @class   WC_Itau_Shopline_API
 * @version 1.0.0
 * @author  Claudio Sanches
 */
class WC_Itau_Shopline_API {

	/**
	 * Payment gateway ID.
	 *
	 * @var string
	 */
	protected $id = 'itau-shopline';

	/**
	 * Shopline URL.
	 *
	 * @var string
	 */
	protected $shopline_url = 'https://shopline.itau.com.br/shopline/shopline.aspx';

	/**
	 * Ticket URL.
	 *
	 * @var string
	 */
	protected $ticket_url = 'https://shopline.itau.com.br/shopline/Itaubloqueto.aspx';

	/**
	 * Request URL.
	 *
	 * @var string
	 */
	protected $request_url = 'https://shopline.itau.com.br/shopline/consulta.aspx';

	/**
	 * Website code.
	 *
	 * @var string
	 */
	protected $website_code;

	/**
	 * Encryption key.
	 *
	 * @var string
	 */
	protected $encryption_key;

	/**
	 * Payment gateway title.
	 *
	 * @var string
	 */
	protected $title;

	/**
	 * Days to pay.
	 *
	 * @var int
	 */
	protected $days_to_pay;

	/**
	 * Notes line 1.
	 *
	 * @var string
	 */
	protected $note_line1;

	/**
	 * Notes line 2.
	 *
	 * @var string
	 */
	protected $note_line2;

	/**
	 * Notes line 3.
	 *
	 * @var string
	 */
	protected $note_line3;

	/**
	 * Debug mode.
	 *
	 * @var string
	 */
	protected $debug;

	/**
	 * Logger.
	 *
	 * @var WC_Logger
	 */
	protected $log = null;

	/**
	 * Constructor.
	 *
	 * @param WC_Itau_Shopline_Gateway $gateway
	 */
	public function __construct(
		$website_code,
		$encryption_key,
		$title       = '',
		$days_to_pay = '',
		$note_line1  = '',
		$note_line2  = '',
		$note_line3  = '',
		$debug       = 'no'
	) {
		$this->website_code   = $website_code;
		$this->encryption_key = $encryption_key;
		$this->title          = $title;
		$this->days_to_pay    = $days_to_pay;
		$this->note_line1     = $note_line1;
		$this->note_line2     = $note_line2;
		$this->note_line3     = $note_line3;
		$this->debug          = $debug;

		// Active logs.
		if ( 'yes' == $this->debug ) {
			$this->log = new WC_Logger();
		}
	}

	/**
	 * Get Shopline URL.
	 *
	 * @return string
	 */
	public function get_shopline_url() {
		return $this->shopline_url;
	}

	/**
	 * Get Ticket URL.
	 *
	 * @return string
	 */
	public function get_ticket_url() {
		return $this->ticket_url;
	}

	/**
	 * Get Request URL.
	 *
	 * @return string
	 */
	public function get_request_url() {
		return $this->request_url;
	}

	/**
	 * Only numbers.
	 *
	 * @param  string|int $string
	 *
	 * @return string|int
	 */
	protected function only_numbers( $string ) {
		return preg_replace( '([^0-9])', '', $string );
	}

	/**
	 * Get CPF or CNPJ.
	 *
	 * @param  WC_Order $order
	 *
	 * @return array
	 */
	protected function get_cpf_cnpj( $order ) {
		$wcbcf_settings = get_option( 'wcbcf_settings' );

		if ( 0 != $wcbcf_settings['person_type'] ) {
			if ( ( 1 == $wcbcf_settings['person_type'] && 1 == $order->billing_persontype ) || 2 == $wcbcf_settings['person_type'] ) {
				return array(
					'code'   => '01',
					'number' => $this->only_numbers( $order->billing_cpf )
				);
			}

			if ( ( 1 == $wcbcf_settings['person_type'] && 2 == $order->billing_persontype ) || 3 == $wcbcf_settings['person_type'] ) {
				return array(
					'code'   => '02',
					'number' => $this->only_numbers( $order->billing_cnpj )
				);
			}
		}

		return array(
			'code'   => '00',
			'number' => ''
		);
	}

	/**
	 * Get Customer name.
	 *
	 * @param  WC_Order $order
	 * @param  string $document_type
	 *
	 * @return string
	 */
	protected function get_customer_name( $order, $document_type ) {
		if ( '02' == $document_type ) {
			return sanitize_text_field( $order->billing_company );
		}

		return sanitize_text_field( $order->billing_first_name . ' ' . $order->billing_last_name );
	}

	/**
	 * Get expiry date.
	 *
	 * @return string
	 */
	protected function get_expiry_date() {
		$days = absint( $this->days_to_pay );

		return date( 'dmY', strtotime( '+' . $days . ' days' ) );
	}

	/**
	 * Save expiry date in the database.
	 *
	 * @param int $order_id
	 */
	protected function save_expiry_date( $order_id ) {
		$days = absint( $this->days_to_pay );
		$date = date( 'Ymd', strtotime( '+' . $days . ' days' ) );

		update_post_meta( $order_id, '_wc_itau_shopline_expiry_date', $date );
	}

	/**
	 * Generating payment hash.
	 *
	 * @param  WC_Order $order
	 *
	 * @return string
	 */
	public function get_payment_hash( $order ) {
		if ( 'yes' == $this->debug ) {
			$this->log->add( $this->id, 'Generating payment hash for order ' . $order->id );
		}

		$document = $this->get_cpf_cnpj( $order );

		$data = array(
			'order_number'  => $order->id,
			'order_total'   => (float) $order->get_total(),
			'description'   => sprintf( __( 'Payment for order %s', 'woocommerce-itau-shopline' ), $order->get_order_number() ),
			'customer_name' => $this->get_customer_name( $order, $document['code'] ),
			'registration'  => $document['code'],
			'document'      => $document['number'],
			'address'       => $order->billing_address_1,
			'neighborhood'  => $order->billing_neighborhood,
			'zipcode'       => $this->only_numbers( $order->billing_postcode ),
			'city'          => $order->billing_city,
			'state'         => $order->billing_state,
			'expiry'        => $this->get_expiry_date(),
			'return_url'    => '', // Just for payment type notification and for websites over SSL.
			'note_line1'    => $this->note_line1,
			'note_line2'    => $this->note_line2,
			'note_line3'    => $this->note_line3
		);

		$data = apply_filters( 'wc_itau_shopline_payment_data', $data );
		$this->save_expiry_date( $order->id );

		if ( 'yes' == $this->debug ) {
			$this->log->add( $this->id, 'Hash data for order ' . $order->id . ': ' . print_r( $data, true ) );
		}

		try {
			$cripto = new WC_Itau_Shopline_Cripto( $this->website_code, $this->encryption_key );
			$hash   = $cripto->generate_data( $data );

			return $hash;
		} catch ( Exception $e ) {
			if ( 'yes' == $this->debug ) {
				$this->log->add( $this->id, 'Error while creating the payment hash for order ' . $order->id . ': ' . $e->getMessage() );
			}

			wc_add_notice( '<strong>' . esc_html( $this->title ) . '</strong>' . esc_html( $e->getMessage() ), 'error' );

			return '';
		}
	}

	/**
	 * Get payment details.
	 *
	 * @param  int $order_id
	 *
	 * @return array
	 */
	public function get_payment_details( $order_id ) {
		if ( 'yes' == $this->debug ) {
			$this->log->add( $this->id, 'Requesting payment details for order ' . $order_id );
		}

		try {
			$cripto = new WC_Itau_Shopline_Cripto( $this->website_code, $this->encryption_key );
			$hash   = $cripto->generate_request( $order_id );
			$params = array(
				'sslverify' => false,
				'timeout'   => 60
			);

			$response = wp_remote_get( $this->get_request_url() . '?DC=' . $hash, $params );

			if ( is_wp_error( $response ) ) {
				throw new Exception( 'WP_Error when requesting the payment details for order ' . $order_id . ': ' . $response->get_error_message() );
			}
			$details = array();
			$data    = new SimpleXmlElement( $response['body'], LIBXML_NOCDATA );

			if ( ! isset( $data->PARAMETER->PARAM[3] ) || isset( $data->PARAMETER->PARAM[4] ) || ! is_object( $data->PARAMETER->PARAM[3] ) || ! is_object( $data->PARAMETER->PARAM[4] ) ) {
				throw new Exception( 'Invalid returned data for order ' . $order_id . ': ' . print_r( $data, true ) );
			}

			$payment_type   = get_object_vars( $data->PARAMETER->PARAM[3] );
			$payment_status = get_object_vars( $data->PARAMETER->PARAM[4] );

			$details = array(
				'payment_type' => sanitize_text_field( $payment_type['@attributes']['VALUE'] ),
				'status'       => sanitize_text_field( $payment_status['@attributes']['VALUE'] )
			);

			if ( 'yes' == $this->debug ) {
				$this->log->add( $this->id, 'Payment details for order ' . $order_id . ' requested successfully:' . print_r( $details, true ) );
			}

			return $details;
		} catch ( Exception $e ) {
			if ( 'yes' == $this->debug ) {
				$this->log->add( $this->id, $e->getMessage() );
			}

			return array();
		}
	}
}
