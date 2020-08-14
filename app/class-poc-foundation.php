<?php

namespace POC\Foundation;

use POC\Foundation\License\POC_Foundation_License;
use POC\Foundation\Utilities\SingletonTrait;
use POC\Foundation\Admin\POC_Foundation_Admin;
use POC\Foundation\POC_Foundation_Post_Types;

class POC_Foundation {

    use SingletonTrait;

    public $api;

    /**
     * POC_Foundation constructor.
     */
    protected function __construct()
    {
        $this->init();
    }

	/**
	 * Init
	 */
    public function init()
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
	        new POC_Foundation_Admin();
        }

        new POC_Foundation_Post_Types();

        new POC_Foundation_AJAX();

        $this->api = new POC_Foundation_API();

		if ( $this->is_license_valid() ) {
			new POC_Foundation_Affiliate();
			new POC_Foundation_LGS();
		}
    }

    protected function is_license_valid()
    {
    	return true;
    	return ( new POC_Foundation_License() )->check_license();
    }

    /**
     * Add hooks
     */
    protected function add_hooks()
    {
        add_action( 'wp_enqueue_scripts', array( $this, 'add_scripts' ) );

        add_filter( 'wp_headers', array( $this, 'modify_wp_headers' ) );
    }

    /**
     * Add needed scripts
     */
    public function add_scripts()
    {
        wp_enqueue_script( 'poc-foundation-script', POC_FOUNDATION_PLUGIN_URL . 'assets/js/c.js', array( 'jquery' ) );
    }

	/**
	 * Modify header to allow iFrame from other domain
	 *
	 * @param $headers
	 *
	 * @return mixed
	 */
    public function modify_wp_headers( $headers )
    {
    	$allowed_iframe_domain = get_option( 'poc_foundation_allowed_iframe_domain', '' );

    	if ( ! $allowed_iframe_domain || empty( $allowed_iframe_domain ) ) {
    		return $headers;
	    }

	    $headers['X-Frame-Options'] = "ALLOW-FROM $allowed_iframe_domain";

    	return $headers;
    }

    public static function activate()
    {
        set_transient( 'poc_foundation_activation_redirect', 1, 30 );
    }

    public static function deactivate()
    {

    }
}