<?php

class Facebook_ID_Tag extends \Elementor\Core\DynamicTags\Tag
{
	public function get_name()
	{
		return 'messenger_url';
	}

	public function get_title()
	{
		return __( 'Messenger URL', 'elementor-pro' );
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
		if ( isset( $_COOKIE['poc_foundation_fanpage_id'] ) ) {
			echo get_option( 'poc_foundation_chatbot_backlink' ) . $_COOKIE['poc_foundation_fanpage_id'];
		} else {
			echo 'https://messenger.com/t/' . get_option( 'poc_foundation_fanpage_id' );
		}
	}
}