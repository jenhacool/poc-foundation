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
				'callback' => 'check_license',
				'button_text' => __( 'Check license', 'poc-foundation' ),
				'can_skip' => false
			),
			'campaign' => array(
				'id' => 'campaign',
				'title' => __( 'Campaigns', 'poc_foundation' ),
				'icon' => 'dashboard',
				'view' => 'get_step_campaign',
				'callback' => 'save_campaign',
				'button_text' => __( 'Save Campaign', 'poc-foundation' ),
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
			'plugins' => array(
				'id' => 'plugins',
				'title' => __( 'Plugins', 'poc_foundation' ),
				'icon' => 'dashboard',
				'view' => 'get_step_plugins',
				'callback' => 'install_plugins',
				'button_text' => __( 'Install & Update Plugins', 'poc-foundation' ),
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
		$content['detail'] = '<form action="" method="POST" id="check-license-form">';
		$content['detail'] .= wp_nonce_field( 'poc_foundation_install_plugins', 'poc_foundation_install_plugins' );
		$content['detail'] .= '<p><input type="text" name="license_key" id="license-key" style="width: 100%" /></p>';
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

	public function get_step_campaign()
	{
		$content = array();

		$content['summary'] = sprintf(
			'<p>%s</p>',
			__( 'Add campaign data', 'poc-foundation' )
		);

		$default_data = serialize( array(
			array(
				'api_key' => '',
				'domain' => '',
				'redirect_page' => '',
				'success_page' => '',
				'chatbot_link' => '',
				'fanpage_url' => '',
				'fanpage_id' => '',
			)
		) );

		$campaign_data = unserialize( get_option( 'poc_foundation_campaign', $default_data ) );

		ob_start();

		include_once POC_FOUNDATION_PLUGIN_DIR . 'app/admin/views/html-campaign-setting-form.php';

		$content['detail'] = ob_get_clean();

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
		$content['detail'] .= '<div class="plugin-config-row"><label>' . __( 'Default Discount', 'poc-foundation' ) . '</label><input type="text" name="poc_foundation_default_discount" value="' . esc_attr( get_option( 'poc_foundation_default_discount' ) ) . '" /></div>';
		$content['detail'] .= '<div class="plugin-config-row"><label>' . __( 'Default Revenue Share', 'poc-foundation' ) . '</label><input type="text" name="poc_foundation_default_revenue_share" value="' . esc_attr( get_option( 'poc_foundation_default_revenue_share' ) ) . '" /></div>';
		$content['detail'] .= '</form></div>';

		return $content;
	}

	public function get_step_done()
	{

	}
}