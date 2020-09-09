<?php

namespace POC\Foundation\Modules\LGS\Hooks\Elementor;

use POC\Foundation\Contracts\Hook;
use POC\Foundation\Modules\LGS\Hooks\Elementor\Tags\Facebook_URL_Tag;
use POC\Foundation\Modules\LGS\Hooks\Elementor\Tags\Messenger_URL_Tag;
use POC\Foundation\Modules\LGS\Hooks\Elementor\Tags\Ref_By_Tag;
use POC\Foundation\Modules\LGS\Hooks\Elementor\Tags\SubID_Tag;

class Elementor_Tags implements Hook
{
	public function hooks()
	{
		add_action( 'elementor/dynamic_tags/register_tags', array( $this, 'register_dynamic_tags' ) );
	}

	public function register_dynamic_tags( $dynamic_tags )
	{
		\Elementor\Plugin::$instance->dynamic_tags->register_group( 'poc-foundation-dynamic-tags', [
			'title' => 'POC Foundation'
		] );

		$dynamic_tags->register_tag( Facebook_URL_Tag::class );
		$dynamic_tags->register_tag( Messenger_URL_Tag::class );
		$dynamic_tags->register_tag( Ref_By_Tag::class );
		$dynamic_tags->register_tag( SubID_Tag::class );
	}
}