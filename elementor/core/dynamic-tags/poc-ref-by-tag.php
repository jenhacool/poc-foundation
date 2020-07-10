<?php

class POC_Ref_By_Tag extends \Elementor\Core\DynamicTags\Tag
{
	public function get_name()
	{
		return 'poc_ref_by';
	}

	public function get_title()
	{
		return __( 'POC Ref By', 'elementor-pro' );
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
		if ( isset( $_COOKIE['ref_by'] ) ) {
			echo $_COOKIE['ref_by'];
		} elseif ( isset( $_GET['poc'] ) && ! empty( $_GET['poc'] ) ) {
			echo $_GET['poc'];
		} else {
			echo '';
		}
	}
}