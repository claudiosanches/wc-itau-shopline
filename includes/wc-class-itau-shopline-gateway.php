<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WooCommerce Itau Shopline Gateway class.
 *
 * Extended by individual payment gateways to handle payments.
 *
 * @class   WC_Itau_Shopline_Gateway
 * @extends WC_Payment_Gateway
 * @version 1.0.0
 * @author  Claudio Sanches
 */
class WC_Itau_Shopline_Gateway extends WC_Payment_Gateway {

	/**
	 * Itau Shopline API.
	 *
	 * @var WC_Itau_Shopline_API
	 */
	protected $api = null;

	/**
	 * Constructor for the gateway.
	 */
	public function __construct() {
		$this->id                 = 'itau-shopline';
		$this->icon               = apply_filters( 'wc_itau_shopline_icon', plugins_url( 'assets/images/itau.png', plugin_dir_path( __FILE__ ) ) );
		$this->method_title       = __( 'Itau Shopline', 'woocommerce-itau-shopline' );
		$this->method_description = __( 'Accept payments by credit card, online debit or banking billet using the Itau Shopline.', 'woocommerce-itau-shopline' );

		// Load the form fields.
		$this->init_form_fields();

		// Load the settings.
		$this->init_settings();

		// Options.
		$this->title          = $this->get_option( 'title' );
		$this->description    = $this->get_option( 'description' );
		$this->website_code   = $this->get_option( 'website_code' );
		$this->encryption_key = $this->get_option( 'encryption_key' );
		$this->days_to_pay    = $this->get_option( 'days_to_pay' );
		$this->note_line1     = $this->get_option( 'note_line1' );
		$this->note_line2     = $this->get_option( 'note_line2' );
		$this->note_line3     = $this->get_option( 'note_line3' );
		$this->debug          = $this->get_option( 'debug' );
		$this->api            = new WC_Itau_Shopline_API(
			$this->website_code,
			$this->encryption_key,
			$this->title,
			$this->days_to_pay,
			$this->note_line1,
			$this->note_line2,
			$this->note_line3,
			$this->debug
		);

		// Actions.
		add_action( 'woocommerce_api_wc_itau_shopline_gateway', array( $this, 'payment_redirect' ) );
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		add_action( 'woocommerce_thankyou_' . $this->id, array( $this, 'thankyou_page' ) );
		add_action( 'woocommerce_email_after_order_table', array( $this, 'email_instructions' ), 10, 3 );
	}

	/**
	 * Returns a bool that indicates if currency is amongst the supported ones.
	 *
	 * @return bool
	 */
	protected function using_supported_currency() {
		return apply_filters( 'wc_itau_shopline_using_supported_currency', 'BRL' == get_woocommerce_currency() );
	}

	/**
	 * Returns a value indicating the the Gateway is available or not. It's called
	 * automatically by WooCommerce before allowing customers to use the gateway
	 * for payment.
	 *
	 * @return bool
	 */
	public function is_available() {
		// Test if is valid for use.
		$api = ! empty( $this->website_code ) && ! empty( $this->encryption_key );

		$available = 'yes' == $this->get_option( 'enabled' ) && $api && $this->using_supported_currency();

		return $available;
	}

	/**
	 * Initialise Gateway Settings Form Fields.
	 */
	public function init_form_fields() {
		$this->form_fields = array(
			'enabled' => array(
				'title'   => __( 'Enable/Disable', 'woocommerce-itau-shopline' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable Itau Shopline', 'woocommerce-itau-shopline' ),
				'default' => 'no'
			),
			'title' => array(
				'title'       => __( 'Title', 'woocommerce-itau-shopline' ),
				'type'        => 'text',
				'description' => __( 'This controls the title which the user sees during checkout.', 'woocommerce-itau-shopline' ),
				'desc_tip'    => true,
				'default'     => __( 'Backing Ticket or Online Debit', 'woocommerce-itau-shopline' )
			),
			'description' => array(
				'title'       => __( 'Description', 'woocommerce-itau-shopline' ),
				'type'        => 'textarea',
				'description' => __( 'This controls the description which the user sees during checkout.', 'woocommerce-itau-shopline' ),
				'default'     => __( 'Pay with banking billet or online debit in Itau safe environment.', 'woocommerce-itau-shopline' )
			),
			'integration' => array(
				'title'       => __( 'Integration Settings', 'woocommerce-itau-shopline' ),
				'type'        => 'title',
				'description' => ''
			),
			'website_code' => array(
				'title'             => __( 'Website Code', 'woocommerce-itau-shopline' ),
				'type'              => 'text',
				'description'       => __( 'Please enter your Website Code. This is needed in order to take payment.', 'woocommerce-itau-shopline' ),
				'default'           => '',
				'custom_attributes' => array(
					'required' => 'required'
				)
			),
			'encryption_key' => array(
				'title'             => __( 'Encryption Key', 'woocommerce-itau-shopline' ),
				'type'              => 'text',
				'description'       => __( 'Please enter your Encryption Key. This is needed in order to take payment.', 'woocommerce-itau-shopline' ),
				'default'           => '',
				'custom_attributes' => array(
					'required' => 'required'
				)
			),
			'behavior' => array(
				'title'       => __( 'Integration Behavior', 'woocommerce-itau-shopline' ),
				'type'        => 'title',
				'description' => ''
			),
			'days_to_pay' => array(
				'title'             => __( 'Days to Pay', 'woocommerce-itau-shopline' ),
				'type'              => 'number',
				'description'       => __( 'Please enter how many consecutive days customers will have to pay.', 'woocommerce-itau-shopline' ),
				'desc_tip'          => true,
				'default'           => '1',
				'custom_attributes' => array(
					'step' => '1',
					'min'  => '1',
					'max'  => '30'
				)
			),
			'note_line1' => array(
				'title'       => __( 'Notes (line 1)', 'woocommerce-itau-shopline' ),
				'type'        => 'textarea',
				'description' => __( 'Can not be more than 60 characters.', 'woocommerce-itau-shopline' ),
				'desc_tip'    => true,
				'default'     => ''
			),
			'note_line2' => array(
				'title'       => __( 'Notes (line 2)', 'woocommerce-itau-shopline' ),
				'type'        => 'textarea',
				'description' => __( 'Can not be more than 60 characters.', 'woocommerce-itau-shopline' ),
				'desc_tip'    => true,
				'default'     => ''
			),
			'note_line3' => array(
				'title'       => __( 'Notes (line 3)', 'woocommerce-itau-shopline' ),
				'type'        => 'textarea',
				'description' => __( 'Can not be more than 60 characters.', 'woocommerce-itau-shopline' ),
				'desc_tip'    => true,
				'default'     => ''
			),
			'testing' => array(
				'title'       => __( 'Gateway Testing', 'woocommerce-itau-shopline' ),
				'type'        => 'title',
				'description' => ''
			),
			'debug' => array(
				'title'       => __( 'Debug Log', 'woocommerce-itau-shopline' ),
				'type'        => 'checkbox',
				'label'       => __( 'Enable logging', 'woocommerce-itau-shopline' ),
				'default'     => 'no',
				'description' => sprintf( __( 'Log Itau Shopline events, you can check this log in %s.', 'woocommerce-itau-shopline' ), '<a href="' . esc_url( admin_url( 'admin.php?page=wc-status&tab=logs&log_file=' . esc_attr( $this->id ) . '-' . sanitize_file_name( wp_hash( $this->id ) ) . '.log' ) ) . '">' . __( 'System Status &gt; Logs', 'woocommerce-itau-shopline' ) . '</a>' )
			)
		);
	}

	/**
	 * Admin page.
	 */
	public function admin_options() {
		include 'views/html-admin-page.php';
	}

	/**
	 * Process the payment and return the result.
	 *
	 * @param  int $order_id
	 *
	 * @return array
	 */
	public function process_payment( $order_id ) {
		$order = wc_get_order( $order_id );

		// Mark as on-hold (we're awaiting the payment).
		$order->update_status( 'on-hold', __( 'Itau Shopline: Awaiting payment.', 'woocommerce' ) );

		// Remove cart.
		WC()->cart->empty_cart();

		// Return thankyou redirect.
		return array(
			'result'   => 'success',
			'redirect' => $this->get_return_url( $order )
		);
	}

	/**
	 * Payment redirect.
	 */
	public function payment_redirect() {
		@ob_start();

		if ( isset( $_GET['key'] ) ) {
			$order_key = wc_clean( $_GET['key'] );
			$order_id  = wc_get_order_id_by_order_key( $order_key );
			$order     = wc_get_order( $order_id );

			if ( is_object( $order ) && $this->id === $order->payment_method ) {
				if ( 'on-hold' !== $order->status ) {
					$message = sprintf( __( 'You can no longer make the payment for order %s.', 'woocommerce' ), $order->get_order_number() );
					wp_die( $message, __( 'Payment method expired', 'woocommerce' ), array( 'response' => 200 ) );
				}

				$hash  = $this->api->get_payment_hash( $order );
				$url   = $this->api->get_shopline_url( $hash );

				wp_redirect( esc_url_raw( $url ) );
				exit;
			}
		}

		wp_die( __( 'Invalid request!', 'woocommerce' ), __( 'Invalid request!', 'woocommerce' ), array( 'response' => 401 ) );
	}

	/**
	 * Thank you message.
	 * Displays the Itau Shopline link.
	 *
	 * @param int $order_id
	 */
	public function thankyou_page( $order_id ) {
		$order = wc_get_order( $order_id );

		wc_get_template(
			'payment-instructions.php',
			array(
				'url' => WC_Itau_Shopline::get_payment_url( $order->order_key )
			),
			'woocommerce/itau-shopline/',
			WC_Itau_Shopline::get_templates_path()
		);
	}

	/**
	 * Add payment instructions to order email.
	 *
	 * @param  object $order         Order object.
	 * @param  bool   $sent_to_admin Send to admin.
	 * @param  bool   $plain_text    Plain text or HTML.
	 */
	public function email_instructions( $order, $sent_to_admin, $plain_text = false ) {
		if ( $sent_to_admin || 'on-hold' !== $order->status || $this->id !== $order->payment_method ) {
			return;
		}

		$url = WC_Itau_Shopline::get_payment_url( $order->order_key );

		if ( $plain_text ) {
			wc_get_template(
				'emails/plain-instructions.php',
				array(
					'pdf' => $data['pdf']
				),
				'woocommerce/itau-shopline/',
				WC_Itau_Shopline::get_templates_path()
			);
		} else {
			wc_get_template(
				'emails/html-instructions.php',
				array(
					'pdf' => $data['pdf']
				),
				'woocommerce/itau-shopline/',
				WC_Itau_Shopline::get_templates_path()
			);
		}
	}
}
