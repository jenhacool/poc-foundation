<?php

namespace POC\Foundation\Modules\LGS\Hooks\Elementor\Tags;

class SubID_Tag extends \Elementor\Core\DynamicTags\Tag
{
	public function get_name()
	{
		return 'poc_subid';
	}

	public function get_title()
	{
		return __( 'POC Sub ID', 'elementor-pro' );
	}

	public function get_group()
	{
		return 'poc-foundation-dynamic-tags';
	}

	public function get_categories()
	{
		return [ \Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY ];
	}

	protected function _register_controls()
	{
		parent::_register_controls();
	}

	public function render()
	{
		if ( isset( $_COOKIE['subid'] ) ) {
			echo $_COOKIE['ref_by'];
		} elseif ( isset( $_GET['subid'] ) && ! empty( $_GET['psubidoc'] ) ) {
			echo $_GET['subid'];
		} else {
			echo '';
		}
	}
}