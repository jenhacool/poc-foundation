<?php

namespace POC\Foundation\Admin;

class POC_Foundation_Setup_Wizard
{
	public function output()
	{
		include_once dirname( __FILE__ ) . '/views/html-setup-wizard.php';
	}

	public function get_steps()
	{
		return array(
			'intro' => array(
				'id' => 'intro',
				'title' => __( 'Welcome', 'poc_foundation' ),
				'icon' => 'dashboard',
				'view' => 'get_step_intro',
				'callback' => 'do_next_step',
				'button_text' => __( 'Start Now', 'poc-foundation' ),
				'can_skip' => false
			),
			'license' => array(
				'id' => 'license',
				'title' => __( 'License', 'poc_foundation' ),
				'icon' => 'dashboard',
				'view' => 'get_step_license',
				'callback' => 'install_plugins',
				'button_text' => __( 'Install Plugins Now', 'poc-foundation' ),
				'can_skip' => false
			),
			'plugins' => array(
				'id' => 'plugins',
				'title' => __( 'Plugins', 'poc_foundation' ),
				'icon' => 'dashboard',
				'view' => 'get_step_plugins',
				'callback' => 'configure_plugin',
				'button_text' => __( 'Configure Plugin', 'poc-foundation' ),
				'can_skip' => false
			),
			'config' => array(
				'id' => 'config',
				'title' => __( 'Config', 'poc_foundation' ),
				'icon' => 'dashboard',
				'view' => 'get_step_config',
				'callback' => 'do_next_step',
				'button_text' => __( 'Save Config', 'poc-foundation' ),
				'can_skip' => false
			),
			'done' => array(
				'id' => 'done',
				'title' => __( 'Done', 'poc_foundation' ),
				'icon' => 'dashboard',
				'view' => 'get_step_done',
				'callback' => '',
			),
		);
	}

	public function get_step_intro()
	{
		$content = array();
		$content['summary'] = sprintf( '<p>%s</p>', 'Click the button below to get started. If you decide not to go through the wizard now, you can return to this page any time you like.', 'poc-foundation' );
		return $content;
	}

	public function get_step_license()
	{
		$content = array();
		$content['summary'] = sprintf( '<p>%s</p>', 'Enter your license key here to start using this plugin.', 'poc-foundation' );
		$content['detail'] = '<form action="" method="POST">';
		$content['detail'] .= wp_nonce_field( 'poc_foundation_install_plugins', 'poc_foundation_install_plugins' );
		$content['detail'] .= '<input type="text" />';
		$content['detail'] .= '<p><input class="button button-primary" type="submit" value="Check license"></p>';
		$content['detail'] .= '</form>';
		
		return $content;
	}

	public function get_step_plugins()
	{

	}

	public function get_step_config()
	{

	}

	public function get_step_done()
	{

	}
}