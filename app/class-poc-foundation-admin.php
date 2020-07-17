<?php

namespace POC\Foundation;

use POC\Foundation\Admin\POC_Foundation_Setup_Wizard;

class POC_Foundation_Admin
{
    public $setup_wizard;

    public function __construct()
    {
        $this->setup_wizard = new POC_Foundation_Setup_Wizard();

        $this->add_hooks();
    }

    protected function add_hooks()
    {
	    add_action( 'admin_menu', array( $this, 'register_options_page' ) );

	    add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
    }

	/**
	 * Add register options page
	 */
	public function register_options_page()
	{
		add_menu_page(
			'POC Foundation',
			'POC Foundation',
			'manage_options',
			'poc-foundation',
			array( $this, 'options_page_html' )
		);

		add_submenu_page(
			'poc-foundation',
			'Getting Started',
			'Getting Started',
			'manage_options',
			'poc-foundation-getting-started',
			array( $this, 'get_started_page' )
		);
	}

	public function get_started_page()
	{
		?>
		<div class="wrap">
			<h1><?php echo __( 'Getting Started', 'poc-foundation' ); ?></h1>
			<div class="card">
				<?php $this->setup_wizard->output(); ?>
			</div>
		</div>
		<?php
	}

	public function enqueue_scripts()
    {
        wp_enqueue_style( 'poc-foundation-setup-wizard', POC_FOUNDATION_PLUGIN_URL . '/assets/css/wizard.css', array(), time() );
        wp_register_script( 'poc-foundation-setup-wizard', POC_FOUNDATION_PLUGIN_URL . '/assets/js/wizard.js', array( 'jquery' ), time() );
        wp_localize_script(
            'poc-foundation-setup-wizard',
            'poc_foundation_params',
            array(
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'wp_nonce' => wp_create_nonce( 'poc_foundation_admin_nonce' ),
            )
        );
        wp_enqueue_script( 'poc-foundation-setup-wizard' );
    }
}