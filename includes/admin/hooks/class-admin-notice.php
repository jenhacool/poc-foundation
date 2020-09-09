<?php

namespace POC\Foundation\Admin\Hooks;

use POC\Foundation\Contracts\Hook;
use POC\Foundation\License\License;

class Admin_Notice implements Hook
{
	public function hooks()
	{
		add_action( 'admin_notices', array( $this, 'admin_license_notice' ) );
	}

	public function admin_license_notice()
	{
		$license = new License();

		if ( $license->check_license() ) {
			return;
		}

		?>
		<div class="notice notice-error">
			<p><?php echo __( 'POC Foundation license is not valid. Please check your license key', 'poc-foundation' ); ?></p>
			<p><a href="<?php echo admin_url( 'admin.php?page=poc-foundation-license' ); ?>" class="button button-primary"><?php echo __( 'Go to License page', 'poc-foundation' ); ?></a></p>
		</div>
		<?php
	}
}