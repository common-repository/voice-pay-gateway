<?php

class WC_Gateway_Voice_Pay extends WC_Payment_Gateway
{

    /**
	 * Merchant ID
	 *
	 * @var string
	 */
	protected $merchant_id;

    /**
	 * Merchant Key
	 *
	 * @var string
	 */
	protected $merchant_key;

    /**
	 * Sandbox Environment
	 *
	 * @var string
	 */
	protected $sandbox;

    /**
	 * Constructor
	 */
    public function __construct() {
        // Unique ID for your gateway, e.g., ‘your_gateway’
        // Do not change this id
        $this->id = 'voice_pay';

        // If you want to show an image next to the gateway’s name on the frontend, enter a URL to an image.
        // $this->icon = ''

        // Bool. Can be set to true if you want payment fields to show on the checkout (if doing a direct integration).
        // $this->has_fields

        // Title of the payment method shown on the admin page.
        $this->method_title = 'Voice Pay';

        // Description for the payment method shown on the admin page.
        $this->method_description = 'Pay with Voice';

        $this->init_form_fields();
        $this->init_settings();
        $this->load_settings();

        add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
    }

    /**
     * Show fields on payment page
     *
     * @return void
     */
    public function init_form_fields() {
        $this->form_fields = array(
            'title_settings' => array(
                'title'         => __( 'Settings', 'woocommerce-gateway-voice-pay' ),
                'type'          => 'title',
                'description'   => sprintf(
                    __( 'You must add the following webhook endpoint <code>%s</code> to your Voice Pay account settings.', 'woocommerce-gateway-voice-pay' ),
                    get_rest_url( null, 'voicepay/v1/payment' )
                )
            ),
            'sandbox' => array(
                'title'     => __( 'Use Sandbox', 'woocommerce-gateway-voice-pay' ),
                'type'      => 'checkbox',
                'label'     => __( 'Check this if you want use Sandbox mode.', 'woocommerce-gateway-voice-pay' ),
            ),
            'merchant_id' => array(
                'title'     => __( 'Merchant ID', 'woocommerce-gateway-voice-pay' ),
                'type'      => 'text',
            ),
            'merchant_key' => array(
                'title'     => __( 'Key', 'woocommerce-gateway-voice-pay' ),
                'type'      => 'text',
            ),

            'title_frontend' => array(
                'title'     => __( 'Frontend', 'woocommerce-gateway-voice-pay' ),
                'type'      => 'title'
            ),
            'frontend_title' => array(
                'title'     => __( 'Title', 'woocommerce-gateway-voice-pay' ),
                'default'   => __( 'Pay with Voice', 'woocommerce-gateway-voice-pay'  ),
                'type'      => 'text',
            ),
            'frontend_description' => array(
                'title'     => __( 'Description', 'woocommerce-gateway-voice-pay' ),
                'type'      => 'textarea',
            ),
        );
    }

    /**
     * Load settings.
     *
     * @return void
     */
    public function load_settings() {
        $this->sandbox      = $this->settings['sandbox'];
        $this->merchant_id  = $this->settings['merchant_id'];
        $this->merchant_key = $this->settings['merchant_key'];

        $this->title        = __( $this->settings['frontend_title'], 'woocommerce-gateway-voice-pay' );
        $this->description  = __( $this->settings['frontend_description'], 'woocommerce-gateway-voice-pay' );
    }

	/**
	 * Process Payment.
	 *
	 * @param int $order_id Order ID.
	 * @return array
	 */
    public function process_payment( $order_id ) {

        global $woocommerce;
        $order = new WC_Order($order_id);

        // Mark as on-hold (we're awaiting the cheque)
        $order->update_status( 'on-hold', __( 'Awaiting payment confirmation.', 'woocommerce-gateway-voice-pay' ) );

        // Empty cart
        $woocommerce->cart->empty_cart();

        // Preparing url
        $endpoint = sprintf(
            'https://alexa-skills.amazon%s/apis/custom/skills/%s/tasks/PurchaseTask/versions/%s?',
            '.it', // TODO param this
            VOICE_PAY_SKILL_ID,
            VOICE_PAY_SKILL_TASK_VERSION
        );

        // Preparing data for query
        $order_total    = $order->get_total() * 100; 
        $order_currency = $order->get_currency();
        $response_url   = get_rest_url( null, 'voicepay/v1/payment' );

        // Security
        $salt = $this->create_salt();
        $hash = Voice_Pay::hash_order(
            $order_id, 
            $order_total, 
            $order_currency, 
            $salt, 
            $this->merchant_key
        );

        $query = http_build_query(array(
            'uid'   => $this->merchant_id,
            'tx'    => $order_id,
            'gt'    => $order_total,
            'c'     => $order_currency,
            'url'   => $response_url,
            's'     => $salt,
            'h'     => $hash,
        ));
        
        $redirect = $endpoint . $query;

        // Return thankyou redirect
        return array(
            'result'    => 'success',
            'redirect'  => $redirect
        );
        
    }

    /**
     * Create random string as salt
     *
     * @param integer $salt_length 
     * @return string
     */
    protected function create_salt( $salt_length = 10 ) {
        $chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $chars_length = strlen($chars);
        $salt = '';
        for ( $i = 0; $i < $salt_length; $i++ ) {
            $salt .= $chars[rand( 0, $chars_length - 1 )];
        }
        return $salt;
    }

}
