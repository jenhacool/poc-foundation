<div class="wrap">
	<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
    <form action="options.php" method="post">
		<?php settings_fields( 'poc_foundation_option_group' ); ?>
		<?php do_settings_sections( 'poc_foundation_option_group' ); ?>
        <table class="form-table">
            <tr valign="top">
                <th scope="row"><?php echo __( 'Bitrix24 Webhook', 'poc-foundation' ); ?></th>
                <td>
                    <input type="text" name="poc_foundation_bitrix24_webhook" value="<?php echo esc_attr( get_option( 'poc_foundation_bitrix24_webhook' ) ); ?>" />
                </td>
            </tr>
        </table>
		<?php submit_button( __( 'Save Settings', 'poc-foundation' ) ); ?>
    </form>
</div>