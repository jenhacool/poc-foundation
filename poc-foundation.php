<?php

/**
 * Plugin Name: Poc Foundation
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

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define constant variables
define( 'POC_FOUNDATION_PLUGIN_FILE', __FILE__ );
define( 'POC_FOUNDATION_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'POC_FOUNDATION_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Require autoload
require_once __DIR__ . '/vendor/autoload.php';

// Use main class
use POC\Foundation\Plugin;

// Register activation hook
register_activation_hook( POC_FOUNDATION_PLUGIN_FILE, array( Plugin::class, 'activate' ) );

// Register deactivation hook
register_deactivation_hook( POC_FOUNDATION_PLUGIN_FILE, array( Plugin::class, 'deactivate' ) );

/**
 * Get POC_Foundation instance
 *
 * @return Plugin
 */
function poc_foundation() {
	return Plugin::instance();
}

// Run plugin
add_action( 'plugins_loaded', 'poc_foundation' );