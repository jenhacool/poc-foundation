<?php

namespace POC\Foundation;

use POC\Foundation\Modules\Affiliate\Affiliate;
use POC\Foundation\Modules\LGS\LGS;
use POC\Foundation\Modules\Bitrix24\Bitrix24;
use POC\Foundation\Contracts\Module;

class Module_Manager
{
	public static function init_modules()
	{
		foreach ( self::get_modules() as $module ) {
			self::init_module( $module );
		}
	}

	protected static function get_modules()
	{
		return array(
			new Affiliate(),
			new LGS(),
			new Bitrix24(),
		);
	}

	protected static function init_module( Module $module )
	{
		$module->init();
	}
}