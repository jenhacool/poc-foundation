<?php

namespace POC\Foundation;

use POC\Foundation\License\License;
use POC\Foundation\Admin\Admin;
use POC\Foundation\Classes\AJAX;
use POC\Foundation\Classes\Module_Manager;

class Plugin
{
    public $api;

	/**
	 * The single instance of class
	 *
	 * @var object
	 */
	private static $instance = null;

    /**
     * POC_Foundation constructor.
     */
    protected function __construct()
    {
	    $this->init_classes();

	    $this->add_hooks();
    }

	/**
	 * Init need classes
	 */
    protected function init_classes()
    {
        if ( is_admin() ) {
	        new Admin();
        }

        new AJAX();

		if ( $this->is_license_valid() ) {
			Module_Manager::init_modules();
		}
    }

    protected function is_license_valid()
    {
    	return ( new License() )->check_license();
    }

    /**
     * Add hooks
     */
    protected function add_hooks()
    {
        add_action( 'wp_enqueue_scripts', array( $this, 'add_scripts' ) );
    }

    /**
     * Add needed scripts
     */
    public function add_scripts()
    {
        wp_enqueue_script( 'poc-foundation-script', POC_FOUNDATION_PLUGIN_URL . 'assets/js/c.js', array( 'jquery' ) );
    }

    public static function activate()
    {
        set_transient( 'poc_foundation_activation_redirect', 1, 30 );
    }

    public static function deactivate()
    {

    }

	/**
	 * Get instance of class
	 *
	 * @return object
	 */
	final public static function instance() {
		if ( is_null( static::$instance ) ) {
			static::$instance = new static();
		}
		return static::$instance;
	}

	/**
	 * Prevent cloning.
	 */
	private function __clone() {}

	/**
	 * Prevent unserializing.
	 */
	private function __wakeup() {}
}