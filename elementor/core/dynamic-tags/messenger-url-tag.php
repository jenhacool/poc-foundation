<?php

class Messenger_URL_Tag extends \Elementor\Core\DynamicTags\Tag
{
	public function get_name()
	{
		return 'messenger_url';
	}

	public function get_title()
	{
		return __( 'Messenger URL', 'poc-foundation' );
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
		if ( ! isset( $_GET['poc_key'] ) ) {
			echo 'https://messenger.com/t/' . get_option( 'poc_foundation_fanpage_id' );
			return;
		}

		$data = get_transient( $_GET['poc_key'] );

		if ( ! $data || ! isset( $data['poc_foundation_fanpage_id'] ) ) {
			echo 'https://messenger.com/t/' . get_option( 'poc_foundation_fanpage_id' );
			return;
		}

		echo get_option( 'poc_foundation_chatbot_backlink' ) . $data['poc_foundation_fanpage_id'];
		return;
	}
}