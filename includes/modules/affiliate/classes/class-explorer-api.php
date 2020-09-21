<?php
namespace POC\Foundation\Modules\Affiliate\Classes;

use POC\Foundation\Abstracts\API;

class Explorer_API extends API
{
   protected $url = 'https://explorer.nexty.io/';
   protected $get_tx_receipt_status = '?module=transaction&action=getstatus&txhash=';

   public function get_default_headers()
   {
       return array();
   }

   public function get_endpoint()
   {
       return $this->url;
   }

   public function get_default_options()
   {
       return array();
   }

    public function check_status_transaction( $data )
    {
        $path = 'api'.$this->get_tx_receipt_status.$data;

        $result = $this->send_request( $path, 'GET' );

        return $result['result']['isError'];
   }
}