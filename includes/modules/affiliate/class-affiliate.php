<?php

namespace POC\Foundation\Modules\Affiliate;

use POC\Foundation\Abstracts\Manager;
use POC\Foundation\Contracts\Module;
use POC\Foundation\Modules\Affiliate\Hooks\Affiliate_AJAX;
use POC\Foundation\Modules\Affiliate\Hooks\Affiliate_Admin;
use POC\Foundation\Modules\Affiliate\Hooks\Affiliate_Checkout;
use POC\Foundation\Modules\Affiliate\Hooks\Affiliate_Order_Actions;
use POC\Foundation\Modules\Affiliate\Hooks\Affiliate_Product_Options;
use POC\Foundation\Modules\Affiliate\Hooks\Affiliate_Cart_Actions;

class Affiliate extends Manager implements Module
{
	public function init()
	{
		$this->init_runners();
	}

	protected function get_runners()
	{
		return array(
			new Affiliate_Admin(),
			new Affiliate_Checkout(),
			new Affiliate_Product_Options(),
			new Affiliate_Order_Actions(),
			new Affiliate_Cart_Actions(),
            new Affiliate_AJAX()
		);
	}
}