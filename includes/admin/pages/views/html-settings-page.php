<div class="wrap">
    <h2><?php echo __( 'POC Foundation Settings', 'poc-foundation' ); ?></h2>
	<form action="" method="post">
		<?php if ( ! empty( $tabs ) ) : ?>
            <?php $current_tab_id = isset( $_GET['tab'] ) ? $_GET['tab'] : $tabs[0]['id']; ?>
            <nav class="nav-tab-wrapper">
                <?php foreach ( $tabs as $tab ) : ?>
                    <a href="<?php echo esc_html( admin_url( 'admin.php?page=poc-foundation&tab=' . esc_attr( $tab['id'] ) ) ); ?>" class="nav-tab <?php echo ( $current_tab_id === $tab['id'] ) ? 'nav-tab-active' : ''; ?> ">
                        <?php echo esc_html( $tab['label'] ); ?>
                    </a>
                <?php endforeach; ?>
            </nav>
            <?php
                $index = array_search( $current_tab_id, array_column( $tabs, 'id' ) );
                $current_tab = $tabs[$index];
                call_user_func( $current_tab['callback'] );
            ?>
            <p class="submit">
                <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php echo __( 'Save Settings', 'poc-foundation' ); ?>">
            </p>
        <?php endif; ?>
	</form>
</div>