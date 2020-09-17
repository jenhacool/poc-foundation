<?php

namespace POC\Foundation\Admin\Hooks;

use kornrunner\Ethereum\Address;
use POC\Foundation\Admin\Classes\Plugin_Manager;
use POC\Foundation\Classes\Option;
use POC\Foundation\Contracts\Hook;
use POC\Foundation\License\License;

class Admin_Setup_Wizard implements Hook
{
    const PAGE_SLUG = 'poc-foundation-setup';

	private $step = '';

	private $steps = array();

	public function hooks()
	{
		add_action( 'admin_menu', array( $this, 'add_setup_wizard_page' ) );

		add_action( 'admin_init', array( $this, 'show_setup_wizard_page' ) );

        add_action( "wp_ajax_take_private_key", array( $this, 'create_ajax_function' ) );

        add_action( "wp_ajax_nopriv_take_private_key", array( $this, 'create_ajax_function' ) );
	}

	public function add_setup_wizard_page()
	{
		add_dashboard_page( '', '', 'manage_options', self::PAGE_SLUG, '' );
	}

	public function show_setup_wizard_page()
	{
		if ( empty( $_GET['page'] ) || self::PAGE_SLUG !== $_GET['page'] ) {
			return;
		}

		$this->steps = array(
			'introduction' => array(
				'name'    =>  __( 'Introduction', 'poc-foundation' ),
				'view'    => array( $this, 'step_introduction' ),
				'handler' => array( $this, 'redirect_to_next_step' )
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
				'handler' => array( $this, 'redirect_to_next_step' )
			),
            'done' => array(
                'name' => __( 'Done', 'poc_foundation' ),
                'view' => array( $this, 'step_done' ),
                'handler' => ''
            )
		);

		$this->step = isset( $_GET['step'] ) ? sanitize_key( $_GET['step'] ) : current( array_keys( $this->steps ) );

		$this->enqueue_scripts();

		if ( ! empty( $_POST['save_step'] ) && isset( $this->steps[ $this->step ]['handler'] ) ) {
			call_user_func($this->steps[$this->step]['handler'], $this);
		}

		ob_start();
		$this->setup_wizard_header();
		$this->setup_wizard_steps();
		$this->setup_wizard_content();
		$this->setup_wizard_footer();
		exit;
	}

	public function setup_wizard_header()
    {
		?>
		<!DOCTYPE html>
		<html <?php language_attributes(); ?>>
			<head>
				<meta name="viewport" content="width=device-width" />
				<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
				<title><?php esc_html_e( 'POC Foundation &rsaquo; Setup Wizard', 'poc-foundation' ); ?></title>
				<?php do_action( 'admin_enqueue_scripts' ); ?>
				<?php wp_print_scripts( 'jquery-validation' ); ?>
				<?php wp_print_scripts( 'poc-foundation-setup' ); ?>
				<?php do_action( 'admin_print_styles' ); ?>
				<?php do_action( 'admin_head' ); ?>
			</head>
		<body class="poc-foundation-setup wp-core-ui">
			<h1><?php echo __( 'POC Foundation Setup Wizard', 'poc-foundation' ); ?></h1>
		<?php
	}

	public function setup_wizard_footer()
    {
		?>
			<?php if ( 'done' === $this->step ) : ?>
				<p>
                    <a class="poc-foundation-return-to-dashboard" href="<?php echo esc_url( admin_url() ); ?>"><?php esc_html_e( 'Return to the WordPress Dashboard', 'erp' ); ?></a>
                </p>
			<?php endif; ?>
		</body>
		</html>
		<?php
	}

	public function setup_wizard_steps()
    {
		$output_steps = $this->steps;
		?>
		<ol class="setup-steps">
			<?php foreach ( $output_steps as $step_key => $step ) : ?>
				<?php
                    $li_class = '';
                    if ( $step_key === $this->step ) {
                        $li_class = 'active';
                    } elseif ( array_search( $this->step, array_keys( $this->steps ) ) > array_search( $step_key, array_keys( $this->steps ) ) ) {
                        $li_class = 'done';
                    }
				?>
				<li style="width: <?php echo ( 100 / count( $this->steps ) ) ;?>%" class="<?php echo $li_class; ?>">
                    <a href="<?php echo esc_url( $this->get_step_link( $step_key ) ); ?>"><?php echo esc_html( $step['name'] ); ?></a>
				</li>
			<?php endforeach; ?>
		</ol>
		<?php
	}

	public function setup_wizard_content()
    {
        ?>
        <?php if ( $this->step != 'introduction' && $this->step != 'license' && ! $this->is_license_valid() ) : ?>
            <script>
                window.location.href = "<?php echo $this->get_license_step_link(); ?>";
            </script>
        <?php else : ?>
            <div class="setup-content">
                <?php call_user_func( $this->steps[ $this->step ]['view'] ); ?>
            </div>
        <?php endif;
	}

	protected function next_step_buttons( $submit_text = 'Continue', $allow_skip = false )
    {
		?>
		<p class="setup-actions step">
			<input type="submit" class="button-primary button button-next" value="<?php esc_attr_e( $submit_text, 'poc-foundation' ); ?>" name="save_step" />
            <?php if ( $allow_skip ) : ?>
			    <a href="<?php echo esc_url( $this->get_next_step_link() ); ?>" class="button button-next"><?php esc_html_e( 'Skip this step', 'poc-foundation' ); ?></a>
            <?php endif; ?>
			<?php wp_nonce_field( 'poc-foundation-setup' ); ?>
		</p>
		<?php
	}

	protected function get_license_step_link()
    {
        return admin_url( 'index.php?page=poc-foundation-setup&step=license' );
    }

    protected function get_next_step_key()
    {
	    $keys = array_keys( $this->steps );

	    return $keys[array_search( $this->step, array_keys( $this->steps ) ) + 1];
    }

    protected function get_step_link( $step_key )
    {
	    return admin_url( 'index.php?page=' . self::PAGE_SLUG . '&step=' . $step_key );
    }

	protected function get_next_step_link()
    {
		return $this->get_step_link( $this->get_next_step_key() );
	}

	protected function enqueue_scripts()
	{
	    wp_enqueue_style( 'poc-foundation-setup', POC_FOUNDATION_PLUGIN_URL . 'includes/admin/assets/css/setup-wizard.css', array( 'dashicons', 'install', 'common' ) );
		wp_register_script( 'jquery-validation', POC_FOUNDATION_PLUGIN_URL . 'includes/admin/assets/js/jquery.validate.min.js', array( 'jquery' ) );
		wp_register_script( 'poc-foundation-setup', POC_FOUNDATION_PLUGIN_URL . 'includes/admin/assets/js/setup-wizard.js', array( 'jquery' ) );
		wp_localize_script(
			'poc-foundation-setup',
			'poc_foundation_setup_data',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
                'next_step_link' => $this->get_next_step_link(),
                'setup_nonce' => wp_create_nonce( 'setup_nonce' )
			)
		);
        wp_localize_script( 'poc-foundation-setup', 'create_private_key',
            array(
                'ajax_url' => admin_url( 'admin-ajax.php' )
            )
        );
	}

	public function step_introduction()
	{
		?>
            <form method="POST">
			    <h2><?php echo __( 'Welcome to POC Foundation', 'poc-foundation' ); ?></h2>
                <p><?php echo __( 'Please complete all the steps to start using POC Foundation plugin', 'poc-foundation' ); ?></p>
                <?php $this->next_step_buttons(); ?>
            </form>
		<?php
	}

    public function step_license()
    {
        $license_key = get_option( 'poc_foundation_license_key', '' );
	    ?>
            <form method="POST" id="step-license">
                <table class="form-table">
                    <tbody>
                        <tr>
                            <th scope="row">
                                <label for=""><?php echo __( 'License Key', 'poc-foundation' ); ?></label>
                            </th>
                            <td>
                                <input id="license_key" name="license_key" type="text" value="<?php echo $license_key; ?>">
                                <span class="description"><?php echo __( 'Enter your license key here to start using this plugin.', 'poc-foundation' ); ?></span>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <?php
                    $this->next_step_buttons( __( 'Check', 'poc-foundation' ) );
                ?>
            </form>
        <?php
    }

	public function step_plugins()
	{
	    $plugin_manager = new Plugin_Manager();
        ?>
        <form method="POST" id="step-plugins">
            <table class="form-table">
                <tbody>
                    <?php foreach ( $plugin_manager->get_required_plugins() as $plugin ) : ?>
                        <tr data-slug="<?php echo esc_attr( $plugin['slug'] ); ?>">
                            <th scope="row"><?php echo esc_html( $plugin['name'] ); ?></th>
                            <td>
                                <?php if ( ! $plugin_manager->is_plugin_installed( $plugin['slug'] ) ) : ?>
	                                <?php echo __( 'Required', 'poc-foundation' ); ?>
                                <?php else : ?>
                                    <?php
	                                    $keys = array();

                                        if ( $plugin_manager->is_plugin_updateable( $plugin['slug'] ) !== false ) {
                                            $keys[] = __( 'Update', 'poc-foundation' );
                                        }

                                        if ( ! $plugin_manager->is_plugin_active( $plugin['slug'] ) ) {
                                            $keys[] = __( 'Activation', 'poc-foundation' );
                                        }

                                        if ( $plugin['slug'] === 'elementor-pro' && ! $plugin_manager->elementor_pro->is_license_valid() ) {
                                            $keys[] = __( 'Activate license', 'poc-foundation' );
                                        }

                                        echo ( empty( $keys ) ) ? '<span class="dashicons dashicons-yes"></span>' : implode( ' and ', $keys ) . ' required';
                                    ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

	        <?php $this->next_step_buttons( __( 'Install', 'poc-foundation' ) ); ?>
        </form>
        <?php
	}

    public function step_config()
    {
        $option = new Option();
        ?>
        <form method="POST" id="step-config">
            <table class="form-table">
                <tbody>
                <tr>
                    <th scope="row"><?php echo __( 'API Key', 'poc-foundation' ); ?></th>
                    <td>
                        <input type="text" name="poc_foundation[api_key]" value="<?php echo $option->get( 'api_key' ); ?>">
                        <p class="description"><?php echo __( 'You can get API Key from Campaign Management page on citizen.poc.me.', 'poc-foundation' ); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php echo __( 'UID Prefix', 'poc-foundation' ); ?></th>
                    <td>
                        <input type="text" name="poc_foundation[uid_prefix]" value="<?php echo $option->get( 'uid_prefix' ); ?>">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php echo __( 'Default Discount', 'poc-foundation' ); ?></th>
                    <td>
                        <input type="number" name="poc_foundation[default_discount]" value="<?php echo $option->get( 'default_discount' ); ?>">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php echo __( 'Default Revenue Share', 'poc-foundation' ); ?></th>
                    <td>
                        <input type="number" name="poc_foundation[default_revenue_share]" value="<?php echo $option->get( 'default_revenue_share' ); ?>">
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Private key poc wallet</th>
                    <td>
                        <input type="text" name="poc_foundation[private_key]" id="private_key" value="<?php echo $option->get( 'private_key' ); ?>">
                        <br><br>
                        <?php if(empty($option->get( 'private_key' ))) { ?>
                        <input type="button" class="button-secondary" id="create_private_key" value="Create poc wallet">
                        <?php } ?>
                    </td>
                </tr>
                </tbody>
            </table>

		    <?php $this->next_step_buttons( __( 'Save', 'poc-foundation' ) ); ?>
        </form>
        <?php
    }

    public function step_done()
    {
        ?>
        <h2><?php echo __( 'Done!', 'poc-foundation' ); ?></h2>
        <p><?php echo __( 'Please enjoy using POC Foundation plugin', 'poc-foundation' ); ?></p>
        <?php
    }

    protected function redirect_to_license_step()
    {
        wp_safe_redirect( $this->get_license_step_link() );
        exit;
    }

    public function redirect_to_next_step()
    {
	    wp_safe_redirect( $this->get_next_step_link() );
	    exit;
    }

    protected function is_license_valid()
    {
	    $license_data = ( new License() )->get_license_data();

	    return ( isset( $license_data['status'] ) && $license_data['status'] === 'Active' );
    }

    protected function create_ajax_function(){
        $generate_key = new Address();
        $generate_key->get();
        $private_key = $generate_key->getPrivateKey();
        wp_send_json_success( array( 'private_key' => $private_key ) );
    }
}