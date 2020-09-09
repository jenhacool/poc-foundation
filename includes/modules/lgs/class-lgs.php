<?php

namespace POC\Foundation\Modules\LGS;

use POC\Foundation\Contracts\Manager;
use POC\Foundation\Contracts\Module;
use POC\Foundation\Modules\LGS\Hooks\Elementor\Elementor_Actions;
use POC\Foundation\Modules\LGS\Hooks\Elementor\Elementor_Tags;
use POC\Foundation\Modules\LGS\Hooks\PostTypes\Lead\Lead_Post_Type_Bulk_Actions;
use POC\Foundation\Modules\LGS\Hooks\PostTypes\Lead\Lead_Post_Type_Listing;
use POC\Foundation\Modules\LGS\Hooks\PostTypes\Lead\Lead_Post_Type_Register;

class LGS extends Manager implements Module
{
	public function init()
	{
		$this->init_runners();
	}

	protected function get_runners()
	{
		return array(
			new Elementor_Actions(),
			new Elementor_Tags(),
			new Lead_Post_Type_Register(),
			new Lead_Post_Type_Listing(),
			new Lead_Post_Type_Bulk_Actions()
		);
	}
}