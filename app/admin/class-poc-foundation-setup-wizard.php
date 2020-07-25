<?php

namespace POC\Foundation\Admin;

use POC\Foundation\POC_Foundation_Plugin_Manager;

class POC_Foundation_Setup_Wizard
{
	public $plugin_manager;

	public function __construct()
	{
		$this->plugin_manager = new POC_Foundation_Plugin_Manager();
	}

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
				'callback' => 'do_next_step',
				'button_text' => __( 'Check license', 'poc-foundation' ),
				'can_skip' => false
			),
			'plugins' => array(
				'id' => 'plugins',
				'title' => __( 'Plugins', 'poc_foundation' ),
				'icon' => 'dashboard',
				'view' => 'get_step_plugins',
				'callback' => 'install_plugins',
				'button_text' => __( 'Install & Update Plugins', 'poc-foundation' ),
				'can_skip' => false
			),
			'config' => array(
				'id' => 'config',
				'title' => __( 'Config', 'poc_foundation' ),
				'icon' => 'dashboard',
				'view' => 'get_step_config',
				'callback' => 'save_config',
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
		$content['detail'] .= '<p><input type="text" /></p>';
		$content['detail'] .= '</form>';
		
		return $content;
	}

	public function get_step_plugins()
	{
		$content = array();

		$content['summary'] = sprintf(
			'<p>%s</p>',
			__( 'This plugin require some additional plugins. Click the button to install. You can still install or deactivate plugins later from the dashboard.', 'poc-foundation' )
		);

		$content['detail'] = '<ul class="plugin-list">';

		foreach ( $this->plugin_manager->get_required_plugins() as $plugin ) {
			$content['detail'] .= '<li data-slug="' . esc_attr( $plugin['slug'] ) . '">' . esc_html( $plugin['name'] ) . '<span class="plugin-label">';
			$keys = array();
			if ( ! $this->plugin_manager->is_plugin_installed( $plugin['slug'] ) ) {
				$keys[] = 'Installation';
			}
			if ( $this->plugin_manager->is_plugin_updateable( $plugin['slug'] ) !== false ) {
				$keys[] = 'Update';
			}
			if ( ! $this->plugin_manager->is_plugin_active( $plugin['slug'] ) ) {
				$keys[] = 'Activation';
			}
			if ( $plugin['slug'] === 'elementor-pro' && ! $this->plugin_manager->elementor_pro->is_license_valid() ) {
				$keys[] = 'Activate license';
			}
			if ( in_array( 'Installation', $keys ) ) {
				$content['detail'] .= 'Required';
			} else {
				$content['detail'] .= ( empty( $keys ) ) ? '<span class="dashicons dashicons-yes"></span>' : implode( ' and ', $keys ) . ' required';
			}
			$content['detail'] .= '</span></li>';
		}

		$content['detail'] .= '</ul>';

		return $content;
	}

	public function get_step_config()
	{
		$content = array();

		$content['summary'] = sprintf(
			'<p>%s</p>',
			__( 'This plugin require some additional plugins. Click the button to install. You can still install or deactivate plugins later from the dashboard.', 'poc-foundation' )
		);

		$content['detail'] = '<div class="plugin-config"><form id="plugin-config-form">';
		$content['detail'] .= '<div class="plugin-config-row"><label>API Key</label><input type="text" name="poc_foundation_api_key" value="' . esc_attr( get_option( 'poc_foundation_api_key' ) ) . '" /></div>';
		$content['detail'] .= '<div class="plugin-config-row"><label>UID Prefix</label><input type="text" name="poc_foundation_uid_prefix" value="' . esc_attr( get_option( 'poc_foundation_uid_prefix' ) ) . '" /></div>';
		$content['detail'] .= '<div class="plugin-config-row"><label>Redirect Page</label><select name="poc_foundation_redirect_page" id="">';
		foreach ( get_pages() as $page ) {
			$selected = ( get_option( 'poc_foundation_redirect_page' ) == $page->ID ) ? 'selected' : '';
			$content['detail'] .= '<option value="' . $page->ID . '" ' . $selected . '>' . $page->post_title . '</option>';
		}
        $content['detail'] .= '</select></div>';
		$content['detail'] .= '<div class="plugin-config-row"><label>Fanpage URL</label><input type="text" name="poc_foundation_fanpage_url" value="' . esc_attr( get_option( 'poc_foundation_fanpage_url' ) ) . '" /></div>';
		$content['detail'] .= '<div class="plugin-config-row"><label>Fanpage ID</label><input type="text" name="poc_foundation_fanpage_id" value="' . esc_attr( get_option( 'poc_foundation_fanpage_id' ) ) . '" /></div>';
		$content['detail'] .= '<div class="plugin-config-row"><label>Chatbot Backlink</label><input type="text" name="poc_foundation_chatbot_backlink" value="' . esc_attr( get_option( 'poc_foundation_chatbot_backlink' ) ) . '" /></div>';
		$content['detail'] .= '</form></div>';

		return $content;
	}

	public function get_step_done()
	{

	}
}