<?php

namespace POC\Foundation\Modules\Affiliate;

use POC\Foundation\Contracts\Manager;
use POC\Foundation\Contracts\Module;
use POC\Foundation\Modules\Affiliate\Hooks\Affiliate_Admin;
use POC\Foundation\Modules\Affiliate\Hooks\Affiliate_Checkout;
use POC\Foundation\Modules\Affiliate\Hooks\Order_Actions;

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
			new Product_Options(),
			new Order_Actions(),
			new Cart_Actions(),
		);
	}
}