<?php

namespace POC\Foundation\Modules\Affiliate\Pages;

use POC\Foundation\Admin\Pages\Admin_Page;
class Reward_Page implements Admin_Page
{
    public static function render()
    {
        $reward_items = self::get_reward_items();

        $url = wp_get_referer();

        include_once dirname( __FILE__ ) . '/views/html-reward-page.php';
    }

    public static function get_reward_items()
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