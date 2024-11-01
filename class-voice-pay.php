<?php

class Voice_Pay {

    private static $initiated = false;

    public static function init() {
        if ( ! self::$initiated ) {
			self::init_hooks();
		}
    }

    /**
	 * Initializes WordPress hooks
	 */
    public static function init_hooks() {
        self::$initiated = true;

        if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
			return;
		}

        // WooCommerce - Gateways
        require_once( VOICE_PAY_PLUGIN_DIR . 'includes/class-voice-pay-gateway.php' ); // Loading this file before, will trigger a PHP fatal error (can't find WC_Payment_Gateway class)
        add_filter( 'woocommerce_payment_gateways', array( __CLASS__, 'add_gateways' ) );

    }

    /**
     * Add Voice Pay gateway to WC registered gateways
     *
     * @param array $methods
     * @return array
     */
    public static function add_gateways($methods) {
        $methods[] = 'WC_Gateway_Voice_Pay';
        return $methods;
    }
    
    /**
     * Hash order data
     *
     * @param string    $tx Order id
     * @param integer   $gt Order total
     * @param string    $c Currency
     * @param string    $s Salt
     * @param string    $key Merchant key
     * @return string
     */
    public static function hash_order( $tx, $gt, $c, $s, $key ) {
        $string = "tx={$tx}gt={$gt}c={$c}s={$s}{$key}";
        // $string = "tx={$tx}gt={$gt}s={$s}{$key}";
        // $string = $include_c == false ? "tx={$tx}gt={$gt}s={$s}{$key}" : "tx={$tx}gt={$gt}c={$c}s={$s}{$key}";
        return sha1($string);
    }

}