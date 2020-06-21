<?php

class Facebook_ID_Tag extends \Elementor\Core\DynamicTags\Tag
{
	public function get_name()
	{
		return 'fanpage-id';
	}

	public function get_title()
	{
		return __( 'Fanpage ID', 'elementor-pro' );
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
		$fanpage_id = ( isset( $_COOKIE['poc_foundation_fanpage_id'] ) ) ? $_COOKIE['poc_foundation_fanpage_id'] : get_option( 'poc_foundation_fanpage_id' );
		echo 'https://www.messenger.com/t/' . $fanpage_id;
	}
}