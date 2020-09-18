<?php

namespace POC\Foundation\Modules\Affiliate\Hooks;

use POC\Foundation\Contracts\Hook;

class Affiliate_Checkout implements Hook
{
	public function hooks()
	{
		add_action( 'woocommerce_after_checkout_form', array( $this, 'checkout_form_script' ) );
	}

	public function checkout_form_script()
	{
		$script = '<script type="text/javascript">
					var timeout = null;
                    jQuery( function( $ ) {
                        $(document).ready(function() {
                            $(document.body).on("keyup", "input#billing_email, input#billing_phone", function() {
                                clearTimeout(timeout);
        						timeout = setTimeout(function () {
                                	$("body").trigger("update_checkout");
                                }, 600);
                            });
                        }); 
                    });
                </script>';
		echo $script;
	}
}