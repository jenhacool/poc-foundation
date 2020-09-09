<table class="form-table">
    <tbody>
        <tr valign="top">
            <th scope="row">API Key</th>
            <td>
                <input type="text" name="poc_foundation[api_key]" value="<?php echo $option->get( 'api_key' ); ?>">
                <p class="description">You can get API Key from Campaign Management page on citizen.poc.me.</p>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row">UID Prefix</th>
            <td>
                <input type="text" name="poc_foundation[uid_prefix]" value="<?php echo $option->get( 'uid_prefix' ); ?>">
            </td>
        </tr>
        <tr valign="top">
            <th scope="row">Default Discount</th>
            <td>
                <input type="number" name="poc_foundation[default_discount]" value="<?php echo $option->get( 'default_discount' ); ?>">
            </td>
        </tr>
        <tr valign="top">
            <th scope="row">Default Revenue Share</th>
            <td>
                <input type="number" name="poc_foundation[default_revenue_share]" value="<?php echo $option->get( 'default_revenue_share' ); ?>">
            </td>
        </tr>
    </tbody>
</table>
