<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Itau Shopline for WooCommerce Cripto class.
 *
 * Inspired by https://github.com/gabrielrcouto/php-itaucripto - Gabriel Rodrigues Couto.
 *
 * @class   WC_Itau_Shopline_Cripto
 * @version 1.0.0
 */
class WC_Itau_Shopline_Cripto {

	/**
	 * Itay Key.
	 */
	const ITAU_KEY = 'SEGUNDA12345ITAU';

	/**
	 * Code length.
	 */
	const CODE_LENGTH = 26;

	/**
	 * Key length.
	 */
	const KEY_LENGTH = 16;

	/**
	 * Company code.
	 *
	 * @var string
	 */
	public $code = '';

	/**
	 * Encryption key.
	 *
	 * @var string
	 */
	public $key = '';

	/**
	 * Data
	 *
	 * @var array
	 */
	public $data = array();

	/**
	 * Itau Shopline cripo.
	 *
	 * @param string $code
	 * @param string $key
	 */
	public function __construct( $code, $key ) {
		if ( strlen( $code ) != self::CODE_LENGTH ) {
			throw new Exception( sprintf( __( 'The company code size can not be different of %d positions.', 'wc-itau-shopline' ), self::CODE_LENGTH ) );
		}

		if ( strlen( $key ) != self::KEY_LENGTH ) {
			throw new Exception( sprintf( __( 'The key size can not be different of %d positions.', 'wc-itau-shopline' ), self::CODE_LENGTH ) );
		}

		$this->code = strtoupper( $code );
		$this->key  = strtoupper( $key );
	}

	/**
	 * Generate the algorithm.
	 *
	 * @param  string $data
	 * @param  string $key
	 *
	 * @return string
	 */
	private function algorithm( $data, $key ) {
		$key = strtoupper( $key );

		$k = 0;
		$m = 0;

		$string = '';
		$this->initialize( $key );

		for ( $j = 1; $j <= strlen( $data ); $j++ ) {
			$k = ( $k + 1 ) % 256;
			$m = ( $m + $this->data[ $k ] ) % 256;
			$i = $this->data[ $k ];
			$this->data[ $k ] = $this->data[ $m ];
			$this->data[ $m ] = $i;
			$n  = $this->data[ ( ( $this->data[ $k ] + $this->data[ $m ] ) % 256 ) ];
			$i1 = ( ord( substr( $data, ( $j - 1 ), 1 ) ) ^ $n );
			$string .= chr( $i1 );
		}

		return $string;
	}

	/**
	 * Fill params with empty spaces.
	 *
	 * @param  string $data
	 * @param  int    $quantity
	 *
	 * @return string
	 */
	private function fill_empty( $data, $quantity ) {
		while ( strlen( $data ) < $quantity ) {
			$data .= ' ';
		}

		return substr( $data, 0, $quantity );
	}

	/**
	 * Fill params with zeros.
	 *
	 * @param  string $data
	 * @param  int    $quantity
	 *
	 * @return string
	 */
	private function fill_zeros( $data, $quantity ) {
		while ( strlen( $data ) < $quantity ) {
			$data = '0' . $data;
		}

		return substr( $data, 0, $quantity );
	}

	/**
	 * Initialize data.
	 *
	 * @param string $key
	 */
	private function initialize( $key ) {
		$m = strlen( $key );

		for ( $j = 0; $j <= 255; $j++ ) {
			$this->key[ $j ]  = substr( $key, ( $j % $m ), 1 );
			$this->data[ $j ] = $j;
		}

		$k = 0;

		for ( $j = 0; $j <= 255; $j++ ) {
			$k = ( $k + $this->data[ $j ] + ord( $this->key[ $j ] ) ) % 256;
			$i = $this->data[ $j ];
			$this->data[ $j ] = $this->data[ $k ];
			$this->data[ $k ] = $i;
		}
	}

	/**
	 * Get random number based on Java Math.get_random_number()
	 *
	 * @return float
	 */
	private function get_random_number() {
		return rand( 0, 999999999 ) / 1000000000;
	}

	/**
	 * Remove accents.
	 *
	 * @param  string $string
	 *
	 * @return string
	 */
	private function remove_accents( $string ) {
		$after  = 'ŠŒŽšœžŸ¥µÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝßàáâãäåæçèéêëìíîïðñòóôõöøùúûüýÿ';
		$before = 'SOZsozYYuAAAAAAACEEEEIIIIDNOOOOOOUUUUYsaaaaaaaceeeeiiiionoooooouuuuyy';

		return strtr( utf8_decode( $string ), utf8_decode( $after ), $before );
	}

	/**
	 * Convert data.
	 *
	 * @param  string $data
	 *
	 * @return string
	 */
	private function convert( $data ) {
		$string = chr( floor( 26.0 * $this->get_random_number() + 65.0 ) );

		for ( $i = 0; $i < strlen( $data ); $i++ ) {
			$string .= ord( substr( $data, $i, 1 ) );
			$string .= chr( floor( 26.0 * $this->get_random_number() + 65.0 ) );
		}

		return $string;
	}

	/**
	 * Unconver data.
	 *
	 * @param  string $data
	 *
	 * @return string
	 */
	private function unconvert( $data ) {
		$string = '';

		for ( $i = 0; $i < strlen( $data ); $i++ ) {
			$_string = '';

			$c = substr( $data, $i, 1 );

			while ( is_numeric( $c ) ) {
				$_string .= substr( $data, $i, 1 );
				$i += 1;
				$c = substr( $data, $i, 1 );
			}

			if ( '' != $_string ) {
				$string .= chr( $_string + 0 );
			}
		}

		return $string;
	}

	/**
	 * Generate payment data.
	 *
	 * @param  array $data
	 *
	 * @return string
	 */
	public function generate_data( $data ) {
		$default = array(
			'order_number'  => '',
			'order_total'   => '',
			'description'   => '',
			'customer_name' => '',
			'registration'  => '',
			'document'      => '',
			'address'       => '',
			'neighborhood'  => '',
			'zipcode'       => '',
			'city'          => '',
			'state'         => '',
			'expiry'        => '',
			'return_url'    => '',
			'note_line1'    => '',
			'note_line2'    => '',
			'note_line3'    => ''
		);
		$args = wp_parse_args( $data, $default );

		if ( ( 1 > strlen( $args['order_number'] ) ) || ( 8 < strlen( $args['order_number'] ) ) ) {
			throw new Exception( __( 'Invalid order number.', 'wc-itau-shopline' ) );
		}

		if ( ! in_array( $args['registration'], array( '01', '02' ) ) ) {
			throw new Exception( __( 'Invalid registration code.', 'wc-itau-shopline' ) );
		}

		if ( '' != $args['document'] && ( ! is_numeric( $args['document'] ) && 14 < strlen( $args['document'] ) ) ) {
			throw new Exception( __( 'Invalid document number.', 'wc-itau-shopline' ) );
		}

		if ( '' != $args['zipcode'] && ( ! is_numeric( $args['zipcode'] ) || 8 != strlen( $args['zipcode'] ) ) ) {
			throw new Exception( __( 'Invalid zipcode.', 'wc-itau-shopline' ) );
		}

		if ( '' != $args['expiry'] && ( ! is_numeric( $args['expiry'] ) || 8 != strlen( $args['expiry'] ) ) ) {
			throw new Exception( __( 'Invalid expiry date.', 'wc-itau-shopline' ) );
		}

		if ( 60 < strlen( $args['note_line1'] ) ) {
			throw new Exception( __( 'Invalid note line 1. Can not be more than 60 characters.', 'wc-itau-shopline' ) );
		}

		if ( 60 < strlen( $args['note_line2'] ) ) {
			throw new Exception( __( 'Invalid note line 2. Can not be more than 60 characters.', 'wc-itau-shopline' ) );
		}

		if ( 60 < strlen( $args['note_line3'] ) ) {
			throw new Exception( __( 'Invalid note line 3. Can not be more than 60 characters.', 'wc-itau-shopline' ) );
		}

		// Fix zeros.
		$args['order_number'] = $this->fill_zeros( $args['order_number'], 8 );
		$args['order_total']  = $this->fill_zeros( number_format( $args['order_total'], 2, '', '' ) , 10 );

		// Remove accents.
		$args['description']   = $this->remove_accents( $args['description'] );
		$args['customer_name'] = $this->remove_accents( $args['customer_name'] );
		$args['address']       = $this->remove_accents( $args['address'] );
		$args['neighborhood']  = $this->remove_accents( $args['neighborhood'] );
		$args['city']          = $this->remove_accents( $args['city'] );
		$args['note_line1']    = $this->remove_accents( $args['note_line1'] );
		$args['note_line2']    = $this->remove_accents( $args['note_line2'] );
		$args['note_line3']    = $this->remove_accents( $args['note_line3'] );

		// Fill empty values.
		$args['description']   = $this->fill_empty( $args['description'], 40 );
		$args['customer_name'] = $this->fill_empty( $args['customer_name'], 30 );
		$args['registration']  = $this->fill_empty( $args['registration'], 2 );
		$args['document']      = $this->fill_empty( $args['document'], 14 );
		$args['address']       = $this->fill_empty( $args['address'], 40 );
		$args['neighborhood']  = $this->fill_empty( $args['neighborhood'], 15 );
		$args['zipcode']       = $this->fill_empty( $args['zipcode'], 8 );
		$args['city']          = $this->fill_empty( $args['city'], 15 );
		$args['state']         = $this->fill_empty( $args['state'], 2 );
		$args['expiry']        = $this->fill_empty( $args['expiry'], 8 );
		$args['return_url']    = $this->fill_empty( $args['return_url'], 60 );
		$args['note_line1']    = $this->fill_empty( $args['note_line1'], 60 );
		$args['note_line2']    = $this->fill_empty( $args['note_line2'], 60 );
		$args['note_line3']    = $this->fill_empty( $args['note_line3'], 60 );

		$_algorithm = $this->algorithm( $args['order_number'] . $args['order_total'] . $args['description'] . $args['customer_name'] . $args['registration'] . $args['document'] . $args['address'] . $args['neighborhood'] . $args['zipcode'] . $args['city'] . $args['state'] . $args['expiry'] . $args['return_url'] . $args['note_line1'] . $args['note_line2'] . $args['note_line3'], $this->key );

		$algorithm = $this->algorithm( $this->code . $_algorithm, self::ITAU_KEY );

		return $this->convert( $algorithm );
	}

	/**
	 * Generate cripto.
	 *
	 * @param  string $customer_code
	 *
	 * @return string
	 */
	public function generate_cripto( $customer_code ) {
		$_algorithm = $this->algorithm( $customer_code, $this->key );
		$algorithm  = $this->algorithm( $this->code . $_algorithm, self::ITAU_KEY);

		return $this->convert( $algorithm );
	}

	/**
	 * Generate request.
	 *
	 * @param  string $order_number
	 * @param  string $type
	 *
	 * @return string
	 */
	public function generate_request( $order_number, $type = '1' ) {
		if ( ! in_array( $type, array( '0', '1' ) ) ) {
			throw new Exception( __( 'Invalid type.', 'wc-itau-shopline' ) );
		}

		$order_number = $this->fill_zeros( $order_number, 8 );

		$_algorithm = $this->algorithm( $order_number . $type, $this->key );
		$algorithm  = $this->algorithm( $this->code . $_algorithm, self::ITAU_KEY );

		return $this->convert( $algorithm );
	}

	/**
	 * Decripto
	 *
	 * @param  string $data
	 *
	 * @return array
	 */
	public function decripto( $data ) {
		$data      = $this->unconvert( $data );
		$algorithm = $this->algorithm( $data, $this->key );

		return array(
			'code'         => substr( $algorithm, 0, 26 ),
			'order_number' => substr( $algorithm, 26, 8 ),
			'payment_type' => substr( $algorithm, 34, 2 )
		);
	}

	/**
	 * Generate generic data.
	 *
	 * @param  string $data
	 *
	 * @return string
	 */
	public function generate_generic_data( $data ) {
		$_algorithm = $this->algorithm( $algorithm, $this->key );
		$algorithm  = $this->algorithm( $this->code . $_algorithm, self::ITAU_KEY );

		return $this->convert( $algorithm );
	}
}
