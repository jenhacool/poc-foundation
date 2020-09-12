<?php

namespace POC\Foundation\Abstracts;

abstract class Manager
{
	abstract protected function get_runners();

	public function init_runners()
	{
		foreach( $this->get_runners() as $runner ) {
			$runner->hooks();
		}
	}
}