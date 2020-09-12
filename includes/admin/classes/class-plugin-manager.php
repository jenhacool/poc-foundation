<?php

namespace POC\Foundation\Admin\Classes;

use POC\Foundation\Admin\Classes\Elementor_Pro;

class Plugin_Manager
{
	public $elementor_pro;

	public function __construct()
	{
		$this->elementor_pro = new Elementor_Pro( $this );
	}

	public function get_required_plugins()
	{
		return array(
			'woocommerce' => array(
				'main_file_path' => 'woocommerce/woocommerce.php',
				'name' => 'Woocommerce',
				'slug' => 'woocommerce',
				'download_link' => 'https://downloads.wordpress.org/plugin/woocommerce.4.3.0.zip'
			),
			'elementor' => array(
				'main_file_path' => 'elementor/elementor.php',
				'name' => 'Elementor',
				'slug' => 'elementor',
				'download_link' => 'https://downloads.wordpress.org/plugin/elementor.2.9.7.zip'
			),
			'elementor-pro' => array(
				'main_file_path' => 'elementor-pro/elementor-pro.php',
				'name' => 'Elementor Pro',
				'slug' => 'elementor-pro',
			),
		);
	}

	public function get_plugins( $plugin_folder = '' )
	{
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		return get_plugins( $plugin_folder );
	}

	public function setup_plugin( $slug )
	{
		if ( ! isset( $this->get_required_plugins()[$slug] ) ) {
			return false;
		}

		if ( $slug === 'elementor-pro' ) {
			return $this->setup_plugin_elementor_pro();
		}

		if ( ! $this->is_plugin_installed( $slug ) ) {
			$install = $this->install_plugin( $slug );

			if ( ! $install ) {
				return false;
			}
		}

		if ( $this->is_plugin_updateable( $slug ) ) {
			$update = $this->upgrade_plugin( $slug );

			if ( ! $update ) {
				return false;
			}
		}

		if ( ! $this->is_plugin_active( $slug ) ) {
			$activate = $this->activate_plugin( $slug );

			if ( ! is_null( $activate ) ) {
				return false;
			}
		}

		return true;
	}

	public function install_plugin( $slug )
	{
		return $this->get_plugin_upgrader()->install(
			$this->get_required_plugins()[$slug]['download_link']
		);
	}

	public function upgrade_plugin( $slug )
	{
		return $this->get_plugin_upgrader()->upgrade(
			$this->get_main_file_path_by_slug( $slug ),
			array(
				'clear_update_cache' => false
			)
		);
	}

	public function activate_plugin( $slug )
	{
		return activate_plugin( $this->get_main_file_path_by_slug( $slug ) );
	}

	public function is_plugin_installed( $slug )
	{
		$installed_plugins = $this->get_plugins();

		$main_file_path = $this->get_main_file_path_by_slug( $slug );

		return ( ! empty( $installed_plugins[$main_file_path] ) );
	}

	public function is_plugin_active( $slug )
	{
		$main_file_path = $this->get_main_file_path_by_slug( $slug );

		return is_plugin_active( $main_file_path );
	}

	public function is_plugin_updateable( $slug )
	{
		if ( ! $this->is_plugin_installed( $slug ) ) {
			return false;
		}

		return ( false !== $this->does_plugin_have_update( $slug ) );
	}

	public function does_plugin_have_update( $slug )
	{
		$repo_updates = get_site_transient( 'update_plugins' );

		$main_file_path = $this->get_main_file_path_by_slug( $slug );

		if ( isset( $repo_updates->response[$main_file_path]->new_version ) ) {
			return $repo_updates->response[$main_file_path]->new_version;
		}

		return false;
	}

	public function get_elementor_pro_handler()
	{
		return new Elementor_Pro( $this );
	}

	protected function setup_plugin_elementor_pro()
	{
		return $this->elementor_pro->setup();
	}

	protected function get_main_file_path_by_slug( $slug )
	{
		if ( ! isset( $this->get_required_plugins()[$slug] ) ) {
			return '';
		}

		return $this->get_required_plugins()[$slug]['main_file_path'];
	}

	public function get_plugin_upgrader()
	{
		include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
		include_once ABSPATH . 'wp-admin/includes/class-wp-ajax-upgrader-skin.php';
		include_once ABSPATH . 'wp-admin/includes/class-plugin-upgrader.php';

		return new \Plugin_Upgrader( new \WP_Ajax_Upgrader_Skin() );
	}
}