<?php

namespace POC\Foundation\Admin\Hooks;

use POC\Foundation\Contracts\Hook;

class Admin_Setup_Wizard implements Hook
{
	private $step = '';

	private $steps = array();

	public function hooks()
	{
		add_action( 'admin_menu', array( $this, 'add_setup_wizard_page' ) );

		add_action( 'admin_init', array( $this, 'show_setup_wizard_page' ) );

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}

	public function add_setup_wizard_page()
	{
		add_dashboard_page( '', '', 'manage_options', 'poc-foundation-setup', '' );
	}

	public function show_setup_wizard_page()
	{
		if ( empty( $_GET['page'] ) || 'poc-foundation-setup' !== $_GET['page'] ) {
			return;
		}

		$this->steps = array(
			'introduction' => array(
				'name'    =>  __( 'Introduction', 'poc-foundation' ),
				'view'    => array( $this, 'step_introduction' ),
				'handler' => ''
			),
			'license' => array(
				'name' => __( 'License', 'poc_foundation' ),
				'view' => array( $this, 'step_license' ),
                'handler' => ''
			),
			'plugins' => array(
				'name' => __( 'Plugins', 'poc_foundation' ),
				'view' => array( $this, 'step_plugins' ),
				'handler' => ''
			),
			'config' => array(
				'name' => __( 'Config', 'poc_foundation' ),
				'view' => array( $this, 'step_config' ),
				'handler' => ''
			),
		);

		$this->step = isset( $_GET['step'] ) ? sanitize_key( $_GET['step'] ) : current( array_keys( $this->steps ) );

		if ( ! empty( $_POST['save_step'] ) && isset( $this->steps[ $this->step ]['handler'] ) ) {
			call_user_func( $this->steps[ $this->step ]['handler'], $this );
		}

		ob_start();
		$this->setup_wizard_header();
		$this->setup_wizard_steps();
		$this->setup_wizard_content();
		$this->setup_wizard_footer();
		exit;
	}

	public function setup_wizard_header() {
		?>
		<!DOCTYPE html>
		<html <?php language_attributes(); ?>>
			<head>
				<meta name="viewport" content="width=device-width" />
				<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
				<title><?php esc_html_e( 'WooCommerce &rsaquo; Setup Wizard', 'woocommerce' ); ?></title>
				<?php do_action( 'admin_enqueue_scripts' ); ?>
				<?php wp_print_scripts( 'poc-foundation-setup' ); ?>
				<?php do_action( 'admin_print_styles' ); ?>
				<?php do_action( 'admin_head' ); ?>
			</head>
		<body class="poc-foundation-setup wp-core-ui">
			<h1>POC Foundation Setup Wizard</h1>
		<?php
	}

	public function setup_wizard_footer() {
		?>
			<?php if ( 'next_steps' === $this->step ) : ?>
				<a class="poc-foundation-return-to-dashboard" href="<?php echo esc_url( admin_url() ); ?>"><?php esc_html_e( 'Return to the WordPress Dashboard', 'erp' ); ?></a>
			<?php endif; ?>
		</body>
		</html>
		<?php
	}

	public function setup_wizard_steps() {
		$output_steps = $this->steps;
		array_shift( $output_steps );
		?>
		<ol class="setup-steps">
			<?php foreach ( $output_steps as $step_key => $step ) : ?>
				<li class="<?php
				if ( $step_key === $this->step ) {
					echo 'active';
				} elseif ( array_search( $this->step, array_keys( $this->steps ) ) > array_search( $step_key, array_keys( $this->steps ) ) ) {
					echo 'done';
				}
				?>"><a href="<?php echo esc_url( admin_url( 'index.php?page=poc-foundation-setup&step=' . $step_key ) ); ?>"><?php echo esc_html( $step['name'] ); ?></a>
				</li>
			<?php endforeach; ?>
		</ol>
		<?php
	}

	public function setup_wizard_content() {
		echo '<div class="setup-content">';
		call_user_func( $this->steps[ $this->step ]['view'] );
		echo '</div>';
	}

	public function next_step_buttons() {
		?>
		<p class="setup-actions step">
			<input type="submit" class="button-primary button button-next" value="<?php esc_attr_e( 'Continue', 'poc-foundation' ); ?>" name="save_step" />
			<a href="<?php echo esc_url( $this->get_next_step_link() ); ?>" class="button button-next"><?php esc_html_e( 'Skip this step', 'poc-foundation' ); ?></a>
			<?php wp_nonce_field( 'erp-setup' ); ?>
		</p>
		<?php
	}

	public function get_next_step_link() {
		$keys = array_keys( $this->steps );
		return add_query_arg( 'step', $keys[ array_search( $this->step, array_keys( $this->steps ) ) + 1 ], remove_query_arg( 'translation_updated' ) );
	}

	public function enqueue_scripts()
	{
	    wp_enqueue_style( 'poc-foundation-setup', POC_FOUNDATION_PLUGIN_URL . 'includes/admin/assets/css/setup-wizard.css', array( 'dashicons', 'install' ) );
		wp_register_script( 'poc-foundation-setup', POC_FOUNDATION_PLUGIN_URL . 'includes/admin/assets/js/setup-wizard.js', array( 'jquery' ) );
		wp_localize_script(
			'poc-foundation-setup',
			'poc_foundation_setup_data',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
			)
		);
	}

	public function step_introduction()
	{
		?>
			<h1><?php echo __( 'Welcome to POC Foundation', 'poc-foundation' ); ?></h1>
		<?php
	}

    public function step_license()
    {
        ?>
        <form method="POST">
            <table class="form-table">
                <tbody>
                    <tr>
                        <th scope="row">
                            <label for=""><?php echo __( 'License Key', 'poc-foundation' ); ?></label>
                        </th>
                        <td>
                            <input id="license_key" name="license_key" type="text">
                            <span class="description"><?php echo __( 'Enter your license key here to start using this plugin.', 'poc-foundation' ); ?></span>
                        </td>
                    </tr>
                </tbody>
            </table>

	        <?php $this->next_step_buttons(); ?>
        </form>
        <?php
    }

	public function step_plugins()
	{
        ?>
        <form method="POST">
            <table class="form-table">
                <tbody>
                <tr>
                    <th scope=""></th>
                </tr>
                </tbody>
            </table>
        </form>
        <?php
	}

    public function step_config()
    {
        ?>
        <form method="POST">
            <table class="form-table">
                <tbody>
                <tr>
                    <th scope="row">
                        <label for=""><?php echo __( 'License Key', 'poc-foundation' ); ?></label>
                    </th>
                    <td>
                        <input type="text">
                        <span class="description"><?php echo __( 'Enter your license key here to start using this plugin.', 'poc-foundation' ); ?></span>
                    </td>
                </tr>
                </tbody>
            </table>

		    <?php $this->next_step_buttons(); ?>
        </form>
        <?php
    }
}