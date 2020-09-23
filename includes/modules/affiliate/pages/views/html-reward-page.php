<div class="wrap">
    <h2><?php echo __( 'Affiliate Reward', 'poc-foundation' ); ?></h2>
    <form action="<?php echo $url ?>">
        <table id="edd_tax_rates" class="wp-list-table widefat fixed striped posts">
            <thead>
                <tr>
                    <th scope="row" class="num"><b><?php echo __( 'ID Referral', 'poc-foundation-reward' ); ?></b></th>
                    <th scope="row" class="num"><b><?php echo __( 'Status', 'poc-foundation-reward' ); ?></b></th>
                </tr>
            </thead>
            <tbody id="table_id_referral">
            <?php foreach ( $reward_items as $item ) : ?>
                <tr id="<?php echo $item->post_id ?>" >
                    <td class="manage-column num desc"><?php echo $item->post_id ?></td>
                    <td class="manage-column num desc" id="<?php echo 'id_referral_'.$item->post_id ?>"><?php echo $item->meta_value ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <br>
    </form>
    <button id="submit_pay_reward" class="button button-primary" > Check pay the reward </button>
</div>