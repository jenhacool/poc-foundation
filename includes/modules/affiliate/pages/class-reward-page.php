<?php

namespace POC\Foundation\Modules\Affiliate\Pages;

use POC\Foundation\Admin\Pages\Admin_Page;
class Reward_Page implements Admin_Page
{
    public static function render()
    {
       $data_array = self::get_data_referral_from_meta_table();

        $url = wp_get_referer();

        include_once dirname( __FILE__ ) . '/views/html-pay-the-reward.php';

    }

    public static function get_data_referral_from_meta_table()
    {
        global $wpdb;
        $sql = "SELECT post_id, meta_value
                FROM wp_postmeta 
                WHERE meta_key = 'reward_status'
                    AND meta_value = 'sent'
                    OR meta_value = 'error'
                ";
        $results = $wpdb->get_results( $sql );
        return $results;
    }
}