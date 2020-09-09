<?php

namespace POC\Foundation\Admin\Pages;

class Getting_Started_Page implements Admin_Page
{
	public static function render()
	{
		?>
		<div class="wrap">
			<h1><?php echo __( 'Getting Started', 'poc-foundation' ); ?></h1>
			<div class="card" style="min-width: unset; max-width: unset;">
                <?php
                    include_once dirname( __FILE__ ) . '/views/html-getting-started-page.php';
                ?>
			</div>
		</div>
		<?php
	}
}