<?php

use POC\Foundation\Admin\Wizard\Plugin_Manager;
use Mockery as m;

class Test_Class_Plugin_Manager extends \WP_UnitTestCase
{
	public $instance;

	public function setUp() {
		parent::setUp();

		$this->instance = new Plugin_Manager();
	}

	public function test_get_required_plugins()
	{
		$required_plugins = $this->instance->get_required_plugins();

		$this->assertEquals( 3, count( $required_plugins ) );

		$main_file_paths = array_map( function ( $plugin ) {
			return $plugin['main_file_path'];
		}, $required_plugins );

		$this->assertTrue( in_array( 'woocommerce/woocommerce.php', $main_file_paths ) );
		$this->assertTrue( in_array( 'elementor/elementor.php', $main_file_paths ) );
		$this->assertTrue( in_array( 'elementor-pro/elementor-pro.php', $main_file_paths ) );
	}

	public function test_setup_plugin()
	{
		$mock = m::mock( Plugin_Manager::class )->makePartial()->shouldAllowMockingProtectedMethods();
		$mock->shouldReceive( 'is_plugin_installed' )->once()->with( 'woocommerce' )->andReturn( false );
		$mock->shouldReceive( 'is_plugin_active' )->once()->with( 'woocommerce' )->andReturn( true );
		$mock->shouldReceive( 'is_plugin_updateable' )->once()->with( 'woocommerce' )->andReturn( false );
		$mock->shouldReceive( 'install_plugin' )->once()->with( 'woocommerce' )->andReturn( true );

		$mock->shouldReceive( 'is_plugin_installed' )->once()->with( 'elementor' )->andReturn( true );
		$mock->shouldReceive( 'is_plugin_active' )->once()->with( 'elementor' )->andReturn( true );
		$mock->shouldReceive( 'is_plugin_updateable' )->once()->with( 'elementor' )->andReturn( true );
		$mock->shouldReceive( 'update_plugin' )->once()->with( 'elementor' )->andReturn( true );

		$this->assertTrue( $mock->setup_plugin( 'woocommerce' ) );
		$this->assertTrue( $mock->setup_plugin( 'elementor' ) );
	}

	public function test_setup_plugin_elementor_pro()
	{
		$mock = m::mock( Plugin_Manager::class )->makePartial()->shouldAllowMockingProtectedMethods();
		$mock->shouldReceive( 'install_plugin' )->andReturn( true );
		$mock->shouldReceive( 'upgrade_plugin' )->andReturn( true );
		$mock->shouldReceive( 'activate_plugin' )->andReturn( true );
		$mock->shouldReceive( 'setup_plugin_elementor_pro' )->once()->andReturn( true );

		$this->assertTrue( $mock->setup_plugin( 'elementor-pro' ) );
	}

	public function test_install_plugin()
	{
		$plugin_upgrader_mock = $this->get_plugin_upgrader_mock();
		$plugin_upgrader_mock->shouldReceive( 'install' )->once()->with( 'http://foo.bar/woocommerce' )->andReturn( true );

		$plugin_manager_mock = $this->get_plugin_manager_mock();
		$plugin_manager_mock->shouldReceive( 'get_required_plugins' )->once()->andReturn( array(
			'woocommerce' => array(
				'download_link' => 'http://foo.bar/woocommerce'
			)
		) );
		$plugin_manager_mock->shouldReceive( 'get_plugin_upgrader' )->once()->andReturn( $plugin_upgrader_mock );

		$this->assertTrue( $plugin_manager_mock->install_plugin( 'woocommerce' ) );
	}

	public function test_upgrade_plugin()
	{
		$plugin_upgrader_mock = $this->get_plugin_upgrader_mock();
		$plugin_upgrader_mock->shouldReceive( 'upgrade' )->once()->with( 'woocommerce/woocommerce.php' )->andReturn( true );

		$plugin_manager_mock = $this->get_plugin_manager_mock();
		$plugin_manager_mock->shouldReceive( 'get_required_plugins' )->once()->andReturn( array(
			'woocommerce' => array(
				'main_file_path' => 'woocommerce/woocommerce.php'
			)
		) );
		$plugin_manager_mock->shouldReceive( 'get_plugin_upgrader' )->once()->andReturn( $plugin_upgrader_mock );

		$this->assertTrue( $plugin_manager_mock->upgrade_plugin( 'woocommerce' ) );
	}

	protected function get_plugin_manager_mock()
	{
		return m::mock( Plugin_Manager::class )->makePartial()->shouldAllowMockingProtectedMethods();
	}

	protected function get_plugin_upgrader_mock()
	{
		include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
		include_once ABSPATH . 'wp-admin/includes/class-plugin-upgrader.php';

		return m::mock( \Plugin_Upgrader::class )->makePartial()->shouldAllowMockingProtectedMethods();
	}
}