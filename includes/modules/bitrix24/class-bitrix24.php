<?php

namespace POC\Foundation\Modules\Bitrix24;

use POC\Foundation\Contracts\Manager;
use POC\Foundation\Contracts\Module;
use POC\Foundation\Modules\Bitrix24\Hooks\Bitrix24_AJAX;
use POC\Foundation\Modules\Bitrix24\Hooks\Bitrix24_Admin;
use POC\Foundation\Modules\Bitrix24\Hooks\Bitrix24_Lead_Listing;

class Bitrix24 extends Manager implements Module
{
	public function init()
	{
		$this->init_runners();
	}

	protected function get_runners()
	{
		return array(
			new Bitrix24_Admin(),
			new Bitrix24_Lead_Listing(),
			new Bitrix24_AJAX()
		);
	}
}