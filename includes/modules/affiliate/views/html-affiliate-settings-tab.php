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
            <th scope="row">Referral rate</th>
            <td>
                <p>Add tax rates for referral rate. Enter a percentage.</p>
                <table id="edd_tax_rates" >
                    <td class="edd_tax_rate">
                        <i>Floor 1 :</i>
                        <input type="number" id="tax_rates[0][rate]" min="0" max="10000" id="ref_rates_1" name="poc_foundation[ref_rates][0]" value="<?php echo ($option->get( 'ref_rates' )[0]); ?>">
                    </td>
                    <td>
                        <span class="edd_remove_tax_rate button-secondary">Remove Rate</span>
                    </td>
                </table>
                <p>
                    <span class="button-secondary" id="edd_add_tax_rate">Add Tax Rate</span>
                </p>
            </td>

        </tr>

    </tbody>
</table>
