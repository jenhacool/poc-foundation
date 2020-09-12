<div id="poc-foundation-wizard">
	<ul class="wizard-steps">
		<?php foreach ( \POC\Foundation\Admin\Wizard\Wizard_Step::get_steps() as $step ) : ?>
			<?php
				$class = 'step step-' . esc_attr( $step['id'] );
			?>
			<li data-step="<?php echo $step['id']; ?>" class="<?php echo $class; ?>">
				<h2><?php echo $step['title']; ?></h2>

				<?php $content = call_user_func( array( \POC\Foundation\Admin\Wizard\Wizard_Step::class, $step['view'] ) ); ?>

				<?php if ( isset( $content['summary'] ) ) : ?>
					<div class="summary">
						<?php echo wp_kses_post( $content['summary'] ); ?>
					</div>
				<?php endif; ?>

				<?php if ( isset( $content['detail'] ) ) : ?>
					<div class="detail">
						<?php echo $content['detail']; ?>
					</div>
				<?php endif; ?>

				<?php if ( isset( $step['button_text'] ) ) : ?>
					<div class="button-wrap">
						<a href="#" class="button button-primary do-it" data-callback="<?php echo esc_attr( $step['callback'] ); ?>" data-step="<?php echo esc_attr( $step['id'] ); ?>">
							<?php echo esc_html( $step['button_text'] ); ?>
						</a>
					</div>
				<?php endif; ?>
			</li>
		<?php endforeach; ?>
	</ul>
	<div class="step-loading"><span class="spinner"></span></div>
</div>