<?php

class Voice_Pay_REST_API
{

    private static $namespace = "voicepay";

    /**
	 * Register the REST API routes.
	 */
    public static function init() {
        if ( ! function_exists( 'register_rest_route' ) ) {
			// The REST API wasn't integrated into core until 4.4, and we support 4.0+ (for now).
			return false;
		}

        if ( ! class_exists( 'woocommerce' ) ) {
			return;
		}

        register_rest_route(
            self::$namespace,
            "/v1/payment",
            array(
                'methods'               => 'POST',
                'permission_callback'   => '__return_true',
                'callback'              => array( __CLASS__, 'process_payment' ),
                'args'                  => array(
                    'tx' => array(
                        'required' => true
                    ),
                    'gt' => array(
                        'required' => true
                    ),
                    'c' => array(
                        'required' => true
                    ),
                    'status' => array(
                        'required' => true
                    ),
                    'gateway_status' => array(
                        'required' => true
                    ),
                    's' => array(
                        'required' => true
                    ),
                    'fee' => array(
                        'required' => true
                    ),
                    'fee_currency' => array(
                        'required' => true
                    ),
                    'hash' => array(
                        'required' => true
                    ),
                )
            )
        );

    }

    /**
     * Process payment result
     *
     * @param WP_REST_Request $request     
     */
    public static function process_payment( $request )
    {

        // Check if Voice Pay is enabled
        $gateways = WC()->payment_gateways()->get_available_payment_gateways();

        /**
         * @var WC_Gateway_Voice_Pay $voice_pay_gateway
         */
        $voice_pay_gateway = isset( $gateways['voice_pay'] ) && $gateways['voice_pay']->enabled === 'yes' ? $gateways['voice_pay'] : false;
        if ( ! $voice_pay_gateway ) {
            return new WP_REST_Response( 'voice_pay_gateway_is_not_enabled', 500 );
        }

        $params = $request->get_params();

        // Check if order id is valid
        $order_id = $params['tx'];
        $order = wc_get_order($order_id);

        if ( ! $order ) {
            return new WP_REST_Response( null, 400 );
        }

        // Get merchant key
        $merchant_key = $voice_pay_gateway->settings['merchant_key'];

        // Generate what the order hash should be
        $correct_hash = Voice_Pay::hash_order(
            $order_id,
            $order->get_total() * 100,
            $order->get_currency(),
            $params["s"],
            $merchant_key,
            true
        );

        // Compare correct hash with the one arrived in POST
        if ( $correct_hash != $params['hash'] ) {
            return new WP_REST_Response( null, 400 );
        }

        // At this point, everything seems fine

        if ( $params['status'] == 'pending' ) {
            // Do nothing
            return new WP_REST_Response( null, 200 );
        }

        if ( $params['status'] == 'failed' ) {
            $order->update_status( 'failed', sprintf( 
                    __( 'Payment has failed. Reason: %s', 'woocommerce-gateway-voice-pay' ),
                    $params['gateway_status']
                )
            );
        }
        
        if ( $params['status'] == 'succeeded' ) {
            $order->update_status( 'processing', __( 'Payment confirmed.', 'woocommerce-gateway-voice-pay' ) );
        }
        
        return new WP_REST_Response( null, 200 );
    }
}
