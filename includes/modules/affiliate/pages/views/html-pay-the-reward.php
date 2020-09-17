<div class="wrap">
    <h1><?php echo __( 'Pay the reward', 'poc-foundation' ); ?></h1>
    <div >
        <form action="<?php echo $url ?>">
            <table id="reward-table" class="form-table comment-ays wp-list-table widefat fixed striped pages">
                <tr>
                    <th scope="row" class="manage-column num "><?php echo __( 'ID Referral', 'poc-foundation-reward' ); ?></th>
                    <th scope="row" class="manage-column num "><?php echo __( 'status', 'poc-foundation-reward' ); ?></th>
                </tr>
                <tbody id="table_id_referral">
                <?php
                foreach ($data_array as $item) {
                    ?>
                    <tr id="<?php echo $item->post_id ?>" >
                        <td class="manage-column num desc"><?php echo $item->post_id ?></td>
                        <td class="manage-column num desc" id="<?php echo 'id_referral_'.$item->post_id ?>"><?php echo $item->meta_value ?></td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
            <br>
        </form>
        <button id="submit_pay_reward" > Check pay the reward </button>
    </div>
</div>
<?php echo $data;
    echo '<br />';
echo $data1; ?>