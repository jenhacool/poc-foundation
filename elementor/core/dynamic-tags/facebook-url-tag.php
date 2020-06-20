<?php

class Facebook_URL_Tag extends \Elementor\Core\DynamicTags\Tag
{
	public function get_name()
	{
		return 'fanpage-url';
	}

	public function get_title()
	{
		return __( 'Fanpage URL', 'elementor-pro' );
	}

	public function get_group()
	{
		return 'poc-foundation-dynamic-tags';
	}

	public function get_categories()
	{
		return [ \Elementor\Modules\DynamicTags\Module::URL_CATEGORY ];
	}

	protected function _register_controls()
	{
		parent::_register_controls();
	}

	public function render()
	{
		echo ( $_COOKIE['poc_foundation_fanpage_url'] ) ? $_COOKIE['poc_foundation_fanpage_url'] : get_option( 'poc_foundation_fanpage_url' );
	}
}