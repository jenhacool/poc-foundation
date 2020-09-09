<div class="wrap">
	<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
    <div class="wrap">
        <?php
            $args = array(
                'meta_key' => 'bitrix24_status',
                'meta_value' => 'scheduled',
                'post_type' => 'poc_foundation_lead',
                'posts_per_page' => -1
            );

            $query = new WP_Query( $args );

            $leads = $query->get_posts();
        ?>
        <div class="card">
            <h2><?php echo __( 'Send to Bitrix24', 'poc_foundation' ); ?></h2>
            <form action="">
                <p>
                    <label for=""><?php echo __( 'Stage', 'poc_foundation' ); ?></label>
                    <select name="" id="">
                        <?php foreach ( $stages as $id => $stage ) : ?>
                            <option value="<?php echo $id; ?>"><?php echo $stage; ?></option>
                        <?php endforeach; ?>
                    </select>
                </p>
                <p>
                    <table>
                        <thead>
                            <th><?php echo __( 'Name', 'poc-foundation' ); ?></th>
                        </thead>
                        <tbody>
                            <tr>
                                <td>abc</td>
                                <td>abc</td>
                                <td>def</td>
                            </tr>
                        </tbody>
                    </table>
                </p>
            </form>
        </div>
    </div>
</div>