<div class="wrap">
	<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
	<form action="options.php" method="post">
		<?php settings_fields( 'poc_foundation_option_group' ); ?>
		<?php do_settings_sections( 'poc_foundation_option_group' ); ?>
        <table class="form-table">
            <tr valign="top">
                <th scope="row"><?php echo __( 'API Key', 'poc-foundation' ); ?></th>
                <td>
                    <input type="text" name="poc_foundation_api_key" value="<?php echo esc_attr( get_option( 'poc_foundation_api_key' ) ); ?>" />
                    <p class="description"><?php echo __( 'You can get API Key from Campaign Management page on citizen.poc.me.' ); ?></p>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><?php echo __( 'UID Prefix', 'poc-foundation' ); ?></th>
                <td>
                    <input type="text" name="poc_foundation_uid_prefix" value="<?php echo esc_attr( get_option( 'poc_foundation_uid_prefix' ) ); ?>" />
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><?php echo __( 'Default Discount', 'poc-foundation' ); ?></th>
                <td>
                    <input type="number" name="poc_foundation_default_discount" value="<?php echo esc_attr( get_option( 'poc_foundation_default_discount' ) ); ?>" />
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><?php echo __( 'Default Revenue Share', 'poc-foundation' ); ?></th>
                <td>
                    <input type="number" name="poc_foundation_default_revenue_share" value="<?php echo esc_attr( get_option( 'poc_foundation_default_revenue_share' ) ); ?>" />
                </td>
            </tr>
        </table>
		<?php submit_button( __( 'Save Settings', 'poc-foundation' ) ); ?>
	</form>
</div>