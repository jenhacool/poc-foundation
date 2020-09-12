<?php

namespace POC\Foundation\Modules\Affiliate\Pages;

use POC\Foundation\Admin\Pages\Admin_Page;
use POC\Foundation\Modules\Affiliate\Hooks\Order_Actions;

class Pay_The_Reward_Page implements Admin_Page
{
    public static function render()
    {
       $data_array = self::get_data_referral_from_meta_table();

        $url = wp_get_referer();

//        $dataTransaction = new Order_Actions();
//        $dataTransaction = $dataTransaction->after_order_completed(188);
//        print_r($dataTransaction);
        include_once dirname( __FILE__ ) . '/views/html-pay-the-reward.php';

    }

    public function get_data_referral_from_meta_table()
    {
        global $wpdb;
        $sql = "SELECT post_id, meta_value
                FROM wp_postmeta 
                WHERE meta_key = 'reward_status'
                    AND meta_value = 'sent'
                    OR meta_value = 'error'
                ";
        $results = $wpdb->get_results($sql);
        return $results;
    }
}