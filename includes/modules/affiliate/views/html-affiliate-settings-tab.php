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
        <tr valign="top">
            <th scope="row">Referral rate</th>
            <td>
                <table>
                    <tr>
                        <th style="text-align: center">Tầng 1</th>
                        <th style="text-align: center">Tầng 2</th>
                        <th style="text-align: center">Tầng 3</th>
                        <th style="text-align: center">Tầng 4</th>
                        <th style="text-align: center">Tầng 5</th>
                        <th style="text-align: center">Tầng 6</th>
                        <th style="text-align: center">Tầng 7</th>
                        <th style="text-align: center">Tầng 8</th>
                        <th style="text-align: center">Tầng 9</th>
                        <th style="text-align: center">Tầng 10</th>
                    </tr>
                    <tr>
                        <td>
                            <input type="number" class="small-text" name="poc_foundation[ref_rates][0]" value="<?php echo ($option->get( 'ref_rates' )[0]); ?>">
                        </td>
                        <td>
                            <input type="number" class="small-text" name="poc_foundation[ref_rates][1]" value="<?php echo ($option->get( 'ref_rates' )[1]); ?>">
                        </td>
                        <td>
                            <input type="number" class="small-text" name="poc_foundation[ref_rates][2]" value="<?php echo ($option->get( 'ref_rates' )[2]); ?>">
                        </td>
                        <td>
                            <input type="number" class="small-text" name="poc_foundation[ref_rates][3]" value="<?php echo ($option->get( 'ref_rates' )[3]); ?>">
                        </td>
                        <td>
                            <input type="number" class="small-text" name="poc_foundation[ref_rates][4]" value="<?php echo ($option->get( 'ref_rates' )[4]); ?>">
                        </td>
                        <td>
                            <input type="number" class="small-text" name="poc_foundation[ref_rates][5]" value="<?php echo ($option->get( 'ref_rates' )[5]); ?>">
                        </td>
                        <td>
                            <input type="number" class="small-text" name="poc_foundation[ref_rates][6]" value="<?php echo ($option->get( 'ref_rates' )[6]); ?>">
                        </td>
                        <td>
                            <input type="number" class="small-text" name="poc_foundation[ref_rates][7]" value="<?php echo ($option->get( 'ref_rates' )[7]); ?>">
                        </td>
                        <td>
                            <input type="number" class="small-text" name="poc_foundation[ref_rates][8]" value="<?php echo ($option->get( 'ref_rates' )[8]); ?>">
                        </td>
                        <td>
                            <input type="number" class="small-text" name="poc_foundation[ref_rates][9]" value="<?php echo ($option->get( 'ref_rates' )[9]); ?>">
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </tbody>
</table>
