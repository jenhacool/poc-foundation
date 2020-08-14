<?php

namespace POC\Foundation;

use POC\Foundation\Admin\ElementorPro\Form_Actions\POC_Foundation_Elementor_Pro_Form_Action;

class POC_Foundation_LGS
{
	/**
	 * POC_Foundation_LGS constructor.
	 */
	public function __construct()
	{
		$this->add_hooks();
	}

	/**
	 * Add hooks
	 */
	protected function add_hooks()
	{
		add_action( 'elementor_pro/init', array( $this, 'add_elementor_form_action' ) );

		add_action( 'elementor/dynamic_tags/register_tags', array( $this, 'register_dynamic_tags' ) );
	}

	/**
	 * Add custom Elementor action
	 */
	public function add_elementor_form_action()
	{
		$poc_affiliate_notifier = new POC_Foundation_Elementor_Pro_Form_Action();

		\ElementorPro\Plugin::instance()->modules_manager->get_modules( 'forms' )->add_form_action( $poc_affiliate_notifier->get_name(), $poc_affiliate_notifier );
	}

	public function register_dynamic_tags( $dynamic_tags )
	{
		\Elementor\Plugin::$instance->dynamic_tags->register_group( 'poc-foundation-dynamic-tags', [
			'title' => 'POC Foundation'
		] );

		include_once( POC_FOUNDATION_PLUGIN_DIR . 'elementor/core/dynamic-tags/facebook-url-tag.php' );
		include_once( POC_FOUNDATION_PLUGIN_DIR . 'elementor/core/dynamic-tags/messenger-url-tag.php' );
		include_once( POC_FOUNDATION_PLUGIN_DIR . 'elementor/core/dynamic-tags/poc-ref-by-tag.php' );
		include_once( POC_FOUNDATION_PLUGIN_DIR . 'elementor/core/dynamic-tags/poc-subid-tag.php' );

		$dynamic_tags->register_tag( 'Facebook_URL_Tag' );
		$dynamic_tags->register_tag( 'Messenger_URL_Tag' );
		$dynamic_tags->register_tag( 'POC_Ref_By_Tag' );
		$dynamic_tags->register_tag( 'POC_SubID_Tag' );
	}
}