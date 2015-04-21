<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WooCommerce Itau Shopline Cripto class.
 *
 * Inspired by https://github.com/gabrielrcouto/php-itaucripto - Gabriel Rodrigues Couto.
 *
 * @class   WC_Itau_Shopline_Cripto
 * @version 1.0.0
 * @author  Claudio Sanches
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
			throw new Exception( sprintf( __( 'The company code size can not be different of %d positions.', 'woocommerce-itau-shopline' ), self::CODE_LENGTH ) );
		}

		if ( strlen( $key ) != self::KEY_LENGTH ) {
			throw new Exception( sprintf( __( 'The key size can not be different of %d positions.', 'woocommerce-itau-shopline' ), self::CODE_LENGTH ) );
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
	 * @param  int    $order_number
	 * @param  float  $order_total
	 * @param  string $description
	 * @param  string $customer_name
	 * @param  string $registration
	 * @param  string $document
	 * @param  string $address
	 * @param  string $neighborhood
	 * @param  string $zipcode
	 * @param  string $city
	 * @param  string $state
	 * @param  string $expiry
	 * @param  string $return_url
	 * @param  string $note_line1
	 * @param  string $note_line2
	 * @param  string $note_line3
	 *
	 * @return string
	 */
	public function generate_data(
		$order_number,
		$order_total,
		$description,
		$customer_name,
		$registration,
		$document,
		$address,
		$neighborhood,
		$zipcode,
		$city,
		$state,
		$expiry,
		$return_url,
		$note_line1,
		$note_line2,
		$note_line3
	) {

		if ( ( 1 > strlen( $order_number ) ) || ( 8 < strlen( $order_number ) ) ) {
			throw new Exception( __( 'Invalid order number.', 'woocommerce-itau-shopline' ) );
		}

		if ( ! in_array( $registration, array( '01', '02' ) ) ) {
			throw new Exception( __( 'Invalid registration code.', 'woocommerce-itau-shopline' ) );
		}

		if ( '' != $document && ( ! is_numeric( $document ) && 14 < strlen( $document ) ) ) {
			throw new Exception( __( 'Invalid document number.', 'woocommerce-itau-shopline' ) );
		}

		if ( '' != $zipcode && ( ! is_numeric( $zipcode ) || 8 != strlen( $zipcode ) ) ) {
			throw new Exception( __( 'Invalid zipcode.', 'woocommerce-itau-shopline' ) );
		}

		if ( '' != $expiry && ( ! is_numeric( $expiry ) || 8 != strlen( $expiry ) ) ) {
			throw new Exception( __( 'Invalid expiry date.', 'woocommerce-itau-shopline' ) );
		}

		if ( 60 < strlen( $note_line1 ) ) {
			throw new Exception( __( 'Invalid note line 1. Can not be more than 60 characters.', 'woocommerce-itau-shopline' ) );
		}

		if ( 60 < strlen( $note_line2 ) ) {
			throw new Exception( __( 'Invalid note line 2. Can not be more than 60 characters.', 'woocommerce-itau-shopline' ) );
		}

		if ( 60 < strlen( $note_line3 ) ) {
			throw new Exception( __( 'Invalid note line 3. Can not be more than 60 characters.', 'woocommerce-itau-shopline' ) );
		}

		// Fix zeros.
		$order_number = $this->fill_zeros( $order_number, 8 );
		$order_total  = $this->fill_zeros( number_format( $order_total, 2, '', '' ) , 10 );

		// Remove accents.
		$description   = $this->remove_accents( $description );
		$customer_name = $this->remove_accents( $customer_name );
		$address       = $this->remove_accents( $address );
		$neighborhood  = $this->remove_accents( $neighborhood );
		$city          = $this->remove_accents( $city );
		$note_line1    = $this->remove_accents( $note_line1 );
		$note_line2    = $this->remove_accents( $note_line2 );
		$note_line3    = $this->remove_accents( $note_line3 );

		// Fill empty values.
		$description   = $this->fill_empty( $description, 40 );
		$customer_name = $this->fill_empty( $customer_name, 30 );
		$registration  = $this->fill_empty( $registration, 2 );
		$document      = $this->fill_empty( $document, 14 );
		$address       = $this->fill_empty( $address, 40 );
		$neighborhood  = $this->fill_empty( $neighborhood, 15 );
		$zipcode       = $this->fill_empty( $zipcode, 8 );
		$city          = $this->fill_empty( $city, 15 );
		$state         = $this->fill_empty( $state, 2 );
		$expiry        = $this->fill_empty( $expiry, 8 );
		$return_url    = $this->fill_empty( $return_url, 60 );
		$note_line1    = $this->fill_empty( $note_line1, 60 );
		$note_line2    = $this->fill_empty( $note_line2, 60 );
		$note_line3    = $this->fill_empty( $note_line3, 60 );

		$_data = $this->algorithm( $order_number . $order_total . $description . $customer_name . $registration . $document . $address . $neighborhood . $zipcode . $city . $state . $expiry . $return_url . $note_line1 . $note_line2 . $note_line3, $this->key );

		$data = $this->algorithm( $this->code . $_data, self::ITAU_KEY );

		return $this->convert( $data );
	}

	/**
	 * Generate cripto.
	 *
	 * @param  string $customer_code
	 *
	 * @return string
	 */
	public function generate_cripto( $customer_code ) {
		$_data = $this->algorithm( $customer_code, $this->key );
		$data  = $this->algorithm( $this->code . $_data, self::ITAU_KEY);

		return $this->convert( $data );
	}

	/**
	 * Generate request.
	 *
	 * @param  string $order_number
	 * @param  string $type
	 *
	 * @return string
	 */
	public function generate_request( $order_number, $type ) {
		if ( ! in_array( $type, array( '0', '1' ) ) ) {
			throw new Exception( __( 'Invalid type.', 'woocommerce-itau-shopline' ) );
		}

		$order_number = $this->fill_zeros( $order_number, 8 );

		$_data = $this->algorithm( $order_number . $type, $this->key );
		$data  = $this->algorithm( $this->code . $_data, self::ITAU_KEY );

		return $this->convert( $data );
	}

	/**
	 * Decripto
	 *
	 * @param  string $data
	 *
	 * @return array
	 */
	public function decripto( $data ) {
		$data   = $this->unconvert( $data );
		$string = $this->algorithm( $data, $this->key );

		return array(
			'code'         => substr( $string, 0, 26 ),
			'order_number' => substr( $string, 26, 8 ),
			'payment_type' => substr( $string, 34, 2 )
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
		$_data = $this->algorithm( $data, $this->key );
		$data  = $this->algorithm( $this->code . $_data, self::ITAU_KEY );

		return $this->convert( $data );
	}
}
