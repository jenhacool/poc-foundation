<?php

namespace POC\Foundation\Admin;

use POC\Foundation\Admin\Hooks\Admin_Setup_Wizard;
use POC\Foundation\Contracts\Manager;
use POC\Foundation\Admin\Hooks\Admin_Init;
use POC\Foundation\Admin\Hooks\Admin_Menu;
use POC\Foundation\Admin\Hooks\Admin_Settings;
use POC\Foundation\Admin\Hooks\Admin_Asset;
use POC\Foundation\Admin\Hooks\Admin_Notice;

class Admin extends Manager
{
    public function __construct()
    {
        $this->init_runners();
    }

    protected function get_runners()
    {
        return array(
        	new Admin_Setup_Wizard(),
        	new Admin_Init(),
	        new Admin_Menu(),
	        new Admin_Settings(),
	        new Admin_Asset(),
	        new Admin_Notice()
        );
    }
}