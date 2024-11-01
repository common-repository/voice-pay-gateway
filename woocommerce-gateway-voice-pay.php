<?php

/**
 * Plugin Name:         Voice Pay Gateway
 * Description:         Accept payments with voice.
 * Version:             2.0.0
 * Requires at least:   4.4
 * Author:              Navoo
 * Author URI:          https://www.navoo.it/
 * License:             GPL-2.0 
 * Text Domain:         woocommerce-gateway-voice-pay
 *
 */


define( 'VOICE_PAY_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'VOICE_PAY_SKILL_ID', 'amzn1.ask.skill.061739c1-9549-4529-9ae6-ec3ba0528647' );
define( 'VOICE_PAY_SKILL_TASK_VERSION', '2' );

require_once( VOICE_PAY_PLUGIN_DIR . 'class-voice-pay.php' );
require_once( VOICE_PAY_PLUGIN_DIR . 'includes/class-voice-pay-rest-api.php' );

add_action( 'plugins_loaded', array( 'Voice_Pay', 'init' ) );
add_action( 'rest_api_init', array( 'Voice_Pay_REST_API', 'init' ) );
