<table class="form-table">
    <tbody>
        <tr valign="top">
            <th scope="row">API Key</th>
            <td>
                <input type="text" id="api_key" name="poc_foundation[api_key]" value="<?php echo $option->get( 'api_key' ); ?>">
                <p class="description">You can get API Key from Campaign Management page on citizen.poc.me.</p>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row">UID Prefix</th>
            <td>
                <input type="text" id="uid_prefix"  name="poc_foundation[uid_prefix]" value="<?php echo $option->get( 'uid_prefix' ); ?>">
            </td>
        </tr>
        <tr valign="top">
            <th scope="row">Default Discount</th>
            <td>
                <input type="number" id="default_discount" name="poc_foundation[default_discount]" value="<?php echo $option->get( 'default_discount' ); ?>">
            </td>
        </tr>
        <tr valign="top">
            <th scope="row">Default Revenue Share</th>
            <td>
                <input type="number" id="default_revenue_share" name="poc_foundation[default_revenue_share]" value="<?php echo $option->get( 'default_revenue_share' ); ?>">
            </td>
        </tr>
        <tr valign="top">
            <th scope="row">Email send notification</th>
            <td>
                <input type="email" name="poc_foundation[email_notification]" value="<?php echo $option->get( 'email_notification' ); ?>">
            </td>
        </tr>
        <tr valign="top">
            <th scope="row">Private key poc wallet</th>
            <td>
                <input type="text" name="poc_foundation[private_key]" id="private_key" value="<?php echo $option->get( 'private_key' ); ?>">
                <?php if(empty($option->get( 'private_key' ))) { ?>
                    <input type="button" class="button-secondary" id="create_private_key" value="Create poc wallet">
                <?php } ?>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row">Referral rates</th>
            <td>
                <p id="title_add_rate">Add level for referral rate. Enter a percentage.</p>
                <table id="edd_tax_rates" >
                    <tbody>
                    <?php if(empty($option->get( 'ref_rates' ))) { ?>
                        <tr>
                            <td class="edd_tax_rate">
                                <i>Level 1 :</i>
                                <input type="number" id="poc_foundation[ref_rates][0]" min="0" max="100" required  name="poc_foundation[ref_rates][0]"> %
                            </td>
                            <td>
                                <span class="edd_remove_tax_rate button-secondary">Remove level</span>
                            </td>
                        </tr>
                    <?php } ?>
                    <?php foreach ($option->get( 'ref_rates' ) as $key => $value) { ?>
                        <tr>
                            <td class="edd_tax_rate">
                                <i>Level <?php echo $key + 1 ?> :</i>
                                <input type="number" id="poc_foundation[ref_rates][<?php echo $key ?>]" min="0" max="100" required  name="poc_foundation[ref_rates][<?php echo $key ?>]" value="<?php echo $value; ?>"> %
                            </td>
                            <td>
                                <span class="edd_remove_tax_rate button-secondary">Remove</span>
                            </td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
                <p>
                    <span class="button-secondary" id="edd_add_tax_rate">Add more level</span>
                </p>
            </td>
            <td>
                <div id="chartContainer" style="height: 300px; width: 500px;"></div>
            </td>

        </tr>

    </tbody>
</table>

