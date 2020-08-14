<?php

namespace POC\Foundation\Admin;

use POC\Foundation\Admin\POC_Foundation_Setup_Wizard;
use POC\Foundation\License\POC_Foundation_License;

class POC_Foundation_Admin
{
    public $setup_wizard;

    public $license;

    public function __construct()
    {
        $this->setup_wizard = new POC_Foundation_Setup_Wizard();

        $this->license = new POC_Foundation_License();

        $this->add_hooks();
    }

    protected function add_hooks()
    {
	    add_action( 'admin_init', array( $this, 'on_admin_init' ) );

	    add_action( 'admin_notices', array( $this, 'admin_license_notice' ) );

	    add_action( 'admin_menu', array( $this, 'register_options_page' ) );

	    add_action( 'admin_menu', array( $this, 'admin_menu_change_name' ), 200 );

	    add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
    }

    public function on_admin_init()
    {
        $this->register_plugin_settings();

        $this->maybe_remove_all_admin_notices();

        $this->admin_redirect();
    }

	/**
	 * Plugin settings init
	 */
	public function register_plugin_settings()
	{
		register_setting( 'poc_foundation_option_group', 'poc_foundation_api_key' );
		register_setting( 'poc_foundation_option_group', 'poc_foundation_uid_prefix' );
		register_setting( 'poc_foundation_option_group', 'poc_foundation_redirect_page' );
		register_setting( 'poc_foundation_option_group', 'poc_foundation_fanpage_id' );
		register_setting( 'poc_foundation_option_group', 'poc_foundation_fanpage_url' );
		register_setting( 'poc_foundation_option_group', 'poc_foundation_chatbot_backlink' );
		register_setting( 'poc_foundation_option_group', 'poc_foundation_allowed_iframe_domain' );
		register_setting( 'poc_foundation_option_group', 'poc_foundation_default_discount' );
        register_setting( 'poc_foundation_option_group', 'poc_foundation_default_revenue_share' );
	}

	/**
	 * Add register options page
	 */
	public function register_options_page()
	{
		add_menu_page(
			__( 'POC Foundation', 'poc-foundation' ),
			__( 'POC Foundation', 'poc-foundation' ),
			'manage_options',
			'poc-foundation',
			array( $this, 'options_page_html' )
		);

//		add_submenu_page(
//			'poc-foundation',
//			__( 'Leads', 'poc-foundation' ),
//			__( 'Leads', 'poc-foundation' ),
//			'manage_options',
//			'poc-foundation-leads',
//			array( $this, 'leads_page_html' )
//		);

		add_submenu_page(
			'poc-foundation',
			__( 'License', 'poc-foundation' ),
			__( 'License', 'poc-foundation' ),
			'manage_options',
			'poc-foundation-license',
			array( $this, 'license_page' )
		);

		add_submenu_page(
			'poc-foundation',
			__( 'Getting Started', 'poc-foundation' ),
			__( 'Getting Started', 'poc-foundation' ),
			'manage_options',
			'poc-foundation-getting-started',
			array( $this, 'get_started_page' )
		);
	}

	public function admin_menu_change_name()
    {
	    global $submenu;

	    if ( isset( $submenu['poc-foundation'] ) ) {
		    $submenu['poc-foundation'][0][0] = __( 'Settings', 'poc-foundation' );
	    }
    }

	/**
	 * Plugin options page HTML
	 */
	public function options_page_html()
	{
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( isset( $_GET['settings-updated'] ) ) {
			add_settings_error( 'poc_foundation_messages', 'poc_foundation_message', __( 'Settings Saved', 'poc_foundation' ), 'updated' );
		}

		settings_errors( 'poc_foundation_messages' );

		include_once dirname( __FILE__ ) . '/views/html-settings.php';
	}

	/*
	 * Get started page HTML
	 */
	public function get_started_page()
	{
		?>
		<div class="wrap">
			<h1><?php echo __( 'Getting Started', 'poc-foundation' ); ?></h1>
			<div class="card" style="min-width: unset; max-width: unset;">
				<?php $this->setup_wizard->output(); ?>
			</div>
		</div>
		<?php
	}

	public function license_page()
    {
        $license_data = ( new POC_Foundation_License() )->get_license_data();

	    include_once dirname( __FILE__ ) . '/views/html-license.php';
    }

    public function campaign_page()
    {
        $default_data = serialize( array(
	        array(
		        'campaign_key' => '',
		        'domain' => '',
		        'redirect_page' => '',
		        'success_page' => '',
		        'chatbot_link' => '',
		        'fanpage_url' => '',
		        'fanpage_id' => '',
	        )
        ) );

        $campaign_data = unserialize( get_option( 'poc_foundation_campaign', $default_data ) );

        include_once dirname( __FILE__ ) . '/views/html-campaign-setting.php';
    }

	public function enqueue_scripts( $hook_suffix )
    {
		if ( strpos( $hook_suffix, 'poc-foundation_page' ) === false && $hook_suffix != 'toplevel_page_poc-foundation' ) {
			return;
		}

		if ( $hook_suffix === 'poc-foundation_page_poc-foundation-getting-started' ) {
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

	    wp_register_script( 'poc-foundation-admin', POC_FOUNDATION_PLUGIN_URL . '/assets/js/admin.js', array( 'jquery' ), time() );
	    wp_localize_script(
		    'poc-foundation-admin',
		    'poc_foundation_params',
		    array(
			    'ajax_url' => admin_url( 'admin-ajax.php' ),
			    'wp_nonce' => wp_create_nonce( 'poc_foundation_admin_nonce' ),
		    )
	    );
	    wp_enqueue_script( 'poc-foundation-admin' );
    }

    public function admin_redirect()
    {
        if ( ! $this->is_new_install() || ! get_transient( 'poc_foundation_activation_redirect' ) ) {
            return;
        }

        return $this->redirect_to_wizard_page();
    }

    public function admin_license_notice()
    {
        if ( $this->license->check_license() ) {
            return;
        }

	    ?>
            <div class="notice notice-error">
                <p><?php echo __( 'POC Foundation license is not valid. Please check your license key', 'poc-foundation' ); ?></p>
                <p><a href="<?php echo admin_url( 'admin.php?page=poc-foundation-license' ); ?>" class="button button-primary"><?php echo __( 'Go to License page', 'poc-foundation' ); ?></a></p>
            </div>
	    <?php
    }

	public function is_new_install()
	{
		return ! get_option( 'poc_foundation_api_key' ) || ! get_option( 'poc_foundation_uid_prefix' );
	}

	protected function redirect_to_wizard_page()
    {
        delete_transient( 'poc_foundation_activation_redirect' );
	    wp_safe_redirect( admin_url( 'admin.php?page=poc-foundation-getting-started' ) );
	    exit;
    }

	protected function maybe_remove_all_admin_notices()
    {
		$elementor_pages = [
			'poc-foundation-license',
            'poc-foundation-getting-started'
		];

		if ( empty( $_GET['page'] ) || ! in_array( $_GET['page'], $elementor_pages, true ) ) {
			return;
		}

		remove_all_actions( 'admin_notices' );
	}
}