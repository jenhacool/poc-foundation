<?php
/**
 * Plugin Name: Poc Foundation for Woocommerce
 * Plugin URI: https://poc.foundation/
 * Description: POC Referral system for Woocommerce
 * Version: 3.9.2
 * Author: POC
 * Author URI: https://poc.foundation
 * Text Domain: poc.foundation
 * Domain Path: /i18n/languages/
 *
 * @package Poc.foundation
 */

if( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'POC_FOUNDATION_PLUGIN_FILE', __FILE__ );
define( 'POC_FOUNDATION_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

require_once POC_FOUNDATION_PLUGIN_DIR . 'class-poc-foundation.php';

function poc_foundation() {
    POC_Foundation::instance();
}

add_action( 'plugins_loaded', 'poc_foundation' );

/*
We have to install this script on all pages, except landing page from Google ads, to prevent ads disapproval


jQuery(document).ready(function(){
    if (pocGetCookie('_crmuid')) {
        urlNotify();
    }
});

async function urlNotify() {
    let result = await pocSendGet('/?crmuid='+pocGetCookie('_crmuid')+'&poc_crmuid_notify_url='+encodeURIComponent(jQuery(location).attr('href')))
    if (result.action == "redirect") window.location.href = result.content;
}
*/
