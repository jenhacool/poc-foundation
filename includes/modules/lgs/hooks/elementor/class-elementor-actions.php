<?php

namespace POC\Foundation\Modules\LGS\Hooks\Elementor;

use POC\Foundation\Contracts\Hook;
use POC\Foundation\Modules\LGS\Hooks\Elementor\FormActions\Form_Action;

class Elementor_Actions implements Hook
{
	public function hooks()
	{
		add_action( 'elementor_pro/init', array( $this, 'add_elementor_form_action' ) );
	}

	/**
	 * Add custom Elementor action
	 */
	public function add_elementor_form_action()
	{
		$poc_affiliate_notifier = new Form_Action();

		\ElementorPro\Plugin::instance()->modules_manager->get_modules( 'forms' )->add_form_action( $poc_affiliate_notifier->get_name(), $poc_affiliate_notifier );
	}
}